@extends('layouts.app')
@section('content')
<style>
.admin-dashboard-cards .card {
    transition: transform 0.25s cubic-bezier(.4,2,.6,1), box-shadow 0.25s;
    box-shadow: 0 2px 12px 0 rgba(0,0,0,0.07);
    border-radius: 18px;
    border: none;
    cursor: pointer;
}
.admin-dashboard-cards .card:hover {
    transform: translateY(-7px) scale(1.04);
    box-shadow: 0 8px 32px 0 rgba(0,0,0,0.18);
    background: linear-gradient(90deg, #00bfae 0%, #1C2541 100%);
    color: #fff;
}
.admin-dashboard-btns a {
    transition: background 0.2s, color 0.2s, box-shadow 0.2s;
    border-radius: 12px;
    margin-right: 12px;
    font-weight: 500;
    box-shadow: 0 2px 8px 0 rgba(0,191,174,0.08);
}
.admin-dashboard-btns a:hover {
    background: #1C2541 !important;
    color: #fff !important;
    box-shadow: 0 4px 16px 0 rgba(0,191,174,0.18);
}
</style>
<div class="container py-4">
    <h2 class="mb-4" style="font-weight:700;letter-spacing:1px;">Admin Dashboard</h2>
    <div class="row mb-4 admin-dashboard-cards">
        <div class="col-md-4">
            <div class="card text-center bg-success text-white mb-3">
                <div class="card-body">
                    <h5 class="card-title">Users</h5>
                    <p class="card-text" style="font-size:2.2rem;font-weight:600;">{{ $usersCount }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center bg-info text-white mb-3">
                <div class="card-body">
                    <h5 class="card-title">Contact Messages</h5>
                    <p class="card-text" style="font-size:2.2rem;font-weight:600;">{{ $contactsCount }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="admin-dashboard-btns mb-3">
        <a href="{{ route('admin.users') }}" class="btn btn-primary">Manage Users</a>
        <a href="{{ route('admin.contacts') }}" class="btn btn-secondary">View Contact Messages</a>
    </div>
</div>
@endsection
