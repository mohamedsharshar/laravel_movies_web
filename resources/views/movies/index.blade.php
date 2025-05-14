@extends('layouts.app')

@section('content')
<div class="sidebar">
    <h3 class="text-white mb-4">🎛️ Filters</h3>
    <ul class="text-white space-y-2">
        <li><a href="{{ route('movies.category', ['category' => 'Action']) }}" class="{{ (isset($activeCategory) && strtolower($activeCategory) == 'action') ? 'active' : '' }} hover:underline">Action Movies</a></li>
        <li><a href="{{ route('movies.category', ['category' => 'Romance']) }}" class="{{ (isset($activeCategory) && strtolower($activeCategory) == 'romance') ? 'active' : '' }} hover:underline">Romance</a></li>
        <li><a href="{{ route('movies.category', ['category' => 'Comedy']) }}" class="{{ (isset($activeCategory) && strtolower($activeCategory) == 'comedy') ? 'active' : '' }} hover:underline">Comedy</a></li>
        <li><a href="{{ route('movies.category', ['category' => 'Documentary']) }}" class="{{ (isset($activeCategory) && strtolower($activeCategory) == 'documentary') ? 'active' : '' }} hover:underline">Documentary</a></li>
        <li><a href="{{ route('movies.category', ['category' => 'Sci-Fi']) }}" class="{{ (isset($activeCategory) && strtolower($activeCategory) == 'sci-fi') ? 'active' : '' }} hover:underline">Sci-Fi</a></li>
    </ul>
</div>

<!-- Responsive Menubar for mobile/tablet -->
<div class="menubar">
    <span style="font-weight:bold; font-size:1.1rem;">🎬 Movie App</span>
    <div class="menu-links">
        <a href="{{ route('movies.index') }}">Home</a>
        <a href="{{ route('movies.category', ['category' => 'Popular']) }}">Popular</a>
        <a href="{{ route('movies.category', ['category' => 'Documentary']) }}">Contact Us</a>
    </div>
</div>

<div class="main-content">
    <h1 class="text-2xl font-bold text-center mb-6">🎬 Popular Movies</h1>

    {{-- ✅ Search Box with Voice --}}
    <div class="search-box">
        <input type="text" placeholder="Search for a movie..." id="searchInput" autocomplete="on" onfocus="showHistory()">
        <button class="mic-btn" onclick="startVoiceSearch()" title="Voice Search">🎤</button>
        <button class="search-btn" onclick="searchMovies()" title="Search">🔍 Search</button>
    </div>

    {{-- ✅ Suggestions --}}
    <div class="suggestions mb-4" id="suggestionsContainer"></div>

    {{-- ✅ Movies Grid --}}
    <div class="movies-grid" style="grid-template-columns: repeat(7, 1fr);">
        @foreach($movies as $movie)
            <div class="movie-card">
                <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" alt="{{ $movie['title'] }}">
                <div class="movie-info">
                    <h2>{{ $movie['title'] }}</h2>
                    <p>Release Date: {{ $movie['release_date'] }}</p>
                    <p class="text-sm">Rating ⭐ {{ $movie['vote_average'] }}/10</p>
                    <p class="text-sm text-gray-600 mt-2">{{ \Illuminate\Support\Str::limit($movie['overview'], 100) }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ✅ Pagination --}}
    @php
        $side = 2; // how many pages to show on each side of current
        $window = $side * 2;
    @endphp
    <div class="pagination flex justify-center items-center gap-2 mt-8 mb-4">
        {{-- Previous Button --}}
        <a href="?page={{ max(1, $currentPage - 1) }}" class="{{ $currentPage == 1 ? 'pointer-events-none opacity-50' : '' }}">
            <button class="px-4 py-2 rounded-md bg-[var(--color-accent)] text-white font-semibold transition hover:bg-[#00bfae] focus:outline-none">Previous</button>
        </a>
        {{-- Page Numbers --}}
        @if ($totalPages <= $window + 4)
            @for ($i = 1; $i <= $totalPages; $i++)
                <a href="?page={{ $i }}" class="{{ $currentPage == $i ? 'pointer-events-none' : '' }}">
                    <button class="px-3 py-2 rounded-md mx-1 font-semibold transition focus:outline-none {{ $currentPage == $i ? 'bg-blue-700 text-white shadow' : 'bg-white text-gray-700 hover:bg-blue-100' }}">{{ $i }}</button>
                </a>
            @endfor
        @else
            {{-- First page --}}
            <a href="?page=1" class="{{ $currentPage == 1 ? 'pointer-events-none' : '' }}">
                <button class="px-3 py-2 rounded-md mx-1 font-semibold transition focus:outline-none {{ $currentPage == 1 ? 'bg-blue-700 text-white shadow' : 'bg-white text-gray-700 hover:bg-blue-100' }}">1</button>
            </a>
            {{-- Ellipsis before window --}}
            @if ($currentPage > $window)
                <span class="px-2 text-gray-400">...</span>
            @endif
            {{-- Page window --}}
            @for ($i = max(2, $currentPage - $side); $i <= min($totalPages - 1, $currentPage + $side); $i++)
                <a href="?page={{ $i }}" class="{{ $currentPage == $i ? 'pointer-events-none' : '' }}">
                    <button class="px-3 py-2 rounded-md mx-1 font-semibold transition focus:outline-none {{ $currentPage == $i ? 'bg-blue-700 text-white shadow' : 'bg-white text-gray-700 hover:bg-blue-100' }}">{{ $i }}</button>
                </a>
            @endfor
            {{-- Ellipsis after window --}}
            @if ($currentPage < $totalPages - $window + 1)
                <span class="px-2 text-gray-400">...</span>
            @endif
            {{-- Last page --}}
            <a href="?page={{ $totalPages }}" class="{{ $currentPage == $totalPages ? 'pointer-events-none' : '' }}">
                <button class="px-3 py-2 rounded-md mx-1 font-semibold transition focus:outline-none {{ $currentPage == $totalPages ? 'bg-blue-700 text-white shadow' : 'bg-white text-gray-700 hover:bg-blue-100' }}">{{ $totalPages }}</button>
            </a>
        @endif
        {{-- Next Button --}}
        <a href="?page={{ min($totalPages, $currentPage + 1) }}" class="{{ $currentPage == $totalPages ? 'pointer-events-none opacity-50' : '' }}">
            <button class="px-4 py-2 rounded-md bg-[var(--color-accent)] text-white font-semibold transition hover:bg-[#00bfae] focus:outline-none">Next</button>
        </a>
    </div>
</div>

{{-- ✅ Voice Search + Suggestions --}}
<script>
function startVoiceSearch() {
    const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
    recognition.lang = "en-US";
    recognition.start();
    recognition.onresult = function(event) {
        document.getElementById("searchInput").value = event.results[0][0].transcript;
        searchMovies();
    };
}

function searchMovies() {
    const input = document.getElementById("searchInput");
    const query = input.value.trim();
    if (query.length < 2) {
        showHistory();
        return;
    }
    // Redirect to the same page with ?q=... (or you can use AJAX to update results)
    window.location.href = `?q=${encodeURIComponent(query)}`;
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

const searchInput = document.getElementById("searchInput");
const suggestionsContainer = document.getElementById("suggestionsContainer");

searchInput.addEventListener("keydown", function(e) {
    const tags = suggestionsContainer.querySelectorAll('.tag');
    if (!tags.length) return;
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
            // No suggestion selected, trigger search
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
    // Get selected category from sidebar (if any)
    let category = '';
    const activeFilter = document.querySelector('.sidebar ul li a.active, .sidebar ul li a[style*="font-weight: bold"]');
    if (activeFilter) {
        category = activeFilter.textContent.trim();
    }
    // Redirect to main search page with both category and suggestion as query
    let q = text;
    if (category && category.toLowerCase() !== 'all') {
        q = category + ' ' + text;
    }
    window.location.href = '/?q=' + encodeURIComponent(q);
}
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
    z-index: 2;
}
.pagination span {
    color: #b0b0b0;
    font-size: 1.2rem;
    padding: 0 0.5rem;
    user-select: none;
}
@media (max-width: 600px) {
    .pagination a button, .pagination button {
        padding: 0.4rem 0.7rem;
        min-width: 32px;
        min-height: 32px;
        font-size: 0.98rem;
    }
}
@media (max-width: 900px) {
    .sidebar {
        display: none !important;
    }
    .main-content {
        margin-left: 0 !important;
        padding: 1rem !important;
    }
    .movies-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    .search-box {
        max-width: 100% !important;
        flex-direction: column;
        gap: 0.5rem;
    }
    .menubar {
        display: flex !important;
    }
}
@media (max-width: 600px) {
    .movies-grid {
        grid-template-columns: 1fr !important;
    }
}
.menubar {
    display: none;
    background: var(--color-sidebar-bg);
    color: #fff;
    padding: 1rem;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 20;
}
.menubar .menu-links {
    display: flex;
    gap: 1.2rem;
}
.menubar .menu-links a {
    color: #fff;
    text-decoration: none;
    font-size: 1.08rem;
    padding: 0.4rem 1rem;
    border-radius: 6px;
    transition: background 0.18s, color 0.18s;
}
.menubar .menu-links a:hover {
    background: var(--color-accent);
    color: #1C2541;
}
@media (max-width: 900px) {
    .sidebar {
        position: static !important;
        width: 100vw !important;
        min-height: unset !important;
        height: auto !important;
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
    .search-box {
        max-width: 100% !important;
        flex-direction: column;
        gap: 0.5rem;
    }
}
@media (max-width: 600px) {
    .movies-grid {
        grid-template-columns: 1fr !important;
    }
    .search-box input {
        font-size: 1rem;
    }
    .sidebar h3 {
        font-size: 1rem;
    }
}
</style>
@endsection
