<?php
namespace Controller\Index;
use Whilegit\Controller\ControllerBase;

class IndexController extends ControllerBase{
    public function actionIndex(){
        global $_GPC;
        //Template::renderJson(array('a' => 1));
        $this->render();
    }
}
