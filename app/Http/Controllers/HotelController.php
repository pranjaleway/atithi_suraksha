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

        activiyLog('New hotel ' . $hotels->hotel_name . ' registered by ' . ucfirst(Auth::user()->name));

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

        if (Auth::user()->user_type_id == 4) {
            $hotel->status = 0;
            $hotel->save();

            $authUser = Auth::user();
            $authUser->status = 0;
            $authUser->save();
        } else {
            $hotel->status = 1;
            $hotel->save();

            $user = User::find($hotel->user_id);
            $user->status = 1;
            $user->save();
        }

        // === Compare changes ===
        $excludedKeys = ['_token', 'password', 'password_confirmation', 'document'];
        $originalData = $hotel->toArray();
        $inputData = $request->except($excludedKeys);
        $changes = array_diff_assoc($inputData, $originalData);

        if (isset($changes['police_station_id'])) {
            $ps = PoliceStation::find($changes['police_station_id']);
            if ($ps) {
                unset($changes['police_station_id']);
                $changes['police_station_name'] = $ps->police_station_name;
            }
        }


        $hotel->update($inputData);

        // === Document update tracking ===
        $updatedDocumentIds = [];
        if ($request->hasFile('document')) {
            foreach ($request->file('document') as $documentId => $file) {
                $existingDocument = HotelOwnerDoc::where('hotel_id', $hotel->id)
                    ->where('document_id', $documentId)
                    ->first();

                if ($existingDocument) {
                    Storage::disk('public')->delete($existingDocument->document_path);
                    $existingDocument->delete();
                }

                $path = $file->store('hotel_owner_documents', 'public');

                HotelOwnerDoc::create([
                    'hotel_id' => $hotel->id,
                    'document_id' => $documentId,
                    'document_path' => $path,
                ]);

                $updatedDocumentIds[] = $documentId;
            }
        }

        // === Update user linked to hotel ===
        $user = User::find($hotel->user_id);
        if ($user) {
            $user->update([
                'name' => $hotel->owner_name,
                'email' => $hotel->email,
                'phone' => $hotel->contact_number,
            ]);
        }

        // === Generate updated field message ===
        $updatedChanges = implode(', ', array_map(function ($key) use ($changes) {
            $readableKey = ucwords(str_replace('_', ' ', $key));
            return $readableKey . ': ' . (isset($changes[$key]) ? $changes[$key] : 'NULL');
        }, array_filter(array_keys($changes), function ($key) use ($excludedKeys) {
            return !in_array($key, $excludedKeys);
        })));

        // === Fetch document names dynamically ===
        $documentNames = Document::pluck('name', 'id')->toArray();

        if (!empty($updatedDocumentIds)) {
            $documentList = collect($updatedDocumentIds)
                ->map(fn($id) => $documentNames[$id] ?? "Document ID $id")
                ->implode(', ');

            $updatedChanges .= ($updatedChanges ? ', ' : '') . 'Updated Documents: ' . $documentList;
        }

        // === Activity log ===
        activiyLog('Hotel ' . ucfirst($hotel->hotel_name) . ' updated by ' . ucfirst(Auth::user()->name) . '. Updated fields: ' . $updatedChanges);

        return response()->json([
            'status' => 'success',
            'message' => Auth::user()->user_type_id == 4 ? 'Profile updated successfully. Please wait for admin approval' : 'Hotel updated successfully',
            'redirect' => route('hotels')
        ]);
    }




    public function deleteHotel(Request $request)
{
    try {
        $hotel = Hotel::find($request->id);

        if (!$hotel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hotel not found',
            ], 404);
        }

        // Log activity before deletion
        activiyLog('Hotel ' . $hotel->hotel_name . ' deleted by ' . ucfirst(Auth::user()->name));

        // Delete associated user if exists
        if ($hotel->user_id) {
            $user = User::find($hotel->user_id);
            if ($user) {
                $user->delete();
            }
        }

        // Delete associated documents and hotel
        $hotel->ownerDocuments()->delete();
        $hotel->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Hotel deleted successfully',
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Something went wrong while deleting the hotel.',
            'error' => $e->getMessage(), // Remove this in production if needed
        ], 500);
    }
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
            activiyLog('Hotel status ' . $hotel->hotel_name . ' changed to ' . ($newStatus == 1 ? 'Active' : 'Inactive') . ' by ' . ucfirst(Auth::user()->name));
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
            activiyLog('Police station assigned to ' . $hotel->name . ' by ' . ucfirst(Auth::user()->name));
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
