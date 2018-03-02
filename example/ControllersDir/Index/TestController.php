<?php
namespace Controller\Index;

use Whilegit\Controller\ControllerBase;
use Whilegit\Math\Probility\Binomial;
use Whilegit\Math\Probility\Poisson;

class TestController extends ControllerBase{
    
    public function actionTest(){
        echo __FILE__ . ' ' . __LINE__;
    }
    
    
    public function actionBionomial(){
        $n = 9;
        $p = 0.2;
        echo Binomial::prob($n, $p, 4);
    }
    
    public function actionPoisson(){
        $lamda = 0.5;
        echo Poisson::prob($lamda, 1);
    }
}