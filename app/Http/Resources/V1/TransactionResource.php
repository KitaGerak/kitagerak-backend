<?php

namespace App\Http\Resources\V1;

use App\Models\Rating;
use Illuminate\Http\Resources\Json\JsonResource;

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
        $rating = Rating::where('user_id', $this->user_id)->where('court_id', $this->schedule->court->id)->where('transaction_id', $this->external_id)->count();
        return [
            'externalId' => $this->external_id,
            'orderDate' => $this->tgl_indo(explode(" ", explode("T", $this->created_at)[0])[0]),
            'schedule' => new ScheduleResource($this->whenLoaded('schedule')),
            'court' => new CourtResource($this->whenLoaded('court')),
            'reason' => $this->reason,
            'transactionStatus' => new TransactionStatusResource($this->whenLoaded('transactionStatus')),
            'isReviewed' => $rating > 0 ? true : false,
            'venueId' => $this->court->venue->id,
        ];
    }
}
