<?php
use Whilegit\Utils\Sms;
use Whilegit\Utils\Trace;

require_once __DIR__ . "/inc.php";

Sms::init('LTAIuK5a7gprq5rd','moMRNbrKlvSoqxI9uqQiLg94z0zJRr');
Trace::out(Sms::send('林忠仁', 'SMS_86610162', '18968596872', array('code'=>'123456')));
//Trace::out(Sms::query('18968596872', date('Ymd'), 10, 1));