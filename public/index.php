<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!file_exists('sessions')) {
    mkdir('sessions');
}

$logger = new \Monolog\Logger('name');
$logger->pushHandler(new \Monolog\Handler\StreamHandler('debug.log', \Monolog\Logger::DEBUG));

// Sessions expire 10 seconds after last written
$storage = new \Cadre\DomainSession\Storage\Files('sessions', 'PT10S');
$storage->setLogger($logger);

$manager = new \Cadre\DomainSession\SessionManager($storage);
$manager->setLogger($logger);

$id = $_COOKIE['PHP_SESSION'] ?? null;

$session = $manager->start($id);

echo $id . "<br>\n";
echo 'Timestamp: ' . ($session->timestamp ?? 'Unknown');

$session->timestamp = date('Y-m-d H:i:s');

// Regenerate ID
$session->getId()->regenerate();

$manager->finish($session);

if ($session->getId()->hasUpdatedValue()) {
    setcookie('PHP_SESSION', $session->getId()->value());
}
