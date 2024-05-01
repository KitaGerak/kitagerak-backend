<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\BulkStoreScheduleRequest;
use App\Http\Requests\V1\StoreScheduleRequest;
use App\Http\Requests\V1\UpdateScheduleRequest;
use App\Http\Resources\V1\ScheduleResource;
use App\Models\Court;
use App\Models\CourtImage;
use App\Models\Schedule;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use stdClass;

class ScheduleController extends Controller
{
    private function tgl_indo($tanggal)
    {
        $bulan = array(
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
        return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
    }

    public function getScheduleForCustomer(Request $request) {
        $courtId = $request->query('courtId');
        $startDate = $request->query('startDate');

        $dayOfWeek = $request->query('dayOfWeek');

        $availability = $request->query('availability');
        $venueId = $request->query('venueId');
        $ownerId = $request->query('ownerId');
        $date = $request->query('date');

        if ($dayOfWeek && $courtId) {

            $query = "date >= '$startDate'";
            if ($availability) {
                $schedules = DB::select(DB::raw("SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` WHERE availability = 1 AND status = 1 AND date > NOW() AND court_id = $courtId AND $query HAVING day_of_week = $dayOfWeek ORDER BY date, time_start"));
            } else {
                $schedules = DB::select(DB::raw("SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` WHERE date > NOW() AND court_id = $courtId AND $query HAVING day_of_week = $dayOfWeek ORDER BY date, time_start"));
            }

            if (count($schedules) > 0) {
                $resSchedules = [];
                $tmpSchedule = [];
                foreach ($schedules as $i => $schedule) {
                    $key = $i - 1;
                    if (($key >= 0 && $schedule->date == $schedules[$key]->date) || $i == 0) {
                        array_push($tmpSchedule, new ScheduleResource($schedule));
                    } else {
                        array_push(
                            $resSchedules,
                            [
                                "date" => $this->tgl_indo($schedules[$key]->date),
                                "dayOfWeek" => $schedules[$key]->day_of_week,
                                "schedule" => $tmpSchedule
                            ]
                        );

                        $tmpSchedule = [];
                        array_push($tmpSchedule, new ScheduleResource($schedule));
                    }
                }

                array_push(
                    $resSchedules,
                    [
                        "date" => $this->tgl_indo($schedules[count($schedules) - 1]->date),
                        "dayOfWeek" => $schedules[count($schedules) - 1]->day_of_week,
                        "schedule" => $tmpSchedule
                    ]
                );
                return response()->json([
                    "data" => $resSchedules,
                ]);
            }
            return response()->json([
                "data" => []
            ]);
        } else if ($courtId) {
            // $schedules = DB::select(DB::raw("SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` WHERE court_id = $courtId AND availability = 1 AND status = 1 ORDER BY date"));

            $sql = "SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` WHERE court_id = ? AND date > NOW() AND availability = 1 AND status = 1 ORDER BY date, time_start";
            $schedules = DB::select($sql, [$courtId]);

            if (count($schedules) > 0) {
                $resSchedules = [];
                $tmpSchedule = [];
                foreach ($schedules as $i=>$schedule) {
                    $key = $i-1;
                    if (($key >= 0 && $schedule->date == $schedules[$key]->date) || $i == 0) {
                        array_push($tmpSchedule, new ScheduleResource($schedule));
                    } else {
                        array_push(
                            $resSchedules, 
                            [
                                "date" => $this->tgl_indo($schedules[$key]->date),
                                "dayOfWeek" => $schedules[$key]->day_of_week,
                                "schedule" => $tmpSchedule
                            ]
                        );

                        $tmpSchedule = [];
                        array_push($tmpSchedule, new ScheduleResource($schedule));
                    }
                }

                array_push(
                    $resSchedules, 
                    [
                        "date" => $this->tgl_indo($schedules[count($schedules)-1]->date),
                        "dayOfWeek" => $schedules[count($schedules)-1]->day_of_week,
                        "schedule" => $tmpSchedule
                    ]
                );
                return response()->json([
                    "data" => $resSchedules,
                ]);
            }
            return response()->json([
                "data" => []
            ]);
        } else if ($venueId) {
            $courts = Court::where('venue_id', $venueId)->get();
            foreach ($courts as $court) {
                $courtId = $court->id;
                $sql = "SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` right join `transactions` on schedules.id = transactions.schedule_id WHERE court_id = ? AND date > NOW() AND availability = 1 AND status = 1 ORDER BY date, time_start";
                $schedules = DB::select($sql, [$courtId]);

                // $schedules = DB::select(DB::raw("SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` WHERE court_id = $courtId AND availability = 1 AND status = 1 ORDER BY date, time_start"));
                if (count($schedules) > 0) {
                    $resSchedules = [];
                    $tmpSchedule = [];
                    foreach ($schedules as $i => $schedule) {
                        $key = $i - 1;
                        if (($key >= 0 && $schedule->date == $schedules[$key]->date) || $i == 0) {
                            $scheduleData = new ScheduleResource($schedule);
                            array_push($tmpSchedule, $scheduleData);
                        } else {
                            array_push(
                                $resSchedules,
                                [
                                    "date" => $this->tgl_indo($schedules[$key]->date),
                                    "dayOfWeek" => $schedules[$key]->day_of_week,
                                    "schedule" => $tmpSchedule
                                ]
                            );

                            $tmpSchedule = [];
                            array_push($tmpSchedule, new ScheduleResource($schedule));
                        }
                    }

                    array_push(
                        $resSchedules,
                        [
                            "date" => $this->tgl_indo($schedules[count($schedules) - 1]->date),
                            "dayOfWeek" => $schedules[count($schedules) - 1]->day_of_week,
                            "schedule" => $tmpSchedule
                        ]
                    );
                }
            }

            if (isset($resSchedules)) {
                return response()->json([
                    "data" => $resSchedules,
                ]);
            }

            return response()->json([
                "data" => []
            ]);
        } else if ($ownerId) {
            $venues = Venue::where('owner_id', $ownerId)->get();
            $resSchedules = [];
            $tmpSchedule = [];
            foreach ($venues as $venue) {
                $venueId = $venue->id;
                $courts = Court::where('venue_id', $venueId)->get();
                foreach ($courts as $court) {
                    
                    $courtId = $court->id;
                    if($date)
                    {
                    $date = strval($date);
                    $sql = "SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` right join `transactions` on schedules.id = transactions.schedule_id WHERE court_id = ? AND date = '$date' AND availability = 1 AND status = 1";
                    $schedules = DB::select($sql, [$courtId]);
                    }
                    else
                    {
                    $sql = "SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` right join `transactions` on schedules.id = transactions.schedule_id WHERE court_id = ? AND availability = 1 AND status = 1 ORDER BY date";
                        $schedules = DB::select($sql, [$courtId]);
                    }
                    $schedules = DB::select($sql, [$courtId]);
                    // $schedules = DB::select(DB::raw("SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` WHERE court_id = $courtId AND availability = 1 AND status = 1 ORDER BY date"));
                    if (count($schedules) > 0) {
                       
                        foreach ($schedules as $i => $schedule) {
                            $key = $i - 1;
                            if (($key >= 0 && $schedule->date == $schedules[$key]->date) || $i == 0) {
                                $scheduleData = new ScheduleResource($schedule);
                                array_push($tmpSchedule, $scheduleData);

                            } else {
                                array_push(
                                    $resSchedules,
                                    [
                                        "date" => $this->tgl_indo($schedules[$key]->date),
                                        "dayOfWeek" => $schedules[$key]->day_of_week,
                                        "schedule" => $tmpSchedule
                                    ]
                                );
    
                                $tmpSchedule = [];
                                array_push($tmpSchedule, new ScheduleResource($schedule));
                            }
                        }
    
                        array_push(
                            $resSchedules,
                            [
                                "date" => $this->tgl_indo($schedules[count($schedules) - 1]->date),
                                "dayOfWeek" => $schedules[count($schedules) - 1]->day_of_week,
                                "schedule" => $tmpSchedule
                            ]
                        );
                    }
                }
            }

            if (isset($resSchedules)) {
                return response()->json([
                    "data" => $resSchedules,
                ]);
            }

            return response()->json([
                "data" => []
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => "Invalid Parameters",
            'data' => null,
        ], 422);
    }

    public function index(Request $request)
    {
        $courtId = $request->query('courtId');
        $startDate = $request->query('startDate');

        $dayOfWeek = $request->query('dayOfWeek');

        // $month = $request->query('month');
        // $intervalMonth = $request->query('intervalMonth');

        $availability = $request->query('availability');
        $venueId = $request->query('venueId');
        $ownerId = $request->query('ownerId');
        $date = $request->query('date');
        // if ($dayOfWeek && $month && $courtId) {
        if ($dayOfWeek && $courtId) {
            // if ($intervalMonth && $intervalMonth > 1) {
            //     $month2 = $month + $intervalMonth;
            //     $query = "MONTH(date) BETWEEN $month AND $month2";
            // } else {
            //     $query = "MONTH(date) = $month";
            // }
            $query = "date >= '$startDate'";
            if ($availability) {
                $schedules = DB::select(DB::raw("SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` WHERE availability = 1 AND status = 1 AND date > NOW() AND court_id = $courtId AND $query HAVING day_of_week = $dayOfWeek ORDER BY date, time_start"));
            } else {
                $schedules = DB::select(DB::raw("SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` WHERE date > NOW() AND court_id = $courtId AND $query HAVING day_of_week = $dayOfWeek ORDER BY date, time_start"));
            }

            if (count($schedules) > 0) {
                $resSchedules = [];
                $tmpSchedule = [];
                foreach ($schedules as $i => $schedule) {
                    $key = $i - 1;
                    if (($key >= 0 && $schedule->date == $schedules[$key]->date) || $i == 0) {
                        array_push($tmpSchedule, new ScheduleResource($schedule));
                    } else {
                        array_push(
                            $resSchedules,
                            [
                                "date" => $this->tgl_indo($schedules[$key]->date),
                                "dayOfWeek" => $schedules[$key]->day_of_week,
                                "schedule" => $tmpSchedule
                            ]
                        );

                        $tmpSchedule = [];
                        array_push($tmpSchedule, new ScheduleResource($schedule));
                    }
                }

                array_push(
                    $resSchedules,
                    [
                        "date" => $this->tgl_indo($schedules[count($schedules) - 1]->date),
                        "dayOfWeek" => $schedules[count($schedules) - 1]->day_of_week,
                        "schedule" => $tmpSchedule
                    ]
                );
                return response()->json([
                    "data" => $resSchedules,
                ]);
            }
            return response()->json([
                "data" => []
            ]);
        } else if ($courtId) {
            $sql = "SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` WHERE court_id = ? AND date > NOW() AND availability = 1 AND status = 1 ORDER BY date, time_start";
            $schedules = DB::select($sql, [$courtId]);
            // $schedules = DB::select(DB::raw("SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` WHERE court_id = $courtId AND availability = 1 AND status = 1 ORDER BY date, time_start"));
            if (count($schedules) > 0) {
                $resSchedules = [];
                $tmpSchedule = [];
                foreach ($schedules as $i => $schedule) {
                    $key = $i - 1;
                    if (($key >= 0 && $schedule->date == $schedules[$key]->date) || $i == 0) {
                        $scheduleData = new stdClass;
                        $scheduleData->id = $schedule->id;
                        $scheduleData->date = $schedule->date;
                        $scheduleData->timeStart = $schedule->time_start;
                        $scheduleData->timeFinish = $schedule->time_finish;
                        $scheduleData->interval = $schedule->interval;
                        $scheduleData->availability = $schedule->availability;
                        $scheduleData->price = $schedule->price;
                        $scheduleData->status = $schedule->status;
                        $scheduleData->courtId = $schedule->court_id;
                        $scheduleData->status = $schedule->status;


                        $court = Court::find($schedule->court_id);
                        $scheduleData->courtName = $court->name;

                        $courtImage = CourtImage::where('court_id', $court->id)->first();
                        if(isset($courtImage))
                            $scheduleData->courtImage = $courtImage->url;

                        array_push($tmpSchedule, $scheduleData);
                    } else {
                        array_push(
                            $resSchedules,
                            [
                                "date" => $this->tgl_indo($schedules[$key]->date),
                                "dayOfWeek" => $schedules[$key]->day_of_week,
                                "schedule" => $tmpSchedule
                            ]
                        );

                        $tmpSchedule = [];

                        $scheduleData = new stdClass;
                        $scheduleData->id = $schedule->id;
                        $scheduleData->date = $schedule->date;
                        $scheduleData->timeStart = $schedule->time_start;
                        $scheduleData->timeFinish = $schedule->time_finish;
                        $scheduleData->interval = $schedule->interval;
                        $scheduleData->availability = $schedule->availability;
                        $scheduleData->price = $schedule->price;
                        $scheduleData->status = $schedule->status;
                        $scheduleData->courtId = $schedule->court_id;
                        $scheduleData->status = $schedule->status;


                        $court = Court::find($schedule->court_id);
                        $scheduleData->courtName = $court->name;

                        array_push($tmpSchedule, $scheduleData);
                    }
                }

                array_push(
                    $resSchedules,
                    [
                        "date" => $this->tgl_indo($schedules[count($schedules) - 1]->date),
                        "dayOfWeek" => $schedules[count($schedules) - 1]->day_of_week,
                        "schedule" => $tmpSchedule
                    ]
                );
                return response()->json([
                    "data" => $resSchedules,
                ]);
            }
            return response()->json([
                "data" => []
            ]);
        } else if ($venueId) {
            $courts = Court::where('venue_id', $venueId)->get();
            foreach ($courts as $court) {
                $courtId = $court->id;
                $sql = "SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` right join `transactions` on schedules.id = transactions.schedule_id WHERE court_id = ? AND date > NOW() AND availability = 1 AND status = 1 ORDER BY date, time_start";
                $schedules = DB::select($sql, [$courtId]);

                // $schedules = DB::select(DB::raw("SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` WHERE court_id = $courtId AND availability = 1 AND status = 1 ORDER BY date, time_start"));
                if (count($schedules) > 0) {
                    $resSchedules = [];
                    $tmpSchedule = [];
                    foreach ($schedules as $i => $schedule) {
                        $key = $i - 1;
                        if (($key >= 0 && $schedule->date == $schedules[$key]->date) || $i == 0) {
                            // $scheduleData = new ScheduleResource($schedule);

                            $scheduleData = new stdClass;
                            $scheduleData->id = $schedule->id;
                            $scheduleData->date = $schedule->date;
                            $scheduleData->timeStart = $schedule->time_start;
                            $scheduleData->timeFinish = $schedule->time_finish;
                            $scheduleData->interval = $schedule->interval;
                            $scheduleData->availability = $schedule->availability;
                            $scheduleData->price = $schedule->price;
                            $scheduleData->status = $schedule->status;
                            $scheduleData->courtId = $schedule->court_id;
                            $scheduleData->status = $schedule->status;


                            $transaction = Transaction::where('schedule_id', $schedule->schedule_id)->first();
                            $user = User::find($transaction->user_id);
                            $scheduleData->name = $user->name;

                            $court = Court::find($schedule->court_id);
                            $scheduleData->courtName = $court->name;

                            array_push($tmpSchedule, $scheduleData);
                        } else {
                            array_push(
                                $resSchedules,
                                [
                                    "date" => $this->tgl_indo($schedules[$key]->date),
                                    "dayOfWeek" => $schedules[$key]->day_of_week,
                                    "schedule" => $tmpSchedule
                                ]
                            );

                            $tmpSchedule = [];

                            $scheduleData = new stdClass;
                            $scheduleData->id = $schedule->id;
                            $scheduleData->date = $schedule->date;
                            $scheduleData->timeStart = $schedule->time_start;
                            $scheduleData->timeFinish = $schedule->time_finish;
                            $scheduleData->interval = $schedule->interval;
                            $scheduleData->availability = $schedule->availability;
                            $scheduleData->price = $schedule->price;
                            $scheduleData->status = $schedule->status;
                            $scheduleData->courtId = $schedule->court_id;
                            $scheduleData->status = $schedule->status;

                            $transaction = Transaction::where('schedule_id', $schedule->schedule_id)->first();
                                $user = User::find($transaction->user_id);
                                $scheduleData->name = $user->name;

                                $court = Court::find($schedule->court_id);
                                $scheduleData->courtName = $court->name;

                                array_push($tmpSchedule, $scheduleData);
                                // array_push($tmpSchedule, new ScheduleResource($schedule));
                        }
                    }

                    array_push(
                        $resSchedules,
                        [
                            "date" => $this->tgl_indo($schedules[count($schedules) - 1]->date),
                            "dayOfWeek" => $schedules[count($schedules) - 1]->day_of_week,
                            "schedule" => $tmpSchedule
                        ]
                    );
                }
            }

            if (isset($resSchedules)) {
                return response()->json([
                    "data" => $resSchedules,
                ]);
            }

            return response()->json([
                "data" => []
            ]);
        } else if ($ownerId) {
            $venues = Venue::where('owner_id', $ownerId)->get();
            $resSchedules = [];
            $tmpSchedule = [];
            foreach ($venues as $venue) {
                $venueId = $venue->id;
                $courts = Court::where('venue_id', $venueId)->get();
                foreach ($courts as $court) {
                    
                    $courtId = $court->id;
                    if($date)
                    {
                    $date = strval($date);
                    $sql = "SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` right join `transactions` on schedules.id = transactions.schedule_id WHERE court_id = ? AND date = '$date' AND availability = 1 AND status = 1";
                    $schedules = DB::select($sql, [$courtId]);
                    }
                    else
                    {
                    $sql = "SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` right join `transactions` on schedules.id = transactions.schedule_id WHERE court_id = ? AND availability = 1 AND status = 1 ORDER BY date";
                        $schedules = DB::select($sql, [$courtId]);
                    }
                    $schedules = DB::select($sql, [$courtId]);
                    // $schedules = DB::select(DB::raw("SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` WHERE court_id = $courtId AND availability = 1 AND status = 1 ORDER BY date"));
                    if (count($schedules) > 0) {
                       
                        foreach ($schedules as $i => $schedule) {
                            $key = $i - 1;
                            if (($key >= 0 && $schedule->date == $schedules[$key]->date) || $i == 0) {
                                // $scheduleData = new ScheduleResource($schedule);
    
                                $scheduleData = new stdClass;
                                $scheduleData->id = $schedule->id;
                                $scheduleData->date = $schedule->date;
                                $scheduleData->timeStart = $schedule->time_start;
                                $scheduleData->timeFinish = $schedule->time_finish;
                                $scheduleData->interval = $schedule->interval;
                                $scheduleData->availability = $schedule->availability;
                                $scheduleData->price = $schedule->price;
                                $scheduleData->status = $schedule->status;
                                $scheduleData->courtId = $schedule->court_id;
                                $scheduleData->status = $schedule->status;
    
    
                                $transaction = Transaction::where('schedule_id', $schedule->schedule_id)->first();
                                $user = User::find($transaction->user_id);
                                $scheduleData->name = $user->name;
    
                                $court = Court::find($schedule->court_id);
                                $scheduleData->courtName = $court->name;
    
                                array_push($tmpSchedule, $scheduleData);
                            } else {
                                array_push(
                                    $resSchedules,
                                    [
                                        "date" => $this->tgl_indo($schedules[$key]->date),
                                        "dayOfWeek" => $schedules[$key]->day_of_week,
                                        "schedule" => $tmpSchedule
                                    ]
                                );
    
                                $tmpSchedule = [];
    
                                $scheduleData = new stdClass;
                                $scheduleData->id = $schedule->id;
                                $scheduleData->date = $schedule->date;
                                $scheduleData->timeStart = $schedule->time_start;
                                $scheduleData->timeFinish = $schedule->time_finish;
                                $scheduleData->interval = $schedule->interval;
                                $scheduleData->availability = $schedule->availability;
                                $scheduleData->price = $schedule->price;
                                $scheduleData->status = $schedule->status;
                                $scheduleData->courtId = $schedule->court_id;
                                $scheduleData->status = $schedule->status;
    
                                $transaction = Transaction::where('schedule_id', $schedule->schedule_id)->first();
                                    $user = User::find($transaction->user_id);
                                    $scheduleData->name = $user->name;
    
                                    $court = Court::find($schedule->court_id);
                                    $scheduleData->courtName = $court->name;
    
                                    array_push($tmpSchedule, $scheduleData);
                                    // array_push($tmpSchedule, new ScheduleResource($schedule));
                            }
                        }
    
                        array_push(
                            $resSchedules,
                            [
                                "date" => $this->tgl_indo($schedules[count($schedules) - 1]->date),
                                "dayOfWeek" => $schedules[count($schedules) - 1]->day_of_week,
                                "schedule" => $tmpSchedule
                            ]
                        );
                    }
                }
            }

            if (isset($resSchedules)) {
                return response()->json([
                    "data" => $resSchedules,
                ]);
            }

            return response()->json([
                "data" => []
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => "Day of week, month, and court id parameters required",
            'data' => null,
        ], 422);
    }

    public function store(StoreScheduleRequest $request)
    {
        return new ScheduleResource(Schedule::create($request->all()));
    }

    public function bulkStore(BulkStoreScheduleRequest $request)
    {
        $bulk = collect($request->all())->map(function ($arr, $key) {
            return Arr::except($arr, ['courtId', 'timeStart', 'timeFinish']);
        });

        Schedule::insert($bulk->toArray());
    }

    public function bulkStoreByDateTime(Request $request)
    {
        $dateStart = $request->dateStart;
        $dateEnd = $request->dateEnd;
        $timeStart = $request->timeStart;
        $timeEnd = $request->timeEnd;

        $courtId = $request->courtId;
        $interval = $request->interval;
        $price = $request->price;
    }

    public function generateSchedule()
    {
        $carbonTodayDate = Carbon::now()->timezone("Asia/Jakarta");
        $dateStart = $carbonTodayDate;
        $dateEnd = $carbonTodayDate->copy()->addDays(1);
        $venues = Venue::all();

        for ($currentDate = $dateStart->copy(); $currentDate <= $dateEnd; $currentDate->addDay()) {
            foreach ($venues as $venue) {
                $timeStart = Carbon::parse($venue->open_hour);
                $timeEnd = Carbon::parse($venue->close_hour);
                $allTime = array();
                for ($currentTime = $timeStart; $currentTime < $timeEnd; $currentTime->addHours($venue->interval)) {
                    foreach ($venue->courts as $court) {
                        $existingSchedule = Schedule::where('court_id', $court->id)->where('date', $currentDate->format('Y-m-d'))->where('time_start', $currentTime->format('H:i:s'))->first();
                        // dd($currentTime->addHours($venue->interval)->toTimeString());
                        // dd(!isset($existingSchedule));
                        if (!isset($existingSchedule)) {
                            // dd($existingSchedule);
                            $newSchedule = new Schedule();
                            $newSchedule->court_id = $court->id;
                            $newSchedule->date = $currentDate;
                            $newSchedule->time_start = $currentTime->toTimeString();
                            $newSchedule->time_finish = $currentTime->copy()->addHours($venue->interval)->toTimeString();
                            $newSchedule->interval = $venue->interval;
                            $newSchedule->availability = 1;
                            $newSchedule->price = $court->price;
                            $newSchedule->status = 1;
                            $newSchedule->save();
                        }
                    }
                    // $allTime[] = $currentTime->format('H:i:s');
                }

                // dd($allTime);

            }
        }


        // $timeStart = $request->timeStart;
        // $timeEnd = $request->timeEnd;

        // $courtId = $request->courtId;
        // $interval = $request->interval;
        // $price = $request->price;


    }

    public function update(Schedule $schedule, UpdateScheduleRequest $request)
    {
        $schedule->update($request->all());
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->status = "0";
        $schedule->save();

        return response()->json([
            "message" => ["Berhasil hapus jadwal!"]
        ]);
    }

    public function destroyMultiple(Request $request)
    {
        if (isset($request->id)) {
            foreach ($request->id as $id) {
                Schedule::where('id', $id)->update([
                    'status' => "0"
                ]);
            }
        }
    }
}
