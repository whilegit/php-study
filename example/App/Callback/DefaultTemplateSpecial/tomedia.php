<?php
use Whilegit\View\Template;

Template::addSpecialFunc('tomedia', function($src){
    return 'http://www.tomedia.com/' . $src;
});