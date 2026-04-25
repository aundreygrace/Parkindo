<?php
/**
 * views/dashboard/owner.php
 * Dashboard khusus Owner
 * Variabel: $stats, $grafikLabel, $grafikData, $jenisData,
 *           $trendPersen, $rekap, $areas
 */
?>

<!-- ── KPI CARDS ───────────────────────────────────────────── -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

  <!-- Pendapatan Hari Ini -->
  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Pendapatan Hari Ini</span>
      <span class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center">
        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1"/>
        </svg>
      </span>
    </div>
    <div class="text-xl font-bold text-slate-800"><?= formatRupiah($stats['pendapatan_hari']) ?></div>
    <div class="flex items-center gap-1 mt-1">
      <?php if ($trendPersen['naik']): ?>
        <svg class="w-3 h-3 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/>
        </svg>
        <span class="text-xs text-green-600 font-medium"><?= $trendPersen['persen'] ?>% vs minggu lalu</span>
      <?php else: ?>
        <svg class="w-3 h-3 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
        </svg>
        <span class="text-xs text-red-500 font-medium"><?= $trendPersen['persen'] ?>% vs minggu lalu</span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Total Pendapatan 30 Hari -->
  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">30 Hari Terakhir</span>
      <span class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center">
        <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
      </span>
    </div>
    <div class="text-xl font-bold text-slate-800"><?= formatRupiah($stats['total_pendapatan']) ?></div>
    <div class="text-xs text-slate-400 mt-1">Total bruto</div>
  </div>

  <!-- Total Transaksi 30 Hari -->
  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Transaksi</span>
      <span class="w-9 h-9 rounded-lg bg-teal-50 flex items-center justify-center">
        <svg class="w-5 h-5 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
      </span>
    </div>
    <div class="text-3xl font-bold text-slate-800"><?= number_format($stats['total_transaksi']) ?></div>
    <div class="text-xs text-slate-400 mt-1">Transaksi selesai (30 hari)</div>
  </div>

  <!-- Rata-rata Per Transaksi -->
  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Rata-rata / Trx</span>
      <span class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center">
        <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
        </svg>
      </span>
    </div>
    <div class="text-xl font-bold text-slate-800"><?= formatRupiah($stats['rata_pendapatan']) ?></div>
    <div class="text-xs text-slate-400 mt-1">Per transaksi keluar</div>
  </div>

</div>

<!-- ── GRAFIK PENDAPATAN 30 HARI ────────────────────────────── -->
<div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
  <div class="flex items-center justify-between mb-4">
    <div>
      <h3 class="text-sm font-semibold text-slate-700">Tren Pendapatan 30 Hari Terakhir</h3>
      <p class="text-xs text-slate-400 mt-0.5">Berdasarkan tanggal keluar kendaraan</p>
    </div>
    <a href="?page=laporan&action=index"
       class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">Lihat Laporan Lengkap →</a>
  </div>
  <canvas id="chartPendapatan30" height="80"></canvas>
</div>

<!-- ── ROW: PIE CHART + BREAKDOWN JENIS + STATUS AREA ──────── -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

  <!-- Doughnut Jenis Kendaraan -->
  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <h3 class="text-sm font-semibold text-slate-700 mb-1">Komposisi Kendaraan</h3>
    <p class="text-xs text-slate-400 mb-4">Hari ini</p>
    <canvas id="chartJenis" height="200"></canvas>
    <div class="mt-4 space-y-2">
      <?php
      $jenisLabel = ['motor'=>'Motor','mobil'=>'Mobil','lainnya'=>'Lainnya'];
      $jenisColor = ['motor'=>'bg-indigo-500','mobil'=>'bg-violet-500','lainnya'=>'bg-amber-400'];
      $total      = array_sum($jenisData);
      foreach ($jenisData as $jenis => $val):
        $pct = $total > 0 ? round($val/$total*100) : 0;
      ?>
      <div class="flex items-center justify-between text-xs">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full <?= $jenisColor[$jenis] ?>"></span>
          <span class="text-slate-600"><?= $jenisLabel[$jenis] ?></span>
        </div>
        <span class="font-semibold text-slate-700">
          <?= $val ?> <span class="text-slate-400 font-normal">(<?= $pct ?>%)</span>
        </span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Breakdown Rekap 30 Hari -->
  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <h3 class="text-sm font-semibold text-slate-700 mb-4">Rekap 30 Hari</h3>
    <div class="space-y-4">
      <?php
      $items = [
        ['label' => 'Total Motor',   'val' => $rekap['total_motor']   ?? 0, 'icon' => '🛵', 'color' => 'text-indigo-600'],
        ['label' => 'Total Mobil',   'val' => $rekap['total_mobil']   ?? 0, 'icon' => '🚗', 'color' => 'text-violet-600'],
        ['label' => 'Lainnya',       'val' => $rekap['total_lainnya'] ?? 0, 'icon' => '🚛', 'color' => 'text-amber-600'],
        ['label' => 'Rata-rata Durasi', 'val' => formatDurasi((float)($rekap['rata_durasi'] ?? 0)), 'icon' => '⏱', 'color' => 'text-teal-600'],
      ];
      foreach ($items as $item):
      ?>
      <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
        <div class="flex items-center gap-2">
          <span class="text-base" style="font-size:16px"><?= $item['icon'] ?></span>
          <span class="text-sm text-slate-600"><?= $item['label'] ?></span>
        </div>
        <span class="font-semibold text-sm <?= $item['color'] ?>">
          <?= is_numeric($item['val']) ? number_format((int)$item['val']) : $item['val'] ?>
        </span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Status Area -->
  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <h3 class="text-sm font-semibold text-slate-700 mb-4">Status Area Saat Ini</h3>
    <div class="space-y-4">
      <?php foreach ($areas as $area):
        $pct   = persenTerisi((int)$area['kapasitas'], (int)$area['terisi']);
        $warn  = isAreaHampirPenuh((int)$area['kapasitas'], (int)$area['terisi']);
        $color = $pct >= 100 ? 'bg-red-500' : ($warn ? 'bg-amber-400' : 'bg-indigo-500');
      ?>
      <div>
        <div class="flex justify-between items-center mb-1.5">
          <span class="text-xs font-medium text-slate-700"><?= e($area['nama_area']) ?></span>
          <span class="text-xs font-bold <?= $pct >= 100 ? 'text-red-600' : ($warn ? 'text-amber-600' : 'text-slate-500') ?>">
            <?= $pct ?>%
          </span>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-2">
          <div class="h-2 rounded-full <?= $color ?>" style="width: <?= $pct ?>%"></div>
        </div>
        <p class="text-[11px] text-slate-400 mt-1">
          <?= $area['terisi'] ?> terisi · <?= $area['kapasitas'] - $area['terisi'] ?> tersedia
        </p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<!-- ── CHART.JS SCRIPTS ────────────────────────────────────── -->
<script>
(function () {
  const blue   = '#667eea';
  const blueBg = 'rgba(102,126,234,0.07)';

  // Label tanggal ringkas (dd/MM)
  const labels30 = <?= json_encode(array_map(
    fn($d) => date('d/m', strtotime($d)),
    $grafikLabel
  )) ?>;
  const data30   = <?= json_encode(array_values($grafikData)) ?>;

  // ── Bar + Line Chart Pendapatan 30 Hari ──────────────────
  new Chart(document.getElementById('chartPendapatan30'), {
    type: 'bar',
    data: {
      labels: labels30,
      datasets: [
        {
          label: 'Pendapatan (Rp)',
          data: data30,
          backgroundColor: blueBg,
          borderColor: blue,
          borderWidth: 1.5,
          borderRadius: 4,
          order: 2,
        },
        {
          label: 'Tren',
          data: data30,
          type: 'line',
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
        legend: {
          display: true,
          labels: { font: { size: 11 }, boxWidth: 12, color: '#64748b' }
        },
        tooltip: {
          callbacks: {
            label: ctx => ctx.dataset.label === 'Tren'
              ? null
              : ' Rp ' + ctx.parsed.y.toLocaleString('id-ID'),
          },
          filter: item => item.dataset.order !== 1,
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: {
            font: { size: 10 }, color: '#94a3b8',
            maxTicksLimit: 10,
          }
        },
        y: {
          beginAtZero: true,
          grid: { color: '#f8fafc' },
          ticks: {
            font: { size: 10 }, color: '#94a3b8',
            callback: v => 'Rp ' + (v >= 1000000
              ? (v/1000000).toFixed(1) + 'jt'
              : (v/1000).toFixed(0) + 'k')
          }
        }
      }
    }
  });

  // ── Doughnut Jenis Kendaraan ──────────────────────────────
  const jenisData  = <?= json_encode(array_values($jenisData)) ?>;
  const totalJenis = jenisData.reduce((a,b) => a+b, 0);

  new Chart(document.getElementById('chartJenis'), {
    type: 'doughnut',
    data: {
      labels: ['Motor', 'Mobil', 'Lainnya'],
      datasets: [{
        data: jenisData,
        backgroundColor: ['#667eea', '#8b5cf6', '#f59e0b'],
        borderWidth: 0,
        hoverOffset: 4,
      }]
    },
    options: {
      responsive: true,
      cutout: '70%',
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => {
              const pct = totalJenis > 0 ? Math.round(ctx.parsed/totalJenis*100) : 0;
              return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
            }
          }
        }
      }
    }
  });
})();
</script>