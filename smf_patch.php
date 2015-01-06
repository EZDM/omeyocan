
include_once('botguard.php');

if(!verify_botguard()) {
	echo	'<html>
	<head>
	<title>Omeyocan verification</title>
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	</head>
	<body style="background-color: black;">
	<h2 style="color: #ff9023">Omeyocan Forum Antispam verification</h2>
	<form method="post" action="index.php">
	<div class="g-recaptcha" data-sitekey="6Lecb8ASAAAAAIMINi693lPyD8gDBuEcU5w0a03k"></div>
	<input type="submit" />
	</form><br>
	</body>
	</html>';
	return;
}

