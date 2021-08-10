<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");

header('Content-Type: application/json');

date_default_timezone_set('Europe/Riga');

$jsonString = json_decode(file_get_contents('olympedia.json'), true);

echo json_encode(['status'=>'ok', 'data' => $jsonString]);
exit();
