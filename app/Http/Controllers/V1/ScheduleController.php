<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\BulkStoreScheduleRequest;
use App\Http\Requests\V1\StoreScheduleRequest;
use App\Http\Requests\V1\UpdateScheduleRequest;
use App\Http\Resources\V1\ScheduleResource;
use App\Models\Schedule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    private function tgl_indo($tanggal){
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
        $courtId = $request->query('courtId');
        $dayOfWeek = $request->query('dayOfWeek');
        $month = $request->query('month');
        $intervalMonth = $request->query('intervalMonth');
        $availability = $request->query('availability');
        if ($dayOfWeek && $month && $courtId) {
            if ($intervalMonth && $intervalMonth > 1) {
                $month2 = $month + $intervalMonth;
                $query = "MONTH(date) BETWEEN $month AND $month2";
            } else {
                $query = "MONTH(date) = $month";
            }
            if ($availability) {
                $schedules = DB::select(DB::raw("SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` WHERE availability = 1 AND status = 1 AND court_id = $courtId AND $query HAVING day_of_week = $dayOfWeek ORDER BY date"));
            } else {
                $schedules = DB::select(DB::raw("SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` WHERE court_id = $courtId AND $query HAVING day_of_week = $dayOfWeek ORDER BY date"));
            }

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
            // return ScheduleResource::collection($schedules);
        } else if ($courtId) {
            $schedules = DB::select(DB::raw("SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` WHERE court_id = $courtId AND availability = 1 AND status = 1 ORDER BY date"));

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
        }

        return response()->json([
            'status' => false,
            'message' => "Day of week, month, and court id parameters required",
            'data' => null,
        ], 422);
    }

    public function store(StoreScheduleRequest $request) {
        return new ScheduleResource(Schedule::create($request->all()));
    }
    
    public function bulkStore(BulkStoreScheduleRequest $request) {
        $bulk = collect($request->all())->map(function($arr, $key) {
            return Arr::except($arr, ['courtId', 'timeStart', 'timeFinish']);
        });

        Schedule::insert($bulk->toArray());
    }
    
    public function update(Schedule $schedule, UpdateScheduleRequest $request) {
        $schedule->update($request->all());
    }

    public function destroy(Schedule $schedule) {
        $schedule->status = "0";
        $schedule->save();
    }

    public function destroyMultiple(Request $request) {
        if(isset($request->id)) {
            foreach($request->id as $id) {
                Schedule::where('id', $id)->update([
                    'status' => "0"
                ]);
            }
        }
    }
}
