<?php
header('Content-Type: application/json');

date_default_timezone_set('Europe/Riga');

require_once __DIR__.'/../php/oauth.php';
require_once __DIR__.'/../php/ToolforgeCommon.php';

$tfc = new ToolforgeCommon('edgars');
$oauth = new MW_OAuth('edgars', 'lv', 'wikipedia');

$tfc->tool_user_name = 'edgars';

$conn = $tfc->openDBtool('wikidays_p');

$action = $tfc->getRequest("action");
$data = $tfc->getRequest("data");

if (!empty($action)) {
    switch ($action) {

        case "main_wikidays":
            main_wikidays();
            break;

        case "new_article_to_wikidays":
            new_article_to_wikidays($data);
            break;

    }
}

function main_wikidays()
{
    global $conn;
    
    $result = $conn->query("SELECT day,article,date,comment from entries")->fetchAll('assoc');
    
    echo json_encode($result);
}

function new_article_to_wikidays($data)
{
    global $conn, $oauth;
    
    if (!$oauth->isAuthOK()) {
        return;
    }
    
    $article = isset($data['article']) ? $data['article'] : "";
    $day = isset($data['day']) ? $data['day'] : 999;
    $comment = isset($data['comment']) ? $data['comment'] : "";
    $date = isset($data['date']) ? $data['date'] : "";
    
    if ($date!="") {
        //$date = date('d/m/Y H:i',strtotime($dateOLD));
        $date = DateTime::createFromFormat('Y-m-d', $date);
    }
    
    $user_data = $oauth->getConsumerRights();
    $username = $user_data->query->userinfo->name;
    
    $cur_time = date("YmdHis");
    $notif_time = $date->format('Ymd');
    
    $stmt = $conn->query("INSERT INTO entries (day,article,date,comment,entry_add_date,entry_add_user) VALUES (?, ?, ?, ?, ?, ?)", [$day,$article,$notif_time,$comment,$cur_time,$username]);
    
    if ($stmt->affectedRows() < 1) {
        echo json_encode(array('status' => 'error','message'=> 'failed'));
    } else {
        echo json_encode(array('status' => 'success','message'=> 'Everything is ok'));
    }
}
