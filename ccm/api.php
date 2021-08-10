<?php
require_once 'vendor/autoload.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: token, Content-Type');

header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__.'/../php/oauth.php';
require_once __DIR__.'/../php/ToolforgeCommon.php';
/*
require_once __DIR__ . '/oauth.php';
require_once __DIR__ . '/ToolforgeCommon.php';
*/
use CCM\Items;
use CCM\Organizations;
use CCM\Ratings;

$tfc = new ToolforgeCommon('edgars');
$oauth = new MW_OAuth('edgars', 'lv','wikipedia');

$tfc->tool_user_name = 'edgars';

$conn = $tfc->openDBtool('ccm2_p');

$data = $tfc->getRequest("data");
$action = $tfc->getRequest("action");

function getPOST() {
	$json = file_get_contents('php://input');
	$data = json_decode($json, true);

	return $data;
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	echo json_encode(['status' => 'ok']);
	exit(0);
}

$CHECK_OAUTH = true;

if (!empty($action)) {
	switch ($action) {
		case 'authorize':
			$oauth->doAuthorizationRedirect();
			//exit(0);
			return;
		case 'userinfo':
			echo json_encode($oauth->getConsumerRights());
			break;
		case 'logout':
			echo json_encode($oauth->logout());
			break;

		case "overview":
			$items = new Items($conn);
			
			$data = $items->getOverview();
			echo json_encode($data);
			break;

		case "rating":
			$items = new Items($conn);
			$org = $tfc->getRequest("org");
			$rating = $tfc->getRequest("rating");
			
			$data = $items->getIssueData($rating, $org);
			echo json_encode($data);
			break;
			
		case "organization":
			$items = new Organizations($conn);
			$org = $tfc->getRequest("org");
			
			$data = $items->getOrganizationData($org);
			echo json_encode($data);
			break;

		case "org_overview":
			$items = new Organizations($conn);
			$org = $tfc->getRequest("org");
			
			$data = $items->getOverviewForOrganization($org);
			echo json_encode($data);
			break;
			
		case "save_organization":
			$Ratings = new Organizations($conn);
			$Ratings->setOauth($oauth,$CHECK_OAUTH);
			
			$data = $Ratings->saveOrg(getPOST());
			echo json_encode($data);

			break;

		case "save_item":
			$org = $tfc->getRequest("org");
			$rating = $tfc->getRequest("rating");
			$Ratings = new Ratings($conn,$rating,$org);
			$Ratings->setOauth($oauth,$CHECK_OAUTH);

			$itemData = $Ratings->ratingUpdate(getPOST());
			echo json_encode($itemData);

			break;
	}
} else {
	$arr = [
		'status' => 'error',
		'message' => 'unknown action'
	];
	echo json_encode($arr);
}