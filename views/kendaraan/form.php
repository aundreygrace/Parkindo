<?php
/**
 * views/kendaraan/form.php
 * Variabel: $kendaraan (null|array), $mode
 */
$isEdit  = $mode === 'edit';
$action  = $isEdit ? '?page=kendaraan&action=update' : '?page=kendaraan&action=store';
?>

<div class="max-w-lg mx-auto">
  <a href="?page=kendaraan&action=index"
     class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 mb-5 transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    Kembali ke Daftar Kendaraan
  </a>

  <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
      <h2 class="font-semibold text-slate-800"><?= $isEdit ? 'Edit Data Kendaraan' : 'Tambah Kendaraan Baru' ?></h2>
    </div>

    <form method="POST" action="<?= $action ?>" class="p-6 space-y-5">
      <?php if ($isEdit): ?>
      <input type="hidden" name="id_kendaraan" value="<?= $kendaraan['id_kendaraan'] ?>">
      <?php endif; ?>

      <!-- Plat Nomor -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">
          Plat Nomor <span class="text-red-500">*</span>
        </label>
        <input type="text" name="plat_nomor"
               value="<?= e($kendaraan['plat_nomor'] ?? '') ?>"
               placeholder="Contoh: AE 1234 AB"
               maxlength="15" required
               oninput="this.value = this.value.toUpperCase()"
               class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm font-mono
                      tracking-widest uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500">
      </div>

      <!-- Jenis & Warna -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">
            Jenis Kendaraan <span class="text-red-500">*</span>
          </label>
          <select name="jenis_kendaraan" required
                  class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <?php foreach (['motor','mobil','lainnya'] as $j): ?>
            <option value="<?= $j ?>"
                    <?= ($kendaraan['jenis_kendaraan'] ?? 'motor') === $j ? 'selected' : '' ?>>
              <?= ucfirst($j) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Warna</label>
          <input type="text" name="warna"
                 value="<?= e($kendaraan['warna'] ?? '') ?>"
                 placeholder="Hitam, Putih, Merah…"
                 class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
      </div>

      <!-- Pemilik & No HP -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">
            Nama Pemilik <span class="text-red-500">*</span>
          </label>
          <input type="text" name="pemilik"
                 value="<?= e($kendaraan['pemilik'] ?? '') ?>"
                 placeholder="Nama pemilik kendaraan"
                 required
                 class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">No. HP</label>
          <input type="text" name="no_hp"
                 value="<?= e($kendaraan['no_hp'] ?? '') ?>"
                 placeholder="08xxxxxxxxxx"
                 class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
      </div>

      <!-- Submit -->
      <div class="flex gap-3 pt-2 border-t border-slate-100">
        <button type="submit"
                class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors">
          <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Kendaraan' ?>
        </button>
        <a href="?page=kendaraan&action=index"
           class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium rounded-lg text-sm transition-colors">
          Batal
        </a>
      </div>
    </form>
  </div>
</div>