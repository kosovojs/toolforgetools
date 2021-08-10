<?php
//http://simplehtmldom.sourceforge.net/

require_once __DIR__ . '/Parserclass.php';

$parser = new Parser;

$thetext = $parser->getLengthForArticle('lv','Lauri Husars');
echo $thetext;

//echo $parser->getLength($thetext);