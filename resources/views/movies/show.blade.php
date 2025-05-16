@extends('layouts.app')

@section('content')
@php
    // Dynamic background and color based on movie poster and palette
    $bg = $movie['backdrop_path'] ? 'https://image.tmdb.org/t/p/original'.$movie['backdrop_path'] : null;
    $dominantColor = '#222'; // fallback
    // Optionally, you could use a color extraction package for real palette
@endphp
<div class="movie-detail-immersive" style="min-height:100vh;background:linear-gradient(120deg,rgba(30,30,40,0.95) 60%,rgba(0,0,0,0.7)),url('{{ $bg }}') center/cover no-repeat;">
    <div style="max-width:800px;margin:0 auto;padding:2rem 1rem;backdrop-filter:blur(2px);">
        <a href="{{ route('movies.index') }}" style="color:var(--color-accent);text-decoration:underline;">← Back to Movies</a>
        @if(isset($category) && $category)
            <div style="margin:1rem 0;color:#eee;font-size:1.1rem;">
                <strong>Category:</strong> {{ $category }}
            </div>
        @endif
        <div class="movie-immersive-card" style="display:flex;gap:2rem;align-items:flex-start;background:rgba(20,20,30,0.85);border-radius:18px;box-shadow:0 8px 32px #0006;overflow:hidden;">
            <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" alt="{{ $movie['title'] }}" style="width:240px;min-width:180px;border-radius:0 18px 18px 0;box-shadow:0 4px 16px #0008;">
            <div class="movie-info" style="flex:1;padding:2rem 1rem 2rem 0;color:#fff;">
                <h1 style="font-size:2.5rem;font-weight:900;letter-spacing:-1px;line-height:1.1;margin-bottom:0.5rem;">{{ $movie['title'] }}</h1>
                <div style="margin-bottom:0.5rem;font-size:1.1rem;opacity:0.85;">
                    <span><strong>Release:</strong> {{ $movie['release_date'] }}</span>
                    <span style="margin-left:1.5rem;"><strong>Rating:</strong> ⭐ {{ $movie['vote_average'] }}/10</span>
                </div>
                <p style="margin:1.5rem 0 2rem 0;font-size:1.15rem;line-height:1.6;">{{ $movie['overview'] }}</p>
                {{-- Streaming Providers Button --}}
                @php
                    $usProviders = $providers['US'] ?? null;
                    $netflix = null;
                    $amazon = null;
                    if ($usProviders && isset($usProviders['flatrate'])) {
                        foreach ($usProviders['flatrate'] as $prov) {
                            if (stripos($prov['provider_name'], 'netflix') !== false) $netflix = $prov;
                            if (stripos($prov['provider_name'], 'amazon') !== false) $amazon = $prov;
                        }
                    }
                    $link = $usProviders['link'] ?? null;
                @endphp
                @if($netflix || $amazon)
                    <div style="margin-bottom:1.5rem;">
                        @if($netflix)
                            <a href="{{ $link }}" target="_blank" class="stream-btn netflix-btn">▶ Watch on Netflix</a>
                        @endif
                        @if($amazon)
                            <a href="{{ $link }}" target="_blank" class="stream-btn amazon-btn">▶ Watch on Amazon</a>
                        @endif
                    </div>
                @endif
                {{-- End Streaming Providers Button --}}
                @if($video && $video['site'] === 'YouTube')
                    <div style="margin:2rem 0 0 0;">
                        <h3 style="margin-bottom:0.5rem;">Watch Trailer</h3>
                        <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:12px;box-shadow:0 2px 16px #0008;">
                            <iframe src="https://www.youtube.com/embed/{{ $video['key'] }}" frameborder="0" allowfullscreen style="position:absolute;top:0;left:0;width:100%;height:100%;"></iframe>
                        </div>
                    </div>
                @elseif($video)
                    <div style="margin:2rem 0 0 0;">
                        <h3 style="margin-bottom:0.5rem;">Watch Clip</h3>
                        <a href="{{ $video['url'] ?? '#' }}" target="_blank" style="color:var(--color-accent);font-weight:bold;">View Video</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.stream-btn {
    display: inline-block;
    margin-right: 1rem;
    margin-bottom: 0.5rem;
    padding: 0.7rem 1.7rem;
    font-size: 1.13rem;
    font-weight: 700;
    border-radius: 10px;
    background: linear-gradient(90deg, #00bfae 60%, #1c2541 100%);
    color: #fff;
    text-decoration: none;
    box-shadow: 0 2px 12px rgba(0,209,178,0.13);
    transition: background 0.18s, color 0.18s, box-shadow 0.18s, transform 0.18s;
    border: none;
    outline: none;
}
.stream-btn:hover {
    background: linear-gradient(90deg, #1c2541 60%, #00bfae 100%);
    color: #fff;
    transform: translateY(-2px) scale(1.04);
    box-shadow: 0 4px 18px rgba(0,209,178,0.18);
}
.netflix-btn {
    background: linear-gradient(90deg, #e50914 60%, #1c2541 100%);
}
.netflix-btn:hover {
    background: linear-gradient(90deg, #1c2541 60%, #e50914 100%);
}
.amazon-btn {
    background: linear-gradient(90deg, #ff9900 60%, #1c2541 100%);
}
.amazon-btn:hover {
    background: linear-gradient(90deg, #1c2541 60%, #ff9900 100%);
}
</style>
@endsection
