<?
include('int/password.inc.php');
include('int/open2.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <title>Guida</title>
</head>
<?

$MySql = "SELECT * FROM Guida WHERE ID = '".$_REQUEST['ID']."'";
$Result = mysql_query($MySql);
$rs = mysql_fetch_array($Result);

?>
<body>

<table border=0 width="100%">
<tr><td height="10"></td></tr>
<tr><td height="60" valign="middle"><h2><?=$rs['Titolo']?></h2></td></tr>

<tr><td><div style="line-height:2;"><?=stripslashes($rs['Testo'])?></div></td></tr>
<?

if ($rs['Tipologia'] == '1') {

$MySql1 = "SELECT ID FROM Guida WHERE ID > '".$_REQUEST['ID']."' AND Tipologia = '1' LIMIT 0,1";
$Result1 = mysql_query($MySql1);
$rs1 = mysql_fetch_array($Result1);

$MySql2 = "SELECT * FROM Guida WHERE ID > '".$_REQUEST['ID']."'";
if (mysql_num_rows($Result1) != "") $MySql2 .= " AND ID < '".$rs1['ID']."'";
$MySql2 .= " AND Tipologia >= '2'";
$Result2 = mysql_query($MySql2);

while ($rs2 = mysql_fetch_array($Result2)) {
?>
<tr><td height="60" valign="middle"><font face="Georgia" size="3" color="#FFFFFF">&nbsp;&nbsp;<i><?=$rs2['Titolo']?></i></font></td></tr>
<tr><td><font face="Georgia" size="2" color="#FFFFFF"><div style="line-height:2;"><?=stripslashes($rs2['Testo'])?></div></font></td></tr>
<?
}}

?>
</table>

</body>

</html>
