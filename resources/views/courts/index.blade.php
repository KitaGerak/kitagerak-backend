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
                    <th scope="col">ID</th>
                    <th scope="col">Nama</th>
                    <th scope="col">Dari Venue</th>
                    <th scope="col">Deskripsi</th>
                    <th scope="col">Tipe court</th>
                    <th scope="col">Harga regular</th>
                    <th scope="col">Harga member</th>
                    <th scope="col">Rating</th>
                    <th scope="col">Status</th>
                    <th scope="col">Keterangan</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($courts as $court)
                    <tr>
                        <td>{{ $court->id }}</td>
                        <td>{{ $court->name }}</td>
                        <td>
                            <a target="_blank" href="{{ url("/venues/" . $court->venue->id) }}">{{ $court->venue->name }}</a><br>
                            (Status Venue: {{ $court->venue->status }})
                        </td>
                        <td>{{ $court->description }}</td>
                        <td>{{ $court->courtType->type }}</td>
                        <td>Rp. {{ $court->regular_price }}</td>
                        <td>Rp. {{ $court->member_price }}</td>
                        <td>{{ $court->sum_rating }}/{{ $court->number_of_people }}</td>
                        @if($court->status == 1)
                        <td>Diterima dan sudah beroperasi</td>
                        @elseif($court->status == -1)
                        <td class="text-warning">Menunggu konfirmasi</td>
                        @elseif($court->status == -2)
                        <td class="text-danger">Di-nonaktif-kan oleh admin</td>
                        @else
                        <td>Nonaktif</td>
                        @endif
                        <td>
                            @if(isset($court->rejectionMessages[0]))
                                Alasan Penolakan Pengajuan Court:
                                <ul>
                                @foreach($court->rejectionMessages as $rm)
                                    <li>{{ $rm->reason }}</li>
                                @endforeach
                                </ul>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if ($court->status == -1 || $court->status == -2)
                            <form action="{{ url("/court/$court->id/accept") }}" method="POST" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-success mr-1" onclick="return confirm('Apakah anda yakin akan melakukan acc pada venue ini?')">ACC</button>
                            </form>
                            @endif

                            @if ($court->status != 0)
                            <button data-id={{ $court->id }} type="button" class="btn btn-danger me-1 btn-decline mb-2" data-bs-toggle="modal" data-bs-target="#closeCourtModal">
                                @if($court->status == -2)
                                Tambah alasan
                                @else
                                Tolak / tutup court
                                @endif
                            </button>
                            @endif
                            <a href="{{ url("/courts/$court->id") }}" class="btn btn-info">Detail</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th scope="col">Nama</th>
                    <th scope="col">Dari Venue</th>
                    <th scope="col">Deskripsi</th>
                    <th scope="col">Tipe court</th>
                    <th scope="col">Harga regular</th>
                    <th scope="col">Harga member</th>
                    <th scope="col">Rating</th>
                    <th scope="col">Status</th>
                    <th scope="col">Keterangan</th>
                    <th scope="col">Aksi</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="modal fade" id="closeCourtModal" tabindex="-1" aria-labelledby="closeCourtModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="closeCourtModalLabel">Alasan Penutupan / Penolakan Court</h1>
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
                            @if(isset($court) && $court->status == -2)
                            Tambah alasan
                            @else
                            Proses penolakan court
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
            var courtId = $(this).data('id');
            $(".reasonForm").attr('action', '/courts/' + courtId +  "/decline");
        });
    } );
</script>
@endsection
