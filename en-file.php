<?php
include("./includes/common.php");

$title = 'Check the file - '.$conf['title'];
$is_file=true;
include SYSTEM_ROOT.'en-header.php';

$csrf_token = md5(mt_rand(0,999).time());
$_SESSION['csrf_token'] = $csrf_token;

$hash = isset($_GET['hash'])?$_GET['hash']:exit("<script language='javascript'>window.location.href='./';</script>");
$pwd = isset($_GET['pwd'])?$_GET['pwd']:null;
$row = $DB->getRow("SELECT * FROM pre_file WHERE hash=:hash", [':hash'=>$hash]);
if(!$row)exit("<script language='javascript'>alert('File does not exist');window.location.href='./';</script>");
$name = $row['name'];
$type = $row['type'];

$downurl = 'en-down.php/'.$row['hash'].'.'.$type;
if(!empty($row['pwd']))$downurl .= '&'.$row['pwd'];
$viewurl = 'view.php/'.$row['hash'].'.'.$type;

$downurl_all = $siteurl.$downurl;
$viewurl_all = $siteurl.$viewurl;

$thisurl = $siteurl.'en-file.php?hash='.$row['hash'];
if(!empty($pwd))$thisurl .= '&pwd='.$pwd;

if(isset($_SESSION['fileids']) && in_array($row['id'], $_SESSION['fileids']) && strtotime($row['addtime'])>strtotime("-7 days")){
  $is_mine = true;
}

$type_image = explode('|',$conf['type_image']);
$type_audio = explode('|',$conf['type_audio']);
$type_video = explode('|',$conf['type_video']);

if(in_array($type, $type_image)){
  $filetype = 1;
  $title = '<i class="fa fa-picture-o"></i> Picture viewer';
  $htmlcode = htmlspecialchars('<img src="'.$viewurl_all.'"/>');
  $ubbcode = '[img]'.$viewurl_all.'[/img]';
  $linktitle = 'Image links';
}elseif(in_array($type, $type_audio)){
  $filetype = 2;
  $title = '<i class="fa fa-music"></i> Music player';
  $htmlcode = htmlspecialchars('<audio id="bgmMusic" src="'.$viewurl_all.'" autoplay="autoplay" loop="loop" preload="auto"></audio>');
  $htmlcode2 = htmlspecialchars('<iframe src="'.$siteurl.'player.php?hash='.$hash.'" width="407" scrolling="no"frameborder="0"height="70"></iframe>');
  $ubbcode = '[audio=X]'.$viewurl_all.'[/audio]';
  $linktitle = 'Music links';
}elseif(in_array($type, $type_video)){
  $filetype = 3;
  $title = '<i class="fa fa-video-camera"></i> Video player';
  $htmlcode = htmlspecialchars('<video id="movies" src="'.$viewurl_all.'" autobuffer="true" controls="" width="100
  %"></video>');
  $htmlcode2 = htmlspecialchars('<iframe src="'.$siteurl.'player.php?hash='.$hash.'" width="800" height="500" scrolling="no" frameborder="0"></iframe>');
  $ubbcode = '[movie=320*180]'.$viewurl_all.'[/movie]';
  $linktitle = 'Video link';
}else{
  $filetype = 0;
  $title = '<i class="fa fa-file"></i> Check the file';
  $htmlcode = htmlspecialchars('<a href="'.$downurl_all.'" target="_blank">'.$name.'</a>');
  $ubbcode = '[url='.$downurl_all.']'.$name.'[/url]';
}
?>
<div class="container">
    <div class="row"><div align="center">
<?php
if($row['pwd']!=null && $row['pwd']!=$pwd){ ?>
  <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
  <title>Please enter your password to download the file</title>
  <script type="text/javascript">
  var pwd=prompt("Enter the password","")
  if (pwd!=null && pwd!="")
  {
      window.location.href="./en-file.php?hash=<?php echo $row['hash']?>&pwd="+pwd
  }
  </script>
<br>
<br>
This file has been encrypted by the sharer and you need to enter a password to access it
<br>
<br>
<a onclick="javascript:location.reload()" class="btn btn-xs btn-warning" style="font:14px Microsoft YaHei;">Reenter password</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="./en-US.php" class="btn btn-xs btn-warning" style="font:14px Microsoft YaHei;">Go back to the previous page</a> 
<?php
  exit;
}

?>
      <div class="col-sm-9">
<div class="panel panel-primary">
<div class="panel-heading">
<h3 class="panel-title"><?php echo $title?></h3>
</div>
<div class="panel-body" align="center">
<?php
if($filetype==1){
  echo '<div class="image_view"><a target="_blank" href="'.$viewurl.'" title="Click to view the original image"><img alt="loading" src="'.$viewurl.'" class="image"></a></div>';
}elseif($filetype==2){
  echo '<div class="view"><div id="aplayer"></div></div>';
}elseif($filetype==3 && $row['block']==0){
  echo '<div class="videosamplex"><video id="videoplayer"></video></div>';
}elseif($filetype==3){
  echo '<div class="view">
  <div class="elseview">
  <div class="tubiao"><i class="fa '.type_to_icon($type).'"></i> </div>
</div>
<div class="elsetext"><p>'.$name.'</p><p>The video files can only be played and downloaded online after they have been approved. Please wait for the approval!</p></div>
</div>';
}else{
  echo '<div class="view">
  <div class="elseview">
  <div class="tubiao"><i class="fa '.type_to_icon($type).'"></i> </div>
</div>
<div class="elsetext"><p>'.$name.'（'.size_format($row['size']).'）</p>
<a href="'.$downurl.'" class="btn btn-raised btn-primary btn-lg"><i class="fa fa-download" aria-hidden="true"></i> download file<div class="ripple-container"></div></a>
</div>
</div>';
}
?>
</div>
</div>
      <div class="panel panel-default">
          <div class="panel-body" style="padding: 0px;">
              <ul class="nav nav-tabs" style="margin-bottom: 15px;">
                  <li class="active"><a href="#link" data-toggle="tab"><i class="fa fa-link" aria-hidden="true"></i> File outside the chain</a>
                  </li>
                  <li><a href="#code" data-toggle="tab"><i class="fa fa-code" aria-hidden="true"></i> Code calls</a>
                  </li>
                  <li><a href="#info" data-toggle="tab"><i class="fa fa-info-circle" aria-hidden="true"></i> File for details</a>
                  </li>
                  <li class="<?php echo $is_mine?'':'hide';?>"><a href="#manager" data-toggle="tab"><i class="fa fa-cog" aria-hidden="true"></i> management</a>
                  </li>
              </ul>
              <div id="myTabContent" class="tab-content" style="padding: 19px;">
                  <div class="tab-pane fade active in" id="link">
                    <div class="form-group row <?php echo $filetype==0?'hide':'';?>">
                      <label for="link1" class="col-md-2 control-label"><?php echo $linktitle?>：</label>
                      <div class="col-md-10">
                        <div class="input-group">
                          <input type="text" class="form-control" id="link1" readonly="readonly" value="<?php echo $viewurl_all?>">
                          <span class="input-group-btn">
                          <button class="btn btn-primary btn-raised copy-btn" type="button" data-clipboard-text="<?php echo $viewurl_all?>">copy<div class="ripple-container"></div></button>
                          </span>
                        </div>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="link2" class="col-md-2 control-label">Download link:</label>
                      <div class="col-md-10">
                        <div class="input-group">
                          <input type="text" class="form-control" id="link2" readonly="readonly" value="<?php echo $downurl_all?>">
                          <span class="input-group-btn">
                          <button class="btn btn-primary btn-raised copy-btn" type="button" data-clipboard-text="<?php echo $downurl_all?>">copy<div class="ripple-container"></div></button>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="tab-pane fade" id="code">
                    <div class="form-group row <?php echo $filetype<2?'hide':'';?>">
                      <label for="code1" class="col-md-2 control-label">Player code:</label>
                      <div class="col-md-10">
                        <div class="input-group">
                          <input type="text" class="form-control" id="code1" readonly="readonly" value="<?php echo $htmlcode2?>">
                          <span class="input-group-btn">
                          <button class="btn btn-primary btn-raised copy-btn" type="button" data-clipboard-text="<?php echo $htmlcode2?>">copy<div class="ripple-container"></div></button>
                          </span>
                        </div>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="code2" class="col-md-2 control-label">HTML code:</label>
                      <div class="col-md-10">
                        <div class="input-group">
                          <input type="text" class="form-control" id="code2" readonly="readonly" value="<?php echo $htmlcode?>">
                          <span class="input-group-btn">
                          <button class="btn btn-primary btn-raised copy-btn" type="button" data-clipboard-text="<?php echo $htmlcode?>">copy<div class="ripple-container"></div></button>
                          </span>
                        </div>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="code3" class="col-md-2 control-label">UBB code:</label>
                      <div class="col-md-10">
                        <div class="input-group">
                          <input type="text" class="form-control" id="code3" readonly="readonly" value="<?php echo $ubbcode?>">
                          <span class="input-group-btn">
                          <button class="btn btn-primary btn-raised copy-btn" type="button" data-clipboard-text="<?php echo $ubbcode?>">copy<div class="ripple-container"></div></button>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="tab-pane fade" id="info">
                      <div class="row" align="center">
                          <table class="table table-bordered fileinfo-table">
                              <tr>
                                  <th width="97">Uploader IP:</td><td width="100"><?php echo preg_replace('/\d+$/','*',$row['ip'])?></td>
                                  <th width="100">Upload time:</td><td width="168"><?php echo $row['addtime']?></td>
                              </tr>
                              <tr>
                                  <th>Number of downloads:</td><td><?php echo $row['count']?></td>
                                  <th>File size:</td><td><?php echo size_format($row['size']).' ('.$row['size'].' byte)'?></td>
                              </tr>
                          </table>
                      </div>
                  </div>
                  <div class="tab-pane fade" id="manager">
                      <div class="row" align="center">
                          <div class="col-md-12">
                            <input type="hidden" id="hash" name="hash" value="<?php echo $hash?>">
                            <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo $csrf_token?>">
                            <button onclick="delete_confirm()" class="btn btn-raised btn-danger"><i class="fa fa-close" aria-hidden="true"></i> Delete the file</button>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
      </div>
      <div class="col-sm-3">
<div class="panel panel-primary">
<div class="panel-heading">
<h3 class="panel-title"><class="fa fa-exclamation-circle"> prompt</h3>
</div>
<div class="panel-body">
<?php echo $conf['en_gg_file']?>
</div>
</div>
<div class="panel panel-default hidden-xs">
<div class="panel-heading">
<h3 class="panel-title"><i class="fa fa-qrcode"></i> Use qr code to share this file</h3>
</div>
<div class="panel-body text-center">
<img alt="Qr code" src="//api.qrserver.com/v1/create-qr-code/?size=180x180&margin=10&data=<?php echo urlencode($thisurl);?>">
</div>
</div>
      </div>
    </div>
  </div>
<?php include SYSTEM_ROOT.'en-footer.php';?>
<?php if($filetype==2){?>
<script type="text/javascript" src="//cdn.staticfile.org/aplayer/1.10.1/APlayer.min.js"></script>
<script type="text/javascript">
var ap1 = new APlayer({
    element: document.getElementById('aplayer'),
    narrow: false,
    autoplay: false,
    showlrc: false,
    mutex: true,
    theme: '#b2dae6',
    music: {
        title: '<?php echo $name?>',
        author: '',
        url: '<?php echo $viewurl_all?>',
    }
});
</script>
<?php }elseif($filetype==3 && $row['block']==0){?>
<script type="text/javascript" src="assets/js/ckplayer.min.js"></script>
<script type="text/javascript">
    var videoObject = {
      container: '.videosamplex',
      variable: 'player',
      mobileCkControls:true,
      mobileAutoFull:false,
      h5container:'#videoplayer',
      flashplayer:false,
      video:'<?php echo $viewurl_all?>'
    };
    var player=new ckplayer(videoObject);
</script>
<?php }?>
<script src="//cdn.staticfile.org/layer/2.3/layer.js"></script>
<script src="//cdn.staticfile.org/clipboard.js/1.7.1/clipboard.min.js"></script>
<script>
function delete_confirm(){
  var hash = $("#hash").val();
  var csrf_token = $("#csrf_token").val();
  var confirmobj = layer.confirm('The deleted file is not recoverable. Are you sure to delete it?', {
	  btn: ['confirm','cancel'], icon: 0
	}, function(){
	  $.ajax({
		type : 'POST',
		url : 'en-ajax.php?act=deleteFile',
    data : {hash:hash, csrf_token:csrf_token},
		dataType : 'json',
		success : function(data) {
			if(data.code == 0){
				layer.alert('Delete the success', {icon:1}, function(){window.location.href="./";});
			}else{
				layer.alert(data.msg, {icon:2});
			}
		},
		error:function(data){
			layer.msg('Server error');
			return false;
		}
	  });
	}, function(){
	  layer.close(confirmobj);
	});
}
$(document).ready(function(){
  var clipboard = new Clipboard('.copy-btn');
  clipboard.on('success', function (e) {
    layer.msg('Copy successful!', {icon: 1});
  });
  clipboard.on('error', function (e) {
    layer.msg('Copy failed, please press the link and copy manually', {icon: 2});
  });
})
<?php
include("./foot.php");
?>
<br>
<br>
<br>
