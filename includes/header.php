<?php
@header('Content-Type: text/html; charset=UTF-8');
?><!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $conf['title']?></title>
  <meta name="keywords" content="<?php echo $conf['keywords']?>">
  <meta name="description" content="<?php echo $conf['description']?>">
  <!-- Mobile support -->
  <meta name="viewport" content="width=device-width,height=device-height,inital-scale=1.0,maximum-scale=1.0,user-scalable=no;">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  <meta name="format-detection" content="telephone=no">
  <!-- Bootstrap Material Design -->
  <link href="/assets/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet">
  <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
  <?php if($is_file){?><link rel="stylesheet" href="/assets/css/APlayer.min.css"><?php }?>
  <link href="assets/css/style.css?v=1001" rel="stylesheet">
  <script type="text/javascript" src="/assets/css/jquery.min.js"></script>
</head>
<body>

  <div class="navbar navbar-default">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-responsive-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="./"><?php echo $conf['title']?></a>
        </div>
        <div class="navbar-collapse collapse navbar-responsive-collapse">
          <ul class="nav navbar-nav">
            <li class="<?php echo checkIfActive('index,')?>"><a href="./"><i class="fa fa-folder-open" aria-hidden="true"></i> 文件列表</a></li>
            <li class="<?php echo checkIfActive('upload')?>"><a href="./upload.php"><i class="fa fa-upload" aria-hidden="true"></i> 上传文件</a></li>
            <li class="<?php echo checkIfActive('myfile')?>"><a href="./myfile.php"><i class="fa fa-user" aria-hidden="true"></i> 我的文件</a></li>
            <?php if($is_file){?>
            <li class="<?php echo checkIfActive('file')?>"><a href="./file.php"><i class="fa fa-file" aria-hidden="true"></i> 文件查看</a></li>
            <?php }?>
          </ul>
          <ul class="nav navbar-nav navbar-right">
          <li class="<?php echo checkIfActive('bbs')?>"><a href="https://bbs.mediy.cn/"><i class="fa fa-commenting" aria-hidden="true"></i> 社区论坛</a></li>
      <li class="<?php echo checkIfActive('toopan.cn')?>"><a href="https://toopan.cn/" target="_blank"><i class="fa fa-user-plus" aria-hidden="true"></i> 注册登录</a></li>
          <li class="nav-item navbar-right dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="fa fa-globe" class="navbar-right" aria-hidden="true"></i> 语言切换
        </a>
        <div style="text-align:center;" class="dropdown-menu" aria-labelledby="navbarDropdown">
          <a class="dropdown-item" href="./en-US.php"><i class="fa fa-language" aria-hidden="true"></i> English</a>
        </div>
      </li>
        </ul>
      </div>
    </div>
  </div>
