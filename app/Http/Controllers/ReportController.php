<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Hotel;
use App\Models\PoliceStation;
use App\Models\SpOffice;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{

    public function getHotelsByPoliceStation(Request $request)
    {
        $data = Hotel::where('police_station_id', $request->police_station_id)->get();
        return response()->json(['data' => $data]);
    }
    public function report()
    {
        if (Auth::user()->user_type_id == 1) {
            $state = State::where('name', 'Madhya Pradesh')->get();
            $cities = City::where('state_id', $state->first()->id)->get();
            return view('report.super-admin-report', compact('cities'));
        } else if (Auth::user()->user_type_id == 2) {
            $spOffice = SpOffice::where('user_id', Auth::user()->id)->first();
            $policeStations = PoliceStation::where('sp_office_id', $spOffice->id)->get();
            return view('report.sp-office-report', compact('policeStations'));
        } else if (Auth::user()->user_type_id == 3) {
            $policeStation = PoliceStation::where('user_id', Auth::user()->id)->first();
            $hotels = Hotel::where('police_station_id', $policeStation->id)->get();
            return view('report.police-station-report', compact('hotels'));
        } else if (Auth::user()->user_type_id == 4) {
            return view('report.hotel-report');
        } else {
            abort(403, 'Unauthorized');
        }
    }

    public function getSuperAdminReport(Request $request)
    {
        $reportType = $request->input('reportType');
        $cityId = $request->input('city_id');
        $policeStationId = $request->input('police_station_id');
        $hotelId = $request->input('hotel_id');
        $dateRange = $request->input('date_range');

        $query = DB::table('sp_offices as spo')
            ->join('cities as c', 'c.id', '=', 'spo.city_id')
            ->join('police_stations as ps', 'ps.sp_office_id', '=', 'spo.id')
            ->leftJoin('hotels as h', 'h.police_station_id', '=', 'ps.id')
            ->leftJoin('transfer_entries as t', 't.hotel_id', '=', 'h.id')
            ->whereNull('spo.deleted_at')
            ->whereNull('c.deleted_at')
            ->whereNull('ps.deleted_at')
            ->where(function ($q) {
                $q->whereNull('h.deleted_at')->orWhereNull('h.id');
            });


        if ($cityId && $cityId !== 'All') {
            $spOfficeIds = SpOffice::where('city_id', $cityId)->pluck('id');

            if ($spOfficeIds->count() > 0) {
                $query->whereIn('ps.sp_office_id', $spOfficeIds);
            } else {
                return response()->json([]);
            }
        } else {
            $query->where('spo.status', 1);
        }

        if ($policeStationId && $policeStationId !== 'All') {
            $query->where('ps.id', $policeStationId);
        }

        if ($hotelId && $hotelId !== 'All') {
            $query->where('h.id', $hotelId);
        }

        if (!empty($dateRange)) {
            $dateRange = str_replace(' to ', ' - ', $dateRange);
            $dates = explode(' - ', $dateRange);

            if (count($dates) == 2) {
                $startDate = trim($dates[0]);
                $endDate = trim($dates[1]);

                $query->whereBetween(DB::raw('DATE(t.transfer_date)'), [$startDate, $endDate]);
            }
        }

        if ($reportType === "list") {
            $result = $query->select(
                'c.id as city_id',
                'c.name as city_name',
                'spo.id as sp_office_id',
                'spo.office_name as office_name',
                'ps.id as police_station_id',
                'ps.police_station_name',
                'h.id as hotel_id',
                'h.hotel_name',
                'h.owner_name',
                DB::raw("COUNT(DISTINCT CASE WHEN t.transfer_date IS NOT NULL THEN CONCAT(t.hotel_id, '-', DATE(t.transfer_date)) END) as total_transferred_bookings"),
                DB::raw("COUNT(CASE WHEN DATE(t.transfer_date) = CURDATE() THEN 1 END) as today_transferred_bookings")
            )
                ->groupBy(
                    'c.id',
                    'c.name',
                    'spo.id',
                    'spo.office_name',
                    'ps.id',
                    'ps.police_station_name',
                    'h.id',
                    'h.hotel_name',
                    'h.owner_name'
                )
                ->get();

            return response()->json($result);
        }

        if ($reportType === "count") {
            if ($hotelId && $hotelId !== 'All') {
                // Hotel level summary
                $result = $query->select(
                    'h.id as hotel_id',
                    'h.hotel_name',
                    DB::raw("COUNT(DISTINCT CASE WHEN t.transfer_date IS NOT NULL THEN CONCAT(t.hotel_id, '-', DATE(t.transfer_date)) END) as total_transferred_bookings"),
                    DB::raw("COUNT(CASE WHEN DATE(t.transfer_date) = CURDATE() THEN 1 END) as today_transferred_bookings")
                )
                    ->groupBy('h.id', 'h.hotel_name')
                    ->first();

                return response()->json($result);

            } elseif ($policeStationId && $policeStationId !== 'All') {
                // Police station level summary
                $result = $query->select(
                    'ps.id as police_station_id',
                    'ps.police_station_name',
                    DB::raw("COUNT(DISTINCT h.id) as hotel_count"),
                    DB::raw("COUNT(DISTINCT CASE WHEN t.transfer_date IS NOT NULL THEN CONCAT(t.hotel_id, '-', DATE(t.transfer_date)) END) as total_transferred_bookings"),
                    DB::raw("COUNT(CASE WHEN DATE(t.transfer_date) = CURDATE() THEN 1 END) as today_transferred_bookings")
                )
                    ->groupBy('ps.id', 'ps.police_station_name')
                    ->first();

                // dd($result);

                return response()->json($result);

            } elseif ($cityId && $cityId !== 'All') {
                // Single city summary
                $result = $query->select(
                    'c.id as city_id',
                    'c.name as city_name',
                    DB::raw("COUNT(DISTINCT ps.id) as police_station_count"),
                    DB::raw("COUNT(DISTINCT h.id) as hotel_count"),
                    DB::raw("COUNT(DISTINCT CASE WHEN t.transfer_date IS NOT NULL THEN CONCAT(t.hotel_id, '-', DATE(t.transfer_date)) END) as total_transferred_bookings"),
                    DB::raw("COUNT(CASE WHEN DATE(t.transfer_date) = CURDATE() THEN 1 END) as today_transferred_bookings")
                )
                    ->groupBy('c.id', 'c.name')
                    ->first();

                return response()->json($result);

            } else {
                // All cities summary
                $result = $query->select(
                    'c.id as city_id',
                    'c.name as city_name',
                    DB::raw("COUNT(DISTINCT ps.id) as police_station_count"),
                    DB::raw("COUNT(DISTINCT h.id) as hotel_count"),
                    DB::raw("COUNT(DISTINCT CASE WHEN t.transfer_date IS NOT NULL THEN CONCAT(t.hotel_id, '-', DATE(t.transfer_date)) END) as total_transferred_bookings"),
                    DB::raw("COUNT(CASE WHEN DATE(t.transfer_date) = CURDATE() THEN 1 END) as today_transferred_bookings")
                )
                    ->groupBy('c.id', 'c.name')
                    ->get();

                return response()->json($result);
            }
        }


        return response()->json(['message' => 'Invalid report type'], 400);
    }




    public function getSpOfficeReport(Request $request)
    {
        $reportType = $request->input('reportType');
        $policeStationId = $request->input('police_station_id');
        $hotelId = $request->input('hotel_id');
        $dateRange = $request->input('date_range');

        $spOffice = SpOffice::where('user_id', Auth::id())->first();
        if (!$spOffice) {
            return response()->json([
                'message' => 'No SP Office found for this user'
            ], 404);
        }

        $query = DB::table('police_stations as ps')
            ->leftJoin('hotels as h', 'h.police_station_id', '=', 'ps.id')
            ->leftJoin('transfer_entries as t', function ($join) {
                $join->on('t.hotel_id', '=', 'h.id')
                    ->whereNull('t.deleted_at'); // exclude deleted transfers
            })
            ->where('ps.sp_office_id', $spOffice->id)
            ->whereNull('ps.deleted_at')
            ->whereNull('h.deleted_at');

        if ($policeStationId && $policeStationId !== 'All') {
            $query->where('ps.id', $policeStationId);
        }

        if ($hotelId && $hotelId !== 'All') {
            $query->where('h.id', $hotelId);
        }

        if (!empty($dateRange)) {
            $dateRange = str_replace(' to ', ' - ', $dateRange);
            $dates = explode(' - ', $dateRange);

            if (count($dates) === 2) {
                $startDate = trim($dates[0]);
                $endDate = trim($dates[1]);

                $query->whereBetween(DB::raw('DATE(t.transfer_date)'), [$startDate, $endDate]);
            }
        }

        if ($reportType === "list") {
            $result = $query->select(
                'ps.id as police_station_id',
                'ps.police_station_name as police_station_name',
                'h.id as hotel_id',
                'h.hotel_name as hotel_name',
                'h.owner_name as owner_name',
                DB::raw("COUNT(DISTINCT CASE WHEN t.transfer_date IS NOT NULL THEN CONCAT(t.hotel_id, '-', DATE(t.transfer_date)) END) as total_transferred_bookings"),
                DB::raw("COUNT(CASE WHEN DATE(t.transfer_date) = CURDATE() THEN 1 END) as today_transferred_bookings")
            )
                ->groupBy('ps.id', 'ps.police_station_name', 'h.id', 'h.hotel_name', 'h.owner_name')
                ->get();

            return response()->json($result);
        }

        if ($reportType === "count") {
            $select = [
                DB::raw("COUNT(DISTINCT CASE WHEN t.transfer_date IS NOT NULL THEN CONCAT(t.hotel_id, '-', DATE(t.transfer_date)) END) as total_transferred_bookings"),
                DB::raw("COUNT(CASE WHEN DATE(t.transfer_date) = CURDATE() THEN 1 END) as today_transferred_bookings")
            ];

            if (!$policeStationId || $policeStationId === 'All') {
                $select[] = DB::raw("COUNT(DISTINCT ps.id) as police_station_count");
            }
            if (!$hotelId || $hotelId === 'All') {
                $select[] = DB::raw("COUNT(DISTINCT h.id) as hotel_count");
            }

            $result = $query->select($select)->first();

            return response()->json($result);
        }

        return response()->json(['message' => 'Invalid report type'], 400);
    }



    public function getPoliceStationReport(Request $request)
    {
        $reportType = $request->input('reportType');
        $hotelId = $request->input('hotel_id');
        $dateRange = $request->input('date_range');

        $policeStation = PoliceStation::where('user_id', Auth::id())->first();
        if (!$policeStation) {
            return response()->json([
                'message' => 'No Police Station found for this user'
            ], 404);
        }

        $query = DB::table('hotels as h')
            ->leftJoin('transfer_entries as t', function ($join) {
                $join->on('t.hotel_id', '=', 'h.id')
                    ->whereNull('t.deleted_at');
            })
            ->where('h.police_station_id', $policeStation->id)
            ->whereNull('h.deleted_at');

        if ($hotelId && $hotelId !== 'All') {
            $query->where('h.id', $hotelId);
        }

        if (!empty($dateRange)) {
            $dateRange = str_replace(' to ', ' - ', $dateRange);
            $dates = explode(' - ', $dateRange);

            if (count($dates) === 2) {
                $startDate = trim($dates[0]);
                $endDate = trim($dates[1]);

                $query->whereBetween(DB::raw('DATE(t.transfer_date)'), [$startDate, $endDate]);
            }
        }

        if ($reportType === "list") {
            $result = $query->select(
                'h.id as hotel_id',
                'h.hotel_name as hotel_name',
                'h.owner_name as owner_name',
                DB::raw("COUNT(DISTINCT CASE WHEN t.transfer_date IS NOT NULL THEN CONCAT(t.hotel_id, '-', DATE(t.transfer_date)) END) as total_transferred_bookings"),
                DB::raw("COUNT(CASE WHEN DATE(t.transfer_date) = CURDATE() THEN 1 END) as today_transferred_bookings")
            )
                ->groupBy('h.id', 'h.hotel_name', 'h.owner_name')
                ->get();

            return response()->json($result);
        }

        if ($reportType === "count") {
            $select = [
                DB::raw("COUNT(DISTINCT CASE WHEN t.transfer_date IS NOT NULL THEN CONCAT(t.hotel_id, '-', DATE(t.transfer_date)) END) as total_transferred_bookings"),
                DB::raw("COUNT(CASE WHEN DATE(t.transfer_date) = CURDATE() THEN 1 END) as today_transferred_bookings")
            ];

            if (!$hotelId || $hotelId === 'All') {
                $select[] = DB::raw("COUNT(DISTINCT h.id) as hotel_count");
            }

            $result = $query->select($select)->first();

            return response()->json($result);
        }

        return response()->json(['message' => 'Invalid report type'], 400);
    }


    public function getHotelReport(Request $request)
    {
        $reportType = $request->input('reportType');
        $transferType = strtolower($request->input('transfer_type') ?? 'all');
        $dateRange = $request->input('date_range');

        $hotel = Hotel::where('user_id', Auth::id())->first();
        if (!$hotel) {
            return response()->json(['message' => 'No Hotel found for this user'], 404);
        }

        // Parse date range if provided
        [$startDate, $endDate] = [null, null];
        if ($dateRange && strpos($dateRange, ' to ') !== false) {
            [$startDate, $endDate] = explode(' to ', $dateRange);
        }

        // -------------------------------
        // LIST REPORT
        // -------------------------------
        if ($reportType === 'list') {
            $results = collect();

            // Manual transfers
            if ($transferType === 'manual' || $transferType === 'all') {
                $manual = DB::table('transfer_entries as t')
                    ->leftJoin('hotel_bookings as b', function ($join) {
                        $join->on('b.hotel_id', '=', 't.hotel_id')
                            ->whereNull('b.parent_id')
                            ->whereRaw('DATE(b.transfer_date) = DATE(t.transfer_date)')
                            ->whereNull('b.deleted_at');
                    })
                    ->where('t.hotel_id', $hotel->id)
                    ->where('t.transfer_type', 'manual')
                    ->whereNull('t.deleted_at')
                    ->when(
                        $startDate && $endDate,
                        fn($q) =>
                        $q->whereBetween(DB::raw('DATE(t.transfer_date)'), [$startDate, $endDate])
                    )
                    ->select(
                        DB::raw('DATE(t.transfer_date) as transfer_date'),
                        't.transfer_type',
                        'b.id as booking_id',
                        'b.guest_name',
                        DB::raw('NULL as uploaded_id'),
                        DB::raw('NULL as file_path')
                    )
                    ->groupBy('b.id', 'b.guest_name', 't.transfer_date', 't.transfer_type')
                    ->orderByDesc('t.transfer_date')
                    ->get();

                $results = $results->merge($manual);
            }

            // Uploaded transfers
            if ($transferType === 'uploaded' || $transferType === 'all') {
                $uploaded = DB::table('transfer_entries as t')
                    ->leftJoin('uploaded_entries as u', function ($join) {
                        $join->on('u.hotel_id', '=', 't.hotel_id')
                            ->whereRaw('DATE(u.transfer_date) = DATE(t.transfer_date)')
                            ->whereNull('u.deleted_at');
                    })
                    ->where('t.hotel_id', $hotel->id)
                    ->where('t.transfer_type', 'uploaded')
                    ->whereNull('t.deleted_at')
                    ->when(
                        $startDate && $endDate,
                        fn($q) =>
                        $q->whereBetween(DB::raw('DATE(t.transfer_date)'), [$startDate, $endDate])
                    )
                    ->select(
                        DB::raw('DATE(t.transfer_date) as transfer_date'),
                        't.transfer_type',
                        DB::raw('NULL as booking_id'),
                        DB::raw('NULL as guest_name'),
                        'u.id as uploaded_id',
                        'u.file_path'
                    )
                    ->groupBy('u.id', 'u.file_path', 't.transfer_date', 't.transfer_type')
                    ->orderByDesc('t.transfer_date')
                    ->get();

                $results = $results->merge($uploaded);
            }

            return response()->json($results->sortByDesc('transfer_date')->values());
        }

        // -------------------------------
        // COUNT REPORT
        // -------------------------------
        if ($reportType === 'count') {
            $query = DB::table('transfer_entries as t')
                ->where('t.hotel_id', $hotel->id)
                ->whereNull('t.deleted_at')
                ->when(
                    $startDate && $endDate,
                    fn($q) =>
                    $q->whereBetween(DB::raw('DATE(t.transfer_date)'), [$startDate, $endDate])
                );

            if ($transferType !== 'all') {
                $query->where('t.transfer_type', $transferType);
            }

            $query->select(
                DB::raw('DATE(t.transfer_date) as transfer_date'),
                't.transfer_type',
                DB::raw('COUNT(*) as total_count')
            )
                ->groupBy('transfer_date', 't.transfer_type')
                ->orderByDesc('transfer_date');

            $results = $query->get();

            // If "all" → format counts by date for both types
            if ($transferType === 'all') {
                $dateSummary = [];
                foreach ($results as $row) {
                    $date = $row->transfer_date;
                    if (!isset($dateSummary[$date])) {
                        $dateSummary[$date] = [
                            'transfer_date' => $date,
                            'manual_count' => 0,
                            'uploaded_count' => 0
                        ];
                    }
                    $row->transfer_type === 'manual'
                        ? $dateSummary[$date]['manual_count'] = $row->total_count
                        : $dateSummary[$date]['uploaded_count'] = $row->total_count;
                }
                $results = collect(array_values($dateSummary));
            }

            return response()->json($results);
        }

        return response()->json(['message' => 'Invalid report type or transfer type'], 400);
    }


}
