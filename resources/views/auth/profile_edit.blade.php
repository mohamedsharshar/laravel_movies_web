@extends('layouts.app')

@section('content')
<div style="max-width: 520px; margin: 2rem auto; background: #fff; border-radius: 16px; box-shadow: 0 4px 18px #1c254122; padding: 2.5rem 2rem;">
    <h2 style="font-size: 1.7rem; font-weight: bold; color: #1c2541; margin-bottom: 1.2rem; text-align:center;">Edit Profile</h2>
    @if(session('success'))
        <div style="background:#e0f7fa;color:#00796b;padding:10px 0;border-radius:8px;margin-bottom:1rem;text-align:center;">{{ session('success') }}</div>
    @endif
    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:1.2rem;">
        @csrf
        <div style="text-align:center;">
            @if(Auth::user()->avatar && file_exists(public_path('storage/' . Auth::user()->avatar)))
                <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar" style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:2.5px solid #1c2541;">
                <br>
                <button type="submit" formaction="{{ route('profile.avatar.delete') }}" formmethod="POST" style="background:#ffe082;color:#1c2541;border:none;padding:6px 18px;border-radius:6px;margin-top:8px;cursor:pointer;font-size:0.98rem;">Delete Image</button>
            @else
                <div style="width:90px;height:90px;border-radius:50%;background:#e0e7ef;display:flex;align-items:center;justify-content:center;font-size:2.5rem;color:#1c2541;margin:0 auto;">👤</div>
            @endif
        </div>
        <label style="font-weight:500;">Name
            <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" required style="width:100%;padding:10px 12px;border-radius:8px;border:1.5px solid #ccc;margin-top:6px;">
        </label>
        <label style="font-weight:500;">Email
            <input type="email" value="{{ Auth::user()->email }}" disabled style="width:100%;padding:10px 12px;border-radius:8px;border:1.5px solid #ccc;margin-top:6px;background:#f3f4f6;">
        </label>
        <label style="font-weight:500;">New Password (optional)
            <input type="password" name="password" style="width:100%;padding:10px 12px;border-radius:8px;border:1.5px solid #ccc;margin-top:6px;">
        </label>
        <label style="font-weight:500;">Confirm Password
            <input type="password" name="password_confirmation" style="width:100%;padding:10px 12px;border-radius:8px;border:1.5px solid #ccc;margin-top:6px;">
        </label>
        <label style="font-weight:500;">Profile Image (optional)
            <input type="file" name="avatar" accept="image/*" style="margin-top:6px;">
        </label>
        <button type="submit" style="background:#1c2541;color:#fff;padding:0.8rem 0;border-radius:8px;font-size:1.1rem;font-weight:600;cursor:pointer;transition:background 0.18s;">Save Changes</button>
        <a href="{{ route('profile') }}" style="display:block;text-align:center;margin-top:1rem;color:#1c2541;text-decoration:underline;">Back to Profile</a>
    </form>
    @if($errors->any())
        <div style="background:#ffe0e0;color:#c00;padding:10px 0;border-radius:8px;margin-top:1.2rem;text-align:center;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif
</div>
@endsection
