@extends('layouts.main')
@section('title', 'Add Manual Transfer Entries')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <h4 class="card-header d-flex justify-content-between align-items-center">
                Add Manual Transfer Entries
                <a href="{{ route('transfer-entries') }}" class="d-none d-sm-inline-block"><button type="button"
                        class="btn btn-primary waves-effect waves-light mx-2">Back</button></a>
            </h4>
            <hr style="margin: 0.25rem">


            <div class="card-body">
                <div class="col-12">
                    @if ($bookings->count() > 0)
                        <form class="form-repeater add-new-record" enctype="multipart/form-data" id="add-form"
                            action="{{ route('store-manual-transfer-entry') }}">
                            <table class=" table table-bordered">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Guset Name</th>
                                        <th>Aadhar Number</th>
                                        <th>Contact Number</th>
                                        <th>Room number</th>
                                        <th>Date and Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bookings as $booking)
                                        <input type="hidden" name="booking_ids[]" value="{{ $booking->id }}">
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $booking->guest_name }}</td>
                                            <td>{{ $booking->aadhar_number }}</td>
                                            <td>{{ $booking->contact_number }}</td>
                                            <td>{{ $booking->room_number }}</td>
                                            <td>{{ $booking->created_at->format('d M Y, h:i A') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="modal-footer mt-2">
                                <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                            </div>
                        </form>
                    @else
                        <p class="text-center">No bookings found.</p>
                    @endif

                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/custom-js/page-add-transfer-entries.js') }}"></script>
@endsection
