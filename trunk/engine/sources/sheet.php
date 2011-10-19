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
?>
<?PHP

function sheet_main(){
	global $x7s, $db, $x7c, $prefix;

	$page='';

	$body='<div style="color: white;">Not ready yet</div>';
	if(isset($_GET['page'])){
		$page=$_GET['page'];
	}
	else{
		$page="main";
		$_GET['page']="main";
	}

	if(!isset($_GET['pg'])){
		$_GET['pg']=$x7s->username;
	}

	if($page=="main"){
		$body = sheet_page_main();
	}
	else if($page=="ability"){
		$body = sheet_page_ability();
	}
	else if($page=="background"){
		$body = sheet_page_background();
	}
	else if($page=="master"){
		$body = sheet_page_master();
	}
	else if($page=="equip"){
		$body = sheet_page_equip();
	}
	else if($page=="corp"){
		$body = sheet_page_corp();
	}

	if ($body != "")
	  print_sheet($body,$page);
}


function sheet_page_equip(){
	global $db,$x7c,$prefix,$x7s,$print;
	$pg=$_GET['pg'];
	$body='';
	$errore='';
	include_once('./lib/sheet_lib.php');
	include_once('./lib/shop_lib.php');
	global $money_name;


	if(isset($_GET['moduse']) && checkIfMaster()){
		if(!isset($_POST['use']) || !isset($_POST['id'])){
			die("Bad form 3");
		}
			
		$db->DoQuery("UPDATE {$prefix}objects SET uses='$_POST[use]'
				WHERE id='$_POST[id]'");
		include_once('./lib/alarms.php');
		object_uses($pg,$_POST['id'],$_POST['use']);
			
			
	}

	if(isset($_GET['delete']) && ($x7s->username==$pg || checkIfMaster())){
		$db->DoQuery("DELETE FROM {$prefix}objects WHERE id='$_GET[delete]'");
	}

	if(isset($_GET['equiptgl']) && ($x7s->username==$pg || checkIfMaster())){
		$query = $db->DoQuery("SELECT equipped,name,size 
				FROM {$prefix}objects WHERE id='$_GET[equiptgl]'");
		$row = $db->Do_Fetch_Assoc($query);
		if(!$row)
			$errore = "Oggetto non esistente";
		else{
			$equip_value = 0;
			$azione="";
			$action_ok=true;

			if(!$row['equipped']){
				$equip_value = 1;
				$azione="equipaggiato";

				if(get_user_space($pg) - $row['size'] < 0){
					$errore="Spazio insufficiente per equipaggiare l'oggetto";
					$action_ok=false;
				}
			} 
			else {
				$equip_value = 0;
				$azione = "depositato";
				if(get_user_space($pg) + $row['size'] < 0){
					$errore="Impossibile depositare l'oggetto";
					$action_ok = false;
				}
			}

			if($action_ok){
				$db->DoQuery("UPDATE {$prefix}objects 
						SET equipped='$equip_value' WHERE id='{$_GET['equiptgl']}'");

				include('./lib/alarms.php');
				toggle_equip_log($row['name'], $azione);

				$query = $db->DoQuery("SELECT position		
						FROM {$prefix}users WHERE username='$pg'");		
				$row_msg=$db->Do_Fetch_Assoc($query);		

				if($row_msg && $row_msg['position']!="Mappa" && $row_msg['position']!=""){
					include_once("./lib/message.php");
					$txt="L\'utente $pg ha $azione l\'oggetto $row[name]";
					alert_room($row_msg['position'], $txt);
				}

				header("location: index.php?act=sheet&page=equip&pg=$pg&refreshparent");
			}
		}
	}

	if(isset($_GET['pay']) && ($x7s->username==$pg || checkIfMaster())){
		global $shopper;
		if(!isset($_POST['owner']) || !isset($_POST['amount'])
				|| !$_POST['owner'] || !$_POST['amount']) {
			$errore = "Non hai specificato il destinatario o l'ammontare";
		} 
		else {
			$query = $db->DoQuery("SELECT count(*) AS cnt
					FROM {$prefix}users WHERE username='$_POST[owner]'");
			$row = $db->Do_Fetch_Assoc($query);

			if((!$row || $row['cnt']==0) && $_POST['owner'] != $shopper){
				$errore = "Utente non esistente";
			}
			else {
				$errore = pay($_POST['amount'], $pg, $_POST['owner']);
			}
		}
	}

	if(isset($_GET['split']) && ($x7s->username==$pg || checkIfMaster())){
		if(!isset($_POST['amount']) || !isset($_POST['group']))
			die("Bad form 2");

		$errore = split_money($_POST['amount'], $pg, $_POST['group']);
	}

	if(isset($_GET['group_money']) && ($x7s->username==$pg || checkIfMaster())){
		group_money($pg);
	}

	if(isset($_GET['assign']) && ($x7s->username==$pg || checkIfMaster())){
		if(!isset($_POST['owner']) || !isset($_POST['id'])){
			die("Bad form owner: $_POST[owner] id: $_POST[id]");
		}
		$query = $db->DoQuery("SELECT count(*) AS cnt
				FROM {$prefix}users WHERE username='$_POST[owner]'");
		$row = $db->Do_Fetch_Assoc($query);

		if(!$row || $row['cnt']==0){
			$errore = "Utente non esistente";
		}

		$query = $db->DoQuery("SELECT * FROM {$prefix}objects 
				WHERE id='$_POST[id]' AND owner='$pg'");
		$row = $db->Do_Fetch_Assoc($query);

		if(!$row || $row['id']==''){
			$errore = "Oggetto non esistente";
		}
		if(!$row['equipped']){
			$errore = "Non puoi consegnare un oggetto che non trasporti";
		}

		$query = $db->DoQuery("SELECT username
				FROM {$prefix}users WHERE username='$_POST[owner]'");
		$row_user=$db->Do_Fetch_Assoc($query);

		if(!$row_user) {
			$errore = "Utente non esistente";
		}
		else {
			if(get_user_space($_POST['owner']) - $row['size'] < 0){
				$errore = "Il destinatario non puo' equipaggiare l'oggetto";
			}
			else if (get_user_space($pg) + $row['size'] < 0){
				$errore = "Non puoi disequipaggiare l'oggetto";
			}
		}

		$obj=$row['name'];
		if($errore==''){
			//keys duplicates, and does not disappera from my sheet
			if(preg_match("/^masterkey/", $row['name'])){
				list($pre, $name)=split("masterkey_", $row['name']);
				$obj="key_$name";

				if(!isset($_POST['grants']) ||
						$_POST['grants'] <= 0 || $_POST['grants']== '') {
					$_POST['grants'] = -1;
				}
				
					
				$db->DoQuery("INSERT INTO {$prefix}objects
							(name, description, owner, uses, image_url, equipped,
							 expire_span, visible_uses)
							VALUES (
								'$obj',
								'$row[description]',
								'$_POST[owner]',
								'$_POST[grants]',
								'$row[image_url]','1',
								'$row[expire_span]', '$row[visible_uses]')
						");

				$new_id = mysql_insert_id();

				// If master key expires, also the client key must expire at the same moment
				if ($row['expire_span'] > 0) {
					$expire_query = $db->DoQuery("SELECT expire_time FROM {$prefix}temp_obj 
							WHERE id = '$row[id]'");
					$row_expire = $db->Do_Fetch_Assoc($expire_query);
					if ($row_expire) {
						$db->DoQuery("INSERT INTO {$prefix}temp_obj
								(id, expire_time) VALUES
								('$new_id', '$row_expire[expire_time]')");
					}
				}
			}
			else{
				$db->DoQuery("UPDATE {$prefix}objects
							SET owner='$_POST[owner]'
							WHERE id='$_POST[id]' AND owner<>''");
				//The last is only for protection to pattern objects
					
			}

			$errore="Oggetto assegnato correttamente\n";
			include_once('./lib/alarms.php');
			object_moves($_POST['owner'],$pg,$obj);
		}

	}

	$body .= "<script language=\"javascript\" type=\"text/javascript\">
				function confirmDrop(id){
					if(confirm(\"Vuoi davvero buttare l'oggetto?\")){
						location.href='index.php?act=sheet&page=equip&pg=$pg&delete='+id;
					}
					
				}

			function requestReady_channel1(){
				if(httpReq2){
					if(httpReq2.readyState == 4){
						if(httpReq2.status == 200){
							var dataArray = httpReq2.responseText;
							document.getElementById('objects').innerHTML = dataArray;
						}
					}
				}
			}

			function do_refresh(search){
				jd=new Date();
				nocache = jd.getTime();
				text = search.value;
				url = './index.php?act=sheet&page=equip&search=' + text + '&nc=' + nocache;
				if(window.XMLHttpRequest){
					try {
						httpReq2 = new XMLHttpRequest();
					} catch(e) {
						httpReq2 = false;
					}
				}else if(window.ActiveXObject){
					try{
						httpReq2 = new ActiveXObject('Msxml2.XMLHTTP');
					}catch(e){
						try{
							httpReq2 = new ActiveXObject('Microsoft.XMLHTTP');
						}catch(e){
							httpReq2 = false;
						}
					}
				}
				httpReq2.onreadystatechange = requestReady_channel1;
				httpReq2.open('GET', url, true);
				httpReq2.send('');
			}
			</script>";


	$body.="<div id=\"search_box\">
	<input type=\"text\" size=20 onkeyup=\"javascript:do_refresh(this)\"
	onclick=\"this.value=''\" 
	onblur=\"this.value=!this.value?'cerca oggetto':this.value;\"
	value=\"cerca oggetto\">
	</div>\n";
	$body.="<div id=\"objects\">\n";
  $obj_div = "";

	$refine_query = "";
	if (isset($_GET['search']) && $_GET['search']) {
		$refine_query = " AND name like '%{$_GET['search']}%'";
	}
	$query = $db->DoQuery("SELECT * FROM {$prefix}objects
			WHERE owner='$pg' $refine_query ORDER BY equipped DESC, name");

	$room='';

	while($row=$db->Do_Fetch_Assoc($query)){

		if(($pg!=$x7s->username && $row['equipped']) ||
				($pg==$x7s->username) || checkIfMaster()){
			$more_form='';
			$obj_name = $row['name'];
			$description = $row['description'];
			$dimensione="";
			$disabled="";
			if(!$row['equipped'])
				$disabled="style=\"color: #aeaeae;\"";

			switch($row['size']) {
				case 0:
					$dimensione="Minuscolo";
					break;
				case 1:
					$dimensione="Piccolo";
					break;
				case 2:
					$dimensione="Medio";
					break;
				case 5:
					$dimensione="Grande";
					break;
				case -1:
					$dimensione="Capienza piccola";
					break;
				case -2:
					$dimensione="Capienza media";
					break;
				case -5:
					$dimensione="Capienza grande";
					break;
			}

			if($row['uses']==0){
				$obj_name .= " [inutilizzabile]";
			}

			if(preg_match("/^key_/", $row['name']) ||
					preg_match("/^masterkey_/", $row['name'])) {
				$master_key=0;
				$master_string='';
				if(preg_match("/^key_/", $row['name']))
				list($pre, $name)=split("key_", $row['name']);
				elseif(preg_match("/^masterkey_/", $row['name'])){
					list($pre, $name)=split("masterkey_", $row['name']);
					$master_key=1;
					$master_string = " (chiave master)";
				}
				 
				if(strcasecmp($_GET['pg'], $x7s->username) == 0 || checkIfMaster()){
					//we make clickable only key of my sheet
					if($master_key){
						//This a master key
						$more_form = ' <tr>
							<td>Usi concessi (vuoto per illimitati):</td>
							<td><input type="text" name="grants" class="text_input" size=2>
							</td>
              </tr>';
					}

					$obj_name = '<a onClick="javascript: hdl=window.open(\'\',\'main\'); hdl.location.href=\'index.php?act=frame&room='.$name.'&key_used='.$row['id'].'\'; window.location.reload(); hdl.focus(); "> Stanza di '.$name.$master_string.'</a>';

				}
				else{
					$obj_name = "Stanza di $name";
				}
			}

			if($row['name'] == $money_name)	{
					$obj_name .= ": $row[uses]";
			}

			$visible_uses = '';
			if($row['visible_uses']) {
				if($row['uses'] >= 0)
					$visible_uses = "<br>Usi rimasti: $row[uses]";
				else
					$visible_uses = "<br>Usi rimasti: illimitati";
			}

			$expire_string = '';
			if ($row['expire_span'] > 0) {
				$query_expire = $db->DoQuery("SELECT expire_time 
						FROM {$prefix}temp_obj
						WHERE id = '$row[id]'");
				$row_expire = $db->Do_Fetch_Assoc($query_expire);
				if ($row_expire) {
					$expire_string = "<br>(Scadenza: ".
						date("d/m/Y H:i",$row_expire['expire_time']).")";
				}
			}


			$obj_div.= "<table width=100%> <tr> <td class=\"obj\">
				<img width=100 height=100 src=\"$row[image_url]\" align=\"left\">
        <div $disabled>
        <b>$obj_name</b>
        <br>Dimensione: $dimensione
				$visible_uses
				$expire_string
        <p>$description</p>
        </div> </td> </tr> </table>";

			if($pg==$x7s->username || checkIfMaster()){
				$money_qty = "";
				$money_form = "";
				if($row['name'] == $money_name)	{
					if ($row['equipped']) {
						$money_form= "
							<form action=\"index.php?act=sheet&page=equip&pg=$pg&split=1\"
							method=\"post\" name=\"split\">
							<input type=\"hidden\" name=\"id\" value=\"$row[id]\">
							<tr>
							<td>
							<input type=\"button\" class=\"button\" value=\"Raggruppa tutti\"
							onClick=\"javascript: location.href='index.php?act=sheet&page=equip&pg=$pg&group_money'\">
							</td>
							<td>
							<input type=\"submit\" class=\"button\" value=\"Dividi\">".
							"<input type=\"text\" name=\"amount\" class=\"text_input\" size=5>
							<input type=\"hidden\" name=\"group\" value=\"$row[id]\">
							</td>
							</tr>
							</form>
							<form action=\"index.php?act=sheet&page=equip&pg=$pg&pay=1\"
							method=\"post\" name=\"payment\">
							<input type=\"hidden\" name=\"id\" value=\"$row[id]\">
							<tr>
							<td>Paga a:</td>
							<td>
							<input type=\"text\" name=\"owner\" class=\"text_input\" size=10>
							</td>
							</tr>
							<tr>
							<td>
							Ammontare:</td>
							<td>
							<input type=\"text\" name=\"amount\" class=\"text_input\" size=5>
							</td>
							</tr>
							<tr>
							<td><input type=\"submit\" class=\"button\" value=\"Paga\"></td>
							</tr>
							</form>";
					}
				}

				$equip_text="Deposita";
				if(!$row['equipped']){
					$equip_text="Equipaggia";
				}
				$obj_div.="
          <table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
					<form action=\"index.php?act=sheet&page=equip&pg=$pg&assign=1\"
					method=\"post\" name=\"object_assign\">
          <input type=\"hidden\" name=\"id\" value=\"$row[id]\">
          <tr>
          <td>Dai a:</td>
          <td><input type=\"text\" name=\"owner\" class=\"text_input\"></td>
          <td><input type=\"submit\" class=\"button\" value=\"Dai\"></td>
          </tr>
          $more_form
          </form>
          <tr>
          <td><input type=\"button\" class=\"button\" value=\"Butta\"
					onClick=\"javascript: confirmDrop($row[id])\">
          <input type=\"button\" class=\"button\" value=\"$equip_text\"
					onClick=\"javascript: location.href='index.php?act=sheet&page=equip&pg=$pg&equiptgl=$row[id]'\"></td>
          </tr>
					$money_form
          </table>";
			}

			if(checkIfMaster() && $row['name'] != $money_name){
				$obj_div.="<form action=\"index.php?act=sheet&page=equip&pg=$pg&moduse=1\"".
					" method=\"post\" name=\"object_moduse\">
          <table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
          <input type=\"hidden\" name=\"id\" value=\"$row[id]\">
          <tr>
          <td>Usi:</td>
          <td><input type=\"text\" name=\"use\" class=\"text_input\" size=2 value=\"$row[uses]\"></td>
          <td><input type=\"submit\" class=\"button\" value=\"Cambia\"></div></td>
          </tr>
          </table>
          ";

				$obj_div.="</form>\n";
			}

			$obj_div.="<br><br>\n";
		}
	}

	if (isset($_GET['search'])) {
		echo $obj_div;
		return "";
	}
	$body.=$obj_div;
	$body.="</div>\n";

	$body .= '<div class="counter" id="spazio">'. get_user_space($pg) .'</div>';

	if($errore!=''){
		$body.='<script language="javascript" type="text/javascript">
					function close_err(){
						document.getElementById("errore").style.visibility="hidden";
				}
				</script>
				<div id="errore" class="errore">'.$errore.'
				<br><br><input name="ok" type="button" class="button" value="OK" onClick="javascript: close_err(); window.location.href=\'index.php?act=sheet&page=equip&pg='.$_GET['pg'].'\';">
				</div>';
	}

	return $body;
}

function sheet_page_master(){
	global $db,$x7c,$prefix,$x7s,$print;
	$pg=$_GET['pg'];
	$body='';

	if(isset($_GET['settings_change']) && checkIfMaster()){
		if(isset($_POST['master']) && isset($_POST['master_private'])){
				
			if($pg!=$x7s->username){
				include_once('./lib/alarms.php');
				sheet_modification($pg,$_GET['page']);
			}

			$master = eregi_replace("\n","<Br>",$_POST['master']);
			$master_private = eregi_replace("\n","<Br>",$_POST['master_private']);
			$master = preg_replace("/\[img\]([^\[]*)\[\/img\]/i",
					"<img src=\"$1\">",$master);
			$master_private = preg_replace("/\[img\]([^\[]*)\[\/img\]/i",
					"<img src=\"$1\">",$master_private);

			$db->DoQuery("UPDATE {$prefix}users SET master='$master', master_private='$master_private' WHERE username='$pg'");
		}
	}

	$query = $db->DoQuery("SELECT master, master_private FROM {$prefix}users WHERE username='$pg'");
	$row = $db->Do_Fetch_Assoc($query);

	if($row){
		$modify_master = "";
		$modify_master_private = "";

		if(checkIfMaster()){
			$body .= '<script language="javascript" type="text/javascript">
					mod=false;
					
					function modify(){
						if(!mod){
							mod=true;
							document.forms[0].elements["master"].disabled=false;
							document.forms[0].elements["master"].style.border="1px solid";
							
							document.forms[0].elements["master_private"].disabled=false;
							document.forms[0].elements["master_private"].style.border="1px solid";
							
							document.forms[0].elements["aggiorna"].style.visibility="visible";
							document.forms[0].elements["mod_button"].style.visibility="hidden";
							
							document.getElementById("inner_master").style.visibility="hidden";
							document.getElementById("inner_private").style.visibility="hidden";
							document.getElementById("master_text").style.visibility="visible";
							document.getElementById("master_private_text").style.visibility="visible";
						}
					}
				</script>
				';
				
			$body .= '<form action="index.php?act=sheet&page=master&settings_change=1&pg='.$pg.'" method="post" name="sheet_form">
				';

			$master = eregi_replace("<Br>","\n",$row['master']);
			$master_private = eregi_replace("<Br>","\n",$row['master_private']);
			$master = preg_replace("/<img src=\"([^\"]*)\">/i",
					"[img]$1[/img]", $master);
			$master_private = preg_replace("/<img src=\"([^\"]*)\">/i",
					"[img]$1[/img]", $master_private);

			$modify_master = '<div id="master_text" style="visibility: hidden;"><textarea name="master" class="sheet_text" autocomplete="off">'.$master.'</textarea></div>';
			$modify_master_private = '<div id="master_private_text" style="visibility: hidden;"><textarea name="master_private" class="sheet_text" autocomplete="off">'.$master_private.'</textarea></div>';


		}
		
		$body .= '<div class="indiv" id="masterdiv">'.
			'<div id="inner_master">'.$row['master'].'</div>'.$modify_master.'</div>';

		if(checkIfMaster() || $x7s->username == $pg)
			$body .= '<div class="indiv" id="masterdiv_private">
				Annotazioni private:<div class="inner_private" id="inner_private">'.$row['master_private'].
				'</div>'.$modify_master_private.'</div>';
		
		if(checkIfMaster()){
			$body .= "<div id=\"modify\">
								<INPUT name=\"mod_button\" class=\"button\" type=\"button\" value=\"Modifica\" onClick=\"javascript: modify();\" style=\"visibility: visible;\">
								<INPUT name=\"aggiorna\" class=\"button\" type=\"SUBMIT\" value=\"Invia modifiche\" style=\"visibility: hidden;\">		
						</div>";

			$body .="</form>\n";
		}

	}

	return $body;
}

function sheet_page_background(){
	global $db,$x7c,$prefix,$x7s,$print;
	$pg=$_GET['pg'];
	$body='';
	$visual='';


	if(isset($_GET['settings_change']) && ($pg==$x7s->username || checkIfMaster())){
		if(	isset($_POST['storia']) &&
		isset($_POST['fisici']) &&
		isset($_POST['psico'])
		){

			$storia = $_POST['storia'];
			$fisici = $_POST['fisici'];
			$psico = $_POST['psico'];


			if($pg!=$x7s->username){
				include_once('./lib/alarms.php');
				sheet_modification($pg,$_GET['page']);
			}

			$db->DoQuery("UPDATE {$prefix}users
						SET 	storia='$storia',
							fisici='$fisici',
							psico='$psico'
						WHERE	username='$pg'");
		}
	}

	$query = $db->DoQuery("SELECT storia, fisici, psico
					FROM {$prefix}users
					WHERE username='$pg'");

	$row = $db->Do_Fetch_Assoc($query);

	if($row){
			
		$visual .= '<div class="indiv" id="storia">'.eregi_replace("\n","<Br>",$row['storia']).'</div>
				';
		$visual .= '<div class="indiv" id="fisici">'.eregi_replace("\n","<Br>",$row['fisici']).'</div>
				';
		$visual .= '<div class="indiv" id="psico">'.eregi_replace("\n","<Br>",$row['psico']).'</div>
				';
		if($pg==$x7s->username || checkIfMaster()){
			$body .= '<script language="javascript" type="text/javascript">
					mod=false;
					
					function modify(){
						if(!mod){
							mod=true;
							document.getElementById("storia").style.visibility = "hidden";
							document.getElementById("fisici").style.visibility = "hidden";
							document.getElementById("psico").style.visibility = "hidden";

							document.getElementById("storia_hid").style.visibility = "visible";
							document.getElementById("fisici_hid").style.visibility = "visible";
							document.getElementById("psico_hid").style.visibility = "visible";
							
							document.forms[0].elements["storia"].style.border="1px solid";
							document.forms[0].elements["psico"].style.border="1px solid";
							document.forms[0].elements["fisici"].style.border="1px solid";
							
							document.forms[0].elements["storia"].disabled=false;
							document.forms[0].elements["psico"].disabled=false;
							document.forms[0].elements["fisici"].disabled=false;
							
							document.forms[0].elements["aggiorna"].style.visibility="visible";
							document.forms[0].elements["mod_button"].style.visibility="hidden";
						}
					}
				</script>
				';
				
			$body .= $visual;
			
			$body .= '<form action="index.php?act=sheet&page=background&settings_change=1&pg='.$pg.'" method="post" name="sheet_form">
			';
				
			$body .= '<div class="indiv" id="storia_hid"><textarea name="storia" id="storia_text" class="sheet_text" autocomplete="off" disabled>'.$row['storia'].'</textarea></div>
			';
			$body .= '<div class="indiv" id="fisici_hid"><textarea name="fisici" id="fisici_text" class="sheet_text" autocomplete="off" disabled>'.$row['fisici'].'</textarea></div>
			';
			$body .= '<div class="indiv" id="psico_hid"><textarea name="psico" id="psico_text" class="sheet_text" autocomplete="off" disabled>'.$row['psico'].'</textarea></div>
			';
				
			$body .= "<div id=\"modify\">
							<INPUT name=\"mod_button\" class=\"button\" type=\"button\" value=\"Modifica\" onClick=\"javascript: modify();\" style=\"visibility: visible;\">
							<INPUT name=\"aggiorna\" class=\"button\" type=\"SUBMIT\" value=\"Invia modifiche\" style=\"visibility: hidden;\">		
					</div>";
		}
		else{
			$body .= $visual;
		}
			
		$body .= '</form>';

	}

	return $body;

}

function sheet_page_ability(){
	global $db,$x7c,$prefix,$x7s,$print;
	$errore='';
	$pg=$_GET['pg'];
	$min_auth=0;
	include_once('./lib/sheet_lib.php');
		
	if(isset($_GET['settings_change']) && ($pg==$x7s->username || checkIfModifySheet())){
		$ok = true;

		$query = $db->DoQuery("SELECT * FROM {$prefix}users WHERE username='$pg'");
		$row_user = $db->Do_Fetch_Assoc($query);
		$xp_avail=$row_user['xp']/$x7c->settings['xp_ratio'];
		$starting_xp = $x7c->settings['starting_xp'];

		if(!$row_user)
		die("Users not in database");

		$query = $db->DoQuery("SELECT a.corp AS corp, u.ability_id AS ab_id, u.value AS value, a.dep AS dep, a.dep_val AS dep_val, a.name AS name
							FROM 	{$prefix}userability u, 
							{$prefix}ability a
							WHERE 
								u.ability_id = a.id AND
								username='$pg' AND
								corp=''
							ORDER BY a.name");
								
							$ability='';
							while($row = $db->Do_Fetch_Assoc($query)){
								$ability[$row['ab_id']]=$row;
								if(!isset($_POST[$row['ab_id']])){
									$ok = false;
									break;
								}
							}
							if(!checkIfModifySheet() && !isset($_POST['xp']))
							$ok = false;

							//Controllo se le abilit� non sono state abbassate o superano il massimo
							//Il master fa quel che gli pare: niente controlli

							$tot_used=0;
							$lvl_gained=0;
							if(!checkIfModifySheet() && $ok){
								$max_ab = $x7c->settings['max_ab'];

									
								foreach($ability as $cur){
									if($cur['corp']!=""){
										die("Fatal: attempt to modify corp ability from normal form");
									}
									if($cur['value'] != $_POST[$cur['ab_id']]){
										$new_value = $_POST[$cur['ab_id']];

										while($new_value > $cur['value']){
											$tot_used+= $new_value;
											$new_value--;
										}
											
										if($cur['value'] > $_POST[$cur['ab_id']]){
											$errore .= "Errore, non puoi abbassare le caratteristiche<br>";
											$ok = false;
											break;
										}
										elseif($_POST[$cur['ab_id']] > $max_ab){
											$errore .= "Errore, non puoi superare il valore massimo<br>";
											$ok = false;
											break;
										}
									}
								}
									
								$lvl_gained=$tot_used;

									
								if(!checkIfModifySheet()){
									if($tot_used > $xp_avail){
										$errore .= "Hai usato troppi PX<br>";
										$ok = false;
									}
								}


								if($ok){
									//Controllo le dipendenze
									foreach($ability as $cur){
										if($cur['value'] != $_POST[$cur['ab_id']]){
											if($cur['dep'] != ""){
												if($_POST[$cur['ab_id']] > 0 && 2*$_POST[$cur['dep']] < $_POST[$cur['ab_id']]){
													$right_value=(2*$_POST[$cur['dep']])>0 ? 2*$_POST[$cur['dep']] : 1;
													$errore .= "Errore, non puoi avere ".$_POST[$cur['ab_id']]." gradi in <b>".$cur['name']."</b> senza vere almeno ".$right_value." gradi in b>".$ability[$cur['dep']]['name']."<br>";
													$ok = false;
													break;
												}
											}
										}
									}
								}

							}
								
							if($ok){
								//Ora posso aggiornare
									
								if($pg!=$x7s->username){
									include_once('./lib/alarms.php');
									sheet_modification($pg,$_GET['page']);
								}
									
								$newxp = $row_user['xp']-($tot_used * $x7c->settings['xp_ratio']);
								$newlvl = $row_user['lvl']+$lvl_gained;
									
								$db->DoQuery("UPDATE {$prefix}users
									SET xp='$newxp',
									lvl='$newlvl'
									WHERE username='$pg'");
								foreach($ability as $cur){
									if($cur['value'] != $_POST[$cur['ab_id']]){
										$db->DoQuery("UPDATE {$prefix}userability
									SET value='{$_POST[$cur['ab_id']]}'
									WHERE username='$pg'
									 AND ability_id='{$cur['ab_id']}'");
									}
								}
								header("location: index.php?act=sheet&page=ability&pg=$pg");
									
							}
								
	}
		
	$query = $db->DoQuery("SELECT xp FROM {$prefix}users WHERE username='$pg'");
	$row = $db->Do_Fetch_Assoc($query);
	$xp = floor($row['xp']/$x7c->settings['xp_ratio']);
	$body='';
		

		
	$body.="<div id=\"ability\">\n";
		
	$query = $db->DoQuery("SELECT * FROM 	{$prefix}userability,
	{$prefix}ability
							WHERE 
								ability_id=id AND
								username='$pg' AND
								corp=''
							ORDER BY dep,name");

	$ability = array();
	while($row = $db->Do_Fetch_Assoc($query)){
		$ability[$row['ability_id']]=$row;
	}
		
	$body .= "<div id=\"visual\"><table>";
	foreach($ability as $cur){
		if($cur['dep'] == ""){
			//  onMouseOver=\"javascript: show_desc('{$cur['ability_id']}')\" onMouseOut=\"javascript: hide_desc()\"
			$body .= "<tr class=\"ab_text\"><td class=\"ab_text\">".$cur['name']."</td><td>";
			for($i=0; $i<6; $i++){
				if($i<$cur['value']){
					$body.='<img src="./graphic/on.gif"/>';
				}
				else{
					$body.='<img src="./graphic/off.gif"/>';
				}
			}

			$body .= "</td></tr>\n";
			foreach($ability as $cur2){
				if($cur2['dep'] == $cur['ability_id']){
					// onMouseOver=\"javascript: show_desc('{$cur2['ability_id']}')\" onMouseOut=\"javascript: hide_desc()\"
					$body .= "<tr><td class=\"ab_text\" class=\"ab_text\">&nbsp;&nbsp;&nbsp;".$cur2['name']."</td><td>";
						
					for($i=0; $i<6; $i++){
						if($i<$cur2['value']){
							$body.='<img src="./graphic/on.gif"/>';
						}
						else{
							$body.='<img src="./graphic/off.gif"/>';
						}
					}

					$body .= "</td></tr>\n";
				}
			}
		}
	}

	$body.="</table></div>";
		
	if(($xp!=0 && $pg==$x7s->username) || checkIfModifySheet()){
		$max_ab = $x7c->settings['max_ab'];

		$body.=build_ability_javascript($max_ab);

		$body .= '<form action="index.php?act=sheet&page=ability&settings_change=1&pg='.$pg.'" method="post" name="sheet_form"><div id="modifiable">
						<table align="left" border="0" cellspacing="0" cellpadding="0">';



		foreach($ability as $cur){
			if($cur['dep'] == ""){
				$body .= "<tr>";
				$body .= "<td  onMouseOver=\"javascript: show_desc('{$cur['ability_id']}')\" onMouseOut=\"javascript: hide_desc()\" style=\"font-weight: bold;\">".$cur['name']."</td>
						<td><input class=\"button\" type=\"button\" value=\"-\" onClick=\"return sub('{$cur['ability_id']}');\">
						<input type=\"text\" name=\"{$cur['ability_id']}_display\" value=\"{$cur['value']}\" size=\"2\" style=\"text-align: right; color: blue;\" disabled/>
						<input type=\"hidden\" name=\"{$cur['ability_id']}\" value=\"{$cur['value']}\"/>
						<input class=\"button\" type=\"button\" value=\"+\" onClick=\"return add('{$cur['ability_id']}');\">
						<input type=\"hidden\" name=\"".$cur['ability_id']."_min\" value=\"{$cur['value']}\">
						<input type=\"hidden\" name=\"".$cur['ability_id']."_name\" value=\"{$cur['name']}\">
						<input type=\"hidden\" name=\"".$cur['ability_id']."_dep\" value=\"{$cur['dep']}\">";

				$query = $db->DoQuery("SELECT id FROM {$prefix}ability WHERE dep='{$cur['ability_id']}' ORDER BY name");
				$body .="<input type=\"hidden\" name=\"".$cur['ability_id']."_leaf\" value=\"";
				while($leaf = $db->Do_Fetch_Assoc($query)){
					$body .= $leaf['id']."|";
				}
				$body .= "\">";

				$body .= "</td></tr>\n";

				foreach($ability as $cur2){
					if($cur2['dep'] == $cur['ability_id']){
						$body .= "<tr>\n";
						$body .= "<td onMouseOver=\"javascript: show_desc('{$cur2['ability_id']}')\" onMouseOut=\"javascript: hide_desc()\" style=\"font-weight: bold;\">&nbsp;&nbsp;&nbsp;".$cur2['name']."</td>
									<td><input class=\"button\" type=\"button\" value=\"-\" onMouseDown=\"return sub('{$cur2['ability_id']}');\">
									<input type=\"text\" name=\"{$cur2['ability_id']}_display\" value=\"{$cur2['value']}\" size=\"2\" style=\"text-align: right; color: blue;\" disabled/>
									<input type=\"hidden\" name=\"{$cur2['ability_id']}\" value=\"{$cur2['value']}\"/>
									<input class=\"button\" type=\"button\" value=\"+\" onMouseDown=\"return add('{$cur2['ability_id']}');\">
									<input type=\"hidden\" name=\"".$cur2['ability_id']."_min\" value=\"{$cur2['value']}\">
									<input type=\"hidden\" name=\"".$cur2['ability_id']."_name\" value=\"{$cur2['name']}\">
									<input type=\"hidden\" name=\"".$cur2['ability_id']."_dep\" value=\"{$cur2['dep']}\">";
							
						if($cur2['dep']!= ""){
							$body .="
									<input type=\"hidden\" name=\"".$cur2['ability_id']."_dep_val\" value=\"{$cur2['dep_val']}\">";
						}
						$body .= "</td></tr>\n";
					}
				}
			}
		}


		$disabled="";

		$body .= "	</table>";

		if(!checkIfModifySheet()){
			$body .='<div id="#xp" align="center">Punti abilit&agrave;:<br>
							<input type="text" size="2" name="xp_display" value="'.$xp.'" style="text-align: right; color: blue;" disabled>
							<input type="hidden" name="xp" value="'.$xp.'"></div>
						';
		}

		$body.="</div>";
	}
		
	$body.="<div id=\"descr\"> </div>
				</div>";

	if(($xp!=0 && $pg==$x7s->username) || checkIfModifySheet()){
		$body .= "<div id=\"modify\">
                                                    <INPUT name=\"mod_button\" class=\"button\" type=\"button\" value=\"Modifica\" onClick=\"javascript: modify();\">
                                                    <INPUT id=\"aggiorna\" style=\"visibility: hidden;\" name=\"aggiorna\" class=\"button\" type=\"SUBMIT\" value=\"Invia modifiche\" $disabled>
				          </div></form>";
	}
		
	if($errore!=''){
		$body.='<script language="javascript" type="text/javascript">
					function close_err(){
						document.getElementById("errore").style.visibility="hidden";
					}
				</script>
				<div id="errore" class="errore">'.$errore.'
				<br><input name="ok" type="button" class="button" value="OK" onClick="javascript: close_err();">
				</div>';
	}
	return $body;

}

function sheet_page_main(){
	global $db,$x7c,$prefix,$x7s,$print, $auth_pcookie, $X7CHAT_CONFIG;
	$pg=$_GET['pg'];

	$head = "Scheda del personaggio";
	$body="";
	$errore="";
	$ok = true;
	$reload = false;
	$char;

	$query_usr = $db->DoQuery("SELECT * FROM {$prefix}users WHERE username='$pg'");
	$row_user = $db->Do_Fetch_Assoc($query_usr);
	if(!$row_user){
		die("User not in Database");
	}

	if(isset($_GET['daily_px']) && checkIfMaster()){

		$time = time();
		$day = date("j/n/Y", $row_user['daily_px']);
		if($row_user['daily_px'] < $time && $day != date("j/n/Y", $time)){
			$db->DoQuery("UPDATE {$prefix}users SET xp=xp+1, daily_px='$time' WHERE username='$pg'");
			$errore = "PX Giornaliero assegnato correttamente";
			$reload = true;
		}
		else
			$errore ="PX gironaliero gia' assegnato";
	}
	if(isset($_GET['toggle_death']) && isset($_GET['pg'])&& checkIfMaster()){
		$pg=$_GET['pg'];
		include_once('./lib/sheet_lib.php');
		$resurgo = true;
		if (isset($_GET['permanent_death']))
			$resurgo = false;
		$errore=toggle_death($pg, $_GET['toggle_death'], $resurgo);
		$reload = true;
	}

	if(isset($_GET['toggle_heal']) && isset($_GET['pg'])&& checkIfMaster()){
		$pg=$_GET['pg'];
		include_once('./lib/sheet_lib.php');
		$errore=toggle_heal($pg, $_GET['toggle_heal']);
		$reload = true;
	}

	if(isset($_GET['settings_change']) && checkIfMaster()){

		//We are modifiyng character sheet
		if(isset($_POST['name']) &&
				isset($_POST['age'])&&
				isset($_POST['hobbies'])&&
				isset($_POST['nat']) &&
				isset($_POST['marr']) &&
				isset($_POST['gender']) &&
				isset($_POST['avatar_in']) &&
				isset($_POST['lvl'])) {


			if($_POST['name']==''){
				$ok = false;
				$errore .= "Non hai specificato il nome<br>";
			}
			if(($_POST['age']=='' || $_POST['age']<16) && !checkIfMaster()){
				$ok = false;
				$errore .= "Et&agrave; non valida<br>";
			}
			if($_POST['nat']==''){
				$ok = false;
				$errore .= "Non hai specificato la nazionalit&agrave;<br>";
			}


		}
		else{
			$ok = false;
			$errore .= "Parametri mancanti<br>";
		}


		$query = $db->DoQuery("SELECT * FROM {$prefix}characteristic ORDER BY name");

		$char='';
		while($row = $db->Do_Fetch_Assoc($query)){
			$char[$row['id']]=$row;
		}


		if($ok){
			$reload = true;
			//Ora posso aggiornare
			if($pg!=$x7s->username){
				include_once('./lib/alarms.php');
				sheet_modification($pg,$_GET['page']);
			}

			$db->DoQuery("UPDATE {$prefix}users SET
					name='$_POST[name]',
					age='$_POST[age]',
					nat='$_POST[nat]',
					marr='$_POST[marr]',
					hobbies='$_POST[hobbies]',
					gender='$_POST[gender]',
					avatar='$_POST[avatar_in]',
					lvl='$_POST[lvl]'
					WHERE username='$pg'");


			if(isset($_POST['pwd1']) && isset($_POST['pwd2']) && $_POST['pwd1']!='' && $_POST['pwd2']!=''){

				if($_POST['pwd1'] != $_POST['pwd2']){
					$errore .= "Non hai digitato correttamente la password";
				}
				else{
					$errore .= "Password cambiata";
					$newpwd = md5($_POST['pwd1']);
					if($pg==$x7s->username){
						setcookie($auth_pcookie,$newpwd,time()+$x7c->settings['cookie_time'],$X7CHAT_CONFIG['COOKIE_PATH']);
					}

					$db->DoQuery("UPDATE {$prefix}users SET
							password='$newpwd'
							WHERE username='$pg'");
				}
			}

			if(isset($_POST['info'])){
				if(is_numeric($_POST['info'])){
					if($_POST['info']!=$row_user['info']){
						$time=time();
						$db->DoQuery("UPDATE {$prefix}users
								SET info='$_POST[info]',
								heal_time='$time'
								WHERE username='$pg'");
					}
				}
				else{
					$errore .= "Il campo \"Status\" puo' contenere solo numeri";
				}
			}

			if(isset($_POST['xp'])){
				$db->DoQuery("UPDATE {$prefix}users SET	xp='$_POST[xp]'	WHERE username='$pg'");
			}

			$strength_query = $db->DoQuery("SELECT value FROM {$prefix}usercharact
					WHERE username='$pg' AND charact_id='for'");
			if ($row = $db->Do_Fetch_Assoc($strength_query)) {
        if ($row['value'] != $_POST['for']) {
					$spazio = ($_POST['for'] - 3) * 2;
					$db->DoQuery("UPDATE {$prefix}users
									SET sheet_ok='1', spazio = $spazio
									WHERE username='$pg'");
					$db->DoQuery("UPDATE {$prefix}objects 
							SET equipped='0'
							WHERE owner='$pg'");

				}
			}

			foreach($char as $cur){
				if(!isset($_POST[$cur['id']])){
					$ok = false;
					break;
				}

				$db->DoQuery("UPDATE {$prefix}usercharact
						SET value='{$_POST[$cur['id']]}'
						WHERE username='$pg'
						AND charact_id='{$cur['id']}'");
			}
		}
	}
	else if(isset($_GET['settings_change']) && !checkIfMaster() && $x7s->username==$pg){

		if(isset($_POST['avatar_in'])){
			$db->DoQuery("UPDATE {$prefix}users SET
					avatar='$_POST[avatar_in]'
					WHERE username='$pg'");
			$reload = true;
		}

		if(isset($_POST['pwd1']) && isset($_POST['pwd2']) && $_POST['pwd1']!='' && $_POST['pwd2']!=''){

			if($_POST['pwd1'] != $_POST['pwd2']){
				$errore .= "Non hai digitato correttamente la password";
			}
			else{
				$errore .= "Password cambiata";
				$newpwd = md5($_POST['pwd1']);
				setcookie($auth_pcookie,$newpwd,time()+$x7c->settings['cookie_time'],$X7CHAT_CONFIG['COOKIE_PATH']);
				$db->DoQuery("UPDATE {$prefix}users SET
						password='$newpwd'
						WHERE username='$pg'");
				$reload = true;	
			}
		}
	}

	if($reload){
		$query_usr = $db->DoQuery("SELECT * FROM {$prefix}users WHERE username='$pg'");
		$row_user = $db->Do_Fetch_Assoc($query_usr);
		if(!$row_user){
			die("User not in Database");
		}
	}



	$gender = $row_user['gender'] == 0 ? "M":"F";
	$group = $row_user['user_group'];
	$date = date("j/n/Y",$row_user['iscr']);


	if(checkIfMaster()){
		$body .= '		<script language="javascript" type="text/javascript">
			mod=false;

		function modify(){
			if(!mod){
				mod=true;
				document.forms[0].elements["name"].style.color="blue";
				document.forms[0].elements["age"].style.color="blue";
				document.forms[0].elements["nat"].style.color="blue";
				document.forms[0].elements["marr"].style.color="blue";
				document.forms[0].elements["gender"].style.color="blue";
				document.forms[0].elements["avatar_in"].style.color="blue";

				document.forms[0].elements["name"].style.border="1px solid";
				document.forms[0].elements["age"].style.border="1px solid";
				document.forms[0].elements["nat"].style.border="1px solid";
				document.forms[0].elements["marr"].style.border="1px solid";
				document.forms[0].elements["gender"].style.border="1px solid";
				document.forms[0].elements["avatar_in"].style.border="1px solid";

				document.forms[0].elements["name"].style.background="white";
				document.forms[0].elements["age"].style.background="white";
				document.forms[0].elements["nat"].style.background="white";
				document.forms[0].elements["marr"].style.background="white";
				document.forms[0].elements["gender"].style.background="white";
				document.forms[0].elements["avatar_in"].style.background="white";

				document.forms[0].elements["name"].disabled=false;
				document.forms[0].elements["age"].disabled=false;
				document.forms[0].elements["nat"].disabled=false;
				document.forms[0].elements["marr"].disabled=false;
				document.forms[0].elements["avatar_in"].disabled=false;
				document.forms[0].elements["gender"].disabled=false;
				document.forms[0].elements["marr"].disabled=false;

				document.forms[0].elements["avatar_in"].style.visibility="visible";
				document.forms[0].elements["aggiorna"].style.visibility="visible";
				document.forms[0].elements["mod_button"].style.visibility="hidden";

				document.forms[0].elements["pwd1"].style.color="blue";
				document.forms[0].elements["pwd1"].style.border="1px solid";
				document.forms[0].elements["pwd1"].style.background="white";
				document.forms[0].elements["pwd1"].disabled=false;
				document.forms[0].elements["pwd1"].style.visibility="visible";

				document.forms[0].elements["pwd2"].style.color="blue";
				document.forms[0].elements["pwd2"].style.border="1px solid";
				document.forms[0].elements["pwd2"].style.background="white";
				document.forms[0].elements["pwd2"].disabled=false;
				document.forms[0].elements["pwd2"].style.visibility="visible";

				document.getElementById("visual_modify").style.visibility="visible";
				document.getElementById("pwd1").style.visibility="visible";
				document.getElementById("pwd2").style.visibility="visible";


				document.getElementById("avatar").innerHTML="<br><br><br>Specifica l\'URL del tuo avatar nel campo qui sopra";

				document.forms[0].elements["info"].style.color="blue";
				document.forms[0].elements["info"].style.border="1px solid";
				document.forms[0].elements["info"].style.background="white";
				document.forms[0].elements["info"].disabled=false;

				document.forms[0].elements["hobbies"].style.color="blue";
				document.forms[0].elements["hobbies"].style.border="1px solid";
				document.forms[0].elements["hobbies"].style.background="white";
				document.forms[0].elements["hobbies"].disabled=false;

				document.forms[0].elements["xp"].style.color="blue";
				document.forms[0].elements["xp"].style.background="white";
				document.forms[0].elements["xp"].disabled=false;
				
				document.forms[0].elements["lvl"].style.color="blue";
				document.forms[0].elements["lvl"].style.background="white";
				document.forms[0].elements["lvl"].disabled=false;
			}
		}

		</script>';
	}

	$body .= "
		<div class=\"indiv\" id=\"login\"><a class=\"dark_link\" onClick=\"javascript: hndl = window.open('index.php?act=mail&write&to=$row_user[username]','MsgCenter','location=no,menubar=no,resizable=no,status=no,toolbar=no,scrollbars=yes,width=488,height=650'); hndl.focus();\">$row_user[username]</a></div>
		<div class=\"indiv\" id=\"group\">$group</div>
		<div class=\"indiv\" id=\"date\">$date</div>
		<div class=\"indiv\" id=\"lvl\">$row_user[lvl]</div>
		<div class=\"indiv\" id=\"avatar\"><a class=\"dark_link\" onClick=\"javascript: hndl = window.open('index.php?act=mail&write&to=$row_user[username]','MsgCenter','location=no,menubar=no,resizable=no,status=no,toolbar=no,scrollbars=yes,width=488,height=650'); hndl.focus();\">
		";

	if($row_user['avatar']!='')
		$body .= "<img src=\"$row_user[avatar]\" width=200 height=200 />";

	$body.='</a></div>';

	$body.= "
		<div class=\"indiv\" id=\"status\">$row_user[info]</div>
		<div class=\"indiv\" id=\"real_status\">$row_user[hobbies]</div>
		<div class=\"indiv\" id=\"xp_point\">$row_user[xp]</div>
		";



	$query_char = $db->DoQuery("SELECT uc.value AS value, c.name AS name, c.id AS id
			FROM 	{$prefix}usercharact uc,
			{$prefix}characteristic c
			WHERE	c.id=uc.charact_id
			AND 	uc.username='$pg'");

	while($row_ch = $db->Do_Fetch_Assoc($query_char)){
		$charact[$row_ch['id']]=$row_ch;
	}


	//Auto heal_button
	$rob=$charact['rob']['value'];

	if($row_user['autoheal'] && $row_user['info']!="Morto" && $row_user['info']<($rob*2)){
		$time=time();
		$elapsed=$time-$row_user['heal_time'];
		$rec_rate=(13-$rob)*3600*24;

		$rec_value=floor($elapsed/$rec_rate);

		if($rec_value>0){
			$new_status=$row_user['info']+$rec_value;
			$new_status= ($new_status > $rob*2) ? $rob*2 : $new_status;

			$db->DoQuery("UPDATE {$prefix}users SET heal_time='$time', info='$new_status' WHERE username='$pg'");
			$row_user['info']=$new_status;
		}
	}


	$ability='';

	$body .="<div class=\"indiv\" id=\"name\">$row_user[name]</div>
		<div class=\"indiv\" id=\"age\">$row_user[age]</div>
		<div class=\"indiv\" id=\"nat\">$row_user[nat]</div>
		<div class=\"indiv\" id=\"marr\">$row_user[marr]</div>
		<div class=\"indiv\" id=\"gender\">$gender</div>
		";

	foreach($charact as $cur_ch){
		$body .= "<div id=\"".$cur_ch['name']."\">".$cur_ch['value']."</div>\n";
	}


	if(checkIfMaster()){
		foreach($charact as $cur_ch){
			$body .= "<div id=\"".$cur_ch['name']."\">".$cur_ch['value']."</div>\n";
		}

		//Modified script for master modification that can everything

		$body .= '
			<script language="javascript" type="text/javascript">
			function add_ch(ch_name){
				var value = parseInt(document.sheet_form[ch_name].value);
				document.sheet_form[ch_name].value = value + 1;

				do_ch_form_refresh(ch_name);
			}

		function sub_ch(ch_name){
			var value = parseInt(document.sheet_form[ch_name].value);
			document.sheet_form[ch_name].value = value -1;

			do_ch_form_refresh(ch_name);
		}

		function do_ch_form_refresh(ch_name){
			document.sheet_form[ch_name+"_display"].value = document.sheet_form[ch_name].value;
			modify();
		}

		</script>';


		$body.='<form action="index.php?act=sheet&settings_change=1&pg='.$pg.
			'" method="post" name="sheet_form">
			<div id="visual_modify">';


		$ch = $x7c->settings['starting_ch'] - (($x7c->settings['min_ch'])*sizeof($charact));



		foreach($charact as $cur_ch){
			$ch -= $cur_ch['value'] - $x7c->settings['min_ch'];

			$body .= "
				<div id=\"{$cur_ch['name']}\">
				<input class=\"button\" type=\"button\" value=\"-\" onMouseDown=\"return sub_ch('{$cur_ch['id']}');\">
				<input type=\"text\" name=\"{$cur_ch['id']}_display\" value=\"{$cur_ch['value']}\" size=\"2\" style=\"text-align: right; color: blue;\" disabled/>
				<input type=\"hidden\" name=\"{$cur_ch['id']}\" value=\"{$cur_ch['value']}\"/>
				<input class=\"button\" type=\"button\" value=\"+\" onMouseDown=\"return add_ch('{$cur_ch['id']}');\"></div>\n";
		}


		if($gender=="M"){
			$male="selected";
			$female="";

			if($row_user['marr']=="Libero")
				$marr_opt="<option value=\"Libero\" selected>Libero</option>
					<option value=\"Sposato\">Sposato</option>";
			else
				$marr_opt="<option value=\"Libero\">Libero</option>
					<option value=\"Sposato\" selected>Sposato</option>";
		}
		else{
			$male="";
			$female="selected";

			if($row_user['marr']=="Libera")
				$marr_opt="<option value=\"Libera\" selected>Libera</option>
					<option value=\"Sposata\">Sposata</option>";
			else
				$marr_opt="<option value=\"Libera\">Libera</option>
					<option value=\"Sposata\" selected>Sposata</option>";
		}

		$body .= "<div class=\"indiv\" id=\"pwd1\" style=\"visibility: hidden;\">Nuova password:<br><input class=\"sheet_input\" type=\"password\" name=\"pwd1\" size=\"10\" style=\"visibility: hidden; font-size:10pt;\" disabled /></div>\n";

		$body .= "<div class=\"indiv\" id=\"pwd2\" style=\"visibility: hidden;\">Ripeti nuova password:<br><input class=\"sheet_input\" type=\"password\" name=\"pwd2\" size=\"10\" style=\"visibility: hidden; font-size:10pt;\" disabled /></div>\n";

		$body .= "
			<div class=\"indiv\" id=\"name\"><input class=\"sheet_input\" type=\"text\" name=\"name\" value=\"$row_user[name]\" size=\"16\" disabled /></div>
			<div class=\"indiv\" id=\"age\"><input class=\"sheet_input\" type=\"text\" name=\"age\" value=\"$row_user[age]\" size=\"2\" style=\"text-align: right;\" disabled /></div>
			<div class=\"indiv\" id=\"nat\"><input class=\"sheet_input\" type=\"text\" name=\"nat\" value=\"$row_user[nat]\" size=\"16\" disabled /></div>
			<div class=\"indiv\" id=\"marr\">
			<select class=\"button\" name=\"marr\" disabled>
			$marr_opt
			</select>
			</div>
			<div class=\"indiv\" id=\"gender\">
			<select class=\"button\" name=\"gender\" disabled>
			<option value=\"0\" $male>M</option>
			<option value=\"1\" $female>F</option>
			</select>
			</div>
			<div class=\"indiv\" id=\"avatar\"><input class=\"sheet_input\" type=\"text\" name=\"avatar_in\" value=\"$row_user[avatar]\" size=\"10\" style=\"visibility: hidden; font-size:10pt;\" disabled /></div>
			";

		$time = time();
		$day=date("j/n/Y", $row_user['daily_px']);
		$extra='';

		if(checkIfMaster() && $row_user['daily_px'] < $time && $day!=date("j/n/Y", $time)){
			$extra= "<INPUT name=\"daily_px\" class=\"button\" type=\"button\" value=\"PX Giornaliero\" onClick=\"javascript: window.location.href='index.php?act=sheet&page=main&daily_px=1&pg=$pg';\" style=\"visibility: visible;\">";
		}

		$body.= "
			<div class=\"indiv\" id=\"status\"><input class=\"sheet_input\" type=\"text\" name=\"info\" value=\"$row_user[info]\" size=\"5\" disabled /></div>
			<div class=\"indiv\" id=\"real_status\"><input class=\"sheet_input\" type=\"text\" name=\"hobbies\" value=\"$row_user[hobbies]\" size=\"10\" disabled /></div>
			<div class=\"indiv\" id=\"xp_point\"><input class=\"sheet_input\" type=\"text\" id=\"xp\" name=\"xp\" size=\"5\" value=\"$row_user[xp]\" disabled />$extra</div>
			<div class=\"indiv\" id=\"lvl\"><input class=\"sheet_input\" type=\"text\" id=\"lvl\" name=\"lvl\" size=\"5\" value=\"$row_user[lvl]\" disabled/></div>
			";


		$body .= "<script language=\"javascript\" type=\"text/javascript\">

			function master_action(sel){
				switch(sel.options[sel.selectedIndex].value) {
					case 'kill':
						do_kill();
						break;
					case 'real_kill':
						do_permanent_kill();
						break;
					case 'respawn':
						window.location.href='index.php?act=sheet&page=main&toggle_death=0&pg=$pg';
						break;
					case 'heal_off':
						window.location.href='index.php?act=sheet&page=main&toggle_heal=0&pg=$pg';
						break;
					case 'heal_on':
						window.location.href='index.php?act=sheet&page=main&toggle_heal=1&pg=$pg';
						break;
				}
			}
		function do_kill(){
			if(!confirm('vuoi davvero uccidere il personaggio?'))
				return;
			window.location.href='index.php?act=sheet&page=main&toggle_death=1&pg=$pg';
		}
		function do_permanent_kill(){
			if(!confirm('vuoi davvero uccidere il personaggio?'))
				return;
			window.location.href='index.php?act=sheet&page=main&toggle_death=1&permanent_death=1&pg=$pg';
		}
		</script>
			
		</div>";

		$body .= "<div id=\"modify\"><INPUT name=\"mod_button\" class=\"button\" type=\"button\" value=\"Modifica\" onClick=\"javascript: modify();\" style=\"visibility: visible;\">
			<INPUT name=\"aggiorna\" class=\"button\" type=\"SUBMIT\" value=\"Invia modifiche\" style=\"visibility: hidden;\">"; 

		$body .="<br>
			<select class=\"button\" onChange=\"javascript: master_action(this);\">
			<option>Azioni Master</option>
			<option>------------</option>";

		if($row_user['info']!="Morto" && $row_user['info'] >= -10){
			$body .= "<option value=\"kill\">Uccidi</option>";
			$body .= "<option value=\"real_kill\">EXTERMINATE!</option>";
		}
		else{
			$body .= "<option value=\"respawn\">Resuscita</option>";
		}

		if($row_user['autoheal']){
			$body .= "<option value=\"heal_off\">Disattiva Auto-Heal</option>";
		}
		else{
			$body .= "<option value=\"heal_on\">Attiva auto-heal</option>";

		}

		$body .= "</select>";
		if ($row_user['autoheal']){
			$body .= "  Autoheal: ON";
		}
		else {
			$body .= "  Autoheal: OFF";
		}
		$body .="</div></form>";

	}


	//Just for the avatar and password modification
	if(!checkIfMaster() && $x7s->username==$pg){

		$body .='		<script language="javascript" type="text/javascript">
			mod=false;

		function modify(){
			if(!mod){
				mod=true;
				document.forms[0].elements["avatar_in"].style.color="blue";
				document.forms[0].elements["avatar_in"].style.border="1px solid";
				document.forms[0].elements["avatar_in"].style.background="white";
				document.forms[0].elements["avatar_in"].disabled=false;
				document.forms[0].elements["avatar_in"].style.visibility="visible";

				document.forms[0].elements["pwd1"].style.color="blue";
				document.forms[0].elements["pwd1"].style.border="1px solid";
				document.forms[0].elements["pwd1"].style.background="white";
				document.forms[0].elements["pwd1"].disabled=false;
				document.forms[0].elements["pwd1"].style.visibility="visible";

				document.forms[0].elements["pwd2"].style.color="blue";
				document.forms[0].elements["pwd2"].style.border="1px solid";
				document.forms[0].elements["pwd2"].style.background="white";
				document.forms[0].elements["pwd2"].disabled=false;
				document.forms[0].elements["pwd2"].style.visibility="visible";

				document.getElementById("pwd1").style.visibility="visible";
				document.getElementById("pwd2").style.visibility="visible";

				document.getElementById("avatar").innerHTML="<br><br><br>Specifica l\'URL del tuo avatar nel campo qui sopra";
				document.forms[0].elements["aggiorna"].style.visibility="visible";
				document.forms[0].elements["mod_button"].style.visibility="hidden";
			}
		}
		</script>';

		$body.='<form action="index.php?act=sheet&settings_change=1&pg='.$pg.'" method="post" name="sheet_form">';
		$body .= "<div class=\"indiv\" id=\"avatar\"><input class=\"sheet_input\" type=\"text\" name=\"avatar_in\" value=\"$row_user[avatar]\" size=\"10\" style=\"visibility: hidden; font-size:10pt;\" disabled /></div>\n";

		$body .= "<div class=\"indiv\" id=\"pwd1\" style=\"visibility: hidden;\">Nuova password:<br><input class=\"sheet_input\" type=\"password\" name=\"pwd1\" size=\"10\" style=\"visibility: hidden; font-size:10pt;\" disabled /></div>\n";
		$body .= "<div class=\"indiv\" id=\"pwd2\" style=\"visibility: hidden;\">Ripeti nuova password:<br><input class=\"sheet_input\" type=\"password\" name=\"pwd2\" size=\"10\" style=\"visibility: hidden; font-size:10pt;\" disabled /></div>\n";

		$body .= "<div id=\"modify\">
			<INPUT name=\"mod_button\" class=\"button\" type=\"button\" value=\"Modifica\" onClick=\"javascript: modify();\" style=\"visibility: visible;\">
			<INPUT name=\"aggiorna\" class=\"button\" type=\"SUBMIT\" value=\"Invia modifiche\" style=\"visibility: hidden;\">
			</div></form>";
	}

	$body .= "<div id=\"descr\"> </div>";
	if($errore!=''){
		$body.='<script language="javascript" type="text/javascript">
			function close_err(){
				document.getElementById("errore").style.visibility="hidden";
			}
		</script>
			<div id="errore" class="errore">'.$errore.'
			<br><input name="ok" type="button" class="button" value="OK" onClick="javascript: close_err();">
			</div>';
	}

	return $body;

}

function sheet_page_corp(){
	global $db,$x7c,$prefix,$x7s,$print, $x7p;
	$pg=$_GET['pg'];
	$body='';
	$errore='';

	$query = $db->DoQuery("SELECT xp,user_group,bio,corp_charge,base_group 
			FROM {$prefix}users WHERE username='$pg'");
	$row_user = $db->Do_Fetch_Assoc($query);

	if(!$row_user)
	die("Fatal: error fetching user");

	$query_group = $db->DoQuery("SELECT corp_master FROM {$prefix}groups
			WHERE username='$pg' AND usergroup='{$row_user['user_group']}'");
	$row_group = $db->Do_Fetch_Assoc($query_group);

	if(!$row_group)
		die("Fatal: error fetching group");

	$corp_master=false;
	if($row_group['corp_master'] && 
			$row_user['user_group']!=$row_user['base_group'])
		$corp_master=true;

	$xp=floor($row_user['xp']/$x7c->settings['xp_ratio']);

	$max_ab = $x7c->settings['max_ab'];

	if(isset($_GET['mgmt']) && ($corp_master || checkIfModifySheet())){
		if(isset($_GET['target']) || isset($_POST['target'])){

			if(isset($_GET['target']))
				$target = $_GET['target'];
			if(isset($_POST['target']))
				$target=$_POST['target'];

			$query=$db->DoQuery("SELECT username, user_group, base_group
					FROM {$prefix}users WHERE username='$target'");
			$row = $db->Do_Fetch_Assoc($query);

			if($row && $row['username']!=null){
				include_once('./lib/sheet_lib.php');
				if($_GET['mgmt']=='add'){
					if($row['user_group']==$row['base_group'])
						$errore=join_corp($target, $row_user['user_group'], 1);
					else{
						$errore="$target fa gia' parte di un altro Gremios";
					}
				}
				else if($_GET['mgmt']=='del'){
					$errore=leave_corp($target,$row_user['user_group']);
				}
				else if($_GET['mgmt']=='admin'){
					$errore=admin_corp($target, true, $row_user['user_group']);
				}
				else if($_GET['mgmt']=='notadmin'){
					$errore=admin_corp($target, false, $row_user['user_group']);
				}
				else if($_GET['mgmt']=='charge'){
					if(isset($_POST['charge'])){
						$db->DoQuery("UPDATE {$prefix}users SET corp_charge='{$_POST['charge']}' WHERE username='$target'");
						header("location: index.php?act=sheet&page=corp&pg=$pg");
					}
				}

			}
			else{
				$errore="Utente $target non esistente";
			}

		}
	}

	if(isset($_GET['settings_change']) &&
			($pg==$x7s->username || checkIfModifySheet())){
		$ok = true;
			
		$query = $db->DoQuery("SELECT * FROM {$prefix}users WHERE username='$pg'");
		$row_user = $db->Do_Fetch_Assoc($query);
			
		$xp_avail=$row_user['xp']/$x7c->settings['xp_ratio'];
			
		if(!$row_user)
		die("Users not in database");
			
		$query = $db->DoQuery("SELECT a.corp AS corp, u.ability_id AS ab_id,
				u.value AS value, a.dep AS dep, a.dep_val AS dep_val, a.name AS name
				FROM 	{$prefix}userability u, 
				{$prefix}ability a
				WHERE 
				u.ability_id = a.id AND
				username='$pg' AND
				corp<>''
				ORDER BY a.name");

		$ability='';
		while($row = $db->Do_Fetch_Assoc($query)){
			$ability[$row['ab_id']]=$row;
			if(!isset($_POST[$row['ab_id']])){
				$ok = false;
				break;
			}
		}
		if(!checkIfModifySheet() && !isset($_POST['xp']))
			$ok = false;

		//Controllo se le abilita' non sono state abbassate o superano il massimo
		//Il master fa quel che gli pare: niente controlli

		$tot_used=0;
		$lvl_gained=0;
		if(!checkIfModifySheet() && $ok){
			$max_ab = $x7c->settings['max_ab'];


			foreach($ability as $cur){
				if($cur['value'] != $_POST[$cur['ab_id']]){
					if(!in_array($cur['corp'],$x7p->profile['usergroup']) && $cur['corp']!="_personal"){
						$ok=false;
						$errore="Non puoi modificare l'abilita' $cur[name]; non fai piu' parte di {$cur['corp']}";
					}

					$new_value = $_POST[$cur['ab_id']];
					while($new_value > $cur['value']){
						$tot_used+= $new_value;
						$new_value--;
					}

					if($cur['value'] > $_POST[$cur['ab_id']]){
						$errore .= "Errore, non puoi abbassare le caratteristiche<br>";
						$ok = false;
						break;
					}
					elseif($_POST[$cur['ab_id']] > $max_ab){
						$errore .= "Errore, non puoi superare il valore massimo<br>";
						$ok = false;
						break;
					}
				}
			}


			if(!checkIfModifySheet()){
				if($tot_used > $xp_avail){
					$errore .= "Hai usato troppi PX<br>";
					$ok = false;
				}
			}

		}
		$lvl_gained = $tot_used;

		if($ok){
			//Ora posso aggiornare

			if($pg!=$x7s->username){
				include_once('./lib/alarms.php');
				sheet_modification($pg,$_GET['page']);
			}

			$newxp = $row_user['xp']-($tot_used * $x7c->settings['xp_ratio']);
			$newlvl = $row_user['lvl']+$lvl_gained;

			$db->DoQuery("UPDATE {$prefix}users
					SET xp='$newxp', lvl='$newlvl'
					WHERE username='$pg'");
			foreach($ability as $cur){
				if($cur['value'] != $_POST[$cur['ab_id']]){
					$db->DoQuery("UPDATE {$prefix}userability
							SET value='{$_POST[$cur['ab_id']]}'
							WHERE username='$pg'
							AND ability_id='{$cur['ab_id']}'");
				}
			}
			header("location: index.php?act=sheet&page=corp&pg=$pg");

		}

	}



	$query=$db->DoQuery("SELECT * FROM {$prefix}ability ab, {$prefix}userability ua
                                      WHERE ab.id=ua.ability_id
                                        AND ua.username='$pg' AND ab.corp<>''
																				ORDER BY ab.name");


	$ability=array();
	while($row = $db->Do_Fetch_Assoc($query)){
		$ability[$row['ability_id']]=$row;
	}

	$body .="<div id=\"corp_name\">$row_user[user_group]</div>\n";
	$body .="<div id=\"corp_symbol\"><img src=\"$row_user[bio]\" /></div>\n";
	
	if($row_user['corp_charge'] != $row_user['base_group'])
		$body .="<div id=\"corp_charge\">$row_user[corp_charge]</div>";

	$body.="<div id=\"corp\">\n";

	if(!$corp_master && !checkIfModifySheet())
		$body .= "<div id=\"visual\"><table>";
	else
		$body .= "<div id=\"visual2\"><table>";

	foreach($ability as $cur){
		if($cur['dep'] == ""){
			//onMouseOver=\"javascript: show_desc('{$cur['ability_id']}')\" onMouseOut=\"javascript: hide_desc()\"
			$body .= "<tr class=\"ab_text\"><td class=\"ab_text\">".$cur['name']."</td><td>";
			for($i=0; $i<6; $i++){
				if($i<$cur['value']){
					$body.='<img src="./graphic/on.gif"/>';
				}
				else{
					$body.='<img src="./graphic/off.gif"/>';
				}
			}

			$body .= "</td></tr>\n";
		}
	}
	$body.="</table></div>";

	include_once('./lib/sheet_lib.php');
	$body .= build_ability_javascript($max_ab);

	$body .= '<form action="index.php?act=sheet&page=corp&settings_change=1&pg='.
		$pg.'" method="post" name="sheet_form">';

	if(!$corp_master && !checkIfModifySheet())
		$body .= '<div id="modifiable3">';
	else
		$body .= '<div id="modifiable2">';

	$body.='<table align="left" border="0" cellspacing="0" cellpadding="0">';
	foreach($ability as $cur){
		$body .= "<tr>";
		$body .= "<td  onMouseOver=\"javascript: show_desc('{$cur['ability_id']}')\"
			onMouseOut=\"javascript: hide_desc()\" style=\"font-weight: bold;\">".$cur['name']."</td>
				<td>";
		
		if(in_array($cur['corp'],$x7p->profile['usergroup']) || $cur['corp'] == "_personal" || checkIfModifySheet())
			$body .= "<input class=\"button\" type=\"button\" value=\"-\" onClick=\"return sub('{$cur['ability_id']}');\">";

		$body .= "<input type=\"text\" name=\"{$cur['ability_id']}_display\" value=\"{$cur['value']}\" size=\"2\" style=\"text-align: right; color: blue;\" disabled/>";
		$body .= "<input type=\"hidden\" name=\"{$cur['ability_id']}\" value=\"{$cur['value']}\"/>";

		if(in_array($cur['corp'],$x7p->profile['usergroup']) || $cur['corp'] == "_personal" || checkIfModifySheet())
			$body .= "<input class=\"button\" type=\"button\" value=\"+\" onClick=\"return add('{$cur['ability_id']}');\">";

		$body .= "<input type=\"hidden\" name=\"".$cur['ability_id']."_min\" value=\"{$cur['value']}\">
				<input type=\"hidden\" name=\"".$cur['ability_id']."_name\" value=\"{$cur['name']}\">
				<input type=\"hidden\" name=\"".$cur['ability_id']."_dep\" value=\"{$cur['dep']}\">";

		$query = $db->DoQuery("SELECT id FROM {$prefix}ability WHERE dep='{$cur['ability_id']}' ORDER BY name");
		$body .="<input type=\"hidden\" name=\"".$cur['ability_id']."_leaf\" value=\"";
		while($leaf = $db->Do_Fetch_Assoc($query)){
			$body .= $leaf['id']."|";
		}
		$body .= "\">";

		$body .= "</td></tr>\n";
	}

	$body .= "	</table>";

	if(!checkIfModifySheet()){
		$body .='<div id="#xp" align="center">Punti abilit&agrave;:<br>
							<input type="text" size="2" name="xp_display" value="'.$xp.
							'" style="text-align: right; color: blue;" disabled>
							<input type="hidden" name="xp" value="'.$xp.'"></div>
						';
	}

	$body.="</div>";

	if(($xp!=0 && $pg==$x7s->username) || checkIfModifySheet()){
		if($corp_master || checkIfModifySheet())
		$body .= "<div id=\"modify2\">";
		else
		$body .= "<div id=\"modify3\">";

		$body .="<INPUT name=\"mod_button\" class=\"button\" type=\"button\"
			value=\"Modifica\" onClick=\"javascript: modify();\">
      <INPUT id=\"aggiorna\" name=\"aggiorna\" class=\"button\"
			type=\"SUBMIT\" value=\"Invia modifiche\" style=\"visibility: hidden;\">
			</div>";
	}

	$body.= '</form>';

	if(($corp_master && $pg==$x7s->username) ||
			(checkIfModifySheet() &&
			 $row_user['user_group'] != $row_user['base_group'])){
		$body.="<div id=\"corp_mgmt\">
                                <div id=\"corp_panel\">
                                Gestione gremios {$row_user['user_group']}
                                <form action=\"index.php?act=sheet&page=corp&pg=$pg&mgmt=add\" method=\"post\" name=\"corp_mgmt_form\">
                                    <table>
                                    <tr><td>Nuovo membro:<input type=\"text\" name=\"target\" class=\"text_input\"></td>
                                    <td><input type=\"submit\" class=\"button\" value=\"Inserisci\"></td></tr>
                                    </table>
                                </form>
                                  </div>
                                  <div id=\"people\"><table>";

		$query = $db->DoQuery("SELECT g.username AS username, g.corp_master AS corp_master, corp_charge FROM {$prefix}users u, {$prefix}groups g WHERE g.username=u.username AND usergroup='{$row_user['user_group']}'");

		while($row=$db->Do_Fetch_Assoc($query)){

			if($row['corp_master']){
				$admin="<td><a href=\"index.php?act=sheet&page=corp&pg=$pg&mgmt=notadmin&target=$row[username]\">[Destituisci capo]</a></td>";
			}
			else{
				$admin="<td><a href=\"index.php?act=sheet&page=corp&pg=$pg&mgmt=admin&target=$row[username]\">[Rendi capo]</a></td>";
			}
			$body.="<tr>
                                        <td>$row[username]</td>
                                        <td><a href=\"index.php?act=sheet&page=corp&pg=$pg&mgmt=del&target=$row[username]\">[Cancella]</a></td>
                                        $admin
                                        <form action=\"index.php?act=sheet&page=corp&pg=$pg&mgmt=charge\" method=\"post\" name=\"corp_charge_form\">
                                        	<td><input type=\"text\" name=\"charge\" class=\"text_input\" style=\"width: 90;\" value=\"$row[corp_charge]\"></td>
                                    		<td><input type=\"submit\" class=\"button\" value=\"Assegna carica\">
                                    		<input type=\"hidden\" value=\"$row[username]\" name=\"target\"></td>
                                        </form>
                                    </tr>";
		}

		$body.="</table></div></div>";
	}

	$body.="</div>";

	if($errore!=''){
		$body.='<script language="javascript" type="text/javascript">
				function close_err(){
					document.getElementById("errore").style.visibility="hidden";
				}
			</script>
			<div id="errore" class="errore">'.$errore.'
			<br><input name="ok" type="button" class="button" value="OK" onClick="javascript: close_err();">
			</div>';
	}



	return $body;

}

function print_sheet($body,$bg){
	global $print,$x7c,$x7s;
	if(!isset($_GET['pg'])){
		$pg=$x7s->username;
	}
	$pg=$_GET['pg'];

	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
	echo "<html dir=\"$print->direction\"><head><title>{$x7c->settings['site_name']} -- Scheda PG $pg</title>";
	echo $print->style_sheet;
	echo $print->ss_mini;
	echo $print->ss_chatinput;
	echo $print->ss_uc;

	echo '
		<LINK REL="SHORTCUT ICON" HREF="./favicon.ico">
		<style type="text/css">
			INPUT{
				height: 21px;
			}
			.obj {
				font-size: 10pt;
			}
			#errore{
				top: 200px;
				left: 50px;
				position: absolute;
				background-color: lightyellow;
				padding: 5px;
				border: 3px dashed red;
				text-decoration: none;
			}
			#sheetmain{
				background-image:url(./graphic/schedapgPRINC.jpg);
			}
			#sheetability{
				background-image:url(./graphic/schedapgAB.jpg);
			}
			#sheetmaster{
				background-image:url(./graphic/schedapgCOM.jpg);
			}
			#sheetequip{
				background-image:url(./graphic/schedapgEQP.jpg);
			}
			#sheetbackground{
				background-image:url(./graphic/schedapgBG.jpg);
			}
			#sheetcorp{
				background-image:url(./graphic/schedapgCORP.jpg);
			}
			#psico_hid{
				top: 370px;
				left: 250px;
				width: 200px;
				height: 250px;
				visibility: hidden;
			}
			#storia_hid{
				top: 80px;
				left: 30px;
				width: 430px;
				height: 250px;
				visibility: hidden;
			}
			#fisici_hid{
				top: 370px;
				left: 30px;
				width: 200px;
				height: 250px;
				visibility: hidden;
			}
			#psico{
				top: 370px;
				left: 250px;
				width: 200px;
				height: 250px;
			}
			#storia{
				top: 80px;
				left: 30px;
				width: 430px;
				height: 250px;
			}
			#fisici{
				top: 370px;
				left: 30px;
				width: 200px;
				height: 250px;
			}';
		

		echo '#master, #masterdiv{
				top: 60px;
				left: 50px;
				width: 400px;
				height: 220px;
			}
			
      #master_private, #masterdiv_private{
				top: 300px;
				left: 50px;
				width: 400px;
				height: 220px;
				overflow: hidden;
			}
      #master_private_text, #inner_private, .inner_private{
				height: 200px;
				width: 400px;
				overflow: auto;
				position: absolute;
			}
			';
		
	echo '
			.sheet_text{
				background: transparent;
				overflow: auto;
				font-size: 10pt;
				font-weight: bold;
				color: black;
				border: 0;
				width: 98%;
				height: 90%;
			}
			.ab_text{
				font-size: 8pt;
				font-weight: bold;
				color: black;
			}
			.sheet {
				width: 500px; 
				height: 680px;
				position: absolute;
				left: 0px;
				top: 0px;
				color: black;
				background: white;
				font-weight: bold;
				font-size: 11pt;
			}
			.sheet_input{
				background: transparent;
				border: 0;
				font-weight: bold;
				font-size: 11pt;
				color: black;
			}
			.indiv{
				position: absolute;
				overflow: auto;
			}
			.sheetnav{
				position: absolute;
				width: 20px; 
				height: 20px; 
			}
			#objects{
				position: absolute;
				overflow: auto;
				top: 65px;
				left: 55px;
				width: 400px;
				height: 530px;
			}
			#pwd1{
				top: 280px;
				left: 300px;
			}
			#pwd2{
				top: 320px;
				left: 300px;
			}
			#ch_point{
				position: absolute;
				left: 300px;
				top: 30px;
			}
			#submit{
				position: absolute;
				left: 200px;
				top: 630px;
			}
			#modify{
				position: absolute;
				left: 50px;
				top: 630px;
			}
	               #modify2{
				position: absolute;
				left: 0px;
				top: 190px;
				width: 200px;
			}
			#modify3{
				position: absolute;
				left: 50px;
				top: 460px;
				width: 200px;
			}
			#ability{
				position: absolute;
				left: 50px;
				top: 70px;
			}
			#corp{
				position: absolute;
				left: 50px;
				top: 150px;
			}	
			#Forza{
				position: absolute;
				left: 360px;
				top: 123px;
			}
			#Robustezza{
				position: absolute;
				left: 360px;
				top: 145px;
			}
			#Riflessi{
				position: absolute;
				left: 360px;
				top: 167px;
			}
			#Mente{
				position: absolute;
				left: 360px;
				top: 190px;
			}		
			#Charme{
				position: absolute;
				left: 360px;
				top: 211px;
			}	
			#Intuito{
				position: absolute;
				left: 360px;
				top: 233px;
			}
			#login{
				left: 68px;
 				top: 276px;
 				width: 190px;
 				text-align: center;
 				font-size: 12pt;
			}
			#name{
				left: 52px;
				top: 386px;
			}
			#age{
				left: 312px;
				top: 365px;
			}
			#nat{
				left: 52px;
				top: 429px;
			}
			#gender{
				left: 324px;
				top: 407px;
			}
			#group{
				left: 52px;
				top: 473px;
			}
			#marr{
				left: 263px;
				top: 473px;
			}
			#lvl{
				left: 52px;
				top: 517px;
			}
			#date{
				left: 263px;
				top: 516px;
			}
			#xp_point{
				left: 52px;
				top: 559px;
			}
			#status{
				left: 52px;
				top: 602px;
			}
			#real_status{
				left: 265px;
				top: 602px;
				width: 200px;
			}
			#avatar{
				left: 65px;
				top: 61px;
				width: 200px;
				color: white;
				font-size: 10pt;
				text-align: center;
			}
			#descr{
				position: fixed;
				top: 0px;
				right: 0px;
				width: 50%;
				font-size: 6pt;
				background-color: lightyellow;
				padding: 5px;
				border: 3px dashed red;
				text-decoration: none;
				visibility: hidden;
			}
			#modifiable{
				position: absolute;
				top: 0;
				left: 0;
				visibility: hidden;
				width: 400px;
				height: 550px;
				border: solid 1px;
				overflow: auto;
			}
            
			#modifiable2{
				position: absolute;
				top: 0;
				left: 0;
				visibility: hidden;
				width: 400px;
				height: 180px;
				border: solid 1px;
				overflow: auto;
            }
            
            #modifiable3{
				position: absolute;
				top: 0;
				left: 0;
				visibility: hidden;
				width: 400px;
				height: 450px;
				border: solid 1px;
				overflow: auto;
			}
            
            #corp_mgmt{
				position: absolute;
				top: 220px;
				left: 0;
				width: 450px;
				height: 200px;
				
            }
			#visual{
				position: absolute;
				top: 0;
				left:0;
				width: 400px;
			}
			#visual_modify{
				visibility: hidden;
			}
			#visual2{
				position: absolute;
				top: 0;
				left:0;
				height: 180px;
				border: 1px solid;
				width: 400px;
				overflow: auto;
				
			}
			
			#corp_panel{
				width:400px;
			}
			#people{
                overflow: auto;
                width:400px;
                height:150px;
			}

			.dark_link{
				color: black;
			}

			a:hover{
				color: red;
			}

			.nohover:hover {
				text-decoration: none;
			}
			
			.counter{
				color: black;
				font-size: 11pt;
				font-weight: bold;
				position: absolute;
			}
			
			#spazio{
				top: 602px;
				left: 270px;			
			}
			
			#corp_name{
				position: absolute;
				top: 41px;
				left: 130px;
			}
			#corp_symbol{
				position: absolute;
				top: 73px;
				left: 400px;
			}
			#corp_charge{
				position: absolute;
				top: 80px;
				left: 120px;
			}
      #search_box{
        position: absolute;
				top: 38px;
				left: 300px;
			}
		</style>
		';

		if (isset($_GET['refreshparent'])) {
			echo '<script type="text/javascript">
				window.opener.location.href = window.opener.location.href;
			</script>'; 
		}


	echo '</head><body>
 			<div class="sheet" id="sheet'.$bg.'">
 			';


	echo $body;

	echo '
		<a class="nohover" href="./index.php?act=sheet&page=main&pg='.$pg.'">
			<div class="sheetnav" style="left: 313px; top: 638px;">&nbsp;</div>
		</a>
		<a class="nohover" href="./index.php?act=sheet&page=ability&pg='.$pg.'">
			<div class="sheetnav" style="left: 337px; top: 638px;">&nbsp;</div>
		</a>
		<a class="nohover" href="./index.php?act=sheet&page=background&pg='.$pg.'">
			<div class="sheetnav" style="left: 367px; top: 638px;">&nbsp;</div>
		</a>
		<a class="nohover" href="./index.php?act=sheet&page=equip&pg='.$pg.'">
			<div class="sheetnav" style="left: 399px; top: 638px;">&nbsp;</div>
		</a>
		<a class="nohover" href="./index.php?act=sheet&page=master&pg='.$pg.'">
			<div class="sheetnav" style="left: 424px; top: 638px;">&nbsp;</div>
		</a>
		<a class="nohover" href="./index.php?act=sheet&page=corp&pg='.$pg.'">
			<div class="sheetnav" style="left: 451px; top: 638px;">&nbsp;</div>
		</a>
		</div>
		</body>
			</html>';
}

function checkIfMaster(){
	global $x7s, $x7c;

	$value = $x7c->permissions['admin_panic'];

	return $value;
}

function checkIfModifySheet(){
	global $x7s, $x7c;

	$value = $x7c->permissions['sheet_modify'];

	return $value;
}

?>
