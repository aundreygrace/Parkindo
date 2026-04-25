<?php
/**
 * views/transaksi/keluar.php
 * FIX: Jika $transaksiAktif sudah ada dari controller (via ?id=),
 *      langsung render — tidak perlu AJAX. Kalkulasi PHP sekaligus JS.
 */

$biayaAwal  = 0;
$durasiAwal = 0;
if ($transaksiAktif) {
    $durasiAwal = hitungDurasi($transaksiAktif['waktu_masuk'], date('Y-m-d H:i:s'));
    $biayaAwal  = hitungBiaya(
        $durasiAwal,
        (float)$transaksiAktif['tarif_per_jam'],
        (float)$transaksiAktif['tarif_masuk']
    );
}
?>

<div class="max-w-2xl mx-auto">
  <a href="?page=transaksi&action=index"
     class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 mb-5 transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    Kembali ke Daftar Transaksi
  </a>

  <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">

    <div class="bg-slate-800 px-6 py-4">
      <h2 class="text-white font-semibold text-base flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/>
        </svg>
        Kendaraan Keluar Parkir
      </h2>
      <p class="text-slate-400 text-xs mt-0.5"><?= date('l, d F Y — H:i') ?> WIB</p>
    </div>

    <div class="p-6">

    <?php if (!$transaksiAktif): ?>
    <!-- ══ MODE CARI ══════════════════════════════════════════ -->
    <div x-data="cariKeluar()">

      <div x-show="!trx">
        <label class="block text-sm font-medium text-slate-700 mb-1.5">
          Plat Nomor Kendaraan <span class="text-red-500">*</span>
        </label>
        <div class="flex gap-2">
          <input type="text" x-ref="inp"
                 placeholder="Contoh: AE 1234 AB"
                 @keyup.enter="cari($refs.inp.value)"
                 class="flex-1 border border-slate-200 rounded-lg px-4 py-2.5 text-sm
                        font-mono uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-indigo-500"
                 style="text-transform:uppercase">
          <button type="button" @click="cari($refs.inp.value)"
                  :disabled="loading"
                  class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50
                         text-white text-sm font-semibold rounded-lg transition-colors">
            <span x-text="loading ? 'Mencari...' : 'Cari'">Cari</span>
          </button>
        </div>
        <p x-show="errMsg" x-text="errMsg" x-cloak class="mt-2 text-xs text-red-600 font-medium"></p>
      </div>

      <template x-if="trx">
        <div>
          <!-- Info kendaraan -->
          <div class="border border-slate-200 rounded-xl overflow-hidden mb-5">
            <div class="bg-slate-50 px-4 py-2.5 border-b border-slate-200">
              <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Data Kendaraan</p>
            </div>
            <div class="grid grid-cols-2 divide-x divide-y divide-slate-100">
              <div class="px-4 py-3"><p class="text-xs text-slate-400">Kode Tiket</p><p class="text-sm font-semibold font-mono text-slate-700" x-text="trx.kode_parkir"></p></div>
              <div class="px-4 py-3"><p class="text-xs text-slate-400">Plat Nomor</p><p class="text-sm font-bold font-mono tracking-widest text-slate-800" x-text="trx.plat_nomor"></p></div>
              <div class="px-4 py-3"><p class="text-xs text-slate-400">Jenis</p><p class="text-sm font-semibold text-slate-700 capitalize" x-text="trx.jenis_kendaraan"></p></div>
              <div class="px-4 py-3"><p class="text-xs text-slate-400">Pemilik</p><p class="text-sm font-semibold text-slate-700" x-text="trx.pemilik"></p></div>
              <div class="px-4 py-3"><p class="text-xs text-slate-400">Area</p><p class="text-sm font-semibold text-slate-700" x-text="trx.nama_area"></p></div>
              <div class="px-4 py-3"><p class="text-xs text-slate-400">Waktu Masuk</p><p class="text-sm font-semibold font-mono text-slate-700" x-text="fmtWaktu(trx.waktu_masuk)"></p></div>
            </div>
          </div>

          <!-- Kalkulasi -->
          <div class="bg-slate-800 rounded-xl p-5 text-white space-y-3 mb-5">
            <div class="flex justify-between text-sm"><span class="text-slate-400">Waktu Keluar</span><span class="font-mono text-indigo-300" x-text="waktuKeluar"></span></div>
            <div class="flex justify-between text-sm"><span class="text-slate-400">Durasi</span><span class="font-semibold text-amber-300" x-text="durasiLabel"></span></div>
            <div class="border-t border-slate-600 pt-3 flex justify-between items-center">
              <span class="text-slate-300 font-medium">Total Biaya</span>
              <span class="text-2xl font-black text-green-400" x-text="fmtRp(biayaTotal)"></span>
            </div>
          </div>

          <!-- Form bayar -->
          <form method="POST" action="?page=transaksi&action=keluar" class="space-y-4">
            <input type="hidden" name="id_parkir" :value="trx.id_parkir">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Uang Dibayar <span class="text-red-500">*</span></label>
              <input type="number" name="bayar" id="bayarInputAjax"
                     placeholder="0" step="500" min="0"
                     @input="hitungKembalian($event.target.value)"
                     class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm
                            font-mono text-right focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
              <div class="flex gap-2 mt-2 flex-wrap">
                <?php foreach ([5000,10000,20000,50000,100000] as $n): ?>
                <button type="button" @click="setBayar(<?= $n ?>)"
                        class="text-xs px-3 py-1.5 border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-600 font-medium">
                  <?= formatRupiah($n) ?>
                </button>
                <?php endforeach; ?>
                <button type="button" @click="setBayar(biayaTotal)"
                        class="text-xs px-3 py-1.5 border border-indigo-200 bg-indigo-50 rounded-lg text-indigo-700 font-semibold">
                  Pas
                </button>
              </div>
            </div>
            <div class="flex justify-between items-center p-4 rounded-xl border"
                 :class="kembalian >= 0 ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
              <span class="text-sm font-medium" :class="kembalian >= 0 ? 'text-green-800' : 'text-red-700'">Kembalian</span>
              <span class="text-xl font-bold" :class="kembalian >= 0 ? 'text-green-700' : 'text-red-600'"
                    x-text="kembalian >= 0 ? fmtRp(kembalian) : 'Kurang ' + fmtRp(-kembalian)"></span>
            </div>
            <button type="submit" :disabled="kembalian < 0"
                    class="w-full bg-slate-800 hover:bg-slate-700 disabled:bg-slate-300 disabled:cursor-not-allowed
                           text-white font-semibold py-3 rounded-lg text-sm transition-colors">
              Proses Keluar &amp; Cetak Struk
            </button>
          </form>

          <button type="button" @click="trx=null;errMsg=''"
                  class="w-full text-xs text-slate-400 hover:text-slate-600 mt-3 py-1 transition-colors">
            Cari kendaraan lain
          </button>
        </div>
      </template>
    </div>

    <?php else: ?>
    <!-- ══ MODE LANGSUNG — data sudah ada dari URL ?id= ═══════ -->
    <div x-data="kalkulasi('<?= addslashes($transaksiAktif['waktu_masuk']) ?>',<?= (float)$transaksiAktif['tarif_per_jam'] ?>,<?= (float)$transaksiAktif['tarif_masuk'] ?>)"
         x-init="mulai()">

      <!-- Info kendaraan — render PHP, tidak butuh JS -->
      <div class="border border-slate-200 rounded-xl overflow-hidden mb-5">
        <div class="bg-slate-50 px-4 py-2.5 border-b border-slate-200">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Data Kendaraan</p>
        </div>
        <div class="grid grid-cols-2 divide-x divide-y divide-slate-100">
          <div class="px-4 py-3"><p class="text-xs text-slate-400">Kode Tiket</p><p class="text-sm font-semibold font-mono text-slate-700"><?= e($transaksiAktif['kode_parkir']) ?></p></div>
          <div class="px-4 py-3"><p class="text-xs text-slate-400">Plat Nomor</p><p class="text-sm font-bold font-mono tracking-widest text-slate-800"><?= e($transaksiAktif['plat_nomor']) ?></p></div>
          <div class="px-4 py-3"><p class="text-xs text-slate-400">Jenis</p><p class="text-sm font-semibold text-slate-700 capitalize"><?= e($transaksiAktif['jenis_kendaraan']) ?></p></div>
          <div class="px-4 py-3"><p class="text-xs text-slate-400">Pemilik</p><p class="text-sm font-semibold text-slate-700"><?= e($transaksiAktif['pemilik']) ?></p></div>
          <div class="px-4 py-3"><p class="text-xs text-slate-400">Area</p><p class="text-sm font-semibold text-slate-700"><?= e($transaksiAktif['nama_area']) ?></p></div>
          <div class="px-4 py-3"><p class="text-xs text-slate-400">Petugas</p><p class="text-sm font-semibold text-slate-700"><?= e($transaksiAktif['nama_petugas']) ?></p></div>
          <div class="px-4 py-3 col-span-2"><p class="text-xs text-slate-400">Waktu Masuk</p><p class="text-sm font-semibold font-mono text-slate-700"><?= formatTanggal($transaksiAktif['waktu_masuk']) ?></p></div>
        </div>
      </div>

      <!-- Kalkulasi real-time -->
      <div class="bg-slate-800 rounded-xl p-5 text-white space-y-3 mb-5">
        <div class="flex justify-between text-sm">
          <span class="text-slate-400">Waktu Keluar (sekarang)</span>
          <span class="font-mono text-indigo-300" x-text="waktuKeluar"><?= date('d/m/Y H:i:s') ?></span>
        </div>
        <div class="flex justify-between text-sm">
          <span class="text-slate-400">Durasi</span>
          <span class="font-semibold text-amber-300" x-text="durasiLabel">
            <?= formatDurasi($durasiAwal) ?> (ditagih <?= $durasiAwal ?> jam)
          </span>
        </div>
        <div class="flex justify-between text-sm">
          <span class="text-slate-400">Tarif</span>
          <span class="text-slate-300 text-xs">
            <?= formatRupiah($transaksiAktif['tarif_masuk']) ?> masuk +
            <?= formatRupiah($transaksiAktif['tarif_per_jam']) ?>/jam
          </span>
        </div>
        <div class="border-t border-slate-600 pt-3 flex justify-between items-center">
          <span class="text-slate-300 font-medium">Total Biaya</span>
          <span class="text-2xl font-black text-green-400" x-text="fmtRp(biayaTotal)">
            <?= formatRupiah($biayaAwal) ?>
          </span>
        </div>
      </div>

      <!-- Form bayar -->
      <form method="POST" action="?page=transaksi&action=keluar" class="space-y-4">
        <input type="hidden" name="id_parkir" value="<?= $transaksiAktif['id_parkir'] ?>">

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">
            Uang Dibayar <span class="text-red-500">*</span>
          </label>
          <input type="number" name="bayar" id="bayarInput"
                 placeholder="0" step="500" min="<?= $biayaAwal ?>"
                 @input="hitungKembalian($event.target.value)"
                 class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm
                        font-mono text-right focus:outline-none focus:ring-2 focus:ring-indigo-500"
                 required>

          <!-- Nominal cepat -->
          <div class="flex gap-2 mt-2 flex-wrap">
            <?php foreach ([5000,10000,20000,50000,100000] as $n): ?>
            <button type="button" @click="setBayar(<?= $n ?>)"
                    class="text-xs px-3 py-1.5 border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-600 font-medium">
              <?= formatRupiah($n) ?>
            </button>
            <?php endforeach; ?>
            <button type="button" @click="setBayar(biayaTotal)"
                    class="text-xs px-3 py-1.5 border border-indigo-200 bg-indigo-50 rounded-lg text-indigo-700 font-semibold">
              Pas (<span x-text="fmtRp(biayaTotal)"><?= formatRupiah($biayaAwal) ?></span>)
            </button>
          </div>
        </div>

        <!-- Kembalian -->
        <div class="flex justify-between items-center p-4 rounded-xl border"
             :class="kembalian >= 0 ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
          <span class="text-sm font-medium"
                :class="kembalian >= 0 ? 'text-green-800' : 'text-red-700'">Kembalian</span>
          <span class="text-xl font-bold"
                :class="kembalian >= 0 ? 'text-green-700' : 'text-red-600'"
                x-text="kembalian >= 0 ? fmtRp(kembalian) : 'Kurang ' + fmtRp(-kembalian)">
            —
          </span>
        </div>

        <button type="submit"
                :disabled="kembalian < 0"
                class="w-full bg-slate-800 hover:bg-slate-700 disabled:bg-slate-300 disabled:cursor-not-allowed
                       text-white font-semibold py-3 rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Proses Keluar &amp; Cetak Struk
        </button>
      </form>

    </div>
    <?php endif; ?>

    </div>
  </div>
</div>

<script>
// Mode langsung — data dari PHP
function kalkulasi(waktuMasukStr, tarifJam, tarifMasuk) {
  return {
    waktuKeluar: '', durasiLabel: '',
    biayaTotal: tarifMasuk + tarifJam, // fallback 1 jam
    kembalian: 0, timer: null,

    mulai() {
      this.update();
      this.timer = setInterval(() => this.update(), 15000);
    },

    update() {
      const now      = new Date();
      const masuk    = new Date(waktuMasukStr.replace(' ', 'T'));
      const jamReal  = (now - masuk) / 3600000;
      const jamTagih = Math.max(1, Math.ceil(jamReal));

      this.biayaTotal = tarifMasuk + (jamTagih * tarifJam);

      const j = Math.floor(jamReal);
      const m = Math.floor((jamReal - j) * 60);
      this.durasiLabel = (j > 0 ? j + ' jam ' : '') + m + ' menit (ditagih ' + jamTagih + ' jam)';
      this.waktuKeluar = now.toLocaleString('id-ID', {
        day:'2-digit', month:'2-digit', year:'numeric',
        hour:'2-digit', minute:'2-digit', second:'2-digit'
      });
    },

    hitungKembalian(val) {
      this.kembalian = (parseFloat(val) || 0) - this.biayaTotal;
    },

    setBayar(nominal) {
      document.getElementById('bayarInput').value = nominal;
      this.kembalian = nominal - this.biayaTotal;
    },

    fmtRp(n) {
      return 'Rp ' + Math.abs(Math.round(n)).toLocaleString('id-ID');
    }
  }
}

// Mode cari AJAX
function cariKeluar() {
  return {
    trx: null, errMsg: '', loading: false,
    waktuKeluar: '', durasiLabel: '', biayaTotal: 0, kembalian: 0, timer: null,

    async cari(val) {
      const q = val.trim().toUpperCase();
      if (!q) { this.errMsg = 'Masukkan plat nomor.'; return; }
      this.errMsg = ''; this.loading = true;
      try {
        const r = await fetch('?page=transaksi&action=cariAktif&plat=' + encodeURIComponent(q));
        const d = await r.json();
        if (d.found && d.transaksi) {
          this.trx = d.transaksi;
          this.initKalkulasi();
        } else {
          this.errMsg = 'Kendaraan tidak ditemukan atau tidak sedang parkir.';
        }
      } catch { this.errMsg = 'Gagal. Coba lagi.'; }
      this.loading = false;
    },

    initKalkulasi() {
      this.updateKalkulasi();
      this.timer = setInterval(() => this.updateKalkulasi(), 15000);
    },

    updateKalkulasi() {
      if (!this.trx) return;
      const now      = new Date();
      const masuk    = new Date(this.trx.waktu_masuk.replace(' ', 'T'));
      const jamReal  = (now - masuk) / 3600000;
      const jamTagih = Math.max(1, Math.ceil(jamReal));
      this.biayaTotal = (parseFloat(this.trx.tarif_masuk)||0) + jamTagih*(parseFloat(this.trx.tarif_per_jam)||0);
      const j = Math.floor(jamReal), m = Math.floor((jamReal-j)*60);
      this.durasiLabel = (j>0?j+' jam ':'')+m+' menit (ditagih '+jamTagih+' jam)';
      this.waktuKeluar = now.toLocaleString('id-ID',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit'});
    },

    hitungKembalian(val) {
      this.kembalian = (parseFloat(val)||0) - this.biayaTotal;
    },

    setBayar(nominal) {
      document.getElementById('bayarInputAjax').value = nominal;
      this.kembalian = nominal - this.biayaTotal;
    },

    fmtRp(n) { return 'Rp ' + Math.abs(Math.round(n)).toLocaleString('id-ID'); },
    fmtWaktu(dt) {
      return new Date(dt.replace(' ','T')).toLocaleString('id-ID',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'});
    }
  }
}
</script>