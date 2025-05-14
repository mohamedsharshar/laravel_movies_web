@extends('layouts.app')

@section('content')
<div class="main-content">
    <a href="{{ route('movies.index') }}" style="color:var(--color-accent);text-decoration:underline;">← Back to Movies</a>
    @if(isset($category) && $category)
        <div style="margin:1rem 0;color:#555;font-size:1.1rem;">
            <strong>Category:</strong> {{ $category }}
        </div>
    @endif
    <div class="movie-card" style="max-width:500px;margin:2rem auto;">
        <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" alt="{{ $movie['title'] }}" style="width:100%;border-radius:8px;">
        <div class="movie-info" style="padding:1rem;">
            <h1 style="font-size:2rem;font-weight:bold;">{{ $movie['title'] }}</h1>
            <p><strong>Release Date:</strong> {{ $movie['release_date'] }}</p>
            <p><strong>Rating:</strong> ⭐ {{ $movie['vote_average'] }}/10</p>
            <p style="margin-top:1rem;">{{ $movie['overview'] }}</p>
        </div>
    </div>
</div>
@endsection
