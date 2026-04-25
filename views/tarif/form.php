<?php
/**
 * views/tarif/form.php
 * Variabel: $tarif (null = create, array = edit), $mode ('create'|'edit')
 *           $existingJenis (array) — hanya untuk mode create
 */
$isCreate = ($mode ?? 'edit') === 'create';
$action   = $isCreate ? '?page=tarif&action=store' : '?page=tarif&action=update';
$title    = $isCreate ? 'Tambah Tarif Baru' : 'Edit Tarif — ' . ucfirst(e($tarif['jenis_kendaraan']));

// Semua jenis yang mungkin
$allJenis      = ['motor', 'mobil', 'lainnya'];
$existingJenis = $existingJenis ?? [];
// Untuk create: hanya jenis yang belum ada; untuk edit: semua
$availableJenis = $isCreate
    ? array_diff($allJenis, $existingJenis)
    : $allJenis;

$jenisLabel = ['motor' => 'Motor', 'mobil' => 'Mobil', 'lainnya' => 'Lainnya (Roda > 4)'];
?>
<div class="max-w-md mx-auto">
  <a href="?page=tarif&action=index"
     class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 mb-5 transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    Kembali ke Daftar Tarif
  </a>

  <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
      <h2 class="font-semibold text-slate-800"><?= $title ?></h2>
      <p class="text-xs text-slate-400 mt-0.5">
        <?= $isCreate
            ? 'Tambah tarif baru untuk jenis kendaraan yang belum terdaftar'
            : 'Perubahan langsung berlaku untuk transaksi berikutnya' ?>
      </p>
    </div>

    <?php if ($isCreate && empty($availableJenis)): ?>
    <!-- Semua jenis sudah ada -->
    <div class="p-6">
      <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:10px;padding:16px 18px;display:flex;gap:12px;align-items:flex-start">
        <svg style="width:20px;height:20px;color:#d97706;flex-shrink:0;margin-top:1px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <div>
          <p style="font-size:14px;font-weight:600;color:#92400e;margin:0 0 4px">Semua jenis kendaraan sudah memiliki tarif</p>
          <p style="font-size:13px;color:#b45309;margin:0">Gunakan tombol <strong>Edit</strong> pada daftar tarif untuk mengubah tarif yang sudah ada.</p>
        </div>
      </div>
      <div class="mt-5">
        <a href="?page=tarif&action=index"
           class="inline-block px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium rounded-lg text-sm transition-colors">
          Kembali ke Daftar
        </a>
      </div>
    </div>

    <?php else: ?>
    <form method="POST" action="<?= $action ?>" class="p-6 space-y-5">
      <?php if (!$isCreate): ?>
        <input type="hidden" name="id_tarif" value="<?= $tarif['id_tarif'] ?>">
      <?php endif; ?>

      <!-- Jenis Kendaraan -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">
          Jenis Kendaraan <span class="text-red-500">*</span>
        </label>
        <?php if ($isCreate): ?>
          <select name="jenis_kendaraan" required
                  class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm
                         focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
            <option value="">— Pilih Jenis —</option>
            <?php foreach ($availableJenis as $j): ?>
              <option value="<?= $j ?>"><?= $jenisLabel[$j] ?></option>
            <?php endforeach; ?>
          </select>
          <p class="text-xs text-slate-400 mt-1">Hanya jenis yang belum memiliki tarif yang dapat ditambahkan</p>
        <?php else: ?>
          <!-- Edit: jenis dikunci karena UNIQUE constraint -->
          <input type="hidden" name="jenis_kendaraan" value="<?= e($tarif['jenis_kendaraan']) ?>">
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;
                      font-size:14px;color:#475569;display:flex;align-items:center;gap:8px">
            <svg style="width:16px;height:16px;color:#94a3b8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <?= $jenisLabel[$tarif['jenis_kendaraan']] ?? ucfirst($tarif['jenis_kendaraan']) ?>
            <span style="font-size:11px;color:#94a3b8;margin-left:4px">(tidak dapat diubah)</span>
          </div>
        <?php endif; ?>
      </div>

      <!-- Tarif per Jam -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">
          Tarif per Jam (Rp) <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
          <input type="number" name="tarif_per_jam"
                 value="<?= e($tarif['tarif_per_jam'] ?? '') ?>"
                 min="0" step="500" required placeholder="2000"
                 class="w-full border border-slate-200 rounded-lg pl-10 pr-4 py-2.5 text-sm
                        focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
      </div>

      <!-- Biaya Masuk -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">
          Biaya Masuk / Flat (Rp)
        </label>
        <div class="relative">
          <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
          <input type="number" name="tarif_masuk"
                 value="<?= e($tarif['tarif_masuk'] ?? 0) ?>"
                 min="0" step="500" placeholder="0"
                 class="w-full border border-slate-200 rounded-lg pl-10 pr-4 py-2.5 text-sm
                        focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <p class="text-xs text-slate-400 mt-1">Biaya flat saat kendaraan pertama masuk, tidak dikalikan durasi. Isi 0 jika tidak ada.</p>
      </div>

      <!-- Keterangan -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Keterangan</label>
        <input type="text" name="keterangan"
               value="<?= e($tarif['keterangan'] ?? '') ?>"
               placeholder="Contoh: Tarif Motor Per Jam"
               class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm
                      focus:outline-none focus:ring-2 focus:ring-indigo-500">
      </div>

      <div class="flex gap-3 pt-2 border-t border-slate-100">
        <button type="submit"
                class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors">
          <?= $isCreate ? 'Tambah Tarif' : 'Simpan Perubahan' ?>
        </button>
        <a href="?page=tarif&action=index"
           class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium rounded-lg text-sm transition-colors">
          Batal
        </a>
      </div>
    </form>
    <?php endif; ?>
  </div>
</div>