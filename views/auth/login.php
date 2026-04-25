<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — <?= APP_NAME ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <style>
    [x-cloak] { display: none !important; }
    .bg-grid {
      background-color: #0f172a;
      background-image: radial-gradient(circle at 1px 1px, rgba(148,163,184,0.08) 1px, transparent 0);
      background-size: 32px 32px;
    }
  </style>
</head>

<body class="bg-grid min-h-screen flex items-center justify-center p-4">

  <div class="w-full max-w-md" x-data="loginForm()">

    <!-- Logo + Judul -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-600 rounded-2xl mb-4 shadow-lg shadow-indigo-600/30">
        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12h12l1-12M10 12h4"/>
        </svg>
      </div>
      <h1 class="text-2xl font-bold text-white"><?= APP_NAME ?></h1>
      <p class="text-slate-400 text-sm mt-1"><?= APP_TAGLINE ?></p>
    </div>

    <!-- Card Form -->
    <div class="bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl p-8">

      <!-- Flash Message -->
      <?php if ($flash): ?>
      <div class="mb-5 px-4 py-3 rounded-lg text-sm font-medium
                  <?= $flash['type'] === 'error'   ? 'bg-red-900/50 text-red-300 border border-red-700' : '' ?>
                  <?= $flash['type'] === 'success' ? 'bg-green-900/50 text-green-300 border border-green-700' : '' ?>
                  <?= $flash['type'] === 'warning' ? 'bg-amber-900/50 text-amber-300 border border-amber-700' : '' ?>">
        <?= e($flash['message']) ?>
      </div>
      <?php endif; ?>

      <h2 class="text-white font-semibold text-lg mb-6">Masuk ke Akun Anda</h2>

      <form method="POST" action="?page=login" @submit="loading = true" novalidate>

        <!-- Username -->
        <div class="mb-4">
          <label class="block text-slate-300 text-sm font-medium mb-1.5" for="username">
            Username
          </label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </span>
            <input
              type="text"
              id="username"
              name="username"
              value="<?= e($_POST['username'] ?? '') ?>"
              placeholder="Masukkan username"
              required
              autocomplete="username"
              class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg
                     pl-10 pr-4 py-2.5 placeholder-slate-500
                     focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                     transition-all duration-150">
          </div>
        </div>

        <!-- Password -->
        <div class="mb-6">
          <label class="block text-slate-300 text-sm font-medium mb-1.5" for="password">
            Password
          </label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
            </span>
            <input
              :type="showPassword ? 'text' : 'password'"
              id="password"
              name="password"
              placeholder="Masukkan password"
              required
              autocomplete="current-password"
              class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg
                     pl-10 pr-10 py-2.5 placeholder-slate-500
                     focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                     transition-all duration-150">
            <!-- Toggle show/hide password -->
            <button type="button" @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors">
              <svg x-show="!showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:bg-indigo-800 disabled:cursor-not-allowed
                 text-white font-semibold py-2.5 px-4 rounded-lg text-sm
                 transition-all duration-150 flex items-center justify-center gap-2">
          <svg x-show="loading" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          <span x-text="loading ? 'Memproses...' : 'Masuk'">Masuk</span>
        </button>

      </form>
    </div>

    <!-- Footer -->
    <p class="text-center text-slate-600 text-xs mt-6">
      <?= APP_NAME ?> v<?= APP_VERSION ?> &copy; <?= date('Y') ?>
    </p>
  </div>

  <script>
    function loginForm() {
      return {
        loading: false,
        showPassword: false,
      }
    }
  </script>
</body>
</html>