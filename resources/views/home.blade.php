@extends('layouts.main')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css" />

<div class="container">
    <h1 class="mb-4">{{ $title }}</h1>

    <table id="dataTable" class="table table-striped">
        <thead>
            <tr>
            <th scope="col">Nomor Transaksi</th>
            <th scope="col">Identitas Penyewa</th>
            <th scope="col">Identitas Pemilik</th>
            <th scope="col">Court</th>
            <th scope="col">Jadwal</th>
            <th scope="col">Status Pembayaran</th>
            <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>
                <th scope="row">{{ $transaction->external_id }}</th>
                <td>{{ $transaction->user->name }}</td>
                <td>{{ $transaction->court->venue->owner->name }}</td>
                <td>{{ $transaction->court->name }}</td>
                <td>
                    <ul>
                    @foreach($transaction->schedules as $schedule)
                    <li>
                        {{ $schedule->date }} at {{ $schedule->time_start }} - {{ $schedule->time_finish }}
                    </li>
                    @endforeach
                    </ul>
                </td>
                <td>
                    {{ $transaction->status->status }}
                </td>
                <td>
                    <button class="btn btn-danger"><i class="fa fa-trash"></i></button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('script')
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
<script>
    $(document).ready( function () {
        new DataTable('#dataTable', {
            scrollX: true,
            initComplete: function () {
                this.api()
                    .columns()
                    .every(function () {
                        let column = this;
                        let title = column.footer().textContent;
        
                        // Create input element
                        let input = document.createElement('input');
                        input.placeholder = title;
                        column.footer().replaceChildren(input);
        
                        // Event listener for user input
                        input.addEventListener('keyup', () => {
                            if (column.search() !== this.value) {
                                column.search(input.value).draw();
                            }
                        });
                    });
            }
        });

        $(".btn-decline").click(function() {
            var courtId = $(this).data('id');
            $(".reasonForm").attr('action', '/courts/' + courtId +  "/decline");
        });
    } );
</script>
@endsection
