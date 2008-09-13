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

			
			
		print_sheet($body,$page);
	}
	
	
	function sheet_page_equip(){
		global $db,$x7c,$prefix,$x7s,$print;
		$pg=$_GET['pg'];
		$body='';
		$errore='';
		
		
		if(isset($_GET['moduse']) && checkIfMaster()){
			if(!isset($_POST['use']) || !isset($_POST['id'])){
				die("Bad form");
			}
			
			$db->DoQuery("UPDATE {$prefix}objects SET uses='$_POST[use]' WHERE id='$_POST[id]'");
			include('./lib/alarms.php');
			object_uses($pg,$_POST['id'],$_POST['use']);
			
			
		}
		
		if(isset($_GET['delete']) && ($x7s->username==$pg || checkIfMaster())){
			$db->DoQuery("DELETE FROM {$prefix}objects WHERE id='$_GET[delete]'");
		}
		
		if(isset($_GET['assign']) && ($x7s->username==$pg || checkIfMaster())){
				if(!isset($_POST['owner']) || !isset($_POST['id'])){
					die("Bad form");
				}
				$query = $db->DoQuery("SELECT count(*) AS cnt FROM {$prefix}users WHERE username='$_POST[owner]'");
				$row = $db->Do_Fetch_Assoc($query);
				
				if(!$row || $row['cnt']==0){
					$errore = "Utente non esistente";
				}
				
				$query = $db->DoQuery("SELECT * FROM {$prefix}objects WHERE id='$_POST[id]' AND owner='$pg'");
				$row = $db->Do_Fetch_Assoc($query);
				
				if(!$row || $row['id']==''){
					$errore = "Oggetto non esistente";
				}

				if($errore==''){
					//keys duplicates, and does not disappera from my sheet
					if($row['name']=="key_{$x7s->username}" || ($row['name']=="key_$_GET[pg]" && checkIfMaster())){
						if(!isset($_POST['grants']) || $_POST['grants'] <= 0 || $_POST['grants']== '')
							$_POST['grants'] = -1;
							
						$db->DoQuery("INSERT INTO {$prefix}objects
							(name, description, owner, uses, image_url)
							VALUES ('$row[name]', '$row[description]', '$_POST[owner]','$_POST[grants]','$row[image_url]')
						");
					}
					else{
						$db->DoQuery("UPDATE {$prefix}objects
							SET owner='$_POST[owner]'
							WHERE id='$_POST[id]' AND owner<>''"); //The last is only for protection to pattern objects
							
					}

					$errore="Oggetto assegnato correttamente\n";
					include('./lib/alarms.php');
					object_moves($_POST['owner'],$pg,$row['name']);
				}
				
			}
		
		$body .= "<script language=\"javascript\" type=\"text/javascript\">
				function confirmDrop(id){
					if(confirm(\"Vuoi davvero buttare l'oggetto?\")){
						location.href='index.php?act=sheet&page=equip&pg=$pg&delete='+id;
					}
					
				}
			</script>";
		
		$body.="<div id=\"objects\">\n";
		
		
		$query = $db->DoQuery("SELECT * FROM {$prefix}objects WHERE owner='$pg'");

		$room='';
		$more_form='';
		while($row=$db->Do_Fetch_Assoc($query)){
			$obj_name = $row['name'];
			$description = $row['description'];
			
			if(preg_match("/key_/", $row['name'])) {
				list($pre, $name)=split("key_", $row['name']);
				if($_GET['pg']==$x7s->username || checkIfMaster()){
					//we make clickable only key of my sheet
					if($row['name'] == "key_{$x7s->username}" || ($row['name']=="key_$_GET[pg]" && checkIfMaster())){
						//This is my key
						$more_form = '
							<tr>
								<td>Usi concessi (vuoto per illimitati):</td>
								<td><input type="text" name="grants" class="text_input" size=2></td>
							</tr>
						';
					}
					else{
						$remaining_uses = ($row['uses'] == -1) ? "illimitati" : $row['uses'];
						$description .= "<br>(Usi rimasti: $remaining_uses)";
					}
					
					$obj_name = '<a onClick="javascript: window.opener.location.href=\'index.php?act=frame&room='.$name.'&key_used='.$row['id'].'\'; window.location.reload(); "> Stanza di '.$name.'</a>';
					
				}
				else{
					$obj_name = "Stanza di $name";
				}
			}
			
			$body.= "<table width=100%> <tr> <td class=\"obj\"> <img width=100 height=100 src=\"$row[image_url]\" align=\"left\">
					<b>$obj_name</b>
					<p>$description</p> </td> </tr> </table>";
			
			if($pg==$x7s->username || checkIfMaster()){
				$body.="<form action=\"index.php?act=sheet&page=equip&pg=$pg&assign=1\" method=\"post\" name=\"object_assign\">
						<input type=\"hidden\" name=\"id\" value=\"$row[id]\">
       						<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
								<tr>
									<td>Dai a:</td>
									<td><input type=\"text\" name=\"owner\" class=\"text_input\"></td>
									<td><input type=\"submit\" class=\"button\" value=\"Dai\"></div></td>
									<td><input type=\"button\" class=\"button\" value=\"Butta\" onClick=\"javascript: confirmDrop($row[id])\"></td>
								</tr>
								$more_form
							</table>
					</form>";
			}
			
			if(checkIfMaster()){
				$body.="<form action=\"index.php?act=sheet&page=equip&pg=$pg&moduse=1\" method=\"post\" name=\"object_moduse\">
						<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
							<input type=\"hidden\" name=\"id\" value=\"$row[id]\">
							<tr>
									<td>Usi:</td>
									<td><input type=\"text\" name=\"use\" class=\"text_input\" size=2 value=\"$row[uses]\"></td>
									<td><input type=\"submit\" class=\"button\" value=\"Cambia\"></div></td>
							</tr>
						</table>
					";
				
				$body.="</form>\n";
			}
				
			$body.="<br><br>\n";
		}
		
		$body.="</div>\n";
		
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
			if(isset($_POST['master'])){
			
				if($pg!=$x7s->username){
					include('./lib/alarms.php');
					sheet_modification($pg,$_GET['page']);
				}
				
				$master = eregi_replace("\n","<Br>",$_POST['master']);
				$db->DoQuery("UPDATE {$prefix}users SET master='$master' WHERE username='$pg'");
			}
		}
		
		$query = $db->DoQuery("SELECT master FROM {$prefix}users WHERE username='$pg'");
		$row = $db->Do_Fetch_Assoc($query);
		
		if($row){
		
			if(checkIfMaster()){
				$body .= '<script language="javascript" type="text/javascript">
					mod=false;
					
					function modify(){
						if(!mod){
							mod=true;
							document.forms[0].elements["master"].disabled=false;
							
							document.forms[0].elements["master"].style.border="1px solid";
							
							document.forms[0].elements["aggiorna"].style.visibility="visible";
							document.forms[0].elements["mod_button"].style.visibility="hidden";
						}
					}
				</script>
				';
			
				$body .= '<form action="index.php?act=sheet&page=master&settings_change=1&pg='.$pg.'" method="post" name="sheet_form">
				';
				
				$master = eregi_replace("<Br>","\n",$row['master']);
				
				$body .= '<div class="indiv" id="master"><textarea name="master" id="master_text" class="sheet_text" autocomplete="off" disabled>'.$master.'</textarea></div>';
				
				$body .= "<div id=\"submit\"><INPUT name=\"aggiorna\" class=\"button\" type=\"SUBMIT\" value=\"Invia modifiche\" style=\"visibility: hidden;\"></div>
				<div id=\"modify\"><INPUT name=\"mod_button\" class=\"button\" type=\"button\" value=\"Modifica\" onClick=\"javascript: modify();\" style=\"visibility: visible;\"></div>";
				
				$body .="</form>\n";
		
			}
			else{
				$body .= '<div class="indiv" id="masterdiv">'.$row['master'].'</div>
			';
			}
	
		}
		
		return $body;
	}
	
	function sheet_page_background(){
		global $db,$x7c,$prefix,$x7s,$print;
		$pg=$_GET['pg'];
		$body='';
		
		
		if(isset($_GET['settings_change']) && ($pg==$x7s->username || checkIfMaster())){
			if(	isset($_POST['storia']) &&
				isset($_POST['fisici']) &&
				isset($_POST['psico'])	
				){
				
				$storia = $_POST['storia'];
				$fisici = $_POST['fisici'];
				$psico = $_POST['psico'];
				
				
				if($pg!=$x7s->username){
					include('./lib/alarms.php');
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
			
			if($pg==$x7s->username || checkIfMaster()){
			$body .= '<script language="javascript" type="text/javascript">
					mod=false;
					
					function modify(){
						if(!mod){
							mod=true;
							document.forms[0].elements["storia"].disabled=false;
							document.forms[0].elements["psico"].disabled=false;
							document.forms[0].elements["fisici"].disabled=false;
							
							document.forms[0].elements["storia"].style.border="1px solid";
							document.forms[0].elements["psico"].style.border="1px solid";
							document.forms[0].elements["fisici"].style.border="1px solid";
							
							document.forms[0].elements["aggiorna"].style.visibility="visible";
							document.forms[0].elements["mod_button"].style.visibility="hidden";
						}
					}
				</script>
				';
			
			$body .= '<form action="index.php?act=sheet&page=background&settings_change=1&pg='.$pg.'" method="post" name="sheet_form">
			';
			
			$body .= '<div class="indiv" id="storia"><textarea name="storia" id="storia_text" class="sheet_text" autocomplete="off">'.$row['storia'].'</textarea></div>
			';
			$body .= '<div class="indiv" id="fisici"><textarea name="fisici" id="fisici_text" class="sheet_text" autocomplete="off">'.$row['fisici'].'</textarea></div>
			';
			$body .= '<div class="indiv" id="psico"><textarea name="psico" id="psico_text" class="sheet_text" autocomplete="off">'.$row['psico'].'</textarea></div>
			';
			
			$body .= "<div id=\"submit\"><INPUT name=\"aggiorna\" class=\"button\" type=\"SUBMIT\" value=\"Invia modifiche\" style=\"visibility: hidden;\"></div>
				<div id=\"modify\"><INPUT name=\"mod_button\" class=\"button\" type=\"button\" value=\"Modifica\" onClick=\"javascript: modify();\" style=\"visibility: visible;\"></div>";
			}
			else{				
				$body .= '<div class="indiv" id="storia">'.eregi_replace("\n","<Br>",$row['storia']).'</div>
				';
				$body .= '<div class="indiv" id="fisici">'.eregi_replace("\n","<Br>",$row['fisici']).'</div>
				';
				$body .= '<div class="indiv" id="psico">'.eregi_replace("\n","<Br>",$row['psico']).'</div>
				';
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
			include('./lib/sheet_lib.php');
			
			if(isset($_GET['settings_change']) && ($pg==$x7s->username || checkIfMaster())){
				$ok = true;
				
				$query = $db->DoQuery("SELECT * FROM {$prefix}users WHERE username='$pg'");
				$row_user = $db->Do_Fetch_Assoc($query);
				$xp_avail=$row_user['xp']/$x7c->settings['xp_ratio'];
				$starting_xp = $x7c->settings['starting_xp'];
				
				if(!$row_user)
					die("Users not in database");
				
				$query = $db->DoQuery("SELECT u.ability_id AS ab_id, u.value AS value, a.dep AS dep, a.dep_val AS dep_val, a.name AS name
							FROM 	{$prefix}userability u, 
								{$prefix}ability a
							WHERE 
								u.ability_id = a.id AND
								username='$pg'
							ORDER BY a.name");
							
				$ability='';
				while($row = $db->Do_Fetch_Assoc($query)){
					$ability[$row['ab_id']]=$row;
					if(!isset($_POST[$row['ab_id']])){
						$ok = false;
						break;
					}
				}
				if(!checkIfMaster() && !isset($_POST['xp']))
					$ok = false;
				
				//Controllo se le abilità non sono state abbassate o superano il massimo
				//Il master fa quel che gli pare: niente controlli
				
				$tot_used=0;
				$lvl_gained=0;
				if(!checkIfMaster() && $ok){
					$max_ab = $x7c->settings['max_ab'];

					
					foreach($ability as $cur){
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

					
					if(!checkIfMaster()){
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
									if($_POST[$cur['ab_id']] > 0 && $_POST[$cur['dep']] < $_POST[$cur['ab_id']]){
										$errore .= "Errore, non puoi avere gradi in <b>".$cur['name']."</b> senza vere almeno pari gradi in b>".$ability[$cur['dep']]['name']."<br>";
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
						include('./lib/alarms.php');
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
								username='$pg'
							ORDER BY dep,name");
			
			while($row = $db->Do_Fetch_Assoc($query)){
				$ability[$row['ability_id']]=$row;
			}
			
			$body .='	<script language="javascript" type="text/javascript">
						var descr = new Array();
					
			
			';
			
			foreach($ability as $cur){
					$body .= "descr['$cur[ability_id]']=\"$cur[descr]\";\n";
			}
			
			$body .= 'function show_desc(el){
					document.getElementById("descr").innerHTML = descr[el];
					document.getElementById("descr").style.visibility = "visible";
				}

				function hide_desc(){
					document.getElementById("descr").style.visibility = "hidden";
				}
			</script>
			';
			
			
			$body .= "<div id=\"visual\"><table>";
			foreach($ability as $cur){
				if($cur['dep'] == ""){
					$body .= "<tr class=\"ab_text\"><td onMouseOver=\"javascript: show_desc('{$cur['ability_id']}')\" onMouseOut=\"javascript: hide_desc()\" class=\"ab_text\">".$cur['name']."</td><td>";
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
								$body .= "<tr><td class=\"ab_text\" onMouseOver=\"javascript: show_desc('{$cur2['ability_id']}')\" onMouseOut=\"javascript: hide_desc()\" class=\"ab_text\">&nbsp;&nbsp;&nbsp;".$cur2['name']."</td><td>";
							
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
			
			if(($xp!=0 && $pg==$x7s->username) || checkIfMaster()){
				$max_ab = $x7c->settings['max_ab'];
				
				if(!checkIfMaster()){
					$body .='	<script language="javascript" type="text/javascript">
								
								'.ability_script($max_ab).'
								
								function do_form_refresh(ab_name){
									document.sheet_form[ab_name+"_display"].value = document.sheet_form[ab_name].value;
									document.sheet_form["xp_display"].value = document.sheet_form["xp"].value;
								}';
				}
				//Master can everithing wothout controls
				else{
					$body .='	<script language="javascript" type="text/javascript">
								function add(ab_name){
									var value = parseInt(document.sheet_form[ab_name].value);
									
									document.sheet_form[ab_name].value = value + 1;
										
									do_form_refresh(ab_name);
								}								
								
								
								function sub(ab_name){
									var value = parseInt(document.sheet_form[ab_name].value);
									
									document.sheet_form[ab_name].value = value - 1;
									
									do_form_refresh(ab_name);
									
									
								}
								
								function do_form_refresh(ab_name){
									document.sheet_form[ab_name+"_display"].value = document.sheet_form[ab_name].value;
								}';
				
				}
							
				$body .= '
								function modify(){
									document.getElementById("visual").style.visibility="hidden";
									document.getElementById("modifiable").style.visibility="visible";
									document.getElementById("modify").style.visibility="hidden";
								}
	
						</script>
				<div id="modifiable"><form action="index.php?act=sheet&page=ability&settings_change=1&pg='.$pg.'" method="post" name="sheet_form">
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
						$body .="
						<input type=\"hidden\" name=\"".$cur['ability_id']."_leaf\" value=\"";
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
				
				$body .= "	<tr><td><INPUT name=\"aggiorna\" class=\"button\" type=\"SUBMIT\" value=\"Invia modifiche\" $disabled></td></tr></table>";
				
				if(!checkIfMaster()){
					$body .='<div id="#xp" align="center">Punti esperienza:<br>
							<input type="text" size="2" name="xp_display" value="'.$xp.'" style="text-align: right; color: blue;" disabled>
							<input type="hidden" name="xp" value="'.$xp.'"></form></div></div>
						';
				}
			}
			
			$body.="<div id=\"descr\"> </div></div>";

			if(($xp!=0 && $pg==$x7s->username) || checkIfMaster()){
				$body .= "<div id=\"modify\"><INPUT name=\"mod_button\" class=\"button\" type=\"button\" value=\"Modifica\" onClick=\"javascript: modify();\"></div>";
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
			$char;
			
	
			if(isset($_GET['settings_change']) && checkIfMaster()){
							
				//We are modifiyng character sheet
				if(isset($_POST['name']) && 
					isset($_POST['age'])&&
					isset($_POST['nat']) &&
					isset($_POST['marr']) &&
					isset($_POST['gender']) &&
					isset($_POST['avatar_in'])) {
					
					
					if($_POST['name']==''){
						$ok = false;
						$errore .= "Non hai specificato il nome<br>";
					}
					if($_POST['age']=='' || $_POST['age']<16){
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
					//Ora posso aggiornare
					if(isset($_POST['name']) &&
						isset($_POST['age'])&&
						isset($_POST['nat']) &&
						isset($_POST['marr']) &&
						isset($_POST['gender']) &&
						isset($_POST['avatar_in'])
						){


						if($pg!=$x7s->username){
							include('./lib/alarms.php');
							sheet_modification($pg,$_GET['page']);
						}

						$db->DoQuery("UPDATE {$prefix}users SET
							name='$_POST[name]',
							age='$_POST[age]',
							nat='$_POST[nat]',
							marr='$_POST[marr]',
							gender='$_POST[gender]',
							avatar='$_POST[avatar_in]'
							WHERE username='$pg'");
						}

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

					if(checkIfMaster()){
						if(isset($_POST['xp']) &&
							isset($_POST['info']) ){
							$db->DoQuery("UPDATE {$prefix}users
									SET 	xp='$_POST[xp]',
										info='$_POST[info]'
									 WHERE username='$pg'");
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
					

			}
			else if(isset($_GET['settings_change']) && !checkIfMaster() && $x7s->username==$pg){
			
				if(isset($_POST['avatar_in'])){
					$db->DoQuery("UPDATE {$prefix}users SET
						avatar='$_POST[avatar_in]'
						WHERE username='$pg'");
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
					}
				}
			}
			
			
			$query = $db->DoQuery("SELECT * FROM {$prefix}users WHERE username='$pg'");
			$row_user = $db->Do_Fetch_Assoc($query);
			if(!$row_user){
				die("User not in Database");
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
									
									document.getElementById("pwd1").style.visibility="visible";
									document.getElementById("pwd2").style.visibility="visible";
									
									
									document.getElementById("avatar").innerHTML="<br><br><br>Specifica l\'URL del tuo avatar nel campo qui sopra";

								document.forms[0].elements["info"].style.color="blue";
								document.forms[0].elements["info"].style.border="1px solid";
								document.forms[0].elements["info"].style.background="white";
								document.forms[0].elements["info"].disabled=false;

								document.forms[0].elements["xp"].style.color="blue";
								document.forms[0].elements["xp"].style.background="white";
								document.forms[0].elements["xp"].disabled=false;
								}
							}
					
					</script>';
			}
			
			//Here everithing tha is untouchable by anyone
			$body .= "
				<div class=\"indiv\" id=\"login\">$row_user[username]</div>
				<div class=\"indiv\" id=\"group\">$group</div>
				<div class=\"indiv\" id=\"date\">$date</div>
				<div class=\"indiv\" id=\"lvl\">$row_user[lvl]</div>
				<div class=\"indiv\" id=\"avatar\">
			";
			
			if($row_user['avatar']!='')
				$body .= "<img src=\"$row_user[avatar]\" width=200 height=200 />";
			
			$body.='</div>';
			
			if(!checkIfMaster()){
				$body.= "
					<div class=\"indiv\" id=\"status\">$row_user[info]</div>
					<div class=\"indiv\" id=\"xp_point\">$row_user[xp]</div>
				";
			}
			
			
							
			$query_char = $db->DoQuery("SELECT uc.value AS value, c.name AS name, c.id AS id
								FROM 	{$prefix}usercharact uc,
									{$prefix}characteristic c
								WHERE	c.id=uc.charact_id
								  AND 	uc.username='$pg'");
								  
			while($row_ch = $db->Do_Fetch_Assoc($query_char)){
					$charact[$row_ch['id']]=$row_ch;
			}
			
			
			if(!checkIfMaster()){
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
				
				
				
			}
			else{
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
			
				
				$body.='<form action="index.php?act=sheet&settings_change=1&pg='.$pg.'" method="post" name="sheet_form">';


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
					
					if($row_user['marr']=="Celibe")
						$marr_opt="<option value=\"Libero\" selected>Libero</option>
						<option value=\"Sposato\">Sposato</option>";
					else
						$marr_opt="<option value=\"Libero\">Libero</option>
						<option value=\"Sposato\" selected>Sposato</option>";
				}
				else{
					$male="";
					$female="selected";	
					
					if($row_user['marr']=="Nubile")
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
					<div class=\"indiv\" id=\"avatar\"><input class=\"sheet_input\" type=\"text\" name=\"avatar_in\" value=\"$row_user[avatar]\" size=\"15\" style=\"visibility: hidden; font-size:10pt;\" disabled /></div>
					";
					
				$body.= "
					<div class=\"indiv\" id=\"status\"><input class=\"sheet_input\" type=\"text\" name=\"info\" value=\"$row_user[info]\" size=\"30\" disabled /></div>
					<div class=\"indiv\" id=\"xp_point\"><input class=\"sheet_input\" type=\"text\" id=\"xp\" name=\"xp\" value=\"$row_user[xp]\" size=\"10\" disabled /></div>
				";
						
				
				
				$body .= "<div id=\"submit\"><INPUT name=\"aggiorna\" class=\"button\" type=\"SUBMIT\" value=\"Invia modifiche\" style=\"visibility: hidden;\"></div>
				<div id=\"modify\"><INPUT name=\"mod_button\" class=\"button\" type=\"button\" value=\"Modifica\" onClick=\"javascript: modify();\" style=\"visibility: visible;\"></div></form>";
		
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
				$body .= "<div class=\"indiv\" id=\"avatar\"><input class=\"sheet_input\" type=\"text\" name=\"avatar_in\" value=\"$row_user[avatar]\" size=\"15\" style=\"visibility: hidden; font-size:10pt;\" disabled /></div>\n";
				
				$body .= "<div class=\"indiv\" id=\"pwd1\" style=\"visibility: hidden;\">Nuova password:<br><input class=\"sheet_input\" type=\"password\" name=\"pwd1\" size=\"10\" style=\"visibility: hidden; font-size:10pt;\" disabled /></div>\n";
				$body .= "<div class=\"indiv\" id=\"pwd2\" style=\"visibility: hidden;\">Ripeti nuova password:<br><input class=\"sheet_input\" type=\"password\" name=\"pwd2\" size=\"10\" style=\"visibility: hidden; font-size:10pt;\" disabled /></div>\n";
				
				$body .= "<div id=\"submit\"><INPUT name=\"aggiorna\" class=\"button\" type=\"SUBMIT\" value=\"Invia modifiche\" style=\"visibility: hidden;\"></div>
				<div id=\"modify\"><INPUT name=\"mod_button\" class=\"button\" type=\"button\" value=\"Modifica\" onClick=\"javascript: modify();\" style=\"visibility: visible;\"></div></form>";
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
			#storia_text{
				height: 250px;
				width: 430px;
			}
			#storia{
				top: 80px;
				left: 30px;
				width: 430px;
				height: 250px;
			}
			#fisici_text{
				width: 200px;
				height: 250px
				width: 200px;
				height: 250px;
			}
			#fisici{
				top: 370px;
				left: 30px;
				width: 200px;
				height: 250px;
			}
			#master_text{
				width: 400px;
				height: 550px;
			}
			#master{
				top: 60px;
				left: 50px;
			}
			#masterdiv{
				width: 400px;
				height: 550px;
				top: 60px;
				left: 50px;
				overflow: auto;
			}
			#psico_text{
				width: 200px;
				height: 250px;
			}
			#psico{
				top: 370px;
				left: 250px;
				width: 200px;
				height: 250px;
			}
			.sheet_text{
				background: transparent;
				overflow: auto;
				font-size: 12pt;
				font-weight: bold;
				color: black;
				border: 0;
			}
			.ab_text{
				font-size: 10pt;
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
				left: 30px;
				top: 630px;
			}
			#modify{
				position: absolute;
				left: 130px;
				top: 630px;
			}
			#ability{
				position: absolute;
				left: 50px;
				top: 70px;
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
			#Fortuna{
				position: absolute;
				left: 400px;
				top: 255px;
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
				font-size: 10pt;
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
			}
			#visual{
				posotion: absolute;
				top: 0;
				left:0;
			}	
		</style>
		';
		

		
		echo '</head><body>
 			<div class="sheet" id="sheet'.$bg.'">
 			';
 			
		
		echo $body;
		
		echo '
		<a href="./index.php?act=sheet&page=main&pg='.$pg.'"><div class="sheetnav" style="left: 345px; top: 638px;"></div></a>
		<a href="./index.php?act=sheet&page=ability&pg='.$pg.'"><div class="sheetnav" style="left: 370px; top: 638px;"></div></a>
		<a href="./index.php?act=sheet&page=background&pg='.$pg.'"><div class="sheetnav" style="left: 398px; top: 638px;"></div></a>
		<a href="./index.php?act=sheet&page=master&pg='.$pg.'"><div class="sheetnav" style="left: 428px; top: 638px;"></div></a>
		<a href="./index.php?act=sheet&page=equip&pg='.$pg.'"><div class="sheetnav" style="left: 456px; top: 638px;"></div></a>
		</div>
		</body>
			</html>';
	}
	
	function checkIfMaster(){
		global $x7s, $x7c;
		
		$value = $x7c->permissions['admin_panic'];
		
		return $value;
	}

?>