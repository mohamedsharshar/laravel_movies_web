<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class TmdbService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.tmdb.base_url');
        $this->apiKey = config('services.tmdb.api_key');
    }

    public function popular(int $page = 1)
    {
        $response = Http::get("{$this->baseUrl}/movie/popular", [
            'api_key' => $this->apiKey,
            'language' => 'en-US',
            'page' => $page,
        ]);

        return $response->json();
    }

    public function getMovieVideos($movieId)
    {
        $response = Http::get("{$this->baseUrl}/movie/{$movieId}/videos", [
            'api_key' => $this->apiKey,
            'language' => 'en-US',
        ]);
        return $response->ok() ? $response->json()['results'] ?? [] : [];
    }

}