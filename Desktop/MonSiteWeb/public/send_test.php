<?php
require __DIR__.'/lib/send_smtp.php';
$html = '<p>Test MailHog OK</p>';
var_dump( send_smtp_basic('test@local', 'Test', $html) );
