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
	
	function print_ban($body){
		global $print,$x7c,$x7s;
		
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
		echo "<html dir=\"$print->direction\"><head><title>{$x7c->settings['site_name']}</title>";
		echo $print->style_sheet;
		echo $print->ss_mini;
		echo $print->ss_chatinput;
		echo $print->ss_uc;
		
		
		echo '
		<LINK REL="SHORTCUT ICON" HREF="./favicon.ico">
		<style type="text/css">
		
			#container{
				position: relative;
			}

			#innerdiv{
				position: absolute;
				top: 0;
				left: 0;
				width: 1024px;
				height: 723px;
				background-image:url(\'./graphic/ban_page.jpg\');
			}
			#bandiv {
				position: absolute;
				left: 0;
				top: 450;
				width: 1024px;
				text-align: center;
				font-size: 12pt;
				color: #8f8f8f;
			}
		</style>
		';
		
		
		
                echo '</head><body>
			<div id="container">
				<div id="innerdiv">
					<div id="bandiv">';

		echo $body;


		echo '			<br><br>
					<a href="index.php?act=logout">Logout</a>
					</div>
				</div>
			</div>

                        </body>
                                </html>';
                
		
	}

?>
