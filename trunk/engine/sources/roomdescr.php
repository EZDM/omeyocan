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
?>
<?PHP

	function roomdescr_main(){
		global $x7s;
		
		$page='';
		
		$body = roomdescr();
			
			
		print_page($body);
	}
	
	
	function roomdescr(){
			global $txt, $x7c, $x7s, $print, $db, $prefix;
			if(!isset($_GET['room'])){
				die("Missing parameter");
			}

			$body ='';

			$query = $db->DoQuery("SELECT topic, background FROM {$prefix}rooms WHERE name='$_GET[room]' ");
			$row = $db->Do_Fetch_Assoc($query);

			if($row == null){
				die("Room $_GET[room] does not exists");
			}

			if($row['topic']==''){
				$body = "Non esiste la descrizione per questa stanza";
			}
			else{
				if($row['background']!=''){
					$body .= '<img src="'.$row['background'].'" />'."\n";
				}
				$description = $row['topic'];
				$description = render_weather($description);
				$body .= '<p id="descr">'.$description.'</p>';
			}

			return $body;
			
	}

  function take_random_item($probabilities, $type) {
    $prob = explode(",", $probabilities);
		$prev = 0;
		$count = 0;
		foreach($prob as $i) {
			if(!is_numeric($i))
				return "N/A";
      $prob[$count] = $prev + $i;
			if (($prev + $i) > 100)
				return "N/A";
			$prev = $prob[$count];
			$count++;
		}
		if ($prev != 100)
			return "N/A";

		$seed = date("W") . date("Y") . $type;
		srand($seed);
		$item = rand(0, 100);

		$count = 0;
		foreach($prob as $i) {
			print "$item <= $i ";
			if ($item <= $i)
				break;
			$count++;
		}

		return $count;
	}
	
  function render_weather($description) {
		$humidity = array();
		$humidity['N/A'] = 'N/A';
		$humidity[] = "Sereno (nessuna nube)"; 
		$humidity[] = "Coperto (temperatura -15°C";
		$humidity[] = "Pioviggine (< 1 mm ogni ora)";
		$humidity[] = "Pioggia debole (1/2 mm/h)"; 
		$humidity[] = "Pioggia moderata (2/6 mm/h)";
		$humidity[] = "Pioggia forte (> 6 mm/h)";
		$humidity[] = "Rovescio (> 10 mm/h ma limitato nella durata)";
		$humidity[] = "Nubifragio (> 30 mm/h)"; 

		$wind = array();
		$wind['N/A'] = 'N/A';
		$wind[] = "Nessun vento (0-5 Km/h)";
		$wind[] = "Brezza leggera (6-11 Km/h)";
		$wind[]	= "Vento (39-49Km/h)"; 
		$wind[] = "Vento forte (50-61 Km/h)"; 
		$wind[]	= "Burrasca (62-74 Km/h)";
		$wind[] = "Tempesta (89-102 Km/h)";
		$wind[] = "Fortunale (103-117 Km/h)";
		$wind[] = "Haboob (oltre 118Km/h)";

		$radiation = array(); 
		$radiation['N/A'] = 'N/A';
		$radiation[] = "Radiazione assente (0 mSv)";
		$radiation[] = "Radiazione lieve (radiografia <1 mSv)";
		$radiation[] = "Radiazione moderata (TAC 2/15 mSv)";
		$radiation[] = "Radiazione media (tomografia 10/20 mSv)";
		$radiation[] = "Radiazione alta (radioterapia 21/40 mSv)";
		$radiation[] = "Radiazione forte (alterazioni temporanee emoglobina (1 Sv)";
		$radiation[] = "Radiazione intensa nausea, perdita dei capelli, emorragie 2/5 Sv)";
		$radiation[] = "Radiazione nociva (morte nel 50% dei casi 4 Sv)";
		$radiation[] = "Radiazione letale (sopravvivenza improbabile 6 Sv)"; 

		$matches  = array();

		if(!preg_match("/%humidity:(.*)%/", $description, $matches))
			return $description;
		$description = preg_replace("/%humidity:(.*)%/", "",
				$description);
		$humidity_choice = $humidity[take_random_item($matches[1], 1)];

		if(!preg_match("/%wind:(.*)%/", $description, $matches))
			return $description;
		$description = preg_replace("/%wind:(.*)%/", "",
				$description);
		$wind_choice = $wind[take_random_item($matches[1], 2)];

		if(!preg_match("/%radiation:(.*)%/", $description, $matches))
			return $description;
		$description = preg_replace("/%radiation:(.*)%/", "",
				$description);
		$radiation_choice = $radiation[take_random_item($matches[1], 3)];

		$weather = '
			<p align="center">
			<table border="1">
			<tr>
			<th>UMIDITA\'</th>
			<th>VENTO</th>
			<th>RADIZIONE</th>
			</tr>

			<tr>
			<td>'.$humidity_choice.'</td>
			<td>'.$wind_choice.'</td>
			<td>'.$radiation_choice.'</td>
			</tr>

			</table>
			</p>';

		return $weather.$description;

	}

	function print_page($body){
		global $print,$x7c,$x7s;
		
		
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
		echo "<html dir=\"$print->direction\"><head><title>{$x7c->settings['site_name']} Descrizione stanza</title>";
		echo $print->style_sheet;
		echo $print->ss_mini;
		echo $print->ss_chatinput;
		echo $print->ss_uc;
		
		
		$mail_style = '
		<style type="text/css">
			#roomdescr{
				position: relative;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
			}

			#inner_roomdescr{
				position: relative;
				width: 100%;
				height: 100%;
				overflow: auto;
				text-align: center;
			}
			
		</style>
		';
		
		echo $mail_style;
		
		echo '</head><body>
 			<div id="roomdescr">
 				<div id="inner_roomdescr">
 			';
 			
		
		echo $body;
		echo '
			</div>
		</div>
		</body>
			</html>';
	}


?>
