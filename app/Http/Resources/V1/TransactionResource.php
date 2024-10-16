<?php

namespace App\Http\Resources\V1;

use App\Models\Rating;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class TransactionResource extends JsonResource
{
    private  function tgl_indo($tanggal){
        $bulan = array (
            1 =>   'Jan',
            'Feb',
            'Mar',
            'Apr',
            'Mei',
            'Jun',
            'Jul',
            'Agt',
            'Sept',
            'Okt',
            'Nov',
            'Des'
        );
        $pecahkan = explode('-', $tanggal);
        
        // variabel pecahkan 0 = tanggal
        // variabel pecahkan 1 = bulan
        // variabel pecahkan 2 = tahun
        
        return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // TODO
        // $rating = Rating::where('user_id', $this->user_id)->where('court_id', $this->schedule->court->id)->where('transaction_id', $this->external_id)->count();
        
        // $schedules = DB::select(DB::raw("SELECT *, DAYOFWEEK(date) AS day_of_week FROM `schedules` WHERE id IN (SELECT schedule_id FROM `transaction_schedule_details` WHERE transaction_id = " . $this->id . ")"));
        // $resSchedules = [];
        // if (count($schedules) > 0) {
        //     $resSchedules = [];
        //     $tmpSchedule = [];
        //     foreach ($schedules as $i => $schedule) {
        //         $key = $i - 1;
        //         if (($key >= 0 && $schedule->date == $schedules[$key]->date) || $i == 0) {
        //             array_push($tmpSchedule, new ScheduleResource($schedule));
        //         } else {
        //             array_push(
        //                 $resSchedules,
        //                 [
        //                     "date" => $this->tgl_indo($schedules[$key]->date),
        //                     "dayOfWeek" => $schedules[$key]->day_of_week,
        //                     "schedule" => $tmpSchedule
        //                 ]
        //             );

        //             $tmpSchedule = [];
        //             array_push($tmpSchedule, new ScheduleResource($schedule));
        //         }
        //     }

        //     array_push(
        //         $resSchedules,
        //         [
        //             "date" => $this->tgl_indo($schedules[count($schedules) - 1]->date),
        //             "dayOfWeek" => $schedules[count($schedules) - 1]->day_of_week,
        //             "schedule" => $tmpSchedule
        //         ]
        //     );
        // }
        
        return [
            'externalId' => $this->external_id,
            'checkoutLink' => $this->checkout_link,
            'price' => $this->amount_rp,
            'orderDate' => $this->tgl_indo(explode(" ", explode("T", $this->created_at)[0])[0]),
            // 'schedules' => $resSchedules,
            'schedules' => new ScheduleCollection($this->whenLoaded('schedules')),
            'court' => new CourtResource($this->whenLoaded('court')),
            'reason' => $this->reason,
            'status' => new TransactionStatusResource($this->whenLoaded('status')),
            //TODO
            // 'isReviewed' => $rating > 0 ? true : false,
            'venueId' => $this->court->venue->id,
        ];
    }
}
