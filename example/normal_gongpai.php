<?php
use Whilegit\Database\Model;
use Whilegit\Database\Basic\IPdo;
use Whilegit\Tree\NormalGongpai\GongpaiModel;
//use Whilegit\Model\Virtual\While_Member; //虚拟模型，报错不要理会

error_reporting(E_ALL);
ini_set('error_displays', 1);
require_once __DIR__ . '/trace.php';

$db_config  = array(
    'dbname' => 'php_study',
    'host' => '127.0.0.1',
    'port' => 3306,
    'username' => 'root',
    'password' => '317507',
    'charset' => 'utf8');

IPdo::instance('master', $db_config);
IPdo::instance()->table(function($table){return "ims_{$table}";});
Model::model_init(IPdo::instance());

$floorAwards = array();
/*
for($i = 2; $i<100; $i++){
    
    $parent_id = mt_rand(1, $i-1);
    $gpitem = GongpaiModel::get(array('user_id'=>$parent_id));
    $new_user_id = $i;
    $entry = $gpitem->addSub($new_user_id);

    $floorAwardEntry = $entry->detectFloorAward();
    if($floorAwardEntry instanceof GongpaiModel) {
        $floorAwards[] = "<div>增加会员[{$entry['user_id']}]时，父级[{$floorAwardEntry['user_id']}]获得层奖</div>";
    }
}
*/

//echo GongpaiModel::plot($gpitem); die;
$entry = GongpaiModel::get(array('user_id'=>35));
$siblings = $entry->detectPairAward();

/*
$gpitem = GongpaiModel::get(array('user_id'=>1));
$new_user_id = 9;
$gpitem->addSub($new_user_id);
*/

?>
<html>
<head>
	<title></title>
	<script src='../../static/js/jquery-1.11.1.min.js'></script>
	<style>
	.sub{display:block;}
	</style>
</head>
<body>
<div>
 <pre><?php var_dump(array_keys($siblings));?></pre>
    <?php 
    echo GongpaiModel::plot(); 
    
    //$parents = GongpaiModel::ls(array('user_id'=> array(1,2,3)), null, 'user_id');
    //MM($parents);
    
    //$gpitem = GongpaiModel::get(array('user_id'=>300));
    //$gpitem->buildParentTree();
    ?>
   
</div>
<div>
<?php 
echo '<div>获得层奖次数：'.count($floorAwards) . '</div>';
for($i = 0; $i<count($floorAwards); $i++) {
    echo $floorAwards[$i];
}
?>
</div>
</body>
<script>
$(".key").click(function(){
	$(this).next().toggle();
});
</script>
</html>
