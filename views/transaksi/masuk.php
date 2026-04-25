<?php
/**
 * views/transaksi/masuk.php
 * Variabel: $areas, $tarifs, $platPrefill
 */
?>

<div class="max-w-2xl mx-auto">

  <!-- Back -->
  <a href="?page=transaksi&action=index"
     class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 mb-5 transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    Kembali ke Daftar Transaksi
  </a>

  <div class="bg-white rounded-xl border border-slate-200 overflow-hidden"
       x-data="formMasuk()">

    <!-- Header Card -->
    <div class="bg-indigo-600 px-6 py-4">
      <h2 class="text-white font-semibold text-base flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Kendaraan Masuk Parkir
      </h2>
      <p class="text-indigo-100 text-xs mt-0.5"><?= date('l, d F Y — H:i') ?> WIB</p>
    </div>

    <form method="POST" action="?page=transaksi&action=masuk" class="p-6 space-y-5">

      <!-- ── PLAT NOMOR + CARI ─────────────────────────────── -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">
          Plat Nomor <span class="text-red-500">*</span>
        </label>
        <div class="flex gap-2">
          <input type="text" name="plat_nomor" id="platInput"
                 value="<?= e($platPrefill) ?>"
                 placeholder="Contoh: AE 1234 AB"
                 maxlength="15"
                 @input.debounce.400ms="cariKendaraan($event.target.value)"
                 @keyup.enter.prevent="cariKendaraan($el.value)"
                 x-model="plat"
                 class="flex-1 border border-slate-200 rounded-lg px-4 py-2.5 text-sm uppercase
                        font-mono tracking-wider focus:outline-none focus:ring-2 focus:ring-indigo-500"
                 style="text-transform:uppercase"
                 required>
          <button type="button" @click="cariKendaraan(plat)"
                  class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium rounded-lg transition-colors">
            Cari
          </button>
        </div>

        <!-- Status pencarian -->
        <div x-show="status === 'loading'" class="mt-2 text-xs text-slate-400 flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          Mencari kendaraan…
        </div>

        <!-- Kendaraan ditemukan -->
        <div x-show="status === 'found'" x-cloak
             class="mt-2 p-3 bg-green-50 border border-green-200 rounded-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-green-800">Kendaraan ditemukan</p>
              <p class="text-xs text-green-700 mt-0.5">
                <span x-text="kendaraan.pemilik"></span> ·
                <span x-text="kendaraan.warna"></span> ·
                <span x-text="kendaraan.jenis_kendaraan" class="capitalize"></span>
              </p>
            </div>
            <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <!-- Peringatan: sedang parkir -->
          <div x-show="kendaraan.sedang_parkir" x-cloak
               class="mt-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-1.5 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Kendaraan ini masih tercatat sedang parkir (tiket: <span x-text="kendaraan.kode_aktif" class="font-mono font-semibold"></span>)
          </div>
        </div>

        <!-- Kendaraan tidak ditemukan -->
        <div x-show="status === 'notfound'" x-cloak
             class="mt-2 p-3 bg-amber-50 border border-amber-200 rounded-lg">
          <p class="text-xs font-semibold text-amber-800">Kendaraan belum terdaftar</p>
          <p class="text-xs text-amber-700 mt-0.5">Isi data kendaraan di bawah untuk mendaftarkan otomatis.</p>
        </div>
      </div>

      <!-- ── DATA KENDARAAN (muncul jika belum terdaftar) ───── -->
      <div x-show="status === 'notfound'" x-cloak
           class="border border-slate-200 rounded-lg p-4 space-y-4 bg-slate-50">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Data Kendaraan Baru</p>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Jenis Kendaraan</label>
            <select name="jenis_kendaraan"
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
              <option value="motor">Motor</option>
              <option value="mobil">Mobil</option>
              <option value="lainnya">Lainnya</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Warna</label>
            <input type="text" name="warna" placeholder="Hitam, Putih, dll."
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Nama Pemilik</label>
            <input type="text" name="pemilik" placeholder="Nama pemilik kendaraan"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">No. HP (opsional)</label>
            <input type="text" name="no_hp" placeholder="08xxxxxxxxxx"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
          </div>
        </div>
      </div>

      <!-- ── AREA PARKIR ───────────────────────────────────── -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">
          Area Parkir <span class="text-red-500">*</span>
        </label>
        <?php if (empty($areas)): ?>
          <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            Semua area parkir penuh. Tidak bisa memproses kendaraan masuk.
          </div>
        <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-<?= min(count($areas), 3) ?> gap-3">
          <?php foreach ($areas as $i => $area):
            $pct  = persenTerisi($area['kapasitas'], $area['terisi']);
            $warn = isAreaHampirPenuh($area['kapasitas'], $area['terisi']);
          ?>
          <label class="relative cursor-pointer">
            <input type="radio" name="id_area" value="<?= $area['id_area'] ?>"
                   <?= $i === 0 ? 'checked' : '' ?> required
                   class="peer sr-only">
            <div class="border-2 border-slate-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50
                        rounded-xl p-3 transition-all duration-150">
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-slate-700"><?= e($area['nama_area']) ?></span>
                <div class="w-4 h-4 rounded-full border-2 border-slate-300 peer-checked:border-indigo-500
                            flex items-center justify-center">
                </div>
              </div>
              <div class="w-full bg-slate-100 rounded-full h-1.5 mb-1.5">
                <div class="h-1.5 rounded-full <?= $warn ? 'bg-amber-400' : 'bg-indigo-500' ?>"
                     style="width:<?= $pct ?>%"></div>
              </div>
              <p class="text-xs text-slate-500">
                <?= $area['tersedia'] ?? ($area['kapasitas'] - $area['terisi']) ?> slot tersedia
                <span class="<?= $warn ? 'text-amber-600 font-medium' : '' ?>">
                  (<?= $pct ?>% terisi)
                </span>
              </p>
            </div>
          </label>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- ── INFO TARIF ────────────────────────────────────── -->
      <div class="border border-slate-200 rounded-lg divide-y divide-slate-100 overflow-hidden">
        <div class="px-4 py-2.5 bg-slate-50">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Info Tarif</p>
        </div>
        <?php foreach ($tarifs as $tarif): ?>
        <div class="px-4 py-2.5 flex justify-between items-center">
          <span class="text-sm capitalize text-slate-600"><?= e($tarif['jenis_kendaraan']) ?></span>
          <div class="text-right">
            <span class="text-sm font-semibold text-slate-700"><?= formatRupiah($tarif['tarif_per_jam']) ?>/jam</span>
            <?php if ($tarif['tarif_masuk'] > 0): ?>
            <span class="text-xs text-slate-400 block">+ <?= formatRupiah($tarif['tarif_masuk']) ?> biaya masuk</span>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- ── CATATAN ───────────────────────────────────────── -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Catatan (opsional)</label>
        <textarea name="catatan" rows="2" placeholder="Catatan tambahan jika perlu…"
                  class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm resize-none
                         focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
      </div>

      <!-- ── SUBMIT ────────────────────────────────────────── -->
      <div class="flex gap-3 pt-2">
        <button type="submit"
                :disabled="kendaraan.sedang_parkir"
                class="flex-1 bg-indigo-600 hover:bg-indigo-500 disabled:bg-slate-300 disabled:cursor-not-allowed
                       text-white font-semibold py-3 rounded-lg text-sm transition-colors
                       flex items-center justify-center gap-2">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Catat Kendaraan Masuk
        </button>
        <a href="?page=transaksi&action=index"
           class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium rounded-lg text-sm transition-colors">
          Batal
        </a>
      </div>

    </form>
  </div>
</div>

<script>
function formMasuk() {
  return {
    plat: '<?= e($platPrefill) ?>',
    status: '',   // '' | 'loading' | 'found' | 'notfound'
    kendaraan: {
      pemilik: '', warna: '', jenis_kendaraan: '',
      sedang_parkir: false, kode_aktif: ''
    },

    async cariKendaraan(plat) {
      plat = plat.trim().toUpperCase();
      if (plat.length < 3) { this.status = ''; return; }

      this.status = 'loading';

      try {
        const res  = await fetch(`?page=transaksi&action=cari&plat=${encodeURIComponent(plat)}`);
        const data = await res.json();

        if (data.found) {
          this.kendaraan = data;
          this.status    = 'found';
        } else {
          this.kendaraan = { sedang_parkir: false };
          this.status    = 'notfound';
        }
      } catch {
        this.status = 'notfound';
      }
    },

    init() {
      if (this.plat.length >= 3) this.cariKendaraan(this.plat);
    }
  }
}
</script>