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

	function buildpg_main(){
		global $x7s;
		
		$page='';

		if(!$x7s->sheet_ok){
			$body = buildpg();
		}
		else{
			header("Location: index.php");
		}
			
		print_page($body);
	}


	function repair_db(){
		global $db, $prefix;

		echo "Fecthing users<br>";
		$users = $db->DoQuery("SELECT username FROM {$prefix}users");
		
		echo "Fetching abilities<br>";
		$q_abilities = $db->DoQuery("SELECT id FROM {$prefix}ability");
		$abilities = array();
		$i=0;
		
		while($row = $db->Do_Fetch_Assoc($q_abilities)){
			$ability[$i++] = $row['id'];
		}

		echo "Repairing users<br>";
		while($row = $db->Do_Fetch_Assoc($users)){
			foreach($ability as $ab){
				echo "Checking $ab for $row[username]<br>";
				$unique = $db->DoQuery("SELECT count(*) as cnt FROM {$prefix}userability u, {$prefix}ability a WHERE  u.ability_id=a.id AND username = '$row[username]' AND ability_id = '$ab' AND corp=''");
				$exist = $db->Do_Fetch_Assoc($unique);

				if($exist['cnt']==0){
					echo "Missing $ab for $row[username]<br>";
					$db->DoQuery("INSERT INTO {$prefix}userability (ability_id, username, value) VALUES
										('$ab', '$row[username]', '0')");
				}
			}
		}
		die('Done');
	}
	
	
	function buildpg(){
			global $txt, $x7c, $x7s, $print, $db, $prefix;
			$errore='';
			$ok=true;
			$pg=$x7s->username;
			include('./lib/sheet_lib.php');
			
			if(isset($_GET['build'])){
				$query = $db->DoQuery("SELECT * FROM {$prefix}users WHERE username='$pg'");
				$row_user = $db->Do_Fetch_Assoc($query);

				$starting_xp = $x7c->settings['starting_xp'];
				$xp_avail=$starting_xp;
				
				
				if(!$row_user)
					die("Users not in database");
				
				$query = $db->DoQuery("SELECT u.ability_id AS ab_id, u.value AS value, a.dep AS dep, a.dep_val AS dep_val, a.name AS name
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
				if(!isset($_POST['xp']))
					$ok = false;

				//Controllo se le abilit� non sono state abbassate o superano il massimo
				//Il master fa quel che gli pare: niente controlli
				
				$tot_used=0;
				$lvl_gained=0;
				if($ok){
					$max_ab = $x7c->settings['max_ab_constr'];
					
					foreach($ability as $cur){
						if($cur['value'] != $_POST[$cur['ab_id']]){
							$new_value = $_POST[$cur['ab_id']];

							while($new_value > $cur['value']){
								$tot_used+= $new_value;
								$new_value--;
							}
							
							if($cur['value'] > $_POST[$cur['ab_id']]){
								$errore .= "Errore, non puoi scendere sotto il valore attuale<br>";
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
					
					
					if($tot_used > $xp_avail){
						$errore .= "Hai usato troppi PX<br>";
						$ok = false;
					}
						
					if($tot_used < $starting_xp){
						$errore .= "Non hai usato tutti i punti abilit&agrave; $tot_used/$starting_xp<br>";
						$ok = false;
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

				if(isset($_POST['name']) && 
					isset($_POST['age'])&&
					isset($_POST['nat']) &&
					isset($_POST['marr']) &&
					isset($_POST['gender'])) {
					
					
					if($_POST['name']==''){
						$ok = false;
						$errore .= "Non hai specificato il nome<br>";
					}
					if($_POST['surname']==''){
						$ok = false;
						$errore .= "Non hai specificato il cognome<br>";
                                        } 
					if($_POST['age']=='' || $_POST['age']<16){
						$ok = false;
						$errore .= "Et&agrave; non valida... deve essere maggiore di 16<br>";
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
				
				
				if (isset($_POST['ch']) && $_POST['ch']>0){
					$errore .="Non hai usato tutti i tuoi punti caratteristica<br>";
				}
				else{
					$query = $db->DoQuery("SELECT * FROM {$prefix}characteristic ORDER BY name");
								
					$char='';
					while($row = $db->Do_Fetch_Assoc($query)){
						$char[$row['id']]=$row;
					}
					
					$total_char=0;
					//Controllo se le caratteristiche non sono state abbassate o superano il massimo
					foreach($char as $cur){
						if(!isset($_POST[$cur['id']])){
							$ok = false;
							break;
						}

						$total_char+=$_POST[$cur['id']];
						if($_POST[$cur['id']] < $x7c->settings['min_ch']){
							$errore .= "Errore, non puoi abbassare le caratteristiche sotto il {$x7c->settings['min_ch']}<br>";
							$ok = false;
							break;
						}
						elseif($_POST[$cur['id']] > $x7c->settings['max_ch']){
							$errore .= "Errore, le caratteristiche non possono superare il valore massimo {$x7c->settings['max_ch']}<br>";
							$ok = false;
							break;
						}
					}

					if($total_char > $x7c->settings['starting_ch']){
						$errore .= "Errore, hai usato troppi punti caratteristica<br>";
						$ok = false;
					}
				}

					
				if($ok){
					//Ora posso aggiornare

					$newxp = $row_user['xp']-($tot_used * $x7c->settings['xp_ratio']);
					$newlvl = 1;

					if($_POST['gender'] == 0)
						if($_POST['marr'] == 0)
							$marr="Libero";
						else
							$marr="Sposato";
					else
						if($_POST['marr'] == 0)
							$marr="Libera";
						else
							$marr="Sposata";
						

					$db->DoQuery("UPDATE {$prefix}users SET
								name='$_POST[name] $_POST[surname]',
								age='$_POST[age]',
								nat='$_POST[nat]',
								marr='$marr',
								gender='$_POST[gender]'
								WHERE username='$pg'");

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
					
					$db->DoQuery("UPDATE {$prefix}users
									SET sheet_ok='1'
									WHERE username='$pg'");
						
					header('Location: ./index.php');
					return;

				}

			}
			
				
			$max_ab = $x7c->settings['max_ab_constr'];

			//Characteristics
			$query_char = $db->DoQuery("SELECT * FROM {$prefix}characteristic");

         		$ch_descr_vector="\n";
			while($row_ch = $db->Do_Fetch_Assoc($query_char)){
				$charact[$row_ch['id']]=$row_ch;
				$ch_descr_vector .= "\t\t\t\t\t\tdescr['$row_ch[id]']=\"$row_ch[descr]\";\n";
			}


			$ch_fields ='';
			foreach($charact as $cur_ch){
				$ch_fields .= "<tr onMouseOver=\"javascript: show_desc('{$cur_ch['id']}');\" onMouseOut=\"javascript: hide_desc();\">
					<td>{$cur_ch['name']}:</td><td><input class=\"button\" type=\"button\" value=\"-\" onMouseDown=\"return sub_ch('{$cur_ch['id']}');\">
					<input type=\"text\" name=\"{$cur_ch['id']}_display\" value=\"{$x7c->settings['min_ch']}\" size=\"2\" style=\"text-align: right; color: blue;\" disabled/>
						<input type=\"hidden\" name=\"{$cur_ch['id']}\" value=\"{$x7c->settings['min_ch']}\"/>
						<input class=\"button\" type=\"button\" value=\"+\" onMouseDown=\"return add_ch('{$cur_ch['id']}');\">\n</td></tr>";
			}

			$ch = $x7c->settings['starting_ch'] - (($x7c->settings['min_ch'])*sizeof($charact));



			//Ability
			$xp = $x7c->settings['starting_xp'];

			$max_ab = $x7c->settings['max_ab_constr'];

			$query = $db->DoQuery("SELECT * FROM 	{$prefix}userability,
							{$prefix}ability
						WHERE
							ability_id=id AND
							username='{$x7s->username}'
						ORDER BY dep,name");


			while($row = $db->Do_Fetch_Assoc($query)){
				$ability[$row['ability_id']]=$row;
			}

			$ab_descr_vector="\n";
			foreach($ability as $cur){
				$ab_descr_vector .= "\t\t\t\t\t\tdescr['$cur[ability_id]']=\"$cur[descr]\";\n";
			}


			$ab_fields ='';
			foreach($ability as $cur){
				if($cur['dep'] == ""){
					$ab_fields .= "<tr onMouseOver=\"javascript: show_desc('{$cur['ability_id']}');\" onMouseOut=\"javascript: hide_desc();\">";
					$ab_fields .= "<td style=\"font-weight: bold;\">".$cur['name']."</td>
					<td><input class=\"button\" type=\"button\" value=\"-\" onClick=\"return sub('{$cur['ability_id']}');\">
					<input type=\"text\" name=\"{$cur['ability_id']}_display\" value=\"{$cur['value']}\" size=\"2\" style=\"text-align: right; color: blue;\" disabled/>
					<input type=\"hidden\" name=\"{$cur['ability_id']}\" value=\"{$cur['value']}\"/>
					<input class=\"button\" type=\"button\" value=\"+\" onClick=\"return add('{$cur['ability_id']}');\">
					<input type=\"hidden\" name=\"".$cur['ability_id']."_min\" value=\"{$cur['value']}\">
					<input type=\"hidden\" name=\"".$cur['ability_id']."_name\" value=\"{$cur['name']}\">
					<input type=\"hidden\" name=\"".$cur['ability_id']."_dep\" value=\"{$cur['dep']}\">";

					$query = $db->DoQuery("SELECT id FROM {$prefix}ability WHERE dep='{$cur['ability_id']}' ORDER BY name");
					$ab_fields .="
					<input type=\"hidden\" name=\"".$cur['ability_id']."_leaf\" value=\"";
					while($leaf = $db->Do_Fetch_Assoc($query)){
						$ab_fields .= $leaf['id']."|";
					}
					$ab_fields .= "\">";

					$ab_fields .= "</td></tr>\n";

					foreach($ability as $cur2){
						if($cur2['dep'] == $cur['ability_id']){
							$ab_fields .= "<tr onMouseOver=\"javascript: show_desc('{$cur2['ability_id']}')\">\n";
							$ab_fields .= "<td style=\"font-weight: bold;\">&nbsp;&nbsp;&nbsp;".$cur2['name']."</td>
								<td><input class=\"button\" type=\"button\" value=\"-\" onMouseDown=\"return sub('{$cur2['ability_id']}');\">
								<input type=\"text\" name=\"{$cur2['ability_id']}_display\" value=\"{$cur2['value']}\" size=\"2\" style=\"text-align: right; color: blue;\" disabled/>
								<input type=\"hidden\" name=\"{$cur2['ability_id']}\" value=\"{$cur2['value']}\"/>
								<input class=\"button\" type=\"button\" value=\"+\" onMouseDown=\"return add('{$cur2['ability_id']}');\">
								<input type=\"hidden\" name=\"".$cur2['ability_id']."_min\" value=\"{$cur2['value']}\">
								<input type=\"hidden\" name=\"".$cur2['ability_id']."_name\" value=\"{$cur2['name']}\">
								<input type=\"hidden\" name=\"".$cur2['ability_id']."_dep\" value=\"{$cur2['dep']}\">";

							if($cur2['dep']!= ""){
								$ab_fields .="
								<input type=\"hidden\" name=\"".$cur2['ability_id']."_dep_val\" value=\"{$cur2['dep_val']}\">";
							}
							$ab_fields .= "</td></tr>\n";
						}
					}
				}
			}


			$body ='
			<script language="javascript" type="text/javascript">
						var descr=Array();
						'.$ab_descr_vector.
						$ch_descr_vector
						.'

						descr[\'naz\']="La Nazionalit&agrave; indica lo stato dal quale provenite. Italiana, statunitense, russa... Facile dai!";
						descr[\'nome\']="Il nome completo del pg &egrave; inteso come il vero nome anagrafico del personaggio, quello \"legale\" ... Quindi il <b>NICK</b> � quello che appare in chat (scorpion, butterfly, volpe quel che vi pare...) ma questo &egrave; la Vera Identit&agrave; del player. Non sono quindi ammessi nomi impossibili (Leo-99 o Topolina 74) o cretini (the undead lord o Diabolik), come nemmeno nomi fantasy (Gandalf il bianco, Elandriel Blacwisdom o cose simili)... Insomma siate veritieri...";
						descr[\'sesso\']="Sesso? Spesso e volentieri grazie... Non vi spiego cosa sia, tanto il men&ugrave; a tendina non vi permetter&agrave; grossi errori!";
						descr[\'civile\']="Indica se siete sposati o single.";

						function add_ch(ch_name){
							var value = parseInt(document.sheet_form[ch_name].value);
							var ch = parseInt(document.sheet_form["ch"].value);

							if (ch > 0 && value < 12){
								document.sheet_form[ch_name].value = value + 1;
								document.sheet_form["ch"].value = ch - 1;
							}
							do_ch_form_refresh(ch_name);
						}

						function sub_ch(ch_name){
							var value = parseInt(document.sheet_form[ch_name].value);
							var ch = parseInt(document.sheet_form["ch"].value);

							if (value > 4){
								document.sheet_form[ch_name].value = value -1;
								document.sheet_form["ch"].value = ch + 1;
							}
							do_ch_form_refresh(ch_name);
						}

						function do_ch_form_refresh(ch_name){
							document.sheet_form[ch_name+"_display"].value = document.sheet_form[ch_name].value;
							document.sheet_form["ch_display"].value = document.sheet_form["ch"].value;
							enable_send();
						}

						'.ability_script($max_ab).'
						function do_form_refresh(ab_name){
							document.sheet_form[ab_name+"_display"].value = document.sheet_form[ab_name].value;
							document.sheet_form["xp_display"].value = document.sheet_form["xp"].value;
							enable_send();
						}

						function show_desc(el){
							document.getElementById("help").innerHTML = descr[el];
							document.getElementById("help").style.visibility = "visible";
						}

						function hide_desc(){
							document.getElementById("help").style.visibility = "hidden";
							document.getElementById("help").innerHTML = "";
						}

						function enable_send(){
							var xp=document.sheet_form["xp"].value;
							var ch=document.sheet_form["ch"].value;
							var send = document.getElementById("send");

							if(xp > 0 || ch > 0){
								send.disabled = true;
							}
							else{
								send.disabled = false;
							}

						}

				</script>

			<p>Completa la scheda del tuo personaggio<BR>
			 Per proseguire devi completare tutti i campi e usare tutti i punti abilit&agrave; e caratteristica.
			 </p>
			<p class="error_msg">'.$errore.'</p>
			<form action="index.php?act=buildpg&build" method="post" name="sheet_form">
				<div class="overflow" id="all">
				<table>
					<tr onMouseOver="javascript: show_desc(\'nome\');" onMouseOut="javascript: hide_desc();">
						<td>Nome:</td>
						<td><input class="sheet_input" type="text" name="name" size="16" /></td>
					</tr>
					
					<tr onMouseOver="javascript: show_desc(\'nome\');" onMouseOut="javascript: hide_desc();">
						<td>Cognome:</td>
						<td><input class="sheet_input" type="text" name="surname" size="16" /></td>
					</tr>

					<tr>
						<td>Et&agrave;</td>
						<td><input class="sheet_input" type="text" name="age" value="16" size="2" style="text-align: right;" /></td>
					</tr>

					<tr onMouseOver="javascript: show_desc(\'naz\');" onMouseOut="javascript: hide_desc();">
						<td>Nazionalit&agrave;</td>
						<td><input class="sheet_input" type="text" name="nat" size="16" /></td>
					</tr>
					
					</tr>
					<tr onMouseOver="javascript: show_desc(\'sesso\');" onMouseOut="javascript: hide_desc();"><td>Sesso:</td>
						<td>
						<select class="button" name="gender">
											<option value="0">M</option>
											<option value="1">F</option>
						</select>
						</td>
					</tr>

					<tr onMouseOver="javascript: show_desc(\'civile\');" onMouseOut="javascript: hide_desc();">
						<td>Stato civile:</td>
						<td>
							<select class="button" name="marr">
								<option value="0">Libero</option>
								<option value="1">Sposato</option>
						</select>
						</td>
					</tr>

					<tr><td colspan=2><hr></td></tr>
					
					<tr>
						<td>Punti caratteristica:</td> <td><input type="text" size="2" name="ch_display" value="'.$ch.'" style="text-align: right; color: blue;" disabled> <input type="hidden" name="ch" value="'.$ch.'"> </td>
					</tr>
					'.$ch_fields.'

					<tr>
						<td><INPUT id="send" name="aggiorna" class="button" type="SUBMIT" value="Crea personaggio" disabled></td>
					</tr>
				</table>
				</div>
				
				<div class="overflow" id="ability">
				<table>
					<tr>
						<td>Punti abilit&agrave;:</td><td><input type="text" size="2" name="xp_display" value="'.$xp.'" style="text-align: right; color: blue;" disabled>
						<input type="hidden" name="xp" value="'.$xp.'"></td>
					</tr>

					'.$ab_fields.'

				</table>
				</div>
			</form>
			<div id="help">help</div>
			';

			return $body;
			
	}
	
	

	function print_page($body){
		global $print,$x7c,$x7s;
		
		
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
		echo "<html dir=\"$print->direction\"><head><title>{$x7c->settings['site_name']} Creazione PG</title>";
		echo $print->style_sheet;
		echo $print->ss_mini;
		echo $print->ss_chatinput;
		echo $print->ss_uc;
		
		$sfondo="./graphic/sfondoposta.jpg";
		
		if($x7c->settings['panic'])
	 		$sfondo="./graphic/sfondopostaobscure.jpg";
		
		$mail_style = '
		<style type="text/css">
			td{
				color: white;
			}

			#help{
				position: fixed;
				left: 620px;
				top: 70px;;
				font-size: 10pt;
				border: solid 1px white;
				width:300px;
				margin-left: 5px;
				padding: 5px;
				visibility: hidden;
			}

			#all{
				position: relative;
				float: left;
				top: 0;
			}

			#ability{
				position: relative;
				float: left;
				margin-left: 20px;
			}

			p{
				font-size: 10pt;
				color: pink;
			}

			.error_msg{
				color: red;
				font-weight: bold;
			}

			#buildpg {
				margin-left: 50px;
				overflow: auto;
				height: 100%;
			}
                        .overflow {
				overflow: auto;
                        }

			input[disabled]{color: #555555;}

			
		</style>
		';
		
		echo $mail_style;
		
		echo '</head><body>
 			<div id="buildpg">
 				<div id="inner_buildpg">
 			';
 			
		
		echo $body;
		echo '
			</div>
		</div>
		</body>
			</html>';
	}


?>