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
	
	function hint_display(){
		global $print, $x7c, $x7s, $db, $prefix;
		
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
		echo "<html dir=\"$print->direction\"><head><title>{$x7c->settings['site_name']}</title>";
		echo $print->style_sheet;

		echo '
		<LINK REL="SHORTCUT ICON" HREF="./favicon.ico">
		<style type="text/css">
		
			#innerdiv{
				width: 100%;
				height: 100%;
			}
			#hintdiv {
				padding: 20px;
				text-align: center;
				font-size: 12pt;
				color: #8f8f8f;
			}
		</style>
		';
		
	
		$query = $db->DoQuery("SELECT count(*) AS total FROM {$prefix}hints");
		$row = $db->Do_Fetch_Assoc($query);

		//srand(date("dmYGi", time()));
		$hint_num = rand(0, $row['total'] - 1);

		$query = $db->DoQuery("SELECT text FROM {$prefix}hints ORDER BY id
				LIMIT $hint_num, 1");
		$row = $db->Do_Fetch_Assoc($query);

		$hint = "^__^ $hint_num";
		if ($row) {
			$hint = $hint_num." ".$row['text'];
		}

		
    echo '</head><body>
				<div id="innerdiv">
					<div id="hintdiv">';

		echo $hint;

		echo '			<br><br>
			<a href="#" onClick="javascript: window.self.close();">[Chiudi]</a>
			</div>
			</div>

			</body>
			</html>';


	}

?>
