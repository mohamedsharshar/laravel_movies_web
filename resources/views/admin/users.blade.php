@extends('layouts.app')
@section('content')
<style>
.admin-table-container {
    max-width: 1100px;
    margin: 0 auto;
    background: #f8fafc;
    border-radius: 22px;
    box-shadow: 0 4px 24px 0 rgba(58,134,255,0.10);
    padding: 32px 18px 24px 18px;
    margin-top: 32px;
}
.admin-table-title {
    font-size: 2.1rem;
    font-weight: 800;
    background: linear-gradient(90deg, #00bfae 0%, #3a86ff 100%);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 28px;
    text-align: center;
    letter-spacing: 1px;
}
.table.admin-table {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 12px 0 rgba(58,134,255,0.07);
}
.table.admin-table th {
    background: linear-gradient(90deg, #00bfae 0%, #3a86ff 100%);
    color: #fff;
    font-weight: 700;
    border: none;
    font-size: 1.08rem;
    letter-spacing: 0.5px;
}
.table.admin-table td {
    vertical-align: middle;
    font-size: 1.04rem;
    background: #f8fafc;
    border: none;
    transition: background 0.2s;
}
.table.admin-table tbody tr {
    transition: box-shadow 0.2s, background 0.2s;
}
.table.admin-table tbody tr:hover {
    background: linear-gradient(90deg, #e0f7fa 0%, #f8fafc 100%);
    box-shadow: 0 4px 18px 0 rgba(0,191,174,0.10);
}
.btn-admin-action {
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.98rem;
    padding: 7px 18px;
    border: none;
    background: #3a86ff;
    color: #fff;
    transition: background 0.2s, color 0.2s, box-shadow 0.2s;
    box-shadow: 0 2px 8px 0 rgba(58,134,255,0.10);
    margin-right: 4px;
}
.btn-admin-action:hover {
    background: #00bfae;
    color: #fff;
    box-shadow: 0 6px 18px 0 rgba(0,191,174,0.18);
}
.btn-admin-back {
    border-radius: 10px;
    font-weight: 600;
    font-size: 1rem;
    padding: 8px 22px;
    border: 2px solid #3a86ff;
    background: #fff;
    color: #3a86ff;
    transition: background 0.2s, color 0.2s, border 0.2s;
    margin-top: 18px;
    margin-left: 4px;
}
.btn-admin-back:hover {
    background: #3a86ff;
    color: #fff;
    border: 2px solid #00bfae;
}
@media (max-width: 700px) {
    .admin-table-container {
        padding: 10px 2px 10px 2px;
    }
    .admin-table-title {
        font-size: 1.2rem;
    }
    .table.admin-table th, .table.admin-table td {
        font-size: 0.95rem;
    }
}
</style>
<div class="admin-table-container">
    <div class="admin-table-title"><i class="fas fa-users"></i> Manage Users</div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <div class="table-responsive">
    <table class="table admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Admin</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @if($user->is_admin)
                        <span style="color:#00bfae;font-weight:700;">Yes</span>
                    @else
                        <span style="color:#888;">No</span>
                    @endif
                </td>
                <td>
                    @if(auth()->id() != $user->id)
                    <form method="POST" action="{{ route('admin.users.delete', $user->id) }}" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button class="btn-admin-action" onclick="return confirm('Delete this user?')"><i class="fas fa-trash"></i> Delete</button>
                    </form>
                    @else
                    <span class="text-muted">(You)</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
<div style="text-align:center;margin-bottom:32px;">
    <a href="{{ route('admin.dashboard') }}" class="btn-admin-back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection
