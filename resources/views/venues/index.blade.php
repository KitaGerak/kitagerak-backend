@extends('layouts.light')

@section('content')
    @if (session()->has('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif


    <div class="card m-3 p-2">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Nama</th>
                    <th scope="col">Alamat</th>
                    <th scope="col">Pemilik</th>
                    <th scope="col">Status</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($venues as $venue)
                    <tr>
                        <td>{{ $venue->name }}</td>
                        <td>{{ $venue->address->street }}</td>
                        <td>{{ $venue->owner->name }}</td>
                        <td>{{ $venue->status == 1 ? 'Accepted' : 'Waiting' }}</td>
                        <td>
                            <form action="{{ url("/venues/$venue->id/accept") }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success mr-1" onclick="return confirm('Apakah anda yakin akan melakukan acc pada venue ini?')">ACC</button>
                            </form>
                            <a href="{{ url("/venues/$venue->id/detail") }}" class="btn btn-info">Detail</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
