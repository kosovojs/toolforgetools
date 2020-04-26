<?php
date_default_timezone_set('Europe/Riga');

require_once __DIR__.'/../php/oauth.php';
require_once __DIR__.'/../php/ToolforgeCommon.php';

$tfc = new ToolforgeCommon('edgars');
$oauth = new MW_OAuth('edgars','lv','wikipedia');

$tfc->tool_user_name = 'edgars';

$conn = $tfc->openDBtool('meta_p');

/*
if ( !$oauth->isAuthOK() ) {
	exit();
}
*/
$user_data = $oauth->getConsumerRights();
$user = $user_data->query->userinfo->name;
/*
if ($user !== 'Edgars2007') {
	exit();
}
*/
$theQuery = "select user, count(*) as balsis
from labakais
where tips=1
group by user
order by balsis desc, user asc";

$userMap = ['Biafra', 'Edgars2007', 'Feens', 'Ingii', 'Kleivas', 'Lieeeneee', 'Ludis21345', 'MC2013', 'Papuass', 'Pirags', 'ScAvenger', 'Treisijs', 'Turaids', 'Vylks', 'Zuiks'];

$data = $conn->query($theQuery)->fetchAll("assoc");

echo '<ul>';
foreach($data as $res) {
	$user1 = $userMap[$res['user']];
	$balsis = $res['balsis'];
	echo "<li>$user1 - $balsis</li>";
}
echo '</ul>';

$theQuery = "select *
from labakais
where tips=1
order by laiks asc";
$data = $conn->query($theQuery)->fetchAll("assoc");

echo '<table><tr><th>user</th><th>balsotajs</th><th>laiks</th></tr>';
foreach($data as $res) {
	$user1 = $userMap[$res['user']];
	echo "<tr><td>".$user1."</td><td>".$res['balsotajs']."</td><td>".$res['laiks']."</td></tr>";
}
echo '</table>';


echo '<hr />';

/*  labakais jaunais  */
$theQuery = "select user, count(*) as balsis
from labakais
where tips=2
group by user
order by balsis desc, user asc";

$userMap = ['Bendžamins','Entuziasts','FubolsLatvijā','Kaamis007','Lieeeneee','Tttoooxxx','Undiine55'];

$data = $conn->query($theQuery)->fetchAll("assoc");

echo '<ul>';
foreach($data as $res) {
	$user1 = $userMap[$res['user']];
	$balsis = $res['balsis'];
	echo "<li>$user1 - $balsis</li>";
}
echo '</ul>';

$theQuery = "select *
from labakais
where tips=2
order by laiks asc";
$data = $conn->query($theQuery)->fetchAll("assoc");

echo '<table><tr><th>user</th><th>balsotajs</th><th>laiks</th></tr>';
foreach($data as $res) {
	$user1 = $userMap[$res['user']];
	echo "<tr><td>".$user1."</td><td>".$res['balsotajs']."</td><td>".$res['laiks']."</td></tr>";
}
echo '</table>';