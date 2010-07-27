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
			wrong_login($_POST['username']);
    }

    // Print the login form that the user must enter username and password
    $body = "	<div class=\"center\"><img src=\"./graphic/benvenuti.jpg\"></div>
      <div id=\"login_form\">
      <form action=\"index.php\" method=\"post\" name=\"loginform\">
      <input type=\"hidden\" name=\"dologin\" value=\"dologin\">
      <table align=\"center\" border=\"0\" width=\"225\" cellspacing=\"0\" 
      cellpadding=\"4\">
      <tr valign=\"top\">
      <td width=\"225\" style=\"text-align: center\" colspan=\"2\"
      class=\"error\">$failmsg<Br><Br></td>
      </tr>
      <tr valign=\"top\">
      <td width=\"80\">Username: </td>
      <td width=\"175\"><input type=\"text\" class=\"text_input\"
      name=\"username\"></td>
      </tr>
      <tr valign=\"top\">
      <td width=\"80\">$txt[3]: </td>
      <td width=\"175\"><input type=\"password\" class=\"text_input\"
      name=\"password\"></td>
      </tr>
      <tr valign=\"top\">
      <td width=\"225\" style=\"text-align: center\" colspan=\"2\">
      <input type=\"submit\" value=\"Entra\" class=\"button\">
      <Br>
      <Br>
      <a href=\"./index.php?act=register\">[$txt[6]]</a> &nbsp;";

    if($x7c->settings['enable_passreminder'] == 1)
      $body .= 	"<a href=\"./index.php?act=forgotmoipass\">[Recupera password]
        </a></td>";

    include('sources/disclaimer.txt');
    $body .= 	"</tr>
      <tr><td>&nbsp;</td></tr>
      <tr><td>&nbsp;</td></tr>
      <tr>
      <td colspan=2>
      <div id=\"disclaimer\">".
      $disc	
      ."</div>
      </td>
      </tr>
      </table>
      </form></div>
      ";

    include('sources/wellcome.html');
    $body .= "<div id=\"wellcome_text\">$wellcome</div>";

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

    // See if the admin wants the upcoming events to show
    if($x7c->settings['show_events'] == 1){
      include_once("./lib/events.php");

      if($x7c->settings['events_show3day'] == 1){
        $body .= cal_threedays()."<Br><br>";
      }

      if($x7c->settings['events_showmonth'] == 1){
        $body .= cal_minimonth();
      }
    }

    print_loginout($body);

  }

  function logout_page(){
    srand(time()+microtime());
    $num = rand(1,20);


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
        #login{
          height: 700px;
          '.$sfondo.'
          position: relative; 
          width: 1026px; 
          left: 50%; 
          margin-left: -513px;
      }

      #inner_login{
        position: absolute;
        color: white;
        margin-left: 60px;
        margin-top: 50px;
        width: 100%;
      }

      #login_form{
        width: 300px;
        margin-left: 42px;
        margin-top: 0px;
      }

      td{
        color: white;
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
        width: 300px;
        height: 200px;
        overflow: auto;
      }

      #wellcome_text{
        position: absolute;
        width: 290px;
        height: 240px;
        top: 80px;
        left: 650px;
        overflow: auto;
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
