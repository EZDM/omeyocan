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

include_once("./lib/shop_lib.php");

function shop_main(){
		global $x7s, $db, $x7c, $prefix;
		$page='';

		$body = show_shop();

		print_shop($body,$page);
	}

function get_object_list($user, $start_from) {
	global $db, $prefix, $max_items, $shopper, $money_name, $x7c;
	$body = '<table width=100% border=1 rules="rows">';
	$trade_action = "sell[]";
	$start_limit = ($start_from - 1) * $max_items;

	$category_query="";
	if (isset($_GET['category']) && $_GET['category'])
		$category_query = "AND category='{$_GET['category']}'";

	if ($user == $shopper)
		$trade_action = "buy[]";

	if ($user == $shopper) {
		$query = $db->DoQuery("
				SELECT *, count(*) as qty FROM {$prefix}objects
				WHERE owner = '$user'
				AND name <> '$money_name'
				$category_query
				GROUP BY name, uses
				ORDER BY name
				LIMIT $start_limit, $max_items");
	}
	else {
		$query = $db->DoQuery("
				SELECT * FROM {$prefix}objects
				WHERE owner = '$user'
				AND name <> '$money_name'
				AND equipped = '1'
				ORDER BY name
				LIMIT $start_limit, $max_items");
	}

	$tot_money = get_total_user_money($user);
	if ($user != $shopper)
		$body .= "<tr><td></td><td>Totale $money_name: $tot_money</td></tr>";

	while ($row = $db->Do_Fetch_Assoc($query)) {
		if ($x7c->permissions['write_master'])
			$valore = calculate_obj_value($row['id'], $user, true);
		else
			$valore = calculate_obj_value($row['id'], $user);

		$size = "__Dimensione Errata__";
		switch ($row['size']) {
			case 0:
				$size = "Minuscola";
				break;
			case 1:
				$size = "Piccola";
				break;
			case 2:
				$size = "Media";
				break;
			case 5:
				$size = "Grande";
				break;
			case -1:
				$size = "Capienza piccola";
				break;
			case -2:
				$size = "Capienza media";
				break;
			case -5:
				$size = "Capienza grande";
				break;
		}

		if ($valore <= 0)
			$valore = "Senza valore";
		$body .= '
			<tr>
			<td>
			<input type="checkbox" name="'.$trade_action.'"
			value="'.$row['id'].'">
			</td>
			<td>
			<img width=100 height=100 src="'.$row['image_url'].'" '. 
			'align="left">
			<b>'.$row['name'].'</b><br>
			Valore: '.$valore.'<br>
			Dimensione: '.$size.'
			<p>'.$row['description'].'</p>
			</td>
			</tr>';
	}

	$body .= "</table>";

	return $body;
}

function get_navigator($user) {
	global $db, $prefix, $max_items, $shopper, $money_name;
	$body = '';
	$url_add = '';
	$url_base = '';
	$cur_start = 1;

	if (!isset($_GET['pg_start']) || $_GET['pg_start'] <= 0
			|| !is_numeric($_GET['pg_start']))
		$_GET['pg_start'] = 1;
	if (!isset($_GET['shop_start']) || $_GET['shop_start'] <=0
			|| !is_numeric($_GET['shop_start']))
		$_GET['shop_start'] = 1;

	if ($user == $shopper) {
		$url_add .= "&pg_start={$_GET['pg_start']}";
		$url_base = "shop_start";
		$cur_start = $_GET['shop_start'];
	}
	else {
		$url_add .= "&shop_start={$_GET['shop_start']}";
		$url_base= "pg_start";
		$cur_start = $_GET['pg_start'];
	}

	$category_query="";
	if (isset($_GET['category']) && $_GET['category']) {
		$category_query = "AND category='{$_GET['category']}'";
		$url_add .= "&category=$_GET[category]";
	}

	if ($user == $shopper) {
		$query = $db->DoQuery("
				SELECT count(DISTINCT name) as cnt 
				FROM {$prefix}objects
				WHERE owner = '$user'
				AND name <> '$money_name'
				$category_query");
	}
	else {
		$query = $db->DoQuery("
				SELECT count(*) as cnt 
				FROM {$prefix}objects
				WHERE owner = '$user'
				AND name <> '$money_name'
				AND equipped = '1'");
	}

	$row = $db->Do_Fetch_Assoc($query);
	$total_obj = $row['cnt'];
	$pages = ($total_obj / $max_items) + 1;

	if ($pages > 1) {
		$body .= '<div id="navigator">';

		for ($i = 1; $i <= $pages; $i++) {
			if ($i != $cur_start) {	
				$body .= '<a href=index.php?act=shop'.$url_add.'&'.$url_base.'='.$i.'>'.
					$i.'</a> ';
			}
			else {
				$body .= '<b><a href=index.php?act=shop'.$url_add.'&'.$url_base.'='.$i.'>['.
					$i.']</a></b> ';

			}
		}

		$body .= '</div>';
	}

	return $body;

}

function show_shop() {
	global $x7s, $shopper, $db, $prefix, $evaluate_cost;
	$body = '';
	$retval = '';
	$evaluation = '';

	if (!isset($_POST['evaluate']))
		$_POST['evaluate'] = 0;

	if (isset($_POST['sell'])) {
		foreach ($_POST['sell'] as $obj) {
			if (!$_POST['evaluate'])
				$retval .= sell_obj($obj, $x7s->username, $shopper);
			else {
				$evaluation .= get_evaluation($obj);
			}
		}
	}
	if (isset($_POST['buy'])) {
		foreach ($_POST['buy'] as $obj)
			$retval .=	sell_obj($obj, $shopper, $x7s->username);
	}

	$player_nav = get_navigator($x7s->username);
	$player_list = $player_nav;
	$player_list .= get_object_list($x7s->username, $_GET['pg_start']);
	$player_list .= $player_nav;

	if (!$_POST['evaluate']) {
		$shopper_nav = get_navigator($shopper);
		$shopper_list = $shopper_nav;
		$shopper_list .= get_object_list($shopper, $_GET['shop_start']);
		$shopper_list .= $shopper_nav;
	}
	else {
		$shopper_list = $evaluation;
		$shopper_list .= "<p><a href='./index.php?act=shop'>
			[Torna all'elenco oggetti]</a></p>";
	}

	$body .= '<script language="javascript" type="text/javascript">
		function send_evaluate() {
			document.forms["sell"].evaluate.value = 1;
			document.forms["sell"].submit();
		}

	function close_err(){
		document.getElementById("popup").style.visibility="hidden";
	}

	function category_change(elem) {
		query = "&category=" + elem.options[elem.selectedIndex].value;
		window.location.href = "./index.php?act=shop&pg_start='.
			$_GET['pg_start'].'" + query;
	}
	</script>';

	if($retval!=''){
		$body .= '<div id="popup" >'.$retval.'
			<br><br><input name="ok" type="button" class="button" value="OK"'.
			'onClick="javascript: close_err(); ">
			</div>';
	}

	$categories = '';
	$query = $db->DoQuery("SELECT DISTINCT category 
			FROM {$prefix}objects
			ORDER BY category");
	while ($row = $db->Do_Fetch_Assoc($query)) {
		if ($row['category']) {
			$categories .= "<option value=\"{$row['category']}\"";
			if (isset($_GET['category']) &&
					$_GET['category'] == $row['category']) {
				$categories .= " selected";
			}
			$categories .= ">{$row['category']}</option>";
		}
	}

	$body .= '
		<div id="player">
		<form action="./index.php?act=shop" method="post" name="sell">
		<input type="hidden" name="evaluate" value="0">
		<div id="player_list">
		__player_list__		
		</div>
		<div id="player_buttons">
		<input class="button" type="submit" value="Vendi">
		<input class="button" type="button" 
		value="Valuta usi rimanenti (costo: '.$evaluate_cost.')"
		onClick="javascript: send_evaluate();">
		</div>
		</form>
		</div>
		<div id="shopper">
		<form action="./index.php?act=shop" method="post" name="buy">
		<div id="shopper_list">
		__shopper_list__		
		</div>
		<div id="shopper_buttons">
		<input class="button" type="submit" value="Compra">
		Categoria:
		<select class="button" onChange="javascript: category_change(this);">
		<option value="">Tutto</option>
		'.$categories.'
		</select>
		</div>
		</form>
		</div>
		</div>
		';

	$body = preg_replace("/__player_list__/", $player_list, $body);
	$body = preg_replace("/__shopper_list__/", $shopper_list, $body);

	return $body;
}

function print_shop($body){
	global $print,$x7c;

	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
	echo "<html dir=\"$print->direction\"><head>
		<title>{$x7c->settings['site_name']} -- Negozio</title>";
	echo $print->style_sheet;
	echo $print->ss_mini;
	echo $print->ss_chatinput;
	echo $print->ss_uc;

	echo '
		<style type="text/css">
#shop {
	position: absolute;
background: url(./graphic/sfondonegozio.jpg);
top: 0;
left: 0;
width: 800px;
height: 720px;
				text-align: center;
}
#player {
float: left;
width: 380px;
height: 500px;
}
#player_buttons {
float: left;
width: 370px;
			 text-align: center;
}
#player_list {
height: 470px;
overflow: auto;
}
#shopper {
float: right;
width: 380px;
height: 500px;
}
#shopper_buttons {
float: right;
width: 370px;
			 text-align: center;
}
#shopper_list {
height: 470px;
overflow: auto;
}
#navigator {
	text-align: center;
}
#popup {
	text-align: center;
	background-color: lightyellow;
color: #650000;
			 font-weight: bold;
			 font-size: 10pt;
top: 30%;
width: 60%;
			 margin-left: 20%;
			 margin-right: 20%;
position: absolute;
padding: 5px;
border: 3px dashed red;
				text-decoration: none;
}


</style>
';

echo '</head><body>
<div id="shop">
<div id="shop_head">
<img src="./graphic/shop_head.gif">
</div>
';

echo $body;

echo '</div>
</body>
</html>';
}

function checkIfMaster(){
	global $x7s, $x7c;

	$value = $x7c->permissions['admin_panic'];

	return $value;
}

?>
