<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تطبيق الأفلام</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body>
    {{-- ✅ Navbar --}}
    <nav class="navbar">
        <div class="container" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
            <h2 class="logo" style="font-size: 1.5rem; font-weight: bold; color: white; margin: 0; flex-shrink: 0;">🎬 Movie App</h2>
            <ul class="nav-links" style="list-style: none; display: flex; gap: 1.5rem; margin: 0; padding: 0; flex-wrap: wrap;">
                <li><a href="{{ route('movies.index') }}">Home</a></li>
                <li><a href={{ route('movies.popular') }}>Popular</a></li>
                <li><a href="#">Contact Us</a></li>
            </ul>
            @auth
            <div class="user-menu" style="position: relative; margin-left: 1.5rem;">
                <button id="userDropdownBtn" style="background: none; border: none; cursor: pointer; display: flex; align-items: center; color: #fff; font-size: 1.2rem;">
                    @if(Auth::user()->avatar && file_exists(public_path('storage/' . Auth::user()->avatar)))
                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar" style="width:32px;height:32px;border-radius:50%;object-fit:cover;margin-right:8px;border:2px solid #ffe082;background:#fff;">
                    @else
                        <span style="font-size: 1.6rem; margin-right: 8px;">👤</span>
                    @endif
                    <span style="font-weight: 500; font-size: 1.05rem;">{{ Auth::user()->name ?? 'User' }}</span>
                    <svg style="margin-left: 4px; width: 16px; height: 16px; fill: #fff;" viewBox="0 0 20 20"><path d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.06l3.71-3.83a.75.75 0 1 1 1.08 1.04l-4.25 4.39a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06z"/></svg>
                </button>
                <div id="userDropdown" style="display: none; position: absolute; right: 0; top: 120%; background: #232e4a; color: #fff; border-radius: 10px; box-shadow: 0 4px 18px rgba(28, 37, 65, 0.13); min-width: 150px; z-index: 100;">
                    <a href="{{ route('profile') }}" style="display: block; padding: 10px 18px; color: #fff; text-decoration: none; border-bottom: 1px solid #2d3957; transition: background 0.15s;">👤 Profile</a>
                    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                        @csrf
                        <button type="submit" style="width: 100%; background: none; border: none; color: #fff; padding: 10px 18px; text-align: left; cursor: pointer; transition: background 0.15s;">🚪 Logout</button>
                    </form>
                </div>
            </div>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var btn = document.getElementById('userDropdownBtn');
                var menu = document.getElementById('userDropdown');
                if(btn && menu) {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
                    });
                    document.addEventListener('click', function() {
                        menu.style.display = 'none';
                    });
                }
            });
            </script>
            @else
            <ul class="nav-links" style="list-style: none; display: flex; gap: 1.2rem; margin: 0; padding: 0; flex-wrap: wrap;">
                <li><a href="{{ route('login') }}">Login</a></li>
                <li><a href="{{ route('register') }}">Register</a></li>
            </ul>
            @endauth
        </div>
    </nav>

    <style>
    @media (max-width: 900px) {
        .navbar .container {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        .navbar .logo {
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }
        .nav-links {
            flex-direction: column;
            gap: 0.5rem;
            width: 100%;
        }
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

    {{-- ✅ محتوى الصفحات --}}
    @yield('content')
</body>
</html>
