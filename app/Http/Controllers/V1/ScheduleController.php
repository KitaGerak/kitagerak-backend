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

class ScheduleController extends Controller
{
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
