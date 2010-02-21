<?
include('int/password.inc.php');
include('int/open2.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <title>Add page</title>
</head>

<style type="text/css">
.textbox{
width:700px;
height:300px;
border:1px solid #AAAAAA;
color:#AAAAAA;
font:11px georgia,serif;
letter-spacing: .1em;word-spacing:.2em;
filter:Alpha(opacity=0);
background-color:transparent;
}

.corpo {
border:1px solid #AAAAAA;
color:#AAAAAA;
font:11px georgia,serif;
letter-spacing: .1em;word-spacing:.2em;
background-color:transparent;
}
.selection {
border:1px solid #AAAAAA;
color:#AAAAAA;
font:11px georgia,serif;
letter-spacing: .1em;word-spacing:.2em;
background-color:#000000;
}
</style>

<script type="text/JavaScript">

function riavvio() {

    document.getElementById('taxman').action = "guidaadd.php?option=1";
    document.getElementById('taxman').submit();

}

</script>


<?


$_POST['messaggio'] = htmlentities($_POST['messaggio'], ENT_QUOTES);
$_POST['messaggio'] = str_replace("\n","<br>",$_POST['messaggio']);

$_POST['title'] = htmlentities($_POST['title'], ENT_QUOTES);

if ($_REQUEST['option'] == "register") {

    if ($_POST['position'] == "fine") {

        $MySqlInit = "SELECT * FROM Guida";
        $ResultInit = mysql_query($MySqlInit);
        $ID = mysql_num_rows($ResultInit) + 1;

        $MySql = "INSERT INTO Guida (Tipologia, Titolo, Testo, ID) VALUES ('".$_POST['priority']."', '".$_POST['title']."', '".$_POST['messaggio']."', '$ID')";
        mysql_query($MySql);

    } elseif ($_POST['position'] == "inizio") {

        mysql_query("UPDATE Guida SET ID = ID + 1");

        $MySql = "INSERT INTO Guida (Tipologia, Titolo, Testo, ID) VALUES ('".$_POST['priority']."', '".$_POST['title']."', '".$_POST['messaggio']."', '1')";
        mysql_query($MySql);

    } else {

        mysql_query("UPDATE Guida SET ID = ID + 1 WHERE ID > '".$_POST['position']."'");

        $MySql = "INSERT INTO Guida (Tipologia, Titolo, Testo, ID) VALUES ('".$_POST['priority']."', '".$_POST['title']."', '".$_POST['messaggio']."', '".($_POST['position']+1)."')";
        mysql_query($MySql);

    }

    $MySqlProva = "SELECT * FROM Guida WHERE Titolo = '".$_POST['title']."'";
    $Result = mysql_query($MySqlProva);
    if (mysql_num_rows($Result) != "") echo "<font face=Georgia size=2 color=#FFFFFF>Modifica effettuata correttamente</font>";
    else echo "<font face=Georgia size=2 color=#FFFFFF>Modifica non eseguita. Riprovare.</font>";






} elseif ($_REQUEST['option'] == "register2") {

    $MySql = "UPDATE Guida SET Titolo = '".$_POST['title']."', Testo = '".$_POST['messaggio']."' WHERE ID = '".$_POST['choose']."'";
    mysql_query($MySql);

    $MySqlProva = "SELECT * FROM Guida WHERE Titolo = '".$_POST['title']."' AND Testo = '".$_POST['messaggio']."'";
    $ResultProva = mysql_query($MySqlProva);
    if (mysql_num_rows($ResultProva) != "") echo "<font face=Georgia size=2 color=#FFFFFF>Modifica effettuata correttamente</font>";
    else echo "<font face=Georgia size=2 color=#FFFFFF>Modifica non eseguita. Riprovare.</font>";











} elseif ($_REQUEST['option'] == "register3") {

    $i = 1;

    while ($_POST['posiz'.$i] == $i) {

        $i++;

    }

    $rsi = mysql_fetch_array(mysql_query("SELECT Identifier FROM Guida WHERE ID = '$i'"));
    $posizione = $_POST['posiz'.$i];
    $titolo = $rsi['Identifier'];


    if ($i > $_POST['posiz'.$i]) {

        mysql_query("UPDATE Guida SET ID = ID + 1 WHERE ID >= '".$_POST['posiz'.$i]."' AND ID < '$i'");

    } else {

        mysql_query("UPDATE Guida SET ID = ID - 1 WHERE ID > '$i' AND ID <= '".$_POST['posiz'.$i]."'");

    }

    $MySqlUltimate = "UPDATE Guida SET ID = '$posizione' WHERE ID = '$i' AND Identifier = '$titolo'";
    echo $MySqlUltimate;
    mysql_query($MySqlUltimate);
    echo "<font face=Georgia size=2 color=#FFFFFF>Modifica eseguita.</font>";












} elseif ($_REQUEST['option'] == "register4") {

    $MySql = "DELETE FROM Guida WHERE ID = '".$_POST['selsel']."'";
    mysql_query($MySql);

    $MySql2 = "UPDATE Guida SET ID = ID - 1 WHERE ID > '".$_POST['selsel']."'";
    mysql_query($MySql2);
    echo "<font face=Georgia size=2 color=#FFFFFF>Eliminazione eseguita.</font>";

} else {

?>
<body>
<?
if ($_REQUEST['option'] == '1') {
?>
<form method="post" action="guidaadd.php?option=register" id="taxman">
<table border=0 width="100%">
<tr><td><font face="Georgia" size="1" color=#FFFFFF>In questa sezione &egrave; possibile creare un post. Inserire il titolo, il testo, se si vuole farlo apparire come un titoletto della Guida oppure come un semplice post. Nella sezione "posizione" indicare DOPO QUALE POST mettere l'aggiunta. Se ad esempio si vuole mettere il post dopo "HINT: che lingua parlo?", bisogna cliccare su "HINT: che lingua parlo?", altrimenti, se il post lo si vuole mettere prima, bisogner&agrave; cliccare sul post precedente.<br><br></font></td></tr>
<tr><td><font face="Georgia" size="2" color="#FFFFFF">Inserire il titolo della sezione:</font></td></tr>
<tr><td><input type="text" name="title" class="corpo" value="<?=$_POST['title']?>"></td></tr>
<tr><td><font face="Georgia" size="2" color="#FFFFFF">Posizione: <select class="selection" name="position" onChange="riavvio();">
<option value="inizio" <?
if ($_POST['position'] == "inizio") echo "SELECTED";
?>>All'inizio della lista</option>
<?
$MySqlId = "SELECT * FROM Guida ORDER BY ID";
$ResultId = mysql_query($MySqlId);
while ($rsId = mysql_fetch_array($ResultId)) {
?>
<option value="<?=$rsId['ID']?>" <?
if ($_POST['position'] == $rsId['ID']) echo "SELECTED";
?>><?
if ($rsId['Tipologia'] >= 2) {
    for ($i=2;$i<=$rsId['Tipologia'];$i++) echo "&nbsp;&nbsp;";
}
echo html_entity_decode($rsId['Titolo'],ENT_QUOTES);
?></option>
<?}?>
<option value="fine" <?
if (($_POST['position'] == "") || ($_POST['position'] == "fine")) echo "SELECTED";
?>>Alla fine della lista</option>
</select></font></td></tr>
<tr><td><font face="Georgia" size="2" color="#FFFFFF">Inserire la spiegazione:</font></td></tr>
<tr><td><font face="Georgia" size="2" color="#FFFFFF">A capo automatico? </font> - <input type="checkbox" name="acapo" value="yes"></td></tr>
<tr><td><textarea name="messaggio" class="textbox"><?=$_POST['messaggio']?></textarea></td></tr>
<?
if ($_POST['position'] == "") {
    $rsMax = mysql_fetch_array(mysql_query("SELECT MAX(ID) FROM Guida"));
    $maximum = $rsMax['MAX(ID)'];
} else {
    $maximum = $_POST['position'];
}
$rsType = mysql_fetch_array(mysql_query("SELECT Tipologia, Titolo FROM Guida WHERE ID = '$maximum'"));
$rsType2 = mysql_fetch_array(mysql_query("SELECT Tipologia, Titolo FROM Guida WHERE ID = '".($maximum+1)."'"));
$rsType3 = mysql_fetch_array(mysql_query("SELECT Tipologia, Titolo FROM Guida WHERE ID = '".($maximum-1)."'"));
?>
<tr><td><select name="priority" class="selection"><?
if ($rsType['Tipologia'] == '1') {
?><option value="1">Sezione (es. GREMIOS) a destra di <?=$rsType['Titolo']?></option>
<option value="2">Sottomenu (sotto <?=$rsType['Titolo']?>, subito prima di <?=$rsType2['Titolo']?></option>
<?} else {

for($k=1;$k<$rsType['Tipologia'];$k++) {

$rsControllo = mysql_fetch_array(mysql_query("SELECT MAX(ID) FROM Guida WHERE Tipologia = '$k' AND ID < '$maximum'"));
$rsControllo2 = mysql_fetch_array(mysql_query("SELECT Titolo FROM Guida WHERE ID = '".$rsControllo['MAX(ID)']."'"));

if ($k == '1') {?>
<option value="<?=($rsType['Tipologia']-1)?>">Sezione (a destra di <?=$rsControllo2['Titolo']?>)</option>
<?} else {
?>
<option value="<?=($rsType['Tipologia']-1)?>">Sovramenu (sotto <?=$rsControllo2['Titolo']?>)</option>
<?}
}?>
<option value="<?=$rsType['Tipologia']?>">Sottomenu (sotto <?=$rsType['Titolo']?>)</option>
<option value="<?=($rsType['Tipologia']+1)?>">Submenu (crea un nuovo menu da <?=$rsType['Titolo']?>)</option>
<?}?>
</select></td></tr>
<tr><td><input type="submit" name="invia" class="selection" value="Invia la modifica"></td></tr>
</table></form>




<?
} elseif ($_REQUEST['option'] == '2') {
if ($_POST['choose'] == "") {
?>
<form method="post" action="guidaadd.php?option=2">
<table border=0 width="100%">
<tr><td><font face="Georgia" size="1" color=#FFFFFF>Indicare il titolo del post che si vuole modificare.<br><br></font></td></tr>
<tr><td><font face="Georgia" size="2" color="#FFFFFF">Posizione: <select class="selection" name="choose">
<?
$MySqlId = "SELECT * FROM Guida ORDER BY ID";
$ResultId = mysql_query($MySqlId);
while ($rsId = mysql_fetch_array($ResultId)) {
?>
<option value="<?=$rsId['ID']?>"><?
if ($rsId['Tipologia'] >= 2) {
    for ($i=2;$i<=$rsId['Tipologia'];$i++) echo "&nbsp;&nbsp;";
}
echo $rsId['Titolo'];
?></option>
<?}?>
</select></font></td></tr>
<tr><td><input type="submit" name="inviamod" class="selection" value="Modifica"></td></tr>
</table></form>
<?
} else {

$MySqlSel = "SELECT * FROM Guida WHERE ID = '".$_POST['choose']."'";
$ResultSel = mysql_query($MySqlSel);
$rsSel = mysql_fetch_array($ResultSel);

?>
<form method="post" action="guidaadd.php?option=register2">
<input type="hidden" name="choose" value="<?=$_POST['choose']?>">
<table border=0 width="100%">
<tr><td><font face="Georgia" size="1" color=#FFFFFF>In questa sezione &egrave; possibile modificare un post. Inserire il titolo, il testo, se si vuole farlo apparire come un titoletto della Guida oppure come un semplice post.<br><br></font></td></tr>
<tr><td><font face="Georgia" size="2" color="#FFFFFF">Inserire il titolo della sezione:</font></td></tr>
<tr><td><input type="text" name="title" class="corpo" value="<?=$rsSel['Titolo']?>"></td></tr>
<tr><td><font face="Georgia" size="2" color="#FFFFFF">Inserire la spiegazione:</font></td></tr>
<tr><td><font face="Georgia" size="2" color="#FFFFFF">A capo automatico? </font> - <input type="checkbox" name="acapo" value="yes"></td></tr>
<tr><td><textarea name="messaggio" class="textbox"><?

$rsSel['Testo'] = str_replace("\n","<br>",$rsSel['Testo']);
echo html_entity_decode($rsSel['Testo'],ENT_QUOTES);

?></textarea></td></tr>
<?
$maximum = $_POST['choose'];
$rsType = mysql_fetch_array(mysql_query("SELECT Tipologia, Titolo FROM Guida WHERE ID = '$maximum'"));
$rsMinNum = mysql_fetch_array(mysql_query("SELECT Tipologia, Titolo FROM Guida WHERE ID = '".($maximum+1)."'"));
$rsMaxNum = mysql_fetch_array(mysql_query("SELECT Tipologia, Titolo FROM Guida WHERE ID = '".($maximum-1)."'"));

if ($rsMaxNum['Tipologia'] >= $rsMinNum['Tipologia']) {
?>
<tr><td><input type="submit" name="invia" class="selection" value="Invia la modifica"></td></tr>
</table></form>
<?
}}






} elseif ($_REQUEST['option'] == '3') {

$MySqlList = "SELECT * FROM Guida ORDER BY ID";
$ResultList = mysql_query($MySqlList);
?>
<form method="post" action="guidaadd.php?option=register3">
<table border=0 width="100%">
<tr><td><font face="Georgia" size="1" color=#FFFFFF>In questa sezione sar&agrave; possibile modificare la posizione di un post. Sar&agrave; possibile modificare la posizione di UN SOLO post alla volta, in presenza di pi&ugrave; discordanze, sar&agrave; considerata la prima in ordine di comparizione.<br>
Per modificare la posizione di un post, bisogner&agrave; cambiare il numero di posizione nella casella di testo corrispondente al post stesso, indicando in quale posizione lo si vuole spostare. Se ad esempio si vuole spostare il post 72 e metterlo al posto del post 18, nella casella di testo corrispondente si dovr&agrave; inserire appunto "18" al posto di "72". In questo modo, il 72 sar&agrave; il 18, e il 18 sar&agrave; il 19 e via dicendo fino al 71, che sar&agrave; il 72.<br><br></font></td></tr>
<tr><td colspan="2" height="20"><font face="Georgia" size="2" color="#FFFFFF">Sezioni:</font></td></tr>
<?
while ($rsList = mysql_fetch_array($ResultList)) {
?>
<tr><td><font face="Georgia" size="1" color="#FFFFFF"><?
if ($rsList['Tipologia'] == '1') echo "<b>";
else echo "&nbsp;&nbsp;";
echo $rsList['Titolo'];
?></td><td><input type="text" class="corpo" name="posiz<?=$rsList['ID']?>" value="<?=$rsList['ID']?>"></td></tr>
<?
}
?>
<tr><td colspan="2" height="20" valign="bottom"><input type="submit" name="invia" class="selection" value="aggiorna"></td></tr>
</table></form>





<?
} elseif ($_REQUEST['option'] == '4') {
$MySqlList = "SELECT * FROM Guida ORDER BY ID";
$ResultList = mysql_query($MySqlList);
?>
<form method="post" action="guidaadd.php?option=register4">
<table border=0 width="100%">
<tr><td><font face="Georgia" size="1" color=#FFFFFF>In questa sezione &egrave; possibile eliminare un post. Selezionare il post da eliminare e confermare.<br><br></font></td></tr>
<tr><td colspan="2" height="20"><font face="Georgia" size="2" color="#FFFFFF">Sezioni:</font></td></tr>
<?
while ($rsList = mysql_fetch_array($ResultList)) {
?>
<tr><td width="250"><font face="Georgia" size="1" color="#FFFFFF"><?
if ($rsList['Tipologia'] == '1') echo "<b>";
else echo "&nbsp;&nbsp;";
echo $rsList['Titolo'];
?></td><td align="left"><input type="radio" class="selection" name="selsel" value="<?=$rsList['ID']?>"></td></tr>
<?
}
?>
<tr><td colspan="2" height="20" valign="bottom"><input type="submit" name="invia" class="selection" value="cancella"></td></tr>
</table></form>
<?
}}
?>
</body>

</html>
