@include('layouts.header')
@section('title', 'Privacy Policy')
<!-- Content -->

<div class="container py-5">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-5">
            <h1 class="fw-bold mb-4 text-primary">Privacy Policy</h1>

            <div class="mt-4">
                <h3 class="text-dark mb-2">1. Introduction</h3>
                <p class="text-secondary">
                    This Privacy Policy explains how Atithi Suraksha collects, uses,
                    stores, and protects personal information related to our web application that connects Hotels, Hotel
                    Employees, SP Offices, and Police Stations.
                </p>
            </div>

            <div class="mt-4">
                <h3 class="text-dark mb-2">2. Information We Collect</h3>
                <p class="text-secondary">
                    We may collect the following types of information:
                </p>
                <ul class="text-secondary">
                    <li><strong>Identification Information:</strong> Name, contact details, ID proof of guests.</li>
                    <li><strong> Booking Information:</strong> Guest details, room booking information,
                        check-in/check-out times.</li>
                    <li><strong>Employee Information:</strong> Hotel staff details (name, role, contact).</li>
                    <li><strong>Enforcement Information:</strong> Police station assignments, records of bookings
                        transferred.</li>
                </ul>

            </div>

            <div class="mt-4">
                <h3 class="text-dark mb-2">3. How We Use Information</h3>
                <p class="text-secondary">We use collected information to:</p>
                <ul class="text-secondary">
                    <li>Manage hotel bookings.</li>
                    <li>Assign and notify relevant Police Stations about guest booking details as required by law.</li>
                    <li>Facilitate coordination between Hotels, SP Offices, and Police Stations.</li>
                </ul>
            </div>

            <div class="mt-4">
                <h3 class="text-dark mb-2">4. Sharing of Information</h3>
                <p class="text-secondary">
                    We may share information only under the following circumstances:
                </p>
                <ul class="text-secondary">
                    <li><strong>Police Stations:</strong> Guest booking details are forwarded to the assigned Police
                        Station for security and verification purposes.</li>
                    <li><strong>SP Offices:</strong> For administrative and regulatory oversight.</li>
                </ul>
                <p class="text-secondary">We do not sell or rent user data to third parties.</p>
            </div>

            <div class="mt-4">
                <h3 class="text-dark mb-2">5. Data Storage & Security</h3>
                <ul class="text-secondary">
                    <li>All data is stored securely on our servers.</li>
                    <li>Access to user data is limited to authorized personnel only.</li>
                    <li>We use encryption and secure transfer protocols to protect sensitive data.</li>
                </ul>
            </div>

            <div class="mt-4">
                <h3 class="text-dark mb-2">6. User Rights</h3>
                <p class="text-secondary">
                    Users have the right to:
                </p>
                <ul class="text-secondary">
                    <li>Access and review the information shared.</li>
                    <li>Request corrections to inaccurate information.</li>
                    <li>Contact us regarding concerns about data usage.</li>
                </ul>
            </div>

            <div class="mt-4">
                <h3 class="text-dark mb-2">7. Cookies</h3>
                <p class="text-secondary">
                    Our application may use cookies for session management and improving user experience.
                </p>
            </div>

            <div class="mt-4">
                <h3 class="text-dark mb-2">8. Changes to Policy</h3>
                <p class="text-secondary">
                    We may update this Privacy Policy from time to time. Updates will be reflected with a new “Last
                    Updated” date.
                </p>
            </div>

            <div class="mt-4">
                <h3 class="text-dark mb-2">9. Contact Us</h3>
                <p class="text-secondary">
                    For any questions, please contact us at
                    <a href="mailto:atithi-suraksha@ewayits.com" class="text-decoration-none fw-semibold text-primary">
                        atithi-suraksha@ewayits.com
                    </a>.
                </p>
            </div>
        </div>
    </div>
</div>
<!-- / Content -->

@extends('layouts.scriptLinks')
