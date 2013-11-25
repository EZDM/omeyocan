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

	// Handle the room Control panel
	function roomcp_master(){
		global $x7p, $x7s, $print, $db, $txt, $x7c, $prefix;
	
		// Check permissions to make sure they have access to the Room Control Panel
		if($x7c->permissions['room_operator'] == 0){
			$print->normal_window($txt[215],"++".$txt[216]);
			return "";
		}
				
		$head = $txt[41];
		$body = $txt[217];
		
		if(!isset($_GET['cp_page']))
			$_GET['cp_page'] = "main";
		
		if($_GET['cp_page'] == "main"){
			// The main CP -- hmm, duh
			// Nothing needs done here

			$body="<a href=\"index.php?act=admincp\">Vai al pannello di amministrazione generale</a>";
		}elseif($_GET['cp_page'] == "settings"){
		
			$head = $txt[218];
			if($x7c->permissions['make_rooms'] == 0){
				$body = "--".$txt[216];
				return;
			}
				
		
			if(!isset($_POST['topic'])){

				// Some quick defaults
				if($x7c->room_data['moderated'] == 1)
					$def['moderated'] = " CHECKED";
				else
					$def['moderated'] = "";

				if($x7c->room_data['time'] == 0)
					$def['neverexpire'] = " CHECKED";
				else
					$def['neverexpire'] = "";
				
				if($x7c->room_data['panic_free'] == 1)
					$def['panic_free'] = " CHECKED";
				else
					$def['panic_free'] = "";

				if($x7c->room_data['hunt'] == 1)
					$def['hunt'] = " CHECKED";
				else
					$def['hunt'] = "";

				$body = "<Br><Br><form action=\"index.php?act=roomcp&cp_page=settings&room=$_GET[room]\" method=\"post\">
				<table width=\"100%\" align=\"center\" border=\"0\" cellspacing=\"5\" cellpadding=\"0\">
					<tr>
						<td width=\"33%\">Descrizione:</td>
 						<td ><textarea class=\"text_input\" name=\"topic\" style=\"width: 100%; height: 200px\">{$x7c->room_data['topic']}</textarea></td>
					</tr>
						<input type=\"hidden\" class=\"text_input\" style=\"width: 100%;\" name=\"greeting\" value=\"{$x7c->room_data['greeting_raw']}\" autocomplete=\"off\">
					
					<tr>
						<td>$txt[3]:</td>
						<td><input type=\"password\" class=\"text_input\" style=\"width: 100%;\"  name=\"password\" autocomplete=\"off\" value=\"{$x7c->room_data['password']}\"></td>
					</tr>
					<tr>
						<td>$txt[67]:</td>
						<td><input type=\"text\" class=\"text_input\" style=\"width: 100%;\" name=\"max_users\" value=\"{$x7c->room_data['maxusers']}\"></td>
					</tr>
                                        <td>Nome lungo:</td>
								<td width=\"100\"><input type=\"text\" style=\"width: 100%;\" class=\"text_input\" name=\"long_name\" value=\"{$x7c->room_data['long_name']}\"></td>
					";
				
				if($x7c->permissions['set_background'] == 1 && $x7c->settings['enable_roombgs'] == 1){
					
					$body .= "<tr>
								<td>Immagine per la descrizione:</font></td>
								<td><input type=\"text\" class=\"text_input\" style=\"width: 100%;\"  name=\"rm_bg\" value=\"{$x7c->room_data['background']}\"></td>
							</tr>";
					
				}
				
				if($x7c->permissions['set_logo'] == 1 && $x7c->settings['enable_roomlogo'] == 1){
					
					$body .= "<tr>
								<td>Immagine polaroid:</td>
								<td><input type=\"text\" class=\"text_input\" style=\"width: 100%;\"  name=\"image_url\" value=\"{$x7c->room_data['logo']}\"></td>
								<tr><td>&nbsp;</td><td><a onClick=\"javascript: window.open('index.php?act=images&subdir=polaroid','Images','location=no,menubar=no,resizable=yes,status=no,toolbar=no,scrollbars=yes,width={$x7c->settings['tweak_window_large_width']},height={$x7c->settings['tweak_window_large_height']}');\">[Carica immagine]</a></td></tr>
							</tr>";
					
				}

				if($x7c->room_data['type'] == 1){
					$def['public'] = " selected=true";
					$def['private'] = "";
				}else{
					$def['public'] = "";
					$def['private'] = " selected=true";
				}
					
				$type_options = "<option value=\"1\"$def[public]>$txt[68]</option>";
				if($x7c->permissions['make_proom'] != 0)
					$type_options .= "<option value=\"2\"$def[private]>$txt[69]</option>";

				$body .= "<tr>
							<td>$txt[64]:</td>
							<td><select class=\"text_input\" style=\"width: 100px;\" name=\"room_type\">$type_options</select></td>
						</tr>";

				$body .= "<tr>
								<td>Non &egrave; affetta dal panico:</td>
								<td><input type=\"checkbox\" name=\"panic_free\" value=\"1\"{$def['panic_free']}></td>
						</tr>";
						
				$body .= "<tr>
								<td>Stanza hunt (random avatar):</td>
								<td><input type=\"checkbox\" name=\"hunt\" value=\"1\"{$def['hunt']}></td>
						</tr>";
						
				$body .= "<tr>
							<td colspan=\"2\"><Br><div align=\"center\"><input type=\"submit\" class=\"text_input\" value=\"$txt[187]\"></div></td>
						</tr></form></table>";

			}else{
				// Update actual settings, print ok message
				include_once("./lib/rooms.php");

				// Check some values
				if($_POST['max_users'] == "" || $_POST['max_users'] < 3)
					$_POST['max_users'] = "3";
					
				if(@$_POST['moderated'] == "" || $x7c->permissions['make_mod'] == 0)
					$_POST['moderated'] = 0;
					
				if($x7c->permissions['make_proom'] == 0 || ($_POST['room_type'] != 1 && $_POST['room_type'] != 2))
					$_POST['room_type'] = 1;
					
				if($x7c->permissions['set_background'] == 0 || $x7c->settings['enable_roombgs'] == 0 || !isset($_POST['rm_bg']))
					$_POST['rm_bg'] = $x7c->room_data['background'];
				
				if($x7c->permissions['set_logo'] == 0 || $x7c->settings['enable_roomlogo'] == 0 || !isset($_POST['image_url']))
					$_POST['image_url'] = $x7c->room_data['logo'];
				
				if(!isset($_POST['panic_free']))
					$_POST['panic_free']=0;

				if(!isset($_POST['hunt']))
					$_POST['hunt']=0;

				//We enable html formatting for the topic field
				$_POST['topic'] = preg_replace("/&lt;/i","<",$_POST['topic']);
				$_POST['topic'] = preg_replace("/&gt;/i",">",$_POST['topic']);
				$_POST['topic'] = preg_replace("/&quot;/i","\"",$_POST['topic']);

				// Order `em up
				$new_settings[] = $_POST['room_type'];
				$new_settings[] = $_POST['moderated'];
				$new_settings[] = $_POST['topic'];
				$new_settings[] = $_POST['greeting'];
				$new_settings[] = $_POST['password'];
				$new_settings[] = $_POST['max_users'];
				$new_settings[] = $_POST['rm_bg'];
				$new_settings[] = $_POST['image_url'];
				$new_settings[] = $_POST['panic_free'];
				$new_settings[] = $_POST['long_name'];
				$new_settings[] = $_POST['hunt'];

				
				
				mass_change_roomsettings($_GET['room'],$new_settings);
				$body = $txt[210];
			}

		}elseif($_GET['cp_page'] == "blocklist"){
		
			$head = $txt[141];
			
			if(@$_GET['subact'] == "ban" && isset($_POST['toban'])){
			
				if(@$_POST['len_unlimited'] == 1){
					$length = 0;
				}else{
					$length = $_POST['len_limited']*$_POST['len_period'];
				}
				
				new_ban($_POST['toban'],$length,$_POST['reason'],$x7c->room_data['id']);
				$body = "$txt[234]<br><Br>";
			
			}elseif(@$_GET['subact'] == "unban"){
			
				remove_ban($_GET['banid'],$x7c->room_data['id']);
				$body = "$txt[235]<Br><Br>";
			
			}else{
				$body = "";
			}
				
			
				$body .= "$txt[233]<Br><Br><table border=\"0\" align=\"center\" cellspacing=\"0\" cellpadding=\"2\" class=\"col_header\">
						<tr>
							<td width=\"100\">$txt[224]</td>
							<td width=\"110\">$txt[223]</td>
							<td width=\"50\">$txt[225]</td>
						</tr>
						</table>
						<table border=\"0\" align=\"center\" cellspacing=\"0\" cellpadding=\"2\" class=\"inside_table\">";
				
				// Get the ban records
				$query = $db->DoQuery("SELECT * FROM {$prefix}banned WHERE room='{$x7c->room_data['id']}'");
				while($row = $db->Do_Fetch_Row($query)){
				
					if($row[4] == 0)
						$length = $txt[226];
					else
						$length = date("{$x7c->settings['date_format_full']}",$row[3]+$row[4]);
					
				
					$body .= "<tr>
								<td width=\"100\" class=\"dark_row\"><a href=\"index.php?act=roomcp&cp_page=blocklist&subact=unban&banid=$row[0]&room=$_GET[room]\">$row[2]</a></td>
								<td width=\"110\" class=\"dark_row\">$row[5]</td>
								<td width=\"50\" class=\"dark_row\" style=\"text-align: center\">$length</td>
							</tr>";
				}
							
				$body .= "</table><Br><br>
					<form action=\"index.php?act=roomcp&cp_page=blocklist&subact=ban&room=$_GET[room]\" method=\"post\">
						<table align=\"center\" border=\"0\" cellspacing=\"5\" cellpadding=\"0\">
							<tr>
								<td width=\"200\" colspan=\"2\"><div align=\"center\"><b>$txt[222]</b></div></td>
							</tr>
							<tr>
								<td width=\"100\">$txt[224]: </td>
								<td width=\"100\"><input type=\"text\" name=\"toban\" class=\"text_input\"></td>
							</tr>
							<tr>
								<td width=\"100\">$txt[223]: </td>
								<td width=\"100\"><input type=\"text\" name=\"reason\" class=\"text_input\"></td>
							</tr>
							<tr valign=\"top\">
								<td width=\"100\">$txt[225]: </td>
								<td width=\"100\" style=\"text-align: center\">$txt[226] <input type=\"checkbox\" value=\"1\" name=\"len_unlimited\" CHECKED>
									<Br>$txt[227]
									<Br>
									<input type=\"text\" class=\"text_input\" style=\"width: 45px;text-align: center;\" name=\"len_limited\" value=\"0\">
									<select name=\"len_period\" class=\"text_input\">
										<option value=\"60\">$txt[228]</option>
										<option value=\"3600\">$txt[229]</option>
										<option value=\"86400\">$txt[230]</option>
										<option value=\"604800\">$txt[231]</option>
										<option value=\"2419200\">$txt[232]</option>
									</select>
								</td>
							</tr>
							<tr>
								<td width=\"200\" colspan=\"2\"><div align=\"center\"><input type=\"submit\" value=\"$txt[222]\" class=\"button\"></div></td>
							</tr>
						</table>
					</form>";
			
			
		
		}elseif($_GET['cp_page'] == "ops"){
		
			$head = $txt[219];
			
			// Include the necessary librarys for this
			include_once("./lib/usercontrol.php");
			
			if(isset($_POST['add'])){
			
				// Give them operator status
				$body = $txt[105]."<Br><Br>";
				$uco = new user_control($_POST['add']);
				
				if($uco->user_info->profile['id'] != ""){
					$uco->give_ops();
					$x7c->room_data['ops'] .= ";".$uco->user_info->profile['id'];
				}else{
					$body = $txt[239]."<Br><Br>";
				}
					
			}elseif(isset($_GET['revoke'])){
			
				// Take away their operator status
				$body = $txt[106]."<br><br>";
				$uco = new user_control($_GET['revoke']);
				$uco->take_ops();
				
				// Clear it so it isn't displayed again
				$ops = explode(";",$x7c->room_data['ops']);
				$ops_id = array_search($uco->user_info->profile['id'],$ops);
				unset($ops[$ops_id]);
				$x7c->room_data['ops'] = implode(";",$ops);
				
			}else{
				$body = "";
			}
			
			// Make a listing of all operators in this room
			$body .= $txt[236]."<Br>";
			$ops = explode(";",$x7c->room_data['ops']);
			foreach($ops as $key=>$val){
				$username = get_user_by_id($val);
				$body .= "<Br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"index.php?act=roomcp&cp_page=ops&room=$_GET[room]&revoke=$username\">$username</a>";
			}
			
			$body .= "<Br><Br><form action=\"index.php?act=roomcp&cp_page=ops&room=$_GET[room]\" method=\"post\">
			$txt[94]: <input type=\"text\" name=\"add\" class=\"text_input\"> <input type=\"submit\" class=\"button\" value=\"$txt[94]\">
			</form><Br><Br>";
		
		}elseif($_GET['cp_page'] == "voices"){
		
			$head = $txt[220];
			// Include the necessary librarys for this
			include_once("./lib/usercontrol.php");
			
			if(isset($_POST['add'])){
			
				// Give them a voice
				$body = $txt[113]."<Br><Br>";
				$uco = new user_control($_POST['add']);
				
				if($uco->user_info->profile['id'] != ""){
					$uco->voice();
					$x7c->room_data['voiced'] .= ";".$uco->user_info->profile['id'];
				}else{
					$body = $txt[239]."<Br><Br>";
				}
				
			}elseif(isset($_GET['revoke'])){
			
				// Take their voice
				$body = $txt[114]."<br><br>";
				$uco = new user_control($_GET['revoke']);
				$uco->unvoice();
				
				// Clear it so it isn't displayed again
				$voice = explode(";",$x7c->room_data['voiced']);
				$voice_id = array_search($uco->user_info->profile['id'],$voice);
				unset($voice[$voice_id]);
				$x7c->room_data['voiced'] = implode(";",$voice);
				
			}else{
				$body = "";
			}
			
			// Make a listing of all voiced users in this room
			$body .= $txt[237]."<Br>";
			$voice = explode(";",$x7c->room_data['voiced']);
			foreach($voice as $key=>$val){
				if($val > 0){
					$username = get_user_by_id($val);
					$body .= "<Br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"index.php?act=roomcp&cp_page=voices&room=$_GET[room]&revoke=$username\">$username</a>";
				}
			}
			
			$body .= "<Br><Br><form action=\"index.php?act=roomcp&cp_page=voices&room=$_GET[room]\" method=\"post\">
			$txt[99]: <input type=\"text\" name=\"add\" class=\"text_input\"> <input type=\"submit\" class=\"button\" value=\"$txt[99]\">
			</form><Br><Br>";
			
		}elseif($_GET['cp_page'] == "mutes"){
		
			$head = $txt[221];
			// Include the necessary librarys for this
			include_once("./lib/usercontrol.php");
			
			if(isset($_POST['add'])){
			
				// Mute them
				$body = $txt[111]."<Br><Br>";
				$uco = new user_control($_POST['add']);
				if($uco->user_info->profile['id'] != ""){
					$uco->mute();
					$x7c->room_data['voiced'] .= ";-".$uco->user_info->profile['id'];
				}else{
					$body = $txt[239]."<Br><Br>";
				}
				
			}elseif(isset($_GET['revoke'])){
			
				// Make them on longer muted
				$body = $txt[112]."<br><br>";
				$uco = new user_control($_GET['revoke']);
				$uco->unmute();
				
				// Clear it so it isn't displayed again
				$mute = explode(";",$x7c->room_data['voiced']);
				$mute_id = array_search("-{$uco->user_info->profile['id']}",$mute);
				unset($mute[$mute_id]);
				$x7c->room_data['voiced'] = implode(";",$mute);
				
			}else{
				$body = "";
			}
			
			// Make a listing of all operators in this room
			$body .= $txt[238]."<Br>";
			$mute = explode(";",$x7c->room_data['voiced']);
			foreach($mute as $key=>$val){
				if($val < 0){
					$val = $val*-1;
					$username = get_user_by_id($val);
					$body .= "<Br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"index.php?act=roomcp&cp_page=mutes&room=$_GET[room]&revoke=$username\">$username</a>";
				}
			}
			
			$body .= "<Br><Br><form action=\"index.php?act=roomcp&cp_page=mutes&room=$_GET[room]\" method=\"post\">
			$txt[93]: <input type=\"text\" name=\"add\" class=\"text_input\"> <input type=\"submit\" class=\"button\" value=\"$txt[93]\">
			</form><Br><Br>";
			
		}elseif($_GET['cp_page'] == "logs"){
		
			$head = $txt[240];
		
			if($x7c->permissions['access_room_logs'] == 0){
			
				$body = "--".$txt[216];
				
			}else{
			
				include_once("./lib/logs.php");
				$log = new logs(1,$_GET['room']);
			
				if(isset($_GET['subact'])){
					if($_GET['subact'] == 'enable'){
						// Enable logging
						include_once("./lib/rooms.php");
						change_roomsetting($_GET['room'],"logged",1);
						$x7c->room_data['logged'] = 1;
						
					}elseif($_GET['subact'] == 'disable'){
						// Disable logging
						include_once("./lib/rooms.php");
						change_roomsetting($_GET['room'],"logged",0);
						$x7c->room_data['logged'] = 0;
					
					}elseif($_GET['subact'] == 'clear'){
						// Clear the log
						$log->clear();
						$log->log_size = 0;
					
					}
				}
			
				if($x7c->room_data['logged'] == 1)
					$body = "$txt[242].  <a href=\"index.php?act=roomcp&cp_page=logs&subact=disable&room=$_GET[room]\">[$txt[245]]</a><Br><Br>";
				else
					$body = "$txt[243].  <a href=\"index.php?act=roomcp&cp_page=logs&subact=enable&room=$_GET[room]\">[$txt[244]]</a><Br><Br>";
				
				// Display file size information
				if($x7c->settings['max_log_room'] != 0){
					$percent1 = round($log->log_size/$x7c->settings['max_log_room'],2)*100;
					$percent1 .= "%";
					$percent2 = 100-$percent1."%";
					$fs1 = round($log->log_size/1024,2);
					$fs2 = round(($x7c->settings['max_log_room']-$log->log_size)/1024,2);
				}else{
					$percent1 = $txt[248];
					$percent2 = $txt[248];
					$fs1 = round($log->log_size/1024,2);
					$fs2 = "($txt[248])";
				}

				$txt[246] = eregi_replace("_p","$percent1",$txt[246]);
				$txt[247] = eregi_replace("_p","$percent2",$txt[247]);
				$txt[246] = eregi_replace("_s","$fs1",$txt[246]);
				$txt[247] = eregi_replace("_s","$fs2",$txt[247]);
				$body .= "$txt[246]<Br>$txt[247]<Br><Br>";
				
				// If logging is enabled then show the log
				if($x7c->room_data['logged'] == 1){
				
					// Get the log contents
					$contents = $log->get_log_contents_per_date();
					// Calculate the pages display
					$selected_date="";
					
					if(isset($_POST['date']))
						$selected_date="value=\"$_POST[date]\"";
					
					$pages = '<script language="javascript" type="text/javascript" src="lib/datetimepicker.js" ></script>
					<script language="javascript" type="text/javascript">
						function jump_to_date(date_str) {
							document.getElementById(\'demo1\').value = date_str;
							document.forms[0].submit();

						}
					</script>
					<form id="dateform" name="dateform1" action="index.php?act=roomcp&cp_page=logs&room='.$_GET['room'].'" method="post">
						<input type="Text" name="date" id="demo1" maxlength="15" size="15" '.$selected_date.'><a href="javascript:NewCal(\'demo1\',\'ddmmyyyy\',false,24)"><img src="graphic/cal.gif" width="16" height="16" border="0" alt="Pick a date"></a>
					</form>
					';
					
					$body .= "<Br>{$pages}<hr>";
					
					include_once("./lib/message.php");
					$body .= '<div style="background: black; color: white;">';
					$header = false;
					$count = 0;
					foreach($contents as $linenum=>$entry) {
						// Get date and sender
						$count++;
						$message="";
						$match="";
						if(preg_match("/^(.+?);\[(.+?)\]/",$entry,$match)){
							$entry = preg_replace("/^(.+?);\[(.+?)\]/","",$entry);
							$date = date($x7c->settings['date_format_full'],$match[1]);
							$sender = $match[2];
							// Get message
							$message = $entry;	
						}
						else{
							$message = "<b>Warning: wrong log format </b>".$entry;
						}
						if (!$header) {
							if ($match) {
								$date_short = date("j/n/Y", $match[1]);
								$body .= "<a href=\"#\" onClick=\"javascript: jump_to_date('$date_short');\"> &lt;&lt;&lt; $date_short</a><br><br>";
							}
							$header = true;
						}
						else if ($count == sizeof($contents)) {
							if ($match) {
								$date_short = date("j/n/Y", $match[1]);
								$body .= "<a href=\"#\" onClick=\"javascript: jump_to_date('$date_short');\">$date_short &gt;&gt;&gt;</a><br><br>";
							}
							
						}
						else {
							$body .= "<b>$sender</b>[$date]: $message<br><br>";
						}
						
					}
					
					$body .= "</div><hr>$pages<Br><Br>";
				}
				
			}
		
		}
		
		// THis mini-function determines what the active section link is
		function whatsmyclass($id){
			$x = $_GET['cp_page'];
			
			if($x == $id)
				return " class=\"ucp_sell\"";
			else
				return " class=\"ucp_cell\" onMouseOver=\"javascript: this.className='ucp_sell'\" onMouseOut=\"javascript: this.className='ucp_cell'\"  onClick=\"javascript: window.location='./index.php?act=roomcp&cp_page=$id&room=$_GET[room]'\"";
		}
		
		// Add the menu to the body
		$cbody = "<div align=\"center\">
			<table border=\"0\" width=\"95%\" class=\"ucp_table\" cellspacing=\"0\" cellpadding=\"0\">
				<tr valign=\"top\">
					<td width=\"20%\" height=\"100%\">
						<table class=\"ucp_table2\" height=\"100%\" border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">
							<tr>
								<td width=\"100%\"".whatsmyclass("main").">$txt[137]</td>
							</tr>";

		if($x7c->permissions['make_rooms'] == 1)
			$cbody .= "
							<tr>
								<td width=\"100%\"".whatsmyclass("settings").">$txt[218]</td>
							</tr>";

		if($x7c->permissions['access_room_logs'] == 1)
			$cbody .= "			<tr>
									<td width=\"100%\"".whatsmyclass("logs").">$txt[240]</td>
								</tr>";
							
		$cbody .= 				"<tr valign=\"top\">
								<td width=\"100%\" class=\"ucp_cell\" style=\"cursor: default;\" height=\"100%\"><Br>";
		
		//if($x7c->settings['single_room_mode'] == "")
		//	$cbody .= 					"<a href=\"./index.php\">[$txt[29]]</a><Br>";
			
		$cbody .= 						"<a href=\"#\" onClick=\"javascript: window.close();\">[$txt[133]]</a><Br><Br>
								</td>
							</tr>";
							
		$cbody .=
						"</table>
					</td>
					<Td width=\"5\" class=\"ucp_divider\">&nbsp;</td>
					<td class=\"ucp_bodycell\">$body</td>
				</tr>
			</table>
			</div>";
		
		$print->normal_window($head,$print->ss_ucp.$cbody);
	}
	
?>
