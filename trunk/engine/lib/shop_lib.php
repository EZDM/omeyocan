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

	function calculate_obj_value($obj) {
		$value = 20;

		// la formula deve tenere conto di
		//  Inflazione (tot denaro circolante)
		//  disponibilita'/rarita' dell'oggetto
		//  usi rimasti dell'oggetto rispetto a uno base
		// Valore negativo se non si puo' valutare l'oggetto
		return $value;
	}

	function sell_obj($obj, $pg_from, $pg_to) {
		global $db, $prefix, $money_name, $shopper;

		// Check seller own the object
		$db->DoQuery("
				SELECT count(*) as cnt FROM {$prefix}objects
				WHERE owner = '$pg_from'
				AND name = '$obj'");
		$row = $db->Do_Fetch_Assoc($query);

		if ($row['cnt'] <= 0) {
			return "Oggetto non disponibile";
		}

		$value = calculate_obj_value($obj);
		if ($value < 0)
			return "Spiacente, non so valutare questo oggetto";

		if ($pg_to == $shopper)
			$value *= 0.4;

		$value = round($value);
		
		// Check if buyer own money 
		$db->DoQuery("
				SELECT sum(uses) as cnt FROM {$prefix}objects
				WHERE owner = '$pg_to'
				AND name = '$money_name'
				AND equipped = '1'
				GROUP BY name");
		$row = $db->Do_Fetch_Assoc($query);

		if ($row['cnt'] < $value) {
			return "Denaro non disponibile";
		}

		$retval = move_obj($obj, $pg_from, $pg_to);

		if ($retval)
			return $retval;
		
		pay($value, $pg_from, $pg_to);
		
		return "Transazione eseguita con successo";

	}

	function move_obj($obj, $from, $to) {
		global $db, $prefix;

		$query_user = $db->DoQuery("
				SELECT spazio FROM {$prefix}users
				WHERE username = '$to'");
		$row_user = $db->Do_Fecth_Assoc($query_user);

		$query_obj = $db->DoQuery("
				SELECT size FROM {$prefix}objects
				WHERE id='$obj'
				AND owner='$from'");

		$row_obj = $db->Do_Fetch_Assoc($query_obj);

		if (!$row_obj)
			return "Oggetto non posseduto";

		if ($row_obj['size'] > $row_user['spazio']) {
			return "Spazio non sufficiente per equipaggiare l'oggetto";
		}
		
		$db->DoQuery("
				UPDATE {$prefix}objects
				SET owner='$to'
				WHERE id='$obj'");

		include_once('./lib/sheet_lib.php');
		recalculate_space($from);
		recalculate_space($to);
	}

	function pay($qty, $from, $to) {
		// must handle grouping of cogwheels	
	}

?>
