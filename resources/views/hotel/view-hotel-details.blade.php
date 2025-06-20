@extends('layouts.main')
@section('title', 'Hotels Details')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card" id="section-block">
            <h4 class="card-header d-flex justify-content-between align-items-center">
                <span>Hotel Details</span>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @if (hasPermission('hotels', 'edit') && $hotels->police_station_id === null)
                        <div class="input-group input-group-merge" style="width: 180px;">
                            <div class="form-floating form-floating-outline" style="font-size: initial;">
                                <select class="form-select" data-id="{{ $hotels->id }}" name="police_station_id"
                                    id="police_station_id" data-url="{{ route('assign-police-station')}}">
                                    <option value="">Select Police Station</option>
                                    @foreach ($policeStations as $policeStation)
                                        <option value="{{ $policeStation->id }}">{{ $policeStation->police_station_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="police_station_id">Assign Police Station</label>
                            </div>
                        </div>
                    @endif
                    <a href="{{ route('hotel-employees', base64_encode($hotels->id)) }}" class="d-none d-sm-inline-block">
                        <button type="button" class="btn btn-primary waves-effect waves-light mx-1">Employees</button>
                    </a>
                    <a href="{{ route('hotels') }}" class="d-none d-sm-inline-block">
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
                                data-bs-target="#navs-top-personal-details" aria-controls="navs-top-personal-details"
                                aria-selected="false">
                                Personal Details
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
                                        <td>{{ $hotels->hotel_name }}</td>
                                        <th scope="row">Contact Number</th>
                                        <td>{{ $hotels->contact_number }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Email</th>
                                        <td>{{ $hotels->email }}</td>
                                        <th scope="row">License Number</th>
                                        <td>{{ $hotels->license_number }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Address</th>
                                        <td colspan="3">{{ $hotels->address }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">City</th>
                                        <td>{{ $hotels->city->name }}</td>
                                        <th>State</th>
                                        <td>{{ $hotels->state->name }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Pincode</th>
                                        <td>{{ $hotels->pincode }}</td>
                                        <th scope="row">Alloted Police Station</th>
                                        <td class="@if ($hotels->police_station_id == null)
                                            text-danger
                                        @endif">
                                            {{ $hotels->police_station_id == null ? 'Not Assigned' : $hotels->police_station->police_station_name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">hotels Status</th>
                                        <td>{{ $hotels->status == 1 ? 'Active' : 'Inactive' }}</td>
                                    </tr>

                                </tbody>

                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="navs-top-personal-details" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th scope="row">Owner Name</th>
                                        <td>{{ $hotels->owner_name }}</td>
                                        <th scope="row">Contact Number</th>
                                        <td>{{ $hotels->contact_number }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Aadhar Number</th>
                                        <td>{{ $hotels->aadhar_number }}</td>
                                        <th scope="row">Pan Number</th>
                                        <td>{{ $hotels->pan_number }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="navs-top-document" role="tabpanel">
                        @if ($hotels->ownerDocuments->count())
                            <div class="row mt-4" id="uploaded-preview-row">
                                @foreach ($hotels->ownerDocuments as $doc)
                                    <div class="col-md-4 mb-3">
                                        <p class="fw-bold">{{ $doc->document->name ?? 'Document' }}</p>
                                        @php
                                            $ext = pathinfo($doc->document_path, PATHINFO_EXTENSION);
                                        @endphp
                                        @if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                            <img src="{{ asset('storage/' . $doc->document_path) }}"
                                                alt="Uploaded Document" class="img-fluid" style="max-height: 200px;">
                                        @elseif($ext === 'pdf')
                                            <iframe src="{{ asset('storage/' . $doc->document_path) }}" width="100%"
                                                height="200px" style="border:1px solid #ccc;"></iframe>
                                        @else
                                            <p>No preview available</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">No documents available.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @section('scripts')
        <script src="{{ asset('assets/custom-js/view-hotel-details.js') }}"></script>
    @endsection
