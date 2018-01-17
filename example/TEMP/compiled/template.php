<?php defined('IN_IA') or exit('Access Denied');?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Insert title here</title>
</head>
<body>
<?php  include Whilegit\View\Template::render('header', true);?>
<?php  echo $aa;?>
<br>
<?php  echo Whilegit\View\Template::add(1,2);?>
<br>
<?php  echo Whilegit\View\Template::url('mobile/order/desc');?>
<br>
<?php if(cp($a)) { ?>
ifaifaifaifaifa
<?php  } ?>
<br>
<?php  echo Whilegit\View\Template::tomedia('a.jpg');?>
</body>
</html>
