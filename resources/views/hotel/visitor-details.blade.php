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

            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th scope="row">Full Name</th>
                                <td>{{ $visitor->visitor_name }}</td>
                                
                                <th scope="row">Contact Number</th>
                                <td>{{ $visitor->contact_number }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Age</th>
                                <td>{{ $visitor->age ?? 'N/A' }}</td>
                                <th scope="row">Gender</th>
                                <td>{{ ucfirst($visitor->gender ?? 'N/A') }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Aadhar Number</th>
                                <td>{{ $visitor->aadhar_number }}</td>
                                <th scope="row">Entry Time</th>
                                <td>{{ \Carbon\Carbon::parse($visitor->entry_time)->format('d M Y, h:i A') }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Address</th>
                                <td colspan="3">{{ $visitor->address }}</td>
                                
                            </tr>

                            <tr>
                                <th>State</th>
                                <td>{{ $visitor->state->name }}</td>
                                <th scope="row">City</th>
                                <td>{{ $visitor->city->name }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Pincode</th>
                                <td>{{ $visitor->pincode }}</td>
                                <th>ID Proof</th>
                                <td>
                                    @if ($visitor->id_proof_path)
                                        <a href="{{ asset('storage/' . $visitor->id_proof_path) }}"
                                            target="_blank">View</a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>


                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    @endsection
