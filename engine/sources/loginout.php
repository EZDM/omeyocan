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
      $failmsg = "Tutti i personaggi della precedente era sono congelati per ".
				"questioni di trama. E\' necessario creare un nuovo personaggio per ".
				"giocare nella nuova era. ";
    }else{
      $failmsg = $txt[13];
      $title = $txt[14];
			include_once('./lib/alarms.php');
			wrong_login(@$_POST['username']);
    }

    // Print the login form that the user must enter username and password
		if (@$_GET['act'] == 'disclaimer') {
			include('sources/disclaimer.txt');
			$body = '<div id="animation">'.$disc."<br><br>";

			$body .= '<a href="./index.php">Login</a></div>';
		} else {

		$typed = $x7c->settings['news'];
	  $animation_style = '';
		if(isset($failmsg) && $failmsg) {
			$typed = htmlentities($failmsg, ENT_QUOTES);;
			$animation_style = ' style="color: red;"';
		}

    $body = "	
			<script type=\"text/javascript\">
			  animation_call = setInterval('do_animation()', 50);
		    
		    i = 0;
				j= 0;
				current_text = '';
				text = new Array();

				text[0] = '".$typed."';

		    function do_animation() {
					if (j == text.length) {
						clearInterval(animation_call);
						setInterval('do_animation()', 500);
						j++;
					}
					else if (j > text.length) {
						if (i % 2) {
							current_text = current_text.substring(0, current_text.length - 1);
						} else {
							current_text += '_';
						}
						i++;
					} else {
						if (i >= text[j].length) {
							i = 0;
							j++;
							current_text = current_text.substring(
									0, current_text.length - 1) + '<br> ';
						} else {
							if (i > 0 && (i % 40) == 0 && (text[j][i]) == ' ') {
							current_text = current_text.substring(
									0, current_text.length - 1) + '<br> ';
						  }
							current_text = current_text.substring(
									0, current_text.length - 1) + text[j][i] + '_';
							i++;
						}
					}

          div = document.getElementById('animation');
					div.innerHTML = current_text;
				}
			</script>

			<div id=\"animation\"".$animation_style."></div>
      <div id=\"login_form\">
      <form action=\"index.php\" method=\"post\" name=\"loginform\">
      <input type=\"hidden\" name=\"dologin\" value=\"dologin\">
      <table align=\"center\" border=\"0\" width=\"225\" cellspacing=\"0\" 
      cellpadding=\"4\">
      <tr>
      <td>Username: </td>
      <td>
			<input type=\"text\" class=\"text_input\"
      name=\"username\"></td>
      </tr>
      <tr>
      <td>$txt[3]: </td>
      <td><input type=\"password\" class=\"text_input\"
      name=\"password\"></td>
      </tr>
      <tr>
      <td>
      <input type=\"submit\" value=\"Entra\" class=\"button\">
			</td>
			<td>
			<a href=\"./index.php?act=disclaimer\">[Disclaimer]</a>
      <a href=\"./index.php?act=register\">[$txt[6]]</a>
			<br>";

    if($x7c->settings['enable_passreminder'] == 1)
      $body .= 	"<a href=\"./index.php?act=forgotmoipass\">[Recupera password]</a>";
		$body .= "</td>";

    $body .= 	"</tr>
      <tr>
      </tr>
      </table>
      </form></div>
      ";

		}
    print_loginout($body);
  }

  function logout_page(){
    srand(time()+microtime());
    $num = rand(1,25);


    if(isset($_GET['secret'])){
      $body = "<div id=\"logout_secret\">
        <img src=\"graphic/LOGOUTLOST.jpg\"></div>
        <script language=\"javascript\" type=\"text/javascript\">
        opener.location.href='index.php';
      </script>";
    }
    else{
      $body = '<div id="logout">
        <img src=./graphic/logout'.$num.'.jpg class="logoutimg"><br><br>
        Logout eseguito.
        <a href="index.php">Clicca Qui</a> per accedere nuovamente.</div>
				';
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
          height: 725px;
					width: 1025px;
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
				top: 70px;
        margin-left: 15px;
        margin-top: 320px;
      }

      #animation{
        top: 300px;
				left: 400px;
			  position: absolute;
				color: #00c95a;
   			font-weight: bold;
        width: 250px;
				height: 180px;
        overflow: auto;
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
				position: relative;
				background: black;
				top: 0px;
				left: 0px;
				text-align: center;
				width: 100%;
				height: 100%;
			}
			#class_choice {
        position: relative;
				top: 0;
				left: 0;
				background: black;
				text-align: center;
				width: 100%;
				height: 625px;
			}
			.class_container {
				position: relative;
				display: inline-block;
				width: 247px;
				height: 625px;
			}
			.class_image {
				position: absolute;
				width: 247px;
				top: 0;
				left: 0;
			}
			.class_descr {
				position: absolute;
				text-align: left;
				display: none;
				top: 0;
				left: 0;
				height: 100%;
				font-size: 10pt;
				overflow: auto;
			}
			.classlink,
			.classlink:link,
			.classlink:hover,
			.classlink:active {
				color: white;
				text-decoration: none;
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
				height: 610px;
			}

			#register_table {
				margin: 50px 50px;
			}
			
			.logoutimg {
				height: 650px;
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
