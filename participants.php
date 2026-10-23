<?php
require_once __DIR__.'/includes/module.php';
require_roles(['admin','staff','admin_humas']);
$page_title='Data Peserta';
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$action=$_POST['action']??'';
 if($action==='save'){$id=(int)($_POST['id']??0);$name=trim($_POST['name']??'');$email=trim($_POST['email']??'');$phone=trim($_POST['phone']??'');$institution=trim($_POST['institution']??'');$education=trim($_POST['education']??'');
 if($name!==''){if($id){$s=$pdo->prepare('UPDATE participants SET name=?,email=?,phone=?,institution=?,education=? WHERE id=?');$s->execute([$name,$email?:null,$phone,$institution,$education,$id]);}else{$s=$pdo->prepare('INSERT INTO participants(name,email,phone,institution,education) VALUES(?,?,?,?,?)');$s->execute([$name,$email?:null,$phone,$institution,$education]);}flash('Data peserta disimpan.');}else flash('Nama peserta wajib diisi.');header('Location: participants.php');exit;}
 if($action==='delete'){$s=$pdo->prepare('DELETE FROM participants WHERE id=?');$s->execute([(int)$_POST['id']]);flash('Peserta dihapus.');header('Location: participants.php');exit;}
}
$edit=null;if(isset($_GET['edit'])){$s=$pdo->prepare('SELECT * FROM participants WHERE id=?');$s->execute([(int)$_GET['edit']]);$edit=$s->fetch();}
$q=trim($_GET['q']??'');$sql='SELECT * FROM participants';$params=[];if($q!==''){$sql.=' WHERE name LIKE ? OR institution LIKE ? OR email LIKE ?';$params=["%$q%","%$q%","%$q%"];}
$sql.=' ORDER BY created_at DESC';$rows=module_list($pdo,$sql,$params);
require __DIR__.'/includes/header.php';show_flash();?>
<section class="panel"><div class="panel-head"><h3><?=$edit?'Edit Peserta':'Tambah Peserta'?></h3></div><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=e((string)($edit['id']??''))?>"><div class="form-grid">
<?php foreach(['name'=>'Nama Lengkap','email'=>'Email','phone'=>'Nomor HP/WhatsApp','institution'=>'Instansi','education'=>'Pendidikan Terakhir'] as $k=>$label):?><div class="field"><label><?=e($label)?></label><input name="<?=$k?>" value="<?=e($edit[$k]??'')?>" <?=$k==='name'?'required':''?> <?=$k==='email'?'type="email"':'type="text"'?>></div><?php endforeach;?>
</div><div class="form-actions"><button class="btn">Simpan Peserta</button><?php if($edit):?><a class="btn btn-light" href="participants.php">Batal Edit</a><?php endif;?></div></form></section>
<section class="panel"><div class="panel-head"><h3>Direktori Peserta</h3></div><form class="searchbar"><input name="q" value="<?=e($q)?>" placeholder="Cari nama, instansi, email"><button class="btn btn-light">Cari</button></form><table><thead><tr><th>Nama</th><th>Instansi</th><th>Kontak</th><th>Pendidikan</th><th>Aksi</th></tr></thead><tbody><?php if(!$rows):?><tr><td colspan="5" class="empty">Belum ada peserta.</td></tr><?php endif;foreach($rows as $r):?><tr><td><strong><?=e($r['name'])?></strong></td><td><?=e($r['institution'])?></td><td><?=e($r['phone'])?><br><?=e($r['email'])?></td><td><?=e($r['education'])?></td><td><div class="actions"><a class="btn btn-light" href="?edit=<?=$r['id']?>">Edit</a><form method="post" onsubmit="return confirm('Hapus peserta ini?')"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn btn-danger">Hapus</button></form></div></td></tr><?php endforeach;?></tbody></table></section>
<?php require __DIR__.'/includes/footer.php';?>
