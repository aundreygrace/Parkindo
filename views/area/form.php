<?php
/**
 * views/area/form.php
 * Variabel: $area (null|array), $mode
 */
$isEdit = $mode === 'edit';
$action = $isEdit ? '?page=area&action=update' : '?page=area&action=store';
?>
<div class="max-w-md mx-auto">
  <a href="?page=area&action=index"
     class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 mb-5 transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    Kembali ke Daftar Area
  </a>

  <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
      <h2 class="font-semibold text-slate-800"><?= $isEdit ? 'Edit Area Parkir' : 'Tambah Area Parkir Baru' ?></h2>
    </div>
    <form method="POST" action="<?= $action ?>" class="p-6 space-y-5">
      <?php if ($isEdit): ?>
      <input type="hidden" name="id_area" value="<?= $area['id_area'] ?>">
      <?php endif; ?>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">
          Nama Area <span class="text-red-500">*</span>
        </label>
        <input type="text" name="nama_area"
               value="<?= e($area['nama_area'] ?? '') ?>"
               placeholder="Contoh: Area A — Motor"
               required
               class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">
          Kapasitas (slot) <span class="text-red-500">*</span>
        </label>
        <input type="number" name="kapasitas"
               value="<?= e($area['kapasitas'] ?? '') ?>"
               min="1" max="9999" required
               placeholder="Jumlah maksimal kendaraan"
               class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <?php if ($isEdit && $area['terisi'] > 0): ?>
        <p class="text-xs text-amber-600 mt-1">
          Saat ini <?= $area['terisi'] ?> slot terisi. Kapasitas baru tidak boleh kurang dari angka ini.
        </p>
        <?php endif; ?>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Keterangan</label>
        <input type="text" name="keterangan"
               value="<?= e($area['keterangan'] ?? '') ?>"
               placeholder="Contoh: Parkir motor lantai 1"
               class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
      </div>

      <div class="flex gap-3 pt-2 border-t border-slate-100">
        <button type="submit"
                class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors">
          <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Area' ?>
        </button>
        <a href="?page=area&action=index"
           class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium rounded-lg text-sm transition-colors">
          Batal
        </a>
      </div>
    </form>
  </div>
</div>