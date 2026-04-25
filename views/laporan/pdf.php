<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Parkir <?= e($dari) ?> s/d <?= e($sampai) ?></title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: Arial, sans-serif;
      font-size: 11px;
      color: #1e293b;
      background: #fff;
      padding: 24px;
    }

    /* ── Header ── */
    .header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      border-bottom: 3px solid #1e293b;
      padding-bottom: 14px;
      margin-bottom: 18px;
    }
    .header-left .app-name {
      font-size: 22px;
      font-weight: 900;
      letter-spacing: 2px;
      color: #1e293b;
    }
    .header-left .app-sub {
      font-size: 10px;
      color: #64748b;
      margin-top: 2px;
    }
    .header-right {
      text-align: right;
    }
    .header-right .doc-title {
      font-size: 14px;
      font-weight: 700;
      color: #1e293b;
    }
    .header-right .doc-period {
      font-size: 10px;
      color: #64748b;
      margin-top: 3px;
    }
    .header-right .doc-generated {
      font-size: 9px;
      color: #94a3b8;
      margin-top: 2px;
    }

    /* ── KPI Cards ── */
    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 10px;
      margin-bottom: 18px;
    }
    .kpi-card {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 10px 12px;
    }
    .kpi-label {
      font-size: 9px;
      font-weight: 700;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 4px;
    }
    .kpi-value {
      font-size: 14px;
      font-weight: 900;
      color: #1e293b;
    }
    .kpi-sub { font-size: 9px; color: #94a3b8; margin-top: 2px; }

    /* ── Section Title ── */
    .section-title {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      color: #64748b;
      border-bottom: 1px solid #e2e8f0;
      padding-bottom: 5px;
      margin: 14px 0 10px;
    }

    /* ── Breakdown Grid ── */
    .breakdown-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-bottom: 14px;
    }
    .breakdown-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 10px;
    }
    .breakdown-table th {
      background: #f1f5f9;
      padding: 5px 8px;
      text-align: left;
      font-weight: 600;
      color: #475569;
      font-size: 9px;
      text-transform: uppercase;
    }
    .breakdown-table td {
      padding: 5px 8px;
      border-bottom: 1px solid #f1f5f9;
      color: #334155;
    }
    .breakdown-table tr:last-child td { border-bottom: none; }
    .text-right { text-align: right; }
    .font-bold  { font-weight: 700; }

    /* ── Tabel Transaksi ── */
    .trx-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 9.5px;
    }
    .trx-table thead tr {
      background: #1e293b;
      color: #fff;
    }
    .trx-table th {
      padding: 6px 8px;
      text-align: left;
      font-weight: 600;
      font-size: 9px;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }
    .trx-table th.text-right { text-align: right; }
    .trx-table tbody tr:nth-child(even) { background: #f8fafc; }
    .trx-table tbody tr:hover { background: #f1f5f9; }
    .trx-table td {
      padding: 5px 8px;
      border-bottom: 1px solid #f1f5f9;
      color: #334155;
    }
    .trx-table tfoot tr {
      background: #f1f5f9;
      font-weight: 700;
      border-top: 2px solid #cbd5e1;
    }
    .trx-table tfoot td {
      padding: 7px 8px;
      font-size: 10px;
    }
    .plat-badge {
      font-family: 'Courier New', monospace;
      font-weight: 700;
      background: #e2e8f0;
      padding: 1px 5px;
      border-radius: 3px;
      font-size: 9px;
    }

    /* ── Footer ── */
    .footer {
      margin-top: 20px;
      padding-top: 10px;
      border-top: 1px solid #e2e8f0;
      display: flex;
      justify-content: space-between;
      font-size: 9px;
      color: #94a3b8;
    }

    /* ── TTD ── */
    .ttd-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
      margin-top: 30px;
    }
    .ttd-box {
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      padding: 12px;
      text-align: center;
    }
    .ttd-label { font-size: 9px; color: #64748b; margin-bottom: 50px; }
    .ttd-line  {
      border-top: 1px solid #1e293b;
      padding-top: 5px;
      font-size: 10px;
      font-weight: 700;
      color: #1e293b;
    }

    /* ── Print ── */
    @media print {
      body { padding: 8px; }
      .no-print { display: none; }
      .trx-table { page-break-inside: auto; }
      .trx-table tr { page-break-inside: avoid; }
    }
  </style>
</head>
<body>

  <!-- Header -->
  <div class="header">
    <div class="header-left">
      <div class="app-name"><?= strtoupper(APP_NAME) ?></div>
      <div class="app-sub"><?= APP_TAGLINE ?></div>
    </div>
    <div class="header-right">
      <div class="doc-title">LAPORAN TRANSAKSI PARKIR</div>
      <div class="doc-period">
        Periode: <?= formatTanggal($dari.' 00:00:00', false) ?> s/d <?= formatTanggal($sampai.' 00:00:00', false) ?>
      </div>
      <div class="doc-generated">Dicetak: <?= date('d/m/Y H:i:s') ?> oleh <?= e($_SESSION['user_nama']) ?></div>
    </div>
  </div>

  <!-- KPI -->
  <div class="kpi-grid">
    <div class="kpi-card">
      <div class="kpi-label">Total Pendapatan</div>
      <div class="kpi-value"><?= formatRupiah($rekap['total_pendapatan'] ?? 0) ?></div>
      <div class="kpi-sub">periode terpilih</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Total Transaksi</div>
      <div class="kpi-value"><?= number_format((int)($rekap['total_transaksi'] ?? 0)) ?></div>
      <div class="kpi-sub">transaksi selesai</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Rata-rata / Transaksi</div>
      <div class="kpi-value"><?= formatRupiah((float)($rekap['rata_pendapatan'] ?? 0)) ?></div>
      <div class="kpi-sub">per kendaraan keluar</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Rata-rata Durasi</div>
      <div class="kpi-value"><?= formatDurasi((float)($rekap['rata_durasi'] ?? 0)) ?></div>
      <div class="kpi-sub">per kendaraan</div>
    </div>
  </div>

  <!-- Breakdown -->
  <div class="breakdown-grid">
    <!-- Per Jenis -->
    <div>
      <div class="section-title">Breakdown Per Jenis Kendaraan</div>
      <table class="breakdown-table">
        <thead>
          <tr>
            <th>Jenis</th>
            <th class="text-right">Jumlah</th>
            <th class="text-right">Pendapatan</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($perJenis as $j): ?>
          <tr>
            <td class="font-bold"><?= ucfirst(e($j['jenis_kendaraan'])) ?></td>
            <td class="text-right"><?= number_format((int)$j['jumlah']) ?></td>
            <td class="text-right font-bold"><?= formatRupiah($j['pendapatan']) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($perJenis)): ?>
          <tr><td colspan="3" style="color:#94a3b8;text-align:center;padding:8px">Tidak ada data</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Per Area -->
    <div>
      <div class="section-title">Breakdown Per Area Parkir</div>
      <table class="breakdown-table">
        <thead>
          <tr>
            <th>Area</th>
            <th class="text-right">Jumlah</th>
            <th class="text-right">Pendapatan</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($perArea as $a): ?>
          <tr>
            <td class="font-bold"><?= e($a['nama_area']) ?></td>
            <td class="text-right"><?= number_format((int)$a['jumlah']) ?></td>
            <td class="text-right font-bold"><?= formatRupiah($a['pendapatan']) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($perArea)): ?>
          <tr><td colspan="3" style="color:#94a3b8;text-align:center;padding:8px">Tidak ada data</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Tabel Transaksi -->
  <div class="section-title">Detail Transaksi (<?= count($transaksi) ?> data)</div>
  <table class="trx-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Kode Tiket</th>
        <th>Plat Nomor</th>
        <th>Jenis</th>
        <th>Pemilik</th>
        <th>Area</th>
        <th>Masuk</th>
        <th>Keluar</th>
        <th>Durasi</th>
        <th class="text-right">Biaya</th>
        <th>Petugas</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $totalBiaya = 0;
      foreach ($transaksi as $i => $t):
        $totalBiaya += $t['biaya_total'];
      ?>
      <tr>
        <td><?= $i + 1 ?></td>
        <td><span style="font-family:monospace;font-size:8.5px"><?= e($t['kode_parkir']) ?></span></td>
        <td><span class="plat-badge"><?= e($t['plat_nomor']) ?></span></td>
        <td><?= ucfirst(e($t['jenis_kendaraan'])) ?></td>
        <td><?= e($t['pemilik']) ?></td>
        <td><?= e($t['nama_area']) ?></td>
        <td style="white-space:nowrap"><?= $t['waktu_masuk'] ? date('d/m H:i', strtotime($t['waktu_masuk'])) : '—' ?></td>
        <td style="white-space:nowrap"><?= $t['waktu_keluar'] ? date('d/m H:i', strtotime($t['waktu_keluar'])) : '—' ?></td>
        <td><?= $t['durasi_jam'] > 0 ? formatDurasi((float)$t['durasi_jam']) : '—' ?></td>
        <td class="text-right font-bold"><?= formatRupiah($t['biaya_total']) ?></td>
        <td><?= e($t['petugas']) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($transaksi)): ?>
      <tr><td colspan="11" style="text-align:center;padding:12px;color:#94a3b8">Tidak ada data transaksi</td></tr>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="9" class="text-right">TOTAL PENDAPATAN</td>
        <td class="text-right"><?= formatRupiah($totalBiaya) ?></td>
        <td></td>
      </tr>
    </tfoot>
  </table>

  <!-- TTD -->
  <div class="ttd-grid">
    <div class="ttd-box">
      <div class="ttd-label">Dibuat oleh,</div>
      <div class="ttd-line"><?= e($_SESSION['user_nama']) ?><br><small style="font-weight:400;color:#64748b">Owner</small></div>
    </div>
    <div class="ttd-box">
      <div class="ttd-label">Mengetahui,</div>
      <div class="ttd-line">____________________<br><small style="font-weight:400;color:#64748b">Pimpinan</small></div>
    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    <span><?= APP_NAME ?> — <?= APP_TAGLINE ?></span>
    <span>Laporan ini digenerate otomatis oleh sistem pada <?= date('d/m/Y H:i:s') ?></span>
  </div>

  <!-- Tombol (hilang saat print) -->
  <div class="no-print" style="margin-top:24px;display:flex;gap:10px;justify-content:center">
    <button onclick="window.print()"
            style="padding:10px 24px;background:#1e293b;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;">
      Cetak / Save PDF
    </button>
    <button onclick="window.close()"
            style="padding:10px 24px;background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;cursor:pointer;">
      Tutup
    </button>
  </div>

<script>
  window.addEventListener('load', () => setTimeout(() => window.print(), 700));
</script>
</body>
</html>