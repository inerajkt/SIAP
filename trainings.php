<?php
require_once __DIR__.'/includes/module.php';
require_roles(['admin','staff','admin_humas']);
$page_title='Data Pelatihan';
$q=trim($_GET['q']??'');
$sql="SELECT * FROM trainings";
$params=[];
if($q!==''){$sql.=" WHERE title LIKE ? OR code LIKE ? OR city LIKE ?";$params=["%$q%","%$q%","%$q%"];}
$sql.=" ORDER BY start_date DESC";
$rows=module_list($pdo,$sql,$params);
require __DIR__.'/includes/header.php'; show_flash();
?>
<section class="panel"><div class="panel-head"><h3>Daftar Kegiatan Pelatihan</h3><a class="btn" href="training_form.php">+ Tambah Pelatihan</a></div>
<form class="searchbar" method="get"><input name="q" value="<?= e($q) ?>" placeholder="Cari kode, judul, atau kota..."><button class="btn btn-light">Cari</button></form>
<table><thead><tr><th>Kode</th><th>Nama Pelatihan</th><th>Tanggal</th><th>Kota / Lokasi</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php if(!$rows): ?><tr><td colspan="6" class="empty">Data tidak ditemukan.</td></tr><?php endif; foreach($rows as $r): ?>
<tr><td><?=e($r['code'])?></td><td><strong><?=e($r['title'])?></strong><br><small><?=e($r['category'])?></small></td><td><?=e($r['start_date'])?><br>s.d. <?=e($r['end_date'])?></td><td><?=e($r['city'])?><br><?=e($r['venue'])?></td><td><?=e($r['status'])?></td><td><div class="actions"><a class="btn btn-light" href="training_form.php?id=<?=$r['id']?>">Edit</a><form method="post" action="training_delete.php" onsubmit="return confirm('Hapus kegiatan ini?')"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn btn-danger">Hapus</button></form></div></td></tr>
<?php endforeach; ?></tbody></table></section>
<?php require __DIR__.'/includes/footer.php'; ?>
