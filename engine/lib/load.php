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

	// This class loads the server, room and permission variables
	class settings {

		// Some vars to help cut down on MySql useage
		var $user_query;
		var $room_query;
		var $permission_query;
		var $online_query;

		// This function is run when the object is created, it loads system settings from the database
		function settings(){
			global $prefix,$db,$auth_ucookie;
			$query = $db->DoQuery("SELECT * FROM {$prefix}settings");
			while($row = $db->Do_Fetch_Row($query))
				$this->settings[$row[1]] = $row[2];

		}

		function usersettings(){
			global $prefix,$db,$x7s,$x7c,$x7p,$g_default_settings;

			// Make sure settings are not empty
			if(empty($x7p->profile['settings']))
				$x7p->profile['settings'] = $g_default_settings;

			$settings = explode(";",$x7p->profile['settings']);

			// Store as a raw array for later editing
			$this->rawsettings = $settings;

			// Test if langauge file is valid and if so include
			if(parse_includes($settings[0]) && file_exists("./lang/$settings[0].php"))
				$this->settings['default_lang'] = $settings[0];

			// Test if this skin is valid and if so inlcude, also if it is set as default then we use whatever the boards default setting is
			if(parse_includes($settings[1]) && is_dir("./themes/$settings[1]") && $settings[1] != "default")
				$this->settings['default_skin'] = $settings[1];

			// Set the cookie time for the user
			$this->settings['cookie_time'] = $settings[2];

			// Back up system styles and offsets
			$this->settings['sys_default_font'] = $this->settings['default_font'];
			$this->settings['sys_default_color'] = $this->settings['default_color'];
			$this->settings['sys_default_size'] = $this->settings['default_size'];
			$this->settings['sys_time_offset_hours'] = $this->settings['time_offset_hours'];
			$this->settings['sys_time_offset_mins'] = $this->settings['time_offset_mins'];

			// Set default font, size and color if user has them set (auto-set)
			if($settings[3] != "default")
				$this->settings['default_font'] = $settings[3];
			if($settings[4] != "default")
				$this->settings['default_size'] = $settings[4];
			if($settings[5] != "default")
				$this->settings['default_color'] = $settings[5];

			if($this->settings['disable_styles'] != 1)
				$this->settings['disable_styles'] = $settings[6];

			if($this->settings['disable_smiles'] != 1)
				$this->settings['disable_smiles'] = $settings[7];

			if($this->settings['disable_sounds'] != 1)
				$this->settings['disable_sounds'] = $settings[8];

			// Note: I know this is spelled wrong.  It isn't a bug
			$this->settings['disble_timestamp'] = $settings[9];

			// Set refresh rate
			$this->settings['refresh_rate'] = $settings[10];
			if($settings[10] < $this->settings['min_refresh'] && $this->settings['min_refresh'] != 0)
				$this->settings['refresh_rate'] = $this->settings['min_refresh'];
			elseif($settings[10] > $this->settings['max_refresh'] && $this->settings['max_refresh'] != 0)
				$this->settings['refresh_rate'] = $this->settings['max_refresh'];

			// Make sure that their refresh rate is not greater then the idle time
			if(($this->settings['online_time']*1000) <= $this->settings['refresh_rate'])
				$this->settings['refresh_rate'] = ($this->settings['online_time']-5)*1000;


			if($settings[11] != "default")
				$this->settings['time_offset_hours'] = $settings[11];

			if($settings[12] != "default")
				$this->settings['time_offset_mins'] = $settings[12];

			// Get Permissions and Room Information into $x7c
			$this->room_info(@$_GET['room']);
			$this->permissions = $this->permissions($x7s->username,$x7p);

			// Check certain things about room like custom bg & custom logo
			if($this->settings['enable_roombgs'] == 1 && $this->room_data['background'] != "")
				$this->settings['background_image'] = $this->room_data['background'];

			if($this->settings['enable_roomlogo'] == 1 && $this->room_data['logo'] != "")
				$this->settings['banner_url'] = $this->room_data['logo'];

			// See if PM logging is on
			$this->settings['log_pms'] = 0;
			if($this->permissions['log_pms'] == 1)
				$this->settings['log_pms'] = $settings[13];

			// See if you want to be invisible ;)
			if($this->permissions['b_invisible'] == 1 && $settings[14] == 1){
				$this->settings['invisible'] = 1;
				$this->settings['auto_inv'] = $settings[14];
			}else{
				$this->settings['invisible'] = 0;
				$this->settings['auto_inv'] = 0;
			}

			// Lets check the room, if they are in one, to see if they are already invisible or not
			if(isset($_GET['room']) && $x7c->permissions['b_invisible'] == 1){
				$query = $db->DoQuery("SELECT * FROM {$prefix}online WHERE name='$x7s->username' AND room='$_GET[room]'");
				$row = $db->Do_Fetch_Row($query);

				// See if you are invisible
				if($row[6] == 1)
					$x7c->settings['invisible'] = 1;
				else
					$x7c->settings['invisible'] = 0;
			}

			// Load Ignored Users
			$this->profile['ignored'] = array();
			$q = $db->DoQuery("SELECT ignored_user FROM {$prefix}muted WHERE user='$x7s->username'");
			while($row = $db->Do_Fetch_Row($q)){
				$this->profile['ignored'][] = $row[0];
			}
		}

		// Used to change the value of one of the user's settings
		function edit_user_settings($setting,$newvalue){
			global $db, $prefix, $x7c, $x7s;

			if($setting == "default_lang")
				$index = 0;
			elseif($setting == "default_skin")
				$index = 1;
			elseif($setting == "cookie_time")
				$index = 2;
			elseif($setting == "default_font")
				$index = 3;
			elseif($setting == "default_size")
				$index = 4;
			elseif($setting == "default_color")
				$index = 5;
			elseif($setting == "disable_styles")
				$index = 6;
			elseif($setting == "disable_smilies")
				$index = 7;
			elseif($setting == "disable_sounds")
				$index = 8;
			elseif($setting == "disble_timestamp")
				$index = 9;
			elseif($setting == "refresh_rate")
				$index = 10;
			elseif($setting == "time_offset_hours")
				$index = 11;
			elseif($setting == "time_offset_mins")
				$index = 12;
			elseif($setting == "log_pms")
				$index = 13;

			if($this->rawsettings[$index] != $newvalue)
				$this->rawsettings[$index] = $newvalue;
				$newsettings = implode(";",$this->rawsettings);
				$db->DoQuery("UPDATE {$prefix}users SET settings='$newsettings' WHERE username='$x7s->username'");
		}

		// Used to change the value of all of the user's settings
		function edit_user_setting_field($new_settings,$hideemail){
			global $db, $prefix, $x7s;
			$db->DoQuery("UPDATE {$prefix}users SET settings='$new_settings' WHERE username='$x7s->username'");
			$db->DoQuery("UPDATE {$prefix}users SET hideemail='$hideemail' WHERE username='$x7s->username'");
		}

		function permissions($user,$profile_object=null){
			global $db, $x7c, $prefix, $querydb;

			// This function gets the permissions for a user
			if($profile_object == null)
				$user_data = new profile_info($user);
			else
				$user_data = $profile_object;

			// See if they are an op in this room
			$ops = explode(";",$x7c->room_data['ops']);
			if(in_array($user_data->profile['id'],$ops))
				$return['room_operator'] = 1;
			else
				$return['room_operator'] = 0;

			// See if user has a voice
			if($this->room_data['moderated'] == 1){
				$voiced = explode(";",$x7c->room_data['voiced']);
				if(in_array($user_data->profile['id'],$voiced))
					$return['room_voice'] = 1;
				else
					$return['room_voice'] = 0;
			}else{
				// Now, see if they are muted
				$voiced = explode(";",$x7c->room_data['voiced']);
				if(in_array(-$user_data->profile['id'],$voiced))
					$return['room_voice'] = 0;
				else
					$return['room_voice'] = 1;
			}

			// By default user is not an administrator :)
			$return['admin_access'] = 0;
			$return['make_rooms']  = 0;
			$return['make_proom']  = 0;
			$return['make_nexp']  = 0;
			$return['make_mod']  = 0;
			$return['viewip']  = 0;
			$return['kick']  = 0;
			$return['ban_kick_imm']  = 0;
			$return['AOP_all']  = 0;
			$return['AV_all']  = 0;
			$return['view_hidden_emails']  = 0;
			$return['use_keywords']  = 0;
			$return['access_room_logs'] = 0;
			$return['log_pms'] = 0;
			$return['set_background'] = 0;
			$return['set_logo']  = 0;
			$return['make_admins']  = 0;
			$return['server_msg']  = 0;
			$return['can_mdeop']  = 0;
			$return['can_mkick']  = 0;
			$return['admin_settings']  = 0;
			$return['admin_themes']  = 0;
			$return['admin_filter']  = 0;
			$return['admin_groups']  = 0;
			$return['admin_users']  = 0;
			$return['admin_ban']  = 0;
			$return['admin_bandwidth']  = 0;
			$return['admin_logs']  = 0;
			$return['admin_events']  = 0;
			$return['admin_mail']  = 0;
			$return['admin_mods']  = 0;
			$return['admin_smilies']  = 0;
			$return['admin_rooms']  = 0;
			$return['access_disabled']  = 0;
			$return['b_invisible']  = 0;
			$return['c_invisible']  = 0;
			$return['admin_keywords']  = 0;
			$return['access_pw_rooms']  = 0;
			$return['admin_panic'] = 0;
			$return['admin_alarms'] = 0;
			$return['admin_objects'] = 0;
			$return['sheet_modify'] = 0;
			$return['write_master'] = 0;
      $return['admin_abilities'] = 0;
      $return['admin_money'] = 0;
      $return['admin_hints'] = 0;
			$temp = 0;

			// Load other permissions
			
			$query = $db->DoQuery("SELECT * FROM {$prefix}permissions");
			$this->groups_with_aop = array();
			$this->groups_with_av = array();
			while($permissions_query = $db->Do_Fetch_Row($query)){
				$querydb['permissions_1'][$permissions_query[1]] = $permissions_query;
				if($permissions_query[9] == 1)
					$this->groups_with_aop[] = $permissions_query[1];
				if($permissions_query[10] == 1)
					$this->groups_with_av[] = $permissions_query[1];
			}

			foreach (@$user_data->profile['usergroup'] as $user_group){
				$row = $querydb['permissions_1'][$user_group];
	
				$return['make_rooms'] += $row[2];
				$return['make_proom'] += $row[3];
				$return['make_nexp'] += $row[4];
				$return['make_mod'] += $row[5];
				$return['viewip'] += $row[6];
				$return['kick'] += $row[7];
				$return['ban_kick_imm'] += $row[8];
				$return['AOP_all'] += $row[9];
				$return['AV_all'] += $row[10];
				$return['view_hidden_emails'] += $row[11];
				$return['use_keywords'] += $row[12];
	
				// If logging is enabled, see if this user is allowed to
				if($x7c->settings['enable_logging'] == 1){
					$return['access_room_logs'] += $row[13];
					$return['log_pms'] += $row[14];
				}else{
					$return['access_room_logs'] = 0;
					$return['log_pms'] = 0;
				}
	
	
				$return['set_background'] += $row[15];
				$return['set_logo'] += $row[16];
				$return['make_admins'] += $row[17];
				$return['server_msg'] += $row[18];
				$return['can_mdeop'] += $row[19];
				$return['can_mkick'] += $row[20];
				$return['admin_settings'] += $row[21];
				$return['admin_themes'] += $row[22];
				$return['admin_filter'] += $row[23];
				$return['admin_groups'] += $row[24];
				$return['admin_users'] += $row[25];
				$return['admin_ban'] += $row[26];
				$return['admin_bandwidth'] += $row[27];
				$return['admin_logs'] += $row[28];
				$return['admin_events'] += $row[29];
				$return['admin_mail'] += $row[30];
				$return['admin_mods'] += $row[31];
				$return['admin_smilies'] += $row[32];
				$return['admin_rooms'] += $row[33];
				$return['access_disabled'] += $row[34];
				$return['b_invisible'] += $row[35];
				$return['c_invisible'] += $row[36];
				$return['admin_keywords'] += $row[37];
				$return['access_pw_rooms'] += $row[38];
				$return['admin_panic']+=$row[39];
				$return['admin_alarms']+=$row[40];
				$return['admin_objects']+=$row[41];
				$return['sheet_modify']+=$row[43];
				$return['write_master']+=$row[44];
        $return['admin_abilities']+=$row[46];
        $return['admin_money']+=$row[47];
        $return['admin_hints']+=$row[48];
				
				$temp += $row[21]+$row[22]+$row[23]+$row[24]+$row[25]+$row[26]+$row[27]+
					$row[28]+$row[29]+$row[30]+$row[31]+$row[32]+$row[33]+$row[37]+
					$row[38]+$row[39]+$row[40]+$row[41]+$row[46]+$row[47]+$row[48];
				


				// Check for special ones like AOP_all and AV_all (Auto-Op all rooms and Auto-Voice all rooms)
				if($row[9] == 1)
					$return['room_operator'] += 1;
				if($row[10] == 1)
					$return['room_voice'] += 1;
			
			}
			
			//See if they should have access to the admin panel
			if($temp != 0)
				$return['admin_access'] += 1;

			// Return the values
			return $return;
		}

		function room_info($room){
			global $db, $prefix, $x7s;
			// It is this functions job to get room information
			if($room != ""){
				$query = $db->DoQuery("SELECT name,type,moderated,topic,greeting,".
						"password,maxusers,ops,voiced,id,time,logged,background,".
						"logo,panic_free,long_name,shadow,hunt ".
						"FROM {$prefix}rooms WHERE name='$room'");
				$row = $db->Do_Fetch_Row($query);
			}else{
				$row[0] = "";
			}

			if($room == "" || $row[0] == ""){
				// User is not in a room.  Set all to null values
				$this->room_name = "";
				$this->room_data['type'] = 1;
				$this->room_data['moderated'] = 0;
				$this->room_data['topic'] = "";
				$this->room_data['greeting'] = "";
				$this->room_data['greeting_raw'] = "";
				$this->room_data['password'] = "";
				$this->room_data['maxusers'] = 0;
				$this->room_data['ops'] = "";
				$this->room_data['voiced'] = "";
				$this->room_data['id'] = 0;
				$this->room_data['time'] = 1;
				$this->room_data['logged'] = 0;
				$this->room_data['background'] = "";
				$this->room_data['logo'] = "";
				$this->room_data['panic_free'] = "";
				$this->room_data['long_name'] = "";
				$this->room_data['shadow'] = 0;
				$this->room_data['hunt'] = 0;
			}else{
				// User is in a room!
				$this->room_name = $row[0];
				$this->room_data['type'] = $row[1];
				$this->room_data['moderated'] = $row[2];
				$this->room_data['topic'] = $row[3];
				$this->room_data['greeting'] = $row[4];
				$this->room_data['greeting_raw'] = $row[4];
				$this->room_data['password'] = $row[5];
				$this->room_data['maxusers'] = $row[6];
				$this->room_data['ops'] = $row[7];
				$this->room_data['voiced'] = $row[8];
				$this->room_data['id'] = $row[9];
				$this->room_data['time'] = $row[10];
				$this->room_data['logged'] = $row[11];
				$this->room_data['background'] = $row[12];
				$this->room_data['logo'] = $row[13];
				$this->room_data['panic_free'] = $row[14];
				$this->room_data['long_name'] = $row[15];
				$this->room_data['shadow'] = $row[16];
				$this->room_data['hunt'] = $row[17];

				// Replace special symbals in the greeting with values
				$this->room_data['greeting'] = eregi_replace("%u",$x7s->username,$this->room_data['greeting']);
				$off_time = time()+(($this->settings['time_offset_hours']*3600)+($this->settings['time_offset_mins']*60));
				$this->room_data['greeting'] = eregi_replace("%t",date($this->settings['date_format'],$off_time),$this->room_data['greeting']);
				$this->room_data['greeting'] = eregi_replace("%d",date($this->settings['date_format_date'],$off_time),$this->room_data['greeting']);

			}
		}

		// Return the user's id
		// Also store the users gropu here
		function get_id_by_username($username){
			global $db, $prefix, $querydb;

			if(!isset($querydb['get_id_by_user_1'])){
				$query = $db->DoQuery("SELECT id,username,user_group FROM {$prefix}users");
				while($row = $db->Do_Fetch_Row($query)){
					$querydb['get_id_by_user_1'][$row[1]] = array($row[0],$row[2]);
				}
			}

			return @$querydb['get_id_by_user_1'][$username];
		}

	}

	// This class gets and holds information on a user's profile
	class profile_info {
		var $profile;		// Holds the profile information

		function profile_info($user){
			global $db, $prefix;
			$query = $db->DoQuery("SELECT email,avatar,name,location,hobbies,bio,
					status,id,hideemail,gender,settings,password,corp_master,frozen,
					base_group
					FROM {$prefix}users WHERE username='$user'");
			$row = $db->Do_Fetch_Row($query);
			$this->profile['email'] = $row[0];
			$this->profile['avatar'] = $row[1];
			$this->profile['name'] = $row[2];
			$this->profile['username'] = $user;
			$this->profile['location'] = $row[3];
			$this->profile['hobbies'] = $row[4];
			$this->profile['bio'] = $row[5];
			$this->profile['status'] = $row[6];
			$this->profile['id'] = $row[7];
			$this->profile['hideemail'] = $row[8];
			$this->profile['ip'] = $_SERVER['REMOTE_ADDR'];
			$this->profile['gender'] = $row[9];
			$this->profile['settings'] = $row[10];
			$this->profile['password'] = $row[11];
			$this->profile['corp_master'] = $row[12];
			$this->profile['frozen'] = $row[13];
			
			$this->profile['usergroup'] = array();
			$this->profile['base_group'] = $row[14];
			$query = $db->DoQuery("SELECT usergroup FROM {$prefix}groups WHERE username='$user'");
			while($row = $db->Do_Fetch_Row($query)){
				$this->profile['usergroup'][] = $row[0];
			}
			
			
		}
	}
?>
