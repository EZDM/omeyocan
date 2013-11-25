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
//
// Current status of this file :: Messy, I recommend rewriting it entirly
//


	// This handles all type 1 (regular messages)
	function send_message($body,$room,$sussurro=0){
		global $x7s, $db, $prefix, $x7c, $txt;
		$time = time();
		
		// This is used for hunt mode
		$allow_user_img = ($sussurro == 3);
		$body_parsed = parse_message($body, 0, $allow_user_img);
		if($sussurro == 0){
			$db->DoQuery("INSERT INTO {$prefix}messages VALUES('0','$x7s->username',
				'1','$body','$body_parsed','$room','$time')");

			$db->DoQuery("UPDATE {$prefix}users SET daily_post = daily_post + 1
					WHERE username='$x7s->username'");
			$db->DoQuery("UPDATE {$prefix}rooms SET daily_post = daily_post + 1
					WHERE name='$room'");
			
			//If we are in panic... do panic update
			if($x7c->settings['panic'] && !$x7c->room_data['panic_free']){
				update_panic();
			}
			
		}else if($sussurro == 1){
			if(preg_match("/@.*@/",$body, $user)){
				$user[1] = preg_replace("/@/","",$user[0]);

				//Check if whisper is for everybody					
				if($user[1] == "_all_"){
					if($x7c->permissions['write_master']){
						$query = $db->DoQuery("SELECT name FROM {$prefix}online WHERE room='$room'");
	
						while($row = $db->Do_Fetch_Assoc($query)){
							if($row['name'] != $x7s->username){
								$db->DoQuery("INSERT INTO {$prefix}messages VALUES('0','$x7s->username','10','$body','$row[name]:$body_parsed','$room','$time')");
							}
						}
					}
					
				}else{
					//Check if users are in the same chatroom
					$query = $db->DoQuery("SELECT count(*) AS num FROM {$prefix}online WHERE name='{$user[1]}' AND room='$room'");
					$row = $db->Do_Fetch_Assoc($query);
				
					if($row['num'])
						$db->DoQuery("INSERT INTO {$prefix}messages VALUES('0','$x7s->username','10','$body','$user[1]:$body_parsed','$room','$time')");
				}
			}
		}else if($sussurro == 2 || $sussurro == 3){
			//Mastering message
			$db->DoQuery("INSERT INTO {$prefix}messages VALUES('0','$x7s->username','14','$body','$body_parsed','$room','$time')");
		}

		// Do logging if required
		if($x7c->room_data['logged'] == 1 && $room != "" && $x7c->settings['enable_logging'] == 1){
			include_once("./lib/logs.php");
			$log = new logs(1,$room);
			$log->add($x7s->username,$body_parsed);
		}

	}
	
	function send_refresh_message($body){
		global $x7s, $db, $prefix, $x7c, $txt;
		$time = time();
		
		$query = $db->DoQuery("SELECT * FROM {$prefix}online");
		while($row = $db->Do_Fetch_Assoc($query)){
			$user = $row['name'];
			$db->DoQuery("INSERT INTO {$prefix}messages VALUES('0','System','12','Refresh: $body','$body','$user','$time')");
			
		}
	}
	
	function update_panic(){
		global $x7s, $db, $prefix, $x7c, $txt;
		
			$x7s->panic++;
			$panic = $x7s->panic;
			
			$time = time();
			$db->DoQuery("UPDATE {$prefix}users SET panic='$panic' WHERE username='$x7s->username'");
			$db->DoQuery("INSERT INTO {$prefix}messages VALUES('0','System','11','Panic update: $panic','$panic','$x7s->username','$time')");
			
	}
	
	function delete_communication($id,$room){
		global $x7s, $db, $prefix, $x7c, $txt;
		$time = time();
		
		$query = $db->DoQuery("SELECT * FROM {$prefix}online WHERE room='$room'");
		while($row = $db->Do_Fetch_Assoc($query)){
			$user = $row['name'];
			$db->DoQuery("INSERT INTO {$prefix}messages VALUES('0','System','13','$id','$id','$user','$time')");
			
		}
	}

	// This handles all type 2 (system messages to all room)
	function send_global_message($body){
		global $x7s, $db, $prefix, $x7c;
		$time = time();
		$body_parsed = parse_message($body,1);
		$db->DoQuery("INSERT INTO {$prefix}messages VALUES('0','$x7s->username','2','$body','$body_parsed','','$time')");
	}

	// Sends a system message alert to a user (Type 3)
	function alert_user($user,$message){
		global $db, $prefix;
		$time = time();
		$message = make_sql_safe($message);
		$body_parsed = parse_message($message,1);
		$db->DoQuery("INSERT INTO {$prefix}messages VALUES('0','System','3','$message','$body_parsed','$user','$time')");
	}

	// Sends a system message alert to an entire private chat (Type 7)
	function alert_private_chat($user,$message){
		global $db, $prefix, $x7s;
		$time = time();
		$db->DoQuery("INSERT INTO {$prefix}messages VALUES('0','$x7s->username','7','$message','null','$user:0','$time')");
		$db->DoQuery("INSERT INTO {$prefix}messages VALUES('0','$user','7','$message','null','$x7s->username:0','$time')");
	}

	// Sends a system message alert to only 'you' in a private chat (Type 7)
	function alert_private_chat_you($user,$message){
		global $db, $prefix, $x7s;
		$time = time();
		$db->DoQuery("INSERT INTO {$prefix}messages VALUES('0','$user','7','$message','null','$x7s->username:0','$time')");
	}

	// Sends a system message alert to a room (Type 4)
	// The user argument is used for update messages like take/give ops/voice.
	function alert_room($room,$message,$user=""){
		global $db, $prefix;
		$time = time();
		if($user != "")
			$message = eregi_replace("_u",$user,$message);
		$body_parsed = parse_message($message,1);
		$db->DoQuery("INSERT INTO {$prefix}messages VALUES('0','System','4','$message','$body_parsed','$room','$time')");
	}

	// Parses styles
	function parse_message($message,$sysmsg=0,$allow_user_img=0){
		global $x7s, $x7c, $db, $prefix;
		// We look for the following tags:
		
		// Do Auto-URL linking
		if($x7c->settings['disable_autolinking'] != 1){
			$message = preg_replace("/(http:\/\/(.+?)\.[^ \[\"<]*)(.)/ie","autoparse_url(\"2\",\"$1\",\"$3\");",$message);
			$message = preg_replace("/(www\.(.+?)\.[^ \[\"<]*)(.)/ie","autoparse_url(\"1\",\"$1\",\"$3\");",$message);
			$message = preg_replace("/([^@\]\s]*)@(.+?)\.(.+?)([\s\[])/i","<a href=\"mailto: $1@$2.$3\">$1@$2.$3</a>$4",$message);
		}

		// See if Styles are off
		$styles_off = $x7c->settings['disable_styles'];
		if($sysmsg == 1)
			$styles_off = 1;

			//Action parse
			$pos = stripos($message,"&lt;");
			$open=0;
			while($pos){
				if(!$open){
					$first = substr($message, 0, $pos);
					$last = substr($message, $pos+4);
					$message = $first."<span class=\"action\">\"".$last;
					$open++;
					$pos = stripos($message,"&gt;", $pos);
				}
				else{
					$first = substr($message, 0, $pos);
					$last = substr($message, $pos+4);
					$message = $first."\"</span>".$last;
					$open--;
					$pos = stripos($message,"&lt;", $pos);
				}
				
			}

			if($open){
				$message.="\"</span>";
			}

			$message = preg_replace("/&gt;/i","",$message);
			$message = preg_replace("/&lt;/i","",$message);
			
			//Delete sussurro dest
			$message = preg_replace("/^@.*@/i","",$message);
			
			//Perform ability
			$action_regexp = "/&sect;([^;]+);/i";
			
			srand(time()+microtime());
			
			while(preg_match($action_regexp,$message, $action)){									
				$action_msg="";
				$query = $db->DoQuery("SELECT a.name AS ab_name, ua.value AS ab_value, uc.value AS char_value
							FROM {$prefix}userability ua, {$prefix}usercharact uc , {$prefix}ability a
							WHERE ua.ability_id=a.id
							 AND a.char=uc.charact_id
							 AND uc.username=ua.username
							 AND ua.username='$x7s->username' 
							 AND ua.ability_id='$action[1]'");
			 
				if($row = $db->Do_Fetch_Assoc($query)){					
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
			
			//Perform characteristic
			$charact_regexp = "/%([^;]+);/i";
			
			while(preg_match($charact_regexp,$message, $charact)){
													
				$charact_msg="";
				$query = $db->DoQuery("SELECT c.name AS ch_name, uc.value AS ch_value
							FROM {$prefix}usercharact uc , {$prefix}characteristic c
							WHERE uc.charact_id=c.id
							 AND uc.username='$x7s->username' 
							 AND uc.charact_id='$charact[1]'");
			 
				if($row = $db->Do_Fetch_Assoc($query)){					
					$roll = rand(1,14);
					$result = floor($row['ch_value'] - $roll) + 10;
					
					if($result < 7)
						$charact_msg="<span class=\"roll_neg\">{".$row['ch_name']." ".$result."}</span>";
					else if($result < 14)
						$charact_msg="<span class=\"roll_avg\">{".$row['ch_name']." ".$result."}</span>";
					else
						$charact_msg="<span class=\"roll_pos\">{".$row['ch_name']." ".$result."}</span>";
					
					
				}
				$message = preg_replace($charact_regexp, $charact_msg, $message, 1);
				
			}
			
			//Perform objects
			$obj_regexp = "/&deg;([0-9]+);/i";
			
			while(preg_match($obj_regexp,$message, $obj)){

				$obj_msg="";
				$query = $db->DoQuery("SELECT name, uses, equipped, visible_uses,
						random_img
						FROM {$prefix}objects
						WHERE id='$obj[1]'
						AND owner='$x7s->username'");

				if($row = $db->Do_Fetch_Assoc($query)){
					if(!$row['equipped']){
						$obj_msg="<span class=\"break\">{L\'oggetto ".
							$row['name']." non &egrave; equipaggiato}</span>";
					}
					else{
						$newusage = -1;        
						if($row['uses'] > 0){
							$newusage = $row['uses'] - 1;
							$db->DoQuery("UPDATE {$prefix}objects SET uses='$newusage' 
									WHERE id='{$obj[1]}'");
						}

						$left_usage = '';
						if($row['visible_uses'] && $newusage >= 0)
							$left_usage = " (usi rimasti: $newusage)";

						if($row['uses'] > 1 || $row['uses'] == -1){
							$obj_msg="<span class=\"roll_pos\">{Usa l\'oggetto ".
								$row['name'].$left_usage."}</span>";
						}
						else if($row['uses'] == 1){
							$obj_msg="<span class=\"break\">{Usa l\'oggetto ".
								$row['name']." che diventa inutilizzabile subito dopo ".
								"l\'azione}</span>";
						}
						else{
							$obj_msg="<span class=\"roll_neg\">{Tenta di utilizzare un".
								" oggetto inutilizzabile: ".$row['name']."}</span>";
						}

						include_once('./lib/alarms.php');
						object_usage($x7s->username, $obj[1], $row['uses']);
					}	
				}

				if ($row['random_img']) {
					$obj_msg .= pick_random_img($row['random_img']);
				}

				$message = preg_replace($obj_regexp, $obj_msg, $message, 1);

			}			
			
			//Perform image
			$img_regexp = "/&pound;([^;]+);/i";
			
			while(preg_match($img_regexp,$message, $img_url)){

				if($x7c->permissions['write_master'] || $allow_user_img){
					if(preg_match("/swf$/i",$img_url[1])){
						//This is specific for the server! (works only if the URL root and the DOCUMENT_ROOT point to the same place)
						$file = $_SERVER['DOCUMENT_ROOT'].$img_url[1];
						$size = getimagesize($file);
						$width= $size[0];
												
						$img_msg="<br><br>
									<object>
									<param name=\"movie\" width=\"$size[0]\" height=\"$size[1]\" value=\"".$img_url[1]."\">
									<param name=\"quality\" value=\"high\">
									<param name=\"allowScriptAccess\" value=\"sameDomain\" />
									<embed src=\"".$img_url[1]."\" width=\"$size[0]\" height=\"$size[1]\" quality=\"high\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\" allowScriptAccess=\"sameDomain\">
									</embed>
									</object>						
									<br><br>";	
					}
					else{
						$img_msg="<br><br><img src=\"".$img_url[1]."\" ><br><br>";
					}
				}
					
				$message = preg_replace($img_regexp, $img_msg, $message, 1);
				
			}
			
			//Perform generic dice
			$dice_regexp = "/\~([^;]+);/i";
			
			while(preg_match($dice_regexp,$message, $dice_value)){
				$dice_msg="";
				
				if(is_numeric($dice_value[1])){
					$result = rand(1,$dice_value[1]);
					$dice_msg="<span class=\"roll_avg\">{Tira un d$dice_value[1]:</span><span style=\"color: white; font-weight: bold;\"> $result </span><span class=\"roll_avg\">}</span>";
				}
		
				$message = preg_replace($dice_regexp, $dice_msg, $message, 1);
		
			}
			
			if(eregi("^\*", $message)){
				$message = preg_replace("/^\*/", "", $message);
				if($x7c->permissions['admin_panic'] || $allow_user_img)
					$message = "<div class=\"mastering\">".$message."</div>";
				else if($x7c->permissions['write_master'])
					$message = "<div class=\"ambient\">".$message."</div>";
			}
			else
				$message = "<span class=\"chatmsg\">".$message."</span>";
			
		

		// Put new lines in
		$message = eregi_replace("\n","<Br>",$message);
		$message = eregi_replace("\\\\n","<Br>",$message);

		return $message;
	}

	// This function helps with auto-url parsing
	function autoparse_url($startbit,$url,$extrabit){
		// Start bit tells us what kind of link its coming from (ie: www., http:// or E-Mail)
		// Extrabit is used to tell us if it is already in a link
		if($startbit == 1){
			// See if thsi www. link is already linked, if not link it
			if($extrabit != "\"" && $extrabit != "<")
				$url = "<a href=\"http://$url\" target=\"_blank\">$url</a>$extrabit";
			else
				$url = $url.$extrabit;

		}elseif($startbit == 2){
			// See if this http:// link is already linked, if not link it
			if($extrabit != "\"" && $extrabit != "<")
				$url = "<a href=\"$url\" target=\"_blank\">$url</a>$extrabit";
			else
				$url = $url.$extrabit;

		}

		return $url;
	}

	// The following functions handle offline messages

	// This function sends an offline message
	function send_offline_msg($to,$subject,$msg,$sender=''){
		global $x7s, $db, $prefix, $x7c;
		$time = time();

		if($sender=='')
			$sender=$x7s->username;

		$db->DoQuery("INSERT INTO {$prefix}messages VALUES('0','$sender','6',
			'$subject::$time::$msg','parsed_body','$to','0')");
	}

	// This function gets a list of all offline messages
	function get_offline_msgs(){
		global $x7s, $db, $prefix, $x7c;
		$return = array();
		$query = $db->DoQuery("SELECT id,user,body,time FROM {$prefix}messages WHERE type='6' AND room='$x7s->username' ORDER BY id DESC");
		while($row = $db->Do_Fetch_Row($query)){
			if(!in_array($row[1],$x7c->profile['ignored']))
				$return[$row[0]] = $row;
		}
		return $return;
	}

	// SInce the subject is stored in the body field we need a function to split the body and subject
	// A seconardy function of this isi it parses the message styles
	function offline_msg_split($body){
	       global $x7c;
		// 0 is the body
		$return[0] = preg_replace("/^(.+?)::(.+?)::/i","",$body);

		// 1 is the subject
		if(preg_match("/^(.+?)::/i",$body,$match))
			$return[1] = $match[1];
		else
			$return[1] = "";

		$tmp=preg_replace("/^(.+?)::/i","",$body);
		if(preg_match("/^(.+?)::/i",$tmp,$match) && is_numeric($match[1]))
			$return[2] = date($x7c->settings['date_format_full'], $match[1]);
		else
			$return[2] = "";

		return $return;
	}

	// This function marks a message as read
	function offline_markasread($mid){
		global $x7s, $db, $prefix;
		$db->DoQuery("UPDATE {$prefix}messages SET time='1' WHERE id='$mid' AND room='$x7s->username'");
	}

	// This function deletes an offline message
	function offline_delete($mid){
		global $x7s, $db, $prefix;
		if($mid=="_all_")
                      $db->DoQuery("DELETE FROM {$prefix}messages WHERE type='6' AND room='$x7s->username'");
                else
		      $db->DoQuery("DELETE FROM {$prefix}messages WHERE id='$mid' AND room='$x7s->username'");
	}

	// Counts a users offline messages
	function count_offline($user){
		global $db, $prefix;
		$query = $db->DoQuery("SELECT * FROM {$prefix}messages WHERE room='$user' AND type='6'");
		$total = 0;
		while($row = $db->Do_Fetch_Row($query))
			$total++;
		return $total;
	}

	function format_timestamp($time){
		global $x7c;
		$time = $time+(($x7c->settings['time_offset_hours']*3600)+($x7c->settings['time_offset_mins']*60));
		return date("[".$x7c->settings['date_format']."]",$time);
	}

  function pick_random_img($folder){
		$img = '';
	  if (!$folder)
			return '';

		if(preg_match("/\.\./", $folder)) {
      return '';
		}

		$basedir=dirname($_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']);
		$file_path=$basedir.'/'.$folder.'/';

		if (!file_exists($file_path)) {
			return '';
		}

		if ($dh = opendir($file_path)) {
			$file_array = array();
			while (($file = readdir($dh)) != false) {
				if(filetype($file_path.$file) == 'file') {
					$file_array[]=$file;
				}
			}

			if (count($file_array) > 0) {
				srand(time()+microtime());
				$img_file = $file_array[rand(0,count($file_array) - 1)];

				$img .= '<br>
					<div style="text-align: center;">
					<img src="'.$folder.'/'.$img_file.'" />
					</div><br>';
			}
		}

		return $img;
	}

?>
