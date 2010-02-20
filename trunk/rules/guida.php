<?
include('int/password.inc.php');
include('int/open2.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>Guida Omeyocan</title>
    <link rel="stylesheet" type="text/css" href="prinstyle.css">
    <link rel="stylesheet" href="style.css" type="text/css">
    <script type="text/javascript" src="script.js"></script>
    <script type="text/javascript">

function login() {

    var password = prompt("Inserisci la password per modificare il regolamento");
    document.getElementById("passcode").value=password;
    document.getElementById("form_central").submit();

}

function passchange() {

    var passwordget = prompt("Inserire la nuova password")
    document.getElementById("passmod").value = passwordget;
    document.getElementById("passcode").value = passwordget;
    document.getElementById("form_central").submit();

}

</script>
</head>
<?

if ($_POST['passmod'] != "") mysql_query("UPDATE Utenti SET Pass = '".$_POST['passmod']."' WHERE Utente = 'guida'");

$pass = mysql_fetch_array(mysql_query("SELECT Pass FROM Utenti WHERE Utente = 'guida'"));

?>
<body bgcolor="#000000">

<center><table border=0 width="900"><tr>

<td><a href="#" onClick="login();"><font face="georgia" color="#FFFFFF" size=4>Regolamento</font></a></td></tr>

<form method="post" action="guida.php" id="form_central">
<input type="hidden" name="passcode" id="passcode">
<input type="hidden" name="passmod" id="passmod">
</form>

<tr><td valign="top"><?

$WidthCols = mysql_num_rows(mysql_query("SELECT ID FROM Guida WHERE Tipologia = '1'"));
if ($_POST['passcode'] == $pass['Pass']) $WidthCols++;
$WidthCols = $WidthCols * 150;
$WidthNoCols = (900 - $WidthCols) / 2;

?>

<table border=0 width="100%"><tr><td width="<?=$WidthNoCols?>"></td><td>
<ul class="menu" id="menu">
<?

if ($_POST['passcode'] == $pass['Pass']) {
?>

<li><a href="#" class="menulink">Master</a>
    <ul>
    <li><a href="#" onClick="document.getElementById('list_page').src='guidaadd.php?option=1';">Aggiungi</a></li>
    <li><a href="#" onClick="document.getElementById('list_page').src='guidaadd.php?option=2';">Modifica</a></li>
    <li><a href="#" onClick="document.getElementById('list_page').src='guidaadd.php?option=3';">Sposta</a></li>
    <li><a href="#" onClick="document.getElementById('list_page').src='guidaadd.php?option=4';">Cancella</a></li>
    <li><a href="#" onClick="passchange();">Cambia password</a></li>
    </ul>
</li>

<?}

$MySql1 = "SELECT * FROM Guida ORDER BY ID";
$Result1 = mysql_query($MySql1);

while ($rs1 = mysql_fetch_array($Result1)) {

$rsprima = mysql_fetch_array(mysql_query("SELECT Tipologia FROM Guida WHERE ID = '".($rs1['ID'] - 1)."'"));
$rsdopo = mysql_fetch_array(mysql_query("SELECT Tipologia FROM Guida WHERE ID = '".($rs1['ID'] + 1)."'"));

if ($rs1['Tipologia'] == '1') {
?>

<li><a href="#" onClick="document.getElementById('list_page').src='guidaview.php?ID=<?=$rs1['ID']?>';" class="menulink"><?=$rs1['Titolo']?></a>
<?
if (($rsdopo['Tipologia'] != '1') && ($rsdopo['Tipologia'] != '')) echo "<ul>";
else echo "</li>";

} else {

if ($rsprima['Tipologia'] < $rs1['Tipologia']) echo '<li class="topline">';
else echo "<li>";

?>

<a href="#" onClick="document.getElementById('list_page').src='guidaview.php?ID=<?=$rs1['ID']?>';" <?

if ($rsdopo['Tipologia'] > $rs1['Tipologia']) echo 'class="sub"';

echo ">";

echo $rs1['Titolo'];

if ($rsdopo['Tipologia'] > $rs1['Tipologia']) echo '&nbsp;&nbsp;&nbsp;<img src="freccia.gif" border=0>';

echo '</a>';

if ($rsdopo['Tipologia'] > $rs1['Tipologia']) echo "<ul>";
elseif ($rsdopo['Tipologia'] < $rs1['Tipologia']) {

for ($i=$rsdopo['Tipologia'];$i<$rs1['Tipologia'];$i++) echo '</li></ul>';

} else echo '</li>';

}}?>
</ul>
<script type="text/javascript">
	var menu=new menu.dd("menu");
	menu.init("menu","menuhover");
</script>
</td></tr></table></td></tr>

<tr><td valign="top">
<table border=0 width="900">
<tr><td height="35"></td></tr>
<tr><td height="594"><iframe height="594" width="900" src="guidaview.php" frameborder=0 border=0 id="list_page"></iframe></td></tr>
</table></td>
</tr></table> </center>
</body>

</html>
