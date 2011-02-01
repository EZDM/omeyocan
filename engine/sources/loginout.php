<?PHP
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

// This file's job is to handle all login and logout
// This file controls pages for the following actions:
//		act = login
//		act = login2
//		act = logout

  function page_login($failed=""){
    global $print,$txt,$db,$prefix,$x7c;		
    // Check to see if $failed contains a value, if it does then print
    // a message telling them they failed to authenticate
    //We want SSL on this page
    if($_SERVER["SERVER_PORT"] != 443) {
      header("HTTP/1.1 301 Moved Permanently");
      header("Location: https://" . $_SERVER["SERVER_NAME"] .
          $_SERVER["REQUEST_URI"]);
      exit();
    }

    if($failed == ""){
      $title = $txt[0];
      $failmsg = "";
    }elseif($failed == "invalid"){
      $title = $txt[14];
      $txt[23] = eregi_replace("_n","{$x7c->settings['maxchars_username']}",
          $txt[23]);
      $failmsg = $txt[23];
    }elseif($failed == "activated"){
      $title = $txt[14];
      $failmsg = $txt[613];
    }elseif($failed == "frozen"){
      $title = "Personaggio congelato";
      $failmsg = "Il tuo personaggio e' congelato; chiedi a un admin ".
        "di scongelartelo. <a href=\"mailto:webmaster@omeyocan.it\">".
        "webmaster@omeyocan.it</a> ";
    }else{
      $failmsg = $txt[13];
      $title = $txt[14];
			include_once('./lib/alarms.php');
			wrong_login(@$_POST['username']);
    }

    // Print the login form that the user must enter username and password
    $body = "	<div class=\"center\"><img src=\"./graphic/benvenuti.gif\"></div>
      <div id=\"login_form\">
      <form action=\"index.php\" method=\"post\" name=\"loginform\">
      <input type=\"hidden\" name=\"dologin\" value=\"dologin\">
      <table align=\"center\" border=\"0\" width=\"225\" cellspacing=\"0\" 
      cellpadding=\"4\">
      <tr>
      <td>Username: </td>
			</tr>
			<tr>
      <td>
			<input type=\"text\" class=\"text_input\"
      name=\"username\"></td>
      </tr>
      <tr>
      <td>$txt[3]: </td>
			</tr>
			<tr>
      <td><input type=\"password\" class=\"text_input\"
      name=\"password\"></td>
      </tr>
      <tr>
      <td>
      <input type=\"submit\" value=\"Entra\" class=\"button\">
      <Br>
      <Br>
      <a href=\"./index.php?act=register\">[$txt[6]]</a> &nbsp;";

    if($x7c->settings['enable_passreminder'] == 1)
      $body .= 	"<a href=\"./index.php?act=forgotmoipass\">[Recupera password]
        </a></td>";

    include('sources/disclaimer.txt');
    $body .= 	"</tr>
      <tr>
      <td class=\"error\"><br>$failmsg</td>
      </tr>
      </table>
      </form></div>
      <div id=\"disclaimer\">".
      $disc	
      ."</div>
      ";

    include('sources/wellcome.html');
//    $body .= "<div id=\"wellcome_text\">$wellcome</div>";

    // See if there is any news to show
    if($x7c->settings['news'] != "")
      $body.=$x7c->settings['news'];

    // See if the stats window should be displayed
    if($x7c->settings['show_stats'] == 1){
      // Get the information for the online table
      include_once("./lib/online.php");
      clean_old_data();
      $people_online = get_online();
      $number_online = count($people_online);
      $people_online = implode(", ",$people_online);

      // Calculate total rooms
      $rooms = 0;
      $query = $db->DoQuery("SELECT id FROM {$prefix}rooms WHERE type='1'");
      while($row = $db->Do_Fetch_Row($query))
        $rooms++;

      // Calculate total registered users
      $accounts = 0;
      $query = $db->DoQuery("SELECT id FROM {$prefix}users WHERE 
          user_group<>'{$x7c->settings['usergroup_guest']}'");
      while($row = $db->Do_Fetch_Row($query))
        $accounts++;


      // Now body will hold the stats table
      $body .= "	<div id=\"stats\">
        <table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
        <tr valign=\"top\">
        <td width=\"175\">
        <table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
        <tr valign=\"top\">
        <td width=\"125\"><b>$txt[8]:</b> </td>
        <td width=\"50\">$number_online</td>
        </tr>
        <tr valign=\"top\">
        <td width=\"125\"><b>$txt[9]:</b> </td>
        <td width=\"50\">$rooms</td>
        </tr>
        <tr valign=\"top\">
        <td width=\"125\"><b>$txt[10]:</b> </td>
        <td width=\"50\">$accounts</td>
        </tr>
        </table>
        </td>
        <td width=\"225\"><b>$txt[11]</b><Br>
        <i>$people_online</i>
        </td>
        </tr>
        </table>
        </div>
        ";

    }

    print_loginout($body);

  }

  function logout_page(){
    srand(time()+microtime());
    $num = rand(1,23);


    if(isset($_GET['secret'])){
      $body = "<div id=\"logout_secret\">
        <img src=\"graphic/LOGOUTLOST.jpg\"></div>
        <script language=\"javascript\" type=\"text/javascript\">
        opener.location.href='index.php';
      </script>";
    }
    else{
      $body = '<div id="logout">
        <img src=./graphic/logout'.$num.'.jpg><br>
        Logout eseguito.
        <a href="index.php">Clicca Qui</a> per accedere nuovamente.</div>';
    }


    print_loginout($body,true);
  }

  function print_loginout($body,$nosfondo=false){
    global $print,$x7c,$x7s;

    echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
    echo "<html dir=\"$print->direction\"><head><title>".
      "{$x7c->settings['site_name']}</title>";
    echo $print->style_sheet;
    echo $print->ss_mini;
    echo $print->ss_chatinput;
    echo $print->ss_uc;

    if(!$nosfondo)
      $sfondo = 'background-image:url(./graphic/login01.jpg);';
    else
      $sfondo='';

    $login_style = '
      <LINK REL="SHORTCUT ICON" HREF="./favicon.ico">
      <style type="text/css">
			  body {
          overflow: hidden;
				}
        #login{
          height: 700px;
          '.$sfondo.'
					background-repeat: no-repeat;
          position: relative; 
      }

      #inner_login{
        position: absolute;
        color: white;
        margin-left: 0px;
        margin-top: 0px;
        width: 100%;
				height: 100%;
      }

      #login_form{
				position: absolute;
        width: 100%;
				top: 65px;
        margin-left: 15px;
        margin-top: 320px;
      }

      td{
        color: white;
      }

			#login_form td{
				valign: top;
				text-align: center;
			}

      .text_input{
        border: solid 1px white;
      }

      .center{
        text-align: center;
      }

      #logout{
        width: 1026px; 
        text-align: center;
      }

      #logout_secret{
        position: absolute;
        top: 0;
        left: 0;
        width: 600;
        height: 440;
        text-align: center;
      }

      .error{
        color: red;
        font-weight: bold;
      }

      #disclaimer{
        width: 200px;
        height: 220px;
				top: 490px;
				left: 10px;
				position: absolute;
        overflow: auto;
      }

      #wellcome_text{
        position: absolute;
        width: 290px;
        height: 240px;
        top: 80px;
        left: 680px;
        overflow: auto;
      }

			#register_banner {
				position: absolute;
				background: black;
				top: 0px;
				left: 0px;
				text-align: center;
				width: 100%;
				height: 100%;
			}

			#citizen_popup, #uncitizen_popup {
				position: absolute;
				font-weight: bold;
				font-size: 12pt;
				visibility: hidden;
				border: 1px solid white;
				opacity: 0.6;
				background-color: white;
				color: black;
				padding: 30px;
			}

			#citizen_popup {	
				top: 320px;
				left: 220px;
			}

			#uncitizen_popup {
				top: 320px;
				left: 720px;
			}

			.citizen_banner {
				opacity: 0.4;
			}

			#register_table {
				margin: 50px 50px;
			}

    </style>
    ';



    echo $login_style;

    if(!$nosfondo){
      echo '</head><body>
        <div class="login" id="login">
        <div id="inner_login">
        ';

      echo $body;
      echo '
        </div>
        </div>
        </body>
        </html>';
    }
    else{
      echo '</head><body>';
      echo $body;
      echo '
        </body>
        </html>';
    }
  }

?>
