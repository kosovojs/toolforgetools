<?php
function run_command(array $input)
{
 $arg1 = $input['arg1'];
 $arg2 = $input['arg2'];
 
 $command = "python3 myscript.py $arg1 $arg2";
 
 #change to my python project directory
 #enable debugging with 2>&1
 #throw output in $output

 exec("cd /home/python_project/app && $command 2>&1", $output);
 var_dump($output);
}


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

putenv("PATH=/usr/local/bin/:" . exec('echo $PATH'));

$command = escapeshellcmd("/urs/bin/python3 /data/project/edgars/pwb/ir_r_py3_v2.py");
//$args = escapeshellarg("-faname:" . $_GET["fawiki"]) . " " . escapeshellarg("-enname:" .  $_GET["enwiki"]);
$output = shell_exec($command . " 2>&1");
echo $output;