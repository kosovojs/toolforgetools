<?php
require_once __DIR__.'/../php/oauth.php';

$oauth = new MW_OAuth('edgars','lv','wikipedia');

if ( !$oauth->isAuthOK() ) {
	echo 'Neesi ielogojies';
	return;
}
$user_data = $oauth->getConsumerRights();
$username = $user_data->query->userinfo->name;

$usersOK = ['Edgars2007','Treisijs'];

if (!in_array($username,$usersOK)) {
	echo 'Nav atļauts';
	return;
}

for($i = 0; $i < 5; $i++) {
	//$oauth->setPageText ( 'Dalībnieks:Edgars2007/Īsākie raksti' , "Edgars testē".date('H:i:s') );
}