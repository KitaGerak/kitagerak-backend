@extends('layouts.main')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css" />
<style>
    tfoot input {
        width: 100%;
        padding: 3px;
        box-sizing: border-box;
    }
</style>

    @if (session()->has('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="container mt-4">
        <h1 class="mb-3">{{ $title }}</h1>
        <table id="dataTable" class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">Nama</th>
                    <th scope="col">Alamat</th>
                    <th scope="col">Identitas Pemilik</th>
                    <th scope="col">Status</th>
                    <th scope="col">Keterangan</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($venues as $venue)
                    <tr>
                        <td>{{ $venue->name }}</td>
                        <td>{{ $venue->address->street }}, {{ $venue->address->city }}</td>
                        <td>
                            Nama: <strong>{{ $venue->owner->name }}</strong> <br>
                            Email: <strong><a href="mailto:{{ $venue->owner->email }}">{{ $venue->owner->email }}</a></strong><br>
                            Telp: <strong>{{ $venue->owner->phone_number }}</strong><br>
                        </td>
                        @if($venue->status == 1)
                        <td>Diterima dan sudah beroperasi</td>
                        @elseif($venue->status == -1)
                        <td class="text-warning">Menunggu konfirmasi</td>
                        @elseif($venue->status == -2)
                        <td class="text-danger">Di-nonaktif-kan oleh admin</td>
                        @else
                        <td>Nonaktif</td>
                        @endif
                        <td>
                            @if(isset($venue->rejectionMessages[0]))
                                Alasan Penolakan Pengajuan Venue:
                                <ul>
                                @foreach($venue->rejectionMessages as $rm)
                                    <li>{{ $rm->reason }}</li>
                                @endforeach
                                </ul>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if ($venue->status == -1 || $venue->status == -2)
                            <form action="{{ url("/venues/$venue->id/accept") }}" method="POST" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-success mr-1" onclick="return confirm('Apakah anda yakin akan melakukan acc pada venue ini?')">ACC</button>
                            </form>
                            @endif

                            @if ($venue->status != 0)
                            <button data-id={{ $venue->id }} type="button" class="btn btn-danger me-1 btn-decline mb-2" data-bs-toggle="modal" data-bs-target="#closeVenueModal">
                                @if($venue->status == -2)
                                Tambah alasan
                                @else
                                Tolak / tutup venue
                                @endif
                            </button>
                            @endif
                            <a href="{{ url("/venues/$venue->id") }}" class="btn btn-info">Detail</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th scope="col">Nama</th>
                    <th scope="col">Alamat</th>
                    <th scope="col">Identitas Pemilik</th>
                    <th scope="col">Status</th>
                    <th scope="col">Keterangan</th>
                    <th scope="col">Aksi</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="modal fade" id="closeVenueModal" tabindex="-1" aria-labelledby="closeVenueModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="closeVenueModalLabel">Alasan Penutupan / Penolakan Venue</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="reasonForm" action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <div class="form-floating">
                                <textarea class="form-control" placeholder="Beri komentar kepada pemilik vanue" id="floatingTextarea" name="reason" style="height: 100px" required></textarea>
                                <label for="floatingTextarea2">Beri komentar kepada pemilik venue</label>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="check" name="check" required>
                            <label class="form-check-label" for="check">Saya sudah yakin.</label>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            @if(isset($venue) && $venue->status == -2)
                            Tambah alasan
                            @else
                            Proses penolakan venue
                            @endif
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
            var venueId = $(this).data('id');
            console.log(venueId);
            $(".reasonForm").attr('action', '/venues/' + venueId +  "/decline");
        });
    });
</script>
@endsection
