<?php
namespace Whilegit\Controller;
use Whilegit\View\Template;


abstract class ControllerBase{
    public function __construct(){
    }
    
    public function render($template = null){
        if(empty($template)){
            $template = WG_MODULE . '/' . WG_CONTROLLER . '/' . WG_ACTION;
        }
        include Template::render($template);
    }
}
