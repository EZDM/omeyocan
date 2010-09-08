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
//		X7 Chat Version 2.0.4.2
//		Released July 29, 2006
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
	// This file generates style sheets for simple themes
	// Recieves the theme data file as an array
	
	// Grab the variables
	function get_data($theme_data,$skin){
		foreach($theme_data as $key=>$val){
			if($val != "\n"){
				eregi("([A-z0-9_]*)\[(.*)\]","$val",$match);
				$match[2] = eregi_replace("url\(","url(./themes/$skin/",$match[2]);
				$data[$match[1]] = $match[2];
			}
		}
		return $data;
	}
	
	function gen_css($data,$skin){	
		// Generate the Style information
		$css = "<style type=\"text/css\">
			BODY {
				color: white;
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				background: black;
				margin: 0 auto;
				padding: 0px;
			}
			TD {
				color: white;
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
			}
			INPUT{
				height: 21px;
			}
			.online_list {
				color: $data[FontColor1];
				border: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				width: 96%;
				background: $data[BGColor2];
				height: 98%;
				margin-left: 1%;
				top: 100px;
				left: 1000px;
				position: absolute;
			}
			.menubar {
				color: $data[FontColor2];
				background: url(./themes/$skin/button.gif);
				text-align: center;
				font-size: $data[FontSize1];
				font-family: $data[FontFamily];
				cursor: pointer;
			}

			.throw_eval{
                                border: 1px solid;
                                text-align: center;
                                font-weight: bold;
			}
			.menubar_hover {
				color: $data[FontColor2];
				background: url(./themes/$skin/button_over.gif);
				text-align: center;
				font-size: $data[FontSize1];
				font-family: $data[FontFamily];
				cursor: pointer;
			}
			.infobar {
				color: $data[FontColor1];
				font-size: $data[FontSize1];
				font-family: $data[FontFamily];
			}
			.box_header {
				color: $data[FontColor3];
				font-family: $data[FontFamily];
				font-size: $data[FontSize2];
				background: $data[HeaderBG];
				border: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				text-align: center;
				font-weight: bold;
			}
			.box_body {
				color: $data[FontColor1];
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				background: $data[BGColor2];
				border: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				border-top: none;
				text-align: left;
			}
			#board_wrapper{
				background-image:url(./graphic/sfondobacheca.jpg);
				width: 800px; 
				height: 571px;
				position:relative;
				margin: 0 auto;
			}
			.board_cell{
				border-color: white;
				border-style: solid;
				border-width: 2px;
				text-align: center;
				padding: 5px;
				border-collapse: collapse;
			}
			#board_head{
				position: absolute;
				top: 25px;
				left: 0px;
				width: 100%;;
				color: white;
				font-family: $data[FontFamily];
				font-size: $data[FontSize2];
				text-align: center;
				font-weight: bold;
			}
			#board_index{
				position: absolute;
				width: 33%;
				float: right;
				right: 55px;
				top: 50px;
				text-align: center;
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				color: white;
			}
			#board_body {
				position: absolute;
				float: left;
				top: 50px;
				left: 30px;
				width: 420px;
				height: 480px;
				color: white;
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				text-align: center;
				border-style: solid;
				border-width: 2px;
				padding: 5px;
				overflow: auto;
			}
			.info_box_body {
				color: $data[FontColor1];
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				background: $data[BGColor2];
				border: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				text-align: left;
			}
			.col_header {
				color: $data[FontColor3];
				font-family: $data[FontFamily];
				font-size: $data[FontSize3];
				font-weight: bold;
				background: $data[ColumnBG];
				text-align: left;
				border: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
			}
			.dark_row {
				color: $data[FontColor1];
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				background: $data[BGColor3];
				text-align: left;
                                font-weight: normal;
			}
			.inside_table {
				border: 0;
				border-top: none;
			}
			A {
				color: yellow;
				text-decoration: none;
				cursor: pointer;
			}
			A:HOVER {
				color: yellow;
				text-decoration: underline;
				cursor: pointer;
			}
			A:ACTIVE {
				color: blue;
			}
			a img{
				border: 0;
			}
			.text_input{
				background: transparent;
				border: $data[FormBorderSize] $data[FormBorderStyle] $data[FormBorderColor];
				font-size: $data[FormFontSize];
				font-family: $data[FontFamily];
				color: $data[FormFontColor];
			}
			.button{
				background: $data[FormBG];
				border: $data[FormBorderSize] $data[FormBorderStyle] $data[FormBorderColor];
				font-size: $data[FormFontSize];
				font-family: $data[FontFamily];
				color: $data[FormFontColor];
			}
			.errore{
				text-align: center;
				color: #650000;
				font-weight: bold;
				font-size: 10pt;
				position: absolute;
				top: 300px;
				left: 20px;
				width: 400px;
			}
			.errore_popup{
				text-align: center;
				color: #650000;
				font-weight: bold;
				font-size: 10pt;
				position: absolute;
				top: 50%;
				left: 25%;
				width: 400px;
				background-color: lightyellow;
				padding: 5px;
				border: 3px dashed red;
				text-decoration: none;
				z-index: 2;
			}
			.errore_ab{
				text-align: center;
				color: #650000;
				font-weight: bold;
				font-size: 10pt;
				position: absolute;
				top: 10px;
				left: 20px;
			}

			.msg_row{
				border-bottom: 1px solid white;
				vertical-align: top;
				padding: 5px;
			}
			.msg_avatar{
				border-bottom: 1px solid white;
				vertical-align: top;
				padding: 5px;
				width: 110px;
			}
		</style>";
		
		return $css;
	}
	
	function gen_chatinput($data,$skin){
		global $x7c;
		
		$css = " <style type=\"text/css\">
			.arrow_box {
				border-left: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				background: $data[BGColor3];
				color: $data[FontColor1];
			}
			.selectbar {
				border: none;
				background: url(./themes/$skin/selectbar.gif);
				height: 15px;
				color: $data[FontColor1];
			}
			.msginput_bg {
			}
			.msginput {
				border: $data[FormBorderSize] $data[FormBorderStyle] $data[FormBorderColor];
				background: transparent;
				font-family: $data[FontFamily];
				font-size: $data[FormFontSize];
				width: 500px;
				height: 43px;
				color: $data[FormFontColor];
				border: solid;
				border-width: 1px;
				overflow: auto;
			}
			.location {
				border: $data[FormBorderSize] $data[FormBorderStyle] $data[FormBorderColor];
				background: $data[FormBG];
				font-family: $data[FontFamily];
				font-size: $data[FormFontSize];
				color: $data[FormFontColor];
			
			}
			.smileybuttonOver {
				border: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				background: $data[BGColor4];
				cursor: pointer;
				color: $data[FontColor1];
			}
			.smileybutton {
				border: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				background: url(./themes/$skin/selectbar.gif);
				cursor: pointer;
				color: $data[FontColor1];
			}
			.boldtxt {
				background: url(./themes/$skin/selectbar.gif);
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				cursor: pointer;
				border: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				text-align: center;
				color: $data[FontColor1];
			}
			.boldtxtover {
				background: $data[BGColor2];
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				cursor: pointer;
				border-left: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				border-top: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				border-right: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				border-bottom: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				text-align: center;
				color: $data[FontColor1];
			}
			.boldtxtdown {
				background: url(./themes/$skin/selectbar_inv.gif);
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				cursor: pointer;
				border-right: $data[BorderSize] $data[BorderStyle] $data[BorderColorLight];
				border-bottom: $data[BorderSize] $data[BorderStyle] $data[BorderColorLight];
				border-left: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				border-top: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				text-align: center;
				color: $data[FontColor1];
			}
			.curfont {
				width: 61px;
				height: 15px;
				background: transparent;
				border: 0px solid $data[BorderColor];
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				cursor: pointer;
				color: $data[FontColor1];
			}
			.cursize {
				width: 41px;
				height: 15px;
				background: transparent;
				border: 0px solid $data[BorderColor];
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				cursor: pointer;
				color: $data[FontColor1];
			}
			.curcolor {
				width: 61px;
				height: 15px;
				background: transparent;
				border: 0px solid $data[BorderColor];
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				cursor: pointer;
				color: $data[FontColor1];
			}
			.selected {
				background: $data[BGColor3];
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				cursor: pointer;
				color: $data[FontColor1];
			}
			.nonSelected {
				background: $data[BGColor2];
				border: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				cursor: pointer;
				color: $data[FontColor1];
			}
			.send_button {
				color: $data[FontColor1];
			}
		 	#container {
		 		position: relative; 
		 		width: 1026px; 
		 		left: 50%; 
		 		margin-left: -513px;
		 	}
			#divchat {
				background-image:url(";
			if($x7c->settings['panic'])
				$css .= "./graphic/sfondo1026x723obscure.jpg";
			else
				$css .=	"./graphic/sfondo1026x723.jpg";
				
			$css.=");
				width: 1026px; 
				height: 723px;
				position: absolute;
				left: 0px;
				right: 0px;
			}
			#divmap {
				background-image:url(";
			if($x7c->settings['panic'])
				$css .= "./graphic/sfondomap1026x723obscure.jpg";
			else
				$css .=	"./graphic/sfondomap1026x723.jpg";
				
			$css.=");
				width: 1026px; 
				height: 723px;
				position: absolute;
				left: 0px;
				right: 0px;
			}
			#inputchatdiv {
				position: absolute;
				top: 590px;
				left: 190px;
			}
			#clean_chat{
				position: absolute;
				top: 70px;
				left: 20px;
			}

			#invisible_master{
				position: absolute;
				top: 90px;
				left: 20px;
			}
			
			#shadow_room{
				position: absolute;
				top: 110px;
				left: 20px;
			}
			
			#cmddiv {
				position: absolute;
				top: 680px;
				left: 0px;
				width: 870px;
				text-align: center;
			}
			#action_countdown{
				position: absolute;
				top: 650px;
				left: 770px;
				width: 100px;
				text-align: center;
				font-size: 10pt;
				font-weight: bold;
			}
			
			#panicwrap {
				position: absolute;
				text-align: center;
				top: 580px;
				left: 0px;
			}
			#panic_text {
				position: absolute;
				text-align: center;
				top: 60px;
				left: 10px;
				width: 100px;
				font-style: italic;
				font-size: 10pt;
				font-weight: bold;
				border: 3px solid;
				background: black;
				padding: 5px;
				padding-left: 10px;
				padding-right: 10px;
				border-style: ridge;
				visibility: hidden;
			}
			#position{
				position: absolute;
				font-style: italic;
				font-size: 10pt;
				font-weight: bold;
				border: 3px solid;
				background: black;
				padding: 5px;
				padding-left: 10px;
				padding-right: 10px;
				border-style: ridge;
				visibility: hidden;
			}
			#copyrigth {
				position: absolute;
				font-size: 7pt;
				top: 675px;
				left: 890px;
				text-align: center;
				width: 120px;
			}
		</style>";
		
		return $css;
	}
	
	function gen_events($data,$skin){
		
		$css = "<style type=\"text/css\">
			.event_top {
				font-family: $data[FontFamily];
				font-size: $data[FontSize2];
				font-weight: bold;
				border: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				border-right: 0px $data[BorderStyle] $data[BorderColor];
				background: $data[BGColor2];
				color: $data[FontColor0];
			}
			.event_table {
				border-right: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
			}
			.event_day {
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				border: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				border-right: 0px $data[BorderStyle] $data[BorderColor];
				color: $data[FontColor0];
			}
			.event_day_name {
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				border-bottom: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				text-align: center;
				font-weight: bold;
				color: $data[FontColor0];
			}
			.event_day_no {
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				border: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				border-right: 0px $data[BorderStyle] $data[BorderColor];
				text-align: center;
				color: $data[FontColor0];
			}
			.event_day_yes {
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				text-align: center;
				border: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				border-right: 0px $data[BorderStyle] $data[BorderColor];
				background: url(./themes/$skin/./star.gif);
				cursor: pointer;
				color: $data[FontColor0];
			}
			.event_day_abr {
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				border: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				text-align: center;
				font-weight: bold;
				color: $data[FontColor0];
			}
		</style>";
		
		return $css;
	}
	
	function gen_mini($data,$skin){
		
		$css = "<style type=\"text/css\">
			.male {
				color: #0ca4e3;
				font-weight: bold;
				font-size: 8pt;
			}
			.female {
				color: #ff6dbc;
				font-weight: bold;
				font-size: 8pt;
			}
			.you {
				color: $data[You];
				font-weight: bold;
			} 
			#message_window {
				position: absolute;
				padding: 5px;
				blue: blue;
				height: 1%;
				/*border: 1px solid $data[ChatBorder];*/
				overflow-y: scroll;
				top: 165px;
				left: 117px;
				width: 640px;
				height: 405px;
			}
			span {
			 position: relative;
			 bottom: 0px;
			}
			.action{
			 color: #ffffff;
			}
			.locazione_display{
			 color: #b0b0b0;
			 font-weight: bold;
			}
			.sussurro{
			 color: white;
			}
			.chatmsg{
			 color: #b0b0b0;
			 font-size: 8pt;
			 font-weight: bold;
			}
			.mastering{
			 color: teal;
			 border: 5px ridge #f3b700;
			 padding: 5px;
			 text-align: center;
			 font-style: italic;
			 font-size: 8pt;
			 font-weight: bold;
			}
			.ambient{
			 color: teal;
			 border: 5px ridge purple;
			 padding: 5px;
			 text-align: center;
			 font-style: italic;
			 font-size: 8pt;
			 font-weight: bold;
			}
			.roll_neg{
			 font-weight: bold;
			 color: red;
			}
			.roll_avg{
			 font-weight: bold;
			 color: orange;
			}
			.roll_pos{
			 font-weight: bold;
			 color: #05bf01;
			}
			.break{
			 font-weight: bold;
			 color: #edd50a;
			}
			.masterRoll{
			 font-weight: bold;
			 color: white;
			}
		</style>";
		
		$css = eregi_replace("\r","",$css);
		$css = eregi_replace("\n","",$css);
		$css = eregi_replace("'","\'",$css);
	
		return $css;	
	}
	
	function gen_pm($data,$skin){
		
		$css = "<style type=\"text/css\">
			.pm_infobar {
				background: black;
			}
			.pm_ib_fc {
				text-align: center;
				background: $data[BGColor3];
				font-size: $data[FontSize0];
				font-family: $data[FontFamily];
				font-weight: bold;
				color: $data[FontColor0];
			}
			.pm_ib_r {
				text-align: center;
				background: $data[BGColor2];
				font-size: $data[FontSize0];
				font-family: $data[FontFamily];
				cursor: pointer;
				color: $data[FontColor0];
			}
			.pm_ib_r_alt {
				text-align: center;
				background: $data[BGColor3];
				font-size: $data[FontSize0];
				font-family: $data[FontFamily];
				cursor: pointer;
				color: $data[FontColor0];
			}
			.main_iframe {
				border: 1px solid $data[ChatBorder];
			}
		</style>";
	
		return $css;	
	}
	
	function gen_profile($data,$skin){
		
		$css = "<style type=\"text/css\">
			.profile_username {
				font-size: $data[FontSize2];
				font-weight: bold;
				text-align: center;
			}
			.profile_header_text {
				font-weight: bold;
			}
			.profile_table {
			}
			.profile_cell {
			}
		</style>";
	
		return $css;	
	}
	
	function gen_uc($data,$skin){
		
		$css = " <style type=\"text/css\">
			.uc_item_box{
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				vertical-align: middle;
				border: $data[FormBorderSize] $data[FormBorderStyle] $data[FormBorderColor];
				background: $data[BGColor2];
				text-align: center;
				color: $data[FontColor1];
			}
			.uc_item {
				font-family: $data[FontFamily];
				font-size: $data[FontSize0];
				vertical-align: middle;
				border: $data[FormBorderSize] $data[FormBorderStyle] $data[BGColor2];
				background: $data[BGColor2];
				text-align: center;
				width: 100px;
				color: $data[FontColor1];
				height: 18px;
				cursor: default;
			}
			.uc_header{
				font-family: $data[FontFamily];
				font-size: $data[FontSize0];
				vertical-align: middle;
				cursor: pointer;
				text-align: center;
				background: url(./themes/$skin/user_control_bg.gif);
				color: $data[FontColor1];
			}
			.uc_header_text{
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				vertical-align: middle;
				cursor: pointer;
				text-align: center;
				color: $data[FontColor1];
				cursor: pointer;
			}
			.uc_header_selected{
				font-family: $data[FontFamily];
				font-size: $data[FontSize1];
				vertical-align: middle;
				cursor: pointer;
				text-align: center;
				font-weight: bold;
				background: url(./themes/$skin/user_control_bg2.gif);
				color: $data[FontColor1];
				cursor: pointer;
			}
			.uc_item_over{
				font-family: $data[FontFamily];
				font-size: $data[FontSize0];
				vertical-align: middle;
				cursor: pointer;
				background: $data[BGColor3];
				border: $data[FormBorderSize] $data[FormBorderStyle] $data[FormBorderColor];
				text-align: center;
				width: 100px;
				color: $data[FontColor1];
				height: 18px;
				cursor: pointer;
			}
			.uc_item_blank{
				font-family: $data[FontFamily];
				font-size: $data[FontSize0];
				vertical-align: middle;
				background: $data[BGColor2];
				border: 1px solid $data[BGColor2];
				text-align: center;
				width: 100px;
				color: $data[FontColor1];
				height: 18px;
				cursor: default;
			}
			.infobox {
				font-size: $data[FontSize1];
				font-family: $data[FontFamily];
				border: none;
				cursor: pointer;
				background: transparent;
				color: $data[FontColor1];
			}

		</style>";
	
		return $css;	
	}
	
	function gen_ucp($data,$skin){
		
		$css = "<style type=\"text/css\">
			.ucp_cell {
				text-align: center;
				background: $data[BGColor4];
				font-size: $data[FontSize0];
				font-family: $data[FontFamily];
				cursor: pointer;
				border-bottom: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				color: $data[FontColor1];
			}
			.ucp_sell {
				text-align: center;
				background: $data[BGColor3];
				font-size: $data[FontSize0];
				font-family: $data[FontFamily];
				cursor: pointer;
				border-bottom: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				color: $data[FontColor1];
			}
			.ucp_bodycell {
				border-bottom: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				border-right: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				font-size: $data[FontSize0];
				font-family: $data[FontFamily];
				color: $data[FontColor1];
			}
			.ucp_table {
				border-top: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				color: $data[FontColor1];
			}
			.ucp_table2 {
				border-left: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				color: $data[FontColor1];
			}
			.ucp_divider{
				border-left: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				border-bottom: $data[BorderSize] $data[BorderStyle] $data[BorderColor];
				color: $data[FontColor1];
			}
		</style>";
	
		return $css;	
	}
?>
