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

    public function popular(int $page = 1, int $perPage = 40)
    {
        // TMDB returns 20 per page, so fetch multiple pages if needed
        $results = [];
        $pagesNeeded = (int) ceil($perPage / 20);
        for ($i = 0; $i < $pagesNeeded; $i++) {
            $response = Http::get("{$this->baseUrl}/movie/popular", [
                'api_key' => $this->apiKey,
                'language' => 'en-US',
                'page' => $page + $i,
            ]);
            if ($response->ok()) {
                $json = $response->json();
                $results = array_merge($results, $json['results'] ?? []);
                $total_pages = $json['total_pages'] ?? 1;
            }
        }
        return [
            'results' => array_slice($results, 0, $perPage),
            'page' => $page,
            'total_pages' => $total_pages ?? 1
        ];
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