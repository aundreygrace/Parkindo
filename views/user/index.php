<?php
/**
 * views/user/index.php
 * Variabel: $users, $search, $role
 */
require_once VIEW_PATH . '/layouts/components.php';
?>

<!-- Header -->
<div class="flex items-center justify-between mb-5">
  <p class="text-xs text-slate-400"><?= count($users) ?> user ditemukan</p>
  <a href="?page=user&action=create"
     class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Tambah User
  </a>
</div>

<!-- Filter -->
<div class="bg-white rounded-xl border border-slate-200 p-4 mb-4">
  <form method="GET" class="flex flex-wrap gap-3 items-end">
    <input type="hidden" name="page"   value="user">
    <input type="hidden" name="action" value="index">
    <div class="flex-1 min-w-48">
      <label class="block text-xs font-medium text-slate-500 mb-1">Cari nama / username</label>
      <input type="text" name="search" value="<?= e($search) ?>" placeholder="Ketik nama atau username…"
             class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>
    <div class="min-w-36">
      <label class="block text-xs font-medium text-slate-500 mb-1">Role</label>
      <select name="role" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="">Semua Role</option>
        <option value="admin"   <?= $role==='admin'   ? 'selected':'' ?>>Admin</option>
        <option value="petugas" <?= $role==='petugas' ? 'selected':'' ?>>Petugas</option>
        <option value="owner"   <?= $role==='owner'   ? 'selected':'' ?>>Owner</option>
      </select>
    </div>
    <div class="flex gap-2">
      <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">Filter</button>
      <a href="?page=user&action=index" class="bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium px-4 py-2 rounded-lg transition-colors">Reset</a>
    </div>
  </form>
</div>

<!-- Tabel -->
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
  <div class="overflow-x-auto">
    <?php if (empty($users)): renderEmptyState('Tidak ada user', 'Coba ubah filter atau tambah user baru.');
    else: ?>
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-slate-50 border-b border-slate-200 text-xs text-slate-500 uppercase tracking-wide">
          <th class="text-left px-5 py-3 font-medium">Nama</th>
          <th class="text-left px-3 py-3 font-medium">Username</th>
          <th class="text-left px-3 py-3 font-medium">Role</th>
          <th class="text-left px-3 py-3 font-medium">Status</th>
          <th class="text-left px-3 py-3 font-medium">Dibuat</th>
          <th class="text-center px-5 py-3 font-medium">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($users as $u): ?>
        <tr class="hover:bg-slate-50 transition-colors <?= !$u['status_aktif'] ? 'opacity-60' : '' ?>">
          <td class="px-5 py-3">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-sm font-bold text-slate-500 flex-shrink-0">
                <?= strtoupper(substr($u['nama_lengkap'], 0, 1)) ?>
              </div>
              <span class="font-medium text-slate-800"><?= e($u['nama_lengkap']) ?></span>
            </div>
          </td>
          <td class="px-3 py-3 font-mono text-xs text-slate-600"><?= e($u['username']) ?></td>
          <td class="px-3 py-3"><?= badgeRole($u['role']) ?></td>
          <td class="px-3 py-3"><?= badgeStatus((int)$u['status_aktif']) ?></td>
          <td class="px-3 py-3 text-xs text-slate-400"><?= formatTanggal($u['created_at'], false) ?></td>
          <td class="px-5 py-3">
            <div class="flex items-center justify-center gap-3">
              <a href="?page=user&action=edit&id=<?= $u['id_user'] ?>"
                 class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Edit</a>
              <?php if ($u['id_user'] !== (int)$_SESSION['user_id']): ?>
              <a href="?page=user&action=toggle&id=<?= $u['id_user'] ?>"
                 data-confirm="<?= $u['status_aktif'] ? 'User ini akan dinonaktifkan dan tidak bisa login.' : 'User ini akan diaktifkan kembali.' ?>"
                 data-confirm-title="<?= $u['status_aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?> User"
                 data-confirm-type="<?= $u['status_aktif'] ? 'warning' : 'info' ?>"
                 class="text-xs font-medium <?= $u['status_aktif'] ? 'text-amber-600 hover:text-amber-800' : 'text-green-600 hover:text-green-800' ?>">
                <?= $u['status_aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?>
              </a>
              <a href="?page=user&action=delete&id=<?= $u['id_user'] ?>"
                 data-confirm="Akun &quot;<?= e($u['username']) ?>&quot; akan dihapus permanen dari database. Aksi ini tidak dapat dibatalkan."
                 data-confirm-title="Hapus Akun Permanen"
                 data-confirm-type="danger"
                 class="text-xs text-red-500 hover:text-red-700 font-medium">
                Hapus
              </a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>