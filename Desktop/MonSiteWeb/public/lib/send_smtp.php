<?php
// --- Envoi simple (déjà existant) ---
if (!function_exists('send_smtp_basic')) {
function send_smtp_basic($to, $subject, $htmlBody, $from = 'no-reply@flexvtc.local') {
  $host = 'mailhog'; $port = 1025; $timeout = 5;
  $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
  if (!$socket) return false;
  $read = function() use ($socket) { return fgets($socket, 512); };
  $cmd  = function($line) use ($socket, $read) { fputs($socket, $line . "\r\n"); return $read(); };

  $read(); $cmd("HELO localhost"); $cmd("MAIL FROM:<$from>"); $cmd("RCPT TO:<$to>"); $cmd("DATA");
  $headers = "From: $from\r\nTo: $to\r\nSubject: $subject\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
  fputs($socket, $headers . "\r\n" . $htmlBody . "\r\n.\r\n"); $read(); $cmd("QUIT"); fclose($socket); return true;
}}

// --- Envoi avec pièce jointe (PDF) ---
function send_smtp_with_attachment($to, $subject, $htmlBody, $attachmentPath, $attachmentName = 'document.pdf', $from = 'no-reply@flexvtc.local') {
  if (!is_readable($attachmentPath)) { return false; }
  $host = 'mailhog'; $port = 1025; $timeout = 5;

  $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
  if (!$socket) return false;

  $read = function() use ($socket) { return fgets($socket, 512); };
  $cmd  = function($line) use ($socket, $read) { fputs($socket, $line . "\r\n"); return $read(); };

  $boundary = "=_flexvtc_" . bin2hex(random_bytes(8));
  $fileData = chunk_split(base64_encode(file_get_contents($attachmentPath)));

  $read();                       // banner
  $cmd("HELO localhost");
  $cmd("MAIL FROM:<$from>");
  $cmd("RCPT TO:<$to>");
  $cmd("DATA");

  $headers = "From: $from\r\n"
    . "To: $to\r\n"
    . "Subject: $subject\r\n"
    . "MIME-Version: 1.0\r\n"
    . "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

  $body  = "--$boundary\r\n";
  $body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
  $body .= $htmlBody . "\r\n";
  $body .= "--$boundary\r\n";
  $body .= "Content-Type: application/pdf; name=\"$attachmentName\"\r\n";
  $body .= "Content-Transfer-Encoding: base64\r\n";
  $body .= "Content-Disposition: attachment; filename=\"$attachmentName\"\r\n\r\n";
  $body .= $fileData . "\r\n";
  $body .= "--$boundary--\r\n.\r\n";

  fputs($socket, $headers . "\r\n" . $body);
  $read();
  $cmd("QUIT");
  fclose($socket);
  return true;
}
