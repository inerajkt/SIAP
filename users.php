
<?php
require_once __DIR__ . '/includes/module.php';

require_roles(['admin']);

if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Akses hanya untuk admin.');
}

$page_title = 'Manajemen Pengguna';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';

    // PROSES HAPUS PENGGUNA
    if ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            flash('ID pengguna tidak valid.');
            header('Location: users.php');
            exit;
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT id, name, email, role FROM users WHERE id = ?'
            );
            $stmt->execute([$id]);
            $target = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$target) {
                flash('Pengguna tidak ditemukan.');
            } else {
                $currentId = $_SESSION['user']['id'] ?? null;
                $currentEmail = $_SESSION['user']['email'] ?? '';

                // Lindungi akun yang sedang digunakan.
                if (
                    ($currentId !== null && (string)$id === (string)$currentId)
                    || (
                        $currentEmail !== ''
                        && strcasecmp($currentEmail, $target['email']) === 0
                    )
                ) {
                    flash('Akun yang sedang digunakan tidak boleh dihapus.');
                } else {
                    // Lindungi Super Admin terakhir.
                    if ($target['role'] === 'admin') {
                        $stmt = $pdo->query(
                            "SELECT COUNT(*) FROM users
                             WHERE role = 'admin' AND is_active = 1"
                        );
                        $adminAktif = (int)$stmt->fetchColumn();

                        if ($adminAktif <= 1 && (int)$target['is_active'] === 1) {
                            flash('Super Admin aktif terakhir tidak boleh dihapus.');
                            header('Location: users.php');
                            exit;
                        }
                    }

                    $stmt = $pdo->prepare(
                        'DELETE FROM users WHERE id = ?'
                    );
                    $stmt->execute([$id]);

                    flash('Akun pengguna berhasil dihapus.');
                }
            }
        } catch (PDOException $e) {
            flash(
                'Akun gagal dihapus. Akun mungkin masih terhubung '
                . 'dengan data lain di database.'
            );
        }

        header('Location: users.php');
        exit;
    }

    // PROSES TAMBAH PENGGUNA
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'admin_humas';

    $allowedRoles = [
        'admin',
        'admin_humas',
        'admin_keuangan',
        'admin_umum',
        'admin_hrd',
        'staff'
    ];

    if (!in_array($role, $allowedRoles, true)) {
        $role = 'admin_humas';
    }

    if (
        $name !== ''
        && filter_var($email, FILTER_VALIDATE_EMAIL)
        && strlen($password) >= 10
    ) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO users (name, email, password_hash, role)
                 VALUES (?, ?, ?, ?)'
            );

            $stmt->execute([
                $name,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                $role
            ]);

            flash('Pengguna berhasil dibuat.');
        } catch (PDOException $e) {
            flash('Email sudah digunakan atau data tidak valid.');
        }
    } else {
        flash('Isi nama, email valid, dan kata sandi minimal 10 karakter.');
    }

    header('Location: users.php');
    exit;
}

$rows = module_list(
    $pdo,
    'SELECT id, name, email, role, is_active, created_at
     FROM users ORDER BY created_at DESC'
);

require __DIR__ . '/includes/header.php';
show_flash();
?>

<section class="panel">
    <div class="panel-head">
        <h3>Tambah Pengguna</h3>
    </div>

    <form method="post">
        <input type="hidden" name="csrf_token"
               value="<?= e(csrf_token()) ?>">

        <div class="form-grid">
            <div class="field">
                <label>Nama</label>
                <input name="name" required>
            </div>

            <div class="field">
                <label>Email</label>
                <input name="email" type="email" required>
            </div>

            <div class="field">
                <label>Kata Sandi (minimal 10 karakter)</label>
                <input name="password" type="password"
                       minlength="10" required>
            </div>

            <div class="field">
                <label>Peran</label>
                <select name="role">
                    <option value="admin_humas">Admin Humas</option>
                    <option value="admin_keuangan">Admin Keuangan</option>
                    <option value="admin_umum">Admin Umum</option>
                    <option value="admin_hrd">Admin HRD</option>
                    <option value="staff">Staf</option>
                    <option value="admin">Super Admin</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn" type="submit">Buat Pengguna</button>
        </div>
    </form>
</section>

<section class="panel">
    <div class="panel-head">
        <h3>Pengguna Terdaftar</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Peran</th>
                <th>Status</th>
                <th>Dibuat</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= e($r['name']) ?></td>
                <td><?= e($r['email']) ?></td>
                <td><?= e(role_label($r['role'])) ?></td>
                <td><?= $r['is_active'] ? 'Aktif' : 'Nonaktif' ?></td>
                <td><?= e($r['created_at']) ?></td>
                <td>
                    <?php
                    $currentId = $_SESSION['user']['id'] ?? null;
                    $currentEmail = $_SESSION['user']['email'] ?? '';

                    $isSelf =
                        ($currentId !== null
                            && (string)$r['id'] === (string)$currentId)
                        || (
                            $currentEmail !== ''
                            && strcasecmp($currentEmail, $r['email']) === 0
                        );
                    ?>

                    <?php if (!$isSelf): ?>
                        <form method="post"
                              onsubmit="return confirm('Yakin ingin menghapus akun <?= e($r['name']) ?>? Tindakan ini tidak dapat dibatalkan.');">
                            <input type="hidden" name="csrf_token"
                                   value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id"
                                   value="<?= (int)$r['id'] ?>">

                            <button type="submit"
                                    class="btn"
                                    style="background:#b91c1c;color:white;">
                                Hapus
                            </button>
                        </form>
                    <?php else: ?>
                        <span>Akun Anda</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
