<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreScheduleRequest;
use App\Http\Requests\V1\UpdateScheduleRequest;
use App\Http\Resources\V1\ScheduleResource;
use App\Models\Schedule;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{    
    public function index(Request $request) {
        $courtId = $request->query('courtId');
        
        $dayOfWeek = $request->query('dayOfWeek');
        $startDate = $request->query('startDate');
        $monthInterval = $request->query('monthInterval');

        $date = $request->query('date');
        // SELECT *, DAYOFWEEK(date) AS day_of_week, availability, status FROM `schedules` WHERE date > NOW() AND court_id = 1 AND date <= DATE_ADD('2024-10-02', interval 1 MONTH) HAVING day_of_week = 4 ORDER BY date, time_start;
        
        if ($courtId != null && $dayOfWeek != null && $startDate != null && $monthInterval != null) {
            $res = DB::Select("SELECT *, DAYOFWEEK(date) AS dayOfWeek, availability, status FROM `schedules` WHERE date > NOW() AND date >= ? AND court_id = ? AND date <= DATE_ADD(?, interval ? MONTH) HAVING dayOfWeek = ? ORDER BY date, time_start", [$startDate, $courtId, $startDate, $monthInterval, $dayOfWeek]);
        } else if ($courtId != null && $date != null) {
            $res = DB::Select("SELECT *, DAYOFWEEK(date) AS dayOfWeek, availability, status FROM `schedules` WHERE date > NOW() AND date >= ? AND court_id = ? ORDER BY date, time_start", [$date, $courtId]);
        } else {
            //error
            return response()->json([
                'status' => false,
                // TODO: Change error message
                'message' => 'Parameter tidak lengkap',
            ], 422);
        }

        return $res;
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

    public function update(Request $request)
    {
        
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->status = 0;
        $schedule->save();
    }
}
