<?php
date_default_timezone_set('Europe/Riga');

require_once __DIR__.'/../../php/oauth.php';
require_once __DIR__.'/../../php/ToolforgeCommon.php';

$tfc = new ToolforgeCommon('edgars');
$oauth = new MW_OAuth('edgars','lv','wikipedia');

$tfc->tool_user_name = 'edgars';

$conn = $tfc->openDBtool('meta_p');

var_dump($oauth->isAuthOK());

if ( !$oauth->isAuthOK() ) {
	exit();
}

$user_data = $oauth->getConsumerRights();
$user = $user_data->query->userinfo->name;


if ($user !== 'Edgars2007') {
	exit();
}

$theQuery = "select username, count(*) as balsis
from mvw_2020_pirma
where phase='second'
group by username
order by balsis desc, username asc";

$data = $conn->query($theQuery)->fetchAll("assoc");

echo '<h3>Second</h3>';
echo '<ul>';
foreach($data as $res) {
	$user1 = $res['username'];
	$balsis = $res['balsis'];
	echo "<li>$user1 - $balsis</li>";
}
echo '</ul>';

$theQuery = "select *
from mvw_2020_pirma
where phase='second'
order by vote_time asc";
$data = $conn->query($theQuery)->fetchAll("assoc");

echo '<table><tr><th>user</th><th>balsotajs</th><th>laiks</th></tr>';
foreach($data as $res) {
	$user1 = $res['username'];
	echo "<tr><td>".$user1."</td><td>".$res['voter']."</td><td>".$res['vote_time']."</td></tr>";
}
echo '</table>';


echo '<hr />';

$theQuery = "select username, count(*) as balsis
from mvw_2020_pirma
where phase='newbie'
group by username
order by balsis desc, username asc";

$data = $conn->query($theQuery)->fetchAll("assoc");

echo '<h3>Newbie</h3>';
echo '<ul>';
foreach($data as $res) {
	$user1 = $res['username'];
	$balsis = $res['balsis'];
	echo "<li>$user1 - $balsis</li>";
}
echo '</ul>';

$theQuery = "select *
from mvw_2020_pirma
where phase='newbie'
order by vote_time asc";
$data = $conn->query($theQuery)->fetchAll("assoc");

echo '<table><tr><th>user</th><th>balsotajs</th><th>laiks</th></tr>';
foreach($data as $res) {
	$user1 = $res['username'];
	echo "<tr><td>".$user1."</td><td>".$res['voter']."</td><td>".$res['vote_time']."</td></tr>";
}
echo '</table>';


echo '<hr />';

$theQuery = "select username, count(*) as balsis
from mvw_2020_pirma
where phase='first'
group by username
order by balsis desc, username asc";

$data = $conn->query($theQuery)->fetchAll("assoc");

echo '<ul>';
foreach($data as $res) {
	$user1 = $res['username'];
	$balsis = $res['balsis'];
	echo "<li>$user1 - $balsis</li>";
}
echo '</ul>';

$theQuery = "select *
from mvw_2020_pirma
where phase='first'
order by vote_time asc";
$data = $conn->query($theQuery)->fetchAll("assoc");

echo '<table><tr><th>user</th><th>balsotajs</th><th>laiks</th></tr>';
foreach($data as $res) {
	$user1 = $res['username'];
	echo "<tr><td>".$user1."</td><td>".$res['voter']."</td><td>".$res['vote_time']."</td></tr>";
}
echo '</table>';


echo '<hr />';
