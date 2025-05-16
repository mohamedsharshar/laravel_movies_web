@extends('layouts.app')
@section('content')
<div class="auth-form-container">
    <h2 class="auth-title">Create Account</h2>
    <form method="POST" action="{{ route('register.post') }}" class="auth-form">
        @csrf
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus>
            @error('name')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required>
            @error('email')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
            @error('password')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required>
        </div>
        <button type="submit" class="auth-btn">Register</button>
        <div class="auth-switch">
            Already have an account? <a href="{{ route('login') }}">Login</a>
        </div>
    </form>
</div>
<style>
.auth-form-container {
    max-width: 420px;
    margin: 2.5rem auto;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 32px #00bfae22;
    padding: 2.5rem 2rem 2rem 2rem;
    position: relative;
}
.auth-title {
    text-align: center;
    margin-bottom: 2rem;
    font-size: 2rem;
    font-weight: 800;
    color: #00bfae;
    letter-spacing: -1px;
}
.auth-form .form-group {
    margin-bottom: 1.2rem;
}
.auth-form label {
    display: block;
    margin-bottom: 0.3rem;
    font-weight: 600;
    color: #222;
}
.auth-form input {
    width: 100%;
    padding: 0.65rem 0 .65rem .23rem ;
    border-radius: 7px;
    border: 1.5px solid #cfd8dc;
    background: #f7fafd;
    font-size: 1.08rem;
    transition: border 0.18s;
}
.auth-form input:focus {
    border-color: #00bfae;
    outline: none;
    background: #e0f7fa;
}
.auth-btn {
    width: 100%;
    padding: 0.85rem;
    background: linear-gradient(90deg,#00bfae 60%,#1C3AA9 100%);
    color: #fff;
    border: none;
    border-radius: 7px;
    font-weight: 700;
    font-size: 1.15rem;
    cursor: pointer;
    margin-top: 0.5rem;
    box-shadow: 0 2px 8px #00bfae22;
    transition: background 0.18s, box-shadow 0.18s;
}
.auth-btn:hover {
    background: linear-gradient(90deg,#1C3AA9 0%,#00bfae 100%);
    box-shadow: 0 4px 16px #00bfae33;
}
.auth-switch {
    margin-top: 1.5rem;
    text-align: center;
    font-size: 1.01rem;
}
.auth-switch a {
    color: #00bfae;
    font-weight: 600;
    text-decoration: underline;
}
.error {
    color: #c00;
    font-size: 0.97em;
    margin-top: 0.2rem;
}
</style>
@endsection
