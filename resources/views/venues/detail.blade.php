@extends('layouts.light')

@section('content')
    <div class="card m-3 p-2">
        <div class="row">
            <div class="col">
                <div class="card m-2 p-3">
                    <p>
                        Nama : {{$venue->name}}
                    </p>
                    <p>
                        Alamat : {{$venue->address->street}}, {{$venue->address->city}}, {{$venue->address->province}}. ({{$venue->address->postal_code}})
                    </p>
                    <p>
                        Deskripsi : {{$venue->description}}
                    </p>
                    <p>
                        Jam Operasional : {{$venue->open_hour}} sampai {{$venue->close_hour}}
                    </p>
                </div>
            </div>
            <div class="col">
                <div class="card m-2 p-3">
                    @foreach ($venue->venueImages as $venueImage)
                        <img src="{{url("/storage/$venueImage->url")}}" alt="">
                    @endforeach
                    
                </div>
            </div>
        </div>
    </div>
@endsection