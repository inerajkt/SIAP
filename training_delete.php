<?php
require_once __DIR__.'/includes/module.php';
require_roles(['admin','staff','admin_humas']);
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location: trainings.php');exit;}
verify_csrf();$id=(int)($_POST['id']??0);
try{$s=$pdo->prepare('DELETE FROM trainings WHERE id=?');$s->execute([$id]);flash('Data pelatihan berhasil dihapus.');}
catch(PDOException $e){flash('Pelatihan tidak dapat dihapus karena sudah memiliki pendaftaran.');}
header('Location: trainings.php');exit;
