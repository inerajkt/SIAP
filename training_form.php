<?php
require_once __DIR__.'/includes/module.php';
require_roles(['admin','staff','admin_humas']);
$id=(int)($_GET['id']??0);
$data=['code'=>'','title'=>'','category'=>'Bimtek','description'=>'','start_date'=>'','end_date'=>'','city'=>'','venue'=>'','fee'=>'0','quota'=>'','status'=>'Draft'];
if($id){$s=$pdo->prepare('SELECT * FROM trainings WHERE id=?');$s->execute([$id]);$found=$s->fetch();if(!$found){http_response_code(404);exit('Data tidak ditemukan');}$data=$found;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 foreach(['code','title','category','description','start_date','end_date','city','venue','fee','quota','status'] as $k){$data[$k]=trim($_POST[$k]??'');}
 if($data['code']===''||$data['title']===''||$data['start_date']===''||$data['end_date']===''){$error='Kode, judul, dan tanggal pelatihan wajib diisi.';}
 elseif($data['end_date']<$data['start_date']){$error='Tanggal selesai tidak boleh sebelum tanggal mulai.';}
 else{
  try{
   if($id){$s=$pdo->prepare('UPDATE trainings SET code=?,title=?,category=?,description=?,start_date=?,end_date=?,city=?,venue=?,fee=?,quota=?,status=? WHERE id=?');$s->execute([$data['code'],$data['title'],$data['category'],$data['description'],$data['start_date'],$data['end_date'],$data['city'],$data['venue'],(float)$data['fee'],$data['quota']===''?null:(int)$data['quota'],$data['status'],$id]);}
   else{$s=$pdo->prepare('INSERT INTO trainings(code,title,category,description,start_date,end_date,city,venue,fee,quota,status,created_by) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)');$s->execute([$data['code'],$data['title'],$data['category'],$data['description'],$data['start_date'],$data['end_date'],$data['city'],$data['venue'],(float)$data['fee'],$data['quota']===''?null:(int)$data['quota'],$data['status'],$_SESSION['user']['id']]);}
   flash('Data pelatihan berhasil disimpan.');header('Location: trainings.php');exit;
  }catch(PDOException $e){$error=$e->getCode()==='23000'?'Kode pelatihan sudah digunakan.':'Gagal menyimpan data.';}
 }
}
$page_title=$id?'Edit Pelatihan':'Tambah Pelatihan';
require __DIR__.'/includes/header.php';show_flash();if($error):?><div class="notice error"><?=e($error)?></div><?php endif; ?>
<section class="panel"><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><div class="form-grid">
<?php
$fields=[
['code','Kode Pelatihan','text',true],['title','Nama Pelatihan','text',true],['category','Kategori','text',false],['start_date','Tanggal Mulai','date',true],['end_date','Tanggal Selesai','date',true],['city','Kota','text',false],['venue','Tempat / Hotel','text',false],['fee','Biaya per Peserta (Rp)','number',false],['quota','Kuota Peserta','number',false]];
foreach($fields as [$key,$label,$type,$required]): ?><div class="field"><label><?=e($label)?></label><input type="<?=$type?>" name="<?=$key?>" value="<?=e((string)$data[$key])?>" <?=$required?'required':''?> <?=$key==='fee'?'min="0" step="1000"':''?> <?=$key==='quota'?'min="1"':''?>></div><?php endforeach;?>
<div class="field"><label>Status</label><select name="status"><?php foreach(['Draft','Pendaftaran Dibuka','Berjalan','Selesai','Dibatalkan'] as $st):?><option <?=($data['status']===$st?'selected':'')?>><?=e($st)?></option><?php endforeach;?></select></div>
<div class="field full"><label>Deskripsi / Catatan</label><textarea name="description" rows="4"><?=e($data['description'])?></textarea></div></div><div class="form-actions"><button class="btn">Simpan Data</button><a class="btn btn-light" href="trainings.php">Kembali</a></div></form></section>
<?php require __DIR__.'/includes/footer.php'; ?>
