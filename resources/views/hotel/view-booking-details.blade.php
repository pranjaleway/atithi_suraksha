@extends('layouts.main')
@section('title', 'Details')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card" id="section-block">
            <h4 class="card-header d-flex justify-content-between align-items-center">
                <span>Details</span>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('bookings') }}" class="d-none d-sm-inline-block">
                        <button type="button" class="btn btn-primary waves-effect waves-light mx-1">Back</button>
                    </a>
                </div>
            </h4>
            <hr style="margin: 0.25rem">

            <div class="card-header p-0">
                <div class="nav-align-top">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                                data-bs-target="#navs-top-general-info" aria-controls="navs-top-general-info"
                                aria-selected="true">
                                General Details
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                data-bs-target="#navs-top-document" aria-controls="navs-top-document" aria-selected="false">
                                Document
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div class="tab-content p-0">
                    <div class="tab-pane fade show active" id="navs-top-general-info" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th scope="row">Name</th>
                                        <td>{{ $booking->guest_name }}</td>
                                        <th scope="row">Contact Number</th>
                                        <td>{{ $booking->contact_number }}</td>
                                    </tr>
                                    <tr>
                                       <th scope="row">Hotel Name</th>
                                        <td>{{ $booking->hotel->hotel_name }}</td>
                                        <th scope="row">Room Number</th>
                                        <td>{{ $booking->room_number }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Email</th>
                                        <td>{{ $booking->email ?? 'N/A' }}</td>
                                        <th scope="row">Aadhar Number</th>
                                        <td>{{ $booking->aadhar_number }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Check IN</th>
                                        <td>{{\Carbon\Carbon::parse($booking->check_in)->format('Y-m-d')}}</td>
                                        <th scope="row">Check OUT</th>
                                        <td>{{\Carbon\Carbon::parse($booking->check_out)->format('Y-m-d')}}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Address</th>
                                        <td colspan="3">{{ $booking->address }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">City</th>
                                        <td>{{ $booking->city->name }}</td>
                                        <th>State</th>
                                        <td>{{ $booking->state->name }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Pincode</th>
                                        <td>{{ $booking->pincode }}</td>
                                    </tr>
                                   

                                </tbody>

                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="navs-top-document" role="tabpanel">
                            <div class="row mt-4" id="uploaded-preview-row">
                                   <div class="col-md-4 mb-3">
    <p class="fw-bold">ID Proof</p>
    @php
        $filePath = $booking->id_proof_path;
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $fileUrl = asset('storage/' . $filePath);
    @endphp

    @if(!empty($filePath) && file_exists(public_path('storage/' . $filePath)))
        @if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
            <img src="{{ $fileUrl }}" alt="ID Proof Image" class="img-fluid" style="max-height: 200px;">
        @elseif($ext === 'pdf')
            <iframe src="{{ $fileUrl }}" width="100%" height="200px" style="border:1px solid #ccc;"></iframe>
        @else
            <p>Unsupported file type. <a href="{{ $fileUrl }}" target="_blank">Download</a></p>
        @endif
    @else
        <p>No ID proof uploaded.</p>
    @endif
</div>

                            </div>

                    </div>
                </div>
            </div>
        </div>
    @endsection
