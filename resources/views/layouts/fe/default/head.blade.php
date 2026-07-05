<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $setting?->name ?? config('app.name', 'LaravelAdmin') }}</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

  <!-- Tailwind CSS v4 (CDN) -->
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

  <!-- Motion.js for animations (CDN) -->
  <script src="https://cdn.jsdelivr.net/npm/motion@latest/dist/motion.js"></script>

  <!-- Style kustom (diekstrak dari template monolitik) -->
  <link rel="stylesheet" href="{{ asset('fe/default/css/style.css') }}">
</head>
