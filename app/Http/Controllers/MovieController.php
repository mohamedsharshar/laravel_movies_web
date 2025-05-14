<?php
namespace App\Http\Controllers;

use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MovieController extends Controller
{
    protected $tmdb;

    public function __construct(TmdbService $tmdb)
    {
        $this->tmdb = $tmdb;
    }

    // صفحة الأفلام الشعبية
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $query = $request->get('q');
        if ($query) {
            // If searching, use TMDB search endpoint
            $apiKey = config('services.tmdb.api_key');
            $response = \Illuminate\Support\Facades\Http::get("https://api.themoviedb.org/3/search/movie", [
                'api_key' => $apiKey,
                'language' => 'en-US',
                'query' => $query,
                'page' => $page,
            ]);
            $movies = $response->ok() ? $response->json() : ['results' => [], 'page' => 1, 'total_pages' => 1];
        } else {
            $movies = $this->tmdb->popular($page);
        }
        return view('movies.index', [
            'movies' => $movies['results'],
            'currentPage' => $movies['page'],
            'totalPages' => $movies['total_pages'],
            'overview' => $movies['results'][0]['overview'] ?? '',
        ]);
    }

    // صفحة تفاصيل فيلم بناءً على العنوان
    public function show($title, Request $request)
    {
        $category = $request->query('category');
        $apiKey = config('services.tmdb.api_key');
        $query = $title;
        if ($category && strtolower($category) !== 'all') {
            $query = $category . ' ' . $title;
        }
        $response = \Illuminate\Support\Facades\Http::get("https://api.themoviedb.org/3/search/movie", [
            'api_key' => $apiKey,
            'language' => 'en-US',
            'query' => $query,
            'page' => 1,
        ]);
        $movie = null;
        if ($response->ok()) {
            $results = $response->json()['results'] ?? [];
            $movie = collect($results)->first(function($m) use ($title) {
                return strtolower($m['title']) === strtolower($title);
            }) ?? ($results[0] ?? null);
        }
        if (!$movie) {
            abort(404, 'Movie not found');
        }
        return view('movies.show', ['movie' => $movie, 'category' => $category]);
    }

    // صفحة أفلام التصنيف
    public function category($category, Request $request)
    {
        $page = $request->get('page', 1);
        $apiKey = config('services.tmdb.api_key');
        $response = \Illuminate\Support\Facades\Http::get("https://api.themoviedb.org/3/search/movie", [
            'api_key' => $apiKey,
            'language' => 'en-US',
            'query' => $category,
            'page' => $page,
        ]);
        $movies = $response->ok() ? $response->json() : ['results' => [], 'page' => 1, 'total_pages' => 1];
        return view('movies.index', [
            'movies' => $movies['results'],
            'currentPage' => $movies['page'],
            'totalPages' => $movies['total_pages'],
            'overview' => $movies['results'][0]['overview'] ?? '',
            'activeCategory' => $category,
        ]);
    }

    public function suggestions(Request $request)
    {
        $query = $request->input('q');
        if (!$query) {
            // history suggestions if no query
            $history = $request->session()->get('search_history', []);
            return response()->json(['history' => array_reverse($history)]);
        }

        // Synonyms dictionary (expand as needed)
        $synonyms = [
            'sci-fi' => ['science fiction', 'sf', 'scifi'],
            'romance' => ['love', 'romantic'],
            'comedy' => ['funny', 'humor'],
            'action' => ['adventure', 'thriller'],
        ];
        $expanded = [$query];
        foreach ($synonyms as $key => $words) {
            if (stripos($query, $key) !== false) {
                $expanded = array_merge($expanded, $words);
            }
            foreach ($words as $word) {
                if (stripos($query, $word) !== false) {
                    $expanded[] = $key;
                }
            }
        }

        $apiKey = config('services.tmdb.api_key');
        if (!$apiKey) {
            return response()->json(['error' => 'TMDB API key missing in config/services.php or .env'], 500);
        }
        $allResults = collect();
        foreach ($expanded as $q) {
            $response = Http::get("https://api.themoviedb.org/3/search/movie", [
                'api_key' => $apiKey,
                'language' => 'en-US',
                'query' => $q,
                'page' => 1,
            ]);
            if ($response->ok()) {
                $allResults = $allResults->merge($response->json()['results'] ?? []);
            }
        }
        // Simple TF-IDF/BM25-like ranking (by popularity, vote_average, and query match)
        $scored = $allResults->unique('title')->map(function($movie) use ($query) {
            $score = 0;
            if (stripos($movie['title'], $query) !== false) $score += 10;
            if (isset($movie['overview']) && stripos($movie['overview'], $query) !== false) $score += 5;
            $score += ($movie['popularity'] ?? 0) * 0.1;
            $score += ($movie['vote_average'] ?? 0);
            return ['title' => $movie['title'], 'score' => $score];
        });
        $sorted = $scored->sortByDesc('score')->pluck('title')->unique()->take(10)->values();

        // Save to search history
        $history = $request->session()->get('search_history', []);
        if (!in_array($query, $history)) {
            $history[] = $query;
            $request->session()->put('search_history', array_slice($history, -10));
        }

        return response()->json(['suggestions' => $sorted]);
    }
}