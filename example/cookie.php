<?php
use Whilegit\Utils\Misc;

require_once __DIR__ . "/inc.php";

Misc::cookie(Misc::random(6), 'test cookies, might be timeout in 60 seconds', 60);