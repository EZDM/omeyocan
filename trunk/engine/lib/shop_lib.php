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

$GLOBALS['max_items'] = 50;
$GLOBALS['shopper'] = "_shopper_";
$GLOBALS['money_name'] = "Cogs";
$GLOBALS['money_group'] = 500;
$GLOBALS['money_group_size'] = 1;
$GLOBALS['base_money'] = 100000;
$GLOBALS['evaluate_cost'] = 10;
$GLOBALS['start_cogs'] = 30;

	function get_total_money() {
		global $db, $prefix, $money_name;
		$query_money = $db->DoQuery("
				SELECT SUM(uses) as cnt FROM {$prefix}objects
				WHERE name = '$money_name'
				AND owner <> '' 
				GROUP BY name;
				");

		$row = $db->Do_Fetch_Assoc($query_money);
		return $row['cnt'];

	}
	
	function get_total_user_money($pg, $only_equipped=true) {
		global $db, $prefix, $money_name;
		$more_query = '';
		if ($only_equipped)
			$more_query = "AND equipped = 1";

		$query_money = $db->DoQuery("
				SELECT SUM(uses) as cnt FROM {$prefix}objects
				WHERE name = '$money_name'
				AND owner = '$pg'
				$more_query
				GROUP BY name;
				");

		$row = $db->Do_Fetch_Assoc($query_money);
		return $row['cnt'];

	}

	function get_obj_availability($obj_name) {
		global $db, $prefix, $shopper;

		$query_obj = $db->DoQuery("
				SELECT count(*) as cnt FROM {$prefix}objects
				WHERE name = '$obj_name'
				AND owner = '$shopper'
				");
		$row = $db->Do_Fetch_Assoc($query_obj);
		return $row['cnt'];
	}

	function get_obj_name_and_uses($obj, &$obj_name, &$obj_uses) {
		global $db, $prefix;
		$query = $db->DoQuery("SELECT name, uses FROM {$prefix}objects
				WHERE id='$obj'");

		$row = $db->Do_Fetch_Assoc($query);

		if ($row) {
			$obj_name = $row['name'];
			$obj_uses = $row['uses'];
		}

	}

	function calculate_obj_value($obj, $seller, $detailed=false) {
		global $db, $prefix, $money_name, $shopper, $base_money;

		$obj_name = '';
		$obj_remain_uses = 0;
		get_obj_name_and_uses($obj, $obj_name, $obj_remain_uses);

		$availability = get_obj_availability($obj_name);

		$query_obj = $db->DoQuery("
				SELECT base_value, uses FROM {$prefix}objects
				WHERE name = '$obj_name'
				AND owner = ''");
		$row = $db->Do_Fetch_Assoc($query_obj);
		$base_value = $row['base_value'];
		$base_uses = $row['uses'];

		if ($base_value <= 0)
			return -1;

		if ($seller != $shopper)
			$availability++;
		
		$total_money = get_total_money();

		$inflaction_factor = $total_money / $base_money;
		$avail_factor = 1;

		$use_factor = $obj_remain_uses / $base_uses; 
		if ($use_factor != 0) {
			if ($base_uses <= 0)
				$use_factor = 1;
			else if ($obj_remain_uses < 0) //This object's version is infinite 
				$use_factor = 4;
		}


		if ($availability < 2) {
			$avail_factor = 3;
		} else if ($availability < 3) {
			$avail_factor = 2;
		} else if ($availability < 6) {
			$avail_factor = 1.5;
		}

		$value = $base_value * $avail_factor * $inflaction_factor *
			$use_factor;

		if ($seller != $shopper)
			$value *= 0.4;

		$value = round($value);

		if ($detailed) {
			return "$value<br>
				Tasso di disponibilita': $avail_factor<br>
				Tasso di inflazione: $inflaction_factor<br>
				Tasso di usura: $use_factor";
		}
		return $value;
	}

	function get_evaluation($obj) {
		global $evaluate_cost, $shopper, $x7s;
		$retval = pay($evaluate_cost, $x7s->username, $shopper, true);
		$name = '';
		$uses = '';
		get_obj_name_and_uses($obj, $name, $uses);
		
		if (!$retval){
			pay($evaluate_cost, $x7s->username, $shopper);
			$message = "L'oggetto $name ha $uses rimasti<br>";
			if ($uses < 0)
				$message = "L'oggetto $name ha usi infiniti<br>";
		}
		else {
			$message = "Non hai soldi per valutare l'oggetto $name<br>";
		}
		return $message;
	}

	function sell_obj($obj, $pg_sell, $pg_buy) {
		global $db, $prefix, $money_name, $shopper;

		$value = calculate_obj_value($obj, $pg_sell);
		get_obj_name_and_uses($obj, $obj_name, $uses);
		if ($value <= 0)
			return "Spiacente, non so valutare questo oggetto<br>";

		// Check if money transaction is possible
		$retval = pay($value, $pg_buy, $pg_sell, $check_only=true);
		if ($retval) {
			return $retval;
		}

		// Check if object move is possible
		$retval = move_obj($obj, $pg_sell, $pg_buy, $check_only=true);
		if ($retval) {
			return $retval;
		}
		
		pay($value, $pg_buy, $pg_sell);
		move_obj($obj, $pg_sell, $pg_buy);
	
		include_once('./lib/alarms.php');
		record_sell($pg_sell, $pg_buy, $obj_name);
		return "Transazione eseguita con successo<br>";

	}

	function move_obj($obj, $from, $to, $check_only=false) {
		global $db, $prefix, $shopper;

		$query_obj = $db->DoQuery("
				SELECT id, name, size, expire_span, shop_return FROM {$prefix}objects
				WHERE id='$obj'
				AND owner='$from'
				AND equipped='1'");

		$row_obj = $db->Do_Fetch_Assoc($query_obj);

		if (!$row_obj)
			return "Oggetto non posseduto/equipaggiato<br>";

		include_once('./lib/sheet_lib.php');
		
		if ($from != $shopper) {
			if (get_user_space($from) + $row_obj['size'] < 0) {
				return "Spazio non sufficiente per disequipaggiare l'oggetto ".
					"$row_obj[name]<br>";
			}
		}

		// Shopper has infinite space
		if ($to != $shopper) {
			if (get_user_space($to) - $row_obj['size'] < 0) {
				return "Spazio non sufficiente per equipaggiare l'oggetto ".
					"$row_obj[name]<br>";
			}

			if ($row_obj['expire_span'] > 0) {
				$expire_time = time() + $row_obj['expire_span'] * 60;
				if(!$check_only) {
					$db->DoQuery("INSERT INTO {$prefix}temp_obj 
							(id, expire_time, shop_return)
							VALUES
							('$row_obj[id]', '$expire_time', '$row_obj[shop_return]')");
				}
			}
		}

		if ($check_only)
			return;
		
		$db->DoQuery("
				UPDATE {$prefix}objects
				SET owner='$to'
				WHERE id='$obj'");

	}

	function pay($qty, $from, $to, $check_only=false, $only_equipped=true) {
		global $db, $prefix, $money_group, $money_group_size, $money_name, $shopper;

		$space_required = (($qty / $money_group) + 1) * $money_group_size;

		// Check if buyer own money 
		$money = get_total_user_money($from, $only_equipped);	
		if ($money < $qty) {
			return "Denaro non disponibile<br>";
		}
	
		include_once('./lib/sheet_lib.php');
		// Shopper has infinite space
		if ($to != $shopper) {
			if (get_user_space($to) - $space_required < 0)
				return "Spazio non sufficiente per ricevere i soldi<br>";
		}

		if ($check_only)
			return;

		remove_money($qty, $from);
		assign_money($qty, $to);

		include_once("./lib/alarms.php");
		record_payment($from, $to, $qty);
		return "Pagamento effettuato<br>";
	}

	function assign_money($qty, $pg) {
		global $db, $prefix, $money_name, $money_group, $money_group_size, $shopper;
		
		// Shopper does not split money
		if ($pg == $shopper) {
			$query = $db->DoQuery("SELECT count(*) AS cnt
					FROM  {$prefix}objects
					WHERE name = '$money_name'
					AND owner = '$pg'");
			$row = $db->Do_Fetch_Assoc($query);

			if ($row['cnt']) {
				$db->DoQuery("UPDATE {$prefix}objects
					SET uses = uses + $qty
					WHERE name = '$money_name'
					AND owner = '$pg'");
			}
			else {
				$query_money = $db->DoQuery("
						SELECT * FROM {$prefix}objects
						WHERE name = '$money_name'
						AND owner = ''");
				$row_money = $db->Do_Fetch_Assoc($query_money);

				$db->DoQuery("INSERT INTO {$prefix}objects
					(name, description, owner, uses, image_url, equipped, size)
					VALUES ('{$row_money['name']}',
						'{$row_money['description']}',
						'$pg',
						'$qty',
						'{$row_money['image_url']}',
						'1',
						'$money_group_size')");	

			}
			return;
		}

		$query_money = $db->DoQuery("
				SELECT * FROM {$prefix}objects
				WHERE name = '$money_name'
				AND owner = ''");
		$row_money = $db->Do_Fetch_Assoc($query_money);


		$to_move = $qty;
		while ($to_move > 0) {
			$assign = $to_move;
			if ($to_move > $money_group)
				$assign = $money_group;
			
			$db->DoQuery("INSERT INTO {$prefix}objects
					(name, description, owner, uses, image_url, equipped, size)
					VALUES ('{$row_money['name']}',
						'{$row_money['description']}',
						'$pg',
						'$assign',
						'{$row_money['image_url']}',
						'1',
						'$money_group_size')");	

			$to_move -= $money_group;
		}
	}
	
  function split_money($qty, $pg, $group) {
		global $db, $prefix, $money_name, $money_group, $money_group_size, $shopper;

		// Shopper does not split money
		if ($pg == $shopper) {
			$db->DoQuery("UPDATE {$prefix}objects
					SET uses = uses - $qty
					WHERE name = '$money_name'
					AND owner = '$pg'");
			return;
		}

		$query_money = $db->DoQuery("
				SELECT * FROM {$prefix}objects
				WHERE name = '$money_name'
				AND id = '$group'
				AND owner = '$pg'");
		$row_money = $db->Do_Fetch_Assoc($query_money);
		if (!$row_money)
			return;

		$to_move = $qty;
		if ($to_move > 0) {
			$assign = $to_move;
			
			if ($to_move >= $row_money['uses'])
				return "Quantita' troppo elevata";

			$db->DoQuery("UPDATE {$prefix}objects
					SET uses = uses - $assign
					WHERE id = '{$row_money['id']}'");
		}

		assign_money($qty, $pg);
	}
	
	function remove_money($qty, $pg) {
		global $db, $prefix, $money_name, $money_group, $money_group_size, $shopper;

		// Shopper does not split money
		if ($pg == $shopper) {
			$db->DoQuery("UPDATE {$prefix}objects
					SET uses = uses - $qty
					WHERE name = '$money_name'
					AND owner = '$pg'");
			return;
		}

		$query_money = $db->DoQuery("
				SELECT * FROM {$prefix}objects
				WHERE name = '$money_name'
				AND owner = '$pg'
				AND equipped = 1");
		$row_money = $db->Do_Fetch_Assoc($query_money);
		if (!$row_money)
			return;
		$to_move = $qty;
		while ($to_move > 0) {
			$assign = $to_move;
			
			if ($to_move >= $row_money['uses']) {
				$assign = $row_money['uses'];
				$db->DoQuery("DELETE FROM {$prefix}objects
						WHERE id='{$row_money['id']}'");
				
				$row_money = $db->Do_Fetch_Assoc($query_money);
			}
			else {
				$db->DoQuery("UPDATE {$prefix}objects
						SET uses = uses - $assign
						WHERE id = '{$row_money['id']}'");
			}
			
			$to_move -= $assign;
		}

	}

	function group_money($pg) {
		global $db, $prefix, $money_name, $shopper;

		// Shopper does not split money
		if ($pg == $shopper)
			return;
		
		$qty = get_total_user_money($pg);

		$db->DoQuery("DELETE FROM {$prefix}objects
				WHERE name = '$money_name'
				AND owner = '$pg'
				AND equipped = '1'");

		assign_money($qty, $pg);

	}

?>
