<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Struk <?= e($transaksi['kode_parkir']) ?> — <?= APP_NAME ?></title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Courier New', Courier, monospace;
      font-size: 12px;
      background: #f8fafc;
      display: flex;
      justify-content: center;
      padding: 20px;
      min-height: 100vh;
    }

    .receipt {
      background: #fff;
      width: 300px;
      padding: 20px 16px;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
    }

    .receipt-header {
      text-align: center;
      border-bottom: 2px dashed #cbd5e1;
      padding-bottom: 12px;
      margin-bottom: 12px;
    }

    .receipt-header .logo {
      font-size: 18px;
      font-weight: bold;
      letter-spacing: 2px;
      color: #1e293b;
    }

    .receipt-header .tagline {
      font-size: 10px;
      color: #64748b;
      margin-top: 2px;
    }

    .receipt-header .kode {
      margin-top: 8px;
      font-size: 14px;
      font-weight: bold;
      color: #1e293b;
      background: #f1f5f9;
      padding: 4px 10px;
      border-radius: 4px;
      display: inline-block;
      letter-spacing: 1px;
    }

    .type-badge {
      display: inline-block;
      margin-top: 6px;
      font-size: 11px;
      font-weight: bold;
      padding: 3px 10px;
      border-radius: 20px;
      letter-spacing: 1px;
    }

    .type-masuk  { background: #dbeafe; color: #1d4ed8; }
    .type-keluar { background: #dcfce7; color: #166534; }

    .section {
      margin-bottom: 12px;
      border-bottom: 1px dashed #e2e8f0;
      padding-bottom: 10px;
    }

    .section:last-child {
      border-bottom: none;
      margin-bottom: 0;
    }

    .row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 5px;
      line-height: 1.5;
    }

    .row .label { color: #64748b; }
    .row .value { font-weight: bold; color: #1e293b; text-align: right; }

    .plat {
      text-align: center;
      font-size: 22px;
      font-weight: bold;
      letter-spacing: 3px;
      color: #1e293b;
      background: #f1f5f9;
      padding: 8px;
      border-radius: 6px;
      margin: 8px 0;
    }

    .total-row {
      display: flex;
      justify-content: space-between;
      font-size: 14px;
      font-weight: bold;
      background: #1e293b;
      color: #fff;
      padding: 8px 10px;
      border-radius: 6px;
      margin: 8px 0;
    }

    .kembalian-row {
      display: flex;
      justify-content: space-between;
      color: #16a34a;
      font-weight: bold;
      font-size: 13px;
      margin-top: 4px;
    }

    .footer {
      text-align: center;
      color: #94a3b8;
      font-size: 10px;
      margin-top: 14px;
      line-height: 1.8;
    }

    /* Tombol cetak — hilang saat print */
    .print-actions {
      margin-top: 16px;
      display: flex;
      gap: 8px;
    }

    .btn-print {
      flex: 1;
      padding: 10px;
      background: #1e293b;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-family: inherit;
      font-size: 12px;
      font-weight: bold;
      cursor: pointer;
      letter-spacing: 1px;
    }

    .btn-back {
      flex: 1;
      padding: 10px;
      background: #f1f5f9;
      color: #475569;
      border: none;
      border-radius: 8px;
      font-family: inherit;
      font-size: 12px;
      cursor: pointer;
      text-decoration: none;
      text-align: center;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    @media print {
      body { background: white; padding: 0; }
      .receipt { border: none; border-radius: 0; padding: 0; }
      .print-actions { display: none; }
    }
  </style>
</head>
<body>

<div class="receipt">

  <!-- Header -->
  <div class="receipt-header">
    <div class="logo"><?= strtoupper(APP_NAME) ?></div>
    <div class="tagline"><?= APP_TAGLINE ?></div>
    <div class="kode"><?= e($transaksi['kode_parkir']) ?></div>
    <div>
      <span class="type-badge <?= $type === 'masuk' ? 'type-masuk' : 'type-keluar' ?>">
        TIKET <?= strtoupper($type) ?>
      </span>
    </div>
  </div>

  <!-- Data Kendaraan -->
  <div class="section">
    <div class="plat"><?= e($transaksi['plat_nomor']) ?></div>
    <div class="row">
      <span class="label">Jenis</span>
      <span class="value"><?= ucfirst(e($transaksi['jenis_kendaraan'])) ?></span>
    </div>
    <div class="row">
      <span class="label">Warna</span>
      <span class="value"><?= e($transaksi['warna']) ?></span>
    </div>
    <div class="row">
      <span class="label">Pemilik</span>
      <span class="value"><?= e($transaksi['pemilik']) ?></span>
    </div>
  </div>

  <!-- Waktu & Area -->
  <div class="section">
    <div class="row">
      <span class="label">Area</span>
      <span class="value"><?= e($transaksi['nama_area']) ?></span>
    </div>
    <div class="row">
      <span class="label">Waktu Masuk</span>
      <span class="value"><?= date('d/m/Y H:i', strtotime($transaksi['waktu_masuk'])) ?></span>
    </div>
    <?php if ($type === 'keluar' && $transaksi['waktu_keluar']): ?>
    <div class="row">
      <span class="label">Waktu Keluar</span>
      <span class="value"><?= date('d/m/Y H:i', strtotime($transaksi['waktu_keluar'])) ?></span>
    </div>
    <div class="row">
      <span class="label">Durasi</span>
      <span class="value"><?= formatDurasi((float)$transaksi['durasi_jam']) ?></span>
    </div>
    <?php endif; ?>
  </div>

  <!-- Tarif & Pembayaran (hanya untuk tiket keluar) -->
  <?php if ($type === 'keluar' && $transaksi['waktu_keluar']): ?>
  <div class="section">
    <div class="row">
      <span class="label">Tarif Masuk</span>
      <span class="value"><?= formatRupiah($transaksi['tarif_masuk']) ?></span>
    </div>
    <div class="row">
      <span class="label">Tarif/Jam</span>
      <span class="value"><?= formatRupiah($transaksi['tarif_per_jam']) ?></span>
    </div>
    <div class="row">
      <span class="label">Durasi Tagih</span>
      <span class="value"><?= number_format((float)$transaksi['durasi_jam'], 0) ?> jam</span>
    </div>
  </div>

  <div class="total-row">
    <span>TOTAL</span>
    <span><?= formatRupiah($transaksi['biaya_total']) ?></span>
  </div>

  <div class="section">
    <div class="row">
      <span class="label">Bayar</span>
      <span class="value"><?= formatRupiah($transaksi['bayar']) ?></span>
    </div>
    <div class="kembalian-row">
      <span>Kembalian</span>
      <span><?= formatRupiah($transaksi['kembalian']) ?></span>
    </div>
  </div>
  <?php else: ?>
  <!-- Tiket masuk: tampilkan tarif saja -->
  <div class="section">
    <div class="row">
      <span class="label">Tarif/Jam</span>
      <span class="value"><?= formatRupiah($transaksi['tarif_per_jam']) ?></span>
    </div>
    <div class="row">
      <span class="label">Biaya Masuk</span>
      <span class="value"><?= formatRupiah($transaksi['tarif_masuk']) ?></span>
    </div>
    <div class="row" style="margin-top:6px;font-size:11px;color:#94a3b8;">
      <span style="font-style:italic">*Biaya dihitung saat kendaraan keluar</span>
    </div>
  </div>
  <?php endif; ?>

  <!-- Petugas & Waktu Cetak -->
  <div class="section">
    <div class="row">
      <span class="label">Petugas</span>
      <span class="value"><?= e($transaksi['nama_petugas']) ?></span>
    </div>
    <div class="row">
      <span class="label">Dicetak</span>
      <span class="value"><?= date('d/m/Y H:i:s') ?></span>
    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    <div>Terima kasih telah menggunakan</div>
    <div><?= APP_NAME ?> — <?= APP_TAGLINE ?></div>
    <div style="margin-top:4px;">Simpan struk ini sebagai bukti parkir</div>
  </div>

  <!-- Tombol Aksi -->
  <div class="print-actions">
    <button class="btn-print" onclick="window.print()">CETAK</button>
    <a href="?page=transaksi&action=index" class="btn-back">KEMBALI</a>
  </div>

</div>

<script>
  // Auto-print saat halaman pertama kali dibuka (bisa dimatikan jika tidak perlu)
  window.addEventListener('load', function () {
    // Tunda sedikit agar layout selesai render
    setTimeout(() => window.print(), 600);
  });
</script>
</body>
</html>