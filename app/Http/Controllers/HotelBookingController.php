<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Hotel;
use App\Models\HotelBooking;
use App\Models\HotelEmployee;
use App\Models\PoliceStation;
use App\Models\State;
use App\Models\TransferEntry;
use App\Models\UploadedEntry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class HotelBookingController extends Controller
{
    public function booking(Request $request, $id = null, $date = null)
    {
        if (!hasPermission('bookings', 'view')) {
            abort(403, 'Unauthorized');
        }
        if ($id && $date) {
            $id = base64_decode($id);
            $date = base64_decode($date);
            $entry = TransferEntry::with(['hotel', 'hotelEmployee'])
                ->where('transfer_date', $date)
                ->where('transfer_type', 'manual')
                ->first();
            $query = HotelBooking::with(['hotel', 'hotelEmployee', 'state', 'city'])->where('parent_id', null)
                ->whereDate('transfer_date', $entry->transfer_date);
        } else {
            $query = HotelBooking::with(['hotel', 'hotelEmployee', 'state', 'city'])
                ->where('parent_id', null);
        }

        if ($request->ajax()) {
            if ((Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2)) {
                $data = $query->where('hotel_id', $id)->get();
            } else if (Auth::user()->user_type_id == 3) {
                $hotelId = Hotel::where('police_station_id', Auth::user()->id)->value('id');
                $data = $query->where('hotel_id', $hotelId)->get();
            } else if (Auth::user()->user_type_id == 4) {
                $hotelId = Hotel::where('user_id', Auth::user()->id)->value('id');
                $data = $query->where('hotel_id', $hotelId)->get();
            } else if (Auth::user()->user_type_id == 5) {
                $employeeID = HotelEmployee::where('user_id', Auth::user()->id)->value('id');
                $data = $query->where('hotel_employee_id', $employeeID)->get();
            } else {
                $data = [];
            }

            $canAdd = hasPermission('bookings', 'add');
            $canEdit = hasPermission('bookings', 'edit');
            $canDelete = hasPermission('bookings', 'delete');
            return response()->json(['data' => $data, 'canAdd' => $canAdd, 'canEdit' => $canEdit, 'canDelete' => $canDelete]);
        }
        return view('hotel.bookings', [
            'hotel_id' => base64_encode($id ?? ''),
            'date' => base64_encode($date ?? '')
        ]);
    }

    public function addBooking()
    {
        $states = State::where('status', 1)->orderBy('name', 'asc')->get();
        $cities = City::where('status', 1)->orderBy('name', 'asc')->get();
        return view('hotel.add-booking', compact('states', 'cities'));
    }


    public function storeBooking(Request $request)
    {
        $request->validate([
            'guests' => 'required|array|min:1',
            'guests.*.guest_name' => 'required|string|max:255',
            'guests.*.check_in' => 'required|date_format:Y-m-d',
            'guests.*.check_out' => 'required|date_format:Y-m-d|after_or_equal:guests.*.check_in',
            'guests.*.room_number' => 'required|string|max:50',
            'guests.*.contact_number' => 'required|numeric|digits:10',
            'guests.*.aadhar_number' => 'required|numeric|digits:12',
            'guests.*.email' => 'nullable|email|max:255',
            'guests.*.address' => 'required|string|max:500',
            'guests.*.state_id' => 'required|integer|exists:states,id',
            'guests.*.city_id' => 'required|integer|exists:cities,id',
            'guests.*.pincode' => 'required|numeric|digits:6',
            'guests.*.id_proof_path' => 'nullable|file|mimes:jpeg,jpg,png,pdf',
        ], [
            'guests.*.check_out.after_or_equal' => 'The check out date must be after or equal to check in date.',
            'guests.*.guest_name.required' => 'Please enter guest name.',
            'guests.*.check_in.required' => 'Please enter check in date.',
            'guests.*.check_out.required' => 'Please enter check out date.',
            'guests.*.room_number.required' => 'Please enter room number.',
            'guests.*.contact_number.required' => 'Please enter contact number.',
            'guests.*.contact_number.numeric' => 'Please enter valid contact number.',
            'guests.*.contact_number.digits' => 'The contact number must be 10 digits.',
            'guests.*.aadhar_number.required' => 'Please enter aadhar number.',
            'guests.*.aadhar_number.numeric' => 'Please enter valid aadhar number.',
            'guests.*.aadhar_number.digits' => 'The aadhar number must be 12 digits.',
            'guests.*.email.email' => 'Please enter valid email.',
            'guests.*.address.required' => 'Please enter address.',
            'guests.*.state_id.required' => 'Please select state.',
            'guests.*.city_id.required' => 'Please select city.',
            'guests.*.pincode.required' => 'Please enter pincode.',
            'guests.*.pincode.numeric' => 'Please enter valid pincode.',
            'guests.*.pincode.digits' => 'The pincode must be 6 digits.',
            'guests.*.id_proof_path.mimes' => 'Please upload valid file.',
        ]);

        $guests = $request->file('guests') ?: $request->input('guests'); // mix file+data

        $user = Auth::user();
        $hotelId = null;
        $hotel_employee_id = null;

        if ($user->user_type_id == 4) {
            $hotelId = Hotel::where('user_id', $user->id)->value('id');
        } elseif ($user->user_type_id == 5) {
            $employee = HotelEmployee::where('user_id', $user->id)->first();
            if ($employee) {
                $hotelId = $employee->hotel_id;
                $hotel_employee_id = $employee->id;
            }
        }

        if (empty($guests) || !is_array($guests)) {
            return response()->json(['error' => 'Invalid data'], 422);
        }

        $savedIds = [];

        foreach ($request->guests as $index => $guestData) {
            $filePath = null;

            // Handle file if uploaded
            if ($request->hasFile("guests.$index.id_proof_path")) {
                $file = $request->file("guests.$index.id_proof_path");
                $filePath = $file->store('booking/id_proofs', 'public');
            }

            $booking = HotelBooking::create([
                'hotel_id' => $hotelId,
                'hotel_employee_id' => $hotel_employee_id,
                'guest_name' => $guestData['guest_name'],
                'check_in' => $guestData['check_in'],
                'check_out' => $guestData['check_out'],
                'room_number' => $guestData['room_number'],
                'contact_number' => $guestData['contact_number'],
                'aadhar_number' => $guestData['aadhar_number'],
                'email' => $guestData['email'] ?? null,
                'address' => $guestData['address'],
                'state_id' => $guestData['state_id'],
                'city_id' => $guestData['city_id'],
                'pincode' => $guestData['pincode'],
                'id_proof_path' => $filePath,
                'parent_id' => $index === 0 ? null : $savedIds[0] ?? null,
            ]);

            $savedIds[] = $booking->id;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Hotel booking added successfully',
            'redirect' => route('bookings'),
        ]);
    }

    public function getMembers(Request $request, $id)
    {
        if (!hasPermission('bookings', 'view')) {
            abort(403, 'Unauthorized');
        }
        $id = base64_decode($id);
        if ($request->ajax()) {
            $data = HotelBooking::where('parent_id', $id)->get();
            $canAdd = hasPermission('bookings', 'add');
            $canEdit = hasPermission('bookings', 'edit');
            $canDelete = hasPermission('bookings', 'delete');
            return response()->json(['data' => $data, 'canAdd' => $canAdd, 'canEdit' => $canEdit, 'canDelete' => $canDelete]);
        }
        return view('hotel.members', compact('id'));
    }

    public function deleteBooking(Request $request)
    {
        $booking = HotelBooking::find($request->id);
        if ($booking) {

            $members = HotelBooking::where('parent_id', $booking->id)->get();
            foreach ($members as $member) {
                $member->delete();
            }
            $booking->delete();
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Hotel Booking deleted successfully',
        ]);
    }

    public function editBooking($id)
    {
        $id = base64_decode($id);
        $booking = HotelBooking::find($id);
        $states = State::where('status', 1)->orderBy('name', 'asc')->get();
        $cities = City::where('state_id', $booking->state_id)->where('status', 1)->orderBy('name', 'asc')->get();
        return view('hotel.edit-booking', compact('booking', 'states', 'cities'));
    }

    public function addMember($id)
    {
        $id = base64_decode($id);
        $booking = HotelBooking::find($id);
        $states = State::where('status', 1)->orderBy('name', 'asc')->get();
        $cities = City::where('state_id', $booking->state_id)->where('status', 1)->orderBy('name', 'asc')->get();
        return view('hotel.add-member', compact('booking', 'states', 'cities'));
    }

    public function storeMember(Request $request)
    {
        $request->validate([
            'guest_name' => 'required',
            'check_in' => 'required|date',
            'check_out' => 'required|date',
            'room_number' => 'required',
            'contact_number' => 'required|numeric|digits:10',
            'aadhar_number' => 'required|numeric|digits:12',
            'email' => 'nullable|email',
            'address' => 'required',
            'state_id' => 'required',
            'city_id' => 'required',
            'pincode' => 'required|numeric|digits:6',
            'id_proof_path' => 'required|file|mimes:jpeg,png,jpg,pdf',
        ]);

        $guestData = $request->all();

        // Handle file if uploaded
        $file = $request->file('id_proof_path');
        $filePath = $file->store('booking/id_proofs', 'public');

        $user = Auth::user();
        $hotelId = null;
        $hotel_employee_id = null;

        if ($user->user_type_id == 4) {
            $hotelId = Hotel::where('user_id', $user->id)->value('id');
        } elseif ($user->user_type_id == 5) {
            $employee = HotelEmployee::where('user_id', $user->id)->first();
            if ($employee) {
                $hotelId = $employee->hotel_id;
                $hotel_employee_id = $employee->id;
            }
        }

        $booking = HotelBooking::create([
            'hotel_id' => $hotelId,
            'hotel_employee_id' => $hotel_employee_id,
            'guest_name' => $guestData['guest_name'],
            'check_in' => $guestData['check_in'],
            'check_out' => $guestData['check_out'],
            'room_number' => $guestData['room_number'],
            'contact_number' => $guestData['contact_number'],
            'aadhar_number' => $guestData['aadhar_number'],
            'email' => $guestData['email'],
            'address' => $guestData['address'],
            'state_id' => $guestData['state_id'],
            'city_id' => $guestData['city_id'],
            'pincode' => $guestData['pincode'],
            'id_proof_path' => $filePath,
            'parent_id' => $guestData['parent_id'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Member added successfully',
            'redirect' => route('members', base64_encode($guestData['parent_id'])),
        ]);
    }

    public function deleteMember(Request $request)
    {
        $member = HotelBooking::find($request->id);
        if ($member) {
            $member->delete();
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Member deleted successfully',
        ]);
    }

    public function viewDetails($id)
    {
        $id = base64_decode($id);
        $booking = HotelBooking::with(['hotel', 'state', 'city'])->find($id);
        return view('hotel.view-booking-details', compact('booking'));
    }


    //Uploaded Entries
    public function uploadedEntries(Request $request, $id = null, $date = null)
    {
        if (!hasPermission('uploaded-entries', 'view')) {
            abort(403, 'Unauthorized');
        }

        if ($request->ajax()) {
            if ($id && $date) {
                $id = base64_decode($id);
                $date = base64_decode($date);

                $entry = TransferEntry::with(['hotel', 'hotelEmployee'])
                    ->where('transfer_date', $date)
                    ->where('transfer_type', 'uploaded')
                    ->first();

                if (!$entry) {
                    return response()->json(['error' => 'Transfer entry not found'], 404);
                }

                $query = UploadedEntry::with(['hotel', 'hotelEmployee'])
                    ->where('transfer_date', $entry->transfer_date);
            } else {
                $query = UploadedEntry::with(['hotel', 'hotelEmployee']);
            }

            if ($id && $date && (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2 || Auth::user()->user_type_id == 3)) {
                $data = $query->where('hotel_id', $id)->get();
            } else {
                $data = $query->get();
            }

            return response()->json([
                'data' => $data,
                'canAdd' => hasPermission('uploaded-entries', 'add'),
                'canEdit' => hasPermission('uploaded-entries', 'edit'),
                'canDelete' => hasPermission('uploaded-entries', 'delete'),
            ]);
        }

        return view('hotel.uploaded-entries', [
            'hotel_id' => $id,
            'date' => $date
        ]);
    }


    public function storeUploadedEntry(Request $request)
    {
        $request->validate([
            'file_path' => 'required|array',
            'file_path.*' => 'file|mimes:jpeg,png,jpg,pdf',
        ], [
            'file_path.required' => 'Please select at least one file.',
            'file_path.*.file' => 'Please select a valid file.',
            'file_path.*.mimes' => 'Please select a file with one of the following extensions: jpeg, png, jpg, pdf.',
        ]);

        $user = Auth::user();
        $hotelId = null;
        $hotel_employee_id = null;

        if ($user->user_type_id == 4) {
            $hotelId = Hotel::where('user_id', $user->id)->value('id');
        } elseif ($user->user_type_id == 5) {
            $employee = HotelEmployee::where('user_id', $user->id)->first();
            if ($employee) {
                $hotelId = $employee->hotel_id;
                $hotel_employee_id = $employee->id;
            }
        }
        foreach ($request->file('file_path') as $file) {
            $filePath = $file->store('uploaded_entries', 'public');
            UploadedEntry::create([
                'hotel_id' => $hotelId,
                'hotel_employee_id' => $hotel_employee_id,
                'file_path' => $filePath,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'File uploaded successfully',
        ]);
    }

    public function deleteUploadedEntry(Request $request)
    {
        $entry = UploadedEntry::find($request->id);
        if ($entry) {
            $entry->delete();
        }
        return response()->json([
            'status' => 'success',
            'message' => 'File deleted successfully',
        ]);
    }

    //Transfer Entries
    public function TransferEntries(Request $request)
{
    if (!hasPermission('transfer-entries', 'view')) {
        abort(403, 'Unauthorized');
    }

    if ($request->ajax()) {
        $query = TransferEntry::with(['hotel', 'hotelEmployee']);

        if (Auth::user()->user_type_id == 3) {
            $policeStation = PoliceStation::where('user_id', Auth::id())->first();
            $hotelIDs = Hotel::where('police_station_id', $policeStation->id)->pluck('id');
            $query->whereIn('hotel_id', $hotelIDs);
        } else if (Auth::user()->user_type_id == 4) {
            $hotelIDs = Hotel::where('user_id', Auth::id())->pluck('id');
            $query->whereIn('hotel_id', $hotelIDs);
        } else if (Auth::user()->user_type_id == 5) {
            $employeeID = HotelEmployee::where('user_id', Auth::id())->value('id');
            $query->where('hotel_employee_id', $employeeID);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $from = Carbon::createFromFormat('Y-m-d', $request->from_date)->startOfDay();
            $to = Carbon::createFromFormat('Y-m-d', $request->to_date)->endOfDay();
            $query->whereBetween('transfer_date', [$from, $to]);
        }
          if ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->hotel_id);
        }

        $entries = $query->get();

        // Group by hotel and transfer_date
        $grouped = $entries->groupBy(function ($item) {
            return $item->hotel_id . '|' . $item->transfer_date;
        });

        $result = $grouped->map(function ($items, $key) {
            $first = $items->first();
            [$hotelId, $transferDate] = explode('|', $key);
            $types = $items->pluck('transfer_type')->unique()->values();

            return [
                'id' => $first->id,
                'hotel_id' => $hotelId,
                'transfer_date' => $transferDate,
                'hotel' => $first->hotel,
                'hotelEmployee' => $first->hotelEmployee,
                'transfer_types' => $types,
            ];
        })->values();

        return response()->json([
            'data' => $result,
            'canAdd' => hasPermission('transfer-entries', 'add'),
            'canEdit' => hasPermission('transfer-entries', 'edit'),
            'canDelete' => hasPermission('transfer-entries', 'delete'),
        ]);
    }

    if(Auth::user()->user_type_id == 3){
        $policeStation = PoliceStation::where('user_id', Auth::id())->first();
        $hotels = Hotel::where('police_station_id', $policeStation->id)->get();
    } else {
        $hotels = Hotel::all();
    }

    return view('hotel.transfer-entries', compact('hotels'));
}



    public function addTranserManualEntries()
    {
        $bookings = HotelBooking::where('status', 0)->get();
        return view('hotel.add-manual-transfer-entries', compact('bookings'));
    }
    public function storeManualTransferEntries(Request $request)
    {
        $validated = $request->validate([
            'booking_ids' => 'required|array',
            'booking_ids.*' => 'integer|exists:hotel_bookings,id',
        ]);

        $user = Auth::user();
        $hotelId = null;
        $hotel_employee_id = null;

        if ($user->user_type_id == 4) {
            $hotelId = Hotel::where('user_id', $user->id)->value('id');
        } elseif ($user->user_type_id == 5) {
            $employee = HotelEmployee::where('user_id', $user->id)->first();
            if ($employee) {
                $hotelId = $employee->hotel_id;
                $hotel_employee_id = $employee->id;
            }
        }
        foreach ($validated['booking_ids'] as $id) {
            $booking = HotelBooking::find($id);
            if ($booking) {
                $booking->status = 1;
                $booking->transfer_date = now();
                $booking->save();
            }
        }
        $transferEntries = TransferEntry::create([
            'hotel_id' => $hotelId,
            'hotel_employee_id' => $hotel_employee_id,
            'transfer_date' => now(),
            'transfer_type' => 'manual',
        ]);

        // Return JSON response
        return response()->json([
            'status' => 'success',
            'message' => 'Transfer entries saved successfully.',
            'redirect' => route('transfer-entries'),
        ]);
    }

    public function addTranserUploadedEntries()
    {
        $uploads = UploadedEntry::where('status', 0)->get();
        return view('hotel.add-uploaded-transfer-entries', compact('uploads'));
    }

    public function storeUploadedTransferEntries(Request $request)
    {
        $validated = $request->validate([
            'upload_ids' => 'required|array',
            'upload_ids.*' => 'integer|exists:uploaded_entries,id',
        ]);

        $user = Auth::user();
        $hotelId = null;
        $hotel_employee_id = null;

        if ($user->user_type_id == 4) {
            $hotelId = Hotel::where('user_id', $user->id)->value('id');
        } elseif ($user->user_type_id == 5) {
            $employee = HotelEmployee::where('user_id', $user->id)->first();
            if ($employee) {
                $hotelId = $employee->hotel_id;
                $hotel_employee_id = $employee->id;
            }
        }
        foreach ($validated['upload_ids'] as $id) {
            $uploaded = UploadedEntry::find($id);
            if ($uploaded) {
                $uploaded->status = 1;
                $uploaded->transfer_date = now();
                $uploaded->save();
            }
        }
        $transferEntries = TransferEntry::create([
            'hotel_id' => $hotelId,
            'hotel_employee_id' => $hotel_employee_id,
            'transfer_date' => now(),
            'transfer_type' => 'uploaded',
        ]);

        // Return JSON response
        return response()->json([
            'status' => 'success',
            'message' => 'Transfer entries saved successfully.',
            'redirect' => route('transfer-entries'),
        ]);
    }

}