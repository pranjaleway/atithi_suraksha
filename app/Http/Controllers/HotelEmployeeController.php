<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Document;
use App\Models\Hotel;
use App\Models\HotelEmployee;
use App\Models\HotelEmployeeDoc;
use App\Models\State;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class HotelEmployeeController extends Controller
{
   public function hotelEmployees(Request $request, $id = null)
{
    if (!hasPermission('hotel-employees', 'view')) {
        abort(403, 'Unauthorized');
    }


    // Decode only if $id is provided
    $decodedId = $id ? base64_decode($id) : null;

    // Fallback to logged-in user's ID if $id is not passed
    if ($decodedId === null) {
        $hotelId = Hotel::where('user_id', $request->user()->id)->value('id');
        $decodedId = $hotelId;
    }



    if ($request->ajax()) {
        $hotelEmployee = HotelEmployee::with('user')->where('hotel_id', $decodedId)->get();

        return response()->json([
            'data' => $hotelEmployee,
            'canAdd' => hasPermission('hotel-employees', 'add'),
            'canEdit' => hasPermission('hotel-employees', 'edit'),
            'canDelete' => hasPermission('hotel-employees', 'delete')
        ]);
    }

    $isSuperAdmin = Auth::user()->user_type_id == 1;

    return view('hotel.hotel-employees', [
        'id' => $decodedId,
        'isSuperAdmin' => $isSuperAdmin
    ]);
}

 public function addHotelEmployee()
    {
        $states = State::where('status', 1)->orderBy('name', 'asc')->get();
        $cities = City::where('status', 1)->orderBy('name', 'asc')->get();
        $documents = Document::where('status', 1)->orderBy('name', 'asc')->get();
        return view('hotel.add-edit-hotel-employee', compact('states', 'cities', 'documents'));
    }

    public function storeHotelEmployee(Request $request)
    {
        $request->validate([
            'employee_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'contact_number' => 'required|numeric|digits:10|unique:users,phone',
            'aadhar_number' => 'required|numeric|digits:12|unique:hotel_employees,aadhar_number',
            'pan_number' => 'required|string|max:10|unique:hotel_employees,pan_number',
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
        ]);
        $hotelId = Hotel::where('user_id', $request->user()->id)->value('id');
        $request->merge(['hotel_id' => $hotelId]);

        $employee = HotelEmployee::create($request->only([
            'employee_name',
            'email',
            'contact_number',
            'aadhar_number',
            'pan_number',
            'address',
            'state_id',
            'city_id',
            'pincode',
            'hotel_id'
        ]));

        if ($request->hasFile('document')) {
            foreach ($request->file('document') as $documentId => $file) {
                $path = $file->store('hotel_employee_documents', 'public'); // stores in storage/app/public/hotel_documents

                HotelEmployeeDoc::create([
                    'hotel_employee_id' => $employee->id,
                    'document_id' => $documentId,
                    'document_path' => $path,
                ]);
            }
        }

        $user = $employee->user()->create([
            'name' => $request->employee_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type_id' => 5,
            'role' => UserType::where('id', 5)->value('user_type'),
            'phone' => $request->contact_number,
        ]);

        $employee->update(['user_id' => $user->id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Hotel employee created successfully',
            'redirect' => route('hotel-employees')
        ]);
    }

     public function editHotelEmployee($id)
    {
        $id = base64_decode($id);
        $employee = HotelEmployee::with('user', 'employeeDocuments.document')->find($id);
        $states = State::where('status', 1)->orderBy('name', 'asc')->get();
        $cities = City::where('status', 1)->orderBy('name', 'asc')->get();
        $documents = Document::where('status', 1)->orderBy('name', 'asc')->get();
        return view('hotel.add-edit-hotel-employee', compact('states', 'cities', 'documents','employee'));
    }

    public function updateHotelEmployee(Request $request)
    {
        $employee = HotelEmployee::find($request->id);
        $request->validate([
            'employee_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->user_id,
            'contact_number' => 'required|numeric|digits:10|unique:users,phone,' . $employee->user_id,
            'aadhar_number' => 'required|numeric|digits:12|unique:hotel_employees,aadhar_number,' . $request->id,
            'pan_number' => 'required|string|max:10|unique:hotel_employees,pan_number,' . $request->id,
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
        ]);



        $employee->update($request->all());

        if ($request->hasFile('document')) {
            foreach ($request->file('document') as $documentId => $file) {
                $existingDocument = HotelEmployeeDoc::where('hotel_employee_id', $employee->id)->where('document_id', $documentId)->first();

                if ($existingDocument) {
                    Storage::disk('public')->delete($existingDocument->document_path);
                    $existingDocument->delete();
                }
                $path = $file->store('hotel_employee_documents', 'public'); // stores in storage/app/public/hotel_documents

                HotelEmployeeDoc::create([
                    'hotel_employee_id' => $employee->id,
                    'document_id' => $documentId,
                    'document_path' => $path,
                ]);
            }
        }

        $user = User::find($employee->user_id);
        if ($user) {
            $user->update([
                'name' => $employee->employee_name,
                'email' => $employee->email,
                'phone' => $employee->contact_number,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Hotel employee updated successfully',
            'redirect' => route('hotel-employees')
        ]);
    }

    public function deleteHotelEmployee(Request $request)
    {
        $employee = HotelEmployee::find($request->id);
        if ($employee->user_id) {
            User::find($employee->user_id)->delete();
            $employee->employeeDocuments()->delete();
            $employee->delete();
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Hotel employee deleted successfully',
        ]);
    }

    public function changeHotelEmployeeStatus(Request $request)
    {
        $employee = HotelEmployee::find($request->id);
        if ($employee) {
            $newStatus = $employee->status == 1 ? 0 : 1;
            $employee->update(['status' => $newStatus]);
            if($employee->user_id) {
                $user = User::find($employee->user_id);
                $user->update(['status' => $newStatus]);
            }
            return response()->json(['status' => 'success', 'message' => 'Hotel employee status updated successfully']);
        }
        return response()->json(['status' => 'error', 'message' => 'Hotel not found'], 404);
    }

    public function viewHotelEmployeeDetails($id)
    {
        $id = base64_decode($id);
        $employee = HotelEmployee::with( 'employeeDocuments.document', 'state:id,name', 'city:id,name')->find($id);
        $documents = Document::where('status', 1)->orderBy('name', 'asc')->get();
        if (!$employee) {
            abort(404, 'Hotel Employee not found');
        }
        return view('hotel.view-hotel-employee-details', compact('employee', 'documents'));
    }


}
