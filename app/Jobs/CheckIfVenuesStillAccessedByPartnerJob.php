<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckIfVenuesStillAccessedByPartnerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $venues = Venue::all();

        foreach ($venues as $venue) {
            
            $userThatHandlesVenue = User::find($venue->owner_id);
            if(isset($userThatHandlesVenue))
            {
                $lastAccessing = Carbon::make($userThatHandlesVenue->last_accessing);
                // dd($userThatHandlesVenue->last_accessing);
                $totalDuration = $lastAccessing->diffInDays(Carbon::now());
                if($totalDuration > 0)
                {
                    $venue->status = 0;
                    $venue->save();
                }
            }
        }
    }
}
