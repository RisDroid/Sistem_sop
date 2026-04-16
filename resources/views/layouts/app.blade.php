<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }

        .page-shell {
            min-height: 100vh;
        }

        .page-header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.5rem;
        }

        .page-body {
            max-width: 1100px;
            margin: 0 auto;
            padding: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="page-shell">
        @include('layouts.navigation')

        @isset($header)
            <header class="page-header">
                {{ $header }}
            </header>
        @endisset

        <main class="page-body">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
