<?php
require_once("./db.inc.php");

const DATABASE = __DIR__."/../db.sql";

if (empty($_GET['id'])) {
    header("HTTP/1.1 404 Not Found");
    echo "Not Found";
    exit;
}

$database = new Database(DATABASE);

$file = $database->getEntry((int)$_GET['id']);
if (!$file) {
    header("HTTP/1.1 404 Not Found");
    echo "Not Found";
    exit;
}

$fileName = preg_replace('/[\x00-\x1F\x7F"]/', '_', $file['filename']);
$fileName = substr($fileName, 0, 255);

header('Content-Type: ' . $file['mime_type']);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Transfer-Encoding: binary');

if (!file_exists($file['upload_path'])) {
    header("HTTP/1.1 404 Not Found");
    echo "Not Found";
    exit;
}

readfile($file['upload_path']);
