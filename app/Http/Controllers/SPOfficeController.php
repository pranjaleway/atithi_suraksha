<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\SpOffice;
use App\Models\State;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SPOfficeController extends Controller
{
    public function spOffices(Request $request)
    {
        if (!hasPermission('sp-offices', 'view')) {
            abort(403, 'Unauthorized');
        }
        if ($request->ajax()) {
            $data = SpOffice::orderBy('id', 'desc')->get();
            $canAdd = hasPermission('sp-offices', 'add');
            $canEdit = hasPermission('sp-offices', 'edit');
            $canDelete = hasPermission('sp-offices', 'delete');
            return response()->json(['data' => $data, 'canAdd' => $canAdd, 'canEdit' => $canEdit, 'canDelete' => $canDelete]);
        }
        return view('spOffice.sp-office');
    }

    public function addSPOffice()
    {
        $states = State::where('status', 1)->orderBy('name', 'asc')->get();
        $cities = City::where('status', 1)->orderBy('name', 'asc')->get();
        return view('spOffice.add-edit-sp-office', compact('states', 'cities', ));
    }

    public function storeSpOffice(Request $request)
    {
        $request->validate([
            'office_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'contact_number' => 'required|numeric|digits:10|unique:users,phone',
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

        $spOffice = SpOffice::create($request->only([
            'office_name',
            'email',
            'contact_number',
            'address',
            'state_id',
            'city_id',
            'pincode'
        ]));

        $user = $spOffice->user()->create([
            'name' => $request->office_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type_id' => 2,
            'role' => UserType::where('id', 2)->value('user_type'),
            'phone' => $request->contact_number,
        ]);

        $spOffice->update(['user_id' => $user->id]);

        return response()->json([
            'status' => 'success',
            'message' => 'SP Office created successfully',
            'redirect' => route('sp-offices')
        ]);
    }


    public function editSPOffice($id)
    {
        $id = base64_decode($id);

        $spOffices = SpOffice::find($id);

        $states = State::where('status', 1)->orderBy('name', 'asc')->get();
        $cities = City::where('status', 1)
            ->where('state_id', $spOffices->state_id)
            ->orderBy('name', 'asc')->get();

        if (!$spOffices) {
            abort(404, 'SP Office not found');
        }
        return view('spOffice.add-edit-sp-office', compact('spOffices', 'states', 'cities'));
    }

    public function updateSpOffice(Request $request)
    {
        $id = $request->id;
        $spOffice = SpOffice::find($id);


        $validatedData = $request->validate([
            'office_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $spOffice->user_id,
            'contact_number' => 'required|numeric|digits:10|unique:users,phone,' . $spOffice->user_id,
            'address' => 'required|string',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'pincode' => 'required|numeric|digits:6',
        ], [
            'email.unique' => 'This email has already been taken.',
            'contact_number.unique' => 'This contact number has already been taken.',
            'city_id.exists' => 'The selected city is invalid.',
            'state_id.exists' => 'The selected state is invalid.',
        ]);

        $spOffice->update($validatedData);

        $user = User::find($spOffice->user_id);
        if ($user) {
            $user->update([
                'name' => $spOffice->office_name,
                'email' => $spOffice->email,
                'phone' => $spOffice->contact_number,
            ]);
        }


        return response()->json([
            'status' => 'success',
            'message' => 'SP Office updated successfully',
            'redirect' => route('sp-offices'),
        ]);
    }

    public function deleteSpOffice(Request $request)
    {
        $SpOffice = SpOffice::find($request->id);
        if($SpOffice->user_id) {
            User::find($SpOffice->user_id)->delete();
            $SpOffice->delete();
        }
        return response()->json([
            'status' => 'success',
            'message' => 'SP Office deleted successfully',
        ]);
    }
    public function changeSpOfficeStatus(Request $request)
    {
        $spOffice = SpOffice::find($request->id);
        if ($spOffice) {
            $newStatus = $spOffice->status == 1 ? 0 : 1;
            $spOffice->update(['status' => $newStatus]);
            return response()->json(['status' => 'success', 'message' => 'SP Office status updated successfully']);
        }
        return response()->json(['status' => 'error', 'message' => 'Menu not found'], 404);
    }

}
