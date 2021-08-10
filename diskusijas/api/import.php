<?php

require_once __DIR__.'/npp.php';

$npp = new NPP();

echo date('YmdHis')."<br>\n";
echo $npp->importData();
echo date('YmdHis')."<br>\n";
