<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Back-Office — SI-TAMU</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex items-center justify-center p-4 sm:p-6">
    <main class="w-full max-w-md bg-white rounded-xl shadow-xs border border-slate-200 p-6 sm:p-8">
        <div class="text-center mb-6">
            <div class="mb-3">
                <img src="{{ asset('assets/logo.webp') }}" alt="Logo DPRD Kota Binjai" class="h-14 w-auto mx-auto object-contain drop-shadow-xs">
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">SI-TAMU Back-Office</h1>
            <p class="text-sm font-medium text-slate-600 mt-1">Sekretariat DPRD Kota Binjai</p>
            <p class="text-xs text-slate-500 mt-0.5">Sistem Informasi Buku Tamu Digital</p>
        </div>

        @if ($errors->any())
            <div id="login-errors" role="alert" tabindex="-1" class="mb-5 p-4 rounded-xl bg-danger-50 border border-danger-200 text-danger-800 text-sm">
                <div class="font-semibold mb-1">Gagal Masuk:</div>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Alamat Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm transition @error('email') border-danger-400 @enderror"
                    placeholder="nama@instansi.go.id"
                >
                @error('email')
                    <p id="email-error" class="mt-1.5 text-xs font-medium text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div x-data="{ showPassword: false }">
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Kata Sandi</label>
                <div class="relative">
                    <input
                        :type="showPassword ? 'text' : 'password'"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                        class="w-full px-3.5 py-2.5 pr-12 rounded-xl border border-slate-300 text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm transition @error('password') border-danger-400 @enderror"
                        placeholder="••••••••"
                    >
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        :aria-pressed="showPassword"
                        aria-controls="password"
                        :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                        :title="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                        class="absolute inset-y-0 right-0 w-11 flex items-center justify-center rounded-r-xl text-slate-500 hover:text-primary-600 transition cursor-pointer focus:outline-hidden focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary-500"
                    >
                        <svg x-show="!showPassword" aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showPassword" x-cloak aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p id="password-error" class="mt-1.5 text-xs font-medium text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 min-h-11 -my-1 cursor-pointer text-slate-600">
                    <input type="checkbox" name="remember" value="1" class="w-5 h-5 rounded border-slate-300 text-primary-600 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                    <span>Ingat saya</span>
                </label>
            </div>

            <button
                type="submit"
                class="w-full min-h-11 py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl shadow-xs transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 text-sm cursor-pointer"
            >
                Masuk ke Sistem
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-100 text-center">
            <a
                href="{{ route('public.registration.create') }}"
                class="inline-flex items-center justify-center gap-2 w-full min-h-11 px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm border border-slate-300 shadow-2xs transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
            >
                <svg aria-hidden="true" class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Buka Form Tamu
            </a>
            <p class="mt-4 text-xs text-slate-500">
                &copy; {{ date('Y') }} SI-TAMU — Akses terbatas hanya untuk petugas dan administrator berwenang.
            </p>
        </div>
    </main>
</body>
</html>
