<?php
/**
 * views/user/form.php
 * Variabel: $user (null = create, array = edit), $mode
 */
$isEdit  = $mode === 'edit';
$action  = $isEdit ? '?page=user&action=update' : '?page=user&action=store';
$backUrl = '?page=user&action=index';
?>

<div class="max-w-lg mx-auto">
  <a href="<?= $backUrl ?>" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 mb-5 transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    Kembali ke Daftar User
  </a>

  <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
      <h2 class="font-semibold text-slate-800"><?= $isEdit ? 'Edit User' : 'Tambah User Baru' ?></h2>
      <p class="text-xs text-slate-400 mt-0.5"><?= $isEdit ? 'Kosongkan password jika tidak ingin mengubahnya' : 'Isi semua field yang diperlukan' ?></p>
    </div>

    <form method="POST" action="<?= $action ?>" class="p-6 space-y-5">
      <?php if ($isEdit): ?>
      <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">
      <?php endif; ?>

      <!-- Nama Lengkap -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">
          Nama Lengkap <span class="text-red-500">*</span>
        </label>
        <input type="text" name="nama_lengkap"
               value="<?= e($user['nama_lengkap'] ?? '') ?>"
               placeholder="Nama lengkap pengguna"
               required
               class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
      </div>

      <!-- Username -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">
          Username <span class="text-red-500">*</span>
        </label>
        <input type="text" name="username"
               value="<?= e($user['username'] ?? '') ?>"
               placeholder="Nama pengguna untuk login"
               required autocomplete="off"
               class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <p class="text-xs text-slate-400 mt-1">Hanya huruf, angka, dan underscore. Tanpa spasi.</p>
      </div>

      <!-- Password -->
      <div x-data="{ show: false }">
        <label class="block text-sm font-medium text-slate-700 mb-1.5">
          Password <?= !$isEdit ? '<span class="text-red-500">*</span>' : '' ?>
        </label>
        <div class="relative">
          <input :type="show ? 'text' : 'password'" name="password"
                 placeholder="<?= $isEdit ? 'Kosongkan jika tidak diubah' : 'Minimal 6 karakter' ?>"
                 <?= !$isEdit ? 'required' : '' ?>
                 autocomplete="new-password"
                 class="w-full border border-slate-200 rounded-lg px-4 pr-10 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <button type="button" @click="show = !show"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Role -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">
          Role <span class="text-red-500">*</span>
        </label>
        <div class="grid grid-cols-3 gap-3">
          <?php
          $roles = [
            'admin'   => ['label'=>'Admin',   'desc'=>'Akses penuh CRUD','color'=>'peer-checked:border-purple-500 peer-checked:bg-purple-50'],
            'petugas' => ['label'=>'Petugas', 'desc'=>'Input transaksi',  'color'=>'peer-checked:border-indigo-500 peer-checked:bg-indigo-50'],
            'owner'   => ['label'=>'Owner',   'desc'=>'Lihat laporan',    'color'=>'peer-checked:border-amber-500 peer-checked:bg-amber-50'],
          ];
          foreach ($roles as $val => $r):
            $checked = ($user['role'] ?? 'petugas') === $val;
          ?>
          <label class="cursor-pointer">
            <input type="radio" name="role" value="<?= $val ?>"
                   <?= $checked ? 'checked' : '' ?> class="peer sr-only">
            <div class="border-2 border-slate-200 <?= $r['color'] ?> rounded-xl p-3 transition-all text-center">
              <p class="text-sm font-semibold text-slate-700"><?= $r['label'] ?></p>
              <p class="text-xs text-slate-400 mt-0.5"><?= $r['desc'] ?></p>
            </div>
          </label>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Status (hanya edit) -->
      <?php if ($isEdit): ?>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Status Akun</label>
        <div class="flex gap-3">
          <?php foreach ([1=>'Aktif', 0=>'Nonaktif'] as $val => $lbl): ?>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" name="status_aktif" value="<?= $val ?>"
                   <?= (int)($user['status_aktif'] ?? 1) === $val ? 'checked' : '' ?>
                   class="text-indigo-600 focus:ring-indigo-500">
            <span class="text-sm text-slate-700"><?= $lbl ?></span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Submit -->
      <div class="flex gap-3 pt-2 border-t border-slate-100">
        <button type="submit"
                class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors">
          <?= $isEdit ? 'Simpan Perubahan' : 'Tambah User' ?>
        </button>
        <a href="<?= $backUrl ?>"
           class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium rounded-lg text-sm transition-colors">
          Batal
        </a>
      </div>
    </form>
  </div>
</div>