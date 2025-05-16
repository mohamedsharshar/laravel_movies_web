@extends('layouts.app')

@section('content')
<div style="max-width: 500px; margin: 2rem auto; background: #fff; border-radius: 16px; box-shadow: 0 4px 18px #1c254122; padding: 2.5rem 2rem; text-align: center;">
    @if(Auth::user()->avatar && file_exists(public_path('storage/' . Auth::user()->avatar)))
        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar" style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:2.5px solid #1c2541;margin-bottom:1rem;">
    @else
        <div style="font-size: 3rem; margin-bottom: 1rem; color: #1c2541;">👤</div>
    @endif
    <h2 style="font-size: 2rem; font-weight: bold; color: #1c2541; margin-bottom: 0.5rem;">{{ Auth::user()->name }}</h2>
    <p style="font-size: 1.1rem; color: #555; margin-bottom: 1.2rem;">{{ Auth::user()->email }}</p>
    <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: center;">
        <a href="/" style="background: #1c2541; color: #fff; padding: 0.7rem 2.2rem; border-radius: 8px; font-size: 1.1rem; font-weight: 500; text-decoration: none; transition: background 0.18s;">🏠 Home</a>
        <a href="{{ route('profile.edit') }}" style="background: #ffe082; color: #1c2541; padding: 0.7rem 2.2rem; border-radius: 8px; font-size: 1.1rem; font-weight: 500; text-decoration: none; transition: background 0.18s;">✏️ Edit Profile</a>
    </div>
</div>
@endsection
