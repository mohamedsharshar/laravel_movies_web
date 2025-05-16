@extends('layouts.app')

@section('content')
<div class="sidebar" style="overflow-y:auto;">
    <h3 class="text-white mb-4">🎛️ Filters</h3>
    <ul class="text-white space-y-2">
        <li>
            <a href="{{ route('movies.index') }}" class="{{ !isset($activeCategory) ? 'active' : '' }} hover:underline">Home</a>
        </li>
        @if(isset($categories) && count($categories))
            @foreach($categories as $cat)
                <li>
                    <a href="{{ route('movies.category', ['category' => $cat['name']]) }}" class="{{ (isset($activeCategory) && strtolower($activeCategory) == strtolower($cat['name'])) ? 'active' : '' }} hover:underline">
                        {{ $cat['name'] }}
                    </a>
                </li>
            @endforeach
        @else
            <li><span>No categories found</span></li>
        @endif
    </ul>
</div>

<!-- Responsive Menubar for mobile/tablet -->
<div class="menubar">
    <span style="font-weight:bold; font-size:1.1rem;">🎬 Movie App</span>
    <div class="menu-links">
        <a href="{{ route('movies.index') }}">Home</a>
        <a href="{{ route('movies.popular') }}">Popular</a>
        <a href={{ route('contact') }}>Contact Us</a>
    </div>
</div>

<div class="main-content">
    <h1 class="text-2xl font-bold text-center mb-6">🎬 Popular Movies</h1>

    {{-- ✅ Search Box with Voice + Search Method Select --}}
    <div class="search-box">
        <input type="text" placeholder="Search for a movie..." id="searchInput" autocomplete="on" onfocus="showHistory()">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin:8px 0;">
            <label class="query-method-label" id="label_boolean" style="display:flex;align-items:center;gap:4px;">
                <input type="checkbox" name="queryMethod" id="method_boolean" value="boolean"> Boolean
            </label>
            <label class="query-method-label" id="label_fuzzy" style="display:flex;align-items:center;gap:4px;">
                <input type="checkbox" name="queryMethod" id="method_fuzzy" value="fuzzy"> Fuzzy
            </label>
            <label class="query-method-label" id="label_phrase" style="display:flex;align-items:center;gap:4px;">
                <input type="checkbox" name="queryMethod" id="method_phrase" value="phrase"> Phrase
            </label>
            <select id="searchMethod" style="margin-left:12px; padding:4px 8px; border-radius:6px; border:1px solid #ccc;">
                <option value="dtm">Matrix Document-Term</option>
                <option value="inverted">Index Inverted</option>
                <option value="biwords">Index BiWords</option>
                <option value="positional">Index Positional</option>
                <option value="bplustree">Index Tree +B</option>
            </select>
        </div>
        <button class="mic-btn" id="micBtn" onclick="startVoiceSearch()" title="Voice Search">🎤</button>
        <button class="search-btn" onclick="searchMovies()" title="Search">🔍 Search</button>
    </div>
    
    {{-- ✅ Show selected search method --}}
    @if(request('method'))
        <div class="mb-2 text-sm text-gray-600 text-center">
            <span>Search Method:</span>
            <span class="font-semibold">@switch(request('method'))
                @case('dtm') Document-Term Matrix @break
                @case('inverted') Inverted Index @break
                @case('biwords') BiWords Index @break
                @case('positional') Positional Index @break
                @case('bplustree') B+ Tree Index @break
                @default {{ ucfirst(request('method')) }}
            @endswitch</span>
        </div>
    @endif

    {{-- ✅ Suggestions --}}
    <div class="suggestions mb-4" id="suggestionsContainer"></div>

    {{-- ✅ Movies Grid --}}
    @php
        // Helper to highlight search terms in a string (works for both text and voice input)
        function highlight_terms($text, $query) {
            if (!$query) return $text;
            $query = trim($query, '"');
            // Split on whitespace for multiple words
            $terms = preg_split('/\s+/', preg_quote($query, '/'));
            $pattern = '/(' . implode('|', array_filter($terms)) . ')/iu';
            // Use preg_replace_callback to wrap each match in <span class="highlight">
            return preg_replace_callback($pattern, function($m) {
                return '<span class="highlight">' . $m[0] . '</span>';
            }, e($text));
        }
    @endphp
    <div class="movies-grid" >
        @foreach($movies as $movie)
            <a href="{{ route('movies.show', ['title' => urlencode($movie['title'])]) }}" style="text-decoration:none;color:inherit;">
                <div class="movie-card" style="cursor:pointer; min-height: 340px; max-height: 400px;">
                    <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" alt="{{ $movie['title'] }}">
                    <div class="movie-info">
                        <h2>{!! highlight_terms($movie['title'], $searchQuery ?? request('q')) !!}</h2>
                        <p>Release Date: {{ $movie['release_date'] }}</p>
                        <p class="text-sm">Rating ⭐ {{ $movie['vote_average'] }}/10</p>
                        <p class="text-sm text-gray-600 mt-2">{!! highlight_terms(\Illuminate\Support\Str::limit($movie['overview'], 100), $searchQuery ?? request('q')) !!}</p>
                    </div>
                </div>
            </a>
        @endforeach
        @for($i = count($movies); $i < 40; $i++)
            <div class="movie-card" style="visibility:hidden;"></div>
        @endfor
    </div>

    {{-- ✅ Pagination --}}
    @php
        $side = 2; // how many pages to show on each side of current
        $window = $side * 2;
        $minPage = 1;
        $maxPage = 500;
        $currentPage = max($minPage, min($currentPage, $maxPage));
        $totalPages = $maxPage;
    @endphp
    <div class="pagination-wrapper">
        <div class="pagination flex justify-center items-center gap-2 mt-8 mb-4 animate-fade-in">
            {{-- Previous Button --}}
            <a href="?page={{ max($minPage, $currentPage - 1) }}" class="{{ $currentPage == $minPage ? 'pointer-events-none opacity-50' : '' }}">
                <button class="px-4 py-2 rounded-md bg-[var(--color-accent)] text-white font-semibold transition hover:bg-[#00bfae] focus:outline-none">Previous</button>
            </a>
            {{-- Page Numbers --}}
            @if ($totalPages <= $window + 4)
                @for ($i = $minPage; $i <= $totalPages; $i++)
                    <a href="?page={{ $i }}" class="{{ $currentPage == $i ? 'pointer-events-none' : '' }}">
                        <button class="px-3 py-2 rounded-md mx-1 font-semibold transition focus:outline-none {{ $currentPage == $i ? 'bg-blue-700 text-white shadow scale-110' : 'bg-white text-gray-700 hover:bg-blue-100' }}">{{ $i }}</button>
                    </a>
                @endfor
            @else
                {{-- First page --}}
                <a href="?page=1" class="{{ $currentPage == 1 ? 'pointer-events-none' : '' }}">
                    <button class="px-3 py-2 rounded-md mx-1 font-semibold transition focus:outline-none {{ $currentPage == 1 ? 'bg-blue-700 text-white shadow scale-110' : 'bg-white text-gray-700 hover:bg-blue-100' }}">1</button>
                </a>
                {{-- Ellipsis before window --}}
                @if ($currentPage > $window)
                    <span class="px-2 text-gray-400">...</span>
                @endif
                {{-- Page window --}}
                @for ($i = max(2, $currentPage - $side); $i <= min($totalPages - 1, $currentPage + $side); $i++)
                    <a href="?page={{ $i }}" class="{{ $currentPage == $i ? 'pointer-events-none' : '' }}">
                        <button class="px-3 py-2 rounded-md mx-1 font-semibold transition focus:outline-none {{ $currentPage == $i ? 'bg-blue-700 text-white shadow scale-110' : 'bg-white text-gray-700 hover:bg-blue-100' }}">{{ $i }}</button>
                    </a>
                @endfor
                {{-- Ellipsis after window --}}
                @if ($currentPage < $totalPages - $window + 1)
                    <span class="px-2 text-gray-400">...</span>
                @endif
                {{-- Last page --}}
                <a href="?page={{ $totalPages }}" class="{{ $currentPage == $totalPages ? 'pointer-events-none' : '' }}">
                    <button class="px-3 py-2 rounded-md mx-1 font-semibold transition focus:outline-none {{ $currentPage == $totalPages ? 'bg-blue-700 text-white shadow scale-110' : 'bg-white text-gray-700 hover:bg-blue-100' }}">{{ $totalPages }}</button>
                </a>
            @endif
            {{-- Next Button --}}
            <a href="?page={{ min($totalPages, $currentPage + 1) }}" class="{{ $currentPage == $totalPages ? 'pointer-events-none opacity-50' : '' }}">
                <button class="px-4 py-2 rounded-md bg-[var(--color-accent)] text-white font-semibold transition hover:bg-[#00bfae] focus:outline-none">Next</button>
            </a>
        </div>
    </div>
    <style>
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            margin: 0 auto 2rem auto;
            margin-top: 20px;
        }
        .animate-fade-in {
            animation: fadeInUp 0.7s cubic-bezier(.39,.575,.565,1.000);
        }
        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .pagination button.scale-110 {
            transform: scale(1.13) !important;
            z-index: 2;
            box-shadow: 0 4px 16px rgba(28,58,169,0.18);
            border: 2.5px solid var(--color-accent, #00bfae);
        }
        .pagination button {
            transition: all 0.18s cubic-bezier(.39,.575,.565,1.000);
        }
    </style>
</div>

{{-- ✅ Voice Search + Suggestions --}}
<script>
// --- Query Method Highlight ---
const methodCheckboxes = [
    document.getElementById('method_boolean'),
    document.getElementById('method_fuzzy'),
    document.getElementById('method_phrase')
];
const methodLabels = [
    document.getElementById('label_boolean'),
    document.getElementById('label_fuzzy'),
    document.getElementById('label_phrase')
];
methodCheckboxes.forEach((cb, idx) => {
    cb.addEventListener('change', function() {
        if (cb.checked) {
            // Uncheck others
            methodCheckboxes.forEach((other, i) => {
                if (other !== cb) {
                    other.checked = false;
                    methodLabels[i].classList.remove('query-method-active');
                }
            });
            methodLabels[idx].classList.add('query-method-active');
        } else {
            methodLabels[idx].classList.remove('query-method-active');
        }
    });
});
// On page load, highlight the checked one (if any)
window.addEventListener('DOMContentLoaded', () => {
    methodCheckboxes.forEach((cb, idx) => {
        if (cb.checked) methodLabels[idx].classList.add('query-method-active');
        else methodLabels[idx].classList.remove('query-method-active');
    });
});

// --- Mic Highlight ---
function startVoiceSearch() {
    const micBtn = document.getElementById('micBtn');
    micBtn.classList.add('mic-active');
    const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
    recognition.lang = "en-US";
    recognition.start();
    recognition.onresult = function(event) {
        document.getElementById("searchInput").value = event.results[0][0].transcript;
        micBtn.classList.remove('mic-active');
        searchMovies();
    };
    recognition.onend = function() {
        micBtn.classList.remove('mic-active');
    };
    recognition.onerror = function() {
        micBtn.classList.remove('mic-active');
    };
}

function searchMovies() {
    const input = document.getElementById("searchInput");
    const query = input.value.trim();
    // Check which query method is checked (priority: boolean > fuzzy > phrase)
    let method = document.getElementById("searchMethod").value;
    if (document.getElementById("method_boolean").checked) method = "boolean";
    else if (document.getElementById("method_fuzzy").checked) method = "fuzzy";
    else if (document.getElementById("method_phrase").checked) method = "phrase";
    if (query.length < 2) {
        showHistory();
        return;
    }
    window.location.href = `?q=${encodeURIComponent(query)}&method=${encodeURIComponent(method)}`;
}

function showHistory() {
    // Show history if input is empty
    if (searchInput.value.trim().length === 0) {
        fetch('/api/suggestions')
            .then(res => res.json())
            .then(data => {
                if (data.history && data.history.length) {
                    suggestionsContainer.innerHTML = '<div style="margin-bottom:6px;color:#888;font-size:13px;">Recent Searches</div>' +
                        data.history.map(h => `<button class=\"tag\" onclick=\"setSearch('${h.replace(/'/g, "\\'")}')\">${h}</button>`).join('');
                }
            });
    }
}

// Highlight selected query method
function updateQueryMethodHighlight() {
    const methods = ['boolean', 'fuzzy', 'phrase'];
    methods.forEach(m => {
        const input = document.getElementById('method_' + m);
        const label = input.closest('label');
        if (input.checked) {
            label.classList.add('query-method-active');
        } else {
            label.classList.remove('query-method-active');
        }
    });
}

document.getElementById('method_boolean').addEventListener('change', updateQueryMethodHighlight);
document.getElementById('method_fuzzy').addEventListener('change', updateQueryMethodHighlight);
document.getElementById('method_phrase').addEventListener('change', updateQueryMethodHighlight);
// Initial highlight
updateQueryMethodHighlight();

const searchInput = document.getElementById("searchInput");
const suggestionsContainer = document.getElementById("suggestionsContainer");

searchInput.addEventListener("keydown", function(e) {
    const tags = suggestionsContainer.querySelectorAll('.tag');
    if (!tags.length) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchMovies();
        }
        return;
    }
    let idx = Array.from(tags).findIndex(tag => tag.classList.contains('active'));
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (idx < tags.length - 1) idx++;
        else idx = 0;
        tags.forEach(tag => tag.classList.remove('active'));
        tags[idx].classList.add('active');
        tags[idx].focus();
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (idx > 0) idx--;
        else idx = tags.length - 1;
        tags.forEach(tag => tag.classList.remove('active'));
        tags[idx].classList.add('active');
        tags[idx].focus();
    } else if (e.key === 'Enter') {
        if (idx !== -1) {
            e.preventDefault();
            setSearch(tags[idx].textContent);
        } else {
            e.preventDefault();
            searchMovies();
        }
    }
});

function renderSuggestions(suggestions) {
    if (Array.isArray(suggestions)) {
        suggestionsContainer.innerHTML = suggestions.map((tag, i) =>
            `<button class=\"tag\" tabindex=\"0\" onclick=\"setSearch('${tag.replace(/'/g, "\\'")}')\">${tag}</button>`
        ).join("");
        const firstTag = suggestionsContainer.querySelector('.tag');
        if (firstTag) firstTag.classList.add('active');
    } else if (suggestions.suggestions) {
        renderSuggestions(suggestions.suggestions);
    }
}

searchInput.addEventListener("input", async function () {
    const query = this.value.trim();
    if (query.length < 2) {
        showHistory();
        return;
    }
    try {
        const res = await fetch(`/api/suggestions?q=${encodeURIComponent(query)}`);
        const data = await res.json();
        if (data.error) {
            suggestionsContainer.innerHTML = `<p style='color:red;'>${data.error}</p>`;
            console.error('Suggestion error:', data);
            return;
        }
        renderSuggestions(data.suggestions || data);
    } catch (e) {
        suggestionsContainer.innerHTML = "<p>Error loading suggestions.</p>";
        console.error('Fetch error:', e);
    }
});

function setSearch(text) {
    searchInput.value = text;
    suggestionsContainer.innerHTML = "";
    let category = '';
    const activeFilter = document.querySelector('.sidebar ul li a.active, .sidebar ul li a[style*="font-weight: bold"]');
    if (activeFilter) {
        category = activeFilter.textContent.trim();
    }
    let q = text;
    if (category && category.toLowerCase() !== 'all') {
        q = category + ' ' + text;
    }
    const method = document.querySelector('input[name="searchMethod"]:checked').value;
    window.location.href = `/?q=${encodeURIComponent(q)}&method=${encodeURIComponent(method)}`;
}

// --- Dynamic highlight for voice input (if results are updated via JS) ---
function applyDynamicHighlight(query) {
    if (!query) return;
    const terms = query.trim().replace(/"/g, '').split(/\s+/).filter(Boolean);
    if (!terms.length) return;
    const pattern = new RegExp('(' + terms.map(t => t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join('|') + ')', 'gi');
    document.querySelectorAll('.movie-info').forEach(function(info) {
        ['h2', 'p.text-sm.text-gray-600'].forEach(function(sel) {
            info.querySelectorAll(sel).forEach(function(el) {
                let original = el.textContent;
                el.innerHTML = original.replace(pattern, function(m) {
                    return '<span class="highlight">' + m + '</span>';
                });
            });
        });
    });
}
// If you update results via JS, call: applyDynamicHighlight(document.getElementById('searchInput').value);
</script>

{{-- ✅ Styles --}}
<style>
.tag {
    background-color: #f3f4f6;
    border: 1px solid #ccc;
    border-radius: 20px;
    padding: 5px 12px;
    margin: 4px;
    cursor: pointer;
    display: inline-block;
    font-size: 14px;
    transition: background 0.18s, color 0.18s;
}
.tag:hover {
    background-color: #e2e8f0;
}

.pagination a button,
.pagination button {
    transition: all 0.15s;
    font-weight: 600;
    outline: none;
    background: #fff;
    color: #1C2541;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 1px 4px rgba(0,209,178,0.07);
    margin: 0 2px;
    padding: 0.6rem 1.2rem;
    min-width: 40px;
    min-height: 40px;
    font-size: 1.08rem;
    position: relative;
    z-index: 1;
}
.pagination a button:hover,
.pagination button:hover {
    background: var(--color-accent);
    color: #fff;
    border-color: var(--color-accent);
    box-shadow: 0 2px 8px rgba(0,209,178,0.13);
    transform: translateY(-2px) scale(1.04);
}
.pagination a.pointer-events-none button,
.pagination a.pointer-events-none button:hover {
    background: #e5e7eb;
    color: #b0b0b0;
    border-color: #e5e7eb;
    cursor: not-allowed;
    box-shadow: none;
    transform: none;
}
.pagination button.bg-blue-700 {
    background: #1C3AA9;
    color: #fff;
    border-color: #1C3AA9;
    box-shadow: 0 2px 8px rgba(28,58,169,0.13);
}

.query-method-label {
    border: 2px solid transparent;
    border-radius: 6px;
    padding: 2px 8px;
    transition: border 0.18s, background 0.18s;
    cursor: pointer;
}
.query-method-active {
    border: 2px solid var(--color-accent, #00bfae);
    background: #e0f7fa;
    color: #00796b;
}
.mic-btn {
    background: #fff;
    border: 2px solid #ccc;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    font-size: 1.3rem;
    transition: border 0.18s, box-shadow 0.18s;
    margin-right: 8px;
}
.mic-active {
    border: 2.5px solid var(--color-accent, #00bfae);
    box-shadow: 0 0 0 4px #00bfae33;
    background: #e0f7fa;
    color: #00796b;
    animation: micPulse 1s infinite alternate;
}
@keyframes micPulse {
    0% { box-shadow: 0 0 0 4px #00bfae33; }
    100% { box-shadow: 0 0 0 10px #00bfae22; }
}
.highlight {
    background: #ffe082;
    color: #222;
    border-radius: 3px;
    padding: 0 2px;
    font-weight: bold;
    transition: background 0.2s, color 0.2s;
}
.menubar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(90deg, #1c2541 80%, #22305a 100%);
    color: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 18px rgba(28, 37, 65, 0.13), 0 1.5px 8px rgba(28, 37, 65, 0.10);
    border: 1.5px solid #232e4a;
    padding: 0.7rem 1.5rem;
    margin: 1.5rem auto 2rem auto;
    max-width: 900px;
    font-size: 1.08rem;
    font-weight: 500;
    gap: 1.5rem;
    transition: box-shadow 0.18s, background 0.18s;
}
.menubar span {
    font-size: 1.25rem;
    font-weight: bold;
    letter-spacing: 1px;
    color: #fff;
    text-shadow: 0 1px 4px #232e4a;
}
.menubar .menu-links {
    display: flex;
    gap: 1.2rem;
}
.menubar .menu-links a {
    color: #fff;
    text-decoration: none;
    font-size: 1.08rem;
    font-weight: 500;
    padding: 6px 18px;
    border-radius: 8px;
    background: linear-gradient(90deg, #232e4a 60%, #1c2541 100%);
    box-shadow: 0 1px 4px rgba(28,37,65,0.10);
    border: 1.5px solid #22305a;
    transition: background 0.18s, color 0.18s, box-shadow 0.18s, border 0.18s;
    outline: none;
    position: relative;
}
.menubar .menu-links a:hover, .menubar .menu-links a.active {
    background: linear-gradient(90deg, #00bfae 0%, #1c2541 100%);
    color: #1c2541;
    font-weight: bold;
    border: 1.5px solid #00bfae;
    box-shadow: 0 2px 8px rgba(0,209,178,0.13);
    text-decoration: none;
}

/* Responsive Tweaks */
@media (max-width: 1100px) {
   
    .menubar {
        max-width: 68vw;
        padding: 0.7rem 0.7rem;
    }
}
@media (max-width: 900px) {
    .sidebar {
        width: 100vw !important;
        height: auto !important;
        min-height: unset !important;
        position: static !important;
        top: unset !important;
        float: none !important;
        box-shadow: none !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: flex-start !important;
        padding: 1rem 0.5rem !important;
    }
    .sidebar ul {
        display: flex;
        flex-direction: row;
        gap: 1rem;
    }
    .main-content {
        margin-left: 0 !important;
        padding: 1rem !important;
    }
    .movies-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    .menubar {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.7rem;
        max-width: 100vw;
        padding: 0.7rem 0.5rem;
    }
    .menubar .menu-links {
        flex-wrap: wrap;
        gap: 0.7rem;
    }
}
@media (max-width: 700px) {
    .menubar {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
        font-size: 1rem;
        padding: 0.5rem 0.3rem;
    }
    .menubar span {
        font-size: 1.1rem;
    }
    .menubar .menu-links {
        gap: 0.5rem;
    }
}
@media (max-width: 600px) {
    .movies-grid {
        grid-template-columns: 1fr !important;
    }
    .main-content {
        padding: 0.5rem !important;
    }
    .sidebar h3 {
        font-size: 1rem;
    }
    .menubar {
        font-size: 0.98rem;
        padding: 0.4rem 0.1rem;
    }
    .search-box input {
        font-size: 1rem;
    }
    .search-box {
        flex-direction: column;
        gap: 0.5rem;
        padding: 0.5rem;
    }
}

/* Movie Card Responsive Sizing */
.movies-grid {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 1.2rem;
}
@media (max-width: 1200px) {
    .movies-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 900px) {
    .movies-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 1rem;
    }
}
@media (max-width: 600px) {
    .movies-grid {
        grid-template-columns: 1fr !important;
        gap: 0.7rem;
    }
}
.movie-card {
    background-color: var(--color-card-bg);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.10);
    display: flex;
    flex-direction: column;
    align-items: stretch;
    min-height: 320px;
    max-height: 420px;
    height: 100%;
    transition: box-shadow 0.18s, transform 0.18s;
}
.movie-card:hover {
    box-shadow: 0 8px 24px rgba(0,209,178,0.18);
    transform: translateY(-6px) scale(1.04);
}
.movie-card img {
    width: 100%;
    aspect-ratio: 2/3;
    object-fit: cover;
    min-height: 180px;
    max-height: 260px;
    display: block;
}
@media (max-width: 900px) {
    .movie-card {
        min-height: 260px;
        max-height: 340px;
    }
    .movie-card img {
        min-height: 140px;
        max-height: 200px;
    }
}
@media (max-width: 600px) {
    .movie-card {
        min-height: 200px;
        max-height: 320px;
    }
    .movie-card img {
        min-height: 100px;
        max-height: 160px;
    }
}
.movie-info {
    flex: 1 1 auto;
    padding: 0.7rem 0.7rem 0.5rem 0.7rem;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}
.movie-info h2 {
    font-size: 1.1rem;
    margin: 0 0 0.3rem 0;
    min-height: 2.2em;
    line-height: 1.2;
    word-break: break-word;
}
@media (max-width: 600px) {
    .movie-info h2 {
        font-size: 1rem;
        min-height: 1.6em;
    }
}
</style>
</div>
@endsection
