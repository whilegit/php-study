<?php
use Whilegit\View\Template;

Template::addSpecialFunc('css', function($src){
    return "<link href=\"http://php-study/example/TEMP/css/font/{$src}\" rel=\"stylesheet\">";
});