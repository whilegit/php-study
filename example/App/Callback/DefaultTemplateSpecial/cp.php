<?php
use Whilegit\View\Template;

function cp($t){
    return true;
}
Template::addSpecialDirective('/{ifa\s+(.+?)}/', '<?php if(cp($1)) { ?>');