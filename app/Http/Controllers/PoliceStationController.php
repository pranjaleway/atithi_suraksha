<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\PoliceStation;
use App\Models\SpOffice;
use App\Models\State;
use App\Models\User;
use App\Models\UserType;
use App\Notifications\CredentialsNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PoliceStationController extends Controller
{
    public function policeStations(Request $request)
    {
        if (!hasPermission('police-stations', 'view')) {
            abort(403, 'Unauthorized');
        }
        if ($request->ajax()) {
            if (Auth::user()->user_type_id == 2) {
                $spOfficeID = SpOffice::where('user_id', Auth::user()->id)->first()->id;
                $data = PoliceStation::where('sp_office_id', $spOfficeID)->orderBy('id', 'desc')->get();
            } else {
                $data = PoliceStation::orderBy('id', 'desc')->get();
            }
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
        $spOffices = SpOffice::where('status', 1)->get();
        return view('policeStation.add-edit-police-station', compact('states', 'cities', 'spOffices'));
    }

    public function storePoliceStation(Request $request)
    {
        $rules = [
            'police_station_name' => 'required|string|max:255',
            'officer_in_charge' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'contact_number' => 'required|numeric|digits:10|unique:users,phone',
            'address' => 'required|string',
            'pincode' => 'required|numeric|digits:6',
            'password' => 'required|string|min:6|confirmed',
        ];

        if (Auth::user()->user_type_id != 2) {
            $rules['sp_office_id'] = 'required|exists:sp_offices,id';
        }

        $messages = [
            'email.unique' => 'This email has already been taken.',
            'contact_number.unique' => 'This contact number has already been taken.',
            'sp_office_id.required' => 'SP Office is required.',
            'sp_office_id.exists' => 'The selected SP Office is invalid.',
            'password.confirmed' => 'The confirmed password does not match.',
        ];

        $request->validate($rules, $messages);

        // Retrieve SP Office info based on user type
        $spOffice = Auth::user()->user_type_id == 2
            ? SpOffice::where('user_id', Auth::id())->first()
            : SpOffice::find($request->sp_office_id);

        if (!$spOffice) {
            return response()->json([
                'status' => 'error',
                'message' => 'SP Office not found.'
            ], 422);
        }

        $policeStationData = $request->only([
            'police_station_name',
            'officer_in_charge',
            'email',
            'contact_number',
            'address',
            'pincode'
        ]);

        $policeStationData['sp_office_id'] = $spOffice->id;
        $policeStationData['state_id'] = $spOffice->state_id;
        $policeStationData['city_id'] = $spOffice->city_id;

        $policeStation = PoliceStation::create($policeStationData);

        $user = $policeStation->user()->create([
            'name' => $request->police_station_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type_id' => 3,
            'role' => UserType::where('id', 3)->value('user_type'),
            'phone' => $request->contact_number,
        ]);

        $policeStation->update(['user_id' => $user->id]);

        $plainPassword = $request->password;

        $user->notify(new CredentialsNotification($user->name, $user->email, $plainPassword, $user->phone));

        activiyLog('Police Station ' . $policeStation->police_station_name . ' created by ' . ucfirst(Auth::user()->name));

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

        $spOffices = SpOffice::where('status', 1)->get();

        if (!$policeStations) {
            abort(404, 'Police Station not found');
        }
        return view('policeStation.add-edit-police-station', compact('policeStations', 'states', 'cities', 'spOffices'));
    }

    public function updatePoliceStation(Request $request)
    {
        $policeStation = PoliceStation::findOrFail($request->id);

        $rules = [
            'police_station_name' => 'required|string|max:255',
            'officer_in_charge' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $policeStation->user_id,
            'contact_number' => 'required|numeric|digits:10|unique:users,phone,' . $policeStation->user_id,
            'address' => 'required|string',
            'pincode' => 'required|numeric|digits:6',
        ];

        if (Auth::user()->user_type_id != 2) {
            $rules['sp_office_id'] = 'required|exists:sp_offices,id';
        }

        $request->validate($rules, [
            'email.unique' => 'This email has already been taken.',
            'contact_number.unique' => 'This contact number has already been taken.',
            'sp_office_id.exists' => 'The selected SP Office is invalid.',
        ]);

        $inputData = $request->only([
            'police_station_name',
            'officer_in_charge',
            'email',
            'contact_number',
            'address',
            'pincode'
        ]);

        $spOffice = Auth::user()->user_type_id == 2
            ? SpOffice::where('user_id', Auth::id())->first()
            : SpOffice::find($request->sp_office_id);

        if ($spOffice) {
            $inputData['sp_office_id'] = $spOffice->id;
            $inputData['state_id'] = $spOffice->state_id;
            $inputData['city_id'] = $spOffice->city_id;
        }

        $originalData = $policeStation->only(array_keys($inputData));
        $changes = array_diff_assoc($inputData, $originalData);

        if (isset($inputData['sp_office_id']) && $inputData['sp_office_id'] != $policeStation->sp_office_id) {
            $spName = SpOffice::find($inputData['sp_office_id'])?->office_name;
            if ($spName) {
                $changes['sp_office_name'] = $spName;
            }
        }

        $policeStation->update($inputData);

        $user = User::find($policeStation->user_id);
        if ($user) {
            $user->update([
                'name' => $inputData['police_station_name'],
                'email' => $inputData['email'],
                'phone' => $inputData['contact_number'],
            ]);
        }

        $updatedChanges = implode(', ', array_map(function ($key) use ($changes) {
            return ucwords(str_replace('_', ' ', $key)) . ': ' . $changes[$key];
        }, array_keys($changes)));

        activiyLog('Police Station "' . $policeStation->police_station_name . '" updated by ' . ucfirst(Auth::user()->name) . '. Updated fields: ' . $updatedChanges);

        return response()->json([
            'status' => 'success',
            'message' => 'Police Station updated successfully',
            'redirect' => route('police-stations')
        ]);
    }



    public function deletePoliceStation(Request $request)
    {
        $policeStation = PoliceStation::find($request->id);
        if ($policeStation->user_id) {
            User::find($policeStation->user_id)->delete();
            $policeStation->delete();
            activiyLog('Police Station ' . $policeStation->police_station_name . ' deleted by ' . ucfirst(Auth::user()->name));
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
            if ($policeStation->user_id) {
                $user = User::find($policeStation->user_id);
                $user->update(['status' => $newStatus]);
            }
            activiyLog('Police Station ' . $policeStation->police_station_name . ' status changed to ' . ($newStatus == 1 ? 'Active' : 'Inactive') . ' by ' . ucfirst(Auth::user()->name));
            return response()->json(['status' => 'success', 'message' => 'Police Station status updated successfully']);
        }
        return response()->json(['status' => 'error', 'message' => 'Police Station not found'], 404);
    }

    public function resetPassword(Request $request)
    {
        $id = $request->input('id');
        $modal = PoliceStation::find($id);
        $user = User::find($modal->user_id);
        $user->update(['password' => Hash::make($modal->contact_number)]);
        return response()->json(['status' => 'success', 'message' => 'Password reset successfully']);
    }
}
