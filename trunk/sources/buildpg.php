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
		
		$body = buildpg_mail();
			
			
		print_page($body);
	}
	
	
	function buildpg_mail(){
			global $txt, $x7c, $x7s, $print, $db, $prefix;
			
			if(isset($_GET['build'])){
			
			}
			
			else{
				
				$max_ab = $x7c->settings['max_ab_constr'];

				//Characteristics
				$query_char = $db->DoQuery("SELECT * FROM {$prefix}characteristic");
								  
				while($row_ch = $db->Do_Fetch_Assoc($query_char)){
					$charact[$row_ch['id']]=$row_ch;
				}
				

				$ch_fields ='';
				foreach($charact as $cur_ch){
					$ch_fields .= "<tr><td>{$cur_ch['name']}:</td><td><input class=\"button\" type=\"button\" value=\"-\" onMouseDown=\"return sub_ch('{$cur_ch['id']}');\">
						<input type=\"text\" name=\"{$cur_ch['id']}_display\" value=\"{$x7c->settings['min_ch']}\" size=\"2\" style=\"text-align: right; color: blue;\" disabled/>
							<input type=\"hidden\" name=\"{$cur_ch['id']}\" value=\"{$x7c->settings['min_ch']}\"/>
							<input class=\"button\" type=\"button\" value=\"+\" onMouseDown=\"return add_ch('{$cur_ch['id']}');\">\n</td></tr>";
				}

				$ch = $x7c->settings['starting_ch'] - (($x7c->settings['min_ch'])*sizeof($charact));



				//Ability
				$xp = floor($x7c->settings['starting_xp']/$x7c->settings['xp_ratio']);
				
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
						$ab_fields .= "<tr onMouseOver=\"javascript: show_desc('{$cur['ability_id']}')\">";
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

							'.$ab_descr_vector.'
							
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
							
								
								
							function add(ab_name){
								var value = parseInt(document.sheet_form[ab_name].value);
								var xp = parseInt(document.sheet_form["xp"].value);
								
								if (xp > 0 && value < '.$max_ab.'){
								
									dep = document.sheet_form[ab_name+"_dep"].value;
										
									if(dep != ""){
										dep_val = parseInt(document.sheet_form[ab_name+"_dep_val"].value);
										dep_act_val = parseInt(document.sheet_form[dep].value);
										if(dep_act_val >= dep_val){
											document.sheet_form[ab_name].value = value + 1;
											document.sheet_form["xp"].value = xp - 1;
										}
										else{
											alert("Non puoi alzare \""+document.sheet_form[ab_name+"_name"].value+"\" senza avere almeno "+dep_val+" gradi in \""+document.sheet_form[dep+"_name"].value+"\"");
										}
									}
									else{
										document.sheet_form[ab_name].value = value + 1;
										document.sheet_form["xp"].value = xp - 1;
									}
									
									do_form_refresh(ab_name);
								}								
							}
							
							function sub(ab_name){
								var value = parseInt(document.sheet_form[ab_name].value);
								var min = parseInt(document.sheet_form[ab_name+"_min"].value);
								
								if (value > min){
									document.sheet_form[ab_name].value = value - 1;
									var xp = parseInt(document.sheet_form["xp"].value);
									document.sheet_form["xp"].value = xp + 1;
									leafs = "";
									if(document.sheet_form[ab_name+"_dep"].value == ""){
										leafs = document.sheet_form[ab_name+"_leaf"].value;
									}
									
									if(leafs != ""){
										splitted = leafs.split("|");
										for (i in splitted){
											if(splitted[i]!=""){
												back_xp = parseInt(document.sheet_form[splitted[i]].value) - parseInt(document.sheet_form[splitted[i]+"_min"].value);
												
												document.sheet_form[splitted[i]].value = document.sheet_form[splitted[i]+"_min"].value;
												xp = parseInt(document.sheet_form["xp"].value);
												document.sheet_form["xp"].value = xp + back_xp;
												
												do_form_refresh(splitted[i]);
											}
										}
									}
									do_form_refresh(ab_name);
								}
								
							}
							
							function do_form_refresh(ab_name){
								document.sheet_form[ab_name+"_display"].value = document.sheet_form[ab_name].value;
								document.sheet_form["xp_display"].value = document.sheet_form["xp"].value;
								enable_send();
							}

							function show_desc(el){
								document.getElementById("descr").innerHTML = descr[el];
							}

							function enable_send(){
								var xp=document.sheet_form["xp"].value;
								var ch=document.sheet_form["ch"].value;

								if(xp > 0 || ch > 0){
									document.forms[0].elements["aggiorna"].style.visibility="hidden";
								}
								else{
									document.forms[0].elements["aggiorna"].style.visibility="visible";
								}

							}
					
					</script>
				
				<form action="index.php?act=buildpg&build method="post" name="sheet_form">
					<table>
						<tr>
							<td>Punti caratteristica:</td> <td><input type="text" size="2" name="ch_display" value="'.$ch.'" style="text-align: right; color: blue;" disabled> <input type="hidden" name="ch" value="'.$ch.'"> </td>
						</tr>
						'.$ch_fields.'
						
					</table>
					
					<table>
						<tr>
							<td>Punti abilit&agrave;:</td><td><input type="text" size="2" name="xp_display" value="'.$xp.'" style="text-align: right; color: blue;" disabled>
							<input type="hidden" name="xp" value="'.$xp.'"></td>
						</tr>

						'.$ab_fields.'

						<tr>
							<td><INPUT name="aggiorna" class="button" type="SUBMIT" value="Invia" style="visibility: hidden;"></td>
						</tr>
					</table>
				</form>
				
				';
			
				return $body;
			}
			
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