<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreScheduleRequest;
use App\Http\Requests\V1\UpdateScheduleRequest;
use App\Http\Resources\V1\ScheduleResource;
use App\Models\Schedule;
use App\Models\Venue;
use App\Services\V1\ScheduleQuery;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{  
    private  function tgl_indo($tanggal){
        $bulan = array (
            1 =>   'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        );
        $pecahkan = explode('-', $tanggal);
        
        // variabel pecahkan 0 = tanggal
        // variabel pecahkan 1 = bulan
        // variabel pecahkan 2 = tahun
        
        return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
    }

    public function index(Request $request) {
        // $courtId = $request->query('courtId');
        
        // $dayOfWeek = $request->query('dayOfWeek');
        // $monthInterval = $request->query('monthInterval');

        $filter = new ScheduleQuery();
        $queryItems = $filter->transform($request);

        // $date = $request->query('date');

        $schedules = Schedule::selectRaw('schedules.*, DAYOFWEEK(schedules.date) AS day_of_week');
        
        if ($request->query('dayOfWeek') != null && $request->query('dayOfWeek')['eq'] != null) {
            $schedules = $schedules->whereRaw("DAYOFWEEK(date) = " . $request->query('dayOfWeek')['eq']);
        }

        if (count($queryItems) != 0) {
            $schedules = $schedules->where($queryItems)->where('schedules.date', '>=', DB::Raw("NOW()"))->orderBy('schedules.date', 'asc')->orderBy('schedules.time_start', 'asc');

            $schedulesRes = [];

            if ($request->query('dayOfWeek') != null && $request->query('dayOfWeek')['eq'] != null) {
                //Untuk member

                if ($request->query('date') != null && $request->query('date')['lte'] != null && $request->query('date')['gte'] != null) {
                    //Untuk member
                    $dayOfWeek = $request->query('dayOfWeek')['eq'];
                    $startDate = $request->query('date')['gte'];
                    $endDate = $request->query('date')['lte'];
                    $courtId = $request->query('courtId')['eq'];
                    
                    $availScheduleTime = [];
                    $schedules = $schedules->get();

                    foreach ($schedules as $i=>$schedule) {
                        if (!in_array($schedule->time_start, $availScheduleTime) && $schedule->status == 1 && $schedule->availability == 1) {
                            array_push($availScheduleTime, $schedule->time_start);
                        }
                    }

                    if (count($availScheduleTime) <= 0) {
                        return response()->json([
                            "status" => false,
                            "message" => "Data tidak ditemukan"
                        ], 500);
                    }

                    $in = "";
                    foreach ($availScheduleTime as $key=>$val) {
                        if ($in == "") {
                            $in .= "'$val'";
                        } else {
                            $in .= ", '$val'";
                        }
                    }
                    
                    
                    $results = DB::select("SELECT time_start AS `timeStart`, time_finish AS `timeFinish`, `interval`, MIN(availability) AS availability, MIN(status) AS `status`, MAX(member_price) AS `memberPrice`, MIN(member_discount) as `memberDiscount`, COUNT(date) AS count FROM `schedules` WHERE time_start IN ($in) AND (date BETWEEN ? AND ?) AND DAYOFWEEK(date) = ? AND court_id = ? GROUP BY `timeStart`, `timeFinish`, `interval` ORDER BY `timeStart`", [$startDate, $endDate, $dayOfWeek, $courtId]);
                    $results = json_decode(json_encode($results), true);

                    for($i = 0; $i < count($results); $i++) {
                        $countAvail = DB::Select("SELECT count(*) AS count FROM `schedules` WHERE court_id = ? AND time_start = ? AND date IN (SELECT date FROM `schedules` WHERE DAYOFWEEK(date) = ?) AND availability = 1 AND status = 1", [$courtId, $results[$i]['timeStart'], $dayOfWeek]);
                        $results[$i] += [
                            "countAvail" => $countAvail[0]->count
                        ];
                    }

                    if (count($results) > 0) {
                        return response()->json([
                            "data" => 
                            $results
                        ]);
                    } else {
                        return response()->json([
                            "status" => false,
                            "message" => "Data tidak ditemukan"
                        ], 500);    
                    }

                } else {
                    return response()->json([
                        "status" => false,
                        "message" => "Invalid Parameter(s)"
                    ], 500);
                }
            } else {
                //Limit date supaya tidak melebihi 7 hari dari sekarang
                $schedules = $schedules->where('schedules.date', '<=', DB::Raw("adddate(now(), 7)"))->where([['schedules.status', '<>', 0], ['schedules.availability', '<>', 0]])->get();
                $co = -1;
                foreach ($schedules as $i=>$schedule) {
                    if ($i > 0 && $schedule->date == $schedules[$i-1]->date) {
                        array_push($schedulesRes[$co]["details"], new ScheduleResource($schedule));
                    } else {
                        $co++;
                        array_push($schedulesRes, [
                            "date" => $this->tgl_indo($schedule->date),
                            "dayOfWeek" => $schedule->day_of_week,
                            "details" => [
                                new ScheduleResource($schedule),
                            ]
                        ]);
                    }
                }
            }

            return response()->json(["data" => $schedulesRes]);

        } else {
            return response()->json([
                "status" => false,
                "message" => "Params?"
            ], 500);
        }
    }

    public function generateSchedules() {
        // $date = "2024-10-13";
        // $dayofweek = date('w', strtotime($date));
        // return $dayofweek;

        // Declare a date
        $date = now();
        $datess = [];
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime($date. ' + 1 day'));
            array_push($datess, $date);
        }

        $venues = Venue::all();
        $res = "";
        $timeStart = null;
        
        // date("Y-m-d", time() + 86400);

        foreach ($venues as $venue) {
            foreach($venue->courts as $court) {
                $timeStart = null;
                $timeFinish = null;
                foreach($venue->openDays as $openDay) {
                    if ($timeStart == null)
                        $timeStart = $openDay->time_open;

                    do {
                        $timestamp = strtotime($timeStart) + 60*60;
                        $timeFinish = date('H:i', $timestamp);

                        foreach($datess as $date) {
                            if (date('w', strtotime($date)) + 1 == $openDay->day_of_week) {
                                for ($i = 0; $i < 4; $i++) {
                                    if (Schedule::where('court_id', $court->id)->where('date', $date)->where('time_start', $timeStart)->where('time_finish', $timeFinish)->where('status', 1)->count() <= 0) {
                                        Schedule::create([
                                            "court_id" => $court->id,
                                            "date" => $date,
                                            "time_start" => $timeStart,
                                            "time_finish" => $timeFinish,
                                            "interval" => 1,
                                            "availability" => 1,
                                            "regular_price" => $court->regular_price,
                                            "member_price" => $court->member_price,
                                            "status" => 1,
                                        ]);
                                    }
                                    $date = date('Y-m-d', strtotime($date. ' + 7 day'));
                                }
                                break;
                            }
                        }
    
                        $timeStart = $timeFinish;
                    }
                    while (strtotime($timeFinish) < strtotime($openDay->time_close));

                    $timeStart = null;
                    $timeFinish = null;

                    // $res .= " | " . $openDay->day_of_week . "->" . $openDay->time_open . "-" . $openDay->time_close;
                }
            }
        }
        // return $res;
    }

    public function store(StoreScheduleRequest $request)
    {
        return new ScheduleResource(Schedule::create($request->all()));
    }

    public function update(UpdateScheduleRequest $request, Schedule $schedule) {
        $schedule->update($request->all());
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->status = 0;
        $schedule->save();
    }
}
