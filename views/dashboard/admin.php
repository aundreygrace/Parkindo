<?php
/**
 * views/dashboard/admin.php
 * Dashboard khusus Admin
 * Variabel tersedia dari DashboardController::renderAdmin():
 *   $stats, $areas, $logs, $grafikLabel, $grafikData, $jenisData
 */
?>

<!-- ── STAT CARDS ──────────────────────────────────────────── -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

  <!-- Total User Aktif -->
  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">User Aktif</span>
      <span class="w-9 h-9 rounded-lg bg-purple-50 flex items-center justify-center">
        <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/>
        </svg>
      </span>
    </div>
    <div class="text-3xl font-bold text-slate-800"><?= $stats['total_user'] ?></div>
    <div class="text-xs text-slate-400 mt-1">Pengguna terdaftar</div>
  </div>

  <!-- Kendaraan Parkir Sekarang -->
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

  <!-- Transaksi Hari Ini -->
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
    <div class="text-xs text-slate-400 mt-1">Total transaksi masuk + keluar</div>
  </div>

  <!-- Pendapatan Hari Ini -->
  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Pendapatan Hari Ini</span>
      <span class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center">
        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
        </svg>
      </span>
    </div>
    <div class="text-2xl font-bold text-slate-800"><?= formatRupiah($stats['pendapatan_hari']) ?></div>
    <div class="text-xs text-slate-400 mt-1">Dari transaksi keluar hari ini</div>
  </div>

</div>

<!-- ── ROW 2: GRAFIK + PIE CHART ───────────────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

  <!-- Line Chart Pendapatan 7 Hari -->
  <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-5">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h3 class="text-sm font-semibold text-slate-700">Pendapatan 7 Hari Terakhir</h3>
        <p class="text-xs text-slate-400 mt-0.5">Berdasarkan transaksi keluar</p>
      </div>
    </div>
    <canvas id="chartPendapatan" height="110"></canvas>
  </div>

  <!-- Pie Chart Jenis Kendaraan -->
  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <div class="mb-4">
      <h3 class="text-sm font-semibold text-slate-700">Jenis Kendaraan Hari Ini</h3>
      <p class="text-xs text-slate-400 mt-0.5">Komposisi transaksi masuk</p>
    </div>
    <canvas id="chartJenis" height="180"></canvas>
    <!-- Legend manual -->
    <div class="mt-4 space-y-1.5">
      <?php
      $jenisLabel = ['motor' => 'Motor', 'mobil' => 'Mobil', 'lainnya' => 'Lainnya'];
      $jenisColor = ['motor' => 'bg-indigo-500', 'mobil' => 'bg-violet-500', 'lainnya' => 'bg-amber-400'];
      $totalJenis = array_sum($jenisData);
      foreach ($jenisData as $jenis => $val):
        $pct = $totalJenis > 0 ? round($val / $totalJenis * 100) : 0;
      ?>
      <div class="flex items-center justify-between text-xs">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full <?= $jenisColor[$jenis] ?>"></span>
          <span class="text-slate-600"><?= $jenisLabel[$jenis] ?></span>
        </div>
        <span class="font-semibold text-slate-700"><?= $val ?> <span class="text-slate-400 font-normal">(<?= $pct ?>%)</span></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<!-- ── ROW 3: STATUS AREA + LOG AKTIVITAS ──────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

  <!-- Status Area Parkir -->
  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-sm font-semibold text-slate-700">Status Area Parkir</h3>
      <a href="?page=area&action=index"
      class="text-xs font-medium bg-[linear-gradient(180deg,rgba(27,43,82,0.98)_0%,rgba(3,16,75,0.98)_100%)] bg-clip-text text-transparent">
      Kelola →
    </a>
    </div>
    <div class="space-y-4">
      <?php if (empty($areas)): ?>
        <p class="text-sm text-slate-400 text-center py-4">Belum ada area parkir.</p>
      <?php endif; ?>
      <?php foreach ($areas as $area):
        $pct   = persenTerisi((int)$area['kapasitas'], (int)$area['terisi']);
        $warn  = isAreaHampirPenuh((int)$area['kapasitas'], (int)$area['terisi']);
        $color = $pct >= 100 ? 'bg-red-500' : ($warn ? 'bg-amber-400' : 'bg-indigo-500');
        $badge = $pct >= 100 ? 'bg-red-50 text-red-700' : ($warn ? 'bg-amber-50 text-amber-700' : 'bg-green-50 text-green-700');
        $label = $pct >= 100 ? 'Penuh' : ($warn ? 'Hampir Penuh' : 'Tersedia');
      ?>
      <div>
        <div class="flex items-center justify-between mb-1.5">
          <span class="text-sm font-medium text-slate-700"><?= e($area['nama_area']) ?></span>
          <div class="flex items-center gap-2">
            <span class="text-xs text-slate-400"><?= $area['terisi'] ?>/<?= $area['kapasitas'] ?></span>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= $badge ?>"><?= $label ?></span>
          </div>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-2">
          <div class="h-2 rounded-full transition-all duration-500 <?= $color ?>"
               style="width: <?= $pct ?>%"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Log Aktivitas Terbaru -->
  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-sm font-semibold text-slate-700">Log Aktivitas Terbaru</h3>
      <a href="?page=log" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">Lihat Semua →</a>
    </div>
    <div class="space-y-0 divide-y divide-slate-100">
      <?php if (empty($logs)): ?>
        <p class="text-sm text-slate-400 text-center py-4">Belum ada aktivitas.</p>
      <?php endif; ?>
      <?php
      $roleBadge = [
        'admin'   => 'bg-purple-50 text-purple-700',
        'petugas' => 'bg-indigo-50 text-indigo-700',
        'owner'   => 'bg-amber-50 text-amber-700',
      ];
      foreach ($logs as $log):
        $badge = $roleBadge[$log['role']] ?? 'bg-slate-100 text-slate-600';
      ?>
      <div class="flex items-start gap-3 py-2.5">
        <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0 mt-0.5">
          <span class="text-xs font-bold text-slate-500">
            <?= strtoupper(substr($log['nama_lengkap'], 0, 1)) ?>
          </span>
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-0.5">
            <span class="text-xs font-semibold text-slate-700"><?= e($log['nama_lengkap']) ?></span>
            <span class="text-[10px] px-1.5 py-0.5 rounded font-medium <?= $badge ?>">
              <?= e($log['role']) ?>
            </span>
          </div>
          <p class="text-xs text-slate-500 truncate"><?= e($log['aktivitas']) ?></p>
        </div>
        <span class="text-[10px] text-slate-400 flex-shrink-0 mt-0.5">
          <?= date('H:i', strtotime($log['waktu_aktivitas'])) ?>
        </span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<!-- ── CHART.JS SCRIPTS ────────────────────────────────────── -->
<script>
(function () {
  // ── Palet warna ──────────────────────────────────────────
  const blue   = '#667eea';
  const blueBg = 'rgba(102,126,234,0.08)';

  // ── Label tanggal — tampilkan ringkas (dd/MM) ─────────────
  const rawLabels = <?= json_encode(array_map(
    fn($d) => date('d/m', strtotime($d)),
    $grafikLabel
  )) ?>;

  const rawData = <?= json_encode(array_values($grafikData)) ?>;

  // ── Line Chart: Pendapatan ────────────────────────────────
  new Chart(document.getElementById('chartPendapatan'), {
    type: 'line',
    data: {
      labels: rawLabels,
      datasets: [{
        label: 'Pendapatan (Rp)',
        data: rawData,
        borderColor: blue,
        backgroundColor: blueBg,
        borderWidth: 2,
        pointBackgroundColor: blue,
        pointRadius: 3,
        pointHoverRadius: 5,
        fill: true,
        tension: 0.4,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID'),
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { font: { size: 11 }, color: '#94a3b8' }
        },
        y: {
          beginAtZero: true,
          grid: { color: '#f1f5f9' },
          ticks: {
            font: { size: 11 }, color: '#94a3b8',
            callback: v => 'Rp ' + (v / 1000).toFixed(0) + 'k'
          }
        }
      }
    }
  });

  // ── Pie Chart: Jenis Kendaraan ────────────────────────────
  const jenisData = <?= json_encode(array_values($jenisData)) ?>;
  const totalJenis = jenisData.reduce((a, b) => a + b, 0);

  new Chart(document.getElementById('chartJenis'), {
    type: 'doughnut',
    data: {
      labels: ['Motor', 'Mobil', 'Lainnya'],
      datasets: [{
        data: jenisData,
        backgroundColor: ['#667eea', '#764ba2', '#f59e0b'],
        borderWidth: 0,
        hoverOffset: 4,
      }]
    },
    options: {
      responsive: true,
      cutout: '68%',
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => {
              const pct = totalJenis > 0 ? Math.round(ctx.parsed / totalJenis * 100) : 0;
              return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
            }
          }
        }
      }
    }
  });
})();
</script>