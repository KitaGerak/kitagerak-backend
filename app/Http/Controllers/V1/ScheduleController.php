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
    public function index(Request $request) {
        $courtId = $request->query('courtId');
        $dayOfWeek = $request->query('dayOfWeek');
        $month = $request->query('month');
        $availability = $request->query('availability');
        if ($dayOfWeek && $month && $courtId) {
            if ($availability) {
                $schedules = DB::select(DB::raw("SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` WHERE availability = 1 AND status = 1 AND court_id = $courtId AND MONTH(date) = $month HAVING day_of_week = $dayOfWeek"));
            } else {
                $schedules = DB::select(DB::raw("SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` WHERE court_id = $courtId AND MONTH(date) = $month HAVING day_of_week = $dayOfWeek"));
            }
            
            return ScheduleResource::collection($schedules);
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
