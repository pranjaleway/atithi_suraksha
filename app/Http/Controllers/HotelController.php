<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Document;
use App\Models\Hotel;
use App\Models\HotelBooking;
use App\Models\HotelEmployee;
use App\Models\HotelOwnerDoc;
use App\Models\PoliceStation;
use App\Models\State;
use App\Models\TransferEntry;
use App\Models\UploadedEntry;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class HotelController extends Controller
{
    public function hotels(Request $request)
    {
        if (!hasPermission('hotels', 'view')) {
            abort(403, 'Unauthorized');
        }

        if ($request->ajax()) {
            if (Auth::user()->user_type_id == 3) {
                $policeStation = PoliceStation::where('user_id', Auth::id())->first();

                if ($policeStation) {
                    $data = Hotel::where('police_station_id', $policeStation->id)
                        ->orderBy('id', 'desc')
                        ->get();
                }
            } else {
                $data = Hotel::orderBy('id', 'desc')->get();
            }
            $canAdd = hasPermission('hotels', 'add');
            $canEdit = hasPermission('hotels', 'edit');
            $canDelete = hasPermission('hotels', 'delete');
            return response()->json(['data' => $data, 'canAdd' => $canAdd, 'canEdit' => $canEdit, 'canDelete' => $canDelete]);
        }
        return view('hotel.hotels');

    }

    public function addHotel()
    {
        $states = State::where('status', 1)->orderBy('name', 'asc')->get();
        $cities = City::where('status', 1)->orderBy('name', 'asc')->get();
        $documents = Document::where('status', 1)->orderBy('name', 'asc')->get();
        $policeStations = PoliceStation::where('status', 1)->get();
        return view('hotel.add-edit-hotel', compact('states', 'cities', 'documents', 'policeStations'));
    }

    public function storeHotel(Request $request)
    {

        $request->validate([
            'hotel_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'contact_number' => 'required|numeric|digits:10|unique:hotels,contact_number',
            'owner_contact_number' => 'required|numeric|digits:10|unique:users,phone',
            'aadhar_number' => 'required|numeric|digits:12|unique:hotels,aadhar_number',
            'pan_number' => 'required|string|max:10|unique:hotels,pan_number',
            'license_number' => 'required|string|max:255|unique:hotels,license_number',
            'police_station_id' => 'required|exists:police_stations,id',
            'address' => 'required|string',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'pincode' => 'required|numeric|digits:6',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.unique' => 'This email has already been taken.',
            'contact_number.unique' => 'This contact number has already been taken.',
            'city_id.exists' => 'The selected city is invalid.',
            'state_id.exists' => 'The selected state is invalid.',
            'password.confirmed' => 'The confirmed password does not match.',
            'police_station_id.exists' => 'The selected police station is invalid.'
        ]);

        $hotels = Hotel::create($request->only([
            'hotel_name',
            'owner_name',
            'email',
            'contact_number',
            'owner_contact_number',
            'aadhar_number',
            'pan_number',
            'license_number',
            'police_station_id',
            'address',
            'state_id',
            'city_id',
            'pincode'
        ]));

        if ($request->hasFile('document')) {
            foreach ($request->file('document') as $documentId => $file) {
                $path = $file->store('hotel_owner_documents', 'public'); // stores in storage/app/public/hotel_documents

                HotelOwnerDoc::create([
                    'hotel_id' => $hotels->id,
                    'document_id' => $documentId,
                    'document_path' => $path,
                ]);
            }
        }

        $user = $hotels->user()->create([
            'name' => $request->owner_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type_id' => 4,
            'role' => UserType::where('id', 4)->value('user_type'),
            'phone' => $request->owner_contact_number,
        ]);

        $hotels->update(['user_id' => $user->id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Hotel created successfully',
            'redirect' => route('hotels')
        ]);
    }

    public function editHotel($id)
    {
        $id = base64_decode($id);
        $hotels = Hotel::with('ownerDocuments.document', 'police_station:id,police_station_name')->find($id);
        $states = State::where('status', 1)->orderBy('name', 'asc')->get();
        $cities = City::where('status', 1)
            ->where('state_id', $hotels->state_id)
            ->orderBy('name', 'asc')->get();
        $documents = Document::where('status', 1)->orderBy('name', 'asc')->get();
        $policeStations = PoliceStation::where('status', 1)->get();
        if (!$hotels) {
            abort(404, 'Hotel not found');
        }
        return view('hotel.add-edit-hotel', compact('hotels', 'states', 'cities', 'documents', 'policeStations'));
    }

    public function updateHotel(Request $request)
    {
        $hotel = Hotel::find($request->id);
        $request->validate([
            'hotel_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $hotel->user_id,
            'contact_number' => 'required|numeric|digits:10|unique:hotels,contact_number,' . $request->id,
            'owner_contact_number' => 'required|numeric|digits:10|unique:users,phone,' . $hotel->user_id,
            'aadhar_number' => 'required|numeric|digits:12|unique:hotels,aadhar_number,' . $request->id,
            'pan_number' => 'required|string|max:10|unique:hotels,pan_number,' . $request->id,
            'license_number' => 'required|string|max:255|unique:hotels,license_number,' . $request->id,
            'police_station_id' => 'nullable|exists:police_stations,id',
            'address' => 'required|string',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'pincode' => 'required|numeric|digits:6',
            'password' => 'nullable|string|min:6|confirmed',
        ], [
            'email.unique' => 'This email has already been taken.',
            'contact_number.unique' => 'This contact number has already been taken.',
            'city_id.exists' => 'The selected city is invalid.',
            'state_id.exists' => 'The selected state is invalid.',
            'password.confirmed' => 'The confirmed password does not match.',
            'police_station_id.exists' => 'The selected police station is invalid.'
        ]);

        if(Auth::user()->user_type_id == 4){
            $hotel->status = 0;
            Auth::user()->status = 0;
        }

        $hotel->update($request->all());

        if ($request->hasFile('document')) {
            foreach ($request->file('document') as $documentId => $file) {
                $existingDocument = HotelOwnerDoc::where('hotel_id', $hotel->id)->where('document_id', $documentId)->first();

                if ($existingDocument) {
                    Storage::disk('public')->delete($existingDocument->document_path);
                    $existingDocument->delete();
                }
                $path = $file->store('hotel_owner_documents', 'public'); // stores in storage/app/public/hotel_documents

                HotelOwnerDoc::create([
                    'hotel_id' => $hotel->id,
                    'document_id' => $documentId,
                    'document_path' => $path,
                ]);
            }
        }

        $user = User::find($hotel->user_id);
        if ($user) {
            $user->update([
                'name' => $hotel->owner_name,
                'email' => $hotel->email,
                'phone' => $hotel->contact_number,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => Auth::user()->user_type_id == 4 ? 'Profile updated successfully. Please wait for admin approval' : 'Hotel updated successfully',
            'redirect' => route('hotels')
        ]);
    }

    public function deleteHotel(Request $request)
    {
        $hotel = Hotel::find($request->id);
        if ($hotel->user_id) {
            User::find($hotel->user_id)->delete();
            $hotel->ownerDocuments()->delete();
            $hotel->delete();
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Hotel deleted successfully',
        ]);
    }

    public function changehotelStatus(Request $request)
    {
        $hotel = Hotel::find($request->id);
        if ($hotel) {
            $newStatus = $hotel->status == 1 ? 0 : 1;
            $hotel->update(['status' => $newStatus]);
            $user = User::find($hotel->user_id);
            if ($user) {
                $user->update(['status' => $newStatus]);
            }
            return response()->json(['status' => 'success', 'message' => 'Hotel status updated successfully']);
        }
        return response()->json(['status' => 'error', 'message' => 'Hotel not found'], 404);
    }

    public function viewHotelDetails($id)
    {
        $id = base64_decode($id);
        $hotels = Hotel::with('ownerDocuments.document', 'police_station:id,police_station_name', 'state:id,name', 'city:id,name')->find($id);
        $documents = Document::where('status', 1)->orderBy('name', 'asc')->get();
        $policeStations = PoliceStation::where('status', 1)->get();
        if (!$hotels) {
            abort(404, 'Hotel not found');
        }
        return view('hotel.view-hotel-details', compact('hotels', 'documents', 'policeStations'));
    }

    public function assignPoliceStation(Request $request)
    {
        $hotel = Hotel::find($request->hotel_id);
        if ($hotel) {
            $hotel->update(['police_station_id' => $request->police_station_id]);
            return response()->json(['status' => 'success', 'message' => 'Police station assigned successfully']);
        }
        return response()->json(['status' => 'error', 'message' => 'Hotel not found'], 404);
    }

    public function hotelBookingEntries(Request $request)
    {
        if ($request->ajax()) {
            if (Auth::user()->user_type_id == 3) {
                $policeStation = PoliceStation::where('user_id', Auth::id())->first();

                if ($policeStation) {
                    $data = Hotel::where('police_station_id', $policeStation->id)
                        ->orderBy('id', 'desc')
                        ->get();
                }
            } else {
                $data = Hotel::orderBy('id', 'desc')->get();
            }
            $canAdd = hasPermission('hotels', 'add');
            $canEdit = hasPermission('hotels', 'edit');
            $canDelete = hasPermission('hotels', 'delete');
            return response()->json(['data' => $data, 'canAdd' => $canAdd, 'canEdit' => $canEdit, 'canDelete' => $canDelete]);
        }
        return view('hotel.hotel-booking-entries');
    }
}
