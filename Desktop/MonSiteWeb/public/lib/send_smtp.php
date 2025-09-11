<?php
// Envoi SMTP minimal pour dev avec MailHog
// Pour la prod, utilise PHPMailer ou un vrai serveur SMTP
function send_smtp_basic($to, $subject, $htmlBody, $from = 'no-reply@flexvtc.local') {
  $host = 'mailhog';   // nom du service Docker
  $port = 1025;        // port SMTP MailHog
  $timeout = 5;

  $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
  if (!$socket) return false;

  $read = function() use ($socket) { return fgets($socket, 512); };
  $cmd  = function($line) use ($socket, $read) {
    fputs($socket, $line . "\r\n");
    return $read();
  };

  $read(); // bannière
  $cmd("HELO localhost");
  $cmd("MAIL FROM:<$from>");
  $cmd("RCPT TO:<$to>");
  $cmd("DATA");

  $headers = "From: $from\r\n"
    . "To: $to\r\n"
    . "Subject: $subject\r\n"
    . "MIME-Version: 1.0\r\n"
    . "Content-Type: text/html; charset=UTF-8\r\n";

  fputs($socket, $headers . "\r\n" . $htmlBody . "\r\n.\r\n");
  $read(); // réponse DATA
  $cmd("QUIT");
  fclose($socket);
  return true;
}
