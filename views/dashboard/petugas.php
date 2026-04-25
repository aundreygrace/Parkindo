<?php
/**
 * views/dashboard/petugas.php
 * Dashboard khusus Petugas
 * Variabel: $stats, $sedangParkir, $transaksiHariIni, $areas
 */
?>

<!-- ── STAT CARDS ──────────────────────────────────────────── -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Sedang Parkir</span>
      <span class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center">
        <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12h12l1-12"/>
        </svg>
      </span>
    </div>
    <div class="text-3xl font-bold text-slate-800"><?= $stats['kendaraan_parkir'] ?></div>
    <div class="text-xs text-slate-400 mt-1">Kendaraan aktif saat ini</div>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Transaksi Hari Ini</span>
      <span class="w-9 h-9 rounded-lg bg-teal-50 flex items-center justify-center">
        <svg class="w-5 h-5 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
      </span>
    </div>
    <div class="text-3xl font-bold text-slate-800"><?= $stats['transaksi_hari'] ?></div>
    <div class="text-xs text-slate-400 mt-1">Masuk + keluar hari ini</div>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Pendapatan Hari Ini</span>
      <span class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center">
        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
      </span>
    </div>
    <div class="text-2xl font-bold text-slate-800"><?= formatRupiah($stats['pendapatan_hari']) ?></div>
    <div class="text-xs text-slate-400 mt-1">Total dari transaksi keluar</div>
  </div>

</div>

<!-- ── TOMBOL AKSI CEPAT ────────────────────────────────────── -->
<div class="flex flex-wrap gap-3 mb-6">
  <a href="?page=transaksi&action=masuk"
     class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition-colors shadow-sm shadow-indigo-200">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Kendaraan Masuk
  </a>
  <a href="?page=transaksi&action=keluar"
     class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold px-5 py-2.5 rounded-lg border border-slate-200 transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/>
    </svg>
    Kendaraan Keluar
  </a>
  <a href="?page=transaksi&action=index"
     class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold px-5 py-2.5 rounded-lg border border-slate-200 transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
    </svg>
    Semua Transaksi
  </a>
</div>

<!-- ── ROW: KENDARAAN SEDANG PARKIR + SELESAI HARI INI ─────── -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

  <!-- Sedang Parkir -->
  <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
      <h3 class="text-sm font-semibold text-slate-700">Sedang Parkir Sekarang</h3>
      <span class="text-xs bg-indigo-50 text-indigo-700 font-semibold px-2.5 py-1 rounded-full">
        <?= count($sedangParkir) ?> kendaraan
      </span>
    </div>
    <div class="overflow-x-auto">
      <?php if (empty($sedangParkir)): ?>
        <div class="text-center py-10 text-slate-400 text-sm">Tidak ada kendaraan yang sedang parkir.</div>
      <?php else: ?>
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
            <th class="text-left px-5 py-3 font-medium">Plat</th>
            <th class="text-left px-3 py-3 font-medium">Jenis</th>
            <th class="text-left px-3 py-3 font-medium">Area</th>
            <th class="text-left px-3 py-3 font-medium">Masuk</th>
            <th class="text-left px-3 py-3 font-medium">Durasi</th>
            <th class="px-3 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($sedangParkir as $trx):
            $selisih = time() - strtotime($trx['waktu_masuk']);
            $jam     = floor($selisih / 3600);
            $menit   = floor(($selisih % 3600) / 60);
          ?>
          <tr class="hover:bg-slate-50 transition-colors">
            <td class="px-5 py-3">
              <span class="font-mono font-semibold text-slate-800 text-xs bg-slate-100 px-2 py-1 rounded">
                <?= e($trx['plat_nomor']) ?>
              </span>
            </td>
            <td class="px-3 py-3">
              <?php
                $jBadge = ['motor'=>'bg-indigo-50 text-indigo-700','mobil'=>'bg-violet-50 text-violet-700','lainnya'=>'bg-amber-50 text-amber-700'];
                $badge  = $jBadge[$trx['jenis_kendaraan']] ?? 'bg-slate-100 text-slate-600';
              ?>
              <span class="text-xs px-2 py-0.5 rounded font-medium <?= $badge ?>">
                <?= ucfirst($trx['jenis_kendaraan']) ?>
              </span>
            </td>
            <td class="px-3 py-3 text-xs text-slate-600"><?= e($trx['nama_area']) ?></td>
            <td class="px-3 py-3 text-xs text-slate-500"><?= date('H:i', strtotime($trx['waktu_masuk'])) ?></td>
            <td class="px-3 py-3 text-xs font-semibold <?= $jam >= 3 ? 'text-amber-600' : 'text-slate-700' ?>">
              <?= $jam ?>j <?= $menit ?>m
            </td>
            <td class="px-3 py-3">
              <a href="?page=transaksi&action=keluar&id=<?= $trx['id_parkir'] ?>"
                 class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Keluarkan</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- Selesai Hari Ini -->
  <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
      <h3 class="text-sm font-semibold text-slate-700">Selesai Hari Ini</h3>
      <span class="text-xs bg-green-50 text-green-700 font-semibold px-2.5 py-1 rounded-full">
        <?= count($transaksiHariIni) ?> transaksi
      </span>
    </div>
    <div class="overflow-x-auto">
      <?php if (empty($transaksiHariIni)): ?>
        <div class="text-center py-10 text-slate-400 text-sm">Belum ada transaksi keluar hari ini.</div>
      <?php else: ?>
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
            <th class="text-left px-5 py-3 font-medium">Plat</th>
            <th class="text-left px-3 py-3 font-medium">Durasi</th>
            <th class="text-left px-3 py-3 font-medium">Keluar</th>
            <th class="text-right px-5 py-3 font-medium">Biaya</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($transaksiHariIni as $trx): ?>
          <tr class="hover:bg-slate-50 transition-colors">
            <td class="px-5 py-3">
              <span class="font-mono font-semibold text-slate-800 text-xs bg-slate-100 px-2 py-1 rounded">
                <?= e($trx['plat_nomor']) ?>
              </span>
            </td>
            <td class="px-3 py-3 text-xs text-slate-600"><?= formatDurasi((float)$trx['durasi_jam']) ?></td>
            <td class="px-3 py-3 text-xs text-slate-500">
              <?= $trx['waktu_keluar'] ? date('H:i', strtotime($trx['waktu_keluar'])) : '-' ?>
            </td>
            <td class="px-5 py-3 text-right">
              <span class="text-sm font-semibold text-green-600">
                <?= formatRupiah($trx['biaya_total']) ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
    <div class="px-5 py-3 border-t border-slate-100 bg-slate-50">
      <div class="flex justify-between text-xs">
        <span class="text-slate-500">Total pendapatan selesai hari ini</span>
        <span class="font-bold text-slate-700">
          <?= formatRupiah(array_sum(array_column($transaksiHariIni, 'biaya_total'))) ?>
        </span>
      </div>
    </div>
  </div>

</div>