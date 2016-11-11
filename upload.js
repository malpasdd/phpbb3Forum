/* sprawdza czy plik jest wybrany*/
function isPlik(plik) {
	return (plik) ? true : false;
}

/*sprawdza czy rodzaj wybranego pliku jest dozwolony*/
function sprawdzRozszerzenie(dozwolnePliki, nazwaPliku) {
	var ext = nazwaPliku.substring(nazwaPliku.lastIndexOf('.') + 1);
	return (dozwolnePliki.indexOf(ext.toLowerCase()) != -1)  ? true : false;
}

/*sprawdza rozmiar pliku dla prostego*/
function sprawdzRozmiar(rozmiar, plik) {
	return (plik.size < rozmiar) ? true : false;
}

/*sprawdza czy plik jest zdjeciem*/
function sprawdzCzyZdjecie(nazwaPliku) {
	var zdjecia = ["png", "gif", "jpeg", "jpg"];
	var fotoext = nazwaPliku.substring(nazwaPliku.lastIndexOf('.') + 1);
	
	return (zdjecia.indexOf(fotoext.toLowerCase()) != -1)  ? true : false;
}

/*funkcja walidujaca plik z wgrywania prostego*/
function walidacjaPlikuProste(inputPliku, rozmiar, dozwolnePliki) {
	wyczyscKomunikaty();
	var file = inputPliku.files[0];
	
	if(isPlik(file)) {
		var nazwaPliku = inputPliku.value;
		if(sprawdzRozszerzenie(dozwolnePliki, nazwaPliku)) {
			if(sprawdzRozmiar(rozmiar, file)) {
				komunikatWgrywanie();
				// atrapa zatrzymujaca forma, odblokowac w finalu
				// return false;
				return true;
			} else {
				komunikatZaDuzy();
				return false;
			}			
		} else {
			komuniaktZabronionyPlik();
			return false;
		}
	} else {
		komunikatBrakPliku();
		return false;
	}
	
	return false;
}

/*funkcja walidujaca plik z wgrywania zaawansowanego*/
function walidacjaPlikuZaawansowane(inputPliku, rozmiar, dozwolnePliki) {
	wyczyscKomunikaty();
	var file = inputPliku.files[0];
	
	if(isPlik(file)) {
		var nazwaPliku = inputPliku.value;
		if(sprawdzRozszerzenie(dozwolnePliki, nazwaPliku)) {
			if(sprawdzRozmiar(rozmiar, file)) {
				komunikatWgrywanie();
				// atrapa zatrzymujaca forma, odblokowac w finalu
				// return false;
				return true;
			} else {
				komunikatZaDuzy();
				return false;
			}			
		} else {
			komuniaktZabronionyPlik();
			return false;
		}
	} else {
		komunikatBrakPliku();
		return false;
	}
	
	return false;
}

/*sprawdza wybrana wielkosc i wyswietla komunikat*/
function sprawdzWyborWielkosci() {
	var myselect = document.getElementById("zmianarozmiaru");
	if(myselect.options[myselect.selectedIndex].value == 0) {
		document.getElementById('komrozdzielczosci').innerHTML = '';
		document.getElementById('komrozdzielczosci').innerHTML = '<div class="messages warning">Rozmiar nie b&#281;dzie zmieniany je&#380;eli jest mniejszy od 1920px!</div>';
	} else {
		document.getElementById('komrozdzielczosci').innerHTML = '';
	}
}

/*czysci komunikaty na stronie*/
function wyczyscKomunikaty() {
	document.getElementById('komunikaty').innerHTML = '';
	document.getElementById('trwawgrywanie').innerHTML = '';
}

/*komunikat o braku pliku*/
function komunikatBrakPliku() {
	document.getElementById('komunikaty').innerHTML = '<div class="messages error"> Wybierz plik!</div>';
}

/*komunikat o za duzym pliku*/
function komunikatZaDuzy() {
	document.getElementById('komunikaty').innerHTML = '<div class="messages error"> Wybrany plik jest za du&#380;y!</div>';
}

/*komunikat o trwajacym wgrywaniu pliku*/
function komunikatWgrywanie() {
	document.getElementById('plik').style.visibility = 'hidden';
	document.getElementById('trwawgrywanie').innerHTML = '<div class="messages status">Wybrano poprawny plik. Obecnie trwa wgrywanie, prosz&#281; czeka&#263;...</div>';
}

/*komunikat o zabronionym rozszerzeniu pliku*/
function komuniaktZabronionyPlik() {
	document.getElementById('komunikaty').innerHTML = '<div class="messages error"> Wybrano niedozwolony typ pliku!</div>';
}

