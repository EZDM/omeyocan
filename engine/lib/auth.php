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

	// For some odd reason I decided to define this here:
	$g_default_settings = "default;default;{$x7c->settings['cookie_time']};default;default;default;0;0;0;0;5000;default;default;0;0";

	// This class handles authentication
	class session {
		var $loggedin;		// 1 if logged in, 0 if not, 2 if incorrect password
		var $username;		// Holds the person's official username
		var $sheet_ok;		// Holds if the person have build is character
		var $user_group;	// Holds the group which the user belongs to
		var $reg_date;		//Holds registration date of user
		var $second_mod;	//Hold if user has modificed sheet more than 1 time
		var $panic;
		var $max_panic;
		var $talk;
		var $resurgo;
		var $status;
		var $invisible;
		
		// Create a new session
		function session(){
			global $X7CHAT_CONFIG,$db,$auth_ucookie,$auth_pcookie,$prefix,$ACTIVATION_ERROR,$FROZEN_ERROR;
			
			// Set username to null by default
			$this->username = "";
			
			if(@$_COOKIE[$auth_ucookie] != "" && @$_COOKIE[$auth_pcookie] != "" ){
			
				// The user has a cookie set for username
				if($_COOKIE[$auth_pcookie] == auth_getpass($auth_ucookie)){ 
					if(!isset($ACTIVATION_ERROR) && !isset($FROZEN_ERROR)){
						$this->loggedin = 1;
						$this->username = $_COOKIE[$auth_ucookie];
						$db->DoQuery("UPDATE {$prefix}users SET ip='{$_SERVER['REMOTE_ADDR']}'");
					}
					
					if(isset($ACTIVATION_ERROR))
						$this->loggedin = 4;	
				
					if(isset($FROZEN_ERROR))
						$this->loggedin = 5;
					
				}else{
					$this->loggedin = 2;
				}				
					
			}else{
				// This user is NOT logged in
				$this->loggedin = 0;
			}
		}
		
		function dologin(){
			global $X7CHAT_CONFIG,$db,$auth_ucookie,$auth_pcookie,$x7c,$x7s,$prefix,$g_default_settings,$remove_old_guest_logs,$txt,$ACTIVATION_ERROR,$FROZEN_ERROR;
			
			// The AuthMod file has already been included above
			
			// Put test values into the cookie
			$_COOKIE["$auth_ucookie"] = $_POST['username'];
			$_POST['password'] = auth_encrypt($_POST['password']);
			$_COOKIE["$auth_pcookie"] = $_POST['password'];
			
			// A temporary sessions to check password
			$temp = new session();
			
			if($temp->loggedin == 1){
				$un = parse_outgoing($_POST['username']);
				$pw = parse_outgoing($_POST['password']);
				setcookie($auth_ucookie,$un,0/*time()+$x7c->settings['cookie_time']*/,$X7CHAT_CONFIG['COOKIE_PATH']);
				setcookie($auth_pcookie,$pw,0/*time()+$x7c->settings['cookie_time']*/,$X7CHAT_CONFIG['COOKIE_PATH']);
				
				$x7s->loggedin = 1;
				$this->username = $_COOKIE[$auth_ucookie];
				return 1;
			}else{
			
				if($x7c->settings['allow_guests'] == 1){
					$query = $db->DoQuery("SELECT * FROM {$prefix}users WHERE username='$_POST[username]'");
					$row = $db->Do_Fetch_Row($query);
					if($row[0] == ""){
					
						// Make sure username is valid
						if(eregi("\.|'|,|;| ",$_POST['username']) || (strlen($_POST['username']) > $x7c->settings['maxchars_username'] && $x7c->settings['maxchars_username'] != 0)){
							$x7s->loggedin = 3;
							return 0;
						}
							
						// User may enter as a guest with this username
						$time = time();
						$ip = $_SERVER['REMOTE_ADDR'];
						$db->DoQuery("INSERT INTO {$prefix}users (id,username,password,status,user_group,time,settings,hideemail,ip,activated) VALUES('0','$_POST[username]','$_POST[password]','$txt[150]','{$x7c->settings['usergroup_guest']}','$time','{$g_default_settings}','0','$ip','1')");
						
						// Remove old logs
						$remove_old_guest_logs = 1;
						
						// Give them nice cookies with chocolate chips
						$un = parse_outgoing($_POST['username']);
						$pw = parse_outgoing($_POST['password']);
						setcookie($auth_ucookie,$un,0/*time()+$x7c->settings['cookie_time']*/,$X7CHAT_CONFIG['COOKIE_PATH']);
						setcookie($auth_pcookie,$pw,0/*time()+$x7c->settings['cookie_time']*/,$X7CHAT_CONFIG['COOKIE_PATH']);
						$x7s->loggedin = 1;
						$this->username = $_COOKIE[$auth_ucookie];
						return 1;
					}
				}
				
				if($temp->loggedin == 2){
					$x7s->loggedin = 2;
					setcookie($auth_ucookie,"",0/*time()-$x7c->settings['cookie_time']-63000000*/,$X7CHAT_CONFIG['COOKIE_PATH']);
					setcookie($auth_pcookie,"",0/*time()-$x7c->settings['cookie_time']-63000000*/,$X7CHAT_CONFIG['COOKIE_PATH']);
					return 0;
				}elseif($temp->loggedin == 5){
					$x7s->loggedin = 5;
					setcookie($auth_ucookie,"",0/*time()-$x7c->settings['cookie_time']-63000000*/,$X7CHAT_CONFIG['COOKIE_PATH']);
					setcookie($auth_pcookie,"",0/*time()-$x7c->settings['cookie_time']-63000000*/,$X7CHAT_CONFIG['COOKIE_PATH']);
					return 0;
				}
				elseif($temp->loggedin == 4){
					$x7s->loggedin = 4;
					return 0;
				}
			
			}
			
		}
	
	}

?>

