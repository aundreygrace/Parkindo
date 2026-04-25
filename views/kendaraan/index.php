<?php
/**
 * views/kendaraan/index.php
 * Variabel: $kendaraan, $search, $jenis, $total, $halaman, $totalHal, $perPage
 */
require_once VIEW_PATH . '/layouts/components.php';
$baseUrl = '?page=kendaraan&action=index&search=' . urlencode($search) . '&jenis=' . urlencode($jenis);
?>

<div class="flex items-center justify-between mb-5">
  <p class="text-xs text-slate-400"><?= number_format($total) ?> kendaraan terdaftar</p>
  <a href="?page=kendaraan&action=create"
     class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Tambah Kendaraan
  </a>
</div>

<!-- Filter -->
<div class="bg-white rounded-xl border border-slate-200 p-4 mb-4">
  <form method="GET" class="flex flex-wrap gap-3 items-end">
    <input type="hidden" name="page"   value="kendaraan">
    <input type="hidden" name="action" value="index">
    <div class="flex-1 min-w-48">
      <label class="block text-xs font-medium text-slate-500 mb-1">Cari plat / pemilik</label>
      <input type="text" name="search" value="<?= e($search) ?>" placeholder="AE 1234 AB atau Nama Pemilik"
             class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>
    <div class="min-w-36">
      <label class="block text-xs font-medium text-slate-500 mb-1">Jenis</label>
      <select name="jenis" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="">Semua</option>
        <option value="motor"   <?= $jenis==='motor'   ? 'selected':'' ?>>Motor</option>
        <option value="mobil"   <?= $jenis==='mobil'   ? 'selected':'' ?>>Mobil</option>
        <option value="lainnya" <?= $jenis==='lainnya' ? 'selected':'' ?>>Lainnya</option>
      </select>
    </div>
    <div class="flex gap-2">
      <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">Filter</button>
      <a href="?page=kendaraan&action=index" class="bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium px-4 py-2 rounded-lg transition-colors">Reset</a>
    </div>
  </form>
</div>

<!-- Tabel -->
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
  <div class="overflow-x-auto">
    <?php if (empty($kendaraan)): renderEmptyState('Tidak ada kendaraan', 'Tambah kendaraan baru atau ubah filter.');
    else: ?>
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-slate-50 border-b border-slate-200 text-xs text-slate-500 uppercase tracking-wide">
          <th class="text-left px-5 py-3 font-medium">Plat Nomor</th>
          <th class="text-left px-3 py-3 font-medium">Jenis</th>
          <th class="text-left px-3 py-3 font-medium">Warna</th>
          <th class="text-left px-3 py-3 font-medium">Pemilik</th>
          <th class="text-left px-3 py-3 font-medium">No. HP</th>
          <th class="text-left px-3 py-3 font-medium">Didaftarkan</th>
          <th class="text-center px-5 py-3 font-medium">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($kendaraan as $k): ?>
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="px-5 py-3">
            <span class="font-mono font-bold text-slate-800 bg-slate-100 px-2 py-1 rounded text-xs tracking-wider">
              <?= e($k['plat_nomor']) ?>
            </span>
          </td>
          <td class="px-3 py-3"><?= badgeJenis($k['jenis_kendaraan']) ?></td>
          <td class="px-3 py-3 text-xs text-slate-600"><?= e($k['warna']) ?></td>
          <td class="px-3 py-3 text-sm font-medium text-slate-700"><?= e($k['pemilik']) ?></td>
          <td class="px-3 py-3 text-xs text-slate-500 font-mono"><?= e($k['no_hp'] ?: '—') ?></td>
          <td class="px-3 py-3 text-xs text-slate-400"><?= formatTanggal($k['created_at'], false) ?></td>
          <td class="px-5 py-3">
            <div class="flex items-center justify-center gap-3">
              <a href="?page=kendaraan&action=edit&id=<?= $k['id_kendaraan'] ?>"
                 class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Edit</a>
              <a href="?page=kendaraan&action=delete&id=<?= $k['id_kendaraan'] ?>"
                 data-confirm="Kendaraan <?= e($k['plat_nomor']) ?> akan dihapus. Aksi ini tidak dapat dibatalkan."
                 data-confirm-title="Hapus Kendaraan"
                 data-confirm-type="danger"
                 class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
  <?php renderPagination($halaman, $totalHal, $total, $baseUrl); ?>
</div>