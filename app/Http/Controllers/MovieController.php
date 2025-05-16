<?php
namespace App\Http\Controllers;

use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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
        $method = $request->get('method', 'dtm');
        $apiKey = config('services.tmdb.api_key');
        $categories = Cache::remember('tmdb_categories', 1440, function() use ($apiKey) {
            $res = Http::get('https://api.themoviedb.org/3/genre/movie/list', [
                'api_key' => $apiKey,
                'language' => 'en-US',
            ]);
            return $res->ok() ? ($res->json()['genres'] ?? []) : [];
        });
        $perPage = 40; // Show 40 movies per page
        if ($query) {
            $movies = $this->smartQuerySearch($query, $apiKey, $page); // You may want to update this for more results too
        } else {
            $movies = $this->tmdb->popular($page, $perPage);
        }
        return view('movies.index', [
            'movies' => $movies['results'],
            'currentPage' => $movies['page'],
            'totalPages' => $movies['total_pages'],
            'overview' => $movies['results'][0]['overview'] ?? '',
            'categories' => $categories,
            'activeCategory' => $request->route('category') ?? null,
            'searchQuery' => $query,
        ]);
    }

    // صفحة الأفلام الأكثر شهرة وتقييماً
    public function popular(Request $request)
    {
        $page = $request->get('page', 1);
        $apiKey = config('services.tmdb.api_key');
        $perPage = 40; // Show 40 movies per page
        $response = $this->tmdb->popular($page, $perPage);
        $movies = $response;
        $categories = \Illuminate\Support\Facades\Cache::remember('tmdb_categories', 1440, function() use ($apiKey) {
            $res = \Illuminate\Support\Facades\Http::get('https://api.themoviedb.org/3/genre/movie/list', [
                'language' => 'en-US',
            ]);
            return $res->ok() ? ($res->json()['genres'] ?? []) : [];
        });
        return view('movies.index', [
            'movies' => $movies['results'],
            'currentPage' => $movies['page'],
            'totalPages' => min($movies['total_pages'], 500),
            'overview' => $movies['results'][0]['overview'] ?? '',
            'categories' => $categories,
            'activeCategory' => 'Popular',
            'searchQuery' => null,
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
        $video = null;
        $providers = [];
        if ($response->ok()) {
            $results = $response->json()['results'] ?? [];
            $movie = collect($results)->first(function($m) use ($title) {
                return strtolower($m['title']) === strtolower($title);
            }) ?? ($results[0] ?? null);
            if ($movie && isset($movie['id'])) {
                $tmdb = app(\App\Services\TmdbService::class);
                $videos = $tmdb->getMovieVideos($movie['id']);
                $video = collect($videos)->first(function($v) {
                    return $v['site'] === 'YouTube' && $v['type'] === 'Trailer';
                }) ?? (count($videos) ? $videos[0] : null);
                // Fetch streaming providers
                $providers = $tmdb->getMovieWatchProviders($movie['id']);
            }
        }
        if (!$movie) {
            abort(404, 'Movie not found');
        }
        return view('movies.show', ['movie' => $movie, 'category' => $category, 'video' => $video, 'providers' => $providers]);
    }

    // صفحة أفلام التصنيف
    public function category($category, Request $request)
    {
        $page = $request->get('page', 1);
        $apiKey = config('services.tmdb.api_key');
        // Fetch categories from TMDB API (cache for 1 day)
        $categories = Cache::remember('tmdb_categories', 1440, function() use ($apiKey) {
            $res = Http::get('https://api.themoviedb.org/3/genre/movie/list', [
                'api_key' => $apiKey,
                'language' => 'en-US',
            ]);
            return $res->ok() ? ($res->json()['genres'] ?? []) : [];
        });
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
            'categories' => $categories,
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

    public function contact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'message' => 'required|string|max:2000',
        ]);
        // Send email to admin
        Mail::html(
            '<div style="background:#f8fafc;padding:32px 18px 24px 18px;border-radius:16px;max-width:480px;margin:0 auto;font-family:sans-serif;">
                <h2 style="color:#1C2541;margin-bottom:8px;">New Contact Message</h2>
                <div style="margin-bottom:12px;"><b>Name:</b> <span style="color:#00bfae;">' . e($validated['name']) . '</span></div>
                <div style="margin-bottom:12px;"><b>Email:</b> <span style="color:#1C2541;">' . e($validated['email']) . '</span></div>
                <div style="margin-bottom:12px;"><b>Message:</b></div>
                <div style="background:#fff;border-radius:8px;padding:16px 12px;color:#222;font-size:1.08rem;border:1.5px solid #d1f7f2;">' . nl2br(e($validated['message'])) . '</div>
            </div>',
            function ($message) use ($validated) {
                $message->to('mmshsh05@gmail.com')
                    ->subject('New Contact Message from ' . $validated['name']);
            }
        );
        return back()->with('success', 'Your message has been sent successfully!');
    }

    // Movie AI Assistant page
    public function aiAssistant()
    {
        return view('movies.ai');
    }

    // AI chat API endpoint
    public function aiAsk(Request $request)
    {
        $request->validate(['message' => 'required|string|max:500']);
        $userMsg = $request->input('message');
        $apiKey = env('OPENAI_API_KEY');
        if (!$apiKey) {
            return response()->json(['reply' => 'AI service not configured. Please set OPENAI_API_KEY in your .env file.']);
        }
        try {
            $headers = [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ];
            // If using a project key, add OpenAI-Beta header
            if (str_starts_with($apiKey, 'sk-proj-')) {
                $headers['OpenAI-Beta'] = 'assistants=v1';
            }
            $response = Http::withHeaders($headers)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a helpful AI movie assistant. Answer in English or Arabic as appropriate. If the user asks for movie recommendations, suggest popular or highly rated movies.'],
                        ['role' => 'user', 'content' => $userMsg],
                    ],
                    'max_tokens' => 200,
                    'temperature' => 0.7,
                ]);
            if ($response->ok()) {
                $reply = $response->json('choices.0.message.content') ?? 'Sorry, I could not generate a reply.';
            } else {
                Log::error('OpenAI API error', ['status' => $response->status(), 'body' => $response->body()]);
                $reply = 'Sorry, there was a problem contacting the AI service. [' . $response->status() . ']';
            }
        } catch (\Exception $e) {
            Log::error('OpenAI API exception', ['error' => $e->getMessage()]);
            $reply = 'Sorry, there was an error processing your request.';
        }
        return response()->json(['reply' => $reply]);
    }

    // Document-Term Matrix Search
    private function searchDTM($query, $apiKey, $page) {
        $response = Http::get("https://api.themoviedb.org/3/search/movie", [
            'api_key' => $apiKey,
            'language' => 'en-US',
            'query' => $query,
            'page' => $page,
        ]);
        if ($response->ok()) {
            $results = $response->json()['results'] ?? [];
            $terms = preg_split('/\s+/', strtolower($query));
            // Filter: at least one term in title or overview
            $results = array_filter($results, function($movie) use ($terms) {
                foreach ($terms as $term) {
                    if ((isset($movie['title']) && stripos($movie['title'], $term) !== false) ||
                        (isset($movie['overview']) && stripos($movie['overview'], $term) !== false)) {
                        return true;
                    }
                }
                return false;
            });
            return [
                'results' => array_values($results),
                'page' => 1,
                'total_pages' => 1
            ];
        }
        return ['results' => [], 'page' => 1, 'total_pages' => 1];
    }

    // Inverted Index Search
    private function searchInverted($query, $apiKey, $page) {
        $response = Http::get("https://api.themoviedb.org/3/search/movie", [
            'api_key' => $apiKey,
            'language' => 'en-US',
            'query' => $query,
            'page' => $page,
        ]);
        if ($response->ok()) {
            $results = $response->json()['results'] ?? [];
            $terms = preg_split('/\s+/', strtolower($query));
            usort($results, function($a, $b) use ($terms) {
                $aScore = 0; $bScore = 0;
                foreach ($terms as $term) {
                    if ((isset($a['title']) && stripos($a['title'], $term) !== false) ||
                        (isset($a['overview']) && stripos($a['overview'], $term) !== false)) $aScore++;
                    if ((isset($b['title']) && stripos($b['title'], $term) !== false) ||
                        (isset($b['overview']) && stripos($b['overview'], $term) !== false)) $bScore++;
                }
                return $bScore <=> $aScore;
            });
            return [
                'results' => $results,
                'page' => 1,
                'total_pages' => 1
            ];
        }
        return ['results' => [], 'page' => 1, 'total_pages' => 1];
    }

    // BiWords Index Search
    private function searchBiWords($query, $apiKey, $page) {
        $response = Http::get("https://api.themoviedb.org/3/search/movie", [
            'api_key' => $apiKey,
            'language' => 'en-US',
            'query' => $query,
            'page' => $page,
        ]);
        if ($response->ok()) {
            $results = $response->json()['results'] ?? [];
            $words = preg_split('/\s+/', strtolower($query));
            $bigrams = [];
            for ($i = 0; $i < count($words) - 1; $i++) {
                $bigrams[] = $words[$i] . ' ' . $words[$i+1];
            }
            usort($results, function($a, $b) use ($bigrams) {
                $aScore = 0; $bScore = 0;
                foreach ($bigrams as $bigram) {
                    if ((isset($a['title']) && stripos(strtolower($a['title']), $bigram) !== false) ||
                        (isset($a['overview']) && stripos(strtolower($a['overview']), $bigram) !== false)) $aScore++;
                    if ((isset($b['title']) && stripos(strtolower($b['title']), $bigram) !== false) ||
                        (isset($b['overview']) && stripos(strtolower($b['overview']), $bigram) !== false)) $bScore++;
                }
                return $bScore <=> $aScore;
            });
            return [
                'results' => $results,
                'page' => 1,
                'total_pages' => 1
            ];
        }
        return ['results' => [], 'page' => 1, 'total_pages' => 1];
    }

    // Positional Index Search
    private function searchPositional($query, $apiKey, $page) {
        $response = Http::get("https://api.themoviedb.org/3/search/movie", [
            'api_key' => $apiKey,
            'language' => 'en-US',
            'query' => $query,
            'page' => $page,
        ]);
        if ($response->ok()) {
            $results = $response->json()['results'] ?? [];
            $phrase = strtolower($query);
            usort($results, function($a, $b) use ($phrase) {
                $aScore = 0; $bScore = 0;
                if ((isset($a['title']) && stripos(strtolower($a['title']), $phrase) !== false) ||
                    (isset($a['overview']) && stripos(strtolower($a['overview']), $phrase) !== false)) $aScore += 2;
                if ((isset($b['title']) && stripos(strtolower($b['title']), $phrase) !== false) ||
                    (isset($b['overview']) && stripos(strtolower($b['overview']), $phrase) !== false)) $bScore += 2;
                return $bScore <=> $aScore;
            });
            return [
                'results' => $results,
                'page' => 1,
                'total_pages' => 1
            ];
        }
        return ['results' => [], 'page' => 1, 'total_pages' => 1];
    }

    // B+ Tree Index Search
    private function searchBPlusTree($query, $apiKey, $page) {
        $response = Http::get("https://api.themoviedb.org/3/search/movie", [
            'api_key' => $apiKey,
            'language' => 'en-US',
            'query' => $query,
            'page' => $page,
        ]);
        if ($response->ok()) {
            $results = $response->json()['results'] ?? [];
            usort($results, function($a, $b) {
                $aTitle = $a['title'] ?? '';
                $bTitle = $b['title'] ?? '';
                $aOverview = $a['overview'] ?? '';
                $bOverview = $b['overview'] ?? '';
                $cmp = strcmp($aTitle, $bTitle);
                if ($cmp === 0) {
                    return strcmp($aOverview, $bOverview);
                }
                return $cmp;
            });
            return [
                'results' => $results,
                'page' => 1,
                'total_pages' => 1
            ];
        }
        return ['results' => [], 'page' => 1, 'total_pages' => 1];
    }

    // Boolean Search
    private function booleanSearch($query, $apiKey, $page) {
        $response = Http::get("https://api.themoviedb.org/3/search/movie", [
            'api_key' => $apiKey,
            'language' => 'en-US',
            'query' => $query,
            'page' => $page,
        ]);
        if ($response->ok()) {
            $results = $response->json()['results'] ?? [];
            $queryUpper = strtoupper($query);
            if (strpos($queryUpper, ' AND ') !== false) {
                $terms = array_map('trim', explode('AND', $queryUpper));
                $results = array_filter($results, function($movie) use ($terms) {
                    foreach ($terms as $term) {
                        if ((stripos($movie['title'], $term) === false) && (stripos($movie['overview'], $term) === false)) {
                            return false;
                        }
                    }
                    return true;
                });
            } elseif (strpos($queryUpper, ' OR ') !== false) {
                $terms = array_map('trim', explode('OR', $queryUpper));
                $results = array_filter($results, function($movie) use ($terms) {
                    foreach ($terms as $term) {
                        if ((stripos($movie['title'], $term) !== false) || (stripos($movie['overview'], $term) !== false)) {
                            return true;
                        }
                    }
                    return false;
                });
            } elseif (strpos($queryUpper, ' NOT ') !== false) {
                $parts = array_map('trim', explode('NOT', $queryUpper));
                $include = $parts[0];
                $exclude = $parts[1];
                $results = array_filter($results, function($movie) use ($include, $exclude) {
                    $in = (stripos($movie['title'], $include) !== false) || (stripos($movie['overview'], $include) !== false);
                    $out = (stripos($movie['title'], $exclude) !== false) || (stripos($movie['overview'], $exclude) !== false);
                    return $in && !$out;
                });
            }
            return [
                'results' => array_values($results),
                'page' => 1,
                'total_pages' => 1
            ];
        }
        return ['results' => [], 'page' => 1, 'total_pages' => 1];
    }

    // Fuzzy Search
    private function fuzzySearch($query, $apiKey, $page) {
        $response = Http::get("https://api.themoviedb.org/3/search/movie", [
            'api_key' => $apiKey,
            'language' => 'en-US',
            'query' => $query,
            'page' => $page,
        ]);
        if ($response->ok()) {
            $results = $response->json()['results'] ?? [];
            $results = array_filter($results, function($movie) use ($query) {
                return (levenshtein(strtolower($query), strtolower($movie['title'])) <= 2)
                    || (isset($movie['overview']) && levenshtein(strtolower($query), strtolower($movie['overview'])) <= 2);
            });
            return [
                'results' => array_values($results),
                'page' => 1,
                'total_pages' => 1
            ];
        }
        return ['results' => [], 'page' => 1, 'total_pages' => 1];
    }

    // Phrase Search
    private function phraseSearch($query, $apiKey, $page) {
        $response = Http::get("https://api.themoviedb.org/3/search/movie", [
            'api_key' => $apiKey,
            'language' => 'en-US',
            'query' => $query,
            'page' => $page,
        ]);
        if ($response->ok()) {
            $results = $response->json()['results'] ?? [];
            $phrase = trim($query, '"');
            $results = array_filter($results, function($movie) use ($phrase) {
                return (stripos($movie['title'], $phrase) !== false)
                    || (isset($movie['overview']) && stripos($movie['overview'], $phrase) !== false);
            });
            return [
                'results' => array_values($results),
                'page' => 1,
                'total_pages' => 1
            ];
        }
        return ['results' => [], 'page' => 1, 'total_pages' => 1];
    }

    /**
     * Synonym-aware search: expands query with synonyms (supports Arabic and English)
     */
    private function synonymSearch($query, $apiKey, $page) {
        // Simple synonym dictionary (expand as needed)
        $synonyms = [
            // English
            'movie' => ['film', 'cinema', 'picture'],
            'data' => ['information', 'بيانات'],
            'mining' => ['extraction', 'تنقيب'],
            'analysis' => ['analytics', 'تحليل'],
            'love' => ['romance', 'حب'],
            'action' => ['thriller', 'حركة'],
            'science' => ['علم'],
            'fiction' => ['خيال'],
            // Arabic
            'فيلم' => ['movie', 'film', 'سينما'],
            'بيانات' => ['data', 'معلومات'],
            'تنقيب' => ['mining', 'استخراج'],
            'تحليل' => ['analysis', 'analytics'],
            'حب' => ['love', 'romance'],
            'حركة' => ['action', 'thriller'],
            'علم' => ['science'],
            'خيال' => ['fiction'],
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
        $allResults = collect();
        foreach ($expanded as $q) {
            $response = \Illuminate\Support\Facades\Http::get("https://api.themoviedb.org/3/search/movie", [
                'api_key' => $apiKey,
                'language' => 'ar', // Arabic support
                'query' => $q,
                'page' => $page,
            ]);
            if ($response->ok()) {
                $allResults = $allResults->merge($response->json()['results'] ?? []);
            }
        }
        // Remove duplicates by title
        $unique = $allResults->unique('title')->values();
        return [
            'results' => $unique->all(),
            'page' => 1,
            'total_pages' => 1
        ];
    }

    // Detect and route to the correct query method based on input
    private function smartQuerySearch($query, $apiKey, $page) {
        // Phrase search if query is in quotes
        if (preg_match('/^".*"$/', trim($query))) {
            return $this->phraseSearch($query, $apiKey, $page);
        }
        // Boolean search if AND/OR/NOT present
        if (preg_match('/\b(AND|OR|NOT)\b/i', $query)) {
            return $this->booleanSearch($query, $apiKey, $page);
        }
        // Fuzzy search if a word is close to a known word (use a small dictionary for demo)
        $knownWords = ['movie', 'data', 'mining', 'analysis', 'love', 'action', 'science', 'fiction', 'war', 'romance', 'comedy', 'thriller'];
        $words = preg_split('/\s+/', strtolower($query));
        $fuzzy = false;
        $corrected = [];
        foreach ($words as $word) {
            $minDist = 3; $best = $word;
            foreach ($knownWords as $kw) {
                $dist = levenshtein($word, $kw);
                if ($dist < $minDist) {
                    $minDist = $dist;
                    $best = $kw;
                }
            }
            if ($best !== $word) $fuzzy = true;
            $corrected[] = $best;
        }
        if ($fuzzy) {
            $correctedQuery = implode(' ', $corrected);
            return $this->fuzzySearch($correctedQuery, $apiKey, $page);
        }
        // Default: DTM
        return $this->searchDTM($query, $apiKey, $page);
    }
}
