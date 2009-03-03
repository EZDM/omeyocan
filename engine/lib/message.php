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

		// Check message size limit
		if($x7c->settings['maxchars_msg'] != 0 && strlen($body) > $x7c->settings['maxchars_msg']){
			alert_user($x7s->username,$txt[252]);
			return 0;
		}

		$body_parsed = parse_message($body);
		if($sussurro == 0){
			$db->DoQuery("INSERT INTO {$prefix}messages VALUES('0','$x7s->username','1','$body','$body_parsed','$room','$time')");
			
			//If we are in panic... do panic update
			if($x7c->settings['panic'] && !$x7c->room_data['panic_free']){
				update_panic();
			}
			
		}else if($sussurro == 1){
			if(preg_match("/@.*@/",$body, $user)){
				$user[1] = preg_replace("/@/","",$user[0]);

				//Check if whisper is for everybody					
				if($user[1] == "_all_"){
					$query = $db->DoQuery("SELECT name FROM {$prefix}online WHERE room='$room'");

					while($row = $db->Do_Fetch_Assoc($query)){
						if($row['name'] != $x7s->username){
							$db->DoQuery("INSERT INTO {$prefix}messages VALUES('0','$x7s->username','10','$body','$row[name]:$body_parsed','$room','$time')");
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
		}else if($sussurro == 2){
			//Mastering message
			$db->DoQuery("INSERT INTO {$prefix}messages VALUES('0','$x7s->username','14','$body','$body_parsed','$room','$time')");
		}

		// Do logging if required
		if($x7c->room_data['logged'] == 1 && $room != "" && $x7c->settings['enable_logging'] == 1){
			include("./lib/logs.php");
			$log = new logs(1,$room);
			$log->add($x7s->username,$body);
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
	function parse_message($message,$sysmsg=0){
		global $x7s, $x7c, $db, $prefix;
		// We look for the following tags:
		// [b][/b]
		// [i][/i]
		// [u][/u]
		// [color=][/color]
		// [size=][/size]
		// [font=][/font]

		// Filter First
		/*include_once("./lib/filter.php");
		$msg_filter = new filters(isset($_GET['room']) ? $_GET['room'] : '');
		$message = $msg_filter->filter_text($message);*/

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

		//We do not want styles anymore
		/*if($styles_off != 1){
			// Look for simple style tags (b, i, u)
			$message = preg_replace("/\[b\](.+?)\[\/b\]/i","<b>$1</b>",$message);
			$message = preg_replace("/\[u\](.+?)\[\/u\]/i","<u>$1</u>",$message);
			$message = preg_replace("/\[i\](.+?)\[\/i\]/i","<i>$1</i>",$message);

			// Compress the default styles (color, font and size at beginning and end of message)
			$store_color = "";
			$store_size = "";
			$store_font = "";

			$message = preg_replace("/\[\/color\]\[\/size\]\[\/font\]$/i","",$message);
			$message = preg_replace("/^\[color=([^\]]+)\]/ie","\$store_color='$1';",$message);
			$message = preg_replace("/^$store_color/i","",$message);
			$message = preg_replace("/^\[size=([^\]]+)\]/ie","\$store_size='$1';",$message);
			$message = preg_replace("/^$store_size/i","",$message);
			$message = preg_replace("/^\[font=([^\]]+)\]/ie","\$store_font='$1';",$message);
			$message = preg_replace("/^$store_font/i","",$message);

			// Ok add the tags back on after security check
			$store_color = check_font_color($store_color,"",0);
			$store_size = check_font_size($store_size,"",0);
			$store_font = check_font_family($store_font,"",0);
			$message = "<span style=\"color: $store_color; font-size: $store_size; font-family: $store_font;\">".$message;
			$message .= "</span>";

			// Color Tag
			while(preg_match("/\[color=([^\]]+)\](.+?)\[\/color\]/i",$message))
				$message = preg_replace("/\[color=([^\]]+)\](.+?)\[\/color\]/ie","check_font_color('$1','$2');",$message);

			// Size Tag
			while(preg_match("/\[size=([^\]]+)\](.+?)\[\/size\]/i",$message))
				$message = preg_replace("/\[size=([^\]]+)\](.+?)\[\/size\]/ie","check_font_size('$1','$2');",$message);

			// Font Tag
			while(preg_match("/\[font=([^\]]+)\](.+?)\[\/font\]/i",$message))
				$message = preg_replace("/\[font=([^\]]+)\](.+?)\[\/font\]/ie","check_font_family('$1','$2');",$message);
		}else{*/
			// Ok, so either the admin or user does not want styles

			$message = remove_chattags($message);

			// If this is a system message add on default sys_msg styles, otherwise just add on default syltes
			/*$sysmsg_color = $x7c->settings['system_message_color'];
			$default_color = $x7c->settings['sys_default_color'];
			$default_size = $x7c->settings['sys_default_size'];
			$default_font = $x7c->settings['sys_default_font'];*/
			
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
			
			//Location parse
			//$message = preg_replace("/\]/i","]</span>",$message);
			//$message = preg_replace("/\[/i","<span class=\"locazione_display\">[",$message);
			
			//Delete sussurro dest
			$message = preg_replace("/^@.*@/i","",$message);
			
			//Perform ability
			$action_regexp = "/§([^[:space:]]+)/i";
			
			while(preg_match($action_regexp,$message, $action)){
				srand(time()+microtime()/date("s"));
									
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
			$charact_regexp = "/%([^[:space:]]+)/i";
			
			while(preg_match($charact_regexp,$message, $charact)){
				srand(time()+microtime()/date("s"));
									
				$charact_msg="";
				$query = $db->DoQuery("SELECT c.name AS ch_name, uc.value AS ch_value
							FROM {$prefix}usercharact uc , {$prefix}characteristic c
							WHERE uc.charact_id=c.id
							 AND uc.username='$x7s->username' 
							 AND c.id!='fort'
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
			$obj_regexp = "/°([^[:space:]]+)/i";
			
			while(preg_match($obj_regexp,$message, $obj)){
									
				$obj_msg="";
				$query = $db->DoQuery("SELECT name, uses, equipped
							FROM {$prefix}objects
							WHERE id='$obj[1]'
							 AND owner='$x7s->username'");
			 
				if($row = $db->Do_Fetch_Assoc($query)){
				        if(!$row['equipped']){
				                $obj_msg="<span class=\"break\">{L\'oggetto ".$row['name']." non &egrave; equipaggiato}</span>";
				        }
				        else{
                                                if($row['uses'] > 1 || $row['uses'] == -1){
                                                        $obj_msg="<span class=\"roll_pos\">{Usa l\'oggetto ".$row['name']."}</span>";
                                                }
                                                else if($row['uses'] == 1){
                                                        $obj_msg="<span class=\"break\">{Usa l\'oggetto ".$row['name']." che diventa inutilizzabile subito dopo l\'azione}</span>";
                                                }
                                                else{
                                                        $obj_msg="<span class=\"roll_neg\">{Tenta di utilizzare un oggetto inutilizzabile: ".$row['name']."}</span>";
                                                }
                                                        
                                                if($row['uses'] > 0){
                                                        $newusage = $row['uses'] - 1;
                                                        $db->DoQuery("UPDATE {$prefix}objects SET uses='$newusage' WHERE id='{$obj[1]}'");
                                                }
                                        }
					
					
				}

				$message = preg_replace($obj_regexp, $obj_msg, $message, 1);
				
			}			
			
			//Perform image
			$img_regexp = "/ï¿½([^[:space:]]+)/i";
			
			while(preg_match($img_regexp,$message, $img_url)){

				if($x7c->permissions['admin_panic']){
					$img_msg="<br><img src=\"".$img_url[1]."\" ><br>";
				}
					
				$message = preg_replace($img_regexp, $img_msg, $message, 1);
				
			}
			
			if(eregi("^\*",@$_POST['msg'])){
				$message = preg_replace("/^\*/", "", $message);
				$message = "<div class=\"mastering\">".$message."</div>";
			}
			else
				$message = "<span class=\"chatmsg\">".$message."</span>";
			
		

		// Put new lines in
		$message = eregi_replace("\n","<Br>",$message);
		$message = eregi_replace("\\\\n","<Br>",$message);

		return $message;
	}

	// Strips chat tags out and returns raw message
	function remove_chattags($message){
		// Look for simple style tags (b, i, u)
		$message = preg_replace("/\[b\](.+?)\[\/b\]/i","$1",$message);
		$message = preg_replace("/\[u\](.+?)\[\/u\]/i","$1",$message);
		$message = preg_replace("/\[i\](.+?)\[\/i\]/i","$1",$message);

		// Color Tag
		while(preg_match("/\[color=([^\]]+)\](.+?)\[\/color\]/i",$message))
			$message = preg_replace("/\[color=([^\]]+)\](.+?)\[\/color\]/i","$2",$message);

		// Size Tag
		while(preg_match("/\[size=([^\]]+)\](.+?)\[\/size\]/i",$message))
			$message = preg_replace("/\[size=([^\]]+)\](.+?)\[\/size\]/i","$2",$message);

		// Font Tag
		while(preg_match("/\[font=([^\]]+)\](.+?)\[\/font\]/i",$message))
			$message = preg_replace("/\[font=([^\]]+)\](.+?)\[\/font\]/i","$2",$message);

		return $message;
	}

	// Security functions for message parsing
	function check_font_size($size,$text,$mode=1){
		global $x7c;
		$size = eregi_replace("[A-z ]","",$size);
		if($size > $x7c->settings['style_max_size'] && $x7c->settings['style_max_size'] != 0)
			$size = $x7c->settings['style_max_size'];
		if($size < $x7c->settings['style_min_size'])
			$size = $x7c->settings['style_min_size'];
		$size .= "pt";

		if($mode == 1)
			return "<font style=\"font-size: $size;\">$text</font>";
		else
			return $size;
	}

	function check_font_family($family,$text,$mode=1){
		global $x7c;
		$allowed_fonts = explode(",",eregi_replace(" ","",$x7c->settings['style_allowed_fonts']));
		if(!in_array($family,$allowed_fonts))
			$family = $x7c->settings['default_font'];

		if($mode == 1)
			return "<font style=\"font-family: $family;\">$text</font>";
		else
			return $family;

	}

	function check_font_color($color,$text,$mode=1){
		global $x7c;
		$color = strtolower($color);

		// These are the named colors that are valid in web documents
		// yes, all are in english, complain to the W3C not me
		$valid_colors = explode(",",strtolower("AliceBlue,AntiqueWhite,Aqua,Aquamarine,Azure,Beige,Bisque,Black,BlanchedAlmond,Blue,BlueViolet,Brown,BurlyWood,CadetBlue,Chartreuse,Chocolate,Coral,
		CornflowerBlue,Cornsilk,Crimson,Cyan,DarkBlue,DarkCyan,DarkGoldenRod,DarkGray,DarkGreen,DarkKhaki,DarkMagenta,DarkOliveGreen,Darkorange,DarkOrchid,DarkRed,DarkSalmon,
		DarkSeaGreen,DarkSlateBlue,DarkSlateGray,DarkTurquoise,DarkViolet,DeepPink,DeepSkyBlue,DimGray,DodgerBlue,Feldspar,FireBrick,FloralWhite,ForestGreen,Fuchsia,Gainsboro,
		GhostWhite,Gold,GoldenRod,Gray,Green,GreenYellow,HoneyDew,HotPink,IndianRed,Indigo,Ivory,Khaki,Lavender,LavenderBlush,LawnGreen,LemonChiffon,LightBlue,
		LightCoral,LightCyan,LightGoldenRodYellow,LightGrey,LightGreen,LightPink,LightSalmon,LightSeaGreen,LightSkyBlue,LightSlateBlue,LightSlateGray,LightSteelBlue,LightYellow,Lime,LimeGreen,
		Linen,Magenta,Maroon,MediumAquaMarine,MediumBlue,MediumOrchid,MediumPurple,MediumSeaGreen,MediumSlateBlue,MediumSpringGreen,MediumTurquoise,MediumVioletRed,MidnightBlue,MintCream,MistyRose,
		Moccasin,NavajoWhite,Navy,OldLace,Olive,OliveDrab,Orange,OrangeRed,Orchid,PaleGoldenRod,PaleGreen,PaleTurquoise,PaleVioletRed,PapayaWhip,PeachPuff,Peru,Pink,
		Plum,PowderBlue,Purple,Red,RosyBrown,RoyalBlue,SaddleBrown,Salmon,SandyBrown,SeaGreen,SeaShell,Sienna,Silver,SkyBlue,SlateBlue,SlateGray,Snow,
		SpringGreen,SteelBlue,Tan,Teal,Thistle,Tomato,Turquoise,Violet,VioletRed,Wheat,White,WhiteSmoke,Yellow,YellowGreen"));

		if(!in_array($color,$valid_colors) && !eregi("#......",$color))
			$color = $x7c->settings['default_color'];

		if($mode == 1)
			return "<font style=\"color: $color\">$text</font>";
		else
			return $color;

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

	// Include the private message handling function
	include("./lib/private_chat.php");

	// The following functions handle offline messages

	// This function sends an offline message
	function send_offline_msg($to,$subject,$msg){
		global $x7s, $db, $prefix, $x7c;
		$time = time();

		$color = $x7c->settings['default_color'];
		$size = $x7c->settings['default_size'];
		$font = $x7c->settings['default_font'];
		$starttags = "[color=$color][size=$size][font=$font]";
		$endtags = "[/color][/size][/font]";

		//$db->DoQuery("INSERT INTO {$prefix}messages VALUES('0','$x7s->username','6','$subject::$starttags$msg$endtags','parsed_body','$to','0')");
		$db->DoQuery("INSERT INTO {$prefix}messages VALUES('0','$x7s->username','6','$subject::$time::$msg','parsed_body','$to','0')");
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
		preg_match("/^(.+?)::/i",$body,$match);
		$return[1] = $match[1];
		$tmp=preg_replace("/^(.+?)::/i","",$body);
		preg_match("/^(.+?)::/i",$tmp,$match);
		$return[2] = date($x7c->settings['date_format_full'], $match[1]);

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

	// The word eval(SDOIREdus96ds7tfds); is randomingly inserted here
?>
