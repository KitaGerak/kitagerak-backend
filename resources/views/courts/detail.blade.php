@extends('layouts.main')

@section('content')
<h1 class="mb-3">Court Details</h1>
    <div class="row">
        <div class="col">
            <div class="card m-2 p-3">
                <h5 class="mb-3">
                    Nama: {{$court->name}}
                </h5>
                <p>
                    Deskripsi: {{$court->description}}
                </p>
                <p>
                    Jenis lantai: {{ $court->floor_type }}
                </p>
                <p>
                    Tipe lapangan: {{ $court->courtType->type }}
                </p>
                <p>
                    Harga regular: <strong>Rp. {{ $court->regular_price }}</strong><br>
                    Harga member: <strong>Rp. {{ $court->member_price }}</strong>
                </p>
                <p>
                    Jadwal tersedia:<br>

                    <ul>
                        @foreach($court->schedules as $schedule)
                        <li>
                            {{ $schedule->date }}
                        </li>
                        <li>
                            {{ $schedule->time_start }} - {{ $schedule->time_finish }}
                        </li>
                        <li>
                            {{ $schedule->availability }}
                        </li>
                        @endforeach
                    </ul>
                </p>
                <p>
                </p>
            </div>
        </div>
        <div class="col">
            <div class="card m-2 p-3">
                @foreach ($court->images as $image)
                    <img class="mb-3" style="max-width: 100%;" src="@php echo str_replace("private", env("APP_URL"), $image->url) @endphp" alt="">                    
                @endforeach
                
            </div>
        </div>
    </div>
@endsection