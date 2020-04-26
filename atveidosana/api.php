<?php
header('Content-Type: application/json');

require_once __DIR__ . "/../connect.php";

$conn = connect_db("s53143__meta_p");
$result = mysqli_query($conn, 'SELECT `data` FROM atveidosana LIMIT 1');
$data   = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo $data[0]['data'];
