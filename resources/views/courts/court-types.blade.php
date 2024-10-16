@extends('layouts.main')

@section('content')
<h1 class="mb-3">Court Types</h1>

<div class="container">

    <div class="card p-3 mb-3">
        @if(session()->has('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}  
        </div>
        @endif

        @if(session()->has('error'))
        <div class="alert alert-danger" role="alert">
            {{ session('error') }}  
        </div>
        @endif

        <form method="POST" action="/courtTypes">
            @csrf
            <div class="mb-3">
              <label for="type" class="form-label">Type</label>
              <input type="text" class="form-control" id="type" name="type" placeholder="Contoh: basket">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i></button>
        </form>
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Type</th>
                <th scope="col">Status</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($courtTypes as $i=>$courtType)
            <tr>
                <th scope="row">{{ $i + 1 }}</th>
                <td><p class="court-type-field" contenteditable>{{ $courtType->type }}</p></td>
                <td>{{ $courtType->status }}</td>
                <td>
                    @if($courtType->status == 1)
                    <form action="/courtTypes/{{ $courtType->id }}/deactivate" method="POST">
                        @csrf
                        <button class="btn btn-danger" onclick="return confirm('Apakah anda yakin akan menghapus tipe court ini?')"><i class="fa fa-trash"></i></button>
                    </form>
                    <form action="/courtTypes/{{ $courtType->id }}/update" method="POST">
                        @csrf
                        <input class="type" type="text" name="type" hidden>
                        <button class="btn btn-warning"><i class="fa fa-pencil"></i></button>
                    </form>
                    @elseif($courtType->status == 0)
                    <form action="/courtTypes/{{ $courtType->id }}/reactivate" method="POST">
                        @csrf
                        <button class="btn btn-primary"><i class="fa fa-eye"></i></button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $(".court-type-field").keyup(function(e) {
            var text = $(this).text();
            $(this).parent().next().next().find('.type').val(text);
        });
    });
</script>
@endsection