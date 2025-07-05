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
                case 2: // SP Office
                    $spOfficeIDs = SpOffice::where('user_id', $user->id)->pluck('id');
                    $policeStationIDs = PoliceStation::whereIn('sp_office_id', $spOfficeIDs)->pluck('id');
                    $policeUserIDs = PoliceStation::whereIn('id', $policeStationIDs)->pluck('user_id');
                    $query->whereIn('user_id', $policeUserIDs);
                    break;

                case 3: // Police Station
                    $spOfficeId = optional(PoliceStation::where('user_id', $user->id)->first())->sp_office_id;
                    $spUserID = optional(SpOffice::find($spOfficeId))->user_id;
                    if ($spUserID) {
                        $query->where('user_id', $spUserID);
                    }
                    break;

                case 4: // Hotel
                case 5: // Hotel Employee
                    $policeStationId = optional(Hotel::where('user_id', $user->id)->first())->police_station_id;
                    $spOfficeId = optional(PoliceStation::find($policeStationId))->sp_office_id;
                    $spUserID = optional(SpOffice::find($spOfficeId))->user_id;
                    $policeUserIDs = PoliceStation::where('id', $policeStationId)->pluck('user_id');

                    $query->where(function ($q) use ($spUserID, $policeUserIDs) {
                        $q->where('user_id', $spUserID)
                            ->orWhereIn('user_id', $policeUserIDs);
                    });
                    break;

                default:
                    // Super Admin: No filter
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
