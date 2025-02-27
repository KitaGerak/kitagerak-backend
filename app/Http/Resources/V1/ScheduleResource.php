<?php

namespace App\Http\Resources\V1;

use App\Models\CourtPrice;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
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
    public function toArray($request)
    {   
        $offer = NULL;
        if (isset($this->transaction) && str_contains($this->transaction->external_id, "RGLR")) {
            $price = $this->regular_price;
            $discount = $this->regular_discount;
        } else if (isset($this->transaction) && str_contains($this->transaction->external_id, "MEMBER")){
            $price = $this->member_price;
            $discount = $this->member_discount;
        } else {
            $price = 0;
            $discount = 0;
            $offer = [
                'memberPrice' => $this->member_price,
                'regularPrice' => $this->regular_price,
                'memberDiscount' => $this->member_discount,
                'regularDiscount' => $this->regular_discount
            ];
        }

        return [
            'id' => $this->id,
            'date' => $this->tgl_indo($this->date),
            // 'dayOfWeek' => $this->day_of_week,
            'timeStart' => $this->time_start,
            'timeFinish' => $this->time_finish,
            'interval' => $this->interval,
            'availability' => (int)$this->availability,
            'price' => $price,
            'discount' => $discount,
            'offer' => $offer,
            'status' => (int)$this->status,
            'courtId' => $this->court_id,
        ];
    }
}
