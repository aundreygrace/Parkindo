<?php
/**
 * views/laporan/detail.php
 * Variabel: $filter, $transaksi, $areas, $total, $subtotal,
 *           $halaman, $totalHal, $perPage, $search, $jenis, $idArea
 */
require_once VIEW_PATH . '/layouts/components.php';

$dari      = $filter['dari'];
$sampai    = $filter['sampai'];
$baseExport= '?page=laporan&dari=' . urlencode($dari) . '&sampai=' . urlencode($sampai);
$baseUrl   = $baseExport . '&action=detail'
           . '&search='          . urlencode($search)
           . '&jenis_kendaraan=' . urlencode($jenis)
           . '&id_area='         . urlencode($idArea ?? '');

$jBadge = [
    'motor'   => 'bg-indigo-50 text-indigo-700',
    'mobil'   => 'bg-violet-50 text-violet-700',
    'lainnya' => 'bg-amber-50 text-amber-700',
];
?>

<!-- Kembali + export -->
<div class="flex items-center justify-between mb-5">
  <a href="?page=laporan&action=index&dari=<?= urlencode($dari) ?>&sampai=<?= urlencode($sampai) ?>"
     class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    Kembali ke Rekap
  </a>
  <div class="flex gap-2">
    <a href="<?= $baseExport ?>&action=exportCsv"
       class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-600 transition-colors">
      Export CSV
    </a>
    <a href="<?= $baseExport ?>&action=exportExcel"
       class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-green-200 bg-green-50 hover:bg-green-100 text-green-700 transition-colors">
      Export Excel
    </a>
    <a href="<?= $baseExport ?>&action=exportPdf" target="_blank"
       class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 text-red-700 transition-colors">
      Export PDF
    </a>
  </div>
</div>

<!-- Info periode -->
<div class="bg-indigo-50 border border-indigo-200 rounded-xl px-5 py-3 mb-4 flex items-center justify-between">
  <p class="text-sm text-indigo-800">
    Periode: <strong><?= formatTanggal($dari.' 00:00:00', false) ?> — <?= formatTanggal($sampai.' 00:00:00', false) ?></strong>
  </p>
  <p class="text-sm text-indigo-700 font-semibold">
    <?= number_format($total) ?> transaksi · Total: <?= formatRupiah(array_sum(array_column($transaksi, 'biaya_total'))) ?>
  </p>
</div>

<!-- Filter -->
<div class="bg-white rounded-xl border border-slate-200 p-4 mb-4">
  <form method="GET" class="flex flex-wrap gap-3 items-end">
    <input type="hidden" name="page"   value="laporan">
    <input type="hidden" name="action" value="detail">
    <input type="hidden" name="dari"   value="<?= e($dari) ?>">
    <input type="hidden" name="sampai" value="<?= e($sampai) ?>">

    <div class="flex-1 min-w-40">
      <label class="block text-xs font-medium text-slate-500 mb-1">Cari plat / pemilik / kode</label>
      <input type="text" name="search" value="<?= e($search) ?>" placeholder="Contoh: AE 1234 AB"
             class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>
    <div class="min-w-32">
      <label class="block text-xs font-medium text-slate-500 mb-1">Jenis</label>
      <select name="jenis_kendaraan" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="">Semua</option>
        <option value="motor"   <?= $jenis==='motor'   ? 'selected':'' ?>>Motor</option>
        <option value="mobil"   <?= $jenis==='mobil'   ? 'selected':'' ?>>Mobil</option>
        <option value="lainnya" <?= $jenis==='lainnya' ? 'selected':'' ?>>Lainnya</option>
      </select>
    </div>
    <div class="min-w-36">
      <label class="block text-xs font-medium text-slate-500 mb-1">Area</label>
      <select name="id_area" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="">Semua Area</option>
        <?php foreach ($areas as $a): ?>
        <option value="<?= $a['id_area'] ?>" <?= (int)$idArea === (int)$a['id_area'] ? 'selected':'' ?>>
          <?= e($a['nama_area']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="flex gap-2">
      <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">Filter</button>
      <a href="<?= $baseExport ?>&action=detail" class="bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium px-4 py-2 rounded-lg transition-colors">Reset</a>
    </div>
  </form>
</div>

<!-- Tabel -->
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
  <div class="overflow-x-auto">
    <?php if (empty($transaksi)): renderEmptyState('Tidak ada data transaksi', 'Coba ubah filter atau rentang tanggal.');
    else: ?>
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-slate-50 border-b border-slate-200 text-xs text-slate-500 uppercase tracking-wide">
          <th class="text-left px-4 py-3 font-medium">Kode</th>
          <th class="text-left px-3 py-3 font-medium">Plat / Jenis</th>
          <th class="text-left px-3 py-3 font-medium">Pemilik</th>
          <th class="text-left px-3 py-3 font-medium">Area</th>
          <th class="text-left px-3 py-3 font-medium">Masuk</th>
          <th class="text-left px-3 py-3 font-medium">Keluar</th>
          <th class="text-left px-3 py-3 font-medium">Durasi</th>
          <th class="text-right px-3 py-3 font-medium">Biaya</th>
          <th class="text-left px-3 py-3 font-medium">Petugas</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($transaksi as $t): ?>
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="px-4 py-3">
            <span class="font-mono text-xs text-slate-600 font-semibold"><?= e($t['kode_parkir']) ?></span>
          </td>
          <td class="px-3 py-3">
            <div class="font-mono text-xs font-bold text-slate-800 bg-slate-100 px-1.5 py-0.5 rounded inline-block mb-0.5">
              <?= e($t['plat_nomor']) ?>
            </div>
            <div><?= badgeJenis($t['jenis_kendaraan']) ?></div>
          </td>
          <td class="px-3 py-3 text-xs text-slate-700"><?= e($t['pemilik']) ?></td>
          <td class="px-3 py-3 text-xs text-slate-500"><?= e($t['nama_area']) ?></td>
          <td class="px-3 py-3 text-xs text-slate-500 whitespace-nowrap">
            <?= date('d/m H:i', strtotime($t['waktu_masuk'])) ?>
          </td>
          <td class="px-3 py-3 text-xs text-slate-500 whitespace-nowrap">
            <?= $t['waktu_keluar'] ? date('d/m H:i', strtotime($t['waktu_keluar'])) : '—' ?>
          </td>
          <td class="px-3 py-3 text-xs text-slate-600">
            <?= $t['durasi_jam'] > 0 ? formatDurasi((float)$t['durasi_jam']) : '—' ?>
          </td>
          <td class="px-3 py-3 text-right">
            <span class="font-semibold text-slate-800 text-xs"><?= formatRupiah($t['biaya_total']) ?></span>
          </td>
          <td class="px-3 py-3 text-xs text-slate-400"><?= e($t['nama_petugas'] ?? '—') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr class="bg-slate-50 border-t-2 border-slate-200 font-semibold">
          <td colspan="7" class="px-4 py-3 text-sm text-slate-600">
            Subtotal halaman ini (<?= count($transaksi) ?> transaksi)
          </td>
          <td class="px-3 py-3 text-right text-sm text-indigo-700">
            <?= formatRupiah($subtotal) ?>
          </td>
          <td></td>
        </tr>
      </tfoot>
    </table>
    <?php endif; ?>
  </div>
  <?php renderPagination($halaman, $totalHal, $total, $baseUrl); ?>
</div>