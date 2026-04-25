<?php
/**
 * views/tarif/index.php
 * Variabel: $tarifs
 */
require_once VIEW_PATH . '/layouts/components.php';

$jenisIcon = [
    'motor' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="width:32px;height:32px">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
    </svg>',
    'mobil' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="width:32px;height:32px">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h8m-4-4v4M5 11l1.5 7h11L19 11M5 11H3m16 0h2M7 18a1 1 0 100 2 1 1 0 000-2zm10 0a1 1 0 100 2 1 1 0 000-2z"/>
    </svg>',
    'lainnya' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="width:32px;height:32px">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M3 17l3-9h12l3 9M5 17h14M7 20a1 1 0 100 2 1 1 0 000-2zm10 0a1 1 0 100 2 1 1 0 000-2z"/>
    </svg>',
];

// Indigo-purple palette matching sidebar
$jenisColor = [
    'motor'   => ['border' => '#c7d2fe', 'bg' => '#eef2ff', 'icon' => '#667eea', 'badge' => '#e0e7ff', 'badgeText' => '#3730a3'],
    'mobil'   => ['border' => '#ddd6fe', 'bg' => '#f5f3ff', 'icon' => '#7c3aed', 'badge' => '#ede9fe', 'badgeText' => '#5b21b6'],
    'lainnya' => ['border' => '#fde68a', 'bg' => '#fffbeb', 'icon' => '#d97706', 'badge' => '#fef3c7', 'badgeText' => '#92400e'],
];

$allJenis   = ['motor', 'mobil', 'lainnya'];
$usedJenis  = array_column($tarifs, 'jenis_kendaraan');
$canAddMore = count($usedJenis) < count($allJenis);
?>

<!-- Toolbar -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:12px;flex-wrap:wrap">
  <p style="font-size:12px;color:#94a3b8;margin:0">
    Klik <strong>Edit</strong> untuk mengubah tarif. Perubahan berlaku langsung untuk transaksi berikutnya.
  </p>
  <?php if ($canAddMore): ?>
  <a href="?page=tarif&action=create"
     style="display:inline-flex;align-items:center;gap:6px;background:#4f46e5;color:#fff;
            font-size:13px;font-weight:600;padding:8px 16px;border-radius:8px;text-decoration:none;
            transition:background .15s;white-space:nowrap"
     onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
    <svg style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Tambah Tarif
  </a>
  <?php else: ?>
  <span style="display:inline-flex;align-items:center;gap:6px;background:#e2e8f0;color:#94a3b8;
               font-size:13px;font-weight:600;padding:8px 16px;border-radius:8px;cursor:not-allowed;white-space:nowrap"
        title="Semua jenis kendaraan sudah memiliki tarif">
    <svg style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Tambah Tarif
  </span>
  <?php endif; ?>
</div>

<!-- Flash message -->
<?php $flash = getFlash(); if ($flash): ?>
<div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:13px;font-weight:500;
            background:<?= $flash['type'] === 'success' ? '#f0fdf4' : '#fef2f2' ?>;
            border:1px solid <?= $flash['type'] === 'success' ? '#bbf7d0' : '#fecaca' ?>;
            color:<?= $flash['type'] === 'success' ? '#166534' : '#991b1b' ?>;
            display:flex;align-items:center;gap:8px">
  <svg style="width:16px;height:16px;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <?php if ($flash['type'] === 'success'): ?>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    <?php else: ?>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    <?php endif; ?>
  </svg>
  <?= e($flash['message']) ?>
</div>
<?php endif; ?>

<!-- Kartu Tarif -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:24px">
  <?php foreach ($tarifs as $t):
    $jenis = $t['jenis_kendaraan'];
    $c     = $jenisColor[$jenis] ?? $jenisColor['lainnya'];
    $icon  = $jenisIcon[$jenis]  ?? $jenisIcon['lainnya'];
  ?>
  <div style="background:#fff;border:1.5px solid <?= $c['border'] ?>;border-radius:16px;padding:20px;display:flex;flex-direction:column;gap:12px">

    <!-- Header kartu -->
    <div style="display:flex;align-items:center;justify-content:space-between">
      <div style="width:52px;height:52px;background:<?= $c['bg'] ?>;border-radius:12px;
                  display:flex;align-items:center;justify-content:center;color:<?= $c['icon'] ?>">
        <?= $icon ?>
      </div>
      <div style="display:flex;gap:6px;align-items:center">
        <a href="?page=tarif&action=edit&id=<?= $t['id_tarif'] ?>"
           style="font-size:11px;font-weight:600;color:#667eea;border:1px solid #c7d2fe;
                  background:#eef2ff;padding:5px 12px;border-radius:8px;text-decoration:none;
                  transition:all .15s"
           onmouseover="this.style.background='#e0e7ff'" onmouseout="this.style.background='#eef2ff'">
          Edit
        </a>
        <a href="?page=tarif&action=delete&id=<?= $t['id_tarif'] ?>"
           data-confirm="Tarif &quot;<?= ucfirst(e($t['jenis_kendaraan'])) ?>&quot; akan dihapus permanen. Aksi ini tidak dapat dibatalkan."
           data-confirm-title="Hapus Tarif"
           data-confirm-type="danger"
           style="font-size:11px;font-weight:600;color:#ef4444;border:1px solid #fecaca;
                  background:#fff5f5;padding:5px 10px;border-radius:8px;text-decoration:none;
                  transition:all .15s;display:inline-flex;align-items:center;gap:3px"
           onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fff5f5'">
          <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
          </svg>
          Hapus
        </a>
      </div>
    </div>

    <!-- Label jenis -->
    <div>
      <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                   background:<?= $c['badge'] ?>;color:<?= $c['badgeText'] ?>;
                   padding:3px 10px;border-radius:20px">
        <?= ucfirst($jenis) ?>
      </span>
    </div>

    <!-- Tarif per jam -->
    <div>
      <p style="font-size:11px;color:#94a3b8;margin:0 0 2px">Tarif per jam</p>
      <p style="font-size:22px;font-weight:800;color:#0f172a;margin:0;line-height:1">
        <?= formatRupiah($t['tarif_per_jam']) ?>
      </p>
    </div>

    <!-- Biaya masuk -->
    <?php if ($t['tarif_masuk'] > 0): ?>
    <div style="background:#f8fafc;border-radius:8px;padding:10px 12px;display:flex;justify-content:space-between;align-items:center">
      <span style="font-size:11px;color:#64748b">Biaya masuk (flat)</span>
      <span style="font-size:13px;font-weight:700;color:#334155"><?= formatRupiah($t['tarif_masuk']) ?></span>
    </div>
    <?php endif; ?>

    <!-- Keterangan -->
    <?php if ($t['keterangan']): ?>
    <p style="font-size:11px;color:#94a3b8;margin:0;font-style:italic"><?= e($t['keterangan']) ?></p>
    <?php endif; ?>

  </div>
  <?php endforeach; ?>
</div>

<!-- Tabel detail semua tarif -->
<div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden">
  <div style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:11px 20px">
    <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#64748b;margin:0">
      Detail Semua Tarif
    </p>
  </div>
  <table style="width:100%;border-collapse:collapse;font-size:13px">
    <thead>
      <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
        <th style="text-align:left;padding:10px 20px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em">Jenis</th>
        <th style="text-align:right;padding:10px 12px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em">Tarif/Jam</th>
        <th style="text-align:right;padding:10px 12px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em">Biaya Masuk</th>
        <th style="text-align:left;padding:10px 12px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em">Keterangan</th>
        <th style="text-align:center;padding:10px 20px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($tarifs as $t): ?>
      <tr style="border-bottom:1px solid #f1f5f9;transition:background .1s" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
        <td style="padding:12px 20px"><?= badgeJenis($t['jenis_kendaraan']) ?></td>
        <td style="padding:12px;text-align:right;font-weight:700;color:#0f172a"><?= formatRupiah($t['tarif_per_jam']) ?></td>
        <td style="padding:12px;text-align:right;color:#475569"><?= formatRupiah($t['tarif_masuk']) ?></td>
        <td style="padding:12px;color:#94a3b8;font-size:12px"><?= e($t['keterangan'] ?: '—') ?></td>
        <td style="padding:12px 20px;text-align:center">
          <div style="display:inline-flex;gap:10px;align-items:center">
            <a href="?page=tarif&action=edit&id=<?= $t['id_tarif'] ?>"
               style="font-size:12px;color:#667eea;font-weight:600;text-decoration:none"
               onmouseover="this.style.color='#4338ca'" onmouseout="this.style.color='#667eea'">
              Edit
            </a>
            <span style="color:#e2e8f0">|</span>
            <a href="?page=tarif&action=delete&id=<?= $t['id_tarif'] ?>"
               data-confirm="Tarif &quot;<?= ucfirst(e($t['jenis_kendaraan'])) ?>&quot; akan dihapus permanen. Aksi ini tidak dapat dibatalkan."
               data-confirm-title="Hapus Tarif"
               data-confirm-type="danger"
               style="font-size:12px;color:#ef4444;font-weight:600;text-decoration:none"
               onmouseover="this.style.color='#dc2626'" onmouseout="this.style.color='#ef4444'">
              Hapus
            </a>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>