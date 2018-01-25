<?php
require_once __DIR__ . '/inc.php';
use Whilegit\View\Template;

Template::init(array('template_path'=>ROOT_PATH . '/example/TEMP/templates', 
                     'compile_path' => ROOT_PATH . '/example/TEMP/compiled',
                     'include_template_path' => ROOT_PATH . '/example/TEMP/templates/include',
                     'include_compile_path' => ROOT_PATH . '/example/TEMP/compiled/include',
));

Template::addSpecialFunc('url', function($url, $url2 = array()){
    $ret = $url;
    if(!empty($url2) && is_array($url2)){
        $ret .= '/' . var_export($url2, true);
    }
    return $ret;
});

Template::addSpecialFunc('tomedia', function($src){
    return 'http://www.tomedia.com/' . $src;
});

function add($a, $b){
    return $a + $b;
}
Template::addSpecialFunc('add');

Template::addSpecialFunc('css', function($src){
    return "<link href=\"http://php-study/example/TEMP/css/font/{$src}\" rel=\"stylesheet\">";
});

function cp($t){
    return true;
}
Template::addSpecialDirective('/{ifa\s+(.+?)}/', '<?php if(cp($1)) { ?>');

$aa = 2;
$a = false;
include Template::render('template');


