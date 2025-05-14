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
                <li><a href="#">Popular</a></li>
                <li><a href="#">Contact Us</a></li>
            </ul>
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
