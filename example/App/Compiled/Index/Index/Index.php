<?php defined('WG_ACCESS_TEMPLATE') or exit('Access Denied');?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Insert title here</title>
</head>
<body>

<?php  include Whilegit\View\Template::render('header', true);?>
<?php  echo $aa;?>
<br>
{add 1 2}
<br>
<a href='<?php  echo Whilegit\View\Template::url('Index/Index/Index');?>'>链接测试</a>
<br>
<?php if(cp($a)) { ?>
ifaifaifaifaifa
<?php  } ?>
<br>
<?php  echo Whilegit\View\Template::tomedia('a.jpg');?>

</body>
</html>