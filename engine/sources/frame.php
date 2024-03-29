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
//    X7 Chat Version 2.0.5
//    Released Jan 6, 2007
//    Copyright (c) 2004-2006 By the X7 Group
//    Website: http://www.x7chat.com
//
//    This program is free software.  You may
//    modify and/or redistribute it under the
//    terms of the included license as written
//    and published by the X7 Group.
//
//    By using this software you agree to the
//    terms and conditions set forth in the
//    enclosed file "license.txt".  If you did
//    not recieve the file "license.txt" please
//    visit our website and obtain an official
//    copy of X7 Chat.
//
//    Removing this copyright and/or any other
//    X7 Group or X7 Chat copyright from any
//    of the files included in this distribution
//    is forbidden and doing so will terminate
//    your right to use this software.
//
////////////////////////////////////////////////////////////////EOH
?>
<?PHP

  // changing this to false will display all previous messages upon entering a
  // chat room
  $x7c->settings['use_old_sessionmsg_mode'] = false;

  // changing this to true will cause enter messages for every other user
  // currently in the room to be shown to the user who is entering
  $x7c->settings['use_old_sessionentermsg_mode'] = false;

  $x7c->settings['show_enter_message'] = false;

  if(!isset($_GET['frame']))
	  $_GET['frame'] = 'main';


  if(!isset($_GET['room'])) {
		header("Location: index.php?errore=noroom");
		return;
	}

  if($_GET['room'] == "Mappa" && !isset($_GET['frame'])) {
		header("Location: index.php?errore=noroom");
		return;
	}

  if(isset($_GET['delete']))
  if($x7c->permissions['admin_panic']){
    include_once("./lib/message.php");
    if($_GET['delete']!="all"){
      $db->DoQuery("DELETE FROM {$prefix}messages WHERE id='{$_GET['delete']}'");
      delete_communication($_GET['delete'],$_GET['room']);
    }
    else{
      $db->DoQuery("DELETE FROM {$prefix}messages
          WHERE room='{$_GET['room']}' AND type<>'6'");
      delete_communication('all',$_GET['room']);
    }
    return;
  }

  //We check if we are entering a private room
  $query = $db->DoQuery("SELECT type FROM {$prefix}rooms
      WHERE name='$_GET[room]'");
  $row = $db->Do_Fetch_Assoc($query);

	// The room does not exist
	if (!$row) {
		header("Location: index.php?errore=noroom");
		return;
	}

  //If it is private
  if($row['type'] == 2) {
    if($x7s->username != $_GET['room'] && !$x7c->permissions['admin_panic']){
      //We are not the owner of the room or the master..
      //To enter we must own the keyCode
      $univoque_key='';
      if(isset($_GET['key_used'])){
        $univoque_key = " AND id='$_GET[key_used]'";
      }

      $query = $db->DoQuery("SELECT * FROM {$prefix}objects WHERE
          owner = '{$x7s->username}' AND
          (name = 'key_$_GET[room]' OR name = 'masterkey_$_GET[room]')
          $univoque_key
          ");

      $ok=false;
      while(($row = $db->Do_Fetch_Assoc($query))!=null && !$ok){
        $ok = true;
        if($univoque_key=='')
          $_GET['key_used'] = $row['id'];

        $remain = -1;      
      
				if(!isset($_GET['frame']) || ($_GET['frame']!="update" && $_GET['frame']!="send")){
					if($row['uses'] > 0) {
						//If we have a limited access to the room whe must update the use 
						// of the keyCode
						$remain = $row['uses'] - 1;
						$db->DoQuery("UPDATE {$prefix}objects
								SET uses=$remain
								WHERE
								owner = '{$x7s->username}' AND
								name = 'key_$_GET[room]' AND
								id = '$_GET[key_used]'
								");
					} else if ($row['uses'] == 0) {
						$db->DoQuery("DELETE FROM {$prefix}objects
								WHERE
								owner = '{$x7s->username}' AND
								name = 'key_$_GET[room]' AND
								id = '$_GET[key_used]'
								");
					}
					include_once('./lib/alarms.php');
					object_usage($x7s->username, $_GET['key_used'], $remain);
				}
			}
      if(!$ok){
				if(!isset($_GET['frame']) || ($_GET['frame']!="update" && $_GET['frame']!="send")){
        //We do not own a valid key
        header("Location: index.php?errore=nokey");
        return;
				}
				else {
					echo("9;Non hai la chiave per questa stanza;index.php");
					return;
				}
      }

    }
  }

  switch($_GET['frame']){
    case "invisibility":
      header("Content-type: text/plain; charset=iso-8859-1");
      header("Cache-Control: no-cache");
      header("Expires: Thu, 1 Jan 1970 0:00:00 GMT");

      if($x7c->permissions['admin_panic']){
        $query=$db->DoQuery("SELECT m_invisible FROM {$prefix}users
            WHERE username='{$x7s->username}'");
        $row=$db->Do_Fetch_Assoc($query);

        if(!$row)
          die('Invisibility: Inconsistenza');

        $new_invisible=!$row['m_invisible'];

        $db->DoQuery("UPDATE {$prefix}users SET m_invisible='$new_invisible'
            WHERE username='{$x7s->username}'");
      }
      break;


    case "shadow":
      header("Content-type: text/plain; charset=iso-8859-1");
      header("Cache-Control: no-cache");
      header("Expires: Thu, 1 Jan 1970 0:00:00 GMT");

      if(!isset($_GET['room']))
        exit();

      if($x7c->permissions['admin_panic']){
        $query=$db->DoQuery("SELECT shadow FROM {$prefix}rooms
            WHERE name='{$_GET['room']}'");
        $row=$db->Do_Fetch_Assoc($query);

        if(!$row)
          exit();

        $new_shadow=!$row['shadow'];

        $db->DoQuery("UPDATE {$prefix}rooms SET shadow='$new_shadow'
            WHERE name='{$_GET['room']}'");
      }
      break;

    case "update":
      // Make sure they are not trying to cache this page
      header("Content-type: text/plain; charset=iso-8859-1");
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

      $query = $db->DoQuery("SELECT position FROM {$prefix}users
          WHERE username='$x7s->username'");
      $row = $db->Do_Fetch_Assoc($query);

      if($row['position'] != $_GET['room'] && $_GET['startfrom']>0){
        include_once('./lib/alarms.php');
        double_login($_GET['room']);
        die("9;Non puoi aprire due finestre contemporaneamente;index.php");
      }

      // See if the room is being loaded for the first time (create a new session)
      if($_GET['startfrom'] == 0){
        $db->DoQuery("DELETE FROM {$prefix}online
            WHERE name='$x7s->username' AND room='$_GET[room]'");

        $endon = -1;

        $x7c->room_data['greeting'] = preg_replace("/@/",
            "74ce61f75c75b155ea7280778d6e8183",$x7c->room_data['greeting']);
        $x7c->room_data['greeting'] = preg_replace("/\|/",
            "74ce61f75c75b155ea7280778d6e8181",$x7c->room_data['greeting']);
        $x7c->room_data['greeting'] = preg_replace("/;/",
            "74ce61f75c75b155ea7280778d6e8182",$x7c->room_data['greeting']);
        $x7c->room_data['greeting'] = eregi_replace("'","\\'",
            $x7c->room_data['greeting']);
      }

      function format_timestamp($time){
        global $x7c;
        $time = $time+(($x7c->settings['time_offset_hours']*3600)+
            ($x7c->settings['time_offset_mins']*60));
        return date("[".$x7c->settings['date_format']."]",$time);
      }

      // Are you allowed to see invisible users?
      if($x7c->permissions['c_invisible'] == 1)
        $invis = "";
      else
        $invis = "AND o.invisible<>'1' ";

      // Force online_time to be above the max refresh rate
      if($x7c->settings['online_time']*1000 < $x7c->settings['max_refresh'])
        $x7c->settings['online_time'] = 
          ceil($x7c->settings['max_refresh']/1000)+5;

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

      $query = $db->DoQuery("SELECT o.*,u.id
          FROM {$prefix}online o, {$prefix}users u
          WHERE o.room='$_GET[room]' 
          AND u.username=o.name {$invis}ORDER BY o.name ASC");

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
          if($row[1] != '' && $x7c->settings['show_enter_message'] == true && 
              ($x7c->settings['use_old_sessionentermsg_mode'] == true ||
               $_GET['startfrom'] != 0))
            echo utf8_encode("8;" . preg_replace("/;/",
                  "74ce61f75c75b155ea7280778d6e8182",
                  "<span style=\"color: {$x7c->settings['system_message_color']};
                  font-size: {$x7c->settings['sys_default_size']}; font-family:
                  {$x7c->settings['sys_default_font']};\"><b>$row[1] $txt[43]</b>
                  </span><Br>") . "|");
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
        $db->DoQuery("DELETE FROM {$prefix}online
            WHERE name='$x7s->username' AND room<>'{$_GET['room']}'");
        $db->DoQuery("INSERT INTO {$prefix}online
            VALUES('0','$x7s->username','$ip','$_GET[room]','','$time',
              '{$x7c->settings['auto_inv']}')");
        $db->DoQuery("UPDATE {$prefix}users 
            SET position='{$_GET['room']}' WHERE username='$x7s->username'");

      }else{
        // Update an old record
        $time = time();
        $db->DoQuery("UPDATE {$prefix}online SET time='$time'
            WHERE name='$x7s->username' AND room='$_GET[room]'");
      }

      // Handle leave messages
      foreach($_GET['listhash'] as $key=>$val){
        if($val != ''){
          if($x7c->settings['show_enter_message'] == true)
            echo utf8_encode("8;" . preg_replace("/;/",
                  "74ce61f75c75b155ea7280778d6e8182","<span style=\"
                  color: {$x7c->settings['system_message_color']};
                  font-size: {$x7c->settings['sys_default_size']}; 
                  font-family: {$x7c->settings['sys_default_font']};\">
                  <b>$qitu[$val] $txt[44]</b></span><Br>") . "|");
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

      $query = $db->DoQuery("SELECT user,type,body_parsed,m.time,m.id,room
          FROM {$prefix}messages m WHERE
          (m.id>'$_GET[startfrom]'
           AND (
             (room='$_GET[room]' AND (type='1' OR type='4' OR type='14')) OR
             (room='$x7s->username' AND type='3') OR
             (type='2') OR
             (room='$x7s->username:0' AND type='5' AND m.time<$pm_time) OR
             (room='$_GET[room]' AND type='10') OR
             (room='$x7s->username' AND (type='11' OR type='12' OR type='13'))
             )
          )
          OR (room='$x7s->username' AND type='6' AND m.time='0')
          ORDER BY m.id ASC");

      if($db->error == 4){
        $query = eregi_replace("'","\\'",$query);
        $query = eregi_replace("[\n\r]","",$query);
        echo "9;;./index.php?act=panic&dump=$query&source=/sources/frame.php:155";
      }

      $query_gender = $db->DoQuery("
          SELECT DISTINCT gender, bio, username, name, corp_charge
          FROM {$prefix}users, {$prefix}messages 
          WHERE username = user 
	  AND room='$_GET[room]'");

      $genders='';
      $gifs='';
      $long_names='';
      $charges='';
      while($row = $db->Do_Fetch_Assoc($query_gender)){
        $genders[strtolower($row['username'])]=$row['gender'];
        $gifs[strtolower($row['username'])]=$row['bio'];
        $long_names[strtolower($row['username'])]=$row['name'];
        $charges[strtolower($row['username'])]=$row['corp_charge'];
      }
      while($row = $db->Do_Fetch_Row($query)){

        if($row[1]!=6)
          $endon = $row[4];

        if($x7c->settings['use_old_sessionmsg_mode'] && $_GET['startfrom'] == 0)
          continue;

        if(!in_array($row[0],$x7c->profile['ignored'])){
          if(isset($toout))
            unset($toout);

          if($row[1] == 1){
            // See if they want a timestamp
            if($x7c->settings['disble_timestamp'] != 1)
              $timestamp = format_timestamp($row[3]);
            else
              $timestamp = "";

            $gender='';
            if(isset($genders[strtolower($row[0])])){
              if($genders[strtolower($row[0])] == 0)
                $gender='"male"';
              else
                $gender='"female"';
            }
            else
              $gender  = '"none> <span> Gender_undefined_error </span"';

            $long_name='';
            if(isset($long_names[strtolower($row[0])])){
              $long_name=$long_names[strtolower($row[0])];
            }
            else{
              $long_name = $row[0];
            }

            $gif="";
            if(isset($gifs[strtolower($row[0])]) &&
                $gifs[strtolower($row[0])]!=""){
              $gif="<img src=\"".$gifs[strtolower($row[0])]."\" title=\"".
                $charges[strtolower($row[0])].
                "\" style=\"vertical-align: middle\"> ";
            }

            $toout='';
            if($x7c->permissions['admin_panic']){
              $toout = "$gif<a onClick=\"".
								popup_open(500, 680, "index.php?act=sheet&pg=$row[0]",
										'sheet_other')."\">
                  <span class=$gender>$long_name ($row[0]) $timestamp:</span>
                </a>";
              $toout .= "<a onClick=\"javascript: do_delete($row[4])\">
                [Delete]</a>";
              $toout.="$row[2]<br>";
            }
            else{
              $toout = "$gif<a onClick=\"".
								popup_open(500, 680, "index.php?act=sheet&pg=$row[0]",
										'sheet_other')."\">
                <span class=$gender>$long_name $timestamp:</span></a>";

              if(!$x7c->room_data['shadow'] ||
                  strtolower($x7s->username) == strtolower($row[0]))
                $toout.="$row[2]<br>";
              else
                $toout.="<br><span class=\"chatmsg\">Ha agito</span><br>";
            }
          }elseif($row[1] == 2 || $row[1] == 3 || $row[1] == 4 ){
            $toout = "$row[2]";

          }elseif($row[1] == 6){
            $offline_msgs++;
          }elseif($row[1] == 5){
            $row[0] = preg_replace("/@/",
                "74ce61f75c75b155ea7280778d6e8183",$row[0]);
            $row[0] = preg_replace("/\|/",
                "74ce61f75c75b155ea7280778d6e8181",$row[0]);
            $row[0] = preg_replace("/;/",
                "74ce61f75c75b155ea7280778d6e8182",$row[0]);
            echo utf8_encode("7;$row[0]|");
            $db->DoQuery("UPDATE {$prefix}messages SET time='$pm_etime'
                WHERE id='$row[4]'");
          }elseif($row[1] == 10){
            $pos = strpos($row[2],':');
            $user = substr($row[2],0,$pos);
            $msg = substr($row[2],$pos+1);

            if($row[0] != $x7s->username){
              //Only master and final user can have this message
              if(strtolower($x7s->username) == strtolower($user)){
                $toout = "<span class=\"sussurro\">[$row[0]]
                  ti ha mandato un sussurro:".$msg."</span><br>";
              }
              elseif($x7c->permissions['admin_panic']){
                $toout = "<span class=\"sussurro\">[$row[0]]
                  ha mandato un sussurro a [$user]:".$msg."</span><br>";
              }
            }
            else{
              $toout = "<span class=\"sussurro\">Hai inviato un sussurro a 
                [$user]:".$msg."</span><br>";
            }
          }elseif($row[1] == 11){
            //This is a panic upate
            $db->DoQuery("DELETE FROM {$prefix}messages
                WHERE type='11' AND room='$x7s->username'");
            echo utf8_encode("11;$row[2]|");
          }elseif($row[1] == 12){
            //This is a refresher force
            $db->DoQuery("DELETE FROM {$prefix}messages
                WHERE type='12' AND room='$x7s->username'");
            echo utf8_encode("12;$row[2]|");
          }elseif($row[1] == 13){
            $db->DoQuery("DELETE FROM {$prefix}messages
                WHERE type='13' AND room='$x7s->username'");
            echo utf8_encode("13;$row[2]|");
          }elseif($row[1] == 14){
            $toout = "$row[2]";
            if($x7c->permissions['admin_panic']){
              $toout .= "<a onClick=\"javascript: do_delete($row[4])\">
                [Delete]</a>";
            }
            $toout.= "<br>";
          }

          if(isset($toout)){
            $toout = preg_replace("/\|/",
                "74ce61f75c75b155ea7280778d6e8181",$toout);
            $toout = preg_replace("/;/",
                "74ce61f75c75b155ea7280778d6e8182",$toout);
            echo utf8_encode("8;$toout<br>|");
          }

        }
      }

      echo utf8_encode("5;$endon|");
      echo utf8_encode("6;$offline_msgs|");

      // Check bans
      $bans = $x7p->bans_on_you;

      foreach($bans as $key=>$row){

        // If a row returned and they don't have immunity
        // then thrown them out the door and lock up
        if($row != "" && $x7c->permissions['ban_kick_imm'] != 1){
          if($row[1] == "*"){ 
            if($row[6] && $_GET['room'] != "Prigione"){
              echo utf8_encode("9;Sei stato arrestato;".
                  "./index.php?act=frame&room=Prigione|");            
            }elseif(!$row[6]){
              //They are banned from the server
              $txt[117] = eregi_replace("_r",$row[5],$txt[117]);
              echo utf8_encode("9;$txt[117];./index.php|");            
            }
          }elseif($row[1] == $x7c->room_data['id'] && $row[4] == 60){
            // They are kicked from this room
            $txt[115] = eregi_replace("_r",$row[5],$txt[115]);
            echo utf8_encode("9;$txt[115];./index.php?act=kicked|");
            $db->DoQuery("DELETE FROM {$prefix}online
                WHERE name='$x7s->username' AND room='$_GET[room]'");
          }elseif($row[1] == $x7c->room_data['id']){
            // They are banned from this room
            $txt[116] = eregi_replace("_r",$row[5],$txt[116]);
            echo utf8_encode("9;$txt[116];./index.php?act=kicked|");
            $db->DoQuery("DELETE FROM {$prefix}online
                WHERE name='$x7s->username' AND room='$_GET[room]'");
          }
        }
      }
      break;
    case "send":
      //Check if user is online in this room
      $query = $db->DoQuery("SELECT count(*) AS num FROM ${prefix}users 
          WHERE username='$x7s->username' AND position='$x7c->room_name'");
      $row = $db->Do_Fetch_Assoc($query);


      if($row['num'] == 0)
        break;

      // Mappa is a fake room... we exploit it for update offline
      // message and online status of users within mtha map
      if($_GET['room']=='Mappa')
        break;

      // Include the message library
      include_once("./lib/message.php");

      //Check if user can talk
      if(!$x7s->talk){
        alert_user($x7s->username,"Non puoi parlare");
        return;
      }

      // Make sure the message isn't null
      if(@$_POST['msg'] != "" &&
          !eregi("^@.*@",@$_POST['msg']) && !eregi("^\*",@$_POST['msg'])){

        if(strlen(trim($_POST['msg'])) < $x7c->settings['min_post']){
          alert_user($x7s->username,"Messaggio troppo corto");
          break;  
        }

        $tmp=preg_replace("/&[^;]+;/i" ,"",trim($_POST['msg']));
        if(strlen($tmp) > $x7c->settings['max_post']){
          alert_user($x7s->username,"Messaggio troppo lungo: ".
              strlen($tmp).">".$_POST['msg']."<");
          break;
        }

        //If we are in panic
        if($x7c->settings['panic']){
          //If user is not a master and room is not panic_free
          if(!$x7c->permissions['admin_panic'] &&
              !$x7c->room_data['panic_free']){
            if($x7s->panic >= $x7s->max_panic){
              $_POST['msg']=
                "<span style=\"color: red;\">Panico al massimo</span>
                <br>".$_POST['msg'];
            }
          }
        }

        $parsed_msg = 
          "<span class=\"locazione_display\">[".$_POST['locazione']."]".
          "</span><br>"." ".$_POST['msg'];

        send_message($parsed_msg,$x7c->room_name);

      }elseif(eregi("^@.*@",@$_POST['msg'])){

        $_POST['msg'] = eregi_replace("<","&lt;",$_POST['msg']);
        $_POST['msg'] = eregi_replace(">","&gt;",$_POST['msg']);
        $_POST['msg'] = eregi_replace("\n", " ",$_POST['msg']);
        $parsed_msg = $_POST['msg'];

        //if($x7c->permissions['room_voice'] == 1){
        send_message($parsed_msg,$x7c->room_name,1);

      }elseif(eregi("^\*",@$_POST['msg']) && $x7c->permissions['write_master']){
        $_POST['msg'] = eregi_replace("<","&lt;",$_POST['msg']);
        $_POST['msg'] = eregi_replace(">","&gt;",$_POST['msg']);
        $_POST['msg'] = eregi_replace("\n", " ",$_POST['msg']);
        $parsed_msg = $_POST['msg'];

        //Mastering message
        send_message($parsed_msg,$x7c->room_name,2);
      }

      break;
    default:
      //We must check if room is not full
      if($x7c->permissions['c_invisible'] == 1)
        $invis = "";
      else
        $invis = "AND o.invisible<>'1' ";

      $total = 0;
      $query = $db->DoQuery("SELECT count(*) AS num
          FROM {$prefix}online o, {$prefix}users u 
          WHERE o.room='$_GET[room]' 
          AND u.username=o.name {$invis}ORDER BY o.name ASC");

      $row = $db->Do_Fetch_Assoc($query);

      if($row['num'] >= $x7c->room_data['maxusers']) {
        header("Location: ./index.php?act=overload");
				return;
			}

      // Create a new record for you
      $time = time();
      $ip = $_SERVER['REMOTE_ADDR'];

      //Users can stay one chat per time, so we must delete them from other rooms
      $db->DoQuery("DELETE FROM {$prefix}online WHERE name='$x7s->username'");
      $db->DoQuery("INSERT INTO {$prefix}online
          VALUES('0','$x7s->username','$ip','$_GET[room]','','$time',
            '{$x7c->settings['auto_inv']}')");
      $db->DoQuery("UPDATE {$prefix}users SET position='{$_GET['room']}'
          WHERE username='$x7s->username'");


			// Set a random avatar
			if($x7c->room_data['hunt']) {
				$hunt_dir = '/images/random_img';
				$basedir=dirname($_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']);
				$hunt_uri=dirname($_SERVER['PHP_SELF']).$hunt_dir;
				$path = $basedir.$hunt_dir;

				if ($dh = opendir($path)) {
					while (($file = readdir($dh)) !== false) {
						if (filetype($path.'/'.$file) != 'dir') {
							$file_array[]=$file;
						}
					}
				  sort($file_array);

					// Reset at 4am.
					$stable_seed = date("Ymd", time() + 14400);
					// Use registration date as unique seed for the user
					srand($x7s->reg_date + $stable_seed);
					$avatar_index = rand(0, count($file_array) - 1);

					$hunt_avatar = $hunt_uri.'/'.$file_array[$avatar_index];
					$db->DoQuery("UPDATE {$prefix}users SET hunt_avatar = '$hunt_avatar'
							WHERE username = '{$x7s->username}'");

				}

				$parsed_msg = "* Hunter {$x7s->username} entered as &pound;$hunt_avatar;";
        include_once("./lib/message.php");
        send_message($parsed_msg,$x7c->room_name, 3);
			}
      //Here we start with HTML
      echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
      echo "<html dir=\"$print->direction\"><head>
        <title>{$x7c->settings['site_name']} Chat</title>";
			echo '<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />';
      echo $print->style_sheet;
      echo $print->ss_mini;
      echo $print->ss_chatinput;
      echo $print->ss_uc;

			include_once('./sources/graphic_elements.php');

?>

<LINK REL="SHORTCUT ICON"
HREF="./favicon.ico">
<script type="text/javascript" src="sources/AdvancedCountDown.js">
</script>

<script type="text/javascript">

var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-18911231-1']);
_gaq.push(['_trackPageview']);

(function() {
 var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
 ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
 var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
 })();

</script>

</head>
<body onload="javascript: do_initial_refresh();">
  <iframe style='position: absolute; visibility: hidden;'
    src="index.php?act=frame&frame=send&room=<?PHP echo $x7c->room_name; ?>"
    name="send" frameborder="0" scrolling="no" marginwidth="0"
    marginheight="0" noresize="true"></iframe>

<script language="javascript" type="text/javascript">
  listhash = '';
  startfrom = 0;
	first_sound = 0;
  newMail = 0;
  max_panic= <?PHP echo $x7s->max_panic;?>;
  cur_msg='';
  panic_value=<?PHP echo ($x7s->panic > 10 ? 10 : $x7s->panic); ?>

  var panic = new Array();
  panic[0]="Inquietudine"
  panic[1]="Tensione";
  panic[2]="Irrequitezza";
  panic[3]="Nervosismo";
  panic[4]="Agitazione";
  panic[5]="Ansia";
  panic[6]="Angoscia";
  panic[7]="Paura";
  panic[8]="Paranoia";
  panic[9]="Terrore";
  panic[10]="!!! Panico !!!";

  //This saves from enter key when confirming an alert box
  function Alert(msg){
    if(cur_msg==''){
      cur_msg=document.chatIn.msgi.value;
    }
    alert(msg);
  }


  function do_initial_refresh(){
    // Create object
    chatRefresh = setInterval('do_refresh()','<?PHP echo $x7c->settings['refresh_rate']; ?>');
    document.chatIn.counter.value=document.chatIn.msgi.value.length;
    document.chatIn.msgi.focus();
    do_refresh();


  }

  function scrollChat(){
    document.getElementById('message_window').scrollTop = 65000;
    document.getElementById('message_window').scrollTop = document.getElementById('message_window').scrollHeight;
  }

  function requestReady_channel1(){
    if(httpReq1){
      if(httpReq1.readyState == 4){
        if(httpReq1.status == 200){

          // Request is all ready to go
          playSound = 0;
          modification=0;
          count_reset=0;


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

            }else if(dataSubArray[0] == '3'){
              // Users for userlist

              var dataSubArray2 = dataSubArray[1].split(",");
              for(x2 = 0;x2 < dataSubArray2.length;x2++){
                if(dataSubArray2[x2] != ''){
                  dataSubArray2[x2] = restoreText(dataSubArray2[x2]);
                }
              }

            }else if(dataSubArray[0] == '4'){
              // Listhash update
              listhash = dataSubArray[1];
            }else if(dataSubArray[0] == '5'){
              // Endon update
              startfrom = dataSubArray[1];
            }else if(dataSubArray[0] == '6'){
              // Number of offline messages update
              if(dataSubArray[1] > 0) {
                document.getElementById('posta').src = 
                  "<?PHP echo $posta_si;?>";

                if(!newMail){
                  PlayTardis();
                }

                newMail = 1;
              }
              else {
                document.getElementById('posta').src = 
                  "<?PHP echo $posta_no;?>";
                newMail = 0;
              }

            }else if(dataSubArray[0] == '7'){
              // Private message
              dataSubArray[1] = restoreText(dataSubArray[1]);
              window.open('index.php?act=pm&send_to=' + dataSubArray[1],'Pm' + dataSubArray[1],'location=no,menubar=no,resizable=no,status=no,toolbar=no,scrollbars=yes,width=<?PHP echo $x7c->settings['tweak_window_large_width']; ?>,height=<?PHP echo $x7c->settings['tweak_window_large_height']; ?>');

              alertText = '<?PHP echo $txt[511]; ?>';
              alertText = alertText.replace('<a>',"<a style=\"cursor: pointer;\" onClick=\"window.open('index.php?act=pm&send_to=" + dataSubArray[1] + "','Pm" + dataSubArray[1] + "','location=no,menubar=no,resizable=no,status=no,toolbar=no,scrollbars=yes,width=<?PHP echo $x7c->settings['tweak_window_large_width']; ?>,height=<?PHP echo $x7c->settings['tweak_window_large_height']; ?>');\">");
              document.getElementById('message_window').innerHTML += "<span style=\"color: <?PHP echo $x7c->settings['system_message_color']; ?>;font-size: <?PHP echo $x7c->settings['sys_default_size']; ?>; font-family: <?PHP echo $x7c->settings['sys_default_font']; ?>;\"><b>" + alertText + "</b></span><Br>";
              modification=1;

              if(playSound == 0)
                playSound = 1;

            }else if(dataSubArray[0] == '8'){
              // Message
              dataSubArray[1] = restoreText(dataSubArray[1]);
              document.getElementById('message_window').innerHTML +=dataSubArray[1];

              modification=1;
              if(!dataSubArray[1].match('<span class="sussurro">'))
                count_reset=1;

              if(playSound == 0)
                playSound = 1;

            }else if(dataSubArray[0] == '9'){
              // Redirect w/ error msg
              dataSubArray[1] = restoreText(dataSubArray[1]);
              if(dataSubArray[1] != '')
                Alert(dataSubArray[1]);
              document.location = dataSubArray[2];
            }else if(dataSubArray[0] == '10'){
              startfrom = dataSubArray[1];  
            }else if(dataSubArray[0] == '11'){
              //Panic update
              panic_value = parseInt(dataSubArray[1]);
              default_max_panic=<?PHP echo $x7c->settings['default_max_panic'];?>;
              if(Math.round(panic_value*default_max_panic/max_panic)  > default_max_panic)
                panic_value=default_max_panic;
              else
                panic_value=Math.round(panic_value*default_max_panic/max_panic);

              document.getElementById('panic_img').src='./graphic/panic'+panic_value+'.jpg';
              document.getElementById('panic_text').innerHTML=panic[panic_value];
              if(panic_value==10){
                document.getElementById('panic_text').style.color='yellow';
              }
            }else if(dataSubArray[0] == '12'){
              //Panic update
              valore = parseInt(dataSubArray[1]);
              messaggio='';
              if(valore){
                var leftx = (screen.width/2)-(300/2);
                var topy = (screen.height/2)-(200/2);

                <?PHP
									echo popup_open(302, 202, './sources/oscurita_popup.html',
											'oscurita'); ?>
								hndl.moveTo(leftx, topy); 
                hndl.focus();
              }
              
							window.location.href = window.location.href;
            }else if(dataSubArray[0] == '13'){
              //Delete message
              document.getElementById('message_window').innerHTML ='';
              startfrom = 0;
              do_refresh();
            }


            // Scroll to bottom
            if(modification){
              setTimeout("scrollChat()", 500);

              if(count_reset)
                ActivateCountDown("CountDownPanel", '480', null);

            }

          }

          if(<?PHP echo $x7c->settings['disable_sounds']; ?> != 1 && playSound != 0){

            if(first_sound != 0 && document.chatIn.sound.checked){
              try { PlayChat(); } catch(e) {}
            }
						if (first_sound == 0) {
							first_sound = 1;
						}

          }
        }
      }
    }
  }


  function send_async_request(url){
    if(window.XMLHttpRequest){
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


  function restoreText(torestore){
    torestore = torestore.replace(/74ce61f75c75b155ea7280778d6e8183/g,"@");
    torestore = torestore.replace(/74ce61f75c75b155ea7280778d6e8181/g,"|");
    torestore = torestore.replace(/74ce61f75c75b155ea7280778d6e8182/g,";");
    torestore = torestore.replace(/74ce61f75c75b155ea7280778d6e8180/g,",");
    return torestore;
  }

  function speech(){
    document.chatIn.msgi.value = document.chatIn.msgi.value+"<>";
    document.chatIn.msgi.focus();

    ctrl=document.chatIn.msgi;

    var CaretPos = 0;
    // IE Support
    if (document.selection) {

      ctrl.focus ();
      var Sel = document.selection.createRange ();

      Sel.moveStart ('character', -ctrl.value.length);

      CaretPos = Sel.text.length;
    }
    // Firefox support
    else if (ctrl.selectionStart || ctrl.selectionStart == '0')
      CaretPos = ctrl.selectionStart;

    pos=CaretPos-1;
    if(ctrl.setSelectionRange)
    {
      ctrl.focus();
      ctrl.setSelectionRange(pos,pos);
    }
    else if (ctrl.createTextRange) {
      var range = ctrl.createTextRange();
      range.collapse(true);
      range.moveEnd('character', pos);
      range.moveStart('character', pos);
      range.select();
    }
  }

  function do_refresh(){
    jd=new Date();
    nocache = jd.getTime();
    url = './index.php?act=frame&frame=update&room=<?PHP echo $x7c->room_name; ?>&listhash=' + listhash + '&startfrom=' + startfrom + '&nc=' + nocache;              

    send_async_request(url);
  }

  function do_delete(msgid){
    jd=new Date();
    nocache = jd.getTime();
    url = './index.php?act=frame&delete='+msgid+'&room=<?PHP echo $x7c->room_name; ?>';
    if(msgid == 'all'){
      if(!confirm('Vuoi davvero cancellare tutti i messaggi?'))
        return;
    }
    send_async_request(url);
    Alert("Messaggio cancellato");
  }

  invisible=<?PHP echo $x7s->invisible ?>;
  function switch_invisibility(){
    jd=new Date();
    nocache = jd.getTime();
    url = './index.php?act=frame&frame=invisibility&room=<?PHP echo $x7c->room_name; ?>';
    if(invisible){
      document.getElementById('invisible_link').innerHTML='[Diventa invisibile]';
    }
    else{
      document.getElementById('invisible_link').innerHTML='[Diventa visibile]';
    }

    invisible=!invisible;

    send_async_request(url);
  }



  shadow=<?PHP echo $x7c->room_data['shadow']; ?>;
  function switch_shadow(){
    jd=new Date();
    nocache = jd.getTime();
    url = './index.php?act=frame&frame=shadow&room=<?PHP echo $x7c->room_name; ?>';
    if(!shadow){
      document.getElementById('shadow_link').innerHTML='[Disattiva suspence]';
    }
    else{
      document.getElementById('shadow_link').innerHTML='[Attiva suspence]';
    }

    shadow=!shadow;

    send_async_request(url);
  }


</script>


<script language="javascript" type="text/javascript">
  var sent=0;

  function action_select(sel){
    myaction = sel.options[sel.selectedIndex].value;
    if(myaction != ""){
      document.chatIn.msgi.value = document.chatIn.msgi.value + myaction +" ";
    }
    sel.selectedIndex=0;
    document.chatIn.msgi.focus();

  }

  function trim(str) {
    return str.replace(/^\s*/, "").replace(/\s*$/, "");
  }

  function msgSent(){
    message = document.chatIn.msgi.value;
    document.chatIn.msg.value=message;

    if(!message.match(/^@/) && !message.match(/^\*/)){
      if(trim(message).length < <?PHP echo $x7c->settings['min_post'];?>){
        Alert("Il post e' troppo corto - deve essere almeno <?PHP echo $x7c->settings['min_post'];?> caratteri");
        return false;
      }
      if(trim(message).length > <?PHP echo $x7c->settings['max_post'];?>){
        Alert("Il post e' troppo lungo - sono consentiti al max <?PHP echo $x7c->settings['max_post'];?> caratteri");
        return false;
      }
    }

    if(message != ""){


      // Some special things
      if(message.match(/^\/clear/)){
        document.getElementById('message_window').innerHTML = '';
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
      //  PHP date |  JAVASCRIPT variable
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


      // Scroll the screen and empy the message area
      document.chatIn.msgi.value="";
      document.chatIn.msgi.focus();
      document.chatIn.counter.value=document.chatIn.msgi.value.length;
      sent=1;
    }
    return true;
  }

  // This function reads key presses
  document.onkeydown = kp;
  document.onkeyup = ku;
  consec = -1;
  function kp(evt){
    if(evt)
      thisKey = evt.which;
    else
      thisKey = window.event.keyCode;
    if(thisKey == "13" && document.chatIn.autosend.checked){
        document.chatIn.button_send.click();
    }
    document.chatIn.counter.value=document.chatIn.msgi.value.length;

  }

  function ku(evt){
    if(evt)
      thisKey = evt.which;
    else
      thisKey = window.event.keyCode;

    if(thisKey == "13" && document.chatIn.autosend.checked){
      if(sent){
        document.chatIn.msgi.value='';
        cur_msg='';
        document.chatIn.counter.value=document.chatIn.msgi.value.length;
        sent=0;
      }
      //We save from enter key
      else{
        document.chatIn.msgi.value=cur_msg;
      }
    }
    document.chatIn.counter.value=document.chatIn.msgi.value.length;
  }

</script>

<div id="position"></div>
<div id="container">
  <div id="divchat">
<?PHP 
    //This file include common layout for frame and map
    include_once('./sources/layout.php');

    $polaroid=$x7c->room_data['logo'];

    if($x7c->permissions['admin_panic']){
      if($x7s->invisible)
        $inv_txt="[Diventa visibile]";
      else
        $inv_txt="[Diventa invisibile]";

      if($x7c->room_data['shadow'])
        $shd_txt="[Disattiva suspence]";
      else
        $shd_txt="[Attiva suspence]";

      echo '
          <div id="clean_chat">
              <a onClick="javascript: do_delete(\'all\')">
                [Pulisci chats]
              </a>
              <a id="invisible_link"
                onClick="javascript: switch_invisibility()">'.$inv_txt.'
              </a>
              <a id="shadow_link"
                onClick="javascript: switch_shadow()">'.$shd_txt.'
              </a>
            </div>';
  }

  ?> <!-- IMMAGINE DELLA POLAROID (a seconda della stanza) --> 
<?PHP  
  if($x7c->room_data['logo'] != '')
    echo '
      <a onClick="'.popup_open(550, 500, 
			'index.php?act=roomdesc&room='.$_GET['room'],'roomdesc').'">';
		if ($x7c->settings['panic'] && !$x7c->room_data['panic_free']) {
			$basedir=$_SERVER['DOCUMENT_ROOT'];
			$hunt_path=dirname($_SERVER['PHP_SELF'])."/images/random_hunt/";
			$path=$basedir.$hunt_path;
			
			if($dh = opendir($path)){
				$hunters = array();
				while (($file = readdir($dh)) !== false) {				
					if($file[0]!="." && filetype($path.$file)!="dir" &&
							preg_match("/.*\.(jpg|gif|png|jpeg)/i", $file)){
						$hunters[] = $hunt_path.$file;
					}
				}
				srand(intval(date('dmY')));
				$i = rand(0, count($hunters) - 1);
				$polaroid = $hunters[$i];
			}
		}
    echo '<img class="polaroid" src="'.$polaroid.'" >';
		echo '</a>'; 

?>

    <div id="message_window"></div>

    <div style='clear: both;'></div>
    <form name="chatIn" method="post"
      action="index.php?act=frame&frame=send&room=<?PHP echo $_GET['room']; ?>"
      target="send" onSubmit="return msgSent();">
    <div id='debug' style='display: none;'></div>

<?PHP
    if($x7c->settings['panic']){
      $panic=$x7s->panic > $x7c->settings['default_max_panic'] ? $x7c->settings['default_max_panic'] : $x7s->panic;
      echo "<div id=\"panicwrap\"> <img id=\"panic_img\" src=\"./graphic/panic$panic.jpg\" onMouseOver=\"document.getElementById('panic_text').style.visibility='visible'; document.getElementById('panic_text').innerHTML=panic[panic_value];\"
        onMouseOut=\"document.getElementById('panic_text').style.visibility='hidden';\"/>
        <div id=\"panic_text\" onMouseOver=\"document.getElementById('panic_text').style.visibility='visible';\"";

      if($x7s->panic >= 10){
        echo " style=\"color: yellow;\"";
      }

      echo ">Prova</div></div>";
    }
  ?>

<div id="inputchatdiv">
  <table cellspacing=0 cellpadding=0>
    <tr>
      <td><textarea name="msgi" class="msginput" autocomplete="off"></textarea>
      <input type="hidden" name="msg" value=""></td>
    </tr>

    <tr>
      <td align="center"><input type="text" class="location"
        name="locazione" value="locazione" /><input class="location"
        type="text" style="text-align: center; color: white;" name="counter"
        disabled value="0" size="4" /></td>
    </tr>

  </table>
</div>

<div id="action_countdown"><span class="CountDownPanel"
  id="CountDownPanel" time_format="%m:%s"></span> <script
  type="text/javascript">
  ActivateCountDown("CountDownPanel", '480', null);
</script></div>

<div id="cmddiv">
  <table cellspacing=3 cellpadding=0 style="width: 100%">
    <tr>
      <td class="cmdrow"><select class="button"
      name="action" onChange="javascript: return action_select(this);">
      <option value="">Scelta Abilit&agrave;...</option>
      <option value="">------------------</option>
<?PHP
    $query = $db->DoQuery("SELECT a.id AS id,
        ua.value AS value_a, 
        a.name AS name,
        uc.value AS value_c
        FROM {$prefix}userability ua, {$prefix}ability a, {$prefix}usercharact uc
        WHERE ua.ability_id=a.id
        AND ua.username='$x7s->username'
        AND uc.username='$x7s->username'
        AND uc.charact_id=a.char
        ORDER BY a.name");

    while($row = $db->Do_Fetch_Assoc($query)){
      $string = "<option value=\"�".$row['id'].";\">".$row['name']." ".
        (floor($row['value_a']*2+$row['value_c']/2))."</option>\n";
      echo $string;
    }
?>
    </select> <select class="button" name="charact"
    onChange="javascript: return action_select(this);">
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
      $string = "<option value=\"%".$row['id'].";\">".$row['name']." ".
        $row['value']."</option>\n";
      echo $string;
    }
?>
    </select> 
		</td>
		</tr>
		<tr>
		<td class="cmdrow">
		<select class="button" name="objects" style="width: 150px;"
    onChange="javascript: return action_select(this);">
    <option value="">Oggetti...</option>
    <option value="">------------------</option>
<?PHP
    $query = $db->DoQuery("SELECT id, name
        FROM {$prefix}objects
        WHERE owner='$x7s->username'
        AND equipped='1'
        ORDER BY name");

    while($row = $db->Do_Fetch_Assoc($query)){
      $string = "<option value=\"�".$row['id'].";\">".$row['name']."</option>\n";
      echo $string;
    }
?>
    </select><select class="button" name="charact"
    onChange="javascript: return action_select(this);">
    <option value="">Dadi</option>
    <option value="">----</option>
    <option value="~2;">d2</option>
    <option value="~4;">d4</option>
    <option value="~6;">d6</option>
    <option value="~8;">d8</option>
    <option value="~10;">d10</option>
    <option value="~12;">d12</option>
    <option value="~20;">d20</option>
    <option value="~100;">d100</option> 
    </select>

    <!--<input name="speech_btn" type="button" class="button" value="Parlato"
    onClick="javascript: speech();" /> -->
    <input name="button_send" type="submit" class="send_button" 
      style="cursor: pointer;
        background: url(<?PHP echo $print->image_path; ?>send.gif);
        border: none;height: 20px;width: 55px;
        text-align: center;font-weight: bold;"
      onMouseOut="this.style.background='url(<?PHP echo $print->image_path; ?>send.gif)'"
      onMouseOver="this.style.background='url(<?PHP echo $print->image_path; ?>send_over.gif)'" value="<?PHP echo $txt[181]; ?>">
		<input name="autosend" type="checkbox" checked 
			onMousemove="ShowPopup(event, this,'Invia con Enter');" onMouseout="HidePopup(this);">
		<input name="sound" type="checkbox" checked 
			onMousemove="ShowPopup(event, this,'Suono chat');" onMouseout="HidePopup(this);">

<?PHP
    if($x7c->permissions['write_master']){
      echo "<br><input name=\"img_btn\" type=\"button\" class=\"button\"
        value=\"Immagine\" onClick=\"".popup_open(
				$x7c->settings['tweak_window_large_width'],
				$x7c->settings['tweak_window_large_height'],
				'index.php?act=images','Images', "yes")."\">";

      echo "<input name=\"img_btn\" type=\"button\" class=\"button\"
        value=\"Master\"
        onClick=\"document.chatIn.msgi.value ='* ';
        document.chatIn.msgi.focus();\">";

      echo "<input name=\"whisper_all_btn\" type=\"button\" class=\"button\"
        value=\"Sussurra a tutti\"
        onClick=\"document.chatIn.msgi.value ='@_all_@ ';
        document.chatIn.msgi.focus();\" />";
    }

?> 
    </td>
    </tr>
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

