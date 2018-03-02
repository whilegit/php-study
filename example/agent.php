<?php

use Whilegit\Utils\Agent;

require_once __DIR__ . "/inc.php";
header("Content-type: text/html; charset=utf-8");

MM(Agent::is_android());