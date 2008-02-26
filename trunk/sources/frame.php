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
//		X7 Chat Version 2.0.5
//		Released Jan 6, 2007
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

	// changing this to false will display all previous messages upon entering a chat room
	$x7c->settings['use_old_sessionmsg_mode'] = false;

	// changing this to true will cause enter messages for every other user currently in the room
	// to be shown to the user who is entering
	$x7c->settings['use_old_sessionentermsg_mode'] = false;
	
	$x7c->settings['show_enter_message'] = false;

	if(!isset($_GET['frame']))
		$_GET['frame'] = 'main';

	
	if(!isset($_GET['room']))
		die("Fatal error, room name not set.");	
	
	if(isset($_GET['delete']))
		if($x7c->permissions['admin_panic']){
			include("./lib/message.php");
			$db->DoQuery("DELETE FROM {$prefix}messages WHERE id='{$_GET['delete']}'");
			delete_communication($_GET['delete'],$_GET['room']);
			//echo "<html><body onload=\"javascript= window.focus=opener; window.close(self);\"></body></html>";
			return;
		}

	switch($_GET['frame']){
		case "update":
		
			// Make sure they are not trying to cache this page
			header("Content-type: text/plain; charset=UTF-8");
			header("Cache-Control: no-cache");
			header("Expires: Thu, 1 Jan 1970 0:00:00 GMT");

			// This is the update frame, output raw data with no standard HTML code
			if(!isset($_GET['listhash']))
				$_GET['listhash'] = '';
			if(!isset($_GET['startfrom']))
				$_GET['startfrom'] = 0;
			else
				$_GET['startfrom'] = intval($_GET['startfrom']);
			$endon = $_GET['startfrom'];
			
			$query = $db->DoQuery("SELECT position FROM {$prefix}users WHERE username='$x7s->username'");
			$row = $db->Do_Fetch_Assoc($query);
			
			if($row['position'] != $_GET['room'] && $_GET['startfrom']>0){
				include('./lib/alarms.php');
				double_login($_GET['room']);
				die("9;Non puoi aprire due finestre contemporaneamente;index.php");
			}

			// See if the room is being loaded for the first time (create a new session)
			if($_GET['startfrom'] == 0){
				$db->DoQuery("DELETE FROM {$prefix}online WHERE name='$x7s->username' AND room='$_GET[room]'");

				$endon = -1;

				$x7c->room_data['greeting'] = preg_replace("/@/","74ce61f75c75b155ea7280778d6e8183",$x7c->room_data['greeting']);
				$x7c->room_data['greeting'] = preg_replace("/\|/","74ce61f75c75b155ea7280778d6e8181",$x7c->room_data['greeting']);
				$x7c->room_data['greeting'] = preg_replace("/;/","74ce61f75c75b155ea7280778d6e8182",$x7c->room_data['greeting']);

				//echo utf8_encode("8;<b><font color=\"{$x7c->settings['system_message_color']}\">{$x7c->room_data['greeting']}</font></b><br>|");
				$x7c->room_data['greeting'] = eregi_replace("'","\\'",$x7c->room_data['greeting']);
			}

			// Include some libaries
			//include("./lib/online.php");
			//include("./lib/message.php");
			// nevermind these libraries are shit reprogram them here:
			function format_timestamp($time){
				global $x7c;
				$time = $time+(($x7c->settings['time_offset_hours']*3600)+($x7c->settings['time_offset_mins']*60));
				return date("[".$x7c->settings['date_format']."]",$time);
			}

			// Are you allowed to see invisible users?
			if($x7c->permissions['c_invisible'] == 1)
				$invis = "";
			else
				$invis = "AND invisible<>'1' ";

			// Force online_time to be above the max refresh rate
			if($x7c->settings['online_time']*1000 < $x7c->settings['max_refresh'])
				$x7c->settings['online_time'] = ceil($x7c->settings['max_refresh']/1000)+5;

			$exp_time = time() - $x7c->settings['online_time'];
			$room_ops = explode(";",$x7c->room_data['ops']);
			$no_repeat_check = array();

			$listhash = '';
			$ops = '';
			$users = '';
			$total = 0;
			$your_record = array();
			$qitu = array();
			$oldlisthash = $_GET['listhash'];
			$_GET['listhash'] = explode(",",$_GET['listhash']);

			$query = $db->DoQuery("SELECT o.*,u.id FROM {$prefix}online o, {$prefix}users u WHERE o.room='$_GET[room]' AND u.username=o.name {$invis}ORDER BY o.name ASC");

			while($row = $db->Do_Fetch_Row($query)){

				if(isset($no_repeat_check[$row[7]]))
					continue;
				else
					$no_repeat_check[$row[7]] = true;
				$qitu[$row[7]] = $row[1];

				if($row[5] < $exp_time)
					continue;

				if($row[1] == $x7s->username){
					// This is you
					$your_record = $row;
				}

				if(in_array($row[7],$room_ops))
					// Add to op list
					$list_2_add =& $ops;
				else
					// Add to user list
					$list_2_add =& $users;

				$row[1] = preg_replace("/,/","74ce61f75c75b155ea7280778d6e8180",$row[1]);
				$list_2_add .= "$row[1],";
				$listhash .= "$row[7],";
				$total++;

				// Check to see if this user is entering/leaving/staying in place
				if(!in_array($row[7],$_GET['listhash'])){
					if($row[1] != '' && $x7c->settings['show_enter_message'] == true && ($x7c->settings['use_old_sessionentermsg_mode'] == true || $_GET['startfrom'] != 0))
						echo utf8_encode("8;" . preg_replace("/;/","74ce61f75c75b155ea7280778d6e8182","<span style=\"color: {$x7c->settings['system_message_color']};font-size: {$x7c->settings['sys_default_size']}; font-family: {$x7c->settings['sys_default_font']};\"><b>$row[1] $txt[43]</b></span><Br>") . "|");
				}else{
					unset($_GET['listhash'][array_search($row[7],$_GET['listhash'])]);
				}
			}

			if(count($your_record) == 0){
				// Test if the room is full
				if($total >= $x7c->room_data['maxusers'])
					echo "9;;./index.php?act=overload|";

				// Create a new record for you
				$time = time();
				$ip = $_SERVER['REMOTE_ADDR'];
				//Users can stay one chat per time
				$db->DoQuery("DELETE FROM {$prefix}online WHERE name='$x7s->username' AND room<>'{$_GET['room']}'");
				$db->DoQuery("INSERT INTO {$prefix}online VALUES('0','$x7s->username','$ip','$_GET[room]','','$time','{$x7c->settings['auto_inv']}')");
				$db->DoQuery("UPDATE {$prefix}users SET position='{$_GET['room']}' WHERE username='$x7s->username'");

			}else{
				// Update an old record
				$time = time();
				$db->DoQuery("UPDATE {$prefix}online SET time='$time' WHERE name='$x7s->username' AND room='$_GET[room]'");
			}

			// Handle leave messages
			foreach($_GET['listhash'] as $key=>$val){
				if($val != ''){
                                  if($x7c->settings['show_enter_message'] == true)
					echo utf8_encode("8;" . preg_replace("/;/","74ce61f75c75b155ea7280778d6e8182","<span style=\"color: {$x7c->settings['system_message_color']};font-size: {$x7c->settings['sys_default_size']}; font-family: {$x7c->settings['sys_default_font']};\"><b>$qitu[$val] $txt[44]</b></span><Br>") . "|");
				}
			}

			// Export stuff if needed
			if($oldlisthash != $listhash){
				$ops = preg_replace("/\|/","74ce61f75c75b155ea7280778d6e8181",$ops);
				$users = preg_replace("/\|/","74ce61f75c75b155ea7280778d6e8181",$users);
				$ops = preg_replace("/;/","74ce61f75c75b155ea7280778d6e8182",$ops);
				$users = preg_replace("/;/","74ce61f75c75b155ea7280778d6e8182",$users);
				echo utf8_encode("2;$ops|");
				echo utf8_encode("3;$users|");
				echo utf8_encode("4;$listhash|");
			}

			$offline_msgs = 0;
			$pm_time = time()-2*($x7c->settings['refresh_rate']/1000);
			$pm_etime = time()-4*($x7c->settings['refresh_rate']/1000);
			$private_msgs = 0;

			$query = $db->DoQuery("SELECT user,type,body_parsed,time,id,room FROM {$prefix}messages WHERE".
						/*user<>'$x7s->username'
						AND*/" (
							(id>'$_GET[startfrom]'
							AND (
								(room='$_GET[room]' AND (type='1' OR type='4')) OR
								(room='$x7s->username' AND type='3') OR
								(type='2') OR
								(room='$x7s->username:0' AND type='5' AND time<$pm_time) OR
								((room='$x7s->username' OR user='$x7s->username') AND type='10') OR
								(room='$x7s->username' AND (type='11' OR type='12' OR type='13'))
							)
							)
							OR (room='$x7s->username' AND type='6' AND time='0')
						)
						ORDER BY id ASC");

			if($db->error == 4){
				$query = eregi_replace("'","\\'",$query);
				$query = eregi_replace("[\n\r]","",$query);
				echo "9;;./index.php?act=panic&dump=$query&source=/sources/frame.php:155";
			}

			while($row = $db->Do_Fetch_Row($query)){

				if($row[1]!=6)
					$endon = $row[4];

				if($x7c->settings['use_old_sessionmsg_mode'] && $_GET['startfrom'] == 0)
					continue;

				if(!in_array($row[0],$x7c->profile['ignored'])){
					if(isset($toout))
						unset($toout);
					//$row[2] = eregi_replace("'","\\'",$row[2]);

					if($row[1] == 1){
						// See if they want a timestamp
						if($x7c->settings['disble_timestamp'] != 1)
							$timestamp = format_timestamp($row[3]);
						else
							$timestamp = "";

						//$toout = "<span class=\"other_persons\"><a class=\"other_persons\" onClick=\"javascript: window.open('index.php?act=pm&send_to=$row[0]','Pm$row[0]','location=no,menubar=no,resizable=no,status=no,toolbar=no,scrollbars=yes,width={$x7c->settings['tweak_window_large_width']},height={$x7c->settings['tweak_window_large_height']}');\">$row[0]</a>$timestamp:</span> $row[2]<br>";
						
						$toout = "<span class=\"other_persons\">$row[0]$timestamp:</span>";
						
						if($x7c->permissions['admin_panic'])
							$toout .= "<a onClick=\"javascript: do_delete($row[4])\">[Delete]</a>";
						
						$toout.="$row[2]<br>";;
						
						
						
					}elseif($row[1] == 2 || $row[1] == 3 || $row[1] == 4){
						$toout = "$row[2]";
						
					}elseif($row[1] == 6){
						$offline_msgs++;
					}elseif($row[1] == 5){
						$row[0] = preg_replace("/@/","74ce61f75c75b155ea7280778d6e8183",$row[0]);
						$row[0] = preg_replace("/\|/","74ce61f75c75b155ea7280778d6e8181",$row[0]);
						$row[0] = preg_replace("/;/","74ce61f75c75b155ea7280778d6e8182",$row[0]);
						echo utf8_encode("7;$row[0]|");
						$db->DoQuery("UPDATE {$prefix}messages SET time='$pm_etime' WHERE id='$row[4]'");
					}elseif($row[1] == 10){			
						if($row[0] != $x7s->username)		
							$toout = "<span class=\"sussurro\">[$row[0]] ti ha mandato un sussurro:".$row[2]."</span><br>";
						else
							$toout = "<span class=\"sussurro\">Hai inviato un sussurro a [$row[5]]:".$row[2]."</span><br>";
					}elseif($row[1] == 11){
						//This is a panic upate
						$db->DoQuery("DELETE FROM {$prefix}messages WHERE type='11' AND room='$x7s->username'");
						echo utf8_encode("11;$row[2]|");
					}elseif($row[1] == 12){
						//This is a refresher force
						$db->DoQuery("DELETE FROM {$prefix}messages WHERE type='12' AND room='$x7s->username'");
						echo utf8_encode("12;$row[2]|");
					}elseif($row[1] == 13){
						$db->DoQuery("DELETE FROM {$prefix}messages WHERE type='13' AND room='$x7s->username'");
						echo utf8_encode("13;$row[2]|");
					}

					if(isset($toout)){
						$toout = preg_replace("/\|/","74ce61f75c75b155ea7280778d6e8181",$toout);
						$toout = preg_replace("/;/","74ce61f75c75b155ea7280778d6e8182",$toout);
						echo utf8_encode("8;$toout<br>|");
					}

				}
			}

			echo utf8_encode("5;$endon|");
			echo utf8_encode("6;$offline_msgs|");

			// Check bans
			$bans = $x7p->bans_on_you;

			foreach($bans as $key=>$row){

				// If a row returned and they don't have immunity then thrown them out the door and lock up
				if($row != "" && $x7c->permissions['ban_kick_imm'] != 1){
					if($row[1] == "*"){
						// They are banned from the server
						$txt[117] = eregi_replace("_r",$row[5],$txt[117]);
						echo utf8_encode("9;$txt[117];./index.php|");
					}elseif($row[1] == $x7c->room_data['id'] && $row[4] == 60){
						// They are kicked from this room
						$txt[115] = eregi_replace("_r",$row[5],$txt[115]);
						echo utf8_encode("9;$txt[115];./index.php?act=kicked|");
						$db->DoQuery("DELETE FROM {$prefix}online WHERE name='$x7s->username' AND room='$_GET[room]'");
					}elseif($row[1] == $x7c->room_data['id']){
						// They are banned from this room
						$txt[116] = eregi_replace("_r",$row[5],$txt[116]);
						echo utf8_encode("9;$txt[116];./index.php?act=kicked|");
						$db->DoQuery("DELETE FROM {$prefix}online WHERE name='$x7s->username' AND room='$_GET[room]'");
					}
				}
			}

			// See if they have used up all their allowed bandwidth
			if($x7c->settings['log_bandwidth'] == 1){
				if($BW_CHECK){
					echo "9;;./index.php|";
				}
			}

		break;
		case "send":
			//Check if user is online in this room
			$query = $db->DoQuery("SELECT count(*) AS num FROM ${prefix}users WHERE username='$x7s->username' AND position='$x7c->room_name'");
			$row = $db->Do_Fetch_Assoc($query);
			
			
			if($row['num'] == 0)
				break;
				
			//Mappa is a fake room... we exploit it for update offline message and online status of users within mtha map
			if($_GET['room']=='Mappa')
				break;
				
			// Include the message library
			include("./lib/message.php");
			
			// Make sure the message isn't null
			if(@$_GET['msg'] != "" && !eregi("^@.*@",@$_GET['msg'])){
			

				// Save the style settings they used for next time
				//$x7c->edit_user_settings("default_font",$_GET['curfont']);
				//$x7c->edit_user_settings("default_size",$_GET['cursize']);
				//$x7c->edit_user_settings("default_color",$_GET['curcolor']);

				if(strlen($_GET['msg']) < $x7c->settings['min_post'] || strlen($_GET['msg']) > $x7c->settings['max_post'])
					break;

				
				// Get the styles
				$starttags = "";
				$endtags = "";
				//$color = $_GET['curcolor'];
				//$size = eregi_replace(" Pt","pt",$_GET['cursize']);
				//$font = $_GET['curfont'];

				// Make sure incoming values are safe
				$_GET['msg'] = eregi_replace("<","&lt;",$_GET['msg']);
				$_GET['msg'] = eregi_replace(">","&gt;",$_GET['msg']);
				//$color = eregi_replace("<","&lt;",$color);
				//$size = eregi_replace("<","&lt;",$size);
				//$font = eregi_replace("<","&lt;",$font);
				$_GET['msg'] = eregi_replace("\n", " ",$_GET['msg']);

				//If we are in panic
				if($x7c->settings['panic']){
					//If user is not a master and room is not panic_free
					if(!$x7c->permissions['admin_panic'] && !$x7c->room_data['panic_free']){
						if($x7s->panic >= $x7s->max_panic){
							$_GET['msg']="<span style=\"color: red;\">Si piega in un angolo terrorizzato e impossibilitato a compiere qualunque azione</span>";
						}
						/*if($x7s->panic > $x7s->max_panic){
							break;
						}*/
						
					
					}
				}

				$starttags .= "[color=#000000][size=10 pt][font=arial]";

				// Add the styles
				/*if($_GET['bold'] == 1){
					$starttags .= "[b]";
					$endtags .= "[/b]";
				}
				if($_GET['italic'] == 1){
					$starttags .= "[i]";
					$endtags .= "[/i]";
				}
				if($_GET['under'] == 1){
					$starttags .= "[u]";
					$endtags .= "[/u]";
				}*/

				$endtags .= "[/color][/size][/font]";

				$parsed_msg = "<span class=\"locazione_display\">[".$_GET['locazione']."]</span><br>"." ".$_GET['msg'];

				// Make sure the user has a voice
				if($x7c->permissions['room_voice'] == 1){
					send_message($parsed_msg,$x7c->room_name);

				}else{
					// The user doesn't have a voice, alert them
					alert_user($x7s->username,$txt[42]);
				}

			//This is a sussuro
			}elseif(eregi("^@.*@",@$_GET['msg'])){
				// User has done a command
				//include("./lib/irc.php");
				
				$_GET['msg'] = eregi_replace("<","&lt;",$_GET['msg']);
				$_GET['msg'] = eregi_replace(">","&gt;",$_GET['msg']);
				$_GET['msg'] = eregi_replace("\n", " ",$_GET['msg']);
				$parsed_msg = $_GET['msg'];
				
				if($x7c->permissions['room_voice'] == 1){
					send_message($parsed_msg,$x7c->room_name,1);

				}else{
					// The user doesn't have a voice, alert them
					alert_user($x7s->username,$txt[42]);
				}

			}

		break;
		default:
			//We must check if room is not full
			if($x7c->permissions['c_invisible'] == 1)
				$invis = "";
			else
				$invis = "AND invisible<>'1' ";
			
			$total = 0;	
			$query = $db->DoQuery("SELECT count(*) AS num FROM {$prefix}online o, {$prefix}users u WHERE o.room='$_GET[room]' AND u.username=o.name {$invis}ORDER BY o.name ASC");

			$row = $db->Do_Fetch_Assoc($query);
				
			if($row['num'] >= $x7c->room_data['maxusers'])
				echo "9;;./index.php?act=overload|";

			// Create a new record for you
			$time = time();
			$ip = $_SERVER['REMOTE_ADDR'];
			
			//Users can stay one chat per time, so we must delete them from other rooms
			$db->DoQuery("DELETE FROM {$prefix}online WHERE name='$x7s->username'");
			$db->DoQuery("INSERT INTO {$prefix}online VALUES('0','$x7s->username','$ip','$_GET[room]','','$time','{$x7c->settings['auto_inv']}')");			
			$db->DoQuery("UPDATE {$prefix}users SET position='{$_GET['room']}' WHERE username='$x7s->username'");
			
				
			//Here we start with HTML
			echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
			echo "<html dir=\"$print->direction\"><head><title>{$x7c->settings['site_name']} -- $_GET[room]</title>";
			echo $print->style_sheet;
			echo $print->ss_mini;
			echo $print->ss_chatinput;
			echo $print->ss_uc;

			?>
				</head>
					<body onload="javascript: do_initial_refresh();"> 
					<iframe style='position: absolute;visibility: hidden;' src="index.php?act=frame&frame=send&room=<?PHP echo $x7c->room_name; ?>" name="send" frameborder="0" scrolling="no" marginwidth="0" marginheight="0" noresize="true"></iframe>
	
					<script language="javascript" type="text/javascript">
						listhash = '';
						startfrom = 0;
						newMail = 0;

						function do_initial_refresh(){
							// Create object
							chatRefresh = setInterval('do_refresh()','<?PHP echo $x7c->settings['refresh_rate']; ?>');
							document.chatIn.counter.value=document.chatIn.msgi.value.length;
							do_refresh();
							
							
						}

						function requestReady_channel1(){
							if(httpReq1){
								if(httpReq1.readyState == 4){
									if(httpReq1.status == 200){

										// Request is all ready to go

										//document.getElementById('debug').innerHTML = httpReq1.responseText.replace(/</g,'&lt;');
										playSound = 0;
										modification=0;
										
										
										//document.getElementById('message_window').innerHTML += httpReq1.responseText;
										

										var dataArray = httpReq1.responseText.split("|");
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

												playSound = 2;

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
													
											}else if(dataSubArray[0] == '7'){
												// Private message
												dataSubArray[1] = restoreText(dataSubArray[1]);
												window.open('index.php?act=pm&send_to=' + dataSubArray[1],'Pm' + dataSubArray[1],'location=no,menubar=no,resizable=no,status=no,toolbar=no,scrollbars=yes,width=<?PHP echo $x7c->settings['tweak_window_large_width']; ?>,height=<?PHP echo $x7c->settings['tweak_window_large_height']; ?>');

												alertText = '<?PHP echo $txt[511]; ?>';
												alertText = alertText.replace('<a>',"<a style=\"cursor: pointer;\" onClick=\"window.open('index.php?act=pm&send_to=" + dataSubArray[1] + "','Pm" + dataSubArray[1] + "','location=no,menubar=no,resizable=no,status=no,toolbar=no,scrollbars=yes,width=<?PHP echo $x7c->settings['tweak_window_large_width']; ?>,height=<?PHP echo $x7c->settings['tweak_window_large_height']; ?>');\">");
												document.getElementById('message_window').innerHTML += "<span style=\"color: <?PHP echo $x7c->settings['system_message_color']; ?>;font-size: <?PHP echo $x7c->settings['sys_default_size']; ?>; font-family: <?PHP echo $x7c->settings['sys_default_font']; ?>;\"><b>" + alertText + "</b></span><Br>";

												if(playSound == 0)
													playSound = 1;

											}else if(dataSubArray[0] == '8'){
												// Message
												dataSubArray[1] = restoreText(dataSubArray[1]);
												document.getElementById('message_window').innerHTML +=dataSubArray[1];
												
												modification=1;
												
												if(playSound == 0)
													playSound = 1;

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
										

											// Scroll to bottom
											if(modification)
												document.getElementById('message_window').scrollTop = 65000;

										}

										if(<?PHP echo $x7c->settings['disable_sounds']; ?> != 1 && playSound != 0){

											if(playSound == 1){
												try { document.snd_msg.Play(); } catch(e) {}
											}else{
												try { document.snd_enter.Play(); } catch(e) {}
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
							url = './index.php?act=frame&frame=update&room=<?PHP echo $x7c->room_name; ?>&listhash=' + listhash + '&startfrom=' + startfrom + '&nc=' + nocache;							if(window.XMLHttpRequest){
								try {
									httpReq1 = new XMLHttpRequest();
								} catch(e) {
									httpReq1 = false;
								}
							}else if(window.ActiveXObject){
								try{
									httpReq1 = new ActiveXObject("Msxml2.XMLHTTP");
								}catch(e){
									try{
										httpReq1 = new ActiveXObject("Microsoft.XMLHTTP");
									}catch(e){
										httpReq1 = false;
									}
								}
							}
							httpReq1.onreadystatechange = requestReady_channel1;
							httpReq1.open("GET", url, true);
							httpReq1.send("");
						}
						
						function do_delete(msgid){
							jd=new Date();
							nocache = jd.getTime();
							url = './index.php?act=frame&delete='+msgid+'&room=<?PHP echo $x7c->room_name; ?>';		if(window.XMLHttpRequest){
								try {
									httpReq1 = new XMLHttpRequest();
								} catch(e) {
									httpReq1 = false;
								}
							}else if(window.ActiveXObject){
								try{
									httpReq1 = new ActiveXObject("Msxml2.XMLHTTP");
								}catch(e){
									try{
										httpReq1 = new ActiveXObject("Microsoft.XMLHTTP");
									}catch(e){
										httpReq1 = false;
									}
								}
							}
							httpReq1.onreadystatechange = requestReady_channel1;
							httpReq1.open("GET", url, true);
							httpReq1.send("");
							alert("Messaggio cancellato");
						}
						

					</script>
						
					
						<script language="javascript" type="text/javascript">
							SelectorMenu = new Array();
							SelectorMenu['fontselector'] = 0;
							SelectorMenu['sizeselector'] = 0;
							fontTimeout = "";
							sizeTimeout = "";

							function action_select(sel){
								myaction = sel.options[sel.selectedIndex].value;
								if(myaction != ""){
									document.chatIn.msgi.value = document.chatIn.msgi.value + myaction +" ";
								}
								sel.selectedIndex=0;
								document.chatIn.msgi.focus();
								
							}
							
							function doSelect(object){
								object.className = 'selected';
							}
							function doDeSelect(object){
								object.className = '';
							}

							function ClickedSelector(menu){
								popUpAddr = document.getElementById(menu).style
								if(SelectorMenu[menu] == 0){
									popUpAddr.visibility='visible';
									SelectorMenu[menu] = 1;
								}else{
									popUpAddr.visibility='hidden';
									SelectorMenu[menu] = 0;
								}
							}

							function closeMenu(menu){
								popUpAddr = document.getElementById(menu).style
								popUpAddr.visibility='hidden';
								SelectorMenu[menu] = 0;
							}

							function doClickFont(font){
								ClickedSelector('fontselector');
								document.chatIn.curfont.value=font;
								document.getElementById('curfontd').innerHTML=font;
							}

							function DoClickSize(in_font){
								ClickedSelector('sizeselector');

								in_font = in_font.replace(/[a-z]*$/i,"");

								if(in_font < <?PHP echo $x7c->settings['style_min_size']; ?>){
									in_font = "<?PHP echo $x7c->settings['style_min_size']; ?>";
								}

								<?PHP
								$max_size = $x7c->settings['style_max_size'];
								if($max_size != 0){
									echo "if(in_font > $max_size){\n
										in_font = \"$max_size\";\n
									}\n";
								}
								?>

								document.chatIn.cursize.value=in_font+" Pt";
								document.getElementById('cursized').innerHTML=in_font+" Pt";
							}

							function styleOut(object,name){
								ref = "itemh = document.chatIn."+name;
								eval(ref);
								if(itemh.value == 0){
									object.className='boldtxt';
								}
							}

							function styleClicked(object,name){
								ref = "itemh = document.chatIn."+name;
								eval(ref);
								if(itemh.value == 0){
									object.className='boldtxtdown';
									itemh.value = 1;
								}else{
									object.className='boldtxt';
									itemh.value = 0;
								}
							}

							function styleOver(object,name){
								ref = "itemh = document.chatIn."+name;
								eval(ref);
								if(itemh.value == 0){
									object.className='boldtxtover';
								}
							}

							function msgSent(){
								message = document.chatIn.msgi.value;
								message = message.replace(/\+/gi,"%2B");
								document.chatIn.msg.value=message;
								
								if(!message.match(/^@/)){
									if(message.length < <?PHP echo $x7c->settings['min_post'];?>){
										alert("Il post è troppo corto - deve essere almeno <?PHP echo $x7c->settings['min_post'];?> caratteri");
										return false;
									}
									if(message.length > <?PHP echo $x7c->settings['max_post'];?>){
										alert("Il post è troppo lungo - sono consentiti al max <?PHP echo $x7c->settings['max_post'];?> caratteri");
										return false;
									}
								}
								
								message = message.replace(/%2B/gi,"+");
								if(message != ""){


									// Some special things
									if(message.match(/^\/clear/)){
										document.getElementById('message_window').innerHTML = '';
										document.chatIn.msg.value='';
									}
									if(message.match(/^\/debug_on/)){
										document.getElementById('debug').style.display = 'block';
										document.chatIn.msg.value='';
									}
									if(message.match(/^\/debug_off/)){
										document.getElementById('debug').style.display = 'none';
										document.chatIn.msg.value='';
									}

									// Parse/Add styles
									color = "black";
									size = "10 pt";
									size = size.replace(" Pt","pt");
									font = "Verdana";
									starttags = "<span style=\"font-family:"+font+"; color:"+color+"; font-size:"+size+"\">";
									endtags = "</span>";

									message = message.replace(/</gi,"&lt;");


	

									<?PHP
									// Do Keyword parsing, Smilie parsing and filter parsing
									include("./lib/filter.php");
									$msg_filter = new filters($_GET['room']);
									echo $msg_filter->filter_javascript();
									?>

									// Add styles to message
									message = starttags+message+endtags;

									timestamp = '';
									// Do timestamp
									<?PHP
										if($x7c->settings['disble_timestamp'] != 1){
											?>
												d = new Date();

												hours = ""+d.getHours();
												mins = ""+d.getMinutes();
												secs = ""+d.getSeconds();

												<?PHP
													// The following is a bunch of javascript that emulates the PHP's date() function to a small extent
													//  PHP date |	JAVASCRIPT variable
													$dc['a'] = "if(hours > 12)\njva = 'pm';\nelse\njva = 'am';\n\n";
													$dc['A'] = "if(hours > 12)\njvA = 'PM';\nelse\njvA = 'AM';\n\n";
													$dc['g'] = "if(hours > 12)\njvg = hours-12;\nelse\njvg = hours;\n\n";
													$dc['G'] = "jvG = hours;";
													$dc['h'] = "if(hours > 12)\njvh = ''+(hours-12);\nelse\njvh = ''+hours;\nif(jvh.length == 1)\njvh = '0'+jvh;\n\n";
													$dc['H'] = "jvH = hours;\nif(jvH.length == 1)\njvH = '0'+jvH;\n\n";
													$dc['i'] = "jvi = ''+mins;\nif(jvi.length == 1)\njvi = '0'+jvi;\n\n";
													$dc['s'] = "jvs = ''+secs;\nif(jvs.length == 1)\njvs = '0'+jvs;\n\n";
													$dc['U'] = "jvU = d.getTime()/1000;\n\n";

													// The dateformat (Using PHP syntax only a,A,g,G,h,H,i,s and U are supported)
													$df = $x7c->settings['date_format'];

													// THis will be printed, only the needed javascript from above will be added
													$script = "";

													// replace the PHP symbols in $df with the javascript counterpart
													foreach($dc as $phps=>$js){
														$olddf = $df;

														// Preserve any special characters that are back slashed
														$df = ereg_replace("\\\\$phps","o_2R\n08_f",$df);

														// DO the switch
														$df = ereg_replace("$phps","\"+jv{$phps}+\"",$df);

														// Restore those characters who were preserved
														$df = ereg_replace("o_2R\n08_f","$phps",$df);

														// If there was a change then we need this javascript printed
														if($olddf != $df)
															$script .= $js;
													}
												?>
												<?PHP echo $script; ?>
												timestamp = "[<?PHP echo $df; ?>]";
											<?PHP
										}
									?>

									// Put it into screen
									//document.getElementById('message_window').innerHTML += '<span class="you"><?PHP echo $x7s->username; ?>'+timestamp+':</span> '+message+'<Br>';

									// Scroll the screen
									document.chatIn.msgi.value="";
									document.chatIn.msgi.focus();
									document.chatIn.counter.value=document.chatIn.msgi.value.length;
									document.getElementById('message_window').scrollTop = 65000;
								}
								return true;
							}

							// This function reads key presses
							document.onkeyup = kp;
							consec = -1;
							function kp(evt){
								if(evt)
									thisKey = evt.which;
								else
									thisKey = window.event.keyCode;
								if(thisKey == "13"){
									document.chatIn.button_send.click();
								}
								document.chatIn.counter.value=document.chatIn.msgi.value.length;

							}

							
							
							

						</script>
						


<div id="container">
	<div id="divchat">


<?PHP 
//This file include common layout for frame and map
	include('./sources/layout.html'); 

?>
  		<!-- IMMAGINE DELLA POLAROID (a seconda della stanza) -->
  		
  		<img style="position:absolute; top:0px; left:807px;" src="<?PHP echo $x7c->room_data['background']; ?>">
  		
					<div id="message_window"></div>
					
					<div style='clear: both;'></div>
						<form name="chatIn" method="get" action="index.php" target="send" onSubmit="return msgSent();">
							<div id='debug' style='display: none;'></div>

							
							<input type="hidden" name="act" value="frame">
							<input type="hidden" name="frame" value="send">
							<input type="hidden" name="room" value="<?PHP echo $_GET['room']; ?>">
						
							<?PHP 
							if($x7c->settings['panic']){
								$query = $db->DoQuery("SELECT panic FROM {$prefix}users WHERE username='$x7s->username'");
								if($row = $db->Do_Fetch_Assoc($query)){
									echo "<div id=\"panicdiv\">Panico: <input class=\"location\" type=\"text\" size=\"2\" style=\"text-align: right; color: white;\" value=\"".$row['panic']."\" name=\"panic\" disabled></div>";}
								}
							?>
						
							<div id="inputchatdiv">
								<table cellspacing=0 cellpadding=0>
									<tr><td><textarea name="msgi" class="msginput" autocomplete="off"></textarea>
									<input type="hidden" name="msg" value=""></td></tr>
									
									<tr><td><input type="text" class="location" name="locazione" value="locazione"/>
								
								
									<input class ="location" type="text" style="text-align: center; color: white;" name="counter" disabled value="0" size="4"/></td></tr>
							
								</table>
							</div>
							
							<div id="cmddiv">
								<table cellspacing=3 cellpadding=0>
									<tr><td>
											
									
										<select class="button" name="action" onChange="javascript: return action_select(this);">
										<option value="">Scelta Abilit&agrave;...</option>
										<option value="">------------------</option>
										<?PHP
											$query = $db->DoQuery("SELECT a.id AS id, 
												ua.value AS value, 
												a.name AS name 
												FROM {$prefix}userability ua, {$prefix}ability a
												WHERE ua.ability_id=a.id
												 AND username='$x7s->username'
												 ORDER BY a.name");
												 	
								 			while($row = $db->Do_Fetch_Assoc($query)){
								 				$string = "<option value=\"§".$row['id']."\">".$row['name']." ".$row['value']."</option>\n";
								 				echo $string;
								 			}
										?>
										</select>

										<select class="button" name="charact" onChange="javascript: return action_select(this);">
											<option value="">Caratteristica...</option>
											<option value="">------------------</option>
										<?PHP
											$query = $db->DoQuery("SELECT c.id AS id, 
													uc.value AS value, 
													c.name AS name 
													FROM {$prefix}usercharact uc, {$prefix}characteristic c
													WHERE uc.charact_id=c.id
												 	AND c.id!='fort'
												 	AND username='$x7s->username'
												 	ORDER BY c.name");
												 	
								 			while($row = $db->Do_Fetch_Assoc($query)){
								 				$string = "<option value=\"%".$row['id']."\">".$row['name']." ".$row['value']."</option>\n";
												echo $string;
											}
										?>
										</select>
									
										<select class="button" name="charact" onChange="javascript: return action_select(this);">
											<option value="">Oggetti...</option>
											<option value="">------------------</option>
										<?PHP
											$query = $db->DoQuery("SELECT id, name 
													FROM {$prefix}objects
													WHERE owner='$x7s->username'
												 	ORDER BY name");
												 	
								 			while($row = $db->Do_Fetch_Assoc($query)){
								 				$string = "<option value=\"°".$row['id']."\">".$row['name']."</option>\n";
												echo $string;
											}
										?>
										</select>
										
								<?PHP
									if($x7c->permissions['admin_panic'])
										echo "	<input name=\"img_btn\" type=\"button\" class=\"send_button\" value=\"Invia immagine\" onClick=\"javascript: window.open('index.php?act=images','Images','location=no,menubar=no,resizable=yes,status=no,toolbar=no,scrollbars=yes,width={$x7c->settings['tweak_window_large_width']},height={$x7c->settings['tweak_window_large_height']}');\">";
											
								?>
										<input name="button_send" type="submit" class="send_button" style="cursor: pointer;background: url(<?PHP echo $print->image_path; ?>send.gif);border: none;height: 20px;width: 55px;text-align: center;font-weight: bold;" onMouseOut="this.style.background='url(<?PHP echo $print->image_path; ?>send.gif)'" onMouseOver="this.style.background='url(<?PHP echo $print->image_path; ?>send_over.gif)'" value="<?PHP echo $txt[181]; ?>">
									</td></tr>
								</table>
							</div>
						</form>

					
	
	</div>
</div>
</body>
</html>
			<?PHP
		break;

	}
?>
