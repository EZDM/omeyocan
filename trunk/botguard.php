<?php

$botguard_cookie_name = "botguard";
$botguard_expire_time = 1000000;

function get_cookie_value() {
	global $botguard_expire_time;
	return  md5("a;lsdkfj".floor(time()/$botguard_expire_time));
}

function verify_botguard() {
	global $botguard_cookie_name;
	if(@$_COOKIE[$botguard_cookie_name] == get_cookie_value()) {
		return true;
	}
	return verify_captcha();
}

function verify_captcha() {
	$captcha = false;
	if(isset($_POST['g-recaptcha-response'])){
		$captcha=$_POST['g-recaptcha-response'];
	}
	if(!$captcha){
		return false;
	}

	$response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6Lecb8ASAAAAABcfXd6PvPUQPiYvQ4BI0ObLd1In&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']);
	$obj=json_decode($response, true);

	if($obj["success"] == false)
	{
		return false;
	}

	global $botguard_cookie_name, $botguard_expire_time;
	setcookie($botguard_cookie_name, get_cookie_value(),
			time() + $botguard_expire_time, "/");
	return true;
}

?>
