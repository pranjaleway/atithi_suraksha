@extends('layouts.main')
@section('title', 'Details')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card" id="section-block">
            <h4 class="card-header d-flex justify-content-between align-items-center">
                <span>Details</span>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @php
                        $backUrl = in_array(Auth::user()->user_type_id, [4, 5]) ? route('bookings') : url()->previous();
                    @endphp

                    <a href="{{ $backUrl }}" class="d-none d-sm-inline-block">
                        <button type="button" class="btn btn-primary waves-effect waves-light mx-1">Back</button>
                    </a>
                </div>
            </h4>
            <hr style="margin: 0.25rem">

            {{-- <div class="card-header p-0">
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
            </div> --}}
            <div class="card-body">
                {{-- <div class="tab-content p-0">
                    <div class="tab-pane fade show active" id="navs-top-general-info" role="tabpanel"> --}}
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th scope="row">Booking ID</th>
                                <td>{{ $booking->booking_id ?? 'N/A' }}</td>
                                <th scope="row">Full Name</th>
                                <td>{{ $booking->guest_name }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Contact Number</th>
                                <td>{{ $booking->contact_number }}</td>
                                <th scope="row">Email</th>
                                <td>{{ $booking->email ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Age</th>
                                <td>{{ $booking->age ?? 'N/A' }}</td>
                                <th scope="row">Gender</th>
                                <td>{{ ucfirst($booking->gender ?? 'N/A') }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Aadhar Number</th>
                                <td>{{ $booking->aadhar_number }}</td>
                                 <th scope="row">Number of Guest</th>
                                <td>{{ $booking->no_of_guest ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                 <th scope="row">Number of Male</th>
                                <td>{{ $booking->no_of_male ?? 'N/A' }}</td>
                                 <th scope="row">Number of Female</th>
                                <td>{{ $booking->no_of_female ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Number of Children</th>
                                <td>{{ $booking->hotel->no_of_children ?? 'N/A' }}</td>
                                <th scope="row">Hotel Name</th>
                                <td>{{ $booking->hotel->hotel_name }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Room Number</th>
                                <td>{{ $booking->room_number }}</td>
                                <th scope="row">Check IN</th>
                                <td>{{ \Carbon\Carbon::parse($booking->check_in)->format('d M Y, h:i A') }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Check OUT</th>
                                <td>{{ \Carbon\Carbon::parse($booking->check_out)->format('d M Y, h:i A') }}</td>
                                <th scope="row">Address</th>
                                <td>{{ $booking->address }}</td>
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
                                <th>ID Proof</th>
                                <td>
                                    @if ($booking->id_proof_path)
                                        <a href="{{ asset('storage/' . $booking->id_proof_path) }}"
                                            target="_blank">View</a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>


                        </tbody>

                    </table>
                </div>
                @if ($members->count() > 0)
                    <h5 class="mt-4">Members</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">S.No.</th>
                                    <th scope="col">Guest Name</th>
                                    <th scope="col">Aadhar Number</th>
                                    <th scope="col">Room Number</th>
                                    <th scope="col">ID Proof</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($members as $key => $member)
                                    <tr>
                                        <th scope="row">{{ $key + 1 }}</th>
                                        <td>{{ $member->guest_name }}</td>
                                        <td>{{ $member->aadhar_number }}</td>
                                        <td>{{ $member->room_number }}</td>
                                        <td>
                                            @if ($member->id_proof_path)
                                                <a href="{{ asset('storage/' . $member->id_proof_path) }}"
                                                    target="_blank">View</a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif


                @if ($visitors->count() > 0)
                    <h5 class="mt-4">Visitors</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">S.No.</th>
                                    <th scope="col">Visitor Name</th>
                                    <th scope="col">Aadhar Number</th>
                                    <th scope="col">Entry Time</th>
                                    <th scope="col">ID Proof</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($visitors as $key => $visitor)
                                    <tr>
                                        <th scope="row">{{ $key + 1 }}</th>
                                        <td>{{ $visitor->visitor_name }}</td>
                                        <td>{{ $visitor->aadhar_number }}</td>
                                        <td>{{ Carbon\Carbon::parse($visitor->entry_time)->format('d M Y, h:i A') }}</td>
                                        <td>
                                            @if ($member->id_proof_path)
                                                <a href="{{ asset('storage/' . $visitor->id_proof_path) }}"
                                                    target="_blank">View</a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                {{-- </div>
                    <div class="tab-pane fade" id="navs-top-document" role="tabpanel"> 
                        <div class="row mt-4" id="uploaded-preview-row">
                            <div class="col-md-4 mb-3">
                                <p class="fw-bold">ID Proof</p>
                                @php
                                    $filePath = $booking->id_proof_path;
                                    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                                    $fileUrl = asset('storage/' . $filePath);
                                @endphp

                                @if (!empty($filePath) && file_exists(public_path('storage/' . $filePath)))
                                    @if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                        <img src="{{ $fileUrl }}" alt="ID Proof Image" class="img-fluid"
                                            style="max-height: 200px;">
                                    @elseif($ext === 'pdf')
                                        <iframe src="{{ $fileUrl }}" width="100%" height="200px"
                                            style="border:1px solid #ccc;"></iframe>
                                    @else
                                        <p>Unsupported file type. <a href="{{ $fileUrl }}"
                                                target="_blank">Download</a></p>
                                    @endif
                                @else
                                    <p>No ID proof uploaded.</p>
                                @endif
                            </div>

                        </div>


                    </div>
                </div> --}}
            </div>
        </div>
    @endsection
