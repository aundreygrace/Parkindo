<?php
/**
 * views/laporan/excel.php
 * Format: XML SpreadsheetML — dibuka langsung Excel/LibreOffice
 * Variabel: $dari, $sampai, $rekap, $transaksi
 *
 * Output via header Content-Type: application/vnd.ms-excel
 */

// Fungsi escape XML
function xStr(?string $s): string {
    return htmlspecialchars((string)$s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}
function xNum($n): string {
    return (string)(is_numeric($n) ? $n : 0);
}
?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<?php echo '<?mso-application progid="Excel.Sheet"?>'; ?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
          xmlns:o="urn:schemas-microsoft-com:office:office"
          xmlns:x="urn:schemas-microsoft-com:office:excel"
          xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">

  <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
    <Title>Laporan Parkir <?= xStr($dari) ?> s/d <?= xStr($sampai) ?></Title>
    <Author><?= xStr($_SESSION['user_nama'] ?? APP_NAME) ?></Author>
    <Created><?= date('Y-m-d\TH:i:s\Z') ?></Created>
    <Company><?= xStr(APP_NAME) ?></Company>
  </DocumentProperties>

  <Styles>
    <!-- s62: Header utama (judul laporan) -->
    <Style ss:ID="s62">
      <Font ss:Bold="1" ss:Size="16" ss:Color="#1e293b"/>
    </Style>
    <!-- s63: Sub-header -->
    <Style ss:ID="s63">
      <Font ss:Size="11" ss:Color="#64748b"/>
    </Style>
    <!-- s64: Header kolom tabel -->
    <Style ss:ID="s64">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#1e293b"/>
        <Border ss:Position="Top"    ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#94a3b8"/>
      </Borders>
      <Font ss:Bold="1" ss:Size="10" ss:Color="#1e293b"/>
      <Interior ss:Color="#f1f5f9" ss:Pattern="Solid"/>
    </Style>
    <!-- s65: Baris data biasa -->
    <Style ss:ID="s65">
      <Alignment ss:Vertical="Center"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e2e8f0"/>
      </Borders>
      <Font ss:Size="10"/>
    </Style>
    <!-- s66: Baris data angka (rata kanan) -->
    <Style ss:ID="s66">
      <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e2e8f0"/>
      </Borders>
      <Font ss:Size="10"/>
      <NumberFormat ss:Format="#,##0"/>
    </Style>
    <!-- s67: Footer total -->
    <Style ss:ID="s67">
      <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
      <Borders>
        <Border ss:Position="Top"    ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#1e293b"/>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#1e293b"/>
      </Borders>
      <Font ss:Bold="1" ss:Size="11" ss:Color="#1e293b"/>
      <NumberFormat ss:Format="#,##0"/>
    </Style>
    <!-- s68: Label footer -->
    <Style ss:ID="s68">
      <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
      <Borders>
        <Border ss:Position="Top"    ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#1e293b"/>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#1e293b"/>
      </Borders>
      <Font ss:Bold="1" ss:Size="11" ss:Color="#1e293b"/>
    </Style>
    <!-- s69: KPI label -->
    <Style ss:ID="s69">
      <Font ss:Bold="1" ss:Size="10" ss:Color="#64748b"/>
      <Interior ss:Color="#f8fafc" ss:Pattern="Solid"/>
    </Style>
    <!-- s70: KPI nilai -->
    <Style ss:ID="s70">
      <Font ss:Bold="1" ss:Size="13" ss:Color="#1e293b"/>
      <Interior ss:Color="#f8fafc" ss:Pattern="Solid"/>
    </Style>
    <!-- s71: Ganjil row -->
    <Style ss:ID="s71">
      <Alignment ss:Vertical="Center"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e2e8f0"/>
      </Borders>
      <Font ss:Size="10"/>
      <Interior ss:Color="#f8fafc" ss:Pattern="Solid"/>
    </Style>
    <!-- s72: Ganjil angka -->
    <Style ss:ID="s72">
      <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e2e8f0"/>
      </Borders>
      <Font ss:Size="10"/>
      <Interior ss:Color="#f8fafc" ss:Pattern="Solid"/>
      <NumberFormat ss:Format="#,##0"/>
    </Style>
  </Styles>

  <!-- ══ SHEET 1: REKAP ══════════════════════════════════════ -->
  <Worksheet ss:Name="Rekap">
    <Table ss:DefaultRowHeight="18" ss:DefaultColumnWidth="100">
      <Column ss:Width="220"/>
      <Column ss:Width="140"/>
      <Column ss:Width="140"/>
      <Column ss:Width="140"/>

      <!-- Judul -->
      <Row ss:Height="28">
        <Cell ss:StyleID="s62"><Data ss:Type="String"><?= xStr(APP_NAME) ?> — Laporan Transaksi Parkir</Data></Cell>
      </Row>
      <Row ss:Height="18">
        <Cell ss:StyleID="s63">
          <Data ss:Type="String">Periode: <?= xStr(formatTanggal($dari.' 00:00:00', false)) ?> s/d <?= xStr(formatTanggal($sampai.' 00:00:00', false)) ?></Data>
        </Cell>
      </Row>
      <Row ss:Height="14">
        <Cell ss:StyleID="s63">
          <Data ss:Type="String">Digenerate: <?= date('d/m/Y H:i:s') ?> oleh <?= xStr($_SESSION['user_nama'] ?? '') ?></Data>
        </Cell>
      </Row>
      <Row ss:Height="10"/>

      <!-- KPI -->
      <Row ss:Height="20">
        <Cell ss:StyleID="s69"><Data ss:Type="String">Total Pendapatan</Data></Cell>
        <Cell ss:StyleID="s69"><Data ss:Type="String">Total Transaksi</Data></Cell>
        <Cell ss:StyleID="s69"><Data ss:Type="String">Rata-rata / Trx</Data></Cell>
        <Cell ss:StyleID="s69"><Data ss:Type="String">Rata-rata Durasi</Data></Cell>
      </Row>
      <Row ss:Height="24">
        <Cell ss:StyleID="s70"><Data ss:Type="Number"><?= xNum($rekap['total_pendapatan'] ?? 0) ?></Data></Cell>
        <Cell ss:StyleID="s70"><Data ss:Type="Number"><?= xNum($rekap['total_transaksi']  ?? 0) ?></Data></Cell>
        <Cell ss:StyleID="s70"><Data ss:Type="Number"><?= xNum(round($rekap['rata_pendapatan'] ?? 0)) ?></Data></Cell>
        <Cell ss:StyleID="s70"><Data ss:Type="String"><?= xStr(formatDurasi((float)($rekap['rata_durasi'] ?? 0))) ?></Data></Cell>
      </Row>
      <Row ss:Height="10"/>

      <!-- Header breakdown jenis -->
      <Row ss:Height="20">
        <Cell ss:StyleID="s64"><Data ss:Type="String">Jenis Kendaraan</Data></Cell>
        <Cell ss:StyleID="s64"><Data ss:Type="String">Motor</Data></Cell>
        <Cell ss:StyleID="s64"><Data ss:Type="String">Mobil</Data></Cell>
        <Cell ss:StyleID="s64"><Data ss:Type="String">Lainnya</Data></Cell>
      </Row>
      <Row>
        <Cell ss:StyleID="s69"><Data ss:Type="String">Jumlah Kendaraan</Data></Cell>
        <Cell ss:StyleID="s66"><Data ss:Type="Number"><?= xNum($rekap['total_motor']   ?? 0) ?></Data></Cell>
        <Cell ss:StyleID="s66"><Data ss:Type="Number"><?= xNum($rekap['total_mobil']   ?? 0) ?></Data></Cell>
        <Cell ss:StyleID="s66"><Data ss:Type="Number"><?= xNum($rekap['total_lainnya'] ?? 0) ?></Data></Cell>
      </Row>
    </Table>
  </Worksheet>

  <!-- ══ SHEET 2: DETAIL TRANSAKSI ══════════════════════════ -->
  <Worksheet ss:Name="Detail Transaksi">
    <Table ss:DefaultRowHeight="16">
      <Column ss:Width="80"/>
      <Column ss:Width="120"/>
      <Column ss:Width="80"/>
      <Column ss:Width="60"/>
      <Column ss:Width="110"/>
      <Column ss:Width="100"/>
      <Column ss:Width="110"/>
      <Column ss:Width="130"/>
      <Column ss:Width="130"/>
      <Column ss:Width="55"/>
      <Column ss:Width="90"/>
      <Column ss:Width="90"/>
      <Column ss:Width="90"/>
      <Column ss:Width="80"/>

      <!-- Header kolom -->
      <Row ss:Height="22">
        <Cell ss:StyleID="s64"><Data ss:Type="String">No</Data></Cell>
        <Cell ss:StyleID="s64"><Data ss:Type="String">Kode Tiket</Data></Cell>
        <Cell ss:StyleID="s64"><Data ss:Type="String">Plat Nomor</Data></Cell>
        <Cell ss:StyleID="s64"><Data ss:Type="String">Jenis</Data></Cell>
        <Cell ss:StyleID="s64"><Data ss:Type="String">Pemilik</Data></Cell>
        <Cell ss:StyleID="s64"><Data ss:Type="String">Area Parkir</Data></Cell>
        <Cell ss:StyleID="s64"><Data ss:Type="String">Petugas</Data></Cell>
        <Cell ss:StyleID="s64"><Data ss:Type="String">Waktu Masuk</Data></Cell>
        <Cell ss:StyleID="s64"><Data ss:Type="String">Waktu Keluar</Data></Cell>
        <Cell ss:StyleID="s64"><Data ss:Type="String">Durasi (Jam)</Data></Cell>
        <Cell ss:StyleID="s64"><Data ss:Type="String">Biaya Total</Data></Cell>
        <Cell ss:StyleID="s64"><Data ss:Type="String">Bayar</Data></Cell>
        <Cell ss:StyleID="s64"><Data ss:Type="String">Kembalian</Data></Cell>
        <Cell ss:StyleID="s64"><Data ss:Type="String">Status</Data></Cell>
      </Row>

      <!-- Baris data -->
      <?php
      $grandTotal = 0;
      foreach ($transaksi as $i => $t):
        $grandTotal += $t['biaya_total'];
        $isGanjil    = ($i % 2 === 0);
        $sText  = $isGanjil ? 's71' : 's65';
        $sNum   = $isGanjil ? 's72' : 's66';
      ?>
      <Row>
        <Cell ss:StyleID="<?= $sText ?>"><Data ss:Type="Number"><?= $i + 1 ?></Data></Cell>
        <Cell ss:StyleID="<?= $sText ?>"><Data ss:Type="String"><?= xStr($t['kode_parkir']) ?></Data></Cell>
        <Cell ss:StyleID="<?= $sText ?>"><Data ss:Type="String"><?= xStr($t['plat_nomor']) ?></Data></Cell>
        <Cell ss:StyleID="<?= $sText ?>"><Data ss:Type="String"><?= xStr(ucfirst($t['jenis_kendaraan'])) ?></Data></Cell>
        <Cell ss:StyleID="<?= $sText ?>"><Data ss:Type="String"><?= xStr($t['pemilik']) ?></Data></Cell>
        <Cell ss:StyleID="<?= $sText ?>"><Data ss:Type="String"><?= xStr($t['nama_area']) ?></Data></Cell>
        <Cell ss:StyleID="<?= $sText ?>"><Data ss:Type="String"><?= xStr($t['petugas']) ?></Data></Cell>
        <Cell ss:StyleID="<?= $sText ?>"><Data ss:Type="String"><?= xStr($t['waktu_masuk']) ?></Data></Cell>
        <Cell ss:StyleID="<?= $sText ?>"><Data ss:Type="String"><?= xStr($t['waktu_keluar'] ?? '-') ?></Data></Cell>
        <Cell ss:StyleID="<?= $sNum ?>"><Data ss:Type="Number"><?= xNum($t['durasi_jam']) ?></Data></Cell>
        <Cell ss:StyleID="<?= $sNum ?>"><Data ss:Type="Number"><?= xNum($t['biaya_total']) ?></Data></Cell>
        <Cell ss:StyleID="<?= $sNum ?>"><Data ss:Type="Number"><?= xNum($t['bayar']) ?></Data></Cell>
        <Cell ss:StyleID="<?= $sNum ?>"><Data ss:Type="Number"><?= xNum($t['kembalian']) ?></Data></Cell>
        <Cell ss:StyleID="<?= $sText ?>"><Data ss:Type="String"><?= xStr(ucfirst($t['status'])) ?></Data></Cell>
      </Row>
      <?php endforeach; ?>

      <!-- Total row -->
      <Row ss:Height="22">
        <Cell ss:StyleID="s68" ss:MergeAcross="9"><Data ss:Type="String">TOTAL PENDAPATAN</Data></Cell>
        <Cell ss:StyleID="s67"><Data ss:Type="Number"><?= xNum($grandTotal) ?></Data></Cell>
        <Cell ss:StyleID="s67"><Data ss:Type="String"></Data></Cell>
        <Cell ss:StyleID="s67"><Data ss:Type="String"></Data></Cell>
        <Cell ss:StyleID="s67"><Data ss:Type="String"></Data></Cell>
      </Row>
    </Table>
  </Worksheet>

</Workbook>