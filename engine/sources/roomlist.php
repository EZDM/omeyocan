<?PHP
/*

    This file is part of X7 chat Version 2.0.5 - RPG enhanced.
    Released March 2008. Copyright (c) 2008 by Niccolo' Cascarano.

    X7 chat Version 2.0.5 - RPG enhanced is free software:
     you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    X7 chat Version 2.0.5 - RPG enhanced is distributed 
    in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with X7 chat Version 2.0.5 - RPG enhanced.  
    If not, see <http://www.gnu.org/licenses/>


*/

///////////////////////////////////////////////////////////////
//
//		X7 Chat Version 2.0.4.3
//		Released August 28, 2006
//		Copyright (c) 2004-2006 By the X7 Group
//		Website: http://www.x7chat.com
//
//		This program is free software.  You may
//		modify and/or redistribute it under the
//		terms of the included license as written
//		and published by the X7 Group.
//
//		By using this software you agree to the
//		terms and conditions set forth in the
//		enclosed file "license.txt".  If you did
//		not recieve the file "license.txt" please
//		visit our website and obtain an official
//		copy of X7 Chat.
//
//		Removing this copyright and/or any other
//		X7 Group or X7 Chat copyright from any
//		of the files included in this distribution
//		is forbidden and doing so will terminate
//		your right to use this software.
//
////////////////////////////////////////////////////////////////EOH
?><?PHP

	// Handles the room list page
	function room_list_page(){
		global $print, $prefix, $txt, $x7c, $x7s, $db;
		
		include_once('./lib/online.php');
		
		$db->DoQuery("UPDATE {$prefix}users SET position='Mappa' WHERE username='$x7s->username'");
		
		$time = time();
		$query = $db->DoQuery("SELECT count(*) AS num FROM {$prefix}online WHERE name='$x7s->username'");
		$row = $db->Do_Fetch_Assoc($query);
		if($row['num']!=0){
			$db->DoQuery("UPDATE {$prefix}online SET time='$time', room='Mappa' WHERE name='$x7s->username'");
		}
		else{
			$ip = $_SERVER['REMOTE_ADDR'];
			$db->DoQuery("INSERT INTO {$prefix}online VALUES('0','$x7s->username','$ip','Mappa','','$time','{$x7c->settings['auto_inv']}')");
		}
		
		clean_old_data();
		
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
		echo "<html dir=\"$print->direction\"><head><title>{$x7c->settings['site_name']} -- Mappa</title>";
		echo $print->style_sheet;
		echo $print->ss_mini;
		echo $print->ss_chatinput;
		echo $print->ss_uc;

                echo '
		<style type="text/css">
                  #secret{
                      background-color: transparent;
                      position: absolute;
                  }

		</style>
		';
		


?>
 </head><body onload="javascript: do_initial_refresh();"> <!--openActionBox();">-->
 <div id="container">
 <div id="divmap">

<?PHP 
//This file include common layout for frame and map
	include('./sources/layout.html');

	if(isset($_GET['errore'])){
		$errore='';
		switch($_GET['errore']) {
			case "nokey":
				$errore = "Non hai la chiave per entrare in questa stanza";
				break;
			case "noroom":
				$errore = "La stanza non esiste";
				break;
		}

		echo '<div id="errore" class="errore_popup">'.$errore.'
				<br><br><input name="ok" type="button" class="button" value="OK" onClick="javascript: document.getElementById(\'errore\').style.visibility=\'hidden\';">
				</div>';
	}

?>
  
  <!-- IMMAGINE DELLA POLAROID (a seconda della stanza) -->
  <img style="position:absolute; top:0px; left:807px;" src="./graphic/polaroid.jpg">
  <div id="position"> </div>
  
  <!-- Pulsanti mappa -->
	<a href="javascript: hndl = window.open('sources/sub_chiesa.html','sub_location','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><img onMouseDown="this.src='./graphic/pulsante_down.gif'" onMouseOut="HidePopup(this);" onMouseOver="ShowPopup(this,'Chiesa');" style="position:absolute; top:352px; left:206px;" src="./graphic/pulsante.gif"></a>
  
	<A href="index.php?act=frame&room=Cimitero"> <img onMouseDown="this.src='./graphic/pulsante_down.gif'" onMouseOut="HidePopup(this);" onMouseOver="ShowPopup(this,'Cimitero');" style="position:absolute; top:520px; left:655px;" src="./graphic/pulsante.gif"></A>

	<A href="index.php?act=frame&room=Piazza"> <img onMouseDown="this.src='./graphic/pulsante_down.gif'" onMouseOut="HidePopup(this);" onMouseOver="ShowPopup(this,'Piazza');" style="position:absolute; top:550px; left:380px;" src="./graphic/pulsante.gif"></A>

	<A href="index.php?act=frame&room=Licoreria"> <img onMouseDown="this.src='./graphic/pulsante_down.gif'" onMouseOut="HidePopup(this);" onMouseOver="ShowPopup(this,'Licoreria');" style="position:absolute; top:555px; left:360px;" src="./graphic/pulsante.gif"></A>

	<A href="index.php?act=frame&room=Auto"> <img onMouseDown="this.src='./graphic/pulsante_down.gif'" onMouseOut="HidePopup(this);" onMouseOver="ShowPopup(this,'Cimitero delle auto');" style="position:absolute; top:635px; left:490px;" src="./graphic/pulsante.gif"></A>

	<A href="index.php?act=frame&room=Teatro"> <img onMouseDown="this.src='./graphic/pulsante_down.gif'" onMouseOut="HidePopup(this);" onMouseOver="ShowPopup(this,'Teatro');" style="position:absolute; top:496px; left:380px;" src="./graphic/pulsante.gif"></A>


	
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=1011','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 175; left: 205;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=10737','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 234; left: 209;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=1085','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 221; left: 239;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=10869','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 146; left: 202;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=11804','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 143; left: 235;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=1243','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 236; left: 238;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=12946','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 206; left: 240;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=13215','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 173; left: 220;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=13568','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 198; left: 253;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=14034','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 192; left: 210;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=14345','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 165; left: 248;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=14525','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 234; left: 207;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=14935','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 144; left: 247;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=15690','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 219; left: 232;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=16129','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 233; left: 215;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=16680','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 142; left: 243;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=17193','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 214; left: 230;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=17631','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 217; left: 245;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=18055','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 164; left: 222;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=1826','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 200; left: 251;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=1883','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 242; left: 213;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=19560','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 232; left: 219;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=19688','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 211; left: 241;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=1968','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 239; left: 234;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=19805','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 221; left: 214;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=20089','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 170; left: 226;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=20182','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 230; left: 204;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=20264','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 239; left: 237;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=20444','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 146; left: 236;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=20493','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 195; left: 217;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=20664','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 158; left: 215;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=2069','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 158; left: 212;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=2086','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 243; left: 221;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=21106','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 224; left: 252;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=21286','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 140; left: 214;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=21393','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 204; left: 220;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=21680','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 171; left: 229;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=21768','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 167; left: 235;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=22082','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 152; left: 206;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=22258','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 154; left: 214;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=22371','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 140; left: 233;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=23167','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 229; left: 223;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=23430','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 236; left: 220;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=23502','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 229; left: 214;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=23511','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 184; left: 244;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=24088','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 220; left: 216;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=24456','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 143; left: 237;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=25571','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 210; left: 211;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=2621','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 160; left: 230;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=26366','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 179; left: 219;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=2637','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 144; left: 235;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=27350','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 151; left: 216;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=27402','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 223; left: 249;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=27450','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 156; left: 234;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=28008','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 200; left: 230;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=28178','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 207; left: 245;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=2831','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 177; left: 202;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=28324','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 142; left: 203;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=2905','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 218; left: 216;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=29316','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 192; left: 243;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=29391','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 202; left: 254;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=29965','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 179; left: 250;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=30541','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 154; left: 252;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=30754','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 173; left: 230;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=30855','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 227; left: 225;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=31260','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 182; left: 234;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=31325','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 161; left: 210;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=31346','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 177; left: 247;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=32029','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 215; left: 241;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=32135','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 140; left: 221;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=32219','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 236; left: 221;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=32641','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 188; left: 245;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=3292','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 241; left: 230;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=3368','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 153; left: 206;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=4067','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 219; left: 203;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=4128','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 178; left: 237;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=4267','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 225; left: 204;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=4309','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 141; left: 210;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=4443','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 172; left: 226;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=4581','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 150; left: 219;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=4638','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 233; left: 221;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=4678','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 145; left: 222;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=5006','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 206; left: 243;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=509','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 217; left: 231;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=5304','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 205; left: 211;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=5306','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 147; left: 200;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=5504','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 181; left: 210;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=5798','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 151; left: 237;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=5940','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 202; left: 233;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=6069','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 185; left: 200;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=6254','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 143; left: 231;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=6624','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 147; left: 225;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=6743','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 226; left: 227;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=6924','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 206; left: 217;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=6971','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 206; left: 229;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=7275','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 238; left: 232;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=7431','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 169; left: 243;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=7678','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 218; left: 247;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=8601','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 152; left: 207;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=8741','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 241; left: 228;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=9878','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 177; left: 254;"></div></a>
<a href="javascript: hndl = window.open('index.php?act=secret&secret=Giardino%20dei%20suicidi&code=9995','secret','width=600,height=440, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();"><div id="secret" style="width: 3px; height: 3px; top: 170; left: 250;"></div></a>





<script language="javascript" type="text/javascript">
						listhash = '';
						startfrom = 0;
						newMail = 0;
						
						function ShowPopup(hoveritem, locat)
						{
							hp = document.getElementById("position");
		
							// Set popup to visible
							hp.style.top = hoveritem.offsetTop + 18;
							hp.style.left = hoveritem.offsetLeft + 20;
							hp.innerHTML = locat;

							hp.style.visibility = "Visible";
							hoveritem.src='./graphic/pulsante_over.gif';
							
						}

						function HidePopup(hoveritem)
						{
							hp = document.getElementById("position");
							hp.style.visibility = "Hidden";	
							hoveritem.src='./graphic/pulsante.gif';
						}

						function do_initial_refresh(){
							// Create object
							if(window.self.name == ''){
								hndl = window.open('/engine','main','width=1024,height=723, toolbar=no, status=no, location=no, menubar=no, resizable=yes, status=no'); hndl.focus();
								window.self.location.href='/courtesy.html';
							}
							if(window.self.name == 'sheet'){
								window.self.close();
							}
							
							mapRefresh = setInterval('do_refresh()','<?PHP echo $x7c->settings['refresh_rate']; ?>');
							do_refresh();
							
							
						}

						function requestReady_channel1(){
							if(httpReq2){
								if(httpReq2.readyState == 4){
									if(httpReq2.status == 200){

										playSound = 0;
										modification=0;
										
										
										//document.getElementById('debug').innerHTML += httpReq2.responseText;
										

										var dataArray = httpReq2.responseText.split("|");
										for(x = 0;x < dataArray.length;x++){
											var dataSubArray = dataArray[x].split(";");
											if(dataSubArray[0] == '2'){
												// Operators for userlist
												

												var dataSubArray2 = dataSubArray[1].split(",");
												for(x2 = 0;x2 < dataSubArray2.length;x2++){
													if(dataSubArray2[x2] != ''){
														dataSubArray2[x2] = restoreText(dataSubArray2[x2]);
													}
												}

												playSound = 2;

											}else if(dataSubArray[0] == '3'){
												// Users for userlist

												var dataSubArray2 = dataSubArray[1].split(",");
												for(x2 = 0;x2 < dataSubArray2.length;x2++){
													if(dataSubArray2[x2] != ''){
														dataSubArray2[x2] = restoreText(dataSubArray2[x2]);
													}
												}


											}else if(dataSubArray[0] == '4'){
												// Listhash update
												listhash = dataSubArray[1];
											}else if(dataSubArray[0] == '5'){
												// Endon update
												startfrom = dataSubArray[1];
											}else if(dataSubArray[0] == '6'){
												// Number of offline messages update
												if(dataSubArray[1] > 0) {
													document.getElementById('posta').src = "./graphic/05postasi.jpg";
													
													if(!newMail){
														var tardis = document.getElementById('tardis');
														tardis.Play();
													}
													
													newMail = 1;
												}
												else {
													document.getElementById('posta').src = "./graphic/05postano.jpg";
													newMail = 0;
												}
													
											}else if(dataSubArray[0] == '9'){
												// Redirect w/ error msg
												dataSubArray[1] = restoreText(dataSubArray[1]);
												if(dataSubArray[1] != '')
													alert(dataSubArray[1]);
												document.location = dataSubArray[2];
											}else if(dataSubArray[0] == '11'){
												//Panic update
												panic_value = parseInt(dataSubArray[1]);
												document.chatIn.panic.value=panic_value;
											}else if(dataSubArray[0] == '12'){
												//Panic update
												valore = parseInt(dataSubArray[1]);
												var messaggio;
												if(valore)
													messaggio="Arriva l'oscurità";
												else
													messaggio="L'oscurità se ne va";
												
												alert(messaggio);
												window.location.href = window.location.href;
											}else if(dataSubArray[0] == '13'){
												//Delete message
												document.getElementById('message_window').innerHTML ='';
												startfrom = 0;
												do_refresh();
											}
										


										}

									}
								}
							}
						}

						function restoreText(torestore){
							torestore = torestore.replace(/74ce61f75c75b155ea7280778d6e8183/g,"@");
							torestore = torestore.replace(/74ce61f75c75b155ea7280778d6e8181/g,"|");
							torestore = torestore.replace(/74ce61f75c75b155ea7280778d6e8182/g,";");
							torestore = torestore.replace(/74ce61f75c75b155ea7280778d6e8180/g,",");
							return torestore;
						}

						function do_refresh(){
							jd=new Date();
							nocache = jd.getTime();
							url = './index.php?act=frame&frame=update&room=Mappa&listhash=' + listhash + '&startfrom=' + startfrom + '&nc=' + nocache;							if(window.XMLHttpRequest){
								try {
									httpReq2 = new XMLHttpRequest();
								} catch(e) {
									httpReq2 = false;
								}
							}else if(window.ActiveXObject){
								try{
									httpReq2 = new ActiveXObject("Msxml2.XMLHTTP");
								}catch(e){
									try{
										httpReq2 = new ActiveXObject("Microsoft.XMLHTTP");
									}catch(e){
										httpReq2 = false;
									}
								}
							}
							httpReq2.onreadystatechange = requestReady_channel1;
							httpReq2.open("GET", url, true);
							httpReq2.send("");
						}
						
						

					</script>
					


</body>
</html>
		
<?PHP	}



?>
