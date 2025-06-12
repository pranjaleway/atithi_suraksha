@include('layouts.header')
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('layouts.menu')
        <div class="layout-page">
            @include('layouts.navbar')
            <div class="content-wrapper">
                @yield('content')
                @include('layouts.footer')
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
     <!-- Overlay -->
     <div class="layout-overlay layout-menu-toggle"></div>

     <!-- Drag Target Area To SlideIn Menu On Small Screens -->
     <div class="drag-target"></div>
</div>
@include('layouts.scriptLinks')
