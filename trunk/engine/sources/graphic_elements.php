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


  if($x7c->settings['panic']) {
		$mappa = "./graphic/01mappa_dark.jpg";
		$mappa_over = "./graphic/01mappa_dark_over.jpg";
		$bacheca = "./graphic/02bacheca_dark.jpg";
		$bacheca_over = "./graphic/02bacheca_dark_over.jpg";
		$presenti = "./graphic/03presenti_dark.jpg";
		$presenti_over = "./graphic/03presenti_dark_over.jpg";
		$scheda = "./graphic/04scheda_dark.jpg";
		$scheda_over = "./graphic/04scheda_dark_over.jpg";
    $logout_src="./graphic/06logout_dark.jpg";
    $logout_over_src="./graphic/06logout_dark_over.jpg";
		
		$posta_no = "./graphic/05postano_dark.gif";
		$posta_si = "./graphic/05postasi_dark.gif";
	}
  else {
		$mappa = "./graphic/01mappa.jpg";
		$mappa_over = "./graphic/01mappa_over.jpg";
		$bacheca = "./graphic/02bacheca.jpg";
		$bacheca_over = "./graphic/02bacheca_over.jpg";
		$presenti = "./graphic/03presenti.jpg";
		$presenti_over = "./graphic/03presenti_over.jpg";
		$scheda = "./graphic/04scheda.jpg";
		$scheda_over = "./graphic/04scheda_over.jpg";
    $logout_src="./graphic/06logout.jpg";
    $logout_over_src="./graphic/06logout_over.jpg";
		
		$posta_no = "./graphic/05postano.gif";
		$posta_si = "./graphic/05postasi.gif";
	}

?>
