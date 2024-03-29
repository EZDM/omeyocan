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
//		X7 Chat Version 2.0.4
//		Released June 16, 2006
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
	// This class handles the Action tab, and certain user control functions
	// like kicking, ignoring and banning.
	
	class user_control{
		var $user;	// Stores the username of the person this class will operate on
		var $user_info;	// Stores profile information
		var $permissions;	// Stores user permissions
		
		function user_control($user){
			global $x7c;
		
			$this->user_info = new profile_info($user);
			$this->permissions = $x7c->permissions($user);
			$this->user = $user;
		}
		
		// This generates the values for the action tag
		function generate_action_tab(){
			global $x7c, $db, $prefix, $x7s, $txt;
			
			// See if you have ignored this user or if this user is ignored
			$query = $db->DoQuery("SELECT * FROM {$prefix}muted WHERE ignored_user='$this->user' AND user='$x7s->username'");
			$row = $db->Do_Fetch_Row($query);
			/*if($row[0] != ""){
				// This user is currently ignored
				$return[0] = $txt[92];
				$return[1] = 'unignore';
			}else{
				// This user is not currently ignored
				$return[0] = $txt[91];
				$return[1] = 'ignore';
			}*/
			
			// This user is currently ignored
			$return[0] = "Invia un sussurro";
			$return[1] = 'suss';
				
			
			// See if YOU are an operator
			if($x7c->permissions['room_operator'] == 1){
				// You are an operator, so....
				
				// you are able to make other ops
				
				// See if they have AOP-staths
				if($this->permissions['AOP_all'] == 0){
					// see if they are already an op
					if($this->permissions['room_operator'] == 1){
						// They are already an op
						$return[2] = $txt[95];
						$return[3] = "top";
					}else{
						// They are not already an op
						$return[2] = $txt[94];
						$return[3] = 'gop';
					}
				}
				
				// See if you are allowed to view IP addresses
				if($x7c->permissions['viewip'] == 1){
					$return[] = $txt[98];
					$return[] = 'vip';
				}
				
				if($x7c->permissions['room_operator'] == 1){
					$return[] = "Effettua un tiro";
					$return[] = 'dice';
				}
				
				// See if you are allowed to kick people and if that person can be kicked
				if($x7c->permissions['kick'] == 1 && $this->permissions['ban_kick_imm'] != 1){
					$return[] = $txt[97];
					$return[] = 'kick';
				}
				
				// Check to see if they have a voice or not
				// First see if they have Auto-Voice-all, if so we can't do anything to them
				if($this->permissions['AV_all'] == 0){
				
					// Now, we run conditionals to figure out whether
					// the to user mute/unmute or give/take voice
					if($x7c->room_data['moderated'] == 1){
					
						// The room IS moderated, use give/take voice
						if($this->permissions['room_voice'] == 1){
							// They have a voice
							$return[] = $txt[100];
							$return[] = "tv";
						}else{
							// They do not have a voice, allow you to give them one
							$return[] = $txt[99];
							$return[] = "gv";
						}
					
					}else{
					
						// The room is NOT moderated, use mute/unmute
						if($this->permissions['room_voice'] == 1){
							// They have are not muted
							$return[] = $txt[93];
							$return[] = "mute";
						}else{
							// They are muted
							$return[] = $txt[96];
							$return[] = "unmute";
						}
					
					}
					
				}				
			}
			
			return $return;
			
		}
		
		// This generates the values for the Profile tab
		function generate_profile_tab(){
			global $txt;
		
			$return['status'] = $this->user_info->profile['status'];
			$return['group'] = $this->user_info->profile['usergroup'];
			
			if($return['status'] == "")
				$return['status'] = $txt[150];
			
			return $return;
		}
		
		
		// Take a hint from the name if you want to know what this does
		function ignore($ban_length=0){
			global $x7s, $db, $prefix;
			$time = time();
			$db->DoQuery("INSERT INTO {$prefix}muted VALUES('0','$x7s->username','$this->user')");
		}
		
		// *yawn* hopefully I don't need to tell you what this does
		function unignore(){
			global $x7s, $db, $prefix;
			$db->DoQuery("DELETE FROM {$prefix}muted WHERE user='$x7s->username' AND ignored_user='$this->user'");
		}
		
		// Give someone operator status in a room
		function give_ops(){
			global $x7c, $db, $prefix, $txt;
			$their_id = $this->user_info->profile['id'];
			$new_ops = $x7c->room_data['ops'].";$their_id";
			$room_id = $x7c->room_data['id'];
			$db->DoQuery("UPDATE {$prefix}rooms SET ops='$new_ops' WHERE id='$room_id'");
		
			// Alert the room that they have a new operator, and alert the user that they have
			// access to the room cp.  In addition, reload their top frame so that they can see the Room CP button
			include_once("./lib/message.php");
			alert_room($x7c->room_name,$txt[126],$this->user);
			alert_user($this->user,$txt[407]);
		}
		
		// Take away someone's operator status in a room
		function take_ops(){
			global $x7c, $db, $prefix, $txt;
			$ops = explode(";",$x7c->room_data['ops']);
			$their_id = $this->user_info->profile['id'];
			$room_id = $x7c->room_data['id'];
			$key = array_search("$their_id",$ops);
			unset($ops[$key]);
			$ops = implode(";",$ops);
			$db->DoQuery("UPDATE {$prefix}rooms SET ops='$ops' WHERE id='$room_id'");
		
			// Alert the room that they have a new operator no longer
			include_once("./lib/message.php");
			alert_room($x7c->room_name,$txt[127],$this->user);
		}
		
		// View the user's IP address
		function view_ip(){
			global $db, $prefix;
			$query = $db->DoQuery("SELECT ip FROM {$prefix}online WHERE name='$this->user' AND room='$_GET[room]'");
			$row = $db->Do_Fetch_Row($query);
			return $row[0];
		}
		
		//Perform a roll for a user
		function dice($user_info,$room){
			global $db, $prefix;

			$message='';
			
			if(isset($_POST['msg'])){
                                $MAX_AB_RESULT=32;
                                $MAX_CH_RESULT=22;
                                $ab_eval=false;
                                $ch_eval=false;
                                
                                $inv_cum_3d6[0]="100%";
                                $inv_cum_3d6[1]="99%";
                                $inv_cum_3d6[2]="98%";
                                $inv_cum_3d6[3]="95%";
                                $inv_cum_3d6[4]="90%";
                                $inv_cum_3d6[5]="83%";
                                $inv_cum_3d6[6]="74%";
                                $inv_cum_3d6[7]="62%";
                                $inv_cum_3d6[8]="50%";
                                $inv_cum_3d6[9]="37%";
                                $inv_cum_3d6[10]="25%";
                                $inv_cum_3d6[11]="16%";
                                $inv_cum_3d6[12]="9%";
                                $inv_cum_3d6[13]="4%";
                                $inv_cum_3d6[14]="2%";
                                $inv_cum_3d6[15]="1%";

                                $inv_cum_1d14[0]="100%";
                                $inv_cum_1d14[1]="93%";
                                $inv_cum_1d14[2]="86%";
                                $inv_cum_1d14[3]="79%";
                                $inv_cum_1d14[4]="71%";
                                $inv_cum_1d14[5]="64%";
                                $inv_cum_1d14[6]="57%";
                                $inv_cum_1d14[7]="50%";
                                $inv_cum_1d14[8]="43%";
                                $inv_cum_1d14[9]="36%";
                                $inv_cum_1d14[10]="28%";
                                $inv_cum_1d14[11]="21%";
                                $inv_cum_1d14[12]="14%";
                                $inv_cum_1d14[13]="7%";
                                $inv_cum_1d14[14]="0%";

                                

			
				$action_regexp = "/&sect;([^[:space:]]+)/i";
				$message = $_POST['msg'];


                                $table_ability="<table cellspacing=0>";

                                for($i=0; $i< $MAX_AB_RESULT; $i++){ // 31 is the possible max result
                                    if($i < 11)
                                          $action_msg="<span style=\"color: red;\">";
                                    else if($i < 21)
                                          $action_msg="<span style=\"color: orange;\">";
                                    else
                                          $action_msg="<span style=\"color: green;\">";
                                          
                                    $table_ability_row[$i]="<tr><td class=\"throw_eval\">$action_msg $i</td>";
                                }

                                $table_ability_head="<tr><td class=\"throw_eval\" style=\"width:30px;\">Ris.</td>";
                		srand(time()+microtime());
                                
				while(preg_match($action_regexp,$message, $action)){
				
										
					$action_msg="";
					$query = $db->DoQuery("SELECT a.name AS ab_name, ua.value AS ab_value, uc.value AS char_value
								FROM {$prefix}userability ua, {$prefix}usercharact uc , {$prefix}ability a
								WHERE ua.ability_id=a.id
							 	AND a.char=uc.charact_id
							 	AND uc.username=ua.username
							 	AND ua.username='$user_info->user' 
							 	AND ua.ability_id='$action[1]'");
			 
					if($row = $db->Do_Fetch_Assoc($query)){
                                                $ab_eval=true;
                                                 
                                                $modifier=$row['ab_value']*2 + $row['char_value']/2;
                                                $displacement = $modifier - 2;

                                                $table_ability_head.="<td class=\"throw_eval\">$row[ab_name]</td>";
                                                for($i=0; $i < $MAX_AB_RESULT; $i++){
                                                    if($i < 11)
                                                            $action_msg="<span style=\"color: red;\">";
                                                    else if($i < 21)
                                                            $action_msg="<span style=\"color: orange;\">";
                                                    else
                                                            $action_msg="<span style=\"color: green;\">";
                                                            
                                                    if($i < $displacement)
                                                        $table_ability_row[$i].="<td class=\"throw_eval\">$action_msg 100%</span></td>";
                                                    else if($i < $displacement+16)
                                                        $table_ability_row[$i].="<td class=\"throw_eval\">$action_msg".$inv_cum_3d6[$i - $displacement]."</span></td>";
                                                    else
                                                        $table_ability_row[$i].="<td class=\"throw_eval\">$action_msg 0% </span></td>";
                                                }
                                                
                                                
						$roll = rand(1,6);
						$roll += rand(1,6);
						$roll += rand(1,6);
                                                $result = floor($row['ab_value']*2 + $row['char_value']/2 - $roll) + 16;
                                                
                                                if($result < 11)
                                                        $action_msg="<span class=\"roll_neg\">{".$row['ab_name']." ".$result."}</span>";
                                                else if($result < 21)
                                                        $action_msg="<span class=\"roll_avg\">{".$row['ab_name']." ".$result."}</span>";
                                                else
                                                        $action_msg="<span class=\"roll_pos\">{".$row['ab_name']." ".$result."}</span>";
					
						
					}
					$message = preg_replace($action_regexp, $action_msg, $message, 1);
				
				}
				$table_ability_head.="</tr>";
				
				$table_ability.=$table_ability_head;

				for($i=0; $i < $MAX_AB_RESULT; $i++){
                                        $table_ability_row[$i].="</tr>";
                                        $table_ability.=$table_ability_row[$i];
                                }
				$table_ability.="</table>";
			
				//Perform characteristic
				$charact_regexp = "/%([^[:space:]]+)/i";

				$table_char="<table cellspacing=0>";

                                for($i=0; $i< $MAX_CH_RESULT; $i++){ // 31 is the possible max result
                                    if($i < 7)
                                          $action_msg="<span style=\"color: red;\">";
                                    else if($i < 14)
                                          $action_msg="<span style=\"color: orange;\">";
                                    else
                                          $action_msg="<span style=\"color: green;\">";
                                          
                                    $table_char_row[$i]="<tr><td class=\"throw_eval\">$action_msg $i</td>";
                                }

                                $table_char_head="<tr><td class=\"throw_eval\" style=\"width: 30px;\">Ris.</td>";

				while(preg_match($charact_regexp,$message, $charact)){
				
										
					$charact_msg="";
					$query = $db->DoQuery("SELECT c.name AS ch_name, uc.value AS ch_value
								FROM {$prefix}usercharact uc , {$prefix}characteristic c
								WHERE uc.charact_id=c.id
							 	AND uc.username='$user_info->user' 
								 AND uc.charact_id='$charact[1]'");
				 
					if($row = $db->Do_Fetch_Assoc($query)){
                                                $ch_eval=true;
						$roll = rand(1,14);
                                                $result = floor($row['ch_value'] - $roll) + 10;

                                                $displacement=$row['ch_value']-4;
                                                
                                                $table_char_head.="<td class=\"throw_eval\">$row[ch_name]</td>";
                                                for($i=0; $i < $MAX_CH_RESULT; $i++){
                                                    if($i < 7)
                                                            $action_msg="<span style=\"color: red;\">";
                                                    else if($i < 14)
                                                            $action_msg="<span style=\"color: orange;\">";
                                                    else
                                                            $action_msg="<span style=\"color: green;\">";
                                                            
                                                    if($i < $displacement)
                                                        $table_char_row[$i].="<td class=\"throw_eval\">$action_msg 100%</span></td>";
                                                    else if($i < $displacement+14)
                                                        $table_char_row[$i].="<td class=\"throw_eval\">$action_msg".$inv_cum_1d14[$i - $displacement]."</span></td>";
                                                    else
                                                        $table_char_row[$i].="<td class=\"throw_eval\">$action_msg 0% </span></td>";
                                                }
					
                                                if($result < 7)
                                                        $charact_msg="<span class=\"roll_neg\">{".$row['ch_name']." ".$result."}</span>";
                                                else if($result < 14)
                                                        $charact_msg="<span class=\"roll_avg\">{".$row['ch_name']." ".$result."}</span>";
                                                else
                                                        $charact_msg="<span class=\"roll_pos\">{".$row['ch_name']." ".$result."}</span>";
					
					
					}
					$message = preg_replace($charact_regexp, $charact_msg, $message, 1);
				
				}
				$table_char_head.="</tr>";
				
				$table_char.=$table_char_head;

				for($i=0; $i < $MAX_CH_RESULT; $i++){
                                        $table_char_row[$i].="</tr>";
                                        $table_char.=$table_char_row[$i];
                                }
				$table_char.="</table>";
				
				//Perform generic dice
				$generic_eval = false;
				$generic_eval_msg ="";
				$dice_regexp = "/\~([^[:space:]]+)/i";
			
				while(preg_match($dice_regexp,$message, $dice_value)){
					$dice_msg="";
					
					if(is_numeric($dice_value[1])){
						$generic_eval = true;
						$result = rand(1,$dice_value[1]);
						$dice_msg="<span class=\"roll_avg\">{Tira un d$dice_value[1]:</span><span style=\"color: white; font-weight: bold;\"> $result </span><span class=\"roll_avg\">}</span>";
						$generic_eval_msg .= "Tiro d$dice_value[1]<br>";
					}
			
					$message = preg_replace($dice_regexp, $dice_msg, $message, 1);
			
				}
				
				$time = time();
				$todb = "<span class=\"masterRoll\">Il master effettua un tiro per [".$user_info->user."]: </span>".$message."<br>";				

				$message .="<br><br><a href=\"index.php?act=memberlist&room=$room\">[Torna alla lista presenti]</a>";

				if(isset($_POST['perform'])){
				        $db->DoQuery("INSERT INTO {$prefix}messages VALUES('0','System','4','Master roll','$todb','$room','$time')");
				        return $message;
				}
				else{
                                        $message="";
                                        if($ab_eval)
                                                $message.="<h3>Possibilita' di successo abilita'</h3>".$table_ability;
                                        if($ch_eval)
                                                $message.="<h3>Possibilita' di successo caratteristiche</h3>".$table_char;
                                        if($generic_eval)
                                        		$message.="<br>$generic_eval_msg";
				}
			
			}
			
                        $form = "
                        <script language=\"javascript\" type=\"text/javascript\">
                        function action_select(myaction){
                                                                if(myaction != \"\"){
                                                                        document.masterRoll.msg.value = document.masterRoll.msg.value + myaction +\" \";
                                                                }
                                                                document.masterRoll.action.selectedIndex=0;
                                                                document.masterRoll.charact.selectedIndex=0;
                                                        }
                        </script>

                        <form name=\"masterRoll\" method=\"post\" action=\"index.php?act=usr_action&action=dice&user=$user_info->user&room=$room\">";
                                        

                        $the_action="Valuta i tiri";
                        if(!isset($_POST['msg'])){
                                $form.="<select class=\"button\" name=\"action\" onChange=\"javascript: return 	action_select(this.options[this.selectedIndex].value);\">
                                                <option value=\"\">Scelta Abilit&agrave;...</option>
                                                <option value=\"\">------------------</option>";
                                $query = $db->DoQuery("SELECT a.id AS id,
                                                                ua.value AS value,
                                                                a.name AS name
                                                                FROM {$prefix}userability ua, {$prefix}ability a
                                                                WHERE ua.ability_id=a.id
                                                                AND username='$user_info->user'
                                                                ORDER BY a.name");

                                while($row = $db->Do_Fetch_Assoc($query)){
                                        $form .= "<option value=\"�".$row['id']."\">".$row['name']." ".$row['value']."</option>\n";
                                }
                                $form .= "</select>\n";

                                $form .= '<select class="button" name="charact" onChange="javascript: return action_select(this.options[this.selectedIndex].value);">
                                                                                                        <option value="">Scelta Caratteristica...</option>
                                                                                                        <option value="">------------------</option>';
                                $query = $db->DoQuery("SELECT c.id AS id,
                                                        uc.value AS value,
                                                        c.name AS name
                                                        FROM {$prefix}usercharact uc, {$prefix}characteristic c
                                                        WHERE uc.charact_id=c.id
                                                          AND username='$user_info->user'
                                                          ORDER BY c.name");

                                while($row = $db->Do_Fetch_Assoc($query)){
                                        $form .= "<option value=\"%".$row['id']."\">".$row['name']." ".$row['value']."</option>\n";
                                }

                                $form .= "</select>";
                                
                                $form.="<select class=\"button\" name=\"action\" onChange=\"javascript: return 	action_select(this.options[this.selectedIndex].value);\">
                                                <option value=\"\">Dadi</option>
                                                <option value=\"\">----</option>
                                                <option value=\"~2\">d2</option>
                                                <option value=\"~4\">d4</option>
                                                <option value=\"~6\">d6</option>
                                                <option value=\"~8\">d8</option>
                                                <option value=\"~10\">d10</option>
                                                <option value=\"~12\">d12</option>
                                                <option value=\"~20\">d20</option>
                                                <option value=\"~100\">d100</option>
                                                ";

                                $form .= "</select>\n
                                          <br>Tiri da effettuare:
                                          <br><input class=\"button\" type=\"text\" name=\"msg\" size=40>";
                        }

                        else{
                                $form .= "<input type=\"hidden\" name=\"perform\" value=\"1\" />
                                          <input type=\"hidden\" name=\"msg\" value=\"$_POST[msg]\" />";
                        
                                $the_action="Effettua i tiri";
                        }
                        
                        $form .= "
                                <br><input type=\"submit\" class=\"button\" value=\"$the_action\">
                                </form>";

                        $toout='';
                        
                        if(isset($_POST['msg']))
                                $toout="<div style=\"background: black; border: black 1px solid; padding: 2px; overflow: auto; height: 300px; width: 100%\">$message </div>";

                        $toout.=$form;
                        return $toout;
		}
		
		// Kick a user
		function kick($reason){
			global $db, $prefix, $x7c, $txt;
			$room_id = $x7c->room_data['id'];
			$time = time();
			$db->DoQuery("INSERT INTO {$prefix}banned VALUES('0','$room_id','$this->user','$time','60','$reason')");
			// Send a message to the room
			include_once("./lib/message.php");
			$txt[110] = eregi_replace("_r","$reason",$txt[110]);
			alert_room($_GET['room'],"$txt[110]",$this->user);
		}
		
		// Adds user's id and a negative sign to the voice column so they are muted
		function mute(){
			global $db, $prefix, $x7c, $txt;
			$voiced = $x7c->room_data['voiced'];
			$their_id = $this->user_info->profile['id'];
			$room_id = $x7c->room_data['id'];
			$voiced .= ";-$their_id";
			$db->DoQuery("UPDATE {$prefix}rooms SET voiced='$voiced' WHERE id='$room_id'");
			// Alert the room that they have a new operator
			include_once("./lib/message.php");
			alert_room($x7c->room_name,$txt[131],$this->user);
		
		}
		
		// Removes the user's id and negative sign from the voice column so they are unmuted
		function unmute(){
			global $db, $prefix, $x7c, $txt;
			$voiced = $x7c->room_data['voiced'];
			$their_id = $this->user_info->profile['id'];
			$room_id = $x7c->room_data['id'];
			$voiced = explode(";",$voiced);
			$key = array_search("-$their_id",$voiced);
			unset($voiced[$key]);
			$voiced = implode(";",$voiced);
			$db->DoQuery("UPDATE {$prefix}rooms SET voiced='$voiced' WHERE id='$room_id'");
			// Alert the room that they have a new operator
			include_once("./lib/message.php");
			alert_room($x7c->room_name,$txt[132],$this->user);
		}
		
		// Adds a user's id to the voice column
		function voice(){
			global $db, $prefix, $x7c, $txt;
			$voiced = $x7c->room_data['voiced'];
			$their_id = $this->user_info->profile['id'];
			$room_id = $x7c->room_data['id'];
			$voiced .= ";$their_id";
			$db->DoQuery("UPDATE {$prefix}rooms SET voiced='$voiced' WHERE id='$room_id'");
			// Alert the room that they have a new voiced user
			include_once("./lib/message.php");
			alert_room($x7c->room_name,$txt[129],$this->user);
		}
		
		// Removes a user's id from the voice column
		function unvoice(){
			global $db, $prefix, $x7c, $txt;
			$voiced = $x7c->room_data['voiced'];
			$their_id = $this->user_info->profile['id'];
			$room_id = $x7c->room_data['id'];
			$voiced = explode(";",$voiced);
			$key = array_search("$their_id",$voiced);
			unset($voiced[$key]);
			$voiced = implode(";",$voiced);
			$db->DoQuery("UPDATE {$prefix}rooms SET voiced='$voiced' WHERE id='$room_id'");
			// Alert the room that they have a new voiced user
			include_once("./lib/message.php");
			alert_room($x7c->room_name,$txt[130],$this->user);
		}

	}
	
	// This function gets a username by its ID
	function get_user_by_id($id){
		global $db, $prefix, $querydb;
		
		if(!isset($querydb['get_user_by_id_1'])){
			$query = $db->DoQuery("SELECT id,username FROM {$prefix}users");
			while($row = $db->Do_Fetch_Row($query)){
				$querydb['get_user_by_id_1'][$row[0]] = $row[1];
			}
		}
		
		return @$querydb['get_user_by_id_1'][$id];
	}
	
	/*// This function gets a username by its ID
	function get_user_by_id($id){
		global $db, $prefix, $querydb;
		$query = $db->DoQuery("SELECT username FROM {$prefix}users WHERE id='$id'");
		$row = $db->Do_Fetch_Row($query);
		return $row[0];
	}*/

?> 
