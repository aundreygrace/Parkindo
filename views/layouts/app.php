<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? 'Dashboard') ?> — <?= APP_NAME ?></title>

  <!-- Favicon inline SVG — tidak perlu file eksternal -->
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAzMiAzMiI+CiAgPGRlZnM+CiAgICA8bGluZWFyR3JhZGllbnQgaWQ9ImciIHgxPSIwJSIgeTE9IjAlIiB4Mj0iMTAwJSIgeTI9IjEwMCUiPgogICAgICA8c3RvcCBvZmZzZXQ9IjAlIiBzdG9wLWNvbG9yPSIjNjY3ZWVhIi8+CiAgICAgIDxzdG9wIG9mZnNldD0iMTAwJSIgc3RvcC1jb2xvcj0iIzc2NGJhMiIvPgogICAgPC9saW5lYXJHcmFkaWVudD4KICA8L2RlZnM+CiAgPHJlY3Qgd2lkdGg9IjMyIiBoZWlnaHQ9IjMyIiByeD0iOCIgZmlsbD0idXJsKCNnKSIvPgogIDxwYXRoIGQ9Ik0xMCA4aDdhNSA1IDAgMDEwIDEwaC00djZoLTNWOHoiIGZpbGw9IndoaXRlIiBzdHJva2U9Im5vbmUiLz4KICA8cGF0aCBkPSJNMTMgMTF2NGg0YTIgMiAwIDAwMC00aC00eiIgZmlsbD0idXJsKCNnKSIgc3Ryb2tlPSJub25lIi8+Cjwvc3ZnPg==">
  <link rel="shortcut icon" href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAzMiAzMiI+CiAgPGRlZnM+CiAgICA8bGluZWFyR3JhZGllbnQgaWQ9ImciIHgxPSIwJSIgeTE9IjAlIiB4Mj0iMTAwJSIgeTI9IjEwMCUiPgogICAgICA8c3RvcCBvZmZzZXQ9IjAlIiBzdG9wLWNvbG9yPSIjNjY3ZWVhIi8+CiAgICAgIDxzdG9wIG9mZnNldD0iMTAwJSIgc3RvcC1jb2xvcj0iIzc2NGJhMiIvPgogICAgPC9saW5lYXJHcmFkaWVudD4KICA8L2RlZnM+CiAgPHJlY3Qgd2lkdGg9IjMyIiBoZWlnaHQ9IjMyIiByeD0iOCIgZmlsbD0idXJsKCNnKSIvPgogIDxwYXRoIGQ9Ik0xMCA4aDdhNSA1IDAgMDEwIDEwaC00djZoLTNWOHoiIGZpbGw9IndoaXRlIiBzdHJva2U9Im5vbmUiLz4KICA8cGF0aCBkPSJNMTMgMTF2NGg0YTIgMiAwIDAwMC00aC00eiIgZmlsbD0idXJsKCNnKSIgc3Ryb2tlPSJub25lIi8+Cjwvc3ZnPg==">

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">

  <style>
    *, *::before, *::after { box-sizing: border-box; }
    html, body { height: 100%; margin: 0; padding: 0; }
    [x-cloak] { display: none !important; }
    body { font-family: 'Inter', system-ui, sans-serif; }

    /* ── Layout ─────────────────────────────────────────────── */
    .app-shell {
      display: flex; height: 100vh; overflow: hidden;
      background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    }

    /* ── Overlay mobile ─────────────────────────────────────── */
    .sidebar-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,0.45); z-index: 150;
      backdrop-filter: blur(2px);
    }
    @media (max-width: 1024px) {
      .sidebar-overlay.show { display: block; }
    }

    /* ══════════════════════════════════════════════════════════
       SIDEBAR
       FIX #3: state disimpan server-side (cookie PHP) agar
       tidak reset saat user navigasi ke halaman lain.
       FIX #2: active state jelas — border kiri + gradient +
       dot indikator + teks gradient ungu-biru.
       FIX #1: di mobile, sidebar jadi panel overlay (fixed).
    ══════════════════════════════════════════════════════════ */
    .sidebar {
      width: 280px; min-width: 280px;
      background: linear-gradient(180deg,rgba(27,43,82,0.98) 0%,rgba(3,16,75,0.98) 100%);
      display: flex; flex-direction: column;
      height: 100vh; position: sticky; top: 0;
      overflow: hidden; flex-shrink: 0;
      transition: width .35s cubic-bezier(.4,0,.2,1),
                  min-width .35s cubic-bezier(.4,0,.2,1),
                  transform .35s cubic-bezier(.4,0,.2,1);
      border-right: 1px solid rgba(255,255,255,0.08);
      z-index: 100;
    }

    /* Desktop collapsed */
    .sidebar.collapsed { width: 80px; min-width: 80px; }
    .sidebar.collapsed .sidebar-logo-text,
    .sidebar.collapsed .nav-link span,
    .sidebar.collapsed .nav-section,
    .sidebar.collapsed .sidebar-user-info { display: none; }
    .sidebar.collapsed .nav-link { justify-content: center; padding: 11px; }
    .sidebar.collapsed .sidebar-logo { justify-content: center; padding: 22px 0; }
    .sidebar.collapsed .sidebar-user { justify-content: center; }
    .sidebar.collapsed .sidebar-footer { padding: 14px 0; }
    .sidebar.collapsed .nav-link.active::after { display: none; }

    /* Mobile overlay mode */
    @media (max-width: 1024px) {
      .sidebar {
        position: fixed; left: 0; top: 0;
        width: 280px !important; min-width: 280px !important;
        transform: translateX(-100%); z-index: 200;
      }
      .sidebar.mobile-open { transform: translateX(0); }
      .sidebar.mobile-open .sidebar-logo-text,
      .sidebar.mobile-open .nav-link span,
      .sidebar.mobile-open .nav-section,
      .sidebar.mobile-open .sidebar-user-info { display: block; }
      .sidebar.mobile-open .nav-link { justify-content: flex-start; padding: 10px 14px; }
      .sidebar.mobile-open .sidebar-logo { justify-content: flex-start; padding: 26px 20px; }
      .sidebar.mobile-open .sidebar-user { justify-content: flex-start; }
      .sidebar.mobile-open .sidebar-footer { padding: 18px 18px 100px; }
    }

    /* Logo */
    .sidebar-logo {
      display: flex; align-items: center; gap: 14px;
      padding: 26px 20px;
      border-bottom: 1px solid rgba(255,255,255,0.07);
      flex-shrink: 0; position: relative; overflow: hidden;
    }
    .sidebar-logo::after {
      content: ''; position: absolute;
      bottom: 0; left: 20px; right: 20px;
      height: 1.5px;
      background: linear-gradient(90deg,#667eea,#764ba2);
      border-radius: 2px;
    }
    .sidebar-logo-icon {
      width: 42px; height: 42px;
      background: linear-gradient(135deg,#3d4c8d,#764ba2);
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
      box-shadow: 0 6px 16px rgba(102,126,234,0.35);
    }
    .sidebar-logo-text strong {
      display: block; color: #fff; font-size: 15px; font-weight: 800;
      letter-spacing: -.5px; white-space: nowrap;
      background: linear-gradient(135deg,#fff,#a5b4fc);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .sidebar-logo-text span {
      display: block; color: #64748b; font-size: 10px;
      white-space: nowrap; letter-spacing: .5px;
    }

    /* Nav */
.sidebar-nav {
  flex: 1; overflow-y: auto; overflow-x: hidden; padding: 18px 10px;
  padding-bottom: 4px;
}
    .sidebar-nav::-webkit-scrollbar { width: 3px; }
    .sidebar-nav::-webkit-scrollbar-thumb {
      background: linear-gradient(135deg,#667eea,#764ba2); border-radius: 3px;
    }
    .nav-section {
      font-size: 9.5px; font-weight: 800; text-transform: uppercase;
      letter-spacing: 1.5px; color: #3f4f72; padding: 0 10px;
      margin: 22px 0 8px; white-space: nowrap;
    }
    .nav-section:first-child { margin-top: 0; }

    /* ── Nav link base ── */
    .nav-link {
      display: flex; align-items: center; gap: 12px;
      padding: 10px 12px; border-radius: 10px;
      color: #94a3b8; font-size: 13px; font-weight: 500;
      text-decoration: none;
      transition: all .2s ease;
      white-space: nowrap; margin-bottom: 2px;
      border-left: 3px solid transparent;
      position: relative;
    }
    .nav-link svg { flex-shrink: 0; width: 18px; height: 18px; transition: color .2s; }

    /* ── Hover ── */
    .nav-link:hover {
      background: rgba(102,126,234,0.1);
      color: #c7d2fe;
      border-left-color: rgba(102,126,234,0.3);
    }
    .nav-link:hover svg { color: #a5b4fc; }

    /* ── ACTIVE — FIX #2: solid, jelas, bertema ungu-biru ── */
    .nav-link.active {
      background: linear-gradient(90deg,rgba(102,126,234,0.22),rgba(118,75,162,0.12));
      color: #e0e7ff;
      border-left: 3px solid #667eea;
      font-weight: 700;
      box-shadow: inset 0 0 0 1px rgba(102,126,234,0.15),
                  0 2px 8px rgba(102,126,234,0.15);
    }
    .nav-link.active svg { color: #a5b4fc; }
    .nav-link.active span {
      background: linear-gradient(135deg,#e0e7ff,#a5b4fc);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    /* Dot kecil di kanan sebagai penanda tambahan */
    .nav-link.active::after {
      content: ''; position: absolute; right: 10px;
      width: 5px; height: 5px; background: #667eea;
      border-radius: 50%;
      box-shadow: 0 0 5px rgba(102,126,234,0.9);
    }

    /* Footer user */
.sidebar-footer {
  padding: 14px 10px;
  border-top: 1px solid rgba(255,255,255,0.06);
  flex-shrink: 0;
}
    .sidebar-user {
      display: flex; align-items: center; gap: 10px;
      padding: 8px 10px; border-radius: 10px; transition: background .2s;
    }
    .sidebar-user:hover { background: rgba(102,126,234,0.1); }
    .sidebar-avatar {
      width: 36px; height: 36px;
      background: linear-gradient(135deg,#667eea,#764ba2);
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: 13px; font-weight: 800; flex-shrink: 0;
    }
    .sidebar-user-info { flex: 1; min-width: 0; }
    .sidebar-user-info strong {
      display: block; color: #e2e8f0; font-size: 12px; font-weight: 700;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .sidebar-user-info span {
      display: block; color: #64748b; font-size: 10px; text-transform: capitalize;
    }
    .sidebar-logout {
      padding: 6px; color: #4b5683; border-radius: 7px;
      cursor: pointer; border: none; background: none;
      transition: all .2s; flex-shrink: 0; display: flex; align-items: center;
    }
    .sidebar-logout:hover { color: #ef4444; background: rgba(239,68,68,0.12); }

    /* ── Main area ─────────────────────────────────────────── */
    .main-area {
      flex: 1; display: flex; flex-direction: column;
      min-width: 0; height: 100vh; overflow: hidden;
    }

    /* ── Topbar — responsif ────────────────────────────────── */
    .topbar {
      background: rgba(255,255,255,0.96);
      backdrop-filter: blur(20px);
      border-bottom: 1px solid rgba(102,126,234,0.1);
      padding: 0 20px; height: 62px;
      display: flex; align-items: center;
      justify-content: space-between;
      flex-shrink: 0; gap: 12px;
      box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    }
    @media (max-width: 640px) { .topbar { padding: 0 14px; height: 56px; } }
    .topbar-left { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .topbar-right { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

    .sidebar-toggle {
      background: linear-gradient(135deg,#f1f5f9,#e2e8f0);
      border: 1px solid rgba(102,126,234,0.2);
      cursor: pointer; color: #667eea; padding: 9px;
      border-radius: 10px; display: flex; align-items: center;
      transition: all .2s; flex-shrink: 0;
    }
    .sidebar-toggle:hover {
      background: linear-gradient(135deg,#667eea,#764ba2);
      color: #fff; border-color: transparent;
    }
    .page-title {
      font-size: 16px; font-weight: 800; margin: 0;
      background: linear-gradient(135deg,#667eea,#764ba2);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
      background-clip: text; white-space: nowrap;
      overflow: hidden; text-overflow: ellipsis;
    }
    @media (max-width: 480px) { .page-title { font-size: 14px; } }

    /* Notifikasi */
    .notification-btn {
      position: relative;
      background: linear-gradient(135deg,#f1f5f9,#e2e8f0);
      border: 1px solid rgba(102,126,234,0.2);
      cursor: pointer; color: #667eea; padding: 9px;
      border-radius: 10px; display: flex; align-items: center;
      transition: all .2s;
    }
    .notification-btn:hover {
      background: linear-gradient(135deg,#667eea,#764ba2);
      color: #fff; border-color: transparent;
    }
    .notification-badge {
      position: absolute; top: -4px; right: -4px;
      width: 17px; height: 17px;
      background: linear-gradient(135deg,#ef4444,#dc2626);
      color: #fff; font-size: 9px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-weight: 800; animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0%,100% { box-shadow: 0 0 0 0 rgba(239,68,68,.4); }
      50%      { box-shadow: 0 0 0 5px rgba(239,68,68,0); }
    }
    .notification-dropdown {
      position: absolute; right: 0; top: calc(100% + 8px);
      width: 282px; background: #fff; border-radius: 14px;
      box-shadow: 0 10px 40px rgba(0,0,0,.15);
      border: 1px solid rgba(102,126,234,.12);
      z-index: 999; overflow: hidden;
    }
    @media (max-width: 480px) { .notification-dropdown { width: 260px; right: -30px; } }

    .clock {
      font-size: 11px; color: #94a3b8; font-weight: 500; white-space: nowrap;
    }
    @media (max-width: 640px) { .clock { display: none; } }

    /* ── Content — responsif ───────────────────────────────── */
    .main-content { flex: 1; overflow-y: auto; padding: 22px; }
    @media (max-width: 768px) { .main-content { padding: 14px; } }
    @media (max-width: 480px) { .main-content { padding: 10px; } }
    .main-content::-webkit-scrollbar { width: 4px; }
    .main-content::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }

    /* ── Flash Toast ───────────────────────────────────────── */
    .flash-toast {
      margin: 14px 20px 0; padding: 13px 16px; border-radius: 12px;
      font-size: 13px; font-weight: 600;
      display: flex; align-items: center; gap: 11px;
      flex-shrink: 0; border-left: 4px solid;
      animation: slideIn .3s ease;
    }
    @keyframes slideIn {
      from { opacity: 0; transform: translateY(-8px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    @media (max-width: 640px) { .flash-toast { margin: 10px 10px 0; } }
    .flash-success { background:linear-gradient(135deg,#f0fdf4,#dcfce7); color:#166534; border-color:#22c55e; }
    .flash-error   { background:linear-gradient(135deg,#fef2f2,#fee2e2); color:#991b1b; border-color:#ef4444; }
    .flash-warning { background:linear-gradient(135deg,#fffbeb,#fef3c7); color:#92400e; border-color:#f59e0b; }
    .flash-info    { background:linear-gradient(135deg,#eff6ff,#dbeafe); color:#1e40af; border-color:#667eea; }
    .flash-close {
      margin-left: auto; background: none; border: none; cursor: pointer;
      opacity: .5; padding: 4px; border-radius: 5px;
      transition: opacity .15s; display: flex; align-items: center;
    }
    .flash-close:hover { opacity: 1; }

    /* ── Modal Confirm ─────────────────────────────────────── */
    .modal-backdrop {
      position: fixed; inset: 0;
      background: rgba(15,23,42,.6); backdrop-filter: blur(4px);
      z-index: 9999; display: flex; align-items: center;
      justify-content: center; padding: 16px;
    }
    .modal-box {
      background: #fff; border-radius: 20px;
      box-shadow: 0 25px 60px rgba(0,0,0,.25);
      width: 100%; max-width: 400px; overflow: hidden;
      animation: modalIn .2s ease;
    }
    @keyframes modalIn {
      from { opacity: 0; transform: scale(.93) translateY(-10px); }
      to   { opacity: 1; transform: scale(1) translateY(0); }
    }
    .modal-head { padding: 22px 22px 0; display: flex; gap: 14px; align-items: flex-start; }
    .modal-icon-wrap {
      width: 50px; height: 50px; border-radius: 14px;
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .modal-icon-wrap.danger  { background:linear-gradient(135deg,#fef2f2,#fee2e2); }
    .modal-icon-wrap.warning { background:linear-gradient(135deg,#fffbeb,#fef3c7); }
    .modal-icon-wrap.info    { background:linear-gradient(135deg,#eff6ff,#dbeafe); }
    .modal-title { font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 5px; }
    .modal-msg   { font-size: 13px; color: #64748b; margin: 0; line-height: 1.6; }
    .modal-foot  { padding: 18px 22px 22px; display: flex; gap: 10px; justify-content: flex-end; }
    .modal-btn {
      padding: 10px 22px; border-radius: 11px;
      font-size: 13px; font-weight: 700;
      cursor: pointer; border: none; transition: all .2s;
    }
    .modal-btn:hover { opacity: .87; transform: translateY(-1px); }
    .modal-btn-cancel  { background:#f1f5f9; color:#475569; }
    .modal-btn-danger  { background:linear-gradient(135deg,#ef4444,#dc2626); color:#fff; }
    .modal-btn-warning { background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; }
    .modal-btn-primary { background:linear-gradient(135deg,#667eea,#764ba2); color:#fff; }
  </style>
</head>

<body>
<!-- Modal -->
<div id="appModal" style="display:none" class="modal-backdrop" onclick="if(event.target===this)closeModal()">
  <div class="modal-box" onclick="event.stopPropagation()">
    <div class="modal-head">
      <div class="modal-icon-wrap" id="modalIconWrap">
        <svg id="modalIcon" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"></svg>
      </div>
      <div>
        <p class="modal-title" id="modalTitle"></p>
        <p class="modal-msg"   id="modalMsg"></p>
      </div>
    </div>
    <div class="modal-foot">
      <button class="modal-btn modal-btn-cancel" onclick="closeModal()">Batal</button>
      <button class="modal-btn" id="modalConfirmBtn" onclick="confirmModal()">Ya</button>
    </div>
  </div>
</div>

<!-- Overlay mobile -->
<div id="sidebarOverlay" class="sidebar-overlay" onclick="closeMobile()"></div>

<?php
  // FIX BUG #3: baca state dari cookie PHP — tidak reset saat navigasi
  $sidebarCollapsed = ($_COOKIE['sidebar_collapsed'] ?? '0') === '1';
?>
<div class="app-shell">

  <!-- SIDEBAR -->
  <aside class="sidebar <?= $sidebarCollapsed ? 'collapsed' : '' ?>" id="sidebar">
    <div class="sidebar-logo">
      <div class="sidebar-logo-icon">
        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12h12l1-12M10 12h4"/>
        </svg>
      </div>
      <div class="sidebar-logo-text">
        <strong><?= APP_NAME ?></strong>
        <span><?= APP_TAGLINE ?></span>
      </div>
    </div>

    <nav class="sidebar-nav">
      <?php $role = $_SESSION['user_role']; ?>

      <p class="nav-section">Menu Utama</p>
      <a href="?page=dashboard" class="nav-link <?= $page==='dashboard'?'active':'' ?>">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        <span>Dashboard</span>
      </a>

      <?php if ($role===ROLE_PETUGAS): ?>
      <p class="nav-section">Transaksi</p>
      <a href="?page=transaksi&action=index" class="nav-link <?= $page==='transaksi'?'active':'' ?>">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <span>Transaksi Parkir</span>
      </a>
      <?php endif; ?>

      <?php if ($role===ROLE_ADMIN): ?>
      <p class="nav-section">Kelola Data</p>
      <a href="?page=user&action=index" class="nav-link <?= $page==='user'?'active':'' ?>">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <span>Kelola User</span>
      </a>
      <a href="?page=kendaraan&action=index" class="nav-link <?= $page==='kendaraan'?'active':'' ?>">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h8m-4-4v4M5 11l1.5 7h11L19 11M5 11H3m16 0h2M5 11a2 2 0 012-2h10a2 2 0 012 2"/>
        </svg>
        <span>Kelola Kendaraan</span>
      </a>
      <a href="?page=tarif&action=index" class="nav-link <?= $page==='tarif'?'active':'' ?>">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
        </svg>
        <span>Kelola Tarif</span>
      </a>
      <a href="?page=area&action=index" class="nav-link <?= $page==='area'?'active':'' ?>">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
        </svg>
        <span>Kelola Area</span>
      </a>
      <a href="?page=log" class="nav-link <?= $page==='log'?'active':'' ?>">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <span>Log Aktivitas</span>
      </a>
      <?php endif; ?>

      <?php if ($role===ROLE_OWNER): ?>
      <p class="nav-section">Laporan</p>
      <a href="?page=laporan&action=index" class="nav-link <?= $page==='laporan'?'active':'' ?>">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        <span>Rekap Laporan</span>
      </a>
      <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="sidebar-avatar"><?= strtoupper(substr($_SESSION['user_nama']??'U',0,1)) ?></div>
        <div class="sidebar-user-info">
          <strong><?= e($_SESSION['user_nama']??'') ?></strong>
          <span><?= e($_SESSION['user_role']??'') ?></span>
        </div>
        <button class="sidebar-logout" title="Logout"
                onclick="confirmAction('Konfirmasi Logout','Yakin ingin keluar dari sistem?','danger','?page=logout')">
          <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
        </button>
      </div>
    </div>
  </aside>

  <!-- MAIN -->
  <div class="main-area">
    <header class="topbar">
      <div class="topbar-left">
        <button class="sidebar-toggle" id="sidebarToggle">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
        <h1 class="page-title"><?= e($pageTitle??'Dashboard') ?></h1>
      </div>
      <div class="topbar-right">
        <?php
          require_once BASE_PATH.'/models/Area.php';
          $areaModel = new Area();
          $areaNotif = $areaModel->getHampirPenuh();
        ?>
        <?php if (!empty($areaNotif)): ?>
        <div x-data="{open:false}" style="position:relative">
          <button class="notification-btn" @click="open=!open">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span class="notification-badge"><?= count($areaNotif) ?></span>
          </button>
          <div x-show="open" @click.outside="open=false" x-cloak class="notification-dropdown">
            <div style="background:linear-gradient(135deg,#fffbeb,#fef3c7);border-bottom:1px solid #fde68a;padding:12px 16px">
              <p style="font-size:11px;font-weight:800;color:#92400e;margin:0">AREA HAMPIR PENUH</p>
            </div>
            <?php foreach ($areaNotif as $an): ?>
            <div style="padding:11px 16px;border-bottom:1px solid #f1f5f9">
              <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                <span style="font-size:13px;font-weight:700;color:#1e293b"><?= e($an['nama_area']) ?></span>
                <span style="font-size:12px;font-weight:800;color:<?= $an['persen_terisi']>=100?'#dc2626':'#d97706' ?>"><?= $an['persen_terisi'] ?>%</span>
              </div>
              <div style="background:#f1f5f9;border-radius:5px;height:5px;overflow:hidden">
                <div style="height:100%;background:<?= $an['persen_terisi']>=100?'#ef4444':'#f59e0b' ?>;width:<?= min($an['persen_terisi'],100) ?>%"></div>
              </div>
              <p style="font-size:10px;color:#94a3b8;margin:5px 0 0"><?= $an['terisi'] ?>/<?= $an['kapasitas'] ?> terisi</p>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
        <div class="clock" id="clock"></div>
      </div>
    </header>

    <?php $flash = getFlash(); ?>
    <?php if ($flash):
      $fc = ['success'=>'flash-success','error'=>'flash-error','warning'=>'flash-warning','info'=>'flash-info'][$flash['type']]??'flash-info';
      $fi = [
        'success'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'error'  =>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'warning'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
        'info'   =>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
      ][$flash['type']]??'';
    ?>
    <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,5000)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="flash-toast <?= $fc ?>">
      <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="flex-shrink:0"><?= $fi ?></svg>
      <span><?= e($flash['message']) ?></span>
      <button class="flash-close" @click="show=false">
        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>
    <?php endif; ?>

    <main class="main-content"><?= $content??'' ?></main>
  </div>
</div>

<script>
// Jam
(function tick(){
  const el=document.getElementById('clock');
  if(el) el.textContent=new Date().toLocaleString('id-ID',{weekday:'short',day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit',second:'2-digit'});
  setTimeout(tick,1000);
})();

// ════════════════════════════════════════════════════════════
//  SIDEBAR TOGGLE — FIX BUG #3
//  Cookie PHP disimpan agar state persist lintas halaman.
//  Mobile: overlay panel. Desktop: collapse ke ikon saja.
// ════════════════════════════════════════════════════════════
const sidebar  = document.getElementById('sidebar');
const overlay  = document.getElementById('sidebarOverlay');
const toggleBtn= document.getElementById('sidebarToggle');
const isMobile = ()=> window.innerWidth <= 1024;

function setCookie(n,v,d){
  const x=new Date(); x.setTime(x.getTime()+d*86400000);
  document.cookie=`${n}=${v};expires=${x.toUTCString()};path=/`;
}

toggleBtn.addEventListener('click',function(){
  if(isMobile()){
    // Mobile: slide in/out overlay
    const isOpen = sidebar.classList.contains('mobile-open');
    if(isOpen){ closeMobile(); }
    else{
      sidebar.classList.add('mobile-open');
      overlay.classList.add('show');
      document.body.style.overflow='hidden';
    }
  } else {
    // Desktop: collapse/expand — simpan ke cookie
    const collapsed = sidebar.classList.toggle('collapsed');
    setCookie('sidebar_collapsed', collapsed?'1':'0', 365);
  }
});

function closeMobile(){
  sidebar.classList.remove('mobile-open');
  overlay.classList.remove('show');
  document.body.style.overflow='';
}

// Reset mobile saat resize ke desktop
window.addEventListener('resize',function(){
  if(!isMobile()) closeMobile();
});

// ════════════════════════════════════════════════════════════
//  MODAL CONFIRM
// ════════════════════════════════════════════════════════════
let _mHref=null, _mCb=null;
const MICONS={
  danger: {path:'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',color:'#ef4444'},
  warning:{path:'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',color:'#f59e0b'},
  info:   {path:'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',color:'#667eea'},
};
const MBTN={danger:'modal-btn-danger',warning:'modal-btn-warning',info:'modal-btn-primary'};

function openModal(title,msg,type,href,cb){
  _mHref=href||null; _mCb=cb||null;
  const ic=MICONS[type]||MICONS.info;
  document.getElementById('modalTitle').textContent=title;
  document.getElementById('modalMsg').textContent=msg;
  document.getElementById('modalIconWrap').className='modal-icon-wrap '+type;
  document.getElementById('modalIcon').innerHTML=`<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${ic.path}"/>`;
  document.getElementById('modalIcon').style.color=ic.color;
  const btn=document.getElementById('modalConfirmBtn');
  btn.className='modal-btn '+(MBTN[type]||'modal-btn-primary');
  btn.textContent=type==='danger'?'Ya':type==='warning'?'Ya':'Lanjutkan';
  document.getElementById('appModal').style.display='flex';
  document.body.style.overflow='hidden';
}
function closeModal(){
  document.getElementById('appModal').style.display='none';
  document.body.style.overflow='';
  _mHref=null; _mCb=null;
}
function confirmModal(){
  if(_mCb){_mCb();closeModal();return;}
  if(_mHref) window.location.href=_mHref;
  closeModal();
}
function confirmAction(t,m,tp,h){ openModal(t,m,tp,h,null); }

document.addEventListener('click',function(e){
  const el=e.target.closest('[data-confirm]');
  if(!el) return;
  e.preventDefault();
  openModal(
    el.dataset.confirmTitle||'Konfirmasi',
    el.dataset.confirm,
    el.dataset.confirmType||'danger',
    el.href||el.dataset.href,null
  );
});
document.addEventListener('keydown',e=>{ if(e.key==='Escape') closeModal(); });
</script>
</body>
</html>