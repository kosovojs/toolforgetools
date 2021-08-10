<?php
header('Content-Type: application/json');
/* 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
 */


/* 
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Origin: *");
 */
require_once __DIR__.'/npp.php';

$npp = new NPP();

$data = $npp->getRequest("data");
$action = $npp->getRequest("action");

if (!empty($action)) {
    switch ($action) {
        case 'authorize':
            $npp->authorize();
            return;
        case 'userinfo':
            echo $npp->userInfo();
            break;
        case 'logout':
            echo $npp->logout();
            break;
            
		case "main_comments_npp":
			echo $npp->commentPage();
			break;
			
		case "main_list_npp":
			echo $npp->mainList();
			break;
			
		case "search":
			echo $npp->search($npp->getRequest("text"));
			break;
			
		case "archive_npp":
			echo $npp->archive($npp->getRequest("data"));
			break;
			
		case "npp_get_new":
			echo $npp->getArticle($npp->getRequest("last_id"), $npp->getRequest("type1"));
			break;
			
		case "graphdata":
			echo $npp->graphdata($npp->getRequest("period"));
			break;
        
		case "npp_save":
			echo $npp->saveArticle($npp->getRequest("id"));
			break;
        
		case "set_for_deletion":
			echo $npp->setArticleToDeletion($npp->getRequest("title"),$npp->getRequest("days"),$npp->getRequest("reason"));
			break;
        
		case "add_iw_wb":
			echo $npp->addIWFromWD($npp->getRequest("wikibaseItem"),$npp->getRequest("articleTitle"));
			break;
        
		case "npp_comment":
			echo $npp->putForLater($npp->getRequest("id"), $npp->getRequest("comment"));
			break;
    }
}
