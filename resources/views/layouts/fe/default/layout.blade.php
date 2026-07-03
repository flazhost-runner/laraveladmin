<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $setting?->name ?? 'LaravelAdmin' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: {} } }</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/fe/default/css/style.css">
    @stack('head')
</head>
<body>
    @yield('content')
    <script src="/fe/default/js/motion.js"></script>
    @stack('scripts')
</body>
</html>
