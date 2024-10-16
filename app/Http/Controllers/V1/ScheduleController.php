<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreScheduleRequest;
use App\Http\Requests\V1\UpdateScheduleRequest;
use App\Http\Resources\V1\ScheduleResource;
use App\Models\Schedule;
use App\Models\Venue;
use App\Services\v1\ScheduleQuery;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{    
    public function index(Request $request) {
        // $courtId = $request->query('courtId');
        
        // $dayOfWeek = $request->query('dayOfWeek');
        // $monthInterval = $request->query('monthInterval');

        $filter = new ScheduleQuery();
        $queryItems = $filter->transform($request);

        // $date = $request->query('date');

        $schedules = Schedule::select('*')->where('schedules.status', '<>', 0)->where('schedules.availability', '<>', 0);

        if (count($queryItems) != 0) {
            // $schedules = $schedules->leftJoin('courts', 'courts.venue_id', '=', 'venues.id')->leftJoin('court_types', 'court_types.id', '=', 'courts.court_type_id')->leftJoin('addresses', 'addresses.id', '=', 'venues.address_id');
            $schedules = $schedules->where($queryItems)->get();   

            return $schedules;

        } else {
            return response()->json([
                "status" => false,
                "message" => "Params?"
            ]);
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

    // public function generateSchedules()
    // {
    //     $carbonTodayDate = Carbon::now()->timezone("Asia/Jakarta");
    //     $dateStart = $carbonTodayDate;
    //     $dateEnd = $carbonTodayDate->copy()->addDays(1);
    //     $venues = Venue::all();
    //     // $venues = Venue::where('status', 1); // gak perlu dilakukan karena venue yang tutup-pun masi aman. Meski schedule-nya ter generate, nanti nggak bisa dipesan juga koq ujung2nya...

    //     for ($currentDate = $dateStart->copy(); $currentDate <= $dateEnd; $currentDate->addDay()) {
    //         foreach ($venues as $venue) {
    //             $timeStart = Carbon::parse($venue->open_hour);
    //             $timeEnd = Carbon::parse($venue->close_hour);
    //             for ($currentTime = $timeStart; $currentTime < $timeEnd; $currentTime->addHours($venue->interval)) {
    //                 foreach ($venue->courts as $court) {
    //                     $existingSchedule = Schedule::where('court_id', $court->id)->where('date', $currentDate->format('Y-m-d'))->where('time_start', $currentTime->format('H:i:s'))->first();
    //                     // dd($currentTime->addHours($venue->interval)->toTimeString());
    //                     // dd(!isset($existingSchedule));
    //                     if (!isset($existingSchedule)) {
    //                         // dd($existingSchedule);
    //                         $newSchedule = new Schedule();
    //                         $newSchedule->court_id = $court->id;
    //                         $newSchedule->date = $currentDate;
    //                         $newSchedule->time_start = $currentTime->toTimeString();
    //                         $newSchedule->time_finish = $currentTime->copy()->addHours($venue->interval)->toTimeString();
    //                         // $newSchedule->interval = $venue->interval;
    //                         $newSchedule->interval = 1;
    //                         $newSchedule->availability = 1;
    //                         $newSchedule->member_price = $court->member_price;
    //                         $newSchedule->regular_price = $court->regular_price;
    //                         $newSchedule->status = 1;
    //                         $newSchedule->save();
    //                     }
    //                 }
    //                 // $allTime[] = $currentTime->format('H:i:s');
    //             }

    //             // dd($allTime);

    //         }
    //     }


    //     // $timeStart = $request->timeStart;
    //     // $timeEnd = $request->timeEnd;

    //     // $courtId = $request->courtId;
    //     // $interval = $request->interval;
    //     // $price = $request->price;


    // }

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
