<?PHP

	function ability_script($max_ab){
		$body = 'function add(ab_name){
				var value = parseInt(document.sheet_form[ab_name].value);
				var xp = parseInt(document.sheet_form["xp"].value);

				if (xp >= value+1 && value < '.$max_ab.'){

					dep = document.sheet_form[ab_name+"_dep"].value;

					if(dep != ""){
						dep_act_val = parseInt(document.sheet_form[dep].value);
						if(dep_act_val > value){
							document.sheet_form[ab_name].value = value + 1;
							document.sheet_form["xp"].value = xp - (value + 1);
						}
						else{
							alert("Non puoi alzare \""+document.sheet_form[ab_name+"_name"].value+"\" senza avere almeno pari gradi in \""+document.sheet_form[dep+"_name"].value+"\"");
						}
					}
					else{
						document.sheet_form[ab_name].value = value + 1;
						document.sheet_form["xp"].value = xp - (value + 1);
					}

					do_form_refresh(ab_name);
				}
			}

			function sub(ab_name){
				var value = parseInt(document.sheet_form[ab_name].value);
				var min = parseInt(document.sheet_form[ab_name+"_min"].value);

				if (value > min){
					document.sheet_form[ab_name].value = value - 1;
					var xp = parseInt(document.sheet_form["xp"].value);
					document.sheet_form["xp"].value = xp + value;
					leafs = "";
					if(document.sheet_form[ab_name+"_dep"].value == ""){
						leafs = document.sheet_form[ab_name+"_leaf"].value;
					}

					if(leafs != ""){
						splitted = leafs.split("|");
						for (i in splitted){
							if(splitted[i]!=""){
								actual_value = parseInt(document.sheet_form[splitted[i]].value);
								final_value = parseInt(document.sheet_form[splitted[i]+"_min"].value);
								back_xp = 0;
								
								while(actual_value > final_value){
									back_xp += actual_value;
									actual_value--;
								}

								document.sheet_form[splitted[i]].value = document.sheet_form[splitted[i]+"_min"].value;
								xp = parseInt(document.sheet_form["xp"].value);

								document.sheet_form["xp"].value = xp + back_xp;

								do_form_refresh(splitted[i]);
							}
						}
					}
					do_form_refresh(ab_name);
				}

			}';

		return $body;
	}

?>