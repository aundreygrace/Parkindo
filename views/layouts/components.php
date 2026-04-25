<?php
/**
 * views/layouts/components.php
 * Komponen UI reusable yang dipakai oleh semua view
 * Aplikasi Parkir — UKK RPL 2025/2026
 *
 * Fungsi yang tersedia:
 *  — renderEmptyState($title, $subtitle)
 *  — renderPagination($halaman, $totalHal, $total, $baseUrl)
 *  — badgeJenis($jenis)
 *  — badgeStatus($status)
 *  — badgeRole($role)
 *  — renderFlash()
 */

// Pastikan fungsi helpers tersedia
if (!function_exists('e')) {
    require_once __DIR__ . '/../../helpers/functions.php';
}

// ══════════════════════════════════════════════════════════════
//  EMPTY STATE
// ══════════════════════════════════════════════════════════════

/**
 * Tampilkan pesan kosong ketika tidak ada data
 *
 * @param string $title   Judul utama
 * @param string $subtitle Keterangan tambahan
 */
function renderEmptyState(string $title = 'Tidak ada data', string $subtitle = ''): void
{
    echo '<div class="flex flex-col items-center justify-center py-16 text-center">';
    echo '<div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center mb-4">';
    echo '<svg class="w-7 h-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
    echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>';
    echo '</svg></div>';
    echo '<p class="text-sm font-semibold text-slate-600">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</p>';
    if ($subtitle) {
        echo '<p class="text-xs text-slate-400 mt-1">' . htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8') . '</p>';
    }
    echo '</div>';
}

// ══════════════════════════════════════════════════════════════
//  PAGINATION
// ══════════════════════════════════════════════════════════════

/**
 * Render navigasi pagination
 *
 * @param int    $halaman   Halaman saat ini
 * @param int    $totalHal  Total halaman
 * @param int    $total     Total record
 * @param string $baseUrl   URL dasar tanpa parameter hal (contoh: ?page=user&action=index)
 */
function renderPagination(int $halaman, int $totalHal, int $total, string $baseUrl): void
{
    if ($totalHal <= 1) return;
    ?>
    <div class="flex items-center justify-between px-5 py-3 border-t border-slate-100 bg-slate-50">
        <p class="text-xs text-slate-500">
            Total <strong class="text-slate-700"><?= number_format($total) ?></strong> data
            &mdash; Halaman <strong class="text-slate-700"><?= $halaman ?></strong> dari
            <strong class="text-slate-700"><?= $totalHal ?></strong>
        </p>
        <div class="flex items-center gap-1">
            <!-- Pertama -->
            <?php if ($halaman > 1): ?>
            <a href="<?= $baseUrl ?>&hal=1"
               class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 transition-colors">
               «
            </a>
            <!-- Sebelumnya -->
            <a href="<?= $baseUrl ?>&hal=<?= $halaman - 1 ?>"
               class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 transition-colors">
               ‹
            </a>
            <?php else: ?>
            <span class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-100 text-slate-300 cursor-default">«</span>
            <span class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-100 text-slate-300 cursor-default">‹</span>
            <?php endif; ?>

            <!-- Nomor halaman (tampilkan maks 5 di sekitar halaman aktif) -->
            <?php
            $start = max(1, $halaman - 2);
            $end   = min($totalHal, $halaman + 2);
            for ($i = $start; $i <= $end; $i++):
            ?>
            <a href="<?= $baseUrl ?>&hal=<?= $i ?>"
               class="px-2.5 py-1.5 text-xs rounded-lg border transition-colors
                      <?= $i === $halaman
                          ? 'bg-indigo-600 border-indigo-600 text-white font-semibold'
                          : 'border-slate-200 text-slate-600 hover:bg-slate-100' ?>">
               <?= $i ?>
            </a>
            <?php endfor; ?>

            <!-- Selanjutnya -->
            <?php if ($halaman < $totalHal): ?>
            <a href="<?= $baseUrl ?>&hal=<?= $halaman + 1 ?>"
               class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 transition-colors">
               ›
            </a>
            <!-- Terakhir -->
            <a href="<?= $baseUrl ?>&hal=<?= $totalHal ?>"
               class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 transition-colors">
               »
            </a>
            <?php else: ?>
            <span class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-100 text-slate-300 cursor-default">›</span>
            <span class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-100 text-slate-300 cursor-default">»</span>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

// ══════════════════════════════════════════════════════════════
//  BADGE HELPERS
// ══════════════════════════════════════════════════════════════

/**
 * Badge jenis kendaraan
 *
 * @param string $jenis motor | mobil | lainnya
 * @return string HTML badge
 */
function badgeJenis(string $jenis): string
{
    $map = [
        'motor'   => ['label' => 'Motor',   'class' => 'bg-indigo-50 text-indigo-700'],
        'mobil'   => ['label' => 'Mobil',   'class' => 'bg-violet-50 text-violet-700'],
        'lainnya' => ['label' => 'Lainnya', 'class' => 'bg-amber-50 text-amber-700'],
    ];
    $cfg = $map[$jenis] ?? ['label' => ucfirst($jenis), 'class' => 'bg-slate-100 text-slate-600'];
    return '<span class="text-xs px-2 py-0.5 rounded font-medium ' . $cfg['class'] . '">'
         . htmlspecialchars($cfg['label'], ENT_QUOTES, 'UTF-8') . '</span>';
}

/**
 * Badge status transaksi
 *
 * @param string $status masuk | keluar
 * @return string HTML badge
 */
function badgeStatus(string $status): string
{
    $map = [
        'masuk'  => ['label' => 'Masuk',  'class' => 'bg-indigo-50 text-indigo-700'],
        'keluar' => ['label' => 'Keluar', 'class' => 'bg-green-50 text-green-700'],
    ];
    $cfg = $map[$status] ?? ['label' => ucfirst($status), 'class' => 'bg-slate-100 text-slate-600'];
    return '<span class="text-xs px-2 py-0.5 rounded font-medium ' . $cfg['class'] . '">'
         . htmlspecialchars($cfg['label'], ENT_QUOTES, 'UTF-8') . '</span>';
}

/**
 * Badge role user
 *
 * @param string $role admin | petugas | owner
 * @return string HTML badge
 */
function badgeRole(string $role): string
{
    $map = [
        'admin'   => ['label' => 'Admin',   'class' => 'bg-purple-50 text-purple-700'],
        'petugas' => ['label' => 'Petugas', 'class' => 'bg-indigo-50 text-indigo-700'],
        'owner'   => ['label' => 'Owner',   'class' => 'bg-amber-50 text-amber-700'],
    ];
    $cfg = $map[$role] ?? ['label' => ucfirst($role), 'class' => 'bg-slate-100 text-slate-600'];
    return '<span class="text-xs px-2 py-0.5 rounded font-medium ' . $cfg['class'] . '">'
         . htmlspecialchars($cfg['label'], ENT_QUOTES, 'UTF-8') . '</span>';
}

// ══════════════════════════════════════════════════════════════
//  FLASH MESSAGE RENDERER
// ══════════════════════════════════════════════════════════════

/**
 * Tampilkan flash message jika ada
 * Dipanggil di dalam layout app.php
 */
function renderFlash(): void
{
    $flash = getFlash();
    if (!$flash) return;

    $colorMap = [
        'success' => ['bg' => 'bg-green-50',  'border' => 'border-green-200', 'text' => 'text-green-800', 'icon_color' => 'text-green-500'],
        'error'   => ['bg' => 'bg-red-50',    'border' => 'border-red-200',   'text' => 'text-red-800',   'icon_color' => 'text-red-500'],
        'warning' => ['bg' => 'bg-amber-50',  'border' => 'border-amber-200', 'text' => 'text-amber-800', 'icon_color' => 'text-amber-500'],
        'info'    => ['bg' => 'bg-indigo-50',   'border' => 'border-indigo-200',  'text' => 'text-indigo-800',  'icon_color' => 'text-indigo-500'],
    ];

    $type = $flash['type'] ?? 'info';
    $c    = $colorMap[$type] ?? $colorMap['info'];

    // Icon path per tipe
    $icons = [
        'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',
        'error'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>',
        'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>',
        'info'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z"/>',
    ];
    $iconPath = $icons[$type] ?? $icons['info'];
    ?>
    <div class="mb-5 flex items-start gap-3 px-4 py-3 rounded-xl border <?= $c['bg'] ?> <?= $c['border'] ?>">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5 <?= $c['icon_color'] ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <?= $iconPath ?>
        </svg>
        <p class="text-sm font-medium <?= $c['text'] ?>"><?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <?php
}