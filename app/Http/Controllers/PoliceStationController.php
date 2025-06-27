<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\PoliceStation;
use App\Models\State;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PoliceStationController extends Controller
{
    public function policeStations(Request $request)
    {
        if(!hasPermission('police-stations', 'view')) {
            abort(403, 'Unauthorized');
        }

        if($request->ajax()) {
            $data = PoliceStation::orderBy('id', 'desc')->get();
            $canAdd = hasPermission('police-stations', 'add');
            $canEdit = hasPermission('police-stations', 'edit');
            $canDelete = hasPermission('police-stations', 'delete');
            return response()->json(['data' => $data, 'canAdd' => $canAdd, 'canEdit' => $canEdit, 'canDelete' => $canDelete]);
        }
        return view('policeStation.police-stations');
    }

    public function addPoliceStation()
    {
        $states = State::where('status', 1)->orderBy('name', 'asc')->get();
        $cities = City::where('status', 1)->orderBy('name', 'asc')->get();
        return view('policeStation.add-edit-police-station', compact('states', 'cities', ));
    }

    public function storePoliceStation(Request $request)
    {
        $request->validate([
            'police_station_name' => 'required|string|max:255',
            'officer_in_charge' => 'nullable|string|max:255',
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

        $policeStation = PoliceStation::create($request->only([
            'police_station_name',
            'officer_in_charge',
            'email',
            'contact_number',
            'address',
            'state_id',
            'city_id',
            'pincode'
        ]));

        $user = $policeStation->user()->create([
            'name' => $request->police_station_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type_id' => 3,
            'role' => UserType::where('id', 3)->value('user_type'),
            'phone' => $request->contact_number,
        ]);

        $policeStation->update(['user_id' => $user->id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Police Station created successfully',
            'redirect' => route('police-stations')
        ]);
    }
    public function editPoliceStation($id)
    {
        $id = base64_decode($id);

        $policeStations = PoliceStation::find($id);

        $states = State::where('status', 1)->orderBy('name', 'asc')->get();
        $cities = City::where('status', 1)
            ->where('state_id', $policeStations->state_id)
            ->orderBy('name', 'asc')->get();

        if (!$policeStations) {
            abort(404, 'Police Station not found');
        }
        return view('policeStation.add-edit-police-station', compact('policeStations', 'states', 'cities'));
    }

    public function updatePoliceStation(Request $request)
    {
        $policeStation = PoliceStation::find($request->id);
        $request->validate([
            'police_station_name' => 'required|string|max:255',
            'officer_in_charge' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $policeStation->user_id,
            'contact_number' => 'required|numeric|digits:10|unique:users,phone,' . $policeStation->user_id,
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

        $policeStation->update($request->all());

        $user = User::find($policeStation->user_id);
        if ($user) {
            $user->update([
                'name' => $policeStation->police_station_name,
                'email' => $policeStation->email,
                'phone' => $policeStation->contact_number,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Police Station updated successfully',
            'redirect' => route('police-stations')
        ]);
    }

    public function deletePoliceStation(Request $request)
    {
       $policeStation = PoliceStation::find($request->id);
       if($policeStation->user_id) {
           User::find($policeStation->user_id)->delete();
           $policeStation->delete();
       }
       return response()->json([
           'status' => 'success',
           'message' => 'Police Station deleted successfully',
       ]);
    }

    public function changePoliceStationStatus(Request $request)
    {
         $policeStation = PoliceStation::find($request->id);
        if ($policeStation) {
            $newStatus = $policeStation->status == 1 ? 0 : 1;
            $policeStation->update(['status' => $newStatus]);
            if($policeStation->user_id) {
                $user = User::find($policeStation->user_id);
                $user->update(['status' => $newStatus]);
            }
            return response()->json(['status' => 'success', 'message' => 'Police Station status updated successfully']);
        }
        return response()->json(['status' => 'error', 'message' => 'Police Station not found'], 404);
    }
}
