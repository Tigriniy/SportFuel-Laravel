<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — SportFuel</title>
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
</head>
<body>
    <header class="admin-header">
        <a class="admin-logo" href="{{ route('admin.home') }}">SportFuel · Управление каталогом</a>
        <nav aria-label="Управление каталогом">
            <a href="{{ route('admin.products.index') }}">Товары</a>
            <a href="{{ route('admin.references.index', 'categories') }}">Категории</a>
            <a href="{{ route('admin.references.index', 'brands') }}">Бренды</a>
            <a href="{{ route('catalog') }}">Магазин</a>
            <a href="{{ route('profile') }}">Личный кабинет</a>
        </nav>
    </header>
    <main class="admin-main">
        <h1>@yield('title')</h1>

        @if (session('status'))
            <p class="notice" role="status">{{ session('status') }}</p>
        @endif
        @if ($errors->any())
            <ul class="notice notice-error" role="alert">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        @yield('content')
    </main>
</body>
</html>
