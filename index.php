
<?php
require_once __DIR__ . '/includes/module.php';

$role = current_role();
$page_title = 'Dashboard';

/**
 * Menghitung jumlah baris tabel.
 * Nama tabel harus berasal dari daftar tabel yang sudah ditentukan
 * di dalam kode, bukan dari input pengguna.
 */
function count_table(PDO $pdo, string $table): int
{
    $allowed = [
        'participants',
        'prospects',
        'rabs',
        'inventory_items',
        'hrd_personnel',
        'trainings',
        'hrd_documents',
        'registrations',
        'certificates'
    ];

    if (!in_array($table, $allowed, true)) {
        return 0;
    }

    try {
        return (int) $pdo
            ->query("SELECT COUNT(*) FROM `$table`")
            ->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Menghitung total nominal RAB dengan memeriksa kolom
 * yang benar-benar tersedia pada tabel rabs.
 * Jika tidak ditemukan kolom nominal yang sesuai,
 * nilai dikembalikan sebagai null.
 */
function total_rab(PDO $pdo): ?float
{
    try {
        $stmt = $pdo->query('SHOW COLUMNS FROM `rabs`');
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $candidates = [
            'total_amount',
            'total',
            'total_rab',
            'grand_total',
            'jumlah_anggaran',
            'total_anggaran',
            'nilai_anggaran',
            'amount'
        ];

        $column = null;

        foreach ($candidates as $candidate) {
            if (in_array($candidate, $columns, true)) {
                $column = $candidate;
                break;
            }
        }

        if ($column === null) {
            return null;
        }

        // Nama kolom dipilih hanya dari daftar kandidat di atas
        // dan harus cocok dengan kolom yang ada di database.
        $sql = "SELECT COALESCE(SUM(`$column`), 0) FROM `rabs`";

        return (float) $pdo->query($sql)->fetchColumn();
    } catch (PDOException $e) {
        return null;
    }
}

require __DIR__ . '/includes/header.php';
show_flash();
?>

<?php if ($role === 'admin'): ?>

<section class="hero">
    <div>
        <span class="eyebrow" style="color:#d7ffed">
            DASHBOARD SUPER ADMIN
        </span>
        <h2>Selamat datang, <?= e($_SESSION['user']['name']) ?> 👋</h2>
        <p>
            Anda memiliki akses ke seluruh dashboard dan modul
            administrasi SIAP INERA.
        </p>
    </div>
    <div class="hero-mark">ALL ACCESS</div>
</section>

<section class="cards">
    <article class="stat">
        <div class="stat-label">Peserta</div>
        <div class="stat-value">
            <?= number_format(count_table($pdo, 'participants')) ?>
        </div>
        <div class="stat-note">Data peserta pelatihan</div>
    </article>

    <article class="stat">
        <div class="stat-label">Prospek Humas</div>
        <div class="stat-value">
            <?= number_format(count_table($pdo, 'prospects')) ?>
        </div>
        <div class="stat-note">Calon peserta</div>
    </article>

    <article class="stat">
        <div class="stat-label">Total RAB</div>
        <div class="stat-value">
            <?= number_format(count_table($pdo, 'rabs')) ?>
        </div>
        <div class="stat-note">Dokumen anggaran</div>
    </article>

    <article class="stat">
        <div class="stat-label">Jenis Barang</div>
        <div class="stat-value">
            <?= number_format(count_table($pdo, 'inventory_items')) ?>
        </div>
        <div class="stat-note">Item inventaris</div>
    </article>

    <article class="stat">
        <div class="stat-label">Personel HRD</div>
        <div class="stat-value">
            <?= number_format(count_table($pdo, 'hrd_personnel')) ?>
        </div>
        <div class="stat-note">Data personel internal</div>
    </article>

    <article class="stat">
        <div class="stat-label">Kegiatan</div>
        <div class="stat-value">
            <?= number_format(count_table($pdo, 'trainings')) ?>
        </div>
        <div class="stat-note">Kegiatan pelatihan</div>
    </article>
</section>

<section class="panel">
    <div class="panel-head">
        <h3>Akses Semua Dashboard Divisi</h3>
    </div>

    <div class="actions">
        <a class="btn" href="participants.php">Humas · Data Peserta</a>
        <a class="btn" href="prospects.php">Humas · Prospek</a>
        <a class="btn" href="trainings.php">Humas · Kegiatan</a>
        <a class="btn" href="rab.php">Keuangan · Input RAB</a>
        <a class="btn" href="inventory.php">Umum · Stok Barang</a>
        <a class="btn" href="hrd.php">HRD · Data Personel</a>
        <a class="btn btn-light" href="hrd_documents.php">HRD · Dokumen</a>
        <a class="btn btn-light" href="users.php">Kelola Pengguna</a>
    </div>
</section>

<?php elseif ($role === 'admin_humas'): ?>

<section class="hero">
    <div>
        <span class="eyebrow" style="color:#d7ffed">
            DASHBOARD HUMAS
        </span>
        <h2>Halo, <?= e($_SESSION['user']['name']) ?> 👋</h2>
        <p>
            Pantau data peserta, kelola prospek, dan siapkan kegiatan pelatihan.
        </p>
    </div>
    <div class="hero-mark">HUMAS</div>
</section>

<section class="cards">
    <article class="stat">
        <div class="stat-label">Data Peserta</div>
        <div class="stat-value">
            <?= number_format(count_table($pdo, 'participants')) ?>
        </div>
        <div class="stat-note">Peserta terdata</div>
    </article>

    <article class="stat">
        <div class="stat-label">Prospek Peserta</div>
        <div class="stat-value">
            <?= number_format(count_table($pdo, 'prospects')) ?>
        </div>
        <div class="stat-note">Calon peserta</div>
    </article>

    <article class="stat">
        <div class="stat-label">Kegiatan</div>
        <div class="stat-value">
            <?= number_format(count_table($pdo, 'trainings')) ?>
        </div>
        <div class="stat-note">Kegiatan pelatihan</div>
    </article>
</section>

<section class="panel">
    <div class="panel-head">
        <h3>Akses Cepat Humas</h3>
    </div>
    <div class="actions">
        <a class="btn" href="participants.php">Data Peserta</a>
        <a class="btn" href="prospects.php">Kelola Prospek</a>
        <a class="btn" href="trainings.php">Lihat Kegiatan</a>
    </div>
</section>

<?php elseif ($role === 'admin_keuangan'): ?>

<?php $totalAnggaran = total_rab($pdo); ?>

<section class="hero">
    <div>
        <span class="eyebrow" style="color:#d7ffed">
            DASHBOARD KEUANGAN
        </span>
        <h2>Administrasi Anggaran</h2>
        <p>
            Buat Rencana Anggaran Biaya (RAB), kelola rincian biaya,
            dan cetak ringkasan anggaran.
        </p>
    </div>
    <div class="hero-mark">RAB</div>
</section>

<section class="cards">
    <article class="stat">
        <div class="stat-label">Total RAB</div>
        <div class="stat-value">
            <?= number_format(count_table($pdo, 'rabs')) ?>
        </div>
        <div class="stat-note">Dokumen anggaran</div>
    </article>

    <article class="stat">
        <div class="stat-label">Total Anggaran</div>
        <div class="stat-value" style="font-size:22px">
            <?php if ($totalAnggaran !== null): ?>
                Rp <?= number_format($totalAnggaran, 0, ',', '.') ?>
            <?php else: ?>
                Belum tersedia
            <?php endif; ?>
        </div>
        <div class="stat-note">
            <?php if ($totalAnggaran !== null): ?>
                Jumlah nominal RAB
            <?php else: ?>
                Periksa kolom nominal di tabel rabs
            <?php endif; ?>
        </div>
    </article>
</section>

<section class="panel">
    <div class="panel-head">
        <h3>Pengelolaan RAB</h3>
        <a class="btn" href="rab.php">+ Input RAB</a>
    </div>
    <p>
        Buat RAB per kegiatan, tambahkan item dan kuantitas,
        lalu sistem menghitung total sesuai data yang tersedia.
    </p>
</section>

<?php elseif ($role === 'admin_umum'): ?>

<section class="hero">
    <div>
        <span class="eyebrow" style="color:#d7ffed">
            DASHBOARD UMUM
        </span>
        <h2>Inventaris dan Stok Barang</h2>
        <p>
            Pantau jumlah barang, stok minimum, dan pergerakan barang masuk maupun keluar.
        </p>
    </div>
    <div class="hero-mark">STOK</div>
</section>

<section class="cards">
    <article class="stat">
        <div class="stat-label">Jenis Barang</div>
        <div class="stat-value">
            <?= number_format(count_table($pdo, 'inventory_items')) ?>
        </div>
        <div class="stat-note">Item inventaris</div>
    </article>

    <article class="stat">
        <div class="stat-label">Stok Menipis</div>
        <div class="stat-value">
            <?php
            try {
                $stokMenipis = (int) $pdo->query(
                    'SELECT COUNT(*) FROM inventory_items
                     WHERE quantity <= min_quantity'
                )->fetchColumn();
            } catch (PDOException $e) {
                $stokMenipis = 0;
            }

            echo number_format($stokMenipis);
            ?>
        </div>
        <div class="stat-note">Perlu ditindaklanjuti</div>
    </article>
</section>

<section class="panel">
    <div class="panel-head">
        <h3>Pengelolaan Inventaris</h3>
        <a class="btn" href="inventory.php">Kelola Stok Barang</a>
    </div>
    <p>
        Catat stok awal, barang masuk, dan barang keluar agar saldo stok selalu terpantau.
    </p>
</section>

<?php elseif ($role === 'admin_hrd'): ?>

<section class="hero">
    <div>
        <span class="eyebrow" style="color:#d7ffed">
            DASHBOARD HRD
        </span>
        <h2>Data Personel dan Dokumen</h2>
        <p>
            Kelola data personel internal serta kelengkapan dokumen pendukung secara terpusat.
        </p>
    </div>
    <div class="hero-mark">HRD</div>
</section>

<section class="cards">
    <article class="stat">
        <div class="stat-label">Data Personel</div>
        <div class="stat-value">
            <?= number_format(count_table($pdo, 'hrd_personnel')) ?>
        </div>
        <div class="stat-note">Personel terdata</div>
    </article>

    <article class="stat">
        <div class="stat-label">Dokumen Pendukung</div>
        <div class="stat-value">
            <?= number_format(count_table($pdo, 'hrd_documents')) ?>
        </div>
        <div class="stat-note">Dokumen tercatat</div>
    </article>
</section>

<section class="panel">
    <div class="panel-head">
        <h3>Pengelolaan HRD</h3>
        <div class="actions">
            <a class="btn" href="hrd.php">Data Personel</a>
            <a class="btn btn-light" href="hrd_documents.php">Dokumen Pendukung</a>
        </div>
    </div>
    <p>
        Data HRD hanya dapat diakses oleh Admin HRD dan Super Admin.
    </p>
</section>

<?php else: ?>

<?php
$counts = [];

foreach ([
    'trainings' => 'Pelatihan',
    'participants' => 'Peserta',
    'registrations' => 'Pendaftaran',
    'certificates' => 'Sertifikat'
] as $table => $label) {
    $counts[$label] = count_table($pdo, $table);
}

try {
    $upcoming = $pdo->query(
        'SELECT id, code, title, start_date, end_date, city, status
         FROM trainings ORDER BY start_date DESC LIMIT 6'
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $upcoming = [];
}
?>

<section class="hero">
    <div>
        <span class="eyebrow" style="color:#d7ffed">
            PUSAT KENDALI ADMINISTRASI
        </span>
        <h2>Selamat datang di SIAP 👋</h2>
        <p>
            Kelola kegiatan pelatihan, data peserta, pendaftaran,
            presensi, dan sertifikat dalam satu sistem terpadu.
        </p>
    </div>
    <div class="hero-mark">SIAP</div>
</section>

<section class="cards">
    <?php foreach ($counts as $label => $value): ?>
        <article class="stat">
            <div class="stat-label"><?= e($label) ?></div>
            <div class="stat-value"><?= number_format($value) ?></div>
            <div class="stat-note">Data tercatat dalam sistem</div>
        </article>
    <?php endforeach; ?>
</section>

<section class="panel">
    <div class="panel-head">
        <h3>Data Pelatihan Terbaru</h3>
        <a class="btn" href="training_form.php">+ Tambah Pelatihan</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Pelatihan</th>
                <th>Tanggal</th>
                <th>Lokasi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$upcoming): ?>
                <tr>
                    <td colspan="5" class="empty">
                        Belum ada data pelatihan.
                        Tambahkan kegiatan pertama Anda.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($upcoming as $t): ?>
                    <tr>
                        <td><?= e($t['code']) ?></td>
                        <td><strong><?= e($t['title']) ?></strong></td>
                        <td>
                            <?= e($t['start_date']) ?>
                            – <?= e($t['end_date']) ?>
                        </td>
                        <td><?= e($t['city'] ?: '-') ?></td>
                        <td><?= e($t['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
