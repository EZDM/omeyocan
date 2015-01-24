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
	
	function print_disabled($body){
		global $print,$x7c,$x7s;
		
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
		echo "<html dir=\"$print->direction\"><head><title>{$x7c->settings['site_name']}</title>";
		echo $print->style_sheet;

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
				background-image:url(\'./graphic/sfondodisabled.gif\');
				background-repeat: no-repeat;
				background-size: 100%;
			}
			#disableddiv {
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
					<div id="disableddiv">';

		echo $body;


		echo '	
			Omeyocan.it è in manutenzion<br>
			(che non si vede?)<br><br>
			Torneremo presto
			<br><br>
			<a href="index.php?act=logout">Logout</a>
			</div>
			</div>
			</div>

			</body>
			</html>';


	}

?>
