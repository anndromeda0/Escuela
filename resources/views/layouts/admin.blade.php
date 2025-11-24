<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>@yield('title') - Panel Admin</title>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
  @include('partials.sidebar')
  <main class="p-4">
    @yield('content')
  </main>
</body>
</html>
