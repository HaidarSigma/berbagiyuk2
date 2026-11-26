<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('judul') - BerbagiYuk</title>
  @include('partials.head-asset')
  <style>
    body {
      background-color: #dcefe1;
      background-image:
        radial-gradient(ellipse at 8% 12%, rgba(74, 222, 128, 0.45), transparent 42%),
        radial-gradient(ellipse at 92% 78%, rgba(52, 211, 153, 0.45), transparent 40%),
        linear-gradient(135deg, #dcefe1 0%, #e8f5eb 48%, #d4ebdc 100%);
      background-attachment: fixed;
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-sm p-8 w-full max-w-sm space-y-4">
    @yield('isi')
  </div>
  @include('partials.interaksi')
  @include('partials.tombol-tema', ['melayang' => true])
</body>
</html>