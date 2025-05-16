@extends('layouts.app')
@section('content')
<style>
body {
    background: linear-gradient(120deg, #232526 0%, #1C2541 100%);
    min-height: 100vh;
}
.admin-dashboard-container {
    max-width: 950px;
    margin: 0 auto;
    padding: 32px 16px 0 16px;
}
.admin-dashboard-title {
    font-weight: 900;
    letter-spacing: 1.5px;
    color: #232526;
    background: linear-gradient(90deg, #00bfae 0%, #3a86ff 100%);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-size: 2.5rem;
    margin-bottom: 32px;
    text-align: center;
    text-shadow: 0 2px 8px rgba(58,134,255,0.08);
}
.admin-dashboard-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
    justify-content: center;
    margin-bottom: 32px;
}
.admin-dashboard-cards .card {
    flex: 1 1 220px;
    min-width: 220px;
    padding: 15px;
    max-width: 320px;
    background: linear-gradient(135deg, #f8fafc 60%, #e0f7fa 100%);
    color: #232526;
    border-radius: 22px;
    border: none;
    box-shadow: 0 4px 18px 0 rgba(58,134,255,0.10);
    transition: transform 0.25s cubic-bezier(.4,2,.6,1), box-shadow 0.25s, background 0.25s, color 0.25s;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}
.admin-dashboard-cards .card:hover {
    transform: translateY(-10px) scale(1.05) rotate(-1deg);
    box-shadow: 0 12px 36px 0 rgba(58,134,255,0.18);
    background: linear-gradient(120deg, #3a86ff 0%, #00bfae 100%);
    color: #fff;
}
.admin-dashboard-cards .card .card-title {
    font-size: 1.18rem;
    font-weight: 700;
    margin-bottom: 8px;
    letter-spacing: 1px;
    color: inherit;
}
.admin-dashboard-cards .card .card-text {
    font-size: 2.5rem;
    font-weight: 900;
    margin-bottom: 0;
    color: inherit;
}
.admin-dashboard-btns {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    justify-content: center;
    margin-bottom: 24px;
}
.admin-dashboard-btns a {
    transition: background 0.2s, color 0.2s, box-shadow 0.2s, border 0.2s;
    border-radius: 14px;
    font-weight: 700;
    font-size: 1.1rem;
    padding: 13px 32px;
    box-shadow: 0 2px 8px 0 rgba(58,134,255,0.10);
    border: 2px solid #3a86ff;
    background: #3a86ff;
    color: #fff;
    text-decoration: none;
    letter-spacing: 0.5px;
}
.admin-dashboard-btns a:hover {
    background: #fff !important;
    color: #3a86ff !important;
    border: 2px solid #00bfae;
    box-shadow: 0 6px 18px 0 rgba(0,191,174,0.18);
    transform: translateY(-2px) scale(1.04);
}
@media (max-width: 900px) {
    .admin-dashboard-cards {
        flex-direction: column;
        align-items: center;
    }
    .admin-dashboard-cards .card {
        max-width: 100%;
        min-width: 0;
    }
}
@media (max-width: 600px) {
    .admin-dashboard-container {
        padding: 16px 4px 0 4px;
    }
    .admin-dashboard-title {
        font-size: 1.3rem;
    }
    .admin-dashboard-btns a {
        font-size: 0.98rem;
        padding: 10px 16px;
    }
}
</style>
<div class="admin-dashboard-container">
    <div class="admin-dashboard-title">
        <i class="fas fa-cogs" style="margin-right:10px;"></i> Admin Dashboard
    </div>
    <div class="admin-dashboard-cards">
        <div class="card text-center mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-users"></i> Users</h5>
                <p class="card-text">{{ $usersCount }}</p>
            </div>
        </div>
        <div class="card text-center mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-envelope"></i> Contact Messages</h5>
                <p class="card-text">{{ $contactsCount }}</p>
            </div>
        </div>
    </div>
    <div class="admin-dashboard-btns">
        <a href="{{ route('admin.users') }}"><i class="fas fa-user-cog"></i> Manage Users</a>
        <a href="{{ route('admin.contacts') }}" style="background:#00bfae; border-color:#00bfae;"><i class="fas fa-envelope-open-text"></i> View Messages</a>
    </div>
</div>
<!-- Font Awesome CDN for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection
