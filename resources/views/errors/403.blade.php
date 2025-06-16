@extends('layouts.main')
@section('title', '403 Forbidden')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-misc.css') }}" />
@section('content')

    <div class="misc-wrapper">
        <h1 class="mb-2 mx-2" style="font-size: 6rem">403</h1>
        <h4 class="mb-2">You are not authorized! 🔐</h4>
        <p class="mb-2 mx-2">{{ $exception->getMessage() }}</p>
        <p>Kindly reach out to the Administrator to obtain the necessary permissions.</p>
        <div class="d-flex justify-content-center mt-5">
            <a href="{{ route('dashboard') }}" class="btn btn-primary text-center my-4">
                <div class="d-flex flex-column align-items-center">
                    <div>
                        Back to Dashboard
                    </div>
                </div>
            </a>
        </div>
    </div>
@endsection
