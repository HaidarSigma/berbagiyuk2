<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Masuk - BerbagiYuk</title>
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
  <form method="POST" action="/masuk" class="bg-white rounded-2xl shadow-sm p-8 w-full max-w-sm space-y-4">
    @csrf
    <h1 class="text-2xl font-bold text-green-800">Masuk</h1>
    @if (session('status')) <p class="text-sm text-green-700">{{ session('status') }}</p> @endif
    @error('email') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" class="w-full border rounded-lg px-3 py-2" required>
    <input type="password" name="password" placeholder="Password" class="w-full border rounded-lg px-3 py-2" required>
    <label class="flex items-center gap-2 text-sm text-gray-600">
      <input type="checkbox" name="ingat_saya" value="1" class="rounded border-gray-300 text-green-800 focus:ring-green-800" {{ old('ingat_saya') ? 'checked' : '' }}>
      Ingatkan saya
    </label>
    <button class="w-full bg-green-800 text-white rounded-lg py-2 font-semibold">Masuk</button>
    <p class="text-sm text-center"><a href="/lupa" class="text-green-800">Lupa password?</a></p>
    <p class="text-sm text-center text-gray-500">Belum punya akun? <a href="/daftar" class="text-green-800">Daftar</a></p>
  </form>
</body>
</html>