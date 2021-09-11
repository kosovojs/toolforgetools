<?php
require_once __DIR__ . '/php/oauth.php';
$oauth = new MW_OAuth('edgars', 'lv', 'wikipedia');

if (isset($_GET['action'])) {
	switch ($_GET['action']) {
		case 'authorize':
			$oauth->doAuthorizationRedirect();
			exit(0);
			return;
		case 'userinfo':
			echo json_encode($oauth->getConsumerRights());
			break;
		case 'logout':
			echo json_encode($oauth->logout());
			break;
	}
}
?>
<!DOCTYPE HTML>
<html>

<head>
	<meta charset="UTF-8">
	<title>Edgars tools</title>
	<script type="text/javascript" src="https://tools-static.wmflabs.org/cdnjs/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<link rel="stylesheet" type="text/css" href="https://tools-static.wmflabs.org/cdnjs/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
	<script>
		$.ajax({
				type: 'GET',
				url: 'api.php',
				data: {
					action: 'userinfo'
				}
			})
			.done(function(data) {
				if ('query' in data) {
					$('#username').html('Hi, ' + data['query']['userinfo']['name'] + '!');
					$('#login').html('<a href="index.php?action=logout" target="_parent">Logout</a>');


				} else {
					$('#username').html('');
					$('#login').html('<a href="index.php?action=authorize" target="_parent">Login</a>');
				}
			});
	</script>
</head>

<body>
	<nav class="navbar navbar-default" role="navigation">
		<div class="container-fluid">
			<!-- Brand and toggle get grouped for better mobile display -->
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="https://edgars.toolforge.org/">Edgars tools</a>
			</div>

			<!-- Collect the nav links, forms, and other content for toggling -->
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav navbar-right">
					<li><span id="username" class="navbar-text"></span></li>
					<li><span id="login" class="navbar-text"></span></li>
				</ul>
			</div><!-- /.navbar-collapse -->
		</div><!-- /.container-fluid -->
	</nav>
	<div class="container">
		<h3>International</h3>
		<ul>
			<li><a href="cat_check">Category checker</a></li>
			<li><a href="ccm">Community Capacity Map</a></li>
		</ul>

		<h3>lvwiki</h3>
		<ul>
			<li><a href="reminders">Atgādinājumi</a></li>
			<li><a href="atveidosana">Atveidošanas pārbaude</a></li>
			<li><a href="missing">Raksti, kas nav latviešu valodas Vikipēdijā, bet ir visvairāk citu valodu Vikipēdijās</a></li>
			<li><a href="mvw2020">Balsojums par 2020. gada vērtīgāko vikipēdistu</a></li>
			<li><a href="rlr">Red link recovery</a></li>
			<li><a href="wiki-patrol">wiki-patrol</a></li>
		</ul>
	</div>
</body>

</html>
