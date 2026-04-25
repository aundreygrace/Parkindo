<?php
/**
 * views/area/index.php
 * Variabel: $areas
 */
require_once VIEW_PATH . '/layouts/components.php';
?>

<div class="flex items-center justify-between mb-5">
  <p class="text-xs text-slate-400"><?= count($areas) ?> area parkir terdaftar</p>
  <a href="?page=area&action=create"
     class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Tambah Area
  </a>
</div>

<!-- Kartu Area -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
  <?php foreach ($areas as $a):
    $pct  = persenTerisi((int)$a['kapasitas'], (int)$a['terisi']);
    $warn = isAreaHampirPenuh((int)$a['kapasitas'], (int)$a['terisi']);
    $bar  = $pct >= 100 ? 'bg-red-500' : ($warn ? 'bg-amber-400' : 'bg-indigo-500');
    $ring = $pct >= 100 ? 'border-red-200' : ($warn ? 'border-amber-200' : 'border-slate-200');
  ?>
  <div class="bg-white border <?= $ring ?> rounded-2xl p-5">
    <div class="flex items-start justify-between mb-4">
      <div>
        <h3 class="font-semibold text-slate-800"><?= e($a['nama_area']) ?></h3>
        <?php if ($a['keterangan']): ?>
        <p class="text-xs text-slate-400 mt-0.5"><?= e($a['keterangan']) ?></p>
        <?php endif; ?>
      </div>
      <span class="text-lg font-black <?= $pct >= 100 ? 'text-red-600' : ($warn ? 'text-amber-600' : 'text-indigo-600') ?>">
        <?= $pct ?>%
      </span>
    </div>

    <!-- Progress bar -->
    <div class="w-full bg-slate-100 rounded-full h-3 mb-3">
      <div class="h-3 rounded-full transition-all <?= $bar ?>"
           style="width: <?= $pct ?>%"></div>
    </div>

    <!-- Slot info -->
    <div class="flex justify-between text-xs text-slate-500 mb-4">
      <span><strong class="text-slate-700"><?= $a['terisi'] ?></strong> terisi</span>
      <span><strong class="text-slate-700"><?= $a['kapasitas'] - $a['terisi'] ?></strong> tersedia</span>
      <span>Kapasitas: <strong class="text-slate-700"><?= $a['kapasitas'] ?></strong></span>
    </div>

    <!-- Aksi -->
    <div class="flex gap-2 border-t border-slate-100 pt-3">
      <a href="?page=area&action=edit&id=<?= $a['id_area'] ?>"
         class="flex-1 text-center text-xs font-medium text-indigo-600 hover:text-indigo-800 py-1.5 rounded-lg border border-indigo-100 hover:bg-indigo-50 transition-colors">
        Edit
      </a>
      <a href="?page=area&action=delete&id=<?= $a['id_area'] ?>"
         data-confirm="Area &quot;<?= e($a['nama_area']) ?>&quot; akan dihapus permanen."
         data-confirm-title="Hapus Area Parkir"
         data-confirm-type="danger"
         class="flex-1 text-center text-xs font-medium text-red-500 hover:text-red-700 py-1.5 rounded-lg border border-red-100 hover:bg-red-50 transition-colors">
        Hapus
      </a>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Tabel ringkasan -->
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
  <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Ringkasan Kapasitas</p>
  </div>
  <table class="w-full text-sm">
    <thead>
      <tr class="text-xs text-slate-500 uppercase tracking-wide border-b border-slate-100">
        <th class="text-left px-5 py-3 font-medium">Area</th>
        <th class="text-right px-3 py-3 font-medium">Kapasitas</th>
        <th class="text-right px-3 py-3 font-medium">Terisi</th>
        <th class="text-right px-3 py-3 font-medium">Tersedia</th>
        <th class="text-left px-5 py-3 font-medium">Utilisasi</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      <?php
      $totalKap = 0; $totalTerisi = 0;
      foreach ($areas as $a):
        $pct = persenTerisi((int)$a['kapasitas'], (int)$a['terisi']);
        $bar = $pct >= 100 ? 'bg-red-500' : (isAreaHampirPenuh((int)$a['kapasitas'],(int)$a['terisi']) ? 'bg-amber-400' : 'bg-indigo-500');
        $totalKap    += $a['kapasitas'];
        $totalTerisi += $a['terisi'];
      ?>
      <tr class="hover:bg-slate-50">
        <td class="px-5 py-3 font-medium text-slate-700"><?= e($a['nama_area']) ?></td>
        <td class="px-3 py-3 text-right text-slate-600"><?= $a['kapasitas'] ?></td>
        <td class="px-3 py-3 text-right font-semibold text-slate-800"><?= $a['terisi'] ?></td>
        <td class="px-3 py-3 text-right text-green-600 font-medium"><?= $a['kapasitas'] - $a['terisi'] ?></td>
        <td class="px-5 py-3">
          <div class="flex items-center gap-2">
            <div class="flex-1 bg-slate-100 rounded-full h-1.5">
              <div class="h-1.5 rounded-full <?= $bar ?>" style="width:<?= $pct ?>%"></div>
            </div>
            <span class="text-xs font-medium text-slate-600 w-8 text-right"><?= $pct ?>%</span>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr class="bg-slate-50 font-semibold border-t-2 border-slate-200 text-sm">
        <td class="px-5 py-3 text-slate-700">Total</td>
        <td class="px-3 py-3 text-right text-slate-800"><?= $totalKap ?></td>
        <td class="px-3 py-3 text-right text-slate-800"><?= $totalTerisi ?></td>
        <td class="px-3 py-3 text-right text-green-600"><?= $totalKap - $totalTerisi ?></td>
        <td class="px-5 py-3 text-xs text-slate-500">
          <?= $totalKap > 0 ? round($totalTerisi/$totalKap*100) : 0 ?>% utilisasi keseluruhan
        </td>
      </tr>
    </tfoot>
  </table>
</div>