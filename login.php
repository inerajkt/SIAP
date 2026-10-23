<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        'SELECT id, name, email, password_hash, role
         FROM users
         WHERE email = ? AND is_active = 1
         LIMIT 1'
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    $valid = false;

    if (
        $user &&
        $user['password_hash'] === 'SETUP_REQUIRED' &&
        $email === 'admin@siap.local' &&
        hash_equals('SIAPadmin2026!', $password)
    ) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);

        $update = $pdo->prepare(
            'UPDATE users
             SET password_hash = ?
             WHERE id = ? AND password_hash = ?'
        );
        $update->execute([
            $newHash,
            $user['id'],
            'SETUP_REQUIRED'
        ]);

        $valid = $update->rowCount() === 1;

        if (!$valid) {
            $check = $pdo->prepare(
                'SELECT password_hash FROM users WHERE id = ?'
            );
            $check->execute([$user['id']]);

            $valid = password_verify(
                $password,
                (string) $check->fetchColumn()
            );
        }
    } elseif (
        $user &&
        password_verify($password, $user['password_hash'])
    ) {
        $valid = true;
    }

    if ($user && $valid) {
        session_regenerate_id(true);
        unset($user['password_hash']);

        $_SESSION['user'] = $user;

        header('Location: index.php');
        exit;
    }

    $error = 'Email atau kata sandi tidak sesuai.';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Masuk | SIAP</title>

    <!-- Ikon tab browser menggunakan logo INERA -->
    <link
        rel="icon"
        type="image/png"
        href="assets/Logo%20Inera.png?v=3"
    >

    <link rel="stylesheet" href="assets/style.css">

    <style>
        .login-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .login-logo {
            width: 54px;
            height: 54px;
            object-fit: contain;
            flex-shrink: 0;
        }
    </style>
</head>

<body class="login-page">

<div class="login-card">

    <div class="login-brand">
        <img
            src="assets/Logo%20Inera.png"
            alt="Logo INERA"
            class="login-logo"
        >

        <div>
            <strong>SIAP</strong>
            <small style="display:block;color:#71817c">
                Sistem Informasi Administrasi Pelatihan
            </small>
        </div>
    </div>

    <h1>Masuk ke akun Anda</h1>
    <p>Silakan masuk untuk mengelola administrasi pelatihan.</p>

    <?php if ($error): ?>
        <div class="notice error">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(csrf_token()) ?>"
        >

        <div class="field">
            <label>Email</label>
            <input
                type="email"
                name="email"
                required
                autocomplete="username"
                placeholder="admin@siap.local"
            >
        </div>

        <div class="field">
            <label>Kata sandi</label>
            <input
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="Masukkan kata sandi"
            >
        </div>

        <button class="btn" type="submit">
            Masuk ke Dashboard →
        </button>
    </form>

    <p style="font-size:11px;margin-top:20px">
        Akun awal tercantum di README. Ganti kata sandi setelah login pertama.
    </p>

</div>

</body>
</html>