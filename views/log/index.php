<?php
/**
 * views/log/index.php
 * Variabel: $logs, $moduls, $filter, $total, $halaman, $totalHal
 */
require_once VIEW_PATH . '/layouts/components.php';

$baseUrl = '?page=log'
         . '&search='         . urlencode($filter['search'])
         . '&modul='          . urlencode($filter['modul'])
         . '&tanggal_dari='   . urlencode($filter['tanggal_dari'])
         . '&tanggal_sampai=' . urlencode($filter['tanggal_sampai']);

$modulIcon = [
    'auth'      => 'Auth',
    'transaksi' => 'Trx',
    'user'      => 'User',
    'kendaraan' => 'Kndr',
    'tarif'     => 'Tarif',
    'area'      => 'Area',
    'system'    => 'Sys',
];
?>

<div class="flex items-center justify-between mb-5">
  <p class="text-xs text-slate-400"><?= number_format($total) ?> entri log ditemukan</p>
</div>

<!-- Filter -->
<div class="bg-white rounded-xl border border-slate-200 p-4 mb-4">
  <form method="GET" class="flex flex-wrap gap-3 items-end">
    <input type="hidden" name="page" value="log">

    <div class="flex-1 min-w-48">
      <label class="block text-xs font-medium text-slate-500 mb-1">Cari aktivitas / user</label>
      <input type="text" name="search" value="<?= e($filter['search']) ?>"
             placeholder="Ketik nama user atau aktivitas…"
             class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>

    <div class="min-w-36">
      <label class="block text-xs font-medium text-slate-500 mb-1">Modul</label>
      <select name="modul" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="">Semua Modul</option>
        <?php foreach ($moduls as $m): ?>
        <option value="<?= e($m) ?>" <?= $filter['modul'] === $m ? 'selected' : '' ?>>
          <?= ($modulIcon[$m] ?? '•') . ' ' . ucfirst($m) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label class="block text-xs font-medium text-slate-500 mb-1">Dari</label>
      <input type="date" name="tanggal_dari" value="<?= e($filter['tanggal_dari']) ?>"
             class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>

    <div>
      <label class="block text-xs font-medium text-slate-500 mb-1">Sampai</label>
      <input type="date" name="tanggal_sampai" value="<?= e($filter['tanggal_sampai']) ?>"
             class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>

    <div class="flex gap-2">
      <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">Filter</button>
      <a href="?page=log" class="bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium px-4 py-2 rounded-lg transition-colors">Reset</a>
    </div>
  </form>
</div>

<!-- Tabel Log -->
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
  <div class="overflow-x-auto">
    <?php if (empty($logs)): renderEmptyState('Tidak ada log aktivitas', 'Coba ubah filter pencarian.');
    else: ?>
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-slate-50 border-b border-slate-200 text-xs text-slate-500 uppercase tracking-wide">
          <th class="text-left px-5 py-3 font-medium">Waktu</th>
          <th class="text-left px-3 py-3 font-medium">User</th>
          <th class="text-left px-3 py-3 font-medium">Role</th>
          <th class="text-left px-3 py-3 font-medium">Modul</th>
          <th class="text-left px-5 py-3 font-medium">Aktivitas</th>
          <th class="text-left px-3 py-3 font-medium">IP Address</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($logs as $log):
          $icon = $modulIcon[$log['modul']] ?? '•';
        ?>
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="px-5 py-3 text-xs text-slate-500 font-mono whitespace-nowrap">
            <div><?= date('d/m/Y', strtotime($log['waktu_aktivitas'])) ?></div>
            <div class="text-slate-400"><?= date('H:i:s', strtotime($log['waktu_aktivitas'])) ?></div>
          </td>
          <td class="px-3 py-3">
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-500 flex-shrink-0">
                <?= strtoupper(substr($log['nama_lengkap'], 0, 1)) ?>
              </div>
              <div>
                <p class="font-medium text-slate-700 text-xs"><?= e($log['nama_lengkap']) ?></p>
                <p class="text-slate-400 text-xs font-mono">@<?= e($log['username']) ?></p>
              </div>
            </div>
          </td>
          <td class="px-3 py-3"><?= badgeRole($log['role']) ?></td>
          <td class="px-3 py-3">
            <span class="text-xs px-2 py-0.5 rounded font-medium bg-slate-100 text-slate-600">
              <?= $icon ?> <?= ucfirst(e($log['modul'])) ?>
            </span>
          </td>
          <td class="px-5 py-3 text-sm text-slate-700 max-w-xs">
            <?= e($log['aktivitas']) ?>
          </td>
          <td class="px-3 py-3 text-xs text-slate-400 font-mono"><?= e($log['ip_address'] ?? '—') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
  <?php renderPagination($halaman, $totalHal, $total, $baseUrl); ?>
</div>