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

	// This file handles all, I mean most, things room related
	
	// This function returns a list of all rooms
	function list_rooms(){
		global $db,$prefix;
		
		// Get the rooms from the database
		$return = array();
		$query = $db->DoQuery("SELECT name,topic,password,maxusers,logged,long_name FROM {$prefix}rooms WHERE type='1' ORDER BY long_name");
		while($row = $db->Do_Fetch_Row($query)){
			$return[] = $row;
		}
		return $return;
	}
	
	// This function creates a new room
	function create_room($uid,$name,$type,$moded,$topic,$greet,$pass,$max,$exp,$panic_free){
		global $prefix, $db;
		if($exp != 1)
			$time = time();
		else
			$time = 0;
		$ops = "$uid";
		$voice = "$uid";
		$db->DoQuery("INSERT INTO {$prefix}rooms VALUES('0','$name','$type','$moded','$topic','$greet','$pass','$max','$time','$ops','$voice','1','','','$panic_free','$name')");
		return 1;
	}
	
	// Takes an array with the following values in this order:
	// type, moderated, topic, greeting, password, max users, background image, logo image
	function mass_change_roomsettings($room,$new_settings){
		global $prefix, $db;
		$db->DoQuery("UPDATE {$prefix}rooms SET type='$new_settings[0]',moderated='$new_settings[1]',topic='$new_settings[2]',greeting='$new_settings[3]',password='$new_settings[4]',maxusers='$new_settings[5]',background='$new_settings[6]',logo='$new_settings[7]', panic_free='$new_settings[8]', long_name='$new_settings[9]' WHERE name='$room'");
	}
	
	// Changes a single setting (used mostly for IRC cmds I think)
	function change_roomsetting($room,$setting,$new_setting){
		global $prefix, $db;
		$db->DoQuery("UPDATE {$prefix}rooms SET $setting='$new_setting' WHERE name='$room'");
	}

?> 
