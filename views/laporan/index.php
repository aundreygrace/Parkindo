<?php
/**
 * views/laporan/index.php
 * Variabel: $filter, $rekap, $trendPendapatan, $trendTransaksi,
 *           $grafikLabel, $grafikData, $perJenis, $perArea, $jamSibuk
 */
require_once VIEW_PATH . '/layouts/components.php';

$dari   = $filter['dari'];
$sampai = $filter['sampai'];

// URL dasar dengan filter aktif
$baseExport = '?page=laporan&dari=' . urlencode($dari) . '&sampai=' . urlencode($sampai);

$presets = [
    'hari_ini'   => 'Hari Ini',
    'minggu_ini' => 'Minggu Ini',
    'bulan_ini'  => 'Bulan Ini',
    'bulan_lalu' => 'Bulan Lalu',
    '30_hari'    => '30 Hari',
    '90_hari'    => '90 Hari',
];
?>

<!-- ── FILTER PERIODE ──────────────────────────────────────── -->
<div class="bg-white rounded-xl border border-slate-200 p-4 mb-5">
  <div class="flex flex-wrap items-end gap-3">

    <!-- Preset buttons -->
    <div class="flex flex-wrap gap-1.5">
      <?php foreach ($presets as $key => $label): ?>
      <a href="?page=laporan&action=index&preset=<?= $key ?>"
         class="text-xs px-3 py-1.5 rounded-lg font-medium border transition-colors
                <?= $filter['preset'] === $key
                    ? 'bg-blue-600 text-white border-blue-600'
                    : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' ?>">
        <?= $label ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Custom range -->
    <form method="GET" class="flex items-end gap-2 ml-auto">
      <input type="hidden" name="page"   value="laporan">
      <input type="hidden" name="action" value="index">
      <div>
        <label class="block text-xs text-slate-500 mb-1">Dari</label>
        <input type="date" name="dari" value="<?= e($dari) ?>"
               class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-xs text-slate-500 mb-1">Sampai</label>
        <input type="date" name="sampai" value="<?= e($sampai) ?>"
               class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <button type="submit"
              class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-lg transition-colors">
        Terapkan
      </button>
    </form>
  </div>

  <!-- Periode aktif -->
  <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
    <p class="text-xs text-slate-500">
      Menampilkan data:
      <strong class="text-slate-700">
        <?= formatTanggal($dari . ' 00:00:00', false) ?>
        — <?= formatTanggal($sampai . ' 00:00:00', false) ?>
      </strong>
    </p>

    <!-- Export buttons -->
    <div class="flex gap-2">
      <a href="<?= $baseExport ?>&action=exportCsv"
         class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-600 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        CSV
      </a>
      <a href="<?= $baseExport ?>&action=exportExcel"
         class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg border border-green-200 bg-green-50 hover:bg-green-100 text-green-700 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Excel
      </a>
      <a href="<?= $baseExport ?>&action=exportPdf" target="_blank"
         class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 text-red-700 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        PDF
      </a>
      <a href="<?= $baseExport ?>&action=detail"
         class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-700 transition-colors">
        Detail Transaksi →
      </a>
    </div>
  </div>
</div>

<!-- ── KPI CARDS ───────────────────────────────────────────── -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">

  <?php
  $kpis = [
    [
      'label' => 'Total Pendapatan',
      'nilai' => formatRupiah($rekap['total_pendapatan'] ?? 0),
      'trend' => $trendPendapatan,
      'sub'   => 'periode yang dipilih',
      'icon'  => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1',
      'bg'    => 'bg-green-50', 'ic' => 'text-green-600',
    ],
    [
      'label' => 'Total Transaksi',
      'nilai' => number_format((int)($rekap['total_transaksi'] ?? 0)),
      'trend' => $trendTransaksi,
      'sub'   => 'transaksi selesai',
      'icon'  => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
      'bg'    => 'bg-blue-50',  'ic' => 'text-blue-600',
    ],
    [
      'label' => 'Rata-rata / Transaksi',
      'nilai' => formatRupiah((float)($rekap['rata_pendapatan'] ?? 0)),
      'trend' => null,
      'sub'   => 'per kendaraan keluar',
      'icon'  => 'M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z',
      'bg'    => 'bg-amber-50', 'ic' => 'text-amber-600',
    ],
    [
      'label' => 'Rata-rata Durasi',
      'nilai' => formatDurasi((float)($rekap['rata_durasi'] ?? 0)),
      'trend' => null,
      'sub'   => 'per kendaraan',
      'icon'  => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
      'bg'    => 'bg-purple-50','ic' => 'text-purple-600',
    ],
  ];
  foreach ($kpis as $k):
  ?>
  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide"><?= $k['label'] ?></span>
      <span class="w-9 h-9 rounded-lg <?= $k['bg'] ?> flex items-center justify-center">
        <svg class="w-5 h-5 <?= $k['ic'] ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $k['icon'] ?>"/>
        </svg>
      </span>
    </div>
    <div class="text-xl font-bold text-slate-800 mb-1"><?= $k['nilai'] ?></div>
    <?php if ($k['trend']): ?>
    <div class="flex items-center gap-1">
      <?php if ($k['trend']['naik']): ?>
        <svg class="w-3 h-3 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/>
        </svg>
        <span class="text-xs text-green-600 font-medium"><?= $k['trend']['persen'] ?>% vs periode lalu</span>
      <?php else: ?>
        <svg class="w-3 h-3 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
        </svg>
        <span class="text-xs text-red-500 font-medium"><?= $k['trend']['persen'] ?>% vs periode lalu</span>
      <?php endif; ?>
    </div>
    <?php else: ?>
    <p class="text-xs text-slate-400"><?= $k['sub'] ?></p>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>

</div>

<!-- ── GRAFIK PENDAPATAN HARIAN ────────────────────────────── -->
<div class="bg-white rounded-xl border border-slate-200 p-5 mb-5">
  <div class="flex items-center justify-between mb-4">
    <div>
      <h3 class="text-sm font-semibold text-slate-700">Pendapatan Harian</h3>
      <p class="text-xs text-slate-400 mt-0.5">
        <?= formatTanggal($dari . ' 00:00:00', false) ?> —
        <?= formatTanggal($sampai . ' 00:00:00', false) ?>
      </p>
    </div>
    <div class="text-right">
      <p class="text-xs text-slate-400">Tertinggi</p>
      <p class="text-sm font-bold text-slate-700">
        <?= !empty($grafikData) ? formatRupiah(max($grafikData)) : 'Rp 0' ?>
      </p>
    </div>
  </div>
  <canvas id="chartHarian" height="<?= count($grafikLabel) > 14 ? '80' : '100' ?>"></canvas>
</div>

<!-- ── BREAKDOWN ──────────────────────────────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">

  <!-- Per Jenis Kendaraan -->
  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <h3 class="text-sm font-semibold text-slate-700 mb-4">Per Jenis Kendaraan</h3>
    <?php
    $totalPend = array_sum(array_column($perJenis, 'pendapatan')) ?: 1;
    $jenisColor= ['motor'=>'bg-blue-500','mobil'=>'bg-violet-500','lainnya'=>'bg-amber-400'];
    if (empty($perJenis)): ?>
      <p class="text-sm text-slate-400 text-center py-4">Belum ada data</p>
    <?php else: foreach ($perJenis as $j):
      $pct = round($j['pendapatan'] / $totalPend * 100);
      $bar = $jenisColor[$j['jenis_kendaraan']] ?? 'bg-slate-400';
    ?>
    <div class="mb-4">
      <div class="flex justify-between items-center mb-1.5">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full <?= $bar ?>"></span>
          <span class="text-sm text-slate-700 capitalize"><?= e($j['jenis_kendaraan']) ?></span>
        </div>
        <span class="text-xs text-slate-400"><?= number_format((int)$j['jumlah']) ?> trx</span>
      </div>
      <div class="w-full bg-slate-100 rounded-full h-2 mb-1">
        <div class="h-2 rounded-full <?= $bar ?>" style="width:<?= $pct ?>%"></div>
      </div>
      <div class="flex justify-between">
        <span class="text-xs font-semibold text-slate-700"><?= formatRupiah($j['pendapatan']) ?></span>
        <span class="text-xs text-slate-400"><?= $pct ?>%</span>
      </div>
    </div>
    <?php endforeach; endif; ?>
  </div>

  <!-- Per Area Parkir -->
  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <h3 class="text-sm font-semibold text-slate-700 mb-4">Per Area Parkir</h3>
    <?php
    $totalArea = array_sum(array_column($perArea, 'pendapatan')) ?: 1;
    if (empty($perArea)): ?>
      <p class="text-sm text-slate-400 text-center py-4">Belum ada data</p>
    <?php else: foreach ($perArea as $i => $a):
      $pct   = round($a['pendapatan'] / $totalArea * 100);
      $bars  = ['bg-teal-500','bg-cyan-500','bg-sky-500','bg-indigo-500'];
      $bar   = $bars[$i % count($bars)];
    ?>
    <div class="mb-4">
      <div class="flex justify-between items-center mb-1.5">
        <span class="text-sm text-slate-700"><?= e($a['nama_area']) ?></span>
        <span class="text-xs text-slate-400"><?= number_format((int)$a['jumlah']) ?> trx</span>
      </div>
      <div class="w-full bg-slate-100 rounded-full h-2 mb-1">
        <div class="h-2 rounded-full <?= $bar ?>" style="width:<?= $pct ?>%"></div>
      </div>
      <div class="flex justify-between">
        <span class="text-xs font-semibold text-slate-700"><?= formatRupiah($a['pendapatan']) ?></span>
        <span class="text-xs text-slate-400"><?= $pct ?>%</span>
      </div>
    </div>
    <?php endforeach; endif; ?>
  </div>

  <!-- Jam Tersibuk -->
  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <h3 class="text-sm font-semibold text-slate-700 mb-4">5 Jam Tersibuk</h3>
    <?php if (empty($jamSibuk)): ?>
      <p class="text-sm text-slate-400 text-center py-4">Belum ada data</p>
    <?php else:
      $maxJam = max(array_column($jamSibuk, 'jumlah')) ?: 1;
      foreach ($jamSibuk as $i => $j):
        $pct = round($j['jumlah'] / $maxJam * 100);
    ?>
    <div class="flex items-center gap-3 mb-3">
      <div class="w-14 text-right">
        <span class="font-mono text-sm font-semibold text-slate-700">
          <?= str_pad($j['jam'], 2, '0', STR_PAD_LEFT) ?>:00
        </span>
      </div>
      <div class="flex-1 bg-slate-100 rounded-full h-2.5">
        <div class="h-2.5 rounded-full bg-blue-500" style="width:<?= $pct ?>%"></div>
      </div>
      <span class="text-xs text-slate-500 w-12 text-right"><?= $j['jumlah'] ?> trx</span>
    </div>
    <?php endforeach; endif; ?>
  </div>

</div>

<!-- ── RINGKASAN TABEL ─────────────────────────────────────── -->
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
  <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 bg-slate-50">
    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Ringkasan Periode</h3>
  </div>
  <table class="w-full text-sm">
    <tbody class="divide-y divide-slate-100">
      <?php
      $rows = [
        ['Motor',      number_format((int)($rekap['total_motor']   ?? 0)) . ' kendaraan'],
        ['Mobil',      number_format((int)($rekap['total_mobil']   ?? 0)) . ' kendaraan'],
        ['Lainnya',    number_format((int)($rekap['total_lainnya'] ?? 0)) . ' kendaraan'],
        ['Total Transaksi Selesai', number_format((int)($rekap['total_transaksi'] ?? 0))],
        ['Total Pendapatan',        formatRupiah($rekap['total_pendapatan'] ?? 0)],
        ['Rata-rata per Transaksi', formatRupiah((float)($rekap['rata_pendapatan'] ?? 0))],
        ['Rata-rata Durasi Parkir', formatDurasi((float)($rekap['rata_durasi'] ?? 0))],
      ];
      foreach ($rows as $r):
      ?>
      <tr class="hover:bg-slate-50">
        <td class="px-5 py-3 text-slate-500 text-sm"><?= $r[0] ?></td>
        <td class="px-5 py-3 text-right font-semibold text-slate-800"><?= $r[1] ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- ── CHART.JS ────────────────────────────────────────────── -->
<script>
(function () {
  const labels = <?= json_encode(array_map(
    fn($d) => date('d/m', strtotime($d)), $grafikLabel
  )) ?>;
  const data = <?= json_encode(array_values($grafikData)) ?>;

  new Chart(document.getElementById('chartHarian'), {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: 'Pendapatan (Rp)',
          data,
          backgroundColor: 'rgba(37,99,235,0.08)',
          borderColor: '#2563eb',
          borderWidth: 1.5,
          borderRadius: 4,
          order: 2,
        },
        {
          type: 'line',
          label: 'Tren',
          data,
          borderColor: '#f59e0b',
          borderWidth: 2,
          pointRadius: 0,
          tension: 0.4,
          fill: false,
          order: 1,
        }
      ]
    },
    options: {
      responsive: true,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { display: true, labels: { font: { size: 11 }, boxWidth: 12, color: '#64748b' } },
        tooltip: {
          filter: item => item.dataset.order !== 1,
          callbacks: {
            label: ctx => ' Rp ' + ctx.parsed.y.toLocaleString('id-ID'),
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: {
            font: { size: 10 }, color: '#94a3b8',
            maxTicksLimit: <?= count($grafikLabel) > 30 ? 10 : 15 ?>,
          }
        },
        y: {
          beginAtZero: true,
          grid: { color: '#f8fafc' },
          ticks: {
            font: { size: 10 }, color: '#94a3b8',
            callback: v => v >= 1000000
              ? 'Rp ' + (v/1000000).toFixed(1) + 'jt'
              : 'Rp ' + (v/1000).toFixed(0) + 'k'
          }
        }
      }
    }
  });
})();
</script>