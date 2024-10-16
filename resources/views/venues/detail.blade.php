@extends('layouts.main')

@section('content')
<h1 class="mb-3">Venue Details</h1>
    <div class="row">
        <div class="col">
            <div class="card m-2 p-3">
                <h5 class="mb-3">
                    Nama: {{$venue->name}}
                </h5>
                <p>
                    Alamat: {{$venue->address->street}}, {{$venue->address->city}}, {{$venue->address->province}}. ({{$venue->address->postal_code}}) <br>
                    Logitude: {{ $venue->address->longitude }} <br>
                    Latitude: {{ $venue->address->latitude }}
                </p>
                <p>
                    Deskripsi: {{$venue->description}}
                </p>
                <p>
                    Jam Operasional: <br>
                    @php
                        $hari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"]
                    @endphp
                    <ul>
                    @foreach($venue->openDays as $vod)
                        <li>
                        Hari <strong>{{ $hari[$vod->day_of_week] }}</strong> pkl <strong>{{$vod->time_open}}</strong> sampai <strong>{{$vod->time_close}}</strong>
                        </li>
                    @endforeach
                    </ul>
                </p>
            </div>
        </div>
        <div class="col">
            <div class="card m-2 p-3">
                @foreach ($venue->venueImages as $image)
                    <img class="mb-3" style="max-width: 100%;" src="@php echo str_replace("private", env("APP_URL"), $image->url) @endphp" alt="">                    
                @endforeach
                
            </div>
        </div>
    </div>

    <div class="container">
        <h4>Courts</h4>
        <ul>
        @foreach($venue->courts as $court)
        <li class="mb-3">
            <a href="{{ url('/courts/' . $court->id) }}">{{ $court->name }}</a><br>
            <img src="@php echo str_replace("private", env("APP_URL"), $court->images[0]->url) @endphp" style="max-width: 300px" alt="{{ $court->name }}">
        </li>
        @endforeach
        </ul>
    </div>
@endsection