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
//		X7 Chat Version 2.0.4.3
//		Released August 28, 2006
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

// This is by far the largest file in the entire distrobution.  It clocks in at almost 3000 lines
function admincp_master(){
	global $X7CHATVERSION, $x7p, $x7s, $print, $db, $txt, $x7c, $prefix, $X7CHAT_CONFIG, $g_default_settings;

	$head = $txt[37];
	$body = "<h2 style=\"text-align: center;\">Pannello di amministrazione</h2>";

	// Set these so it doesn't complain, all admins have access to these pages
	$x7c->permissions["admin_main"] = 1;
	$x7c->permissions["admin_news"] = 1;
	$x7c->permissions["admin_help"] = 1;

	// Look for the CP page we are on, if not set then make it main
	if(!isset($_GET['cp_page']))
		$_GET['cp_page'] = "main";

	// Check permissions
	$check_page = $_GET['cp_page'];
	if($check_page == "groupmanager")
		$check_page = "groups";
	if($x7c->permissions["admin_{$check_page}"] != 1)
		$_GET['cp_page'] = "ad2";
	if($x7c->permissions['admin_access'] != 1)
		$_GET['cp_page'] = "ad";

	// Figure out which page this is
	if($_GET['cp_page'] == "settings"){

		$head = $txt[139];

		if(isset($_GET['update_settings'])){
			// Update the settings for some section
			$txt[343] = eregi_replace("<a>","<a href=\"./index.php?act=adminpanel&cp_page=settings\">",$txt[343]);

			if($_GET['settings_page'] == "general"){
				// Update the settings page for the general settings
				// Check for unset values (this is a bug in some browers)
				if(!isset($_POST['disable_chat']))
					$_POST['disable_chat'] = 0;
				if(!isset($_POST['allow_reg']))
					$_POST['allow_reg'] = 0;
				if(!isset($_POST['allow_guests']))
					$_POST['allow_guests'] = 0;
				if(!isset($_POST['disable_sounds']))
					$_POST['disable_sounds'] = 0;
				if(!isset($_POST['log_bandwidth']))
					$_POST['log_bandwidth'] = 0;
				if(!isset($_POST['req_activation']))
					$_POST['req_activation'] = 0;

				// Preparse these to cuz we need to convert seconds to miliseconds
				$_POST['min_refresh'] = $_POST['min_refresh']*1000;
				$_POST['max_refresh'] = $_POST['max_refresh']*1000;

				// Check for problems with the submitted data
				if($_POST['min_refresh'] > $_POST['max_refresh'])
					$error = $txt[344];

				if(!isset($error)){
					// Do the actual updates right now, when I say now I mean NOW
					// Yes this section wrecks hell on your MySql server but hopefully you don't need to update your settings to often
					update_setting("disable_chat",$_POST['disable_chat']);
					update_setting("allow_reg",$_POST['allow_reg']);
					update_setting("allow_guests",$_POST['allow_guests']);
					update_setting("disable_sounds",$_POST['disable_sounds']);
					update_setting("site_name",$_POST['site_name']);
					update_setting("admin_email",$_POST['admin_email']);
					update_setting("logout_page",$_POST['logout_page']);
					update_setting("default_lang",$_POST['default_lang']);
					update_setting("default_skin",$_POST['default_skin']);
					update_setting("maxchars_status",$_POST['maxchars_status']);
					update_setting("maxchars_msg",$_POST['maxchars_msg']);
					update_setting("max_offline_msgs",$_POST['max_offline_msgs']);
					update_setting("min_refresh",$_POST['min_refresh']);
					update_setting("max_refresh",$_POST['max_refresh']);
					update_setting("cookie_time",$_POST['cookie_time']);
					update_setting("log_bandwidth",$_POST['log_bandwidth']);
					update_setting("maxchars_username",$_POST['maxchars_username']);
					update_setting("banner_link",$_POST['banner_link']);
					update_setting("single_room_mode",$_POST['single_room_mode']);
					update_setting("req_activation",$_POST['req_activation']);

					// Check activation stuff
					if($_POST['req_activation'] == 0){
						// Update existing accounts so they do not require activation
						$db->doQuery("UPDATE {$prefix}users SET activated='1'");
					}

					$body = $txt[343];
				}else{
					$body = $error."<Br><Br><div align=\"center\"><a href=\"javascript: history.back()\">$txt[77]</a></div>";
				}
			}elseif($_GET['settings_page'] == "logs"){
				// Convert these values from Kilobytes to bytes
				$_POST['max_log_user'] *= 1024;
				$_POST['max_log_room'] *= 1024;

				if(!isset($_POST['enable_logging']))
					$_POST['enable_logging'] = 0;

				// Update the settings
				update_setting("max_log_user",$_POST['max_log_user']);
				update_setting("max_log_room",$_POST['max_log_room']);
				update_setting("logs_path",$_POST['logs_path']);
				update_setting("enable_logging",$_POST['enable_logging']);

				$body = $txt[343];

			}elseif($_GET['settings_page'] == "user_agreement"){

				// Update the user agreement
				$_POST['user_agreement'] = eregi_replace("\n","<Br>",$_POST['user_agreement']);
				$_POST['user_agreement'] = eregi_replace("&lt;","<",$_POST['user_agreement']);
				$_POST['user_agreement'] = eregi_replace("&gt;",">",$_POST['user_agreement']);
				$_POST['user_agreement'] = eregi_replace("&quot;","\"",$_POST['user_agreement']);
				update_setting("user_agreement",$_POST['user_agreement']);
				$body = $txt[343];

			}elseif($_GET['settings_page'] == "timedate"){

				// Update the settings
				update_setting("date_format",$_POST['date_format']);
				update_setting("date_format_full",$_POST['date_format_full']);
				update_setting("date_format_date",$_POST['date_format_date']);
				update_setting("time_offset_hours",$_POST['time_offset_hours']);
				update_setting("time_offset_mins",$_POST['time_offset_mins']);

				$body = $txt[343];

			}elseif($_GET['settings_page'] == "exptime"){

				// Pre-parse, convert these times from hours to seconds
				$_POST['expire_messages'] = round($_POST['expire_messages']*60,0);
				$_POST['expire_rooms'] = round($_POST['expire_rooms']*60,0);
				$_POST['expire_guests'] = round($_POST['expire_guests']*60,0);

				if($_POST['online_time'] <= 0){
					$_POST['online_time'] = 30;
				}

				update_setting("online_time",$_POST['online_time']);
				update_setting("expire_messages",$_POST['expire_messages']);
				update_setting("expire_rooms",$_POST['expire_rooms']);
				update_setting("expire_guests",$_POST['expire_guests']);

				$body = $txt[343];

			}elseif($_GET['settings_page'] == "styles"){

				// uncheck these checkboxs if not checked
				if(!isset($_POST['enable_roombgs']))
					$_POST['enable_roombgs'] = 0;
				if(!isset($_POST['enable_roomlogo']))
					$_POST['enable_roomlogo'] = 0;
				if(!isset($_POST['disable_smiles']))
					$_POST['disable_smiles'] = 0;
				if(!isset($_POST['disable_styles']))
					$_POST['disable_styles'] = 0;
				if(!isset($_POST['disable_autolinking']))
					$_POST['disable_autolinking'] = 0;

				// parse comma spaces
				$_POST['style_allowed_fonts'] = eregi_replace(" ,",",",$_POST['style_allowed_fonts']);
				$_POST['style_allowed_fonts'] = eregi_replace(", ",",",$_POST['style_allowed_fonts']);

				// Update the styles section
				update_setting("banner_url",$_POST['banner_url']);
				update_setting("background_image",$_POST['background_image']);
				update_setting("enable_roombgs",$_POST['enable_roombgs']);
				update_setting("enable_roomlogo",$_POST['enable_roomlogo']);
				update_setting("default_font",$_POST['default_font']);
				update_setting("default_color",$_POST['default_color']);
				update_setting("default_size",$_POST['default_size']);
				update_setting("style_min_size",$_POST['style_min_size']);
				update_setting("style_max_size",$_POST['style_max_size']);
				update_setting("disable_smiles",$_POST['disable_smiles']);
				update_setting("disable_styles",$_POST['disable_styles']);
				update_setting("disable_autolinking",$_POST['disable_autolinking']);
				update_setting("system_message_color",$_POST['system_message_color']);
				update_setting("style_allowed_fonts",$_POST['style_allowed_fonts']);	

				$body = $txt[343];

			}elseif($_GET['settings_page'] == "avatars"){

				// Convert from kilobytes to bytes
				$_POST['avatar_max_size'] *= 1024;

				// Check for unchecked checkboxes
				if(!isset($_POST['enable_avatar_uploads']))
					$_POST['enable_avatar_uploads'] = 0;
				if(!isset($_POST['resize_smaller_avatars']))
					$_POST['resize_smaller_avatars'] = 0;

				update_setting("enable_avatar_uploads",$_POST['enable_avatar_uploads']);
				update_setting("resize_smaller_avatars",$_POST['resize_smaller_avatars']);
				update_setting("avatar_max_size",$_POST['avatar_max_size']);
				update_setting("avatar_size_px",$_POST['avatar_size_px']);
				update_setting("uploads_path",$_POST['uploads_path']);
				update_setting("uploads_url",$_POST['uploads_url']);

				$body = $txt[343];

			}elseif($_GET['settings_page'] == "loginpage"){

				// Check Check boxes
				if(!isset($_POST['enable_passreminder']))
					$_POST['enable_passreminder'] = 0;

				// Adjust this wierd little setting again

				// Update settings
				update_setting("news",$_POST['news']);
				update_setting("floating_text",$_POST['floating_text']);

				$body = $txt[343];

			}elseif($_GET['settings_page'] == "advanced"){

				if(!isset($_POST['disable_gd']))
					$_POST['disable_gd'] = 0;

				update_setting("disable_gd",$_POST['disable_gd']);

				$body = $txt[343];

			}elseif($_GET['settings_page'] == "support"){

				// Clean up the values a little
				$_POST['support_personel'] = eregi_replace("; ",";",$_POST['support_personel']);
				$_POST['support_personel'] = eregi_replace(" ;",";",$_POST['support_personel']);

				update_setting("support_personel",$_POST['support_personel']);
				update_setting("support_image_online",$_POST['support_image_online']);
				update_setting("support_image_offline",$_POST['support_image_offline']);
				update_setting("support_message",$_POST['support_message']);

				$body = $txt[343];

			}

		}elseif(isset($_GET['settings_page'])){
			// Display the settings form

			// Get default values for settings
			// The reason we have to do this here is because values for this admin and the system default may be different
			$query = $db->DoQuery("SELECT * FROM {$prefix}settings");
			while($row = $db->Do_Fetch_Row($query)){
				$def_settings[$row[1]] = $row[2];
			}

			if($_GET['settings_page'] == "general"){

				// Get the default values for check boxes
				$checkboxs[] = "disable_chat";
				$checkboxs[] = "allow_reg";
				$checkboxs[] = "allow_guests";
				$checkboxs[] = "disable_sounds";
				$checkboxs[] = "log_bandwidth";
				foreach($checkboxs as $key=>$val){
					if($def_settings[$val] == 1)
						$def[$val] = " CHECKED=\"true\"";
					else
						$def[$val] = "";
				}

				// Get defaults for lang and skin
				$lng_dir = dir("./lang");
				$skin_dir = dir("./themes");

				$def['default_lang'] = "";
				$def['default_skin'] = "";

				while($option = $lng_dir->read()){
					if($option != "." && $option != ".." && $option != "index.html"){
						$option = eregi_replace("\.php","",$option);
						if($option == $def_settings['default_lang'])
							$slcted = " SELECTED=\"true\"";
						else
							$slcted = "";
						$def['default_lang'] .= "<option value=\"$option\"$slcted>$option</option>";
					}
				}

				while($option = $skin_dir->read()){
					if($option != "." && $option != ".." && @is_file("./themes/$option/theme.info")){
						if($option == $def_settings['default_skin'])
							$slcted = " SELECTED=\"true\"";
						else
							$slcted = "";
						include("./themes/$option/theme.info");
						$def['default_skin'] .= "<option value=\"$option\"$slcted>$name</option>";
					}
				}

				if($def_settings['single_room_mode'] == "")
					$def['single_room_mode'] = "<option value=\"\" SELECTED>$txt[591]</option>";
				else
					$def['single_room_mode'] = "<option value=\"\">$txt[591]</option>";

				$query = $db->DoQuery("SELECT * FROM {$prefix}rooms");
				while($row = $db->Do_Fetch_Row($query)){
					if($def_settings['single_room_mode'] == $row[1])
						$def['single_room_mode'] .= "<option value=\"$row[1]\" SELECTED>$row[1]</option>";
					else
						$def['single_room_mode'] .= "<option value=\"$row[1]\">$row[1]</option>";
				}

				// Default values for these two fields since we need to convert milisconds to seconds
				$def['min_refresh'] = $def_settings['min_refresh']/1000;
				$def['max_refresh'] = $def_settings['max_refresh']/1000;

				if($def_settings['req_activation'] == 1)
					$def['req_activation'] = " checked=\"true\"";
				else
					$def['req_activation'] = "";

				$body = "<Br>
					<form action=\"./index.php?act=adminpanel&cp_page=settings&settings_page=general&update_settings=1\" method=\"POST\">
					<table align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\">
					<tr>
					<td width=\"100\">$txt[329]: </td>
					<td width=\"100\"><input type=\"checkbox\" name=\"disable_chat\"{$def['disable_chat']} value=\"1\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[330]: </td>
					<td width=\"100\"><input type=\"checkbox\" name=\"allow_reg\"{$def['allow_reg']} value=\"1\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[331]: </td>
					<td width=\"100\"><input type=\"checkbox\" name=\"allow_guests\"{$def['allow_guests']} value=\"1\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[468]: </td>
					<td width=\"100\"><input type=\"checkbox\" name=\"log_bandwidth\"{$def['log_bandwidth']} value=\"1\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[207]: </td>
					<td width=\"100\"><input type=\"checkbox\" name=\"disable_sounds\"{$def['disable_sounds']} value=\"1\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[332]: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"site_name\" value=\"{$def_settings['site_name']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[333]: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"admin_email\" value=\"{$def_settings['admin_email']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[334]: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"logout_page\" value=\"{$def_settings['logout_page']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[335]: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"maxchars_status\" value=\"{$def_settings['maxchars_status']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[551]: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"banner_link\" value=\"{$def_settings['banner_link']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[515]*: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"maxchars_username\" value=\"{$def_settings['maxchars_username']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[336]*: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"maxchars_msg\" value=\"{$def_settings['maxchars_msg']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[337]*: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"max_offline_msgs\" value=\"{$def_settings['max_offline_msgs']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[338]* ($txt[351]): </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"min_refresh\" value=\"{$def['min_refresh']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[339]* ($txt[351]): </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"max_refresh\" value=\"{$def['max_refresh']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[341]: </td>
					<td width=\"100\">
					<select name=\"default_lang\" class=\"text_input\">
					{$def['default_lang']}
					</select>
					</td>
					</tr>
					<tr>
					<td width=\"100\">$txt[342]: </td>
					<td width=\"100\">
					<select name=\"default_skin\" class=\"text_input\">
					{$def['default_skin']}
					</select>
					</td>
					</tr>
					<tr>
					<td width=\"100\">$txt[357] ($txt[351]): </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"cookie_time\" value=\"{$def_settings['cookie_time']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[590]<b>**</b>: </td>
					<td width=\"100\"><select class=\"text_input\" name=\"single_room_mode\">{$def['single_room_mode']}</select></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[616]: </td>
					<td width=\"100\"><input type=\"checkbox\" class=\"text_input\" value=\"1\" name=\"req_activation\"{$def['req_activation']}></td>
					</tr>
					<tr>
					<td width=\"200\" colspan=\"2\"><div align=\"center\"><input type=\"submit\" class=\"button\" value=\"$txt[187]\"></div></td>
					</tr>
					<tr>
					<td width=\"200\" colspan=\"2\"><b>* $txt[340]</b><Br><Br><b>** $txt[593]</b></td>
					</tr>
					</table>
					</form>";

			}elseif($_GET['settings_page'] == "user_agreement"){
				// The user agreement page
				$agreement = eregi_replace("<br>","\n",$x7c->settings['user_agreement']);
				$body = "<Br><div align=\"center\">$txt[518]<Br><Br>
					<form action=\"./index.php?act=adminpanel&cp_page=settings&settings_page=user_agreement&update_settings=1\" method=\"POST\">
					<textarea cols=\"35\" rows=\"15\" name=\"user_agreement\" class=\"text_input\">{$agreement}</textarea>
					<br>
					<input type=\"submit\" value=\"$txt[187]\" class=\"button\">
					</form></div>";

			}elseif($_GET['settings_page'] == "logs"){

				// Get defaults
				if($def_settings['enable_logging'] == 1)
					$def['enable_logging'] = "checked=\"true\"";
				else
					$def['enable_logging'] = "";

				// Convert these from bytes to kilobytes
				$def['max_log_user'] = $def_settings['max_log_user']/1024;
				$def['max_log_room'] = $def_settings['max_log_room']/1024;

				$body = "<Br>
					<form action=\"./index.php?act=adminpanel&cp_page=settings&settings_page=logs&update_settings=1\" method=\"POST\">
					<table align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\">
					<tr>
					<td width=\"100\">$txt[244]: </td>
					<td width=\"100\"><input type=\"checkbox\" name=\"enable_logging\"{$def['enable_logging']} value=\"1\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[345]**: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"logs_path\" value=\"{$def_settings['logs_path']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[346]*: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"max_log_room\" value=\"{$def['max_log_room']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[347]*: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"max_log_user\" value=\"{$def['max_log_user']}\"></td>
					</tr>
					<tr>
					<td width=\"200\" colspan=\"2\"><div align=\"center\"><input type=\"submit\" class=\"button\" value=\"$txt[187]\"></div></td>
					</tr>
					<tr>
					<td width=\"200\" colspan=\"2\"><b>* $txt[340]</b><Br><b>** $txt[522]</b></td>
					</tr>
					</table>
					</form>";

			}elseif($_GET['settings_page'] == "timedate"){

				$thelp = $print->help_button("time_date");
				$body = "<Br>
					<form action=\"./index.php?act=adminpanel&cp_page=settings&settings_page=timedate&update_settings=1\" method=\"POST\">
					<table align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\">
					<tr>
					<td width=\"100\">$txt[348]: $thelp</td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"date_format\" value=\"{$def_settings['date_format']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[349]: $thelp</td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"date_format_date\" value=\"{$def_settings['date_format_date']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[350]: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"date_format_full\" value=\"{$def_settings['date_format_full']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[201]: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"time_offset_hours\" value=\"{$def_settings['time_offset_hours']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[202]: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"time_offset_mins\" value=\"{$def_settings['time_offset_mins']}\"></td>
					</tr>
					<tr>
					<td width=\"200\" colspan=\"2\"><div align=\"center\"><input type=\"submit\" class=\"button\" value=\"$txt[187]\"></div></td>
					</tr>
					</table>
					</form>";

			}elseif($_GET['settings_page'] == "exptime"){

				// Convert default values from miliseconds to second
				$def['expire_messages'] = $def_settings['expire_messages']/60;
				$def['expire_rooms'] = $def_settings['expire_rooms']/60;
				$def['expire_guests'] = $def_settings['expire_guests']/60;

				$body = "<Br>
					<form action=\"./index.php?act=adminpanel&cp_page=settings&settings_page=exptime&update_settings=1\" method=\"POST\">
					<table align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\">
					<tr>
					<td width=\"100\">$txt[352] ($txt[351]): </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"online_time\" value=\"{$def_settings['online_time']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[353]: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"expire_messages\" value=\"{$def['expire_messages']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[354]* ($txt[356]): </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"expire_rooms\" value=\"{$def['expire_rooms']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[355]* ($txt[356]): </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"expire_guests\" value=\"{$def['expire_guests']}\"></td>
					</tr>
					<tr>
					<td width=\"200\" colspan=\"2\"><div align=\"center\"><input type=\"submit\" class=\"button\" value=\"$txt[187]\"></div></td>
					</tr>
					<tr>
					<td width=\"200\" colspan=\"2\"><b>* $txt[340]</b></td>
					</tr>
					</table>
					</form>";

			}elseif($_GET['settings_page'] == "styles"){

				// Calculate default check box values
				$checkboxs[] = "enable_roombgs";
				$checkboxs[] = "enable_roomlogo";
				$checkboxs[] = "disable_smiles";
				$checkboxs[] = "disable_styles";
				$checkboxs[] = "disable_autolinking";
				foreach($checkboxs as $key=>$val){
					if($def_settings[$val] == 1)
						$def[$val] = " CHECKED=\"true\"";
					else
						$def[$val] = "";
				}

				$body = "<Br>
					<form action=\"./index.php?act=adminpanel&cp_page=settings&settings_page=styles&update_settings=1\" name=\"settings_form\" method=\"POST\">
					<table align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\">
					<tr>
					<td width=\"100\">$txt[324]: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"banner_url\" value=\"{$def_settings['banner_url']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[358]: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"background_image\" value=\"{$def_settings['background_image']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[359]: </td>
					<td width=\"100\"><input type=\"checkbox\" name=\"enable_roombgs\"{$def['enable_roombgs']} value=\"1\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[360]: </td>
					<td width=\"100\"><input type=\"checkbox\" name=\"enable_roomlogo\"{$def['enable_roomlogo']} value=\"1\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[361]: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"default_font\" style=\"font-family: {$def_settings['default_font']};\" value=\"{$def_settings['default_font']}\" onChange=\"this.style.fontFamily=this.value\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[362]: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"default_size\" value=\"{$def_settings['default_size']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[363]: &nbsp;&nbsp;<img src=\"./colors.png\" width=\"15\" height=\"15\" onClick=\"javascript: window.open('./index.php?act=sm_window&page=colors&toform=settings_form&tofield=default_color','','location=no,menubar=no,resizable=no,status=no,toolbar=no,scrollbars=yes,width={$x7c->settings['tweak_window_small_width']},height={$x7c->settings['tweak_window_small_height']}');\"></td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"default_color\" value=\"{$def_settings['default_color']}\" style=\"color: {$def_settings['default_color']};\" onChange=\"this.style.color=this.value\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[364]: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"style_min_size\" value=\"{$def_settings['style_min_size']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[365]: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"style_max_size\" value=\"{$def_settings['style_max_size']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[366]: </td>
					<td width=\"100\"><input type=\"checkbox\" name=\"disable_smiles\"{$def['disable_smiles']} value=\"1\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[367]: </td>
					<td width=\"100\"><input type=\"checkbox\" name=\"disable_styles\"{$def['disable_styles']} value=\"1\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[368]: </td>
					<td width=\"100\"><input type=\"checkbox\" name=\"disable_autolinking\"{$def['disable_autolinking']} value=\"1\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[369]: &nbsp;&nbsp;<img src=\"./colors.png\" width=\"15\" height=\"15\" onClick=\"javascript: window.open('./index.php?act=sm_window&page=colors&toform=settings_form&tofield=system_message_color','','location=no,menubar=no,resizable=no,status=no,toolbar=no,scrollbars=yes,width={$x7c->settings['tweak_window_small_width']},height={$x7c->settings['tweak_window_small_height']}');\"></td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"system_message_color\" value=\"{$def_settings['system_message_color']}\" style=\"color: {$def_settings['system_message_color']};\" onChange=\"this.style.color=this.value\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[370]: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"style_allowed_fonts\" value=\"{$def_settings['style_allowed_fonts']}\"></td>
					</tr>
					<tr>
					<td width=\"200\" colspan=\"2\"><div align=\"center\"><input type=\"submit\" class=\"button\" value=\"$txt[187]\"></div></td>
					</tr>
					<tr>
					<td width=\"200\" colspan=\"2\"><b>* $txt[371]</b></td>
					</tr>
					</table>
					</form>";

			}elseif($_GET['settings_page'] == "avatars"){

				// Get Default checkbox values
				if($def_settings['enable_avatar_uploads'] == 1)
					$def['enable_avatar_uploads'] = " checked=\"true\"";
				else
					$def['enable_avatar_uploads'] = "";
				if($def_settings['resize_smaller_avatars'] == 1)
					$def['resize_smaller_avatars'] = " checked=\"true\"";
				else
					$def['resize_smaller_avatars'] = "";

				// Convert from bytes to kilobytes
				$def['avatar_max_size'] = $def_settings['avatar_max_size']/1024;

				$body = "<Br>
					<form action=\"./index.php?act=adminpanel&cp_page=settings&settings_page=avatars&update_settings=1\" method=\"POST\">
					<table align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\">
					<tr>
					<td width=\"100\">$txt[372]: </td>
					<td width=\"100\"><input type=\"checkbox\" name=\"enable_avatar_uploads\"{$def['enable_avatar_uploads']} value=\"1\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[373]: </td>
					<td width=\"100\"><input type=\"checkbox\" name=\"resize_smaller_avatars\"{$def['resize_smaller_avatars']} value=\"1\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[374]: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"avatar_max_size\" value=\"{$def['avatar_max_size']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[375]: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"avatar_size_px\" value=\"{$def_settings['avatar_size_px']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[376]: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"uploads_path\" value=\"{$def_settings['uploads_path']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[377]: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"uploads_url\" value=\"{$def_settings['uploads_url']}\"></td>
					</tr>
					<tr>
					<td width=\"200\" colspan=\"2\"><div align=\"center\"><input type=\"submit\" class=\"button\" value=\"$txt[187]\"></div></td>
					</tr>
					</table>
					</form>";

			}elseif($_GET['settings_page'] == "loginpage"){

				// Calculate default check box values
				$body = "<Br>
					<form action=\"./index.php?act=adminpanel&cp_page=settings&settings_page=loginpage&update_settings=1\" method=\"POST\">
					<table align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\">
					<tr>
					<td width=\"100\">$txt[262]: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"news\" value=\"{$def_settings['news']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">Testo in mappa: </td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"floating_text\" value=\"{$def_settings['floating_text']}\"></td>
					</tr>
					<tr>
					<td width=\"100\">$txt[380]: </td>
					<td width=\"100\"><input type=\"checkbox\" name=\"enable_passreminder\"{$def['enable_passreminder']} value=\"1\"></td>
					</tr>
					<tr>
					<td width=\"200\" colspan=\"2\"><div align=\"center\"><input type=\"submit\" class=\"button\" value=\"$txt[187]\"></div></td>
					</tr>
					</table>
					</form>";

			}elseif($_GET['settings_page'] == "advanced"){

				// Default values
				if($def_settings['disable_gd'] == 1)
					$def['disable_gd'] = " checked=\"true\"";
				else
					$def['disable_gd'] = "";

				$body = "<Br>$txt[385]<Br><Br>
					<form action=\"./index.php?act=adminpanel&cp_page=settings&settings_page=advanced&update_settings=1\" method=\"POST\">
					<table align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\">
					<tr>
					<td width=\"100\">$txt[384]: </td>
					<td width=\"100\"><input type=\"checkbox\" name=\"disable_gd\"{$def['disable_gd']} value=\"1\"></td>
					</tr>
					<tr>
					<td width=\"200\" colspan=\"2\"><div align=\"center\"><input type=\"submit\" class=\"button\" value=\"$txt[187]\"></div></td>
					</tr>
					</table>
					</form>";

			}


		}else{
			// Display the many catagories of settings
			$body = "
				<div align=\"center\">$txt[321]
				<br><Br>
				<a href=\"./index.php?act=adminpanel&cp_page=settings&settings_page=general\">[$txt[218]]</a><br><br>
				<a href=\"./index.php?act=adminpanel&cp_page=settings&settings_page=logs\">[$txt[240]]</a><br><br>
				<a href=\"./index.php?act=adminpanel&cp_page=settings&settings_page=timedate\">[$txt[322]]</a><br><br>
				<a href=\"./index.php?act=adminpanel&cp_page=settings&settings_page=exptime\">[$txt[323]]</a><br><br>
				<a href=\"./index.php?act=adminpanel&cp_page=settings&settings_page=styles\">[$txt[325]]</a><br><br>
				<a href=\"./index.php?act=adminpanel&cp_page=settings&settings_page=avatars\">[$txt[326]]</a><br><br>
				<a href=\"./index.php?act=adminpanel&cp_page=settings&settings_page=loginpage\">[$txt[327]]</a><br><br>
				<a href=\"./index.php?act=adminpanel&cp_page=settings&settings_page=user_agreement\">[$txt[517]]</a><br><br>
				<a href=\"./index.php?act=adminpanel&cp_page=settings&settings_page=support\">[$txt[599]]</a><br><br>
				<a href=\"./index.php?act=adminpanel&cp_page=settings&settings_page=advanced\">[$txt[328]]</a><br><br>
				</div>";
		}

	}elseif($_GET['cp_page'] == "groupmanager"){
		// This is the user group control page

		$head = $txt[309];

		$body = "";

		if(isset($_POST['create'])){
			// Create a group
			$db->DoQuery("INSERT INTO {$prefix}permissions (id,usergroup) VALUES('0','{$_POST['create']}')");
			// Edit the settings for this group
			$_GET['edit'] = $_POST['create'];
		}

		if(isset($_GET['edit'])){
			// Edit a groups permissions
			// Get defaults
			$query = $db->DoQuery("SELECT * FROM {$prefix}permissions WHERE usergroup='$_GET[edit]'");
			$row = $db->Do_Fetch_Row($query);
			($row[2] == 1) ? $def['make_rooms'] = " checked=\"true\"" : $def['make_rooms'] = "";
			($row[3] == 1) ? $def['make_proom'] = " checked=\"true\"" : $def['make_proom'] = "";
			($row[4] == 1) ? $def['make_nexp'] = " checked=\"true\"" : $def['make_nexp'] = "";
			($row[5] == 1) ? $def['make_mod'] = " checked=\"true\"" : $def['make_mod'] = "";
			($row[6] == 1) ? $def['viewip'] = " checked=\"true\"" : $def['viewip'] = "";
			($row[7] == 1) ? $def['kick'] = " checked=\"true\"" : $def['kick'] = "";
			($row[8] == 1) ? $def['ban_kick_imm'] = " checked=\"true\"" : $def['ban_kick_imm'] = "";
			($row[9] == 1) ? $def['AOP_all'] = " checked=\"true\"" : $def['AOP_all'] = "";
			($row[10] == 1) ? $def['AV_all'] = " checked=\"true\"" : $def['AV_all'] = "";
			($row[11] == 1) ? $def['view_hidden_emails'] = " checked=\"true\"" : $def['view_hidden_emails'] = "";
			($row[12] == 1) ? $def['use_keywords'] = " checked=\"true\"" : $def['use_keywords'] = "";
			($row[13] == 1) ? $def['access_room_logs'] = " checked=\"true\"" : $def['access_room_logs'] = "";
			($row[14] == 1) ? $def['log_pms'] = " checked=\"true\"" : $def['log_pms'] = "";
			($row[15] == 1) ? $def['set_background'] = " checked=\"true\"" : $def['set_background'] = "";
			($row[16] == 1) ? $def['set_logo'] = " checked=\"true\"" : $def['set_logo'] = "";
			($row[17] == 1) ? $def['make_admins'] = " checked=\"true\"" : $def['make_admins'] = "";
			($row[18] == 1) ? $def['server_msg'] = " checked=\"true\"" : $def['server_msg'] = "";
			($row[19] == 1) ? $def['can_mdeop'] = " checked=\"true\"" : $def['can_mdeop'] = "";
			($row[20] == 1) ? $def['can_mkick'] = " checked=\"true\"" : $def['can_mkick'] = "";
			($row[21] == 1) ? $def['admin_settings'] = " checked=\"true\"" : $def['admin_settings'] = "";
			($row[22] == 1) ? $def['admin_themes'] = " checked=\"true\"" : $def['admin_themes'] = "";
			($row[23] == 1) ? $def['admin_filter'] = " checked=\"true\"" : $def['admin_filter'] = "";
			($row[24] == 1) ? $def['admin_groups'] = " checked=\"true\"" : $def['admin_groups'] = "";
			($row[25] == 1) ? $def['admin_users'] = " checked=\"true\"" : $def['admin_users'] = "";
			($row[26] == 1) ? $def['admin_ban'] = " checked=\"true\"" : $def['admin_ban'] = "";
			($row[27] == 1) ? $def['admin_bandwidth'] = " checked=\"true\"" : $def['admin_bandwidth'] = "";
			($row[28] == 1) ? $def['admin_logs'] = " checked=\"true\"" : $def['admin_logs'] = "";
			($row[29] == 1) ? $def['admin_events'] = " checked=\"true\"" : $def['admin_events'] = "";
			($row[30] == 1) ? $def['admin_mail'] = " checked=\"true\"" : $def['admin_mail'] = "";
			($row[31] == 1) ? $def['admin_mods'] = " checked=\"true\"" : $def['admin_mods'] = "";
			($row[32] == 1) ? $def['admin_smilies'] = " checked=\"true\"" : $def['admin_smilies'] = "";
			($row[33] == 1) ? $def['admin_rooms'] = " checked=\"true\"" : $def['admin_rooms'] = "";
			($row[34] == 1) ? $def['access_disabled'] = " checked=\"true\"" : $def['access_disabled'] = "";
			($row[35] == 1) ? $def['b_invisible'] = " checked=\"true\"" : $def['b_invisible'] = "";
			($row[36] == 1) ? $def['c_invisible'] = " checked=\"true\"" : $def['c_invisible'] = "";
			($row[37] == 1) ? $def['admin_keywords'] = " checked=\"true\"" : $def['admin_keywords'] = "";
			($row[38] == 1) ? $def['access_pw_rooms'] = " checked=\"true\"" : $def['access_pw_rooms'] = "";
			($row[39] == 1) ? $def['admin_panic'] = " checked=\"true\"" : $def['admin_panic'] = "";
			($row[40] == 1) ? $def['admin_alarms'] = " checked=\"true\"" : $def['admin_alarms'] = "";
			($row[41] == 1) ? $def['admin_objects'] = " checked=\"true\"" : $def['admin_objects'] = "";
			($row[43] == 1) ? $def['sheet_modify'] = " checked=\"true\"" : $def['sheet_modify'] = "";
			($row[44] == 1) ? $def['write_master'] = " checked=\"true\"" : $def['write_master'] = "";
			($row[45] == 1) ? $def['gremios'] = " checked=\"true\"" : $def['gremios'] = "";
			($row[46] == 1) ? $def['admin_abilities'] = " checked=\"true\"" : $def['admin_abilities'] = "";
			($row[47] == 1) ? $def['admin_money'] = " checked=\"true\"" : $def['admin_money'] = "";
			($row[48] == 1) ? $def['admin_hints'] = " checked=\"true\"" : $def['admin_hints'] = "";

			$body = "$txt[424]<Br><Br><table border=\"0\" cellspacing=\"0\" cellpadding=\"4\" align=\"center\">
				<form action=\"index.php?act=adminpanel&cp_page=groupmanager&update=$_GET[edit]\" method=\"post\">
				<tr>
				<td width=\"120\">$txt[422]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"make_rooms\" value=\"1\"{$def['make_rooms']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[423]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"make_proom\" value=\"1\"{$def['make_proom']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[425]*</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"make_nexp\" value=\"1\"{$def['make_nexp']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[426]*</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"make_mod\" value=\"1\"{$def['make_mod']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[427]*</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"viewip\" value=\"1\"{$def['viewip']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[428]*</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"kick\" value=\"1\"{$def['kick']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[429]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"ban_kick_imm\" value=\"1\"{$def['ban_kick_imm']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[430]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"AOP_all\" value=\"1\"{$def['AOP_all']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[431]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"AV_all\" value=\"1\"{$def['AV_all']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[432]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"view_hidden_emails\" value=\"1\"{$def['view_hidden_emails']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[433]*</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"use_keywords\" value=\"1\"{$def['use_keywords']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[434]*</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"access_room_logs\" value=\"1\"{$def['access_room_logs']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[435]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"log_pms\" value=\"1\"{$def['log_pms']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[436]**</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"set_background\" value=\"1\"{$def['set_background']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[437]**</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"set_logo\" value=\"1\"{$def['set_logo']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[438]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"make_admins\" value=\"1\"{$def['make_admins']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[439]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"server_msg\" value=\"1\"{$def['server_msg']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[440]*</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"can_mdeop\" value=\"1\"{$def['can_mdeop']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[441]*</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"can_mkick\" value=\"1\"{$def['can_mkick']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[442]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"admin_settings\" value=\"1\"{$def['admin_settings']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[443]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"admin_themes\" value=\"1\"{$def['admin_themes']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[444]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"admin_filter\" value=\"1\"{$def['admin_filter']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[445]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"admin_groups\" value=\"1\"{$def['admin_groups']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[446]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"admin_users\" value=\"1\"{$def['admin_users']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[447]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"admin_ban\" value=\"1\"{$def['admin_ban']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[448]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"admin_bandwidth\" value=\"1\"{$def['admin_bandwidth']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[449]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"admin_logs\" value=\"1\"{$def['admin_logs']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[457]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"admin_events\" value=\"1\"{$def['admin_events']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[450]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"admin_mail\" value=\"1\"{$def['admin_mail']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[451]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"admin_mods\" value=\"1\"{$def['admin_mods']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[452]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"admin_smilies\" value=\"1\"{$def['admin_smilies']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[453]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"admin_rooms\" value=\"1\"{$def['admin_rooms']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[577]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"admin_keywords\" value=\"1\"{$def['admin_keywords']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[454]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"access_disabled\" value=\"1\"{$def['access_disabled']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[505]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"b_invisible\" value=\"1\"{$def['b_invisible']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[506]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"c_invisible\" value=\"1\"{$def['c_invisible']}></td>
				</tr>
				<tr>
				<td width=\"120\">$txt[602]</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"access_pw_rooms\" value=\"1\"{$def['access_pw_rooms']}></td>
				</tr>
				<tr>
				<td width=\"120\">Amministra l'oscurit&agrave;</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"admin_panic\" value=\"1\"{$def['admin_panic']}></td>
				</tr>					
				<tr>
				<td width=\"120\">Amministra gli allarmi</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"admin_alarms\" value=\"1\"{$def['admin_alarms']}></td>
				</tr>	
				<tr>
				<td width=\"120\">Amministra gli oggetti</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"admin_objects\" value=\"1\"{$def['admin_objects']}></td>
				</tr>
				<tr>
				<td width=\"120\">Amministra i soldi</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"admin_money\" value=\"1\"{$def['admin_money']}></td>
				</tr>
				<tr>
				<td width=\"120\">Puo' modificare le schede</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"sheet_modify\" value=\"1\"{$def['sheet_modify']}></td>
				</tr>	
				<tr>
				<td width=\"120\">Puo' scrivere in modo master</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"write_master\" value=\"1\"{$def['write_master']}></td>
				</tr>
				<tr>
				<td width=\"120\">Amministra le abilit&agrave;</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"admin_abilities\" value=\"1\"{$def['admin_abilities']}></td>
				</tr>
				<tr>
				<td width=\"120\">Amministra gli hint del master</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"admin_hints\" value=\"1\"{$def['admin_hints']}></td>
				</tr>
				<tr>
				<td width=\"120\">E' una gremios?</td>
				<td width=\"50\"><input type=\"checkbox\" name=\"gremios\" value=\"1\"{$def['gremios']}></td>
				</tr>
				<tr>
				<td width=\"120\">Logo</td>
				<td width=\"50\"><input type=\"text\" name=\"logo\" value=\"$row[42]\"></td>
				</tr>

				<tr>
				<td width=\"170\" colspan=\"2\"><div align=\"center\"><input type=\"submit\" class=\"button\" value=\"$txt[187]\"></div></td>
				</tr>
				</table><Br><Br>
				<b>*</b>: $txt[455]<br><Br>
				<b>**</b>: $txt[456]<br><Br>";

		}elseif(isset($_GET['view'])){
			// View members in a group
			// Get defaults for changing it
			$query = $db->DoQuery("SELECT usergroup FROM {$prefix}permissions");
			$change_ops = "";
			while($row = $db->Do_Fetch_Row($query)){
				$change_ops .= "<option value=\"$row[0]\">$row[0]</option>";
			}

			$query = $db->DoQuery("SELECT username FROM {$prefix}groups WHERE usergroup='$_GET[view]'");
			// This is the javascript for the check all uncheck all boxes


			$body .= "$txt[418]<Br><br>";
			while($row = $db->Do_Fetch_Row($query)){
				$body .= "&nbsp;&nbsp;<b>$row[0]</b><Br>";
			}

			$body .= "<br><a href=\"index.php?act=adminpanel&cp_page=groupmanager\">$txt[77]</a></div>";

		}else{

			if(isset($_GET['update'])){
				// Update a group
				// Check for checkboxs
				!isset($_POST['make_rooms']) ? $_POST['make_rooms'] = 0 : "";
				!isset($_POST['make_proom']) ? $_POST['make_proom'] = 0 : "";
				!isset($_POST['make_nexp']) ? $_POST['make_nexp'] = 0 : "";
				!isset($_POST['make_mod']) ? $_POST['make_mod'] = 0 : "";
				!isset($_POST['viewip']) ? $_POST['viewip'] = 0 : "";
				!isset($_POST['kick']) ? $_POST['kick'] = 0 : "";
				!isset($_POST['ban_kick_imm']) ? $_POST['ban_kick_imm'] = 0 : "";
				!isset($_POST['AOP_all']) ? $_POST['AOP_all'] = 0 : "";
				!isset($_POST['AV_all']) ? $_POST['AV_all'] = 0 : "";
				!isset($_POST['view_hidden_emails']) ? $_POST['view_hidden_emails'] = 0 : "";
				!isset($_POST['use_keywords']) ? $_POST['use_keywords'] = 0 : "";
				!isset($_POST['access_room_logs']) ? $_POST['access_room_logs'] = 0 : "";
				!isset($_POST['log_pms']) ? $_POST['log_pms'] = 0 : "";
				!isset($_POST['set_background']) ? $_POST['set_background'] = 0 : "";
				!isset($_POST['set_logo']) ? $_POST['set_logo'] = 0 : "";
				!isset($_POST['make_admins']) ? $_POST['make_admins'] = 0 : "";
				!isset($_POST['server_msg']) ? $_POST['server_msg'] = 0 : "";
				!isset($_POST['can_mdeop']) ? $_POST['can_mdeop'] = 0 : "";
				!isset($_POST['can_mkick']) ? $_POST['can_mkick'] = 0 : "";
				!isset($_POST['admin_settings']) ? $_POST['admin_settings'] = 0 : "";
				!isset($_POST['admin_themes']) ? $_POST['admin_themes'] = 0 : "";
				!isset($_POST['admin_filter']) ? $_POST['admin_filter'] = 0 : "";
				!isset($_POST['admin_groups']) ? $_POST['admin_groups'] = 0 : "";
				!isset($_POST['admin_users']) ? $_POST['admin_users'] = 0 : "";
				!isset($_POST['admin_ban']) ? $_POST['admin_ban'] = 0 : "";
				!isset($_POST['admin_bandwidth']) ? $_POST['admin_bandwidth'] = 0 : "";
				!isset($_POST['admin_logs']) ? $_POST['admin_logs'] = 0 : "";
				!isset($_POST['admin_events']) ? $_POST['admin_events'] = 0 : "";
				!isset($_POST['admin_mail']) ? $_POST['admin_mail'] = 0 : "";
				!isset($_POST['admin_mods']) ? $_POST['admin_mods'] = 0 : "";
				!isset($_POST['admin_smilies']) ? $_POST['admin_smilies'] = 0 : "";
				!isset($_POST['admin_rooms']) ? $_POST['admin_rooms'] = 0 : "";
				!isset($_POST['access_disabled']) ? $_POST['access_disabled'] = 0 : "";
				!isset($_POST['b_invisible']) ? $_POST['b_invisible'] = 0 : "";
				!isset($_POST['c_invisible']) ? $_POST['c_invisible'] = 0 : "";
				!isset($_POST['admin_keywords']) ? $_POST['admin_keywords'] = 0 : "";
				!isset($_POST['access_pw_rooms']) ? $_POST['access_pw_rooms'] = 0 : "";
				!isset($_POST['admin_panic']) ? $_POST['admin_panic'] = 0 : "";
				!isset($_POST['admin_alarms']) ? $_POST['admin_alarms'] = 0 : "";
				!isset($_POST['admin_objects']) ? $_POST['admin_objects'] = 0 : "";
				!isset($_POST['admin_money']) ? $_POST['admin_money'] = 0 : "";
				!isset($_POST['sheet_modify']) ? $_POST['sheet_modify'] = 0 : "";
				!isset($_POST['logo']) ? $_POST['logo'] = 0 : "";
				!isset($_POST['write_master']) ? $_POST['write_master'] = 0 : "";
				!isset($_POST['gremios']) ? $_POST['gremios'] = 0 : "";
				!isset($_POST['admin_abilities']) ? $_POST['admin_abilities'] = 0 : "";
				!isset($_POST['admin_hints']) ? $_POST['admin_hints'] = 0 : "";

				// Save the settings
				$db->DoQuery("UPDATE {$prefix}permissions 
						SET make_rooms='$_POST[make_rooms]',
						make_proom='$_POST[make_proom]',
						make_nexp='$_POST[make_nexp]',
						make_mod='$_POST[make_mod]',
						viewip='$_POST[viewip]',
						kick='$_POST[kick]',
						ban_kick_imm='$_POST[ban_kick_imm]',
						AOP_all='$_POST[AOP_all]',
						AV_all='$_POST[AV_all]',
						view_hidden_emails='$_POST[view_hidden_emails]',
						use_keywords='$_POST[use_keywords]',
						access_room_logs='$_POST[access_room_logs]',
						log_pms='$_POST[log_pms]',
						set_background='$_POST[set_background]',
						set_logo='$_POST[set_logo]',
						make_admins='$_POST[make_admins]',
						server_msg='$_POST[server_msg]',can_mdeop='$_POST[can_mdeop]',
						can_mkick='$_POST[can_mkick]',
						admin_settings='$_POST[admin_settings]',
						admin_themes='$_POST[admin_themes]',
						admin_filter='$_POST[admin_filter]',
						admin_groups='$_POST[admin_groups]',
						admin_users='$_POST[admin_users]',
						admin_ban='$_POST[admin_ban]',
						admin_bandwidth='$_POST[admin_bandwidth]',
						admin_logs='$_POST[admin_logs]',
						admin_events='$_POST[admin_events]',
						admin_mail='$_POST[admin_mail]',
						admin_mods='$_POST[admin_mods]',
						admin_smilies='$_POST[admin_smilies]',
						admin_rooms='$_POST[admin_rooms]',
						access_disabled='$_POST[access_disabled]',
						b_invisible='$_POST[b_invisible]',
						c_invisible=$_POST[c_invisible],
						admin_keywords='$_POST[admin_keywords]',
						access_pw_rooms='$_POST[access_pw_rooms]', 
						admin_panic='$_POST[admin_panic]', 
						admin_alarms='$_POST[admin_alarms]', 
						admin_objects='$_POST[admin_objects]', 
						logo='$_POST[logo]', 
						sheet_modify='$_POST[sheet_modify]', 
						write_master='$_POST[write_master]', 
						gremios='$_POST[gremios]', 
						admin_abilities='$_POST[admin_abilities]', 
						admin_hints='$_POST[admin_hints]', 
						admin_money='$_POST[admin_money]' 
							
							WHERE usergroup='$_GET[update]'");
				// Tell user they have been updated
				$body .= "$txt[458]<Br><br>";

			}elseif(isset($_GET['delete'])){
				// Delete a group
				// Make sure the group is empty
				$query = $db->DoQuery("SELECT * FROM {$prefix}groups WHERE usergroup='$_GET[delete]'");
				$row = $db->Do_Fetch_Row($query);
				$query = $db->DoQuery("SELECT * FROM {$prefix}ability WHERE corp='$_GET[delete]'");
				$row2 = $db->Do_Fetch_Row($query);
				if($row[0] != ""){
					$body .= "$txt[420]<Br><Br>";
				}elseif($row2[0] != ""){
					$body .= "Rimuovere tutte le abilita' di gremios prima di cancellare<Br><Br>";
				}
				else{
					$db->DoQuery("DELETE FROM {$prefix}permissions WHERE usergroup='$_GET[delete]'");
					$body .= "$txt[421]<Br><Br>";
				}


			}elseif(isset($_POST['new_g'])){
				// Change user's groups
				$body .= "$txt[415]<Br><Br>";
				foreach($_POST as $key=>$val){
					if(eregi("^ug_",$key) && $val == 1){
						$key = eregi_replace("^ug_","",$key);

						$gif_query = $db->DoQuery("SELECT logo FROM {$prefix}permissions WHERE usergroup='$_POST[new_g]'");
						$row=$db->Do_Fetch_Assoc($gif_query);
						$gif=$row['logo'];

						include_once('./lib/sheet_lib.php');
						join_corp($key, $_POST['new_g']);
					}
				}

			}elseif(isset($_GET['defaults'])){
				// Edit the default groups
				// Update the database
				update_setting("usergroup_admin",$_POST['admin']);
				update_setting("usergroup_guest",$_POST['guest']);
				update_setting("usergroup_default",$_POST['member']);
				$body .= "$txt[412]<Br><Br>";
				// Update member accounts so their user groups are correct
				//$db->DoQuery("UPDATE {$prefix}users SET user_group='_1' WHERE user_group='{$x7c->settings['usergroup_admin']}' WHERE username<>'$x7s->username'");
				//$db->DoQuery("UPDATE {$prefix}users SET user_group='_2' WHERE user_group='{$x7c->settings['usergroup_guest']}' WHERE username<>'$x7s->username'");
				//$db->DoQuery("UPDATE {$prefix}users SET user_group='_3' WHERE user_group='{$x7c->settings['usergroup_default']}' WHERE username<>'$x7s->username'");
				//$db->DoQuery("UPDATE {$prefix}users SET user_group='{$_POST['admin']}' WHERE user_group='_1' WHERE username<>'$x7s->username'");
				//$db->DoQuery("UPDATE {$prefix}users SET user_group='{$_POST['guest']}' WHERE user_group='_2' WHERE username<>'$x7s->username'");
				//$db->DoQuery("UPDATE {$prefix}users SET user_group='{$_POST['member']}' WHERE user_group='_3' WHERE username<>'$x7s->username'");

				// Update these values quickly so that the change is shown
				$x7c->settings['usergroup_admin'] = $_POST['admin'];
				$x7c->settings['usergroup_guest'] = $_POST['guest'];
				$x7c->settings['usergroup_default'] = $_POST['member'];

			}

			// Get default group values
			$query = $db->DoQuery("SELECT usergroup FROM {$prefix}permissions");
			$group_options['admin'] = "";
			$group_options['member'] = "";
			$group_options['guest'] = "";
			while($row = $db->Do_Fetch_Row($query)){
				if($x7c->settings['usergroup_admin'] == $row[0])
					$group_options['admin'] .= "<option value=\"$row[0]\" selected=\"true\">$row[0]</option>";
				else
					$group_options['admin'] .= "<option value=\"$row[0]\">$row[0]</option>";

				if($x7c->settings['usergroup_guest'] == $row[0])
					$group_options['guest'] .= "<option value=\"$row[0]\" selected=\"true\">$row[0]</option>";
				else
					$group_options['guest'] .= "<option value=\"$row[0]\">$row[0]</option>";

				if($x7c->settings['usergroup_default'] == $row[0])
					$group_options['member'] .= "<option value=\"$row[0]\" selected=\"true\">$row[0]</option>";
				else
					$group_options['member'] .= "<option value=\"$row[0]\">$row[0]</option>";

				$groups[] = $row[0];
			}

			// Display groups and settings edit form
			$body .= "<div align=\"center\">
				<b>$txt[408]</b><br>
				<form action=\"index.php?act=adminpanel&cp_page=groupmanager&defaults=1\" method=\"post\">
				<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
				<tr>
				<td width=\"100\">$txt[409]: </td>
				<td width=\"100\"><select name=\"member\" class=\"text_input\">{$group_options['member']}</select></td>
				</tr>
				<tr>
				<td width=\"100\">$txt[410]: </td>
				<td width=\"100\"><select name=\"guest\" class=\"text_input\">{$group_options['guest']}</select></td>
				</tr>
				<tr>
				<td width=\"100\">$txt[411]: </td>
				<td width=\"100\"><select name=\"admin\" class=\"text_input\">{$group_options['admin']}</select></td>
				</tr>
				<tr>
				<td width=\"200\" colspan=\"2\"><div align=\"center\"><input type=\"submit\" class=\"button\" value=\"$txt[187]\"></div></td>
				</tr>
				</table>
				</form><Br><Br>
				<table width=\"95%\" align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"col_header\">
				<tr>
				<td height=\"25\">&nbsp;$txt[123]</td>
				<td width=\"33%\" height=\"25\">$txt[86]</td>
				</tr>
				</table>
				<table width=\"95%\" align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"inside_table\">";

			// Display a table of groups with actions
			foreach($groups as $key=>$group){
				$body .= "<Tr>
					<td>&nbsp;$group</td>
					<td width=\"33%\">
					<a href=\"index.php?act=adminpanel&cp_page=groupmanager&view=$group\">[$txt[413]]</a>
					<a href=\"index.php?act=adminpanel&cp_page=groupmanager&delete=$group\">[$txt[175]]</a>
					<a href=\"index.php?act=adminpanel&cp_page=groupmanager&edit=$group\">[$txt[139]]</a>
					</td>
					</tr>
					<tr><td colspan=\"2\"><hr></tr>
					";
			}

			$body .= "</table><Br><br>
				<form action=\"index.php?act=adminpanel&cp_page=groupmanager\" method=\"post\">
				$txt[414]: <input type=\"text\" class=\"text_input\" name=\"create\">
				<input type=\"submit\" class=\"button\" value=\"$txt[63]\">
				</form></div>";
		}


	}elseif($_GET['cp_page'] == "objects"){
		include_once('./lib/shop_lib.php');
		global $shopper, $money_name;

		$head = "Amministrazione oggetti";
		$navigator='';
		$body='';
		$error='';

		if (isset($_GET['sell'])) {
			if($_POST['sell_copies'] < 0) {
				$error = "Errore: il numero di copie deve essere positivo";
			}
			else if(!$x7c->permissions["admin_panic"]) {
				// Only masters can change the shop
				$error = "Errore: operazione non permessa";
			}
			else {
				get_obj_name_and_uses($_POST['id'], $obj_name, $dummy);
				$cur_avail = get_obj_availability($obj_name);

				$delta_avail = $_POST['sell_copies'] - $cur_avail;

				$value = calculate_obj_value($_POST['id'], $shopper);

				if ($value <= 0 || $obj_name == $money_name) {
					$error = "Errore: l'oggetto non ha valore";
				}
				else {
					if ($delta_avail < 0) {
						$delta_avail = -$delta_avail;
						$db->DoQuery("DELETE FROM {$prefix}objects
								WHERE name = '$obj_name'
								AND owner = '$shopper'
								LIMIT $delta_avail");
					}
					else if($delta_avail > 0) {

						$query = $db->DoQuery("SELECT * 
								FROM {$prefix}objects WHERE id='$_POST[id]'");
						$row = $db->Do_Fetch_Assoc($query);

						if(!$row || $row['id']==''){
							$error = "Oggetto non esistente";
						}
						else {
							for ($i = 0; $i < $delta_avail; $i++) {
								$db->DoQuery("INSERT INTO {$prefix}objects
										(name,description,uses,
										 image_url,owner,equipped,size,category,base_value,
										 visible_uses, expire_span, shop_return,random_img)
										VALUES('$row[name]','$row[description]','$row[uses]',
											'$row[image_url]','$shopper','1','$row[size]',
											'$row[category]',$row[base_value],'$row[visible_uses]',
											'$row[expire_span]','$row[shop_return]',
											'$row[random_img]')");
							}
						}
					}
					$error = "Nuove copie in vendita: {$_POST['sell_copies']}";
				}
			}
			
		}

		if(isset($_GET['assign'])){
			if(!isset($_POST['owner']) || !isset($_POST['id']) ||
					!isset($_POST['qty'])){
				die("Bad form");
			}
			if(!is_numeric($_POST['qty'])) {
				$error = "Quantita' da assegnare non valida";
			}

			get_obj_name_and_uses($_POST['id'], $obj_name, $dummy);
			if ($obj_name == $money_name)
				$error = "Non puoi assegnare soldi da questo pannello";

			include_once('./lib/sheet_lib.php');
			if($error==''){
				for($i=0; $i < $_POST['qty']; $i++) {
					$error .= assign_object($_POST['id'], $_POST['owner'], true);
				}
			}

		}

		if(isset($_GET['modify'])){
			if(	!isset($_POST['name']) || 
					!isset($_POST['id']) ||
					!isset($_POST['description']) ||
					!isset($_POST['uses']) ||
					!isset($_POST['image_url'])||
					!isset($_POST['size'])||
					!isset($_POST['base_value'])||
					!isset($_POST['category'])||
					!isset($_POST['expire_span']) ){

				die("Bad form");
			}

			$_POST['name'] = trim($_POST['name']);
			$visible_uses = false;
			if(isset($_POST['visible_uses'])) {
				$visible_uses = true;
			}

			$shop_return = false;
			if(isset($_POST['shop_return'])) {
				$shop_return = true;
			}

			$category = $_POST['category'];
			if ($_POST['category'] == "_new_" && isset($_POST['new_category']))
				$category = $_POST['new_category'];

			if($_POST['id']!=-1){
				$old_name = '';
				get_obj_name_and_uses($_POST['id'], $old_name, $uses);

				$query_old_size = $db->DoQuery("SELECT size FROM {$prefix}objects
						WHERE id='$_POST[id]'");
				$row_old_size = $db->Do_Fetch_Assoc($query_old_size);

				$db->DoQuery("UPDATE {$prefix}objects 
						SET name='$_POST[name]',
							description='$_POST[description]',
							uses='$_POST[uses]',
							image_url='$_POST[image_url]',
							size='$_POST[size]',
							base_value='$_POST[base_value]',
							category='$category',
							visible_uses='$visible_uses',
							expire_span='$_POST[expire_span]',
							shop_return = '$shop_return',
							random_img = '$_POST[random_img]'
						WHERE id='$_POST[id]'");
				
				// Update not sold copies
				$db->DoQuery("UPDATE {$prefix}objects 
						SET name='$_POST[name]',
							description='$_POST[description]',
							uses='$_POST[uses]',
							image_url='$_POST[image_url]',
							size='$_POST[size]',
							base_value='$_POST[base_value]',
							category='$category',
							visible_uses='$visible_uses',
							expire_span='$_POST[expire_span]',
							shop_return = '$shop_return',
							random_img = '$_POST[random_img]'
						WHERE name='$old_name' AND owner='$shopper'");

				// Sync existing objects 
				// we do not sync uses
				if (isset($_POST['sync']) && $_POST['sync'] == 1) {
					$db->DoQuery("UPDATE {$prefix}objects 
							SET name = '$_POST[name]',
								description='$_POST[description]',
								image_url='$_POST[image_url]',
								size='$_POST[size]',
								base_value='$_POST[base_value]',
								category='$category',
								visible_uses='$visible_uses',
								expire_span='$_POST[expire_span]',
								shop_return = '$shop_return',
								random_img = '$_POST[random_img]'
							WHERE name='$old_name'");
					
					$query_count_obj = $db->DoQuery("SELECT count(*) AS cnt
							FROM {$prefix}objects
							WHERE name='$_POST[name]'");

					$row_count_obj = $db->Do_Fetch_Assoc($query_count_obj);
					$error = "Modifica eseguita e sincronizzati $row_count_obj[cnt]
						oggetti esistenti.";
					
					if ($row_old_size && $row_old_size['size'] != $_POST['size']) {
						if ($row_old_size['size'] >= 0) {
							// Disequip the object if it had a positive value
							
							$query_user_sync = $db->DoQuery("SELECT count(*) AS total
									FROM {$prefix}objects 
									WHERE name='$_POST[name]'
									AND equipped = 1
									AND owner <> ''
									AND owner <> '$shopper'");

							$db->DoQuery("UPDATE {$prefix}objects 
									SET equipped = 0
									WHERE name='$_POST[name]'
									AND equipped = 1
									AND owner <> ''
									AND owner <> '$shopper'");

							$row_user_sync = $db->Do_Fetch_Assoc($query_user_sync);

							$error .= "<br>A $row_user_sync[total] utenti e' stato 
								disequipaggiato	l'oggetto.";	
						} else {
							// Disequip everything if the object had a negative value
							
							$query_user_sync = $db->DoQuery("SELECT owner
									FROM {$prefix}objects 
									WHERE equipped = 1
									AND name='$_POST[name]'
									AND owner <> ''
									AND owner <> '$shopper'");

							$disequipped = 0;
							while ($row_user_sync = $db->Do_Fetch_Assoc($query_user_sync)) {
								if ($row_user_sync['owner'] != "" && 
										$row_user_sync['owner'] != $shopper) {
									$db->DoQuery("UPDATE {$prefix}objects 
											SET equipped = 0
											WHERE owner = '$row_user_sync[owner]'");
									$disequipped++;
								}
							}
							$error .= "<br>A $row_user_sync[total] utenti e' stato 
								disequipaggiato tutto"; 
						}
					}
				}
			}else{
				$query_duplicate = $db->DoQuery("
					SELECT count(*) AS cnt FROM {$prefix}objects
						WHERE name='$_POST[name]' AND owner = ''");
				$row = $db->Do_Fetch_Assoc($query_duplicate);
				if ($row['cnt'] > 0)
					$error = "Oggetto gia' esistente";
				else {
					$db->DoQuery("INSERT INTO {$prefix}objects 
						(name, description, uses, image_url,
						 equipped, size, base_value, category, visible_uses, expire_span, 
						 shop_return,random_img)
						VALUES(
							'$_POST[name]',	'$_POST[description]',
							'$_POST[uses]',	'$_POST[image_url]',
							'1','$_POST[size]', '$_POST[base_value]', '$category', 
							'$visible_uses', '$_POST[expire_span]', '$shop_return',
							'$_POST[random_img]'
							)");
				}
			}
			if (!isset($error) || $error == "")
				$error = "Modifica eseguita con successo";
		}

		if(isset($_GET['delete'])){
			$name = '';
			get_obj_name_and_uses($_GET['delete'], $name, $uses);
			$db->DoQuery("DELETE FROM {$prefix}objects WHERE id='$_GET[delete]'");
			$db->DoQuery("DELETE FROM {$prefix}objects WHERE name='$name'
					AND owner='$shopper'");
			$error = "Oggetto eliminato";
		}

		if(isset($_GET['proom'])){
			if(isset($_POST['owner']) && $_POST['owner']!=''){
				$query = $db->DoQuery("SELECT username 
						FROM {$prefix}users WHERE username='$_POST[owner]'");
				$row = $db->Do_Fetch_Assoc($query);

				if($row==null || $row['username']!=$_POST['owner']){
					$body.= "Errore, utente $_POST[owner] non esistente";
				}
				else{
					$query_rooms = $db->DoQuery("SELECT count(*) AS cnt
							FROM {$prefix}rooms WHERE name='$_POST[owner]'");

					$query_obj_master =  $db->DoQuery("SELECT count(*) AS cnt
							FROM {$prefix}objects WHERE name='masterkey_$_POST[owner]' 
							AND owner=''");

					$query_obj_user =  $db->DoQuery("SELECT count(*) AS cnt
							FROM {$prefix}objects 
							WHERE name='masterkey_$_POST[owner]' AND owner='$_POST[owner]'");
					$row_rooms = $db->Do_Fetch_Assoc($query_rooms);
					$row_obj_master = $db->Do_Fetch_Assoc($query_obj_master);
					$row_obj_user = $db->Do_Fetch_Assoc($query_obj_user);

					if($row_rooms['cnt'] == 0){
						//Room creation
						$db->DoQuery("INSERT INTO {$prefix}rooms
								(name, type, maxusers, logged, logo, long_name)
								VALUES ('$_POST[owner]', '2', '1000', '1',
									'./graphic/private_room.jpg','Stanza di $_POST[owner]')");
						$body .= "Stanza creata con successo<br>";

					}
					else
						$body .= "Stanza gi&agrave; presente<br>";

					if($row_obj_master['cnt'] == 0){
						//Copy of the key for the master
						$db->DoQuery("INSERT INTO {$prefix}objects
								(name, description, uses, image_url, equipped, size, 
								 visible_uses)
								VALUES ('masterkey_$_POST[owner]',
									'Chiave della stanza di $_POST[owner]', '-1',
									'./graphic/private_key.jpg','1','0','1')");
						$body .= "Copia master della chiave creata con successo<br>";
					}
					else
						$body .= "Copia master della chiave gi&agrave; presente<br>";

					if($row_obj_user['cnt'] == 0){
						//Cooy of the key for the owner
						$db->DoQuery("INSERT INTO {$prefix}objects
								(name, description, uses, image_url, owner, equipped, size,
								 visible_uses)
								VALUES ('masterkey_$_POST[owner]',
									'Chiave della stanza di $_POST[owner]', '-1',
									'./graphic/private_key.jpg','$_POST[owner]','1','0', '1')");
						$body .= "Copia utente della chiave creata con successo<br>";
						include_once('./lib/alarms.php');
						object_assignement($_POST['owner'],
								"Chiave della stanza di $_POST[owner]");
					}
					else
						$body .= "Copia utente della chiave master gi&agrave; presente<br>";

				}

				$body.="<br><br><a href=\"index.php?act=adminpanel&cp_page=objects\">
					[Torna agli oggetti]</a>";
			}

			else{
				$body .= "
					<form action=\"index.php?act=adminpanel&cp_page=objects&proom=1\"
					method=\"post\">
					<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
					<tr>
					<td>Nome del proprietario:</td>
					<td><input type=\"text\" name=\"owner\" class=\"text_input\"></td>
					</tr>
					<tr>
					<td><input type=\"submit\" class=\"button\" value=\"Vai\"></td>
					</tr>
					</table>
					</form>
					";


			}
		}

		if(isset($_GET['edit'])){
			$new_object = true;
			if($_GET['edit']!=-1){
				$new_object = false;
				$query = $db->DoQuery("SELECT * FROM {$prefix}objects 
						WHERE id='$_GET[edit]'");
				$row = $db->Do_Fetch_Assoc($query);
				if(!$row)
					die("Error; should not die here");
				if($row['owner'] == $shopper) {
					$query = $db->DoQuery("SELECT * FROM {$prefix}objects 
							WHERE name='$row[name]' AND owner = ''");
					$row = $db->Do_Fetch_Assoc($query);
					if(!$row)
						die("Error; should not die here");
				}
			}else{
				$row['name']='';
				$row['owner']='';
				$row['description']='';
				$row['uses']=-1;
				$row['image_url']='';
				$row['id']=-1;
				$row['size']=0;
				$row['base_value']=-1;
				$row['category']='';
				$row['visible_uses']='';
				$row['expire_span']='-1';
				$row['shop_return']='0';
				$row['random_img']='';

			}
			$minuscolo="";
			$piccolo="";
			$c_piccolo="";
			$medio="";
			$c_medio="";
			$grande="";
			$c_grande="";
			$visible_uses_checked='';
			$shop_return_checked = '';

			if($row['visible_uses']) {
				$visible_uses_checked = "checked";
			}

			if($row['shop_return']) {
				$shop_return_checked = "checked";
			}

			switch ($row['size']) {
				case 0:
					$minuscolo="selected";
					break;
				case 1:
					$piccolo="selected";
					break;
				case 2:
					$medio="selected";
					break;
				case 5:
					$grande="selected";
					break;
				case -1:
					$c_piccolo="selected";
					break;
				case -2:
					$c_medio="selected";
					break;
				case -5:
					$c_grande="selected";
					break;
			}
			$query_cat = $db->DoQuery("SELECT DISTINCT category 
					FROM {$prefix}objects
					ORDER BY category");
			$category_form = '<select class="button" name="category"
				onChange="javascript: category_select(this);">
				<option value="">Seleziona la categoria</option>';

			while ($row_category = $db->Do_Fetch_Assoc($query_cat)) {
				if ($row_category['category']) {
					$selected = "";
					if ($row_category['category'] == $row['category'])
						$selected = "selected";
					$category_form .= '<option value="'.$row_category['category'].'" 
						'.$selected.'>'.$row_category['category'].'</option>';
				}
			}

			$category_form .= '<option value="_new_">-Crea nuova categoria-</option>
				</select>';

			$name_type = "text";
			if ($row['name'] == $money_name)
				$name_type = "hidden";

			$submit_value = "Crea oggetto";
			$sync_button = '';
			if (!$new_object) {
				$submit_value = "Modifica oggetto";
				$sync_button = "<td><input type=\"button\" class=\"button\" 
				value=\"Modifica e sincronizza\" onClick=\"sync_request();\"></td></tr>
				<tr><td>&nbsp;</td><td>
				<br>Con questo tasto le modifiche dell'oggetto vengono 
				<br>riflesse anche alle copie gia' assegnate.
				<br>Gli usi rimanenti non vengono mai riassegnati.
				<br>Tutti gli oggetti modificati vengono disequipaggiati.</td>
				</td>";
			}

			$body.="
				<script language=\"javascript\" type=\"text/javascript\">
				  function sync_request() {
						document.getElementById('sync_field').value = 1;
						document.forms.main_form.submit();
					}
					function category_select(elem) {
						if (elem.options[elem.selectedIndex].value == '_new_'){
							document.getElementById('new_category').style.visibility = 
								'visible';
						}
						else {
							document.getElementById('new_category').style.visibility =
								'hidden';
						}
					}
				</script>
				<form action=\"index.php?act=adminpanel&cp_page=objects&modify=1\"
				method=\"post\" name=\"main_form\">
				<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
				<input type=\"hidden\" name=\"id\" value=\"$row[id]\">
				<tr>
				<td>Nome:</td>
				<td><input type=\"$name_type\" name=\"name\" class=\"text_input\"
				value=\"$row[name]\"></td>
				</tr>
				<tr>
				<td>Descrizione:</td>
				<td><textarea cols=\"30\" rows=\"10\" type=\"text\" name=\"description\"
				class=\"text_input\">$row[description]</textarea></td>
				</tr>
				<tr>
				<td>
			  Cartella per immagine random:
				</td>
				<td>
				<input type=\"text\" name=\"random_img\" class=\"text_input\"
				value=\"$row[random_img]\">
				</td>
				</tr>
				<tr>
				<td>Usi (-1 per usi infiniti):</td>
				<td><input type=\"text\" name=\"uses\" class=\"text_input\"
				value=\"$row[uses]\"></td>
				</tr>
				<tr>
				<td>Gli usi rimasti sono visibili?
				</td>
				<td><input type=\"checkbox\" class=\"text_input\" name=\"visible_uses\" $visible_uses_checked>
				</td>
				</tr>
				<tr>
				<td>Scadenza in minuti (-1: no scadenza)
				</td>
				<td><input type=\"text\" class=\"text_input\" name=\"expire_span\" 
				value=\"$row[expire_span]\">
				</td>
				</tr>
				<tr>
				<td>Torna in vendita dopo la scadenza?
				</td>
				<td><input type=\"checkbox\" class=\"text_input\" name=\"shop_return\" $shop_return_checked>
				</td>
				</tr>
				<tr>
				<td>URL immagine:</td>
				<td><input type=\"text\" name=\"image_url\" class=\"text_input\"
				value=\"$row[image_url]\"
				onChange=\"javascript: document.getElementById('objImg').src=this.value;\"></td>
				</tr>
				<tr>
				<td>Preview:</td>
				<td><img id=\"objImg\" src=\"$row[image_url]\"></td>
				</tr>
				<tr><td><a onClick=\"".popup_open($x7c->settings['tweak_window_large_width'],
						$x7c->settings['tweak_window_large_height'],
						'index.php?act=images','Images',"yes").
						";\">[Carica immagine]</a></td></tr>
				<tr>
				<td>Dimesione:</td>
				<td><select class=\"button\" name=\"size\">
				<option value=\"0\" $minuscolo>Minuscolo</option>
				<option value=\"1\" $piccolo>Piccolo</option>
				<option value=\"2\" $medio>Medio</option>
				<option value=\"5\" $grande>Grande</option>
				<option value=\"-1\" $c_piccolo>Capienza Piccola</option>
				<option value=\"-2\" $c_medio>Capienza Media</option>
				<option value=\"-5\" $c_grande>Capienza Grande</option>
				</select>
				</td>
				</tr>
				<tr>
				<td>
				Valore base di vendita:
				</td>
				<td>
				<input type=\"text\" name=\"base_value\" class=\"text_input\"
				value=\"$row[base_value]\">
				</td>
				</tr>
				<tr>
				<td>Categoria</td>
				<td>$category_form</td>
				</tr>
				<tr id=\"new_category\" style=\"visibility: hidden;\">
				<td>Nuova categoria:</td>
				<td><input type=\"text\" class=\"text_input\" name=\"new_category\">
				</td>
				</tr>
				<tr>
				<input id=\"sync_field\" type=\"hidden\" name=\"sync\" value=\"0\">
				<td><input type=\"submit\" class=\"button\" value=\"$submit_value\"></td>
				$sync_button
				</tr>
				</table>
				";


			$body.="</form>";

			if($_GET['edit']!=-1){
				if ($row['name'] != $money_name) {
					$body.="
						<form action=\"index.php?act=adminpanel&cp_page=objects&assign=1\"
						method=\"post\">
						<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
						<input type=\"hidden\" name=\"id\" value=\"$row[id]\">
						<tr>
						<hr>
						<td>Assegna a:</td>
						<td><input type=\"text\" name=\"owner\" class=\"text_input\"></td>
						<td>Quantita'</td>
						<td><input type=\"text\" size=\"5\" name=\"qty\"
						       class=\"text_input\" value=\"1\"></td>
						<td><input type=\"submit\" class=\"button\" value=\"Assegna\"></div>
						</td>
						</tr>
	
						</table>
					</form>";
	
					$availability = get_obj_availability($row['name']);

					if ($x7c->permissions["admin_panic"])	{
						$body.="<form action=\"index.php?act=adminpanel&cp_page=objects&sell=1\"
							method=\"post\">
							<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
							<input type=\"hidden\" name=\"id\" value=\"$row[id]\">
							<tr>
							<hr>
							<td>Copie in negozio:</td>
							<td><input type=\"text\" name=\"sell_copies\" class=\"text_input\"
							value=\"$availability\"></td>
							<td><input type=\"submit\" class=\"button\"
							value=\"Metti in vendita\"></div></td>
							</tr>
	
							</table>
							</form>";
					}
				}
			}


		}
		else if(!isset($_GET['proom'])){

			$letter='AND name LIKE \'a%\'';
			if(isset($_GET['letter']))
				$letter="AND name LIKE '".$_GET['letter']."%'";									

			if(isset($_POST['letter']))
				$letter="AND name LIKE '%".$_POST['letter']."%'";

			if(isset($_GET['category']))
				$letter="AND category LIKE '{$_GET['category']}'";

			if(!isset($_POST['selling'])) {
				$query = $db->DoQuery("SELECT * FROM {$prefix}objects 
						WHERE owner='' $letter ORDER BY category, name");
			}
			else {	
				$query = $db->DoQuery("SELECT * FROM {$prefix}objects 
						WHERE owner='$shopper' $letter
						AND name <> '$money_name'
						GROUP BY name
						ORDER BY category, name");
			}

			$body = "<b style=\"color: orange;\">$error</b><br><br>";
			$body .= "<div align=\"center\"><input type=\"submit\"
				value=\"Crea nuovo oggetto\" class=\"button\"
				onClick=\"javascript: window.location.href='index.php?act=adminpanel&cp_page=objects&edit=-1'\"> &nbsp;
				<input type=\"submit\" value=\"Crea stanza privata\" class=\"button\"
				onClick=\"javascript: window.location.href='index.php?act=adminpanel&cp_page=objects&proom=1'\"></div>";

			$sell_checked = isset($_POST['selling']) ? "checked" : "";
			$body .= "<div align=\"center\"><br><b>Cerca oggetto</b></div><Br>
				<form action=\"index.php?act=adminpanel&cp_page=objects\"
				method=\"post\" name=\"quicke\">
				<table align=\"center\" border=\"0\" cellspacing=\"0\"
				cellpadding=\"0\">
				<tr>
				<td>Nome oggetto:</td>
				<td><input type=\"text\" name=\"letter\" class=\"text_input\"></td>
				<td><div align=\"center\"><input type=\"submit\" value=\"Cerca\"
				class=\"button\"></div></td>
				<td>
				<input type=\"checkbox\" name=\"selling\" $sell_checked>
				Oggetti in vendita</input>
				</td>
				</tr>
				</table>
				</form>";

			$body .= " <p style=\"text-align: center;\">
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=a\">[a]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=b\">[b]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=c\">[c]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=d\">[d]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=e\">[e]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=f\">[f]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=g\">[g]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=h\">[h]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=i\">[i]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=j\">[j]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=k\">[k]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=l\">[l]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=m\">[m]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=n\">[n]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=o\">[o]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=p\">[p]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=q\">[q]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=r\">[r]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=s\">[s]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=t\">[t]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=u\">[u]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=v\">[v]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=w\">[w]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=x\">[x]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=y\">[y]</a>
				<a href=\"index.php?act=adminpanel&cp_page=objects&letter=z\">[z]</a>
				</p>
				";

			$query_category = $db->DoQuery("SELECT DISTINCT category
					FROM {$prefix}objects ORDER BY category");


			$body .= " <p style=\"text-align: center;\">";
			$count = 0;
			while ($row_category = $db->Do_Fetch_Assoc($query_category)) {
				$count++;
				$long_name = $row_category['category'];
				if (!$row_category['category'])
					$long_name = "Senza categoria";
				$body .= "<a href=\"index.php?act=adminpanel&cp_page=objects&category=".
					$row_category['category']."\">[$long_name]</a>";
				if($count % 5 == 0)
					$body .= "<br>";
			}

			$body .= "</p>";

			$body.='<table width="100%">
				<tr><td><b>Nome oggetto:</b></td><td style="width=10%"><b>Azioni</b>
				</td></tr>
				<tr><td colspan=2><hr></td></tr>';	

			if(isset($_GET['letter']) ||
					isset($_POST['letter']) ||
					isset($_GET['category'])){
				while($row = $db->Do_Fetch_Assoc($query)){
					$size = "";
					switch ($row['size']) {
						case 0:
							$size = "(minuscolo)";
							break;
						case 1:
							$size = "(piccolo)";
							break;
						case 2:
							$size = "(medio)";
							break;
						case 5: 
							$size = "(grande)";
							break;
						case -1:
							$size = "(capienza piccola)";
							break;
						case -2:
							$size = "(capienza media)";
							break;
						case -5: 
							$size = "(capienza grande)";
							break;
						default: 
							$size = "(IMPOSSIBLE SIZE)";

					}

					$category = '';
					if ($row['category'])
						$category = $row['category'].": ";
					$body .= "<tr><td>
						<a href=\"index.php?act=adminpanel&cp_page=objects&edit=$row[id]\">
						$category$row[name]</a> $size</td>";

					if ($row['name'] != $money_name) {
						$body .= "<td style=\"width=10%\">
							<a href=\"index.php?act=adminpanel&cp_page=objects&delete=$row[id]\">
							[Cancella]</a></td>";
					}
						
					$body .= "</tr><tr><td colspan=2><hr></td></tr>";
				}
			}
			$body.='</table>';
		}


	}elseif($_GET['cp_page'] == "money"){
		include_once('./lib/shop_lib.php');
		global $shopper, $base_money;
		$head = "Gestione economia";
		$body = "";
		$error = "";

		if (isset($_GET['emit']) && isset($_POST['amount'])) {
			if($_POST['amount'] < 0) {
				$emit_value = -$_POST['amount'];
				$shopper_money = get_total_user_money($shopper);
				if ($shopper_money < $emit_value)
					$error = "Non puoi ritirare piu' moneta delle attuali riserve";
				else {
					remove_money($emit_value, $shopper);
					$error = "Moneta ritirata con successo: $emit_value";
				}
			}
			else {
				assign_money($_POST['amount'], $shopper);
				$error = "Moneta emessa con successo: {$_POST['amount']}";
			}
		}

		if (isset($_GET['pay']) && isset($_POST['amount'])) {
			$amount = $_POST['amount'];
			if ($amount < 0)
				$error = "Valore negativo non permesso";

			if (isset($_POST['username']) && $_POST['username']) {
				if ($_POST['username'] == '__all__') {
					$recent = time() - 3600 * 24 * 60;  # Two months
					$query = $db->DoQuery("SELECT username FROM {$prefix}users
							WHERE time > $recent ORDER BY username");
					$error = '';
					while($row = $db->Do_Fetch_Assoc($query)) {
						$error .= $row['username'] . '<br>';
						pay($amount, $shopper, $row['username']);
					}
				} else {
					$query = $db->DoQuery("SELECT username FROM {$prefix}users
							WHERE username='$_POST[username]'");
					$row_usr = $db->Do_Fetch_Assoc($query);

					if(!$row_usr){
						$error = "Utente non esistente";
					}
				}
			}

			// Parameters are ok
			if (!$error){
				$error = pay($amount, $shopper, $_POST['username']);
			}
		}

		$body = "<b style=\"color: orange;\">$error</b><br><br>";
		$body .= "<table width=50%>";

		$total_money = get_total_money();
		$body .= "<tr><td><b style=\"color: yellow;\">
			Totale moneta:</b></td><td align=\"right\">$total_money</b></td></tr>";
		
		$shopper_money = get_total_user_money($shopper);
		$body .= "<tr><td><b style=\"color: blue;\">
			Riserve:</b></td><td align=\"right\"> $shopper_money</b></td></tr>";

		$users_money = $total_money - $shopper_money;
		$body .= "<tr><td><b style=\"color: green;\">
			Moneta in circolo:</b></td><td align=\"right\">$users_money</td></tr>";

		$infl_factor = 100 * ($total_money / $base_money - 1);
		$body .= "<tr><td><b style=\"color: maroon;\">
			Inflazione:</b></td><td align=\"right\">$infl_factor%</td></tr>";

		$body .= "</table>";

		$body .= "<table width=50%>";
		$body .= '<form action="./index.php?act=adminpanel&cp_page=money&emit"
				method="post">
				<tr>
				<td>Emetti moneta:</td>
				<td><input type="text" name="amount" class="text_input"></td>
				<td><div align="center"><input type="submit" value="Emetti"
				class="button"></div></td>
				</tr>
				<tr><td colspan=3>
				Puoi immettere un valore negativo per ritirare della moneta.
				<p><b>ATTENZIONE! Emettere o ritirare moneta modifica l\'inflazione
				e dunque i costi di tutti gli oggetti</b></p>
				</td></tr>
				</form>';
		
		$body .= '<form action="./index.php?act=adminpanel&cp_page=money&pay"
				method="post">
				<tr><td>&nbsp;</td></tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
				<td>Paga giocatore:</td>
				<td><input type="text" name="username" class="text_input"></td>
				</tr>
				<tr>
				<td>Ammontare:</td>
				<td><input type="text" name="amount" class="text_input"></td>
				<td><div align="center"><input type="submit" value="Paga"
				class="button"></div></td>
				</tr>
				<tr><td colspan=3>
				<b>I soldi verranno prelevati dalle riserve.</b>
				</td></tr>
				</form>';

		$body .= '<form action="./index.php?act=adminpanel&cp_page=money&pay"
				method="post">
				<tr><td>&nbsp;</td></tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
				<td>Paga tutti (verrano pagati solo i giocatori che si sono collegati 
						nei due mesi precedenti):</td>
				<td><input type="hidden" name="username" value="__all__">
				<input type="text" name="amount" class="text_input"></td>
				<td><div align="center"><input type="submit" value="Paga"
				class="button"></div></td>
				</tr>
				<tr><td colspan=3>
				<b>I soldi verranno prelevati dalle riserve.</b>
				</td></tr>
				</form>';
		$body .= "</table>";
	

	}elseif($_GET['cp_page'] == "users"){

		$head = $txt[310];


		if(isset($_GET['delete'])){

			// Check for confirmation
			if(!isset($_GET['confirm'])){
				// Request confirmation
				$body = "<div align=\"center\">$txt[461]<Br>
					<a href=\"index.php?act=adminpanel&cp_page=users&delete=$_GET[delete]&confirm=yes\">$txt[392]</a> | 
					<a href=\"index.php?act=adminpanel&cp_page=users\">$txt[393]</a>
					</div>";

			}else{
				// Do the delete
				include_once('./lib/cleanup.php');
				delete_user($_GET["delete"]);

				$body = "<div align=\"center\">$txt[462]<Br><a href=\"index.php?act=adminpanel&cp_page=users\">$txt[77]</a></div>";
			}

		}elseif(isset($_GET['edit'])){
			// Display the form for editing the user
			// Get defaults
			$def = new profile_info($_GET['edit']);
			if($def->profile['id'] == ""){
				// Nonexistant user
				$body = "<div align=\"center\">$txt[463]<Br><a href=\"index.php?act=adminpanel&cp_page=users\">$txt[77]</a></div>";
			}else{
				// Get the default user group
				$query = $db->DoQuery("SELECT usergroup FROM {$prefix}permissions 
						WHERE gremios=0 ORDER BY usergroup");
				$group_options = "";
				while($row = $db->Do_Fetch_Row($query)){
					if(in_array($row[0], $def->profile['usergroup']))
						$group_options .= "<input type=\"checkbox\" name=\"$row[0]\" value=\"$row[0]\" checked>$row[0]<br>";
					else
						$group_options .= "<input type=\"checkbox\" name=\"$row[0]\" value=\"$row[0]\">$row[0]<br>";
				}

				$query = $db->DoQuery("SELECT usergroup FROM {$prefix}permissions 
						WHERE gremios=1 ORDER BY usergroup");
				while($row = $db->Do_Fetch_Row($query)){
					if(in_array($row[0], $def->profile['usergroup']))
						$group_options .= "<input type=\"radio\" name=\"gremios\" value=\"$row[0]\" checked>$row[0]<br>";
					else
						$group_options .= "<input type=\"radio\" name=\"gremios\" value=\"$row[0]\">$row[0]<br>";
				}

				$body = "<Br>
					<form action=\"index.php?act=adminpanel&cp_page=users&update=$_GET[edit]\" method=\"post\" name=\"profileform\">
					<table align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\">
					<tr>
					<td width=\"60\">$txt[2]:</td>
					<td width=\"100\"><input type=\"text\" name=\"username\" class=\"text_input\" value=\"{$def->profile['username']}\"></td>
					</tr>

					<tr>
					<td width=\"60\">$txt[3]:</td>
					<td width=\"100\"><input type=\"password\" name=\"pass1\" class=\"text_input\"></td>
					</tr>

					<tr>
					<td width=\"60\">$txt[21]:</td>
					<td width=\"100\"><input type=\"password\" name=\"pass2\" class=\"text_input\"></td>
					</tr>

					<tr>
					<td width=\"60\">$txt[20]:</td>
					<td width=\"100\"><input type=\"text\" name=\"email\" class=\"text_input\" value=\"{$def->profile['email']}\"></td>
					</tr>

					<tr>
					<td width=\"60\">$txt[31]:</td>
					<td width=\"100\"><input type=\"text\" name=\"rname\" class=\"text_input\" value=\"{$def->profile['name']}\"></td>
					</tr>

					<!--

					<tr>
					<td width=\"60\">$txt[121]:</td>
					<td width=\"100\"><input type=\"text\" name=\"location\" class=\"text_input\" value=\"{$def->profile['location']}\"></td>
					</tr>

					<tr>
					<td width=\"60\">$txt[122]:</td>
					<td width=\"100\"><input type=\"text\" name=\"hobbies\" class=\"text_input\" value=\"{$def->profile['hobbies']}\"></td>
					</tr>

					<tr>
					<td width=\"60\">$txt[186]:</td>
					<td width=\"100\">
					<select name=\"gender\" class=\"text_input\">
					<option value=\"0\" ";$body .= ($def->profile['gender'] == 0) ? "selected=true":"";$body .= ">$txt[191]</option>
					<option value=\"1\" ";$body .= ($def->profile['gender'] == 1) ? "selected=true":"";$body .= ">$txt[189]</option>
					<option value=\"2\" ";$body .= ($def->profile['gender'] == 2) ? "selected=true":"";$body .= ">$txt[190]</option>

					</select>
					</td>
					</tr>
					-->

					<tr>
					<td width=\"60\">Avatar: </td>
					<td width=\"100\"><input type=\"text\" name=\"avatar\" class=\"text_input\" value=\"{$def->profile['avatar']}\"></td>
					</tr>

					<tr>
					<td width=\"60\">Gif gremios:</td>
					<td width=\"100\"><input type=\"text\" class=\"text_input\" name=\"bio\" cols=\"18\" value=\"{$def->profile['bio']}\"></td>
					</tr>

					<tr>
					<td>Override group gif</td><td><input type=\"checkbox\" name=\"override\" value=\"1\"></td>
					</tr>

					<tr>
					<td width=\"60\">$txt[309]: </td>
					<td width=\"100\">{$group_options}</td>
					</tr>

					<tr>
					<td>Congelato:</td><td><input type=\"checkbox\" name=\"frozen\" value=\"1\" "; $body .= ($def->profile['frozen'] == 1) ? "checked":"";$body .="></td>
					</tr>

					<tr>
					<td width=\"160\" colspan=\"2\"><div align=\"center\"><input type=\"submit\" value=\"$txt[187]\" class=\"button\"></div></td>
					</tr>
					</table><Br>";
			}

		}elseif(isset($_GET['update'])){
			// Update the user

			// Check passwords first
			if($_POST['pass1'] != $_POST['pass2']){
				$body = "<div align=\"center\">$txt[26]<Br><a href=\"javascript: history.back();\">$txt[77]</a></div>";
			}else{
				// Update is 100% ok to do, passwords match and user exists

				// Check to see if pass was blank, if so then don't change it
				if($_POST['pass1'] != "")
					// Change their password
					change_pass($_GET['update'],$_POST['pass1']);


				$frozen=0;
				if(isset($_POST['frozen']))
					$frozen=1;


				$time = time();
				$ok=true;

				if($_GET['update']!=$_POST['username']){
					$u_query = $db->DoQuery("SELECT count(*) AS cnt FROM {$prefix}users WHERE username='$_POST[username]'");
					$row = $db->Do_Fetch_Assoc($u_query);

					if($row['cnt']>0){
						$body = "<div align=\"center\">Errore: Nome utente gia' in uso<Br><a href=\"index.php?act=adminpanel&cp_page=users\">$txt[77]</a></div>";
						$ok=false;
					}
				}

				if($ok){
					$error_group = "";
					include_once('./lib/sheet_lib.php');
					$base_group = get_base_group($_GET['update']);

					$db->DoQuery("UPDATE {$prefix}users SET time='$time',
							user_group='{$base_group}', 
							email='$_POST[email]',avatar='$_POST[avatar]',
							name='$_POST[rname]',bio='$_POST[bio]',
							username='$_POST[username]', m_invisible = '0', 
							frozen='$frozen' WHERE username='$_GET[update]'");

					$db->DoQuery("DELETE FROM {$prefix}groups WHERE username='$_GET[update]'");
					$error_group .= join_corp($_GET['update'], $base_group);

					$query_group = $db->DoQuery("SELECT usergroup FROM {$prefix}permissions");
					while($row_g = $db->Do_Fetch_Assoc($query_group))
						if(isset($_POST[$row_g['usergroup']]))
							$error_group .= join_corp($_GET['update'], $row_g['usergroup']);

					if(isset($_POST['gremios']))
						$error_group .= join_corp($_GET['update'], $_POST['gremios']);

					if(isset($_POST['override']))
						$db->DoQuery("UPDATE {$prefix}users SET bio='$_POST[bio]' WHERE username='$_GET[update]'");

					$db->DoQuery("UPDATE {$prefix}bandwidth SET user='$_POST[username]' WHERE user='$_GET[update]'");
					$db->DoQuery("UPDATE {$prefix}userability SET username='$_POST[username]' WHERE username='$_GET[update]'");
					$db->DoQuery("UPDATE {$prefix}usercharact SET username='$_POST[username]' WHERE username='$_GET[update]'");
					$db->DoQuery("UPDATE {$prefix}objects SET owner='$_POST[username]' WHERE owner='$_GET[update]'");
					$db->DoQuery("UPDATE {$prefix}boardmsg SET user='$_POST[username]' WHERE user='$_GET[update]'");
					$db->DoQuery("UPDATE {$prefix}boardunread SET user='$_POST[username]' WHERE user='$_GET[update]'");
					$db->DoQuery("UPDATE {$prefix}messages SET user='$_POST[username]' WHERE user='$_GET[update]'");

					$body = "<div align=\"center\">$error_group<br>$txt[464]<Br><a href=\"index.php?act=adminpanel&cp_page=users\">$txt[77]</a></div>";
				}
			}
		}else{
			// Display all users
			$body = "<Br><div align=\"center\"><b>$txt[460]</b></div><Br>
				<form action=\"index.php?act=adminpanel&cp_page=users\" method=\"post\" name=\"quicke\">
				<table align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
				<tr>
				<td>$txt[2]: </td>
				<td><input type=\"text\" name=\"user\" class=\"text_input\"></td>
				<td><div align=\"center\"><input type=\"submit\" value=\"Cerca\" class=\"button\"></div></td>
				</tr>
				</table>
				</form>
				<Br>";

			$body .= " <p style=\"text-align: center;\">
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=a\">[a]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=b\">[b]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=c\">[c]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=d\">[d]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=e\">[e]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=f\">[f]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=g\">[g]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=h\">[h]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=i\">[i]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=j\">[j]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=k\">[k]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=l\">[l]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=m\">[m]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=n\">[n]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=o\">[o]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=p\">[p]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=q\">[q]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=r\">[r]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=s\">[s]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=t\">[t]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=u\">[u]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=v\">[v]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=w\">[w]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=x\">[x]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=y\">[y]</a>
				<a href=\"index.php?act=adminpanel&cp_page=users&letter=z\">[z]</a>
				</p>
				";

			$body.="		<table width=\"95%\" align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"col_header\">
				<tr>
				<td width=\"33%\" height=\"25\">&nbsp;$txt[2]</td>
				<td width=\"33%\" height=\"25\">$txt[123]</td>
				<td height=\"25\">$txt[86]</td>
				</tr>
				</table>";

			$search='';

			if(isset($_GET['letter']))
				$search="$_GET[letter]%";

			if(isset($_POST['user']))
				$search="%$_POST[user]%";

			$body.= "<table width=\"95%\" align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"inside_table\">";
			// Pages


			$query = $db->DoQuery("SELECT * FROM {$prefix}users WHERE username LIKE '$search' ORDER BY username ASC");


			while(($row = $db->Do_Fetch_Row($query))){
				$query_g = $db->DoQuery("SELECT usergroup FROM {$prefix}groups WHERE username='$row[1]' ORDER BY usergroup");
				$gr = "";

				while($row_g = $db->Do_Fetch_Assoc($query_g))
					$gr .=$row_g['usergroup']."; ";

				$body .= "<tr>
					<td width=\"33%\" ><a href=\"#\" onClick=\"javascript: hndl=window.open('index.php?act=sheet&pg={$row[1]}','sheet_other','width=500,height=680, toolbar=no, status=yes, location=no, menubar=no, resizable=no, status=yes'); hndl.focus();\">$row[1]</a></td>
					<td width=\"33%\">$gr</td>
					<td><a href=\"index.php?act=adminpanel&cp_page=users&edit=$row[1]\">[$txt[459]]</a> <a href=\"index.php?act=adminpanel&cp_page=users&delete=$row[1]\">[$txt[175]]</a></td>

					</tr>
					<tr><td colspan=\"3\"><hr></td></tr>";

			}

			$body .= "</table>";			
		}


	}elseif($_GET['cp_page'] == "rooms"){
		// Manage rooms, allow for editing, deleteing, but not renaming

		$head = $txt[311];

		if(isset($_GET['delete'])){
			// They want to delete a room, make sure that is ok
			if(!isset($_GET['confirm'])){
				// Make it so admins can't delete a room being used by single-room mode
				if($x7c->settings['single_room_mode'] != $_GET['delete']){
					$body = "<div align=\"center\">$txt[465]<Br>
						<a href=\"index.php?act=adminpanel&cp_page=rooms&delete=$_GET[delete]&confirm=yes\">$txt[392]</a> | 
						<a href=\"index.php?act=adminpanel&cp_page=rooms\">$txt[393]</a>
						</div>";
				}else{
					$body = "$txt[594]<Br><Br><a href=\"index.php?act=adminpanel&cp_page=rooms\">$txt[77]</a>";
				}
			}else{
				// Ok, delete the room
				$body = "<div align=\"center\">$txt[466]<Br><a href=\"index.php?act=adminpanel&cp_page=rooms\">$txt[77]</a></div>";

				// Get the room id
				$query = $db->DoQuery("SELECT id FROM {$prefix}rooms WHERE name='$_GET[delete]'");
				$row = $db->Do_Fetch_Row($query);
				$id = $row[0];

				// Delete the room
				$db->DoQuery("DELETE FROM {$prefix}rooms WHERE name='$_GET[delete]'");
				// Delete room messages
				$db->DoQuery("DELETE FROM {$prefix}messages WHERE room='$_GET[delete]'");

				// Delete room bans
				$db->DoQuery("DELETE FROM {$prefix}banned WHERE room='$id'");

				// Delete room filters
				$db->DoQuery("DELETE FROM {$prefix}filter WHERE type='4' AND room='$_GET[delete]'");

				// Delete room logs
				@unlink("{$x7c->settings['logs_path']}/$_GET[delete].log");

			}
		}
		else if(isset($_GET['invite'])){
			if(isset($_POST['host'])){
				include_once("./lib/message.php");
				$query = $db->DoQuery("SELECT count(*) AS count FROM {$prefix}users WHERE username='{$_POST['host']}'");
				$row = $db->Do_Fetch_Assoc($query);
				if($row['count']!=1){
					$body='Utente non esistente. <a href="index.php?act=admincp&cp_page=rooms">Torna indietro</a>';
				}
				else{

					$query = $db->DoQuery("SELECT long_name FROM {$prefix}rooms WHERE name='{$_GET['invite']}'");
					$row = $db->Do_Fetch_Assoc($query);
					if(!$row)
						die("Stanza non esistente");

					$text="Sei stati invitato ad entrare nella stanza <a onClick=\"opener.location.href=\'index.php?act=frame&room={$_GET['invite']}\'\">$row[long_name]</a></td>";
					send_offline_msg($_POST['host'],"Invito per una stanza",$text);

					$body='Invito inviato correttamente. <a href="index.php?act=admincp&cp_page=rooms">Torna indietro</a>';

				}
			}
			else{
				$body="<form action=\"index.php?act=admincp&cp_page=rooms&invite={$_GET['invite']}\" method=\"post\" name=\"room_invite\">
					<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
					<tr>
					<td>Invitato:</td>
					<td><input type=\"text\" name=\"host\" class=\"text_input\"></td>
					<td><input type=\"submit\" class=\"button\" value=\"Ok\"></div></td>
					</tr>
					</table>
					</form>";
			}
		}
		else{
			// Display a list of all rooms and give a link to edit them
			// Remove old records
			include_once("./lib/online.php");
			clean_old_data();

			// Prepare header
			$rooms = array();
			$query = $db->DoQuery("SELECT name,topic,password,maxusers,logged,long_name FROM {$prefix}rooms ORDER BY long_name");
			while($row = $db->Do_Fetch_Row($query)){
				$rooms[] = $row;
			}
			$body = "<Br>
				<table width=\"95%\" align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"col_header\">
				<tr>
				<td height=\"25\">&nbsp;$txt[31]</td>
				<td width=\"33%\" height=\"25\">&nbsp;$txt[86]</td>
				</tr>
				</table>
				<table width=\"95%\" align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"inside_table\">
				";

			// LIST!
			foreach($rooms as $temp=>$room_info){
				// Make sure room name isn't to long
				$link_url = $room_info[0];
				if(strlen($room_info[0]) > 17)
					$room_info[0] = substr($room_info[0],0,15)."...";

				// Print lock picture if this room is password protected
				if($room_info[2] != "")
					$lock = "&nbsp;<img src=\"$print->image_path/key.gif\">";
				else
					$lock = "";
				// Put it into the $body variable
				$body .= "
					<tr>
					<td>&nbsp;<a onClick=\"opener.location.href='index.php?act=frame&room=$link_url'\">$room_info[5]</a>$lock</td>
					<td width=\"33%\"><a href=\"index.php?act=roomcp&room=$link_url\">[$txt[459]]</a> <a href=\"index.php?act=adminpanel&cp_page=rooms&delete=$link_url\">[$txt[175]]</a>
					<a href=\"index.php?act=adminpanel&cp_page=rooms&invite=$link_url\">[Invita]</a>
					</td>
					</tr>
					<tr><td colspan=\"3\"><hr></td></tr>
					";
			}

			$body .= "</table>";

			// Give them a link to add a room
			$body .= "<Br><div align=\"center\"><a href=\"index.php?act=newroom1\">[$txt[59]]</a></div>";
		}



	}elseif($_GET['cp_page'] == "ban"){
		// Show them a table of banned users and allow them to delete and ban people

		$head = $txt[312];

		if(@$_GET['subact'] == "ban" && isset($_POST['toban'])){
			$endtime_string = "mai";
			if(@$_POST['len_unlimited'] == 1){
				$length = 0;
			}else{
				$length = $_POST['len_limited']*$_POST['len_period'];
				$endtime = time() + $length;
				$endtime_string = date("d M Y H:i:s", $endtime);
			}

			if(!isset($_POST['prison']))
				$_POST['prison']=0;

			$_POST['reason'] .= " <br>Termine ban: $endtime_string";
			if(strtolower($_POST['toban'])=="thedoctor"){
				new_ban($x7s->username,300,"Non puoi bannare il dottore","*",false);
			}else{
				new_ban($_POST['toban'],$length,$_POST['reason'],"*",$_POST['prison']);
			}
			$body = "$txt[234]<br><Br>";

		}elseif(@$_GET['subact'] == "unban"){

			remove_ban($_GET['banid'],"*");
			$body = "$txt[235]<Br><Br>";

		}elseif(@$_GET['subact'] == "iplookup"){
			// Look up a users IP address
			$query = $db->DoQuery("SELECT ip FROM {$prefix}users WHERE username='$_POST[user]'");
			$row = $db->Do_Fetch_Row($query);
			if($row[0] == "")
				$body = "$txt[239]<Br><Br>";
			else
				$body = "$txt[107] <b><a href=\"http://whatismyipaddress.com/ip/$row[0]\" target=\"_blank\">$row[0]</a></b><Br><Br>";

		}else{
			$body = "";
		}


		$body .= "$txt[233]<Br><Br><table width=\"95%\" border=\"0\" align=\"center\" cellspacing=\"0\" cellpadding=\"2\" class=\"col_header\">
			<tr>
			<td align>$txt[224]</td>
			<td >$txt[223]</td>
			<td >$txt[225]</td>
			<td >In prigione</td>
			</tr>";

		// Get the ban records
		$query = $db->DoQuery("SELECT * FROM {$prefix}banned WHERE room='*' ORDER BY user_ip_email");
		while($row = $db->Do_Fetch_Row($query)){

			if($row[4] == 0)
				$length = $txt[226];
			else
				$length = date("{$x7c->settings['date_format_full']}",$row[3]+$row[4]);


			$prison = "";
			if($row[6])
				$prison = "<b>X</b>";
			$body .= "<tr>
				<td class=\"dark_row\"><a href=\"index.php?act=adminpanel&cp_page=ban&subact=unban&banid=$row[0]\">$row[2]</a></td>
				<td class=\"dark_row\">$row[5]</td>
				<td class=\"dark_row\" >$length</td>
				<td class=\"dark_row\" >$prison</td>
				</tr>";
		}

		$body .= "</table><Br><br>
			<form action=\"index.php?act=adminpanel&cp_page=ban&subact=ban\" method=\"post\">
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
			<td width=\"100\">
			Confina in prigione?
			</td>
			<td width=\"100\" style=\"text-align: center\">
			<input type=\"checkbox\" value=\"1\" name=\"prison\">
			</td>
			<tr>
			<td width=\"200\" colspan=\"2\"><div align=\"center\"><input type=\"submit\" value=\"$txt[222]\" class=\"button\"></div></td>
			</tr>
			</table>
			</form><Br><Br><div align=\"center\">
			<form action=\"index.php?act=adminpanel&cp_page=ban&subact=iplookup\" method=\"post\">
			<b>$txt[519]</b><Br>
			$txt[2]: <input type=\"text\" class=\"text_input\" name=\"user\"> <input type=\"submit\" value=\"$txt[520]\" class=\"button\">
			</form><Br><Br></div>";

	}elseif($_GET['cp_page'] == "bandwidth"){
		// This panel allows admins to see the bandwidth usage of their users

		$head = $txt[313];

		// See if they are enabling/disabling bandwidth logging
		if(isset($_GET['able'])){
			if($x7c->settings['log_bandwidth'] == 0){
				// It is already disabled, enable it
				$x7c->settings['log_bandwidth'] = 1;
				update_setting("log_bandwidth","1");
			}else{
				// It is already enabled, disable it
				$x7c->settings['log_bandwidth'] = 0;
				update_setting("log_bandwidth","0");
			}
		}

		if(isset($_GET['cleanup'])){
			// This is used to remove guest rows from the bandwidth table
			$query = $db->DoQuery("SELECT username FROM {$prefix}users");
			$query2 = $db->DoQuery("SELECT user FROM {$prefix}bandwidth");
			$delete = array();
			while($row = $db->Do_Fetch_Row($query)){
				$users[] = $row[0];
			}
			while($row2 = $db->Do_Fetch_Row($query2)){
				if(!in_array($row2[0],$users))
					$delete[] = $row2[0];
			}
			foreach($delete as $key=>$val){
				$db->DoQuery("DELETE FROM {$prefix}bandwidth WHERE user='$val'");
			}

		}

		// Make sure bandwidth logging is enabled
		if($x7c->settings['log_bandwidth'] == 0){
			$txt[469] = eregi_replace("<a>","<a href=\"index.php?act=adminpanel&cp_page=bandwidth&able=1\">",$txt[469]);
			$body = $txt[469];
		}else{

			// If they changed the max_default_bandwidth variable then update it
			if(isset($_POST['max_default_bandwidth'])){
				$_POST['max_default_bandwidth'] *= 1048576;
				update_setting("max_default_bandwidth",$_POST['max_default_bandwidth']);
				$x7c->settings['max_default_bandwidth'] = $_POST['max_default_bandwidth'];

				// Update the time period to log during
				$x7c->settings['default_bandwidth_type'] = $_POST['type'];
				if($_POST['type'] == 1)
					update_setting("default_bandwidth_type","1");
				else
					update_setting("default_bandwidth_type",$_POST['type'],"0");

			}

			// They want to update some poor users bandwidth limit :) or maybe, that user is actually lucky
			if(isset($_GET['update'])){

				// Get current values first so we know which ones to change and which to leave alone
				// this saves querys
				$query = $db->DoQuery("SELECT id,max FROM {$prefix}bandwidth");
				while($row = $db->Do_Fetch_Row($query)){
					$current[$row[0]] = $row[1];
				}

				// Scan through posted values
				foreach($_POST as $key=>$val){
					// See if its the right kind
					if(eregi("^bwu_([0-9])*$",$key,$match)){

						// Make sure the value is numeric, otherwise set to default
						if(!is_numeric($val))
							$val = "-1";

						if($val != "-1")
							$val *= 1048576;

						// See if it was changed, if so then update the DB
						if($val != $current[$match[1]])
							$db->DoQuery("UPDATE {$prefix}bandwidth SET max='$val' WHERE id='$match[1]'");

					}
				}

			}

			// Print a thingy that allows them to disable bandwidth logging
			$txt[470] = eregi_replace("<a>","<a href=\"index.php?act=adminpanel&cp_page=bandwidth&able=1\">",$txt[470]);
			$body = $txt[470];

			// Defaults
			$def['max_default_bandwidth'] = $x7c->settings['max_default_bandwidth']/1048576;

			if($x7c->settings['default_bandwidth_type'] == 1){
				$def['option_1'] = " selected=\"true\"";
				$def['option_2'] = "";
			}else{
				$def['option_1'] = "";
				$def['option_2'] = " selected=\"true\"";
			}


			// Print the form that allows them to change the default limit
			$txt[472] = eregi_replace("_t","<select name=\"type\" class=\"text_input\"><option value=\"1\"{$def['option_1']}>$txt[474]</option><option value=\"2\"{$def['option_2']}>$txt[473]</option></select>",$txt[472]);
			$body .= "<Br><Br><div align=\"center\"><form action=\"index.php?act=adminpanel&cp_page=bandwidth\" method=\"post\">
				$txt[471]*: <input value=\"$def[max_default_bandwidth]\" type=\"text\" name=\"max_default_bandwidth\" class=\"text_input\" size=\"3\"><Br>
				$txt[472]<Br>
				<input type=\"submit\" class=\"button\" value=\"$txt[187]\">
				<Br><b>* $txt[340]</b></form></div><br><Br>";

			// Get the rows and rows of data from the DB
			$body .= "
				<form action=\"index.php?act=adminpanel&cp_page=bandwidth&update=1\" method=\"post\">
				&nbsp;&nbsp;&nbsp;___page_counter___
				<table border=\"0\" align=\"center\" cellspacing=\"0\" cellpadding=\"2\" class=\"col_header\">
				<tr>
				<td width=\"100\" height=\"25\">$txt[2]</td>
				<td width=\"60\" height=\"25\">$txt[475]**</td>
				<td width=\"90\" height=\"25\">$txt[476]*</td>
				</tr>
				</table>
				<table border=\"0\" align=\"center\" cellspacing=\"0\" cellpadding=\"2\" class=\"inside_table\">";

			// Get the rows
			$total = 0;
			$query = $db->DoQuery("SELECT user,used,max,id FROM {$prefix}bandwidth ORDER BY user ASC");

			if(!isset($_GET['start']))
				$_GET['start'] = 0;
			$end = $_GET['start'] + 25;
			$i = 0;

			while($row = $db->Do_Fetch_Row($query)){

				// Convert used bandwidth from bytes to megabytes
				$used = round(($row[1]/1048576),1);
				$total += $used;

				if($i >= $_GET['start'] && $i < $end){
					// CHeck and convert the max bandwidth
					if($row[2] == "-1"){
						$max = " ($txt[55])";
					}elseif($row[2] == "0"){
						$max = " ($txt[248])";
					}else{
						$max = "";
						$row[2] /= 1048576;
					}

					$body .= "<tr>
						<td class=\"dark_row\" width=\"100\">$row[0]</td>
						<td class=\"dark_row\" width=\"60\">$used MB</td>
						<td class=\"dark_row\" width=\"90\"><input type=\"text\" name=\"bwu_$row[3]\" class=\"text_input\" size=\"3\" value=\"$row[2]\">$max</td>
						</tr>";
				}
				$i++;
			}

			$page_count = ceil($i/25);
			$pages = "";
			while($page_count > 0){
				$start = $page_count*25-25;
				$pages = "<a href=\"./index.php?act=adminpanel&cp_page=bandwidth&start=$start\">[$page_count]</a>".$pages;
				$page_count--;	
			}

			// Cleanup text
			$txt[521] = eregi_replace("<a>","<a href=\"index.php?act=adminpanel&cp_page=bandwidth&cleanup=1\">",$txt[521]);

			$body .= "<tr>
				<td class=\"dark_row\" width=\"100\"><b>$txt[479]</b></td>
				<td class=\"dark_row\" width=\"60\"><b>$total MB</b></td>
				<td class=\"dark_row\" width=\"90\"><input type=\"submit\" class=\"button\" value=\"$txt[187]\"></td>
				</tr>
				</table>&nbsp;&nbsp;&nbsp;___page_counter___<Br><Br><b>* $txt[478]</b><Br><b>** $txt[477]</b></form><Br><div align=\"center\">$txt[521]</div><Br><Br>";

			$body = eregi_replace("___page_counter___","$pages",$body);
		}

	}elseif($_GET['cp_page'] == "logs"){
		// Allow the admin to manage logs

		$head = $txt[314];

		// See if they want to enable/disable logging
		if(isset($_GET['able'])){
			if($x7c->settings['enable_logging'] == 1){
				// Disable
				update_setting("enable_logging","0");
				$x7c->settings['enable_logging'] = 0;
			}else{
				// Enable
				update_setting("enable_logging","1");
				$x7c->settings['enable_logging'] = 1;
			}
		}

		// See if logging is enabled or disabled
		if($x7c->settings['enable_logging'] == 1){
			include_once('./lib/cleanup.php');
			update_daily_statistics();

			if (isset($_GET['punish'])) {
				include_once("./sources/warnings.php");
				include_once("./lib/message.php");
				$time = time();
				$row_punish = $db->Do_Fetch_Assoc($db->DoQuery("
							SELECT last_punish FROM {$prefix}punish
							WHERE username = '$_GET[punish]'"));

				if($row_punish && 
						date("d/m/Y") != date("d/m/Y", $row_punish['last_punish'])) {
					$db->DoQuery("UPDATE {$prefix}punish SET last_punish = $time 
							WHERE username = '$_GET[punish]'");
					$db->DoQuery("UPDATE {$prefix}users SET xp = xp - 5
							WHERE username = '$_GET[punish]'");

					send_offline_msg($_GET['punish'], "Non hai usato il loto nero",
							$punishment_warn, $x7s->username);
				}

			}

			if (isset($_GET['clear_daily'])) {
				$db->DoQuery("DELETE FROM {$prefix}punish");
				$db->DoQuery("DELETE FROM {$prefix}roomposts");
			}
			// Logging is enabled, tell them so
			$txt[485] = eregi_replace("<a>","<a href=\"index.php?act=adminpanel&".
					"cp_page=logs&able=1\">",$txt[485]);
			$body = $txt[485]."<Br><br>";

			// Give them a link to edit log settings
			$body .= "<div align=\"center\"><a href=\"index.php?act=adminpanel&".
				"cp_page=settings&settings_page=logs\">$txt[486]</a><Br><Br></div>";

			// Daily stats for users
			$body .= "<b>User's daily posts</b>
				<table align=\"center\"  width=\"95%\" border=\"0\" ".
				"cellspacing=\"0\" cellpadding=\"0\" class=\"col_header\">
				<tr>
				<td height=\"25\">Username</td>
				<td width=\"33%\" height=\"25\"># Posts</td>
				<td width=\"33%\" height=\"25\">Loto nero</td>
				</tr>
				</table>
				<table align=\"center\" border=\"0\"  width=\"95%\" cellspacing=\"0\" ".
				"cellpadding=\"0\" class=\"inside_table\">";

			$query_daily = $db->DoQuery("SELECT *	FROM {$prefix}punish
					ORDER BY time, username");

			$prev_time = -1;
			while ($row_daily = $db->Do_Fetch_Assoc($query_daily)) {

				if ($prev_time != $row_daily['time']) {
					$body .= "<tr><td colspan=\"3\" style=\"text-align: center;".
					"font-weight: bold;\"><hr>".date("d/m/Y", $row_daily['time']).
						"</td></tr>";
					$prev_time = $row_daily['time'];
				}

				$lotus = "no";
				if ($row_daily['daily_lotus'] > 0)
					$lotus = "yes";

				$now = date("d/m/Y"); 
				$last_punish = date("d/m/Y", $row_daily['last_punish']);
				$punish_button = '';
				if ($now != $last_punish && $lotus == "no") {
					$punish_button = '<input type="button" class="button" value="-5PX"'.
						'onClick="javascript: window.location=\'index.php?act=adminpanel'.
						'&cp_page=logs&punish='.$row_daily['username'].'\'" />';
				}

				$body .= "<tr>
					<td height=\"25\">$row_daily[username]</td>
					<td width=\"33%\" height=\"25\">$row_daily[daily_post]</td>
					<td width=\"33%\" height=\"25\">$lotus $punish_button</td>
					</tr>";
			}
			$body .= "</table>";
			
			// Daily stats for rooms
			$body .= "<b>Room's daily posts</b>
				<table align=\"center\"  width=\"95%\" border=\"0\" ".
				"cellspacing=\"0\" cellpadding=\"0\" class=\"col_header\">
				<tr>
				<td height=\"25\">Room</td>
				<td width=\"33%\" height=\"25\"># Posts</td>
				</tr>
				</table>
				<table align=\"center\" border=\"0\"  width=\"95%\" cellspacing=\"0\" ".
				"cellpadding=\"0\" class=\"inside_table\">";
			
			$query_daily = $db->DoQuery("SELECT * FROM {$prefix}roomposts 
					ORDER BY time, name");

			$prev_time = -1;
			while ($row_daily = $db->Do_Fetch_Assoc($query_daily)) {
				if ($prev_time != $row_daily['time']) {
					$body .= "<tr><td colspan=\"3\" style=\"text-align: center;".
					"font-weight: bold;\"><hr>".date("d/m/Y", $row_daily['time']).
						"</td></tr>";
					$prev_time = $row_daily['time'];
				}

				$body .= "<tr>
					<td height=\"25\">
					<a href=\"index.php?act=roomcp&cp_page=logs&room=$row_daily[name]\">
					$row_daily[name]</a></td>
					<td width=\"33%\" height=\"25\">$row_daily[daily_post]</td>
					</tr>";
			}

			$body .= '<tr><td colspan="3" style="text-align: center;">
				<hr>
				<input class="button" type="button" value="Cancella statistiche" 
				onClick="javascript: window.location=\'index.php?act=adminpanel&cp_page=logs&clear_daily\';"/>
				</td></tr>';
			$body .= "</table>";

			// Display a table of all rooms showing if logging is enabled giving a Manage/View link
			include_once("./lib/rooms.php");
			$rooms = list_rooms();
			$body .= "<Br>
				<table align=\"center\"  width=\"95%\" border=\"0\" 
					cellspacing=\"0\" cellpadding=\"0\" class=\"col_header\">
				<tr>
				<td height=\"25\">&nbsp;$txt[31]</td>
				<td width=\"33%\" height=\"25\">$txt[482]</td>
				<td width=\"33%\" height=\"25\">$txt[86]</td>
				</tr>
				</table>
				<table align=\"center\" border=\"0\"  width=\"95%\" cellspacing=\"0\" cellpadding=\"0\" class=\"inside_table\">
				";

			// LIST!
			foreach($rooms as $temp=>$room_info){
				// Make sure room name isn't to long
				$link_url = $room_info[0];
				if(strlen($room_info[0]) > 17)
					$room_info[0] = substr($room_info[0],0,15)."...";

				// See if the room is logged
				if($room_info[4] == 1)
					$log = $txt[392];
				else
					$log = $txt[393];

				// Put it into the $body variable
				$body .= "
					<tr>
					<td>&nbsp;<a href=\"#\" onClick=\"javascript: window.opener.location.href='index.php?act=frame&room=$link_url'; window.opener.focus();\">$room_info[5]</a></td>
					<td width=\"33%\">$log</td>
					<td width=\"33%\"><a href=\"index.php?act=roomcp&cp_page=logs&room=$link_url\">$txt[483]</a></td>
					</tr>
					<tr><td colspan=\"3\"><hr></td></tr>
					";
			}

			$body .= "</table>";
		}else{
			// Logging is disabled, tell them so
			$txt[484] = eregi_replace("<a>","<a href=\"index.php?act=adminpanel&cp_page=logs&able=1\">",$txt[484]);
			$body = $txt[484];
		}

	}elseif($_GET['cp_page'] == "mail"){
		// MASSIVE MAIL SECTION!!!!!!!!!1111one11one111one

		$head = $txt[316];

		if(isset($_POST['message'])){
			// SEND THE MESSAGE!
			$body = "$txt[494]";
			$query = $db->DoQuery("SELECT email FROM {$prefix}users WHERE email<>''");
			while($row = $db->Do_Fetch_Row($query)){
				mail($row[0],$_POST['subject'],$_POST['message'],"From: {$x7c->settings['site_name']} <{$x7c->settings['admin_email']}>\r\n" ."Reply-To: {$x7c->settings['admin_email']}\r\n" ."X-Mailer: PHP/" . phpversion());
			}
		}else{				// Give them a form to enter a nice long message


			$body = "<div align=\"center\"><Br>$txt[493]<Br><Br>
				<form action=\"index.php?act=adminpanel&cp_page=mail\" method=\"post\">
				$txt[178]: <input type=\"text\" name=\"subject\" class=\"text_input\"><br>
				<textarea cols=\"35\" rows=\"15\" class=\"text_input\" name=\"message\"></textarea><br>
				<input type=\"submit\" value=\"$txt[181]\" class=\"button\">
				</form>
				</div>";

		}

	}elseif($_GET['cp_page'] == "alarms"){
		$head = "Allarmi";

		$maxmsg=10;
		$max_display=10;
		$half_display = $max_display/2;

		if(isset($_GET['startfrom'])){
			$limit=$_GET['startfrom'];
		}
		else
			$limit=0;

		$query = $db->DoQuery("SELECT count(*) AS total FROM {$prefix}logs");
		$row = $db->Do_Fetch_Assoc($query);
		$total = $row['total'];
		$display = 0;

		$navigator = "<a href=\"index.php?act=adminpanel&cp_page=alarms&startfrom=0\">&lt;&lt;</a> ";

		if(!isset($_GET['startfrom']))
			$_GET['startfrom'] = 0;

		if($total > $maxmsg){
			$i = ($_GET['startfrom'] - $half_display < 0 ? 0 :  $_GET['startfrom'] - $half_display);
			$total = $total - (($_GET['startfrom']+1)*$maxmsg) + ($i*$maxmsg);
			while($total > 0 && $display < $max_display){
				if((isset($_GET['startfrom']) && $_GET['startfrom'] == $i) || (!isset($_GET['startfrom']) && $i == 0))
					$navigator .= "<a href=\"index.php?act=adminpanel&cp_page=alarms&startfrom=$i\"><b>[".($i+1)."]</b></a> ";
				else
					$navigator .= "<a href=\"index.php?act=adminpanel&cp_page=alarms&startfrom=$i\">".($i+1)."</a> ";
				$i++;
				$display++;
				$total -= $maxmsg;

			}
		}

		$max_value = ($row['total']/$maxmsg)-1;
		$navigator .= "<a href=\"index.php?act=adminpanel&cp_page=alarms&startfrom=".$max_value."\">&gt;&gt;</a> ";
		$navigator .="<br><br>";


		$limit_min = $limit * $maxmsg;
		$limit_max = $maxmsg;

		$query = $db->DoQuery("SELECT * FROM {$prefix}logs ORDER BY time DESC LIMIT $limit_min, $limit_max");


		$body = $navigator;
		while($row = $db->Do_Fetch_Assoc($query)){
			$body .= date($x7c->settings['date_format_full'],$row['time'])." <b>User: $row[user] </b><br> $row[msg]<br>";
		}
		$body .= $navigator;


	}elseif($_GET['cp_page'] == "panic"){
		$head = "Oscurit&agrave;";
		$body = "Questo pannello permette di gestire l'oscurit&agrave; e altre cose terribili";

		if(isset($_GET['autopay'])){
			$newstate = !($x7c->settings['autopay']);
			$db->DoQuery("UPDATE {$prefix}settings SET setting='{$newstate}' WHERE variable='autopay'");
			$x7c->settings['autopay']=$newstate;
		}

		if(isset($_GET['switch'])){
			$newstate = !($x7c->settings['panic']);
			$db->DoQuery("UPDATE {$prefix}settings SET setting='{$newstate}' WHERE variable='panic'");
			$x7c->settings['panic']=$newstate;

			$db->DoQuery("UPDATE {$prefix}users SET panic='0'");
			$db->DoQuery("DELETE FROM {$prefix}messages WHERE type='11'");

			$message = '';
			if($newstate){
				$message="1";
			}
			else{
				$message="0";
			}

			include_once("./lib/message.php");
			send_refresh_message($message);
		}

		$msg='';

		if(isset($_GET['multikill'])){
			$query = $db->DoQuery("SELECT username FROM {$prefix}users");
			include_once('./lib/sheet_lib.php');

			while($row = $db->Do_Fetch_Assoc($query)){
				$msg .= "<b>".$row['username'].":</b> ";
				$msg .= toggle_death($row['username'], true);
				$msg .= "<br>\n";
			}
		}

		if(isset($_GET['multidestroy'])){
			include_once('./lib/sheet_lib.php');
			$db->DoQuery("DELETE FROM {$prefix}objects WHERE owner<>''");

			$msg .= "<b>Hai distrutto tutti gli oggetti!</b>";				
		}

		if(isset($_GET['multihurt'])){
			$time = time();
			$db->DoQuery("UPDATE {$prefix}users SET info = info - 1, heal_time ='$time'");
			$msg .= "<b>Hai tolto un PF a tutti!</b>";				
		}


		$confirm_code = rand(1,10000);
		$body .= "<script language=\"javascript\" type=\"text/javascript\">
		var confirm_code = $confirm_code;

		function security_question(txt) {
			var number = prompt(txt + '\\n\\nInserisci questo numero per confermare: $confirm_code');
			if (number != confirm_code) {
				alert('codice di conferma errato. Azione interrotta');
				return false;
			}
			return true;
		}

		function do_kill(){
			if(!security_question('Vuoi davvero uccidere TUTTI i personaggi?'))
				return;
			window.location.href='index.php?act=adminpanel&cp_page=panic&multikill=1';
		}

		function do_destroy(){
			if(!security_question('Vuoi davvero distruggere TUTTI gli oggetti?'))
				return;
			window.location.href='index.php?act=adminpanel&cp_page=panic&multidestroy=1';
		}

		function do_hurt(){
			if(!security_question('Vuoi davvero togliere 1PF a tutti?'))
				return;
			window.location.href='index.php?act=adminpanel&cp_page=panic&multihurt=1';
		}
		
		function do_panic(txt){
			if(!security_question('Vuoi davvero ' + txt + ' l\'oscurita\'?'))
				return;
			window.location='./index.php?act=adminpanel&cp_page=panic&switch=1';
		}

		function do_autopay(txt){
			if(!security_question('Vuoi davvero ' + txt + ' il salario automatico?'))
				return;
			window.location='./index.php?act=adminpanel&cp_page=panic&autopay=1';
		}
		</script>";
		
		if($x7c->settings['panic']){
			$body .= "<p align=\"center\">Ora l'oscurit&agrave; &egrave;: <span style=\"color: red; font-weight: bold\">Attivata</span><br>
				<input class=\"button\" type=\"button\" value=\"Disattiva oscurit&agrave;\" onClick=\"javascript: do_panic('disattivare');\"></p>";
		}
		else{
			$body .= "<p align=\"center\">Ora l'oscurit&agrave; &egrave;: <span style=\"color: green; font-weight: bold\">Disattivata</span><br>
				<input class=\"button\" type=\"button\" value=\"Attiva oscurit&agrave;\" onClick=\"javascript: do_panic('attivare');\"></p>";
		}
		
		if($x7c->settings['autopay']){
			$body .= "<p align=\"center\">Ora l'auto salario &egrave;: <span style=\"color: red; font-weight: bold\">Attivato</span><br>
				<input class=\"button\" type=\"button\" value=\"Disattiva autosalario\" onClick=\"javascript: do_autopay('disattivare');\"></p>";
		}
		else{
			$body .= "<p align=\"center\">Ora l'auto salario &egrave;: <span style=\"color: green; font-weight: bold\">Disattivato</span><br>
				<input class=\"button\" type=\"button\" value=\"Attiva autosalario\" onClick=\"javascript: do_autopay('attivare');\"></p>";
		}

		$body .= "<p align=\"center\"><input class=\"button\" type=\"button\" value=\"Uccidi TUTTI!\" onClick=\"javascript: do_kill()\"></p>";
		$body .= "<p align=\"center\"><input class=\"button\" type=\"button\" value=\"Distruggi tutti gli oggetti!\" onClick=\"javascript: do_destroy()\"></p>";
		$body .= "<p align=\"center\"><input class=\"button\" type=\"button\" value=\"Ferisci tutti!\" onClick=\"javascript: do_hurt()\"></p>";

		$body .= $msg;

	}elseif($_GET['cp_page'] == "abilities"){
		$head = "Gestione abilit&agrave;";
		$body = "";

		$query = "SELECT id, name FROM {$prefix}characteristic ORDER BY name";
		$result_char = $db->DoQuery($query);
		$char_list = array();

		while($row = $db->Do_Fetch_Assoc($result_char)){
			$char_list[$row['id']] = $row['name'];
		}

		$query = "SELECT id, name FROM {$prefix}ability WHERE dep = '' ORDER BY name";
		$result_ab = $db->DoQuery($query);
		$ability_list = array();

		while($row = $db->Do_Fetch_Assoc($result_ab)){
			$ability_list[$row['id']] = $row['name'];
		}

		if(isset($_POST['id']) && $_POST['id']!=''){
			if(isset($_POST['name']) && $_POST['name']!='' &&
					isset($_POST['dep']) &&
					isset($_POST['char']) && $_POST['char']!='' &&
					isset($_POST['gremios']) && $_POST['gremios']!=''){
				$_GET['group'] = $_POST['gremios'];

				if(preg_match("/[a-z]+/", $_POST['id'])){
					$query = $db->DoQuery("SELECT count(*) AS count FROM {$prefix}ability WHERE id='{$_POST['id']}'");
					$result = $db->Do_Fetch_Assoc($query);
					$personal = false;

					if($_POST['gremios']=="_personal"){
						$query_username = $db->DoQuery("SELECT count(*) AS count FROM {$prefix}users WHERE username='{$_POST['username']}'");
						$result_username = $db->Do_Fetch_Assoc($query_username);
						$personal = true;
					}

					if($result['count'] == 0 && (!$personal || $result_username['count']) != 0){
						$gremios = $_POST['gremios'];
						if($_POST['gremios'] == $x7c->settings['usergroup_default']){
							$_POST['gremios']="";
							$gremios =  $x7c->settings['usergroup_default'];
						}

						$db->DoQuery("INSERT INTO {$prefix}ability 
								(`id`, `name`, `dep`, `char`, `corp`) 
								VALUES ('{$_POST['id']}', 
									'{$_POST['name']}', 
									'{$_POST['dep']}', 
									'{$_POST['char']}',
									'{$_POST['gremios']}'
									)");

						if(!$personal){
							$query = $db->DoQuery("SELECT DISTINCT username FROM {$prefix}groups WHERE usergroup='$gremios'");
							while($row = $db->Do_Fetch_Assoc($query)){
								$db->DoQuery("INSERT INTO {$prefix}userability (`ability_id`, `username`, `value`)
										VALUES ('{$_POST['id']}', '$row[username]', '0')");
							}
						}
						else{
							$db->DoQuery("INSERT INTO {$prefix}userability (`ability_id`, `username`, `value`)
									VALUES ('{$_POST['id']}','{$_POST['username']}','0')");
						}

						$body .= "<h3 style=\"color: teal\">Abilit&agrave; inserita correttamente</h3>";
					}
					else if(!$personal){
						$body .= "<h3 style=\"color: red\">Errore: id gi&agrave; in uso</h3>";
					}
					else {	
						$body .= "<h3 style=\"color: red\">Errore: utente non esistente</h3>";
					}
				}
				else {
					$body .= "<h3 style=\"color: red\">Errore id non valido: deve contenere SOLO lettere minuscole</h3>";
				}

			}
			else{
				$body .= "<h3 style=\"color: red\">Errore: parametri mancanti</h3>";
			}
		}

		if(isset($_GET['delete'])){
			$query = "DELETE FROM {$prefix}ability WHERE id='$_GET[delete]'";
			$db->DoQuery($query);

			$query = "DELETE FROM {$prefix}ability WHERE dep='$_GET[delete]'";
			$db->DoQuery($query);

			$query = "DELETE FROM {$prefix}userability WHERE ability_id='$_GET[delete]'";
			$db->DoQuery($query);
		}

		if(isset($_GET['del_feat'])) {
			$db->DoQuery("DELETE FROM {$prefix}features WHERE id = '{$_GET['del_feat']}'");
			$db->DoQuery("DELETE FROM {$prefix}user_feat WHERE feat_id = '{$_GET['del_feat']}'");
		}

		if(isset($_POST['new_feature_id'])) {
			$first_lvl = isset($_POST['first_lvl']);
			$cumulative = isset($_POST['cumulative']);
			$query = $db->DoQuery("SELECT COUNT(*) AS cnt FROM ${prefix}features 
					WHERE id = '{$_POST['new_feature_id']}'");
			$row = $db->Do_Fetch_Assoc($query);
			if ($row['cnt'] > 0) {
				$db->DoQuery("UPDATE ${prefix}features SET 
						descr = '{$_POST['feature_desc']}',
						first_lvl = '$first_lvl',
						cumulative = '$cumulative'
						WHERE id = '{$_POST['new_feature_id']}'");
			} else {
				$db->DoQuery("INSERT INTO ${prefix}features 
						(feat_id, descr, first_lvl, cumulative)
						VALUES ('{$_POST['new_feature_id']}', '{$_POST['feature_desc']}',
							'$first_lvl', '$cumulative')");
			}
		}

		if(!isset($_GET['group']))
			$_GET['group'] = $x7c->settings['usergroup_default'];

		$body .= "<div style=\"text-align: center\">
			<form>Seleziona la gremios:
			<select onChange=\"location='index.php?act=adminpanel&cp_page=abilities&group='+this.options[this.selectedIndex].value\">\n";

		$query = "SELECT usergroup FROM {$prefix}permissions WHERE gremios='1'
			ORDER BY usergroup";
		$result = $db->DoQuery($query);
		$usergroup_list = array();

		while($row = $db->Do_Fetch_Assoc($result)){
			$usergroup_list[] = $row['usergroup'];
			$selected = "";
			if($_GET['group'] == $row['usergroup'])
				$selected = "SELECTED";
			$body .= "<option value=\"$row[usergroup]\" $selected>$row[usergroup]</option>\n";
		}

		$selected = "";
		if($_GET['group'] == "_personal")
			$selected = "SELECTED";
		$body .= "<option value=\"_personal\" $selected>Ad personam</option>
			</select></form></div>";

		$body .= '<script language="javascript" type="text/javascript">
			function do_delete(id){
				if(!confirm(\'Attenzione!!! Se cancelli una abilit&agrave; tutti i PG la perderanno irreversibilmente.\n Vuoi proseguire?\'))
					return;
				window.location.href=\'index.php?act=adminpanel&cp_page=abilities&group='.$_GET['group'].'&delete=\'+id;
			}

		function show_personal(value){
			if(value=="_personal"){
				document.getElementById("personal").style.visibility = "visible";
			}
			else{
				document.getElementById("personal").style.visibility = "hidden";
			}
		}

		function show_new_feat(value){
			if(value!="_new"){
				window.location.href=\'index.php?act=adminpanel&cp_page=abilities&mod_feat=\' + value;
			}
			else{
				window.location.href=\'index.php?act=adminpanel&cp_page=abilities\';
			}
		}
		</script>';


		$corp = '';
		if($_GET['group'] != $x7c->settings['usergroup_default'])
			$corp = $_GET['group'];


		$view_personal = ($_GET['group']=="_personal");

		if(!$view_personal)
			$query = "SELECT * FROM {$prefix}ability WHERE corp='$corp'ORDER BY name";
		else
			$query = "SELECT * FROM {$prefix}ability ab,
		{$prefix}userability ua
		WHERE ab.id = ua.ability_id
			AND ab.corp = '$corp'

			ORDER BY name";

		$result = $db->DoQuery($query);


		$personal_col = "";
		if($view_personal)
			$personal_col = "<td class=\"col_header\">Utente</td>";

		$body .="<table class=\"inner_table\" width=100%>
			<tr>	<td class=\"col_header\">ID</td>
			<td class=\"col_header\">Nome</td>
			<td class=\"col_header\">Ab. primaria</td>
			<td class=\"col_header\">Car. associata</td>
			$personal_col
			<td></td></tr>";


		while($row = $db->Do_Fetch_Assoc($result)){
			$personal_col = "";
			if($view_personal)
				$personal_col = "<td class=\"dark_row\">$row[username]</td>";
			$body .= "<tr>
				<td class=\"dark_row\">$row[id]</td>
				<td class=\"dark_row\">$row[name]</td>
				<td class=\"dark_row\">$row[dep]</td>
				<td class=\"dark_row\">$row[char]</td>
				$personal_col";

			// It is too dangerous allowing deletion of default abilities
			if($_GET['group'] != $x7c->settings['usergroup_default'])
				$body .="<td class=\"dark_row\">
					<a href=\"#\" onClick=\"javascript: do_delete('$row[id]');\">[Elimina]</a></td>";

			$body .= "</tr>";
		}

		$body .= "</table>";



		$body .= "<h3>Inserisci una nuova abilit&agrave</h3>
			<form action=\"index.php?act=adminpanel&cp_page=abilities\" method=\"post\">";

		$body .= "<table>
			<tr>
			<td>ID (deve essere univoco <br>e di sole lettere)</td>
			<td><input type=\"text\" name=\"id\"></td>

			</tr>
			<tr>
			<td>Nome abilita</td>
			<td><input type=\"text\" name=\"name\"></td>

			</tr>
			<tr>
			<td>Caratteristica associata</td>
			<td><select name=\"char\">";

		foreach($char_list as $i => $name){
			$body .= "<option value=\"$i\">$name</option>\n";
		}

		$body .= "</select></td>

			</tr>
			<tr>
			<td>Abilit&agrave; primaria</td>
			<td><select name=\"dep\">
			<option value=\"\">Nessuna</option>";

		foreach($ability_list as $i => $name){
			$body .= "<option value=\"$i\">$name</option>\n";
		}

		$body .= "</select></td>
			</tr>
			<tr>
			<td>Gremios</td>
			<td><select name=\"gremios\" onChange=\"show_personal(this.value)\">";

		foreach($usergroup_list as $i){
			$selected = "";
			if($_GET['group'] == $i)
				$selected = "SELECTED";
			$body .= "<option value=\"$i\" $selected>$i</option>\n";
		}

		$selected = "";
		$visibility = "hidden";
		if($_GET['group'] == "_personal"){
			$selected = "SELECTED";
			$visibility = "visible";
		}

		$body .= "<option value=\"_personal\" $selected>Ad personam</option>
			</select></td>
			</tr>
			<tr id=\"personal\" style=\"visibility: $visibility;\">
			<td>Utente:</td>
			<td><input type=\"text\" name=\"username\"></td>
			</tr>
			<tr><td><input type=\"submit\" value=\"Inserisci\"></td></tr>";

		$body .= "</table></form>";


		$body .= "<h3>Inserisci modifica un talento</h3>
			<form action=\"index.php?act=adminpanel&cp_page=abilities\" method=\"post\">";

		$body .= "<table>
			<tr>
			<td><select name=\"feature_id\" onChange=\"show_new_feat(this.value)\">
		  <option value=\"_new\">Nuovo talento...</option>";

		$query = $db->DoQuery("SELECT id,feat_id FROM {$prefix}features ORDER BY feat_id");
		while ($row = $db->Do_Fetch_Assoc($query)) {
			$selected = "";
			if (isset($_GET['mod_feat']) && $_GET['mod_feat'] == $row['id'])
				$selected = "selected=\"selected\"";
			$body .= "<option value=\"{$row['id']}\" $selected>$row[feat_id]</option>";
		}

		$new_feat_show = 'visible';
		$desc="";
		$delete_act = "";
		$first_lvl = "";
		$cumulative = "";
		if (isset($_GET['mod_feat'])) {
			$new_feat_show = 'hidden';
			$query_select = $db->DoQuery("SELECT descr, first_lvl, cumulative
					FROM {$prefix}features
					WHERE id = '{$_GET['mod_feat']}'");
			$row_select = $db->Do_Fetch_Assoc($query_select);
			$desc = $row_select['descr'];
			if ($row_select['first_lvl'])
				$first_lvl = "checked";
			if ($row_select['cumulative'])
				$cumulative = "checked";
			$delete_act = "window.location.href='index.php?act=adminpanel&cp_page=abilities&del_feat=".$_GET['mod_feat']."'";
		} else {
			$_GET['mod_feat'] = "";
		}

		$body .= "</select>
      </td>
			</tr>

			<tr><td>
			<input type=\"text\" name=\"new_feature_id\"
			style=\"visibility: $new_feat_show\" value=\"".$_GET['mod_feat']."\"></td>
			</tr>
			<tr>
			<td>Descrizione:</td>
			<td><textarea name=\"feature_desc\" style=\"height: 200\">$desc</textarea></td>
			</tr>
			<tr><td>Primo livello:</td>
			<td><input type=\"checkbox\" name=\"first_lvl\" $first_lvl></td></tr>
			<tr><td>Cumulativo:</td>
			<td><input type=\"checkbox\" name=\"cumulative\" $cumulative></td></tr>
			<tr><td><input type=\"submit\" value=\"Inserisci/Modifica\"></td></tr>";

		if ($delete_act)
			$body .="<tr><td><input type=\"button\" value=\"Cancella\"
				onClick=\"$delete_act\"></td></tr>";

		$body .= "</table></form>";

	}elseif($_GET['cp_page'] == "hints"){
		$head = "Gestione hints del master";
		$body = "";
		$limit = 0;
		if (isset($_GET['startfrom']))
			$limit = $_GET['startfrom'];


		if (isset($_GET['edit'])){
			if (isset($_POST['text'])) {
				$query = $db->DoQuery("SELECT * FROM {$prefix}hints WHERE id='{$_GET['edit']}'");
				$row = $db->Do_Fetch_Assoc($query);

				$_POST['text'] = preg_replace("/\n/", "<br>", $_POST['text']);
				$url_regexp = "/http(s)?:\/\/[^[:space:]]+/i";
				$_POST['text'] = preg_replace($url_regexp, 
						'<a href="\\0" target="_blank">\\0</a>', $_POST['text']);

				if ($row) {
					$db->DoQuery("UPDATE {$prefix}hints SET text='{$_POST['text']}',
							type = '{$_POST['type']}'
							WHERE id='{$row['id']}'");
				}
				else {
					$db->DoQuery("INSERT INTO {$prefix}hints 
							(text, type) VALUES ('{$_POST['text']}',
								'{$_POST['type']}')");
				}

			header("location: index.php?act=adminpanel&cp_page=hints&startfrom=$limit");
			}

			$hint = "";
			$query = $db->DoQuery("SELECT * FROM {$prefix}hints 
					WHERE id={$_GET['edit']}");

			$row = $db->Do_Fetch_Assoc($query);
			if ($row)
				$hint = $row['text'];
				
			$hint = preg_replace("/<br>/", "\n", $hint);
			$url_regexp = "/<a[^>]*>|<\/a>/i";
			$hint = preg_replace($url_regexp, "", $hint);

			$body .= '<form action="index.php?act=adminpanel&cp_page=hints&edit='.
				$_GET['edit'].'&startfrom='.$limit.'"	method="post">';

			$body .= "<textarea name=\"text\" class=\"text_input\" 
				cols=\"80\" rows=\"20\">$hint</textarea><br>";

			$selected_aya = '';
			$selected_player = '';
			if ($row['type'] == 'aya')
				$selected_aya = 'selected';
			if ($row['type'] == 'player')
				$selected_player = 'selected';

			$body .= '<input type="submit" value="Invia" class="button">
				<select name="type">
				  <option value="aya" '.$selected_aya.'>Aya</option>
				  <option value="player" '.$selected_player.'>Player</option>
				</input>
				</form>';
		}
		else if(isset($_GET['delete'])) {
			$db->DoQuery("DELETE FROM {$prefix}hints WHERE id='{$_GET['delete']}'");	
			header("location: index.php?act=adminpanel&cp_page=hints&startfrom=$limit");
		}
		else {
			$maxmsg=10;
			$navigator='';

			$query = $db->DoQuery("SELECT count(*) AS total FROM {$prefix}hints");
			$row = $db->Do_Fetch_Assoc($query);
			$total = $row['total'];

			if($total > $maxmsg){
				$i=0;
				while($total > 0){
					$navigator .= "<a href=\"index.php?act=adminpanel&cp_page=hints".
						"&startfrom=$i\">";
					if((isset($_GET['startfrom']) && $_GET['startfrom'] == $i) || 
							(!isset($_GET['startfrom']) && $i == 0))
						$navigator .= "<b>[".($i+1)."]</b>";
					else
						$navigator .= $i+1;

					$navigator .= "</a> ";
					$i++;
					$total -= $maxmsg;

				}
			}
			$navigator.="<br>";

			$limit_min = $limit * $maxmsg;
			$limit_max = $maxmsg;
			$query = $db->DoQuery("SELECT *	FROM {$prefix}hints
					ORDER BY id LIMIT $limit_min, $maxmsg");

			$body .= '<p style="text-align: center;"><a href="index.php?act=adminpanel&cp_page=hints&edit=-1">
				Aggiungi nuovo</a><br>';
			$body .= $navigator."</p>";
			$body .= '<table width="95%" align="center" border="0" cellspacing="0"'.
				' cellpadding="0" class="col_header">
				<tr>
				<td width="5%">Id</td><td>Hint</td><td width="20%">Tipo</td><td width="20%">Azioni</td>
				</tr>
				</table>';

			$body .= '<table width="95%" align="center" border="0" cellspacing="0"'.
				' cellpadding="0" class="inside_table">';

			while ($row = $db->Do_Fetch_Assoc($query)) {
				$body .= "<tr>
					<td width=\"5%\">$row[id]</td>
					<td>$row[text]</td>
					<td width=\"20%\">
					$row[type]
					</td>
					<td width=\"20%\">
					<a href=\"index.php?act=adminpanel&cp_page=hints&edit=$row[id]&startfrom=$limit\">
					[Edit]
					</a>
					<a href=\"index.php?act=adminpanel&cp_page=hints&delete=$row[id]&startfrom=$limit\">
					[Delete]
					</a>
					</td>
					<tr><td colspan=\"3\"><hr></td></tr>
					</tr>";	
			}

			$body .= '</table>';
			$body .= "<p style=\"text-align: center;\">".$navigator;
			$body .= '<a href="index.php?act=adminpanel&cp_page=hints&edit=-1">
				Aggiungi nuovo</a></p>';
		}
	}elseif($_GET['cp_page'] == "ad"){
		// A permission denied error occured, Don't show admin menu, only the error
		$head = $txt[14];
		$cbody = $txt[216];
		$perm_error = 1;
	}elseif($_GET['cp_page'] == "ad2"){
		// A permission denied error occured, but this user is an admin so show them the menu anyway
		$head = $txt[14];
		$body = $txt[216];
	}

	if(@$perm_error != 1){

		// THis mini-function helps by checking permissions and printing links
		function printlink($id,$txt){
			global $x7c;

			// See if they have access to this section
			$check_page = $id;
			if($check_page == "groupmanager")
				$check_page = "groups";
			if($x7c->permissions["admin_{$check_page}"] == 0){
				return "";

			}else{

				if($_GET['cp_page'] == $id)
					return "<tr>
						<td class=\"ucp_sell\">$txt</td>
						</tr>";
				else
					return  "<tr>
						<td class=\"ucp_cell\" onMouseOver=\"javascript: this.className='ucp_sell'\" onMouseOut=\"javascript: this.className='ucp_cell'\"  onClick=\"javascript: window.location='./index.php?act=adminpanel&cp_page=$id'\">$txt</td>
						</tr>";
			}

		}

		// Add the menu to the body
		$cbody = "<div align=\"center\">
			<table border=\"0\" width=\"95%\" class=\"ucp_table\" cellspacing=\"0\" cellpadding=\"0\">
			<tr valign=\"top\">
			<td width=\"20%\" height=\"100%\">
			<table width=\"100%\" class=\"ucp_table2\" height=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
			".printlink("main",$txt[137])."
			".printlink("settings","Settaggi server")."
			".printlink("abilities", "Abilit&agrave;/Talenti")."
			".printlink("groupmanager","Gruppi/Gremios")."
			".printlink("users","Utenti")."
			".printlink("ban","Ban")."
			".printlink("rooms","Stanze")."
			".printlink("logs","Registrazioni stanze")."
			".printlink("mail",$txt[316])."
			".printlink("panic","Oscurit&agrave;, multi-kill")."
			".printlink("alarms","Allarmi")."
			".printlink("objects","Oggetti")."
			".printlink("money","Soldi")."
			".printlink("hints","Hint del master")."
			<tr valign=\"top\">
			<td class=\"ucp_cell\" style=\"cursor: default;\" height=\"100%\"><Br><a href=\"#\" onClick=\"javascript: window.close();\">[$txt[133]]</a><Br><Br></td>
			</tr>
			</table>
			</td>
			<Td width=\"5\" class=\"ucp_divider\">&nbsp;</td>
			<td class=\"ucp_bodycell\">$body</td>
			</tr>
			</table>
			</div>";
	}

	$print->normal_window($head,$print->ss_ucp.$cbody);

}

// I almost called this function wreck_hell() because its used so much
function update_setting($setting,$newval){
	global $prefix, $db;
	$db->DoQuery("UPDATE {$prefix}settings SET setting='$newval' WHERE variable='$setting'");
}

?>
