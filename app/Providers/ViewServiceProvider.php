<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\SpOffice;
use App\Models\PoliceStation;
use App\Models\Hotel;


class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('layouts.navbar', function ($view) {

            if (!Auth::check()) {
                $view->with('notifications', []);
                return;
            }

            $user = Auth::user();
            $query = Notification::with('user:id,name')->orderBy('id', 'desc');

            switch ($user->user_type_id) {
                case 2: //  SP Office
                    $spOfficeIDs = SpOffice::where('user_id', $user->id)->pluck('id');
                    $policeStationIDs = PoliceStation::whereIn('sp_office_id', $spOfficeIDs)->pluck('id');

                    $policeUserIDs = PoliceStation::whereIn('id', $policeStationIDs)->pluck('user_id');
                    $hotelUserIDs = Hotel::whereIn('police_station_id', $policeStationIDs)->pluck('user_id');

                    $query->where(function ($q) use ($policeUserIDs, $hotelUserIDs) {
                        $q->whereIn('user_id', $policeUserIDs)
                            ->orWhereIn('user_id', $hotelUserIDs);
                    });
                    break;

                case 3: //  Police Station
                    $policeStation = PoliceStation::where('user_id', $user->id)->first();
                    if ($policeStation) {
                        $spUserID = optional(SpOffice::find($policeStation->sp_office_id))->user_id;
                        $hotelUserIDs = Hotel::where('police_station_id', $policeStation->id)->pluck('user_id');

                        $query->where(function ($q) use ($spUserID, $hotelUserIDs) {
                            if ($spUserID) {
                                $q->where('user_id', $spUserID);
                            }
                            if ($hotelUserIDs->isNotEmpty()) {
                                $q->orWhereIn('user_id', $hotelUserIDs);
                            }
                        });
                    }
                    break;

                case 4: //  Hotel
                case 5: //  Hotel Employee
                    $hotel = Hotel::where('user_id', $user->id)->first();
                    if ($hotel) {
                        $policeStation = PoliceStation::find($hotel->police_station_id);
                        $spUserID = optional(SpOffice::find(optional($policeStation)->sp_office_id))->user_id;
                        $policeUserID = optional($policeStation)->user_id;

                        $query->where(function ($q) use ($spUserID, $policeUserID) {
                            if ($spUserID) {
                                $q->where('user_id', $spUserID);
                            }
                            if ($policeUserID) {
                                $q->orWhere('user_id', $policeUserID);
                            }
                        });
                    }
                    break;

                default: // Super Admin & Others
                    // No filter - fetch all notifications
                    break;
            }

            $count = (clone $query)->count();

            $notifications = $query->take(5)->get();

            $view->with([
                'notifications' => $notifications,
                'count' => $count,
            ]);
        });
    }
}
