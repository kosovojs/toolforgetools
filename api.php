<?php
header('Content-Type: application/json');
require_once __DIR__.'/php/oauth.php';
$oauth = new MW_OAuth('edgars','lv','wikipedia');

if (isset($_GET['action'])){
	switch ( $_GET['action'] ) {
		case 'authorize':
			$oauth->doAuthorizationRedirect();
			exit ( 0 ) ;
			return;
		case 'userinfo':
			echo json_encode($oauth->getConsumerRights());
			break;
		case 'logout':
			echo json_encode($oauth->logout());
			break;
	}
}