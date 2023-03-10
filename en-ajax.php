<?php
$nosecu = true;
include("./includes/common.php");
$act=isset($_GET['act'])?daddslashes($_GET['act']):null;

if(strpos($_SERVER['HTTP_REFERER'],$_SERVER['HTTP_HOST'])===false)exit('{"code":403}');

@header('Content-Type: application/json; charset=UTF-8');

switch($act){
case 'upload':
	if(!isset($_FILES['file']))exit('{"code":-1,"msg":"Please select file"}');
	if(!$_POST['csrf_token'] || $_POST['csrf_token']!=$_SESSION['csrf_token'])exit('{"code":-1,"msg":"CSRF TOKEN ERROR"}');
	$name=trim(htmlspecialchars($_FILES['file']['name']));
	$size=intval($_FILES['file']['size']);
	$hide = $_POST['show']==1?0:1;
	$ispwd = intval($_POST['ispwd']);
	$pwd = $ispwd==1?trim(htmlspecialchars($_POST['pwd'])):null;
	$name = str_replace(['/','\\',':','*','"','<','>','|','?'],'',$name);
	if(empty($name))exit('{"code":-1,"msg":"The filename cannot be empty"}');
	if($ispwd==1 && !empty($pwd)){
		if (!preg_match('/^[a-zA-Z0-9]+$/', $pwd)) {
			exit('{"code":-1,"msg":"The file password can only be alphabetic and numeric"}');
		}
	}
	$extension=explode('.',$name);
	if (($length = count($extension)) > 1) {
		$ext = strtolower($extension[$length - 1]);
	}
	if(strlen($ext)>6)$ext='';
	if($conf['type_block']){
		$type_block = explode('|',$conf['type_block']);
		if(in_array($ext,$type_block)){
			exit('{"code":-1,"msg":"File upload failed","error":"block"}');
		}
	}
	if($conf['name_block']){
		$name_block = explode('|',$conf['name_block']);
		foreach($name_block as $row){
			if(strpos($name,$row)!==false){
				exit('{"code":-1,"msg":"File upload failed","error":"block"}');
			}
		}
	}
	if($conf['upload_limit']>0){
		$ipcount=$DB->getColumn("SELECT count(*) from pre_file WHERE ip='$ip' AND addtime>'".date("Y-m-d H:i:s",strtotime("-1 days"))."'");
		if($ipcount>$conf['upload_limit']){
			exit('{"code":-1,"msg":"The number of files you uploaded today has exceeded the limit"}');
		}
	}
	$hash = md5_file($_FILES['file']['tmp_name']);
	$row = $DB->getRow("SELECT * FROM pre_file WHERE hash=:hash", [':hash'=>$hash]);
	if($row){
		unset($_SESSION['csrf_token']);
		$result = ['code'=>0, 'msg'=>'This file already exists on this site', 'exists'=>1, 'hash'=>$hash, 'name'=>$name, 'size'=>$size, 'type'=>$ext, 'id'=>$row['id']];
		exit(json_encode($result));
	}
	$result = $stor->upload($hash, $_FILES['file']['tmp_name']);
	if(!$result)exit('{"code":-1,"msg":"File upload failed","error":"stor"}');
	$sds = $DB->exec("INSERT INTO `pre_file` (`name`,`type`,`size`,`hash`,`addtime`,`ip`,`hide`,`pwd`) values (:name,:type,:size,:hash,NOW(),:ip,:hide,:pwd)", [':name'=>$name, ':type'=>$ext, ':size'=>$size, ':hash'=>$hash, ':ip'=>$clientip, ':hide'=>$hide, ':pwd'=>$pwd]);
	if(!$sds)exit('{"code":-1,"msg":"Upload failed'.$DB->error().'","error":"database"}');
	$id = $DB->lastInsertId();

	$type_image = explode('|',$conf['type_image']);
	$type_video = explode('|',$conf['type_video']);
	if($conf['green_check']==1 && in_array($ext,$type_image)){
		$apiurl = $conf['apiurl']?$conf['apiurl']:$siteurl;
		$fileurl = $apiurl.'view.php/'.$hash.'.'.$ext;
		if(checkImage($fileurl)==true){
			$DB->exec("UPDATE `pre_file` SET `block`=1 WHERE `id`='{$id}' LIMIT 1");
		}
	}
	if($conf['videoreview']==1 && in_array($ext,$type_video)){
		$DB->exec("UPDATE `pre_file` SET `block`=2 WHERE `id`='{$id}' LIMIT 1");
	}
	
	$_SESSION['fileids'][] = $id;
	unset($_SESSION['csrf_token']);
	$result = ['code'=>0, 'msg'=>'File upload successful!', 'exists'=>0, 'hash'=>$hash, 'name'=>$name, 'size'=>$size, 'type'=>$ext, 'id'=>$id];
	exit(json_encode($result));
break;
case 'deleteFile':
	$hash = isset($_POST['hash'])?trim($_POST['hash']):exit('{"code":-1,"msg":"no hash"}');
	if(!$_POST['csrf_token'] || $_POST['csrf_token']!=$_SESSION['csrf_token'])exit('{"code":-1,"msg":"CSRF TOKEN ERROR"}');
	$row = $DB->getRow("SELECT * FROM `pre_file` WHERE `hash`=:hash", [':hash'=>$hash]);
	if(!$row)exit('{"code":-1,"msg":"File does not exist"}');
	if(!in_array($row['id'], $_SESSION['fileids']))exit('{"code":-1,"msg":"Without permission"}');
	if($row['block']==1)exit('{"code":-1,"msg":"The file has been frozen and cannot be deleted"}');
	if(strtotime($row['addtime'])<strtotime("-7 days"))exit('{"code":-1,"msg":"Unable to delete files from 7 days ago"}');
	$result = $stor->delete($row['hash']);
	$sql = "DELETE FROM pre_file WHERE id=:id";
	if($DB->exec($sql, [':id'=>$row['id']]))exit('{"code":0,"msg":"File deletion successful!"}');
	else exit('{"code":-1,"msg":"File deletion failed['.$DB->error().']"}');
default:
	exit('{"code":-4,"msg":"No Act"}');
break;
}