<?php
/**
 * views/transaksi/index.php
 * Variabel: $transaksi, $tab, $filter, $areas, $total,
 *           $halaman, $totalHal, $perPage,
 *           $search, $idArea, $dari, $sampai,
 *           $countMasuk, $countKeluar
 *
 * Tab 'masuk'  → kendaraan yang statusnya masih parkir
 * Tab 'keluar' → kendaraan yang sudah keluar (riwayat)
 */

$jBadge = [
    'motor'   => 'bg-blue-50 text-blue-700',
    'mobil'   => 'bg-violet-50 text-violet-700',
    'lainnya' => 'bg-amber-50 text-amber-700',
];

// URL dasar tanpa hal= dan tab=
$baseFilter = '?page=transaksi&action=index'
    . '&search='         . urlencode($search)
    . '&id_area='        . urlencode($idArea ?? '')
    . '&tanggal_dari='   . urlencode($dari)
    . '&tanggal_sampai=' . urlencode($sampai);
?>

<!-- ── HEADER ───────────────────────────────────────────── -->
<div class="flex items-center justify-between mb-5">
  <div class="flex gap-2">
    <a href="?page=transaksi&action=masuk"
       class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-500 text-white
              text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
      Kendaraan Masuk
    </a>
    <a href="?page=transaksi&action=keluar"
       class="inline-flex items-center gap-1.5 bg-white hover:bg-slate-50 text-slate-700
              text-sm font-semibold px-4 py-2 rounded-lg border border-slate-200 transition-colors">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/>
      </svg>
      Proses Keluar
    </a>
  </div>
</div>

<!-- ── TAB SWITCHER ─────────────────────────────────────── -->
<div class="flex gap-1 bg-slate-100 p-1 rounded-xl mb-4 w-fit">
  <a href="<?= $baseFilter ?>&tab=masuk"
     class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all
            <?= $tab === 'masuk'
                ? 'bg-white text-slate-800 shadow-sm'
                : 'text-slate-500 hover:text-slate-700' ?>">
    <svg class="w-4 h-4 <?= $tab==='masuk' ? 'text-green-500' : 'text-slate-400' ?>"
         fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12h12l1-12"/>
    </svg>
    Sedang Parkir
    <span class="text-xs font-bold px-2 py-0.5 rounded-full
                 <?= $tab==='masuk'
                     ? 'bg-green-100 text-green-700'
                     : 'bg-slate-200 text-slate-500' ?>">
      <?= $countMasuk ?>
    </span>
  </a>
  <a href="<?= $baseFilter ?>&tab=keluar"
     class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all
            <?= $tab === 'keluar'
                ? 'bg-white text-slate-800 shadow-sm'
                : 'text-slate-500 hover:text-slate-700' ?>">
    <svg class="w-4 h-4 <?= $tab==='keluar' ? 'text-blue-500' : 'text-slate-400' ?>"
         fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    Sudah Keluar
    <span class="text-xs font-bold px-2 py-0.5 rounded-full
                 <?= $tab==='keluar'
                     ? 'bg-blue-100 text-blue-700'
                     : 'bg-slate-200 text-slate-500' ?>">
    </span>
  </a>
</div>

<!-- ── FILTER ────────────────────────────────────────────── -->
<div class="bg-white rounded-xl border border-slate-200 p-4 mb-4">
  <form method="GET" class="flex flex-wrap gap-3 items-end">
    <input type="hidden" name="page"   value="transaksi">
    <input type="hidden" name="action" value="index">
    <input type="hidden" name="tab"    value="<?= e($tab) ?>">

    <div class="flex-1 min-w-40">
      <label class="block text-xs font-medium text-slate-500 mb-1">Cari plat / pemilik / kode</label>
      <input type="text" name="search" value="<?= e($search) ?>"
             placeholder="Contoh: AE 1234 AB"
             class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                    focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div class="min-w-36">
      <label class="block text-xs font-medium text-slate-500 mb-1">Area</label>
      <select name="id_area"
              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                     focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Semua Area</option>
        <?php foreach ($areas as $area): ?>
        <option value="<?= $area['id_area'] ?>"
                <?= (int)$idArea === (int)$area['id_area'] ? 'selected' : '' ?>>
          <?= e($area['nama_area']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>

    <?php if ($tab === 'keluar'): ?>
    <div>
      <label class="block text-xs font-medium text-slate-500 mb-1">Dari</label>
      <input type="date" name="tanggal_dari" value="<?= e($dari) ?>"
             class="border border-slate-200 rounded-lg px-3 py-2 text-sm
                    focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-500 mb-1">Sampai</label>
      <input type="date" name="tanggal_sampai" value="<?= e($sampai) ?>"
             class="border border-slate-200 rounded-lg px-3 py-2 text-sm
                    focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <?php endif; ?>

    <div class="flex gap-2">
      <button type="submit"
              class="bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium
                     px-4 py-2 rounded-lg transition-colors">
        Filter
      </button>
      <a href="?page=transaksi&action=index&tab=<?= $tab ?>"
         class="bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium
                px-4 py-2 rounded-lg transition-colors">
        Reset
      </a>
    </div>
  </form>
</div>

<!-- ── TABEL ─────────────────────────────────────────────── -->
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">

  <?php if ($tab === 'masuk'): ?>
  <!-- ══ TAB: SEDANG PARKIR ═══════════════════════════════ -->
  <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
    <div class="flex items-center gap-2">
      <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
      <span class="text-sm font-semibold text-slate-700">Kendaraan Sedang Parkir</span>
    </div>
    <span class="text-xs text-slate-400"><?= number_format($total) ?> kendaraan</span>
  </div>

  <div class="overflow-x-auto">
    <?php if (empty($transaksi)): ?>
    <div class="text-center py-14 text-slate-400">
      <svg class="w-12 h-12 mx-auto mb-3 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12h12l1-12"/>
      </svg>
      <p class="font-medium text-slate-500">Tidak ada kendaraan yang sedang parkir</p>
      <p class="text-sm mt-1">Area parkir kosong saat ini</p>
    </div>
    <?php else: ?>
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-slate-50 border-b border-slate-200 text-xs text-slate-500 uppercase tracking-wide">
          <th class="text-left px-5 py-3 font-medium">Kode Tiket</th>
          <th class="text-left px-3 py-3 font-medium">Kendaraan</th>
          <th class="text-left px-3 py-3 font-medium">Pemilik</th>
          <th class="text-left px-3 py-3 font-medium">Area</th>
          <th class="text-left px-3 py-3 font-medium">Waktu Masuk</th>
          <th class="text-left px-3 py-3 font-medium">Lama Parkir</th>
          <th class="text-left px-3 py-3 font-medium">Petugas</th>
          <th class="text-center px-5 py-3 font-medium">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($transaksi as $trx):
          $selisihDetik = time() - strtotime($trx['waktu_masuk']);
          $jam   = floor($selisihDetik / 3600);
          $menit = floor(($selisihDetik % 3600) / 60);
          $lamaLabel = ($jam > 0 ? $jam . 'j ' : '') . $menit . 'm';
          $lamaWarn  = $jam >= 8; // Parkir > 8 jam = warning
        ?>
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="px-5 py-3">
            <span class="font-mono text-xs font-semibold text-slate-600">
              <?= e($trx['kode_parkir']) ?>
            </span>
          </td>
          <td class="px-3 py-3">
            <div class="font-mono font-bold text-slate-800 text-xs bg-slate-100
                        px-2 py-1 rounded inline-block mb-1 tracking-wider">
              <?= e($trx['plat_nomor']) ?>
            </div>
            <div><?= isset($jBadge[$trx['jenis_kendaraan']])
                      ? '<span class="text-xs px-2 py-0.5 rounded font-medium '
                        . $jBadge[$trx['jenis_kendaraan']] . '">'
                        . ucfirst($trx['jenis_kendaraan']) . '</span>'
                      : '' ?>
            </div>
          </td>
          <td class="px-3 py-3 text-xs text-slate-600"><?= e($trx['pemilik']) ?></td>
          <td class="px-3 py-3 text-xs text-slate-500"><?= e($trx['nama_area']) ?></td>
          <td class="px-3 py-3 text-xs text-slate-500 whitespace-nowrap">
            <?= date('d/m H:i', strtotime($trx['waktu_masuk'])) ?>
          </td>
          <td class="px-3 py-3">
            <span class="text-xs font-semibold <?= $lamaWarn ? 'text-amber-600' : 'text-slate-700' ?>">
              <?= $lamaLabel ?>
            </span>
            <?php if ($lamaWarn): ?>
            <span class="text-[10px] text-amber-500 block">Parkir lama</span>
            <?php endif; ?>
          </td>
          <td class="px-3 py-3 text-xs text-slate-400"><?= e($trx['nama_petugas']) ?></td>
          <td class="px-5 py-3">
            <div class="flex items-center justify-center gap-3">
              <!-- Struk masuk -->
              <a href="?page=transaksi&action=struk&id=<?= $trx['id_parkir'] ?>&type=masuk"
                 target="_blank" title="Cetak Tiket Masuk"
                 class="text-slate-400 hover:text-blue-600 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
              </a>
              <!-- Proses keluar -->
              <a href="?page=transaksi&action=keluar&id=<?= $trx['id_parkir'] ?>"
                 title="Proses Keluar"
                 class="inline-flex items-center gap-1 text-xs font-semibold text-white
                        bg-slate-700 hover:bg-slate-600 px-2.5 py-1 rounded-lg transition-colors">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7"/>
                </svg>
                Keluarkan
              </a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <?php else: ?>
  <!-- ══ TAB: SUDAH KELUAR (RIWAYAT) ══════════════════════ -->
  <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
    <div class="flex items-center gap-2">
      <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <span class="text-sm font-semibold text-slate-700">Riwayat Kendaraan Keluar</span>
    </div>
    <span class="text-xs text-slate-400"><?= number_format($total) ?> transaksi</span>
  </div>

  <div class="overflow-x-auto">
    <?php if (empty($transaksi)): ?>
    <div class="text-center py-14 text-slate-400">
      <svg class="w-12 h-12 mx-auto mb-3 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
      <p class="font-medium text-slate-500">Belum ada riwayat kendaraan keluar</p>
      <p class="text-sm mt-1">Data akan muncul setelah transaksi keluar diproses</p>
    </div>
    <?php else: ?>
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-slate-50 border-b border-slate-200 text-xs text-slate-500 uppercase tracking-wide">
          <th class="text-left px-5 py-3 font-medium">Kode Tiket</th>
          <th class="text-left px-3 py-3 font-medium">Kendaraan</th>
          <th class="text-left px-3 py-3 font-medium">Pemilik</th>
          <th class="text-left px-3 py-3 font-medium">Area</th>
          <th class="text-left px-3 py-3 font-medium">Masuk</th>
          <th class="text-left px-3 py-3 font-medium">Keluar</th>
          <th class="text-left px-3 py-3 font-medium">Durasi</th>
          <th class="text-right px-3 py-3 font-medium">Biaya</th>
          <th class="text-right px-3 py-3 font-medium">Kembalian</th>
          <th class="text-center px-5 py-3 font-medium">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($transaksi as $trx): ?>
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="px-5 py-3">
            <span class="font-mono text-xs font-semibold text-slate-600">
              <?= e($trx['kode_parkir']) ?>
            </span>
          </td>
          <td class="px-3 py-3">
            <div class="font-mono font-bold text-slate-800 text-xs bg-slate-100
                        px-2 py-1 rounded inline-block mb-1 tracking-wider">
              <?= e($trx['plat_nomor']) ?>
            </div>
            <div><?= isset($jBadge[$trx['jenis_kendaraan']])
                      ? '<span class="text-xs px-2 py-0.5 rounded font-medium '
                        . $jBadge[$trx['jenis_kendaraan']] . '">'
                        . ucfirst($trx['jenis_kendaraan']) . '</span>'
                      : '' ?>
            </div>
          </td>
          <td class="px-3 py-3 text-xs text-slate-600"><?= e($trx['pemilik']) ?></td>
          <td class="px-3 py-3 text-xs text-slate-500"><?= e($trx['nama_area']) ?></td>
          <td class="px-3 py-3 text-xs text-slate-500 whitespace-nowrap">
            <?= date('d/m H:i', strtotime($trx['waktu_masuk'])) ?>
          </td>
          <td class="px-3 py-3 text-xs text-slate-500 whitespace-nowrap">
            <?= $trx['waktu_keluar'] ? date('d/m H:i', strtotime($trx['waktu_keluar'])) : '—' ?>
          </td>
          <td class="px-3 py-3 text-xs text-slate-600">
            <?= $trx['durasi_jam'] > 0 ? formatDurasi((float)$trx['durasi_jam']) : '—' ?>
          </td>
          <td class="px-3 py-3 text-right">
            <span class="text-sm font-bold text-slate-800">
              <?= formatRupiah($trx['biaya_total']) ?>
            </span>
          </td>
          <td class="px-3 py-3 text-right">
            <span class="text-xs text-green-600 font-semibold">
              <?= formatRupiah($trx['kembalian'] ?? 0) ?>
            </span>
          </td>
          <td class="px-5 py-3 text-center">
            <a href="?page=transaksi&action=struk&id=<?= $trx['id_parkir'] ?>&type=keluar"
               target="_blank" title="Cetak Struk"
               class="text-slate-400 hover:text-blue-600 transition-colors inline-flex">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
              </svg>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <!-- Footer total -->
      <tfoot>
        <tr class="bg-slate-50 border-t-2 border-slate-200">
          <td colspan="7" class="px-5 py-3 text-xs font-semibold text-slate-500">
            Total halaman ini (<?= count($transaksi) ?> transaksi)
          </td>
          <td class="px-3 py-3 text-right text-sm font-bold text-slate-800">
            <?= formatRupiah(array_sum(array_column($transaksi, 'biaya_total'))) ?>
          </td>
          <td colspan="2"></td>
        </tr>
      </tfoot>
    </table>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Pagination -->
  <?php
  require_once VIEW_PATH . '/layouts/components.php';
  renderPagination($halaman, $totalHal, $total, $baseFilter . '&tab=' . $tab);
  ?>
</div>