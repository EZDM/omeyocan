<?
$db = mysql_connect('mysql.netsons.com',$par_DbUser,$par_DbPassword) or die("<b>ERRORE DI ACCESSO AI DATI</B><br><a href='Javascript:location.reload()'>riprova</a>");
mysql_select_db($par_Conn);

/*
reset ($_GET);
while (list ($chiave, $valore) = each ($_GET)) {
    eval ("\$$chiave = '".str_replace("'", "\\'", $valore)."';");
}
reset ($_POST);
while (list ($chiave, $valore) = each ($_POST)) {
    eval ("\$$chiave = '".str_replace("'", "\\'", $valore)."';");
}
reset ($_SESSION);
while (list ($chiave, $valore) = each ($_SESSION)) {
    eval ("\$$chiave = '".str_replace("'", "\\'", $valore)."';");
}

$Login = $_SESSION['Login'];
$Stanza = $_SESSION['Stanza'];
*/

function pars($var) {
	return addslashes($var);
}
?>