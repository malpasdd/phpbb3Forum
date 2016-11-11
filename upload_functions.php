<?php

define('PODFORAK_UPLOAD_TABLE', 'phpbb_podforak_upload');

function dozwolone_pliki() {
    global $dozowlone_pliki;
    return str_replace('"', '', $dozowlone_pliki);
}

/**
 * wyswietlanie nawigacji
 * @param unknown_type $miejsce - miejsce gdzie znajduje sie uzytkownik
 * @return string - htmlnawigacji
 */
function wyswietl_nawigacje($miejsce) {
    global $theme, $lang, $board_config, $level;

    $nawigacja = '<script type="text/javascript" src="upload/wymagane/jQuery.js"></script>';

    $nawigacja .= '<a class="nav" href="index.php">' . sprintf($lang['Forum_Index'], $board_config['sitename']) . '</a> &#187; ';
    $nawigacja .= '<a class="nav" href="upload.php">Wgrywanie plik&#243;w</a> &#187; ';
    if ($miejsce == 'zaawansowane') {
        $nawigacja .= '<a class="nav" href="upload.php?action=zaawansowane">Wgrywanie zaawansowane</a>';
    } else if ($miejsce == 'mojepliki') {
        $nawigacja .= '<a class="nav" href="upload.php?action=mojepliki">Moje pliki</a>';
    } else if ($miejsce == 'upload' && $level == '2') {
        $nawigacja .= '<a class="nav" href="upload.php?action=zaawansowane">Wyniki wgrywania</a>';
    } else if ($miejsce == 'upload' && $level == '1') {
        $nawigacja .= '<a class="nav" href="upload.php">Wyniki wgrywania</a>';
    } else if ($miejsce == 'szukaj') {
        $nawigacja .= 'Szukaj';
    } else if ($miejsce == 'admin') {
        $nawigacja .= '<a class="nav" href="upload.php?action=admin">Administracja</a>';
    } else if ($miejsce == 'pomoc') {
        $nawigacja .= '<a class="nav" href="upload.php?action=pomoc">Pomoc</a>';
    } else {
        $nawigacja .= '<a class="nav" href="upload.php">Wgrywanie proste</a>';
    }

    return $nawigacja;
}

/**
 * wyswietalanie formularza wgrywania prostego
 * @return string - htmlformularza
 */
function wyswietl_proste() {
    global $dozowlone_pliki;
    global $max_rozmiar;
//    var_dump($max_rozmiar);
    $wysw = $max_rozmiar / 1048576;
    $new_value = round($wysw, 2);

    $zawartosc = "
				<script type=\"text/javascript\">
					function insert(){
						var dozwolnePliki = [" . '' . $dozowlone_pliki . '' . "];
						var rozmiar = " . $max_rozmiar . ";
						return walidacjaPlikuProste(document.getElementById('plik'), rozmiar, dozwolnePliki);    
					};
				</script>
				";
    $zawartosc .= '
				<div id="komunikaty"></div>
				<form action="?action=upload&level=1" method="POST" ENCTYPE="multipart/form-data" id="wgrajform" onSubmit="return insert();">
				<table cellspacing="1" cellpading="0" class="forumline" align="center"  width="100%" style="margin-top: 0px;">
				<tr>
					<th style="padding-left: 7px;" width="100%" colspan="2">
						Wgrywanie proste
					</th>
				</tr>
				<!--<tr>
					<td class="row1" colspan="2" style="height: 15px;">
						<span class="gensmall">
							
						</span>
					</td>
				</tr>-->
				<tr>
					<td class="row1" style="padding: 7px; width: 22%;"><b> Wybierz plik z dysku:</b></td>
					<td class="row2"  style="padding: 20px 10px; width: 78%;">
						<div id="trwawgrywanie">
						</div>
						<input type="file" id="plik" name="plik" class="post" onclick="wyczyscKomunikaty();"/><br />
					</td>
				</tr>
				<tr>
					<td class="row1" style="padding: 7px;"><b> Dozwolone pliki:</b></td>
					<td class="row2" style="padding: 7px;">' . dozwolone_pliki() . '</td>
				</tr>
				<tr>
					<td class="row1" style="padding: 7px;"><b>Max rozmiar pliku:</b></td>
					<td class="row2" style="padding: 7px;">';
    $zawartosc .= $new_value . ' MB</td>
				</tr>
				<tr>
					<td class="catBottom" align="center" colspan="2">
						<input type="submit" class="liteoption" value="Wgraj plik"/> 
					</td>
				</tr>
				</table>
				</form>';
    return $zawartosc;
}

/**
 * wyswietalnie formularza wgrywania zaawansowanego
 * @return string - html formularza
 */
function wyswietl_zaawansowane() {
    global $max_zaawansowana;
    global $dozowlone_pliki;
    $wysw = $max_zaawansowana / 1048576;

    $new_value = round($wysw, 2);

    $zawartosc = "
				<script type=\"text/javascript\">
					function insert(){
						var dozwolnePliki = [" . '' . $dozowlone_pliki . '' . "];
						var rozmiar = " . $max_zaawansowana . ";
						return walidacjaPlikuZaawansowane(document.getElementById('plik'), rozmiar, dozwolnePliki); 
					};
								
					function komRozdzielczosci() {
						sprawdzWyborWielkosci();		
					};
				</script>
				";
    $zawartosc .= '
				<div id="komunikaty"></div>
				<form action="?action=upload&level=2" method="POST" ENCTYPE="multipart/form-data" id="wgrajform" name="wgrajform" onSubmit="return insert();">
				<table cellspacing="1" cellpading="0" class="forumline" align="center"  width="100%" style="margin-top: 0px;">
				<tr>
					<th style="padding-left: 7px;" width="100%" colspan="2">
						Wgrywanie proste
					</th>
				</tr>
				<!--<tr>
					<td class="row1" colspan="2" style="height: 15px;">
						<span class="gensmall">
				
						</span>
					</td>
				</tr>-->
				<tr>
					<td class="row1" style="padding: 7px; width: 22%;"><b> Wybierz plik z dysku:</b></td>
					<td class="row2"  style="padding: 20px 10px; width: 78%;">
						<div id="trwawgrywanie">
						</div>
						<input type="file" id="plik" name="plik" class="post" onclick="wyczyscKomunikaty();"/><br />
					</td>
				</tr>
				<tr>
					<td class="row1" style="padding: 7px;"><b>Dodaj znak wodny:</b></td>
					<td class="row2" style="padding: 7px;">
						<select name="dodac">	
							<option value="1">Tak</option>
							<option value="0">Nie</option>
						</select>
						
					</td>
				</tr>
				<tr>
					<td class="row1" style="padding: 7px;"><b>Umiejcowienie znaku wodnego:</b></td>
					<td class="row2" style="padding: 7px;">
						<select name="miejsceznaku">
							<option value="pd">Prawy dolny r&#243;g</option>
							<option value="pg">Prawy g&#243;rny r&#243;g</option>
							<option value="ld">Lewy dolny r&#243;g</option>
							<option value="lg">Lewy g&#243;rny r&#243;g</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="row1" style="padding: 7px;"><b>Zmie&#324; rozmiar obrazu:</b></td>
					<td class="row2" style="padding: 7px;" valign="middle">
						<select name="zmianarozmiaru" id="zmianarozmiaru" onchange="komRozdzielczosci()">	
							<option value="0">Nie zmieniaj rozmiaru</option>
							<option value="320">320px</option>
							<option value="640">640px</option>
							<option value="800">800px</option>
							<option value="1024">1024px</option>
							<option value="1280" selected="selected">1280px</option>
							<option value="1600">1600px</option>
							<option value="1920">1920px</option>
						</select>
						<div id="komrozdzielczosci"></div>
					</td>
				</tr>
				<tr>
					<td class="row1" style="padding: 7px;">
						<b>Tytu&#322; pliku:</b>
					</td>
					<td class="row2" style="padding: 7px;">
						<input class="post" type="text" size="42" name="pic_title">
					</td>
				</tr>
				<tr>
					<td class="row1" valign="top" style="padding: 7px;">
						<b>Opis pliku:</b><br />
						<span class="gensmall">
						(Nie jest wymagany)
						</span>
					</td>
					<td class="row2" style="padding: 7px;">
						<textarea class="post" size="40" name="pic_desc" rows="4" cols="40"></textarea>
					</td>
				</tr>
				<tr>
					<td class="row1" style="padding: 7px;"><b> Dozwolone pliki:</b></td>
					<td class="row2" style="padding: 7px;">' . dozwolone_pliki() . '</td>
				</tr>
				<tr>
					<td class="row1" style="padding: 7px;"><b>Max rozmiar pliku:</b></td>
					<td class="row2" style="padding: 7px;">';
    $zawartosc .= $new_value . ' MB</td>
				</tr>
				<tr>
					<td class="catBottom" align="center" colspan="2">
						<input type="submit" class="liteoption" value="Wgraj plik"/>
					</td>
				</tr>
				</table>
				</form>';
    return $zawartosc;
}

/**
 * dodatkowa walidacja rozszerzenia
 * na wszelki wypadek gdyby w js cos poszlo nie tak
 * lub ktos probowal go oszukac
 * @param unknown_type $nazwa - nazwa pliku
 */
function walidacja_rozszerzenia($nazwa) {
    global $dozowlone_pliki;
    $rozszerzenia = explode(',', str_replace('"', '', $dozowlone_pliki));
    $ext = pathinfo($nazwa, PATHINFO_EXTENSION);

    return contains($ext, $rozszerzenia);
}

/**
 * sprawdza czy fraza odpowiada elemntowi arraya
 * @param unknown_type $str - fraza
 * @param array $arr - array
 * @return boolean
 */
function contains($str, array $arr) {
    foreach ($arr as $a) {
        if (stripos(strtolower(trim($str)), strtolower(trim($a))) !== false) {
            return true;
        }
    }
    return false;
}

/**
 * dodaje wpis wgrywanego pliku do bazy danych
 * @param unknown_type $nazwa_pliku - nazwa pliku
 * @param unknown_type $tytul - tytul z formularza
 * @param unknown_type $opis - opis z formularza
 * @param unknown_type $dodano - zrzut daty dodania
 * @return boolean - flaga poprawnosci zapisu
 */
function dodaj_wpis_bazy($nazwa_pliku, $tytul, $opis, $dodano, $miniatura) {
    global $usdata;
    global $db;
    $sql = "INSERT INTO " . PODFORAK_UPLOAD_TABLE . " (miniatura, nazwa_pliku, tytul, opis, uid, dodany)
				VALUES ('" . $miniatura . "', '" . $nazwa_pliku . "', '" . $tytul . "', '" . $opis . "', " . $usdata['user_id'] . ", " . $dodano . ")";

    $result = $db->sql_query($sql);

    return result;
}

/**
 * pobiera wpisy z tabeli plikow uzytkownika
 * @global type $db
 * @param type $uid
 * @return string
 */
function pobierz_wpisy_usera($uid) {
    global $db;
    $pliki_usera = array();
    $sql = "SELECT * FROM " . PODFORAK_UPLOAD_TABLE . " WHERE uid = " . $uid . " ORDER BY dodany DESC";
    if (!($result = $db->sql_query($sql))) {
        return '<tr><td class="row1" align="center" colspan="4">Brak wynik&#243;w</td></tr>';
    } else {
        while ($row = $db->sql_fetchrow($result)) {
            $pliki_usera[] = $row;
        }
        return $pliki_usera;
    }
}

/**
 * zwraca naglowek tabeli listy plikow
 * @param type $title
 * @return string
 */
function lista_plikow_naglowek($title) {
    $zawartosc = '<table cellspacing="1" cellpading="0" class="forumline" align="center"  width="100%" style="margin-top: 0px;">
                    <tr>
                            <th style="padding-left: 7px;" width="100%" colspan="5">
                                    ' . $title . '
                            </th>
                    </tr>
                    <tr>
                            <td class="catBottom" align="center">#</td>
                            <td class="catBottom" align="center">Plik</td>
                            <td class="catBottom" align="center">Opis</td>
                            <td class="catBottom" align="center">Dodany</td>
                            <td class="catBottom" align="center">Akcje</td>
                    </tr>
                    ';
    return $zawartosc;
}

/**
 * zwraca stopke listy plikow
 * @return string
 */
function lista_plikow_stopka() {
    $zawartosc = '<tr>
                            <td class="catBottom" align="center" colspan="5">
                            </td>
                    </tr>
            </table>';
    return $zawartosc;
}

/**
 * dodaje wiersz listy plikow
 * @param type $plik
 * @param type $index
 * @param type $zarzadzanie
 * @return string
 */
function lista_plikow_wiersz($plik, $index, $zarzadzanie = false, $kto_dodal = false) {
    $zawartosc = "";
    $style = "";
    if ($plik['prywatny'] == 1) {
        $style = "background: #CCCCCC;";
    }
    $zawartosc .= '<tr><td class="row1" width="" style="padding: 4px;' . $style . '">' . $index . '</td>';
    $zawartosc .= '<td class="row1" width="25%" style="padding: 4px;' . $style . '">';

    if ($plik['stara_wgrywajka'] == 1) {
        if ($plik['miniatura'] != '') {
            $zawartosc .= '<a href="http://podforak.rzeszow.pl/upload/' . $plik['nazwa_pliku'] . '" target="_blank"><img src="http://podforak.rzeszow.pl/' . $plik['miniatura'] . '" style="width: 233px;"/></a>';
        } else {
            $tytul = $plik['nazwa_pliku'];
            if ($plik['tytul'] != '') {
                $tytul = $plik['tytul'];
            }
            $zawartosc .= '<a href="http://podforak.rzeszow.pl/upload/' . $plik['nazwa_pliku'] . '" target="_blank">' . $tytul . '</a>';
        }
    } else {
        if ($plik['miniatura'] != '') {
            $zawartosc .= '<a href="upload/' . $plik['nazwa_pliku'] . '" target="_blank"><img src="' . $plik['miniatura'] . '" style="width: 233px;"/></a>';
        } else {
            $tytul = $plik['nazwa_pliku'];
            if ($plik['tytul'] != '') {
                $tytul = $plik['tytul'];
            }
            $zawartosc .= '<a href="upload/' . $plik['nazwa_pliku'] . '" target="_blank">' . $tytul . '</a>';
        }
    }

    $zawartosc .= '</td>';
    $zawartosc .= '<td class="row2" width="45%" style="padding: 4px;' . $style . '">' . $plik['opis'] . '</td><td class="row1" width="15%" style="padding: 4px; text-align: center;' . $style . '">' . date('d-m-Y h:i', $plik['dodany']);

    if ($kto_dodal) {
        $nazwa_usera = pobierz_pokolorowana_nazwe($plik['uid']);
        if ($nazwa_usera != "") {
            $zawartosc .= '<br />przez: ';
            $zawartosc .= $nazwa_usera;
        }
    }

    $zawartosc .= '</td>';

    $zawartosc .= '<td class="row2 akcjeplikow" width="15%" style="padding: 4px;' . $style . '"><ul>'
            . '<li><a href="upload.php?action=szukaj&fid=' . $plik['id'] . '">Znajd&#378; posty z plikiem</a></li>';
    $zawartosc .= '<li><a href="upload.php?fid=' . $plik['id'] . '" target="_blank">Pobierz kod na forum</a></li>';
    if ($zarzadzanie) {
//            $zawartosc .= '<li><a href="upload.php?fid=' . $plik['id'] . '">Pobierz kod na forum</a></li>';
        if (request_var('start', 0) > 0) {
            $str = "&start=" . request_var('start', 0);
        } else {
            $str = "";
        }
        if ($plik['prywatny'] == 0) {
            $zawartosc .= '<li><a href="upload.php?action=prywatny&fid=' . $plik['id'] . '&status=1' . $str . '">Oznacz jako prywatny</a></li>';
        } else {
            $zawartosc .= '<li><a href="upload.php?action=prywatny&fid=' . $plik['id'] . '&status=0' . $str . '">Oznacz jako publiczny</a></li>';
        }
        $zawartosc .= '<li><a href="upload.php?action=usun&fid=' . $plik['id'] . '' . $str . '">Usu&#324; plik</a></li>';
    }
    $zawartosc .= '</ul></td></tr>';

    return $zawartosc;
}

/**
 * Pobiera pokolorowana nazwe uzytkownika
 * @global type $db
 * @param type $uid
 * @return string
 */
function pobierz_pokolorowana_nazwe($uid) {
    global $db;
    $zawartosc = "";

    $sql = "SELECT u.user_id, u.username, u.user_colour FROM podf3_users u WHERE u.user_id = $uid";
    if ($result = $db->sql_query($sql)) {
        while ($row = $db->sql_fetchrow($result)) {
            $style = 'class="username"';
            if (trim($row['user_colour']) != '') {
                $style = 'class="username-coloured" style="color: #' . $row['user_colour'] . ';"';
            }
            if ($colored_username != 'Anonymous') {
                $zawartosc = '<a ' . $style . ' href="memberlist.php?mode=viewprofile&u=' . $row['user_id'] . '">'
                        . $row['username'] .
                        '</a>';
            }
        }
    }

    return $zawartosc;
}

/**
 * zwraca zawartosc strony plikow zadanego usera
 * @global type $userdata
 * @global type $per_page
 * @global type $total_match_count
 * @param type $uid
 * @param type $start
 * @return type
 */
function pliki_usera($uid, $start) {
    global $usdata;
    global $per_page;
    $edycja = false;

    if (!isset($uid) || $uid == 0) {
        $uid = $usdata['user_id'];
    }
    // $poster_data = get_userdata($uid);
    $zawartosc .= lista_plikow_naglowek("Pliki dodane przez " . $usdata['username']);
    global $total_match_count;
    $pliki_usera = pobierz_wpisy_usera($uid);
    $total_match_count = count($pliki_usera);
    $index = $start + 1;

    $end = $start + $per_page;
    if ($end > count($pliki_usera)) {
        $end = count($pliki_usera);
    }

    if ($uid == $usdata['user_id']) {
        $edycja = true;
    }

    for ($i = $start; $i < $end; $i++) {
        $zawartosc .= lista_plikow_wiersz($pliki_usera[$i], $index, $edycja);
        $index++;
    }
    $zawartosc .= lista_plikow_stopka();

    return $zawartosc;
}

/**
 * zwraca kod zadanego pliku do wklejenia na forum
 * @global type $userdata
 * @global type $db
 * @param type $fid
 * @return string
 */
function pobierz_kod($fid) {
    global $db;
    $nazwa_pliku = '';
    $nazwa_miniatury = '';
    $stara_wgrywajka = 0;

    $sql = "SELECT * FROM " . PODFORAK_UPLOAD_TABLE . " WHERE id = " . $fid . " ORDER BY dodany DESC";
    if (!($result = $db->sql_query($sql))) {
        return '<table cellspacing="1" cellpading="0" class="forumline" align="center"  width="100%" style="margin-top: 0px;">
						<tr>
							<th style="padding-left: 7px;" width="100%" colspan="4">
								Informacja
							</th>
						</tr>
						</tr>
							<td class="row1" align="center" colspan="4">Brak wynik&#243;w</td>
						</tr>
					</table>';
    } else {
        while ($row = $db->sql_fetchrow($result)) {
            $nazwa_pliku = $row['nazwa_pliku'];
            $nazwa_miniatury = $row['miniatura'];
            $stara_wgrywajka = $row['stara_wgrywajka'];
        }

        if ($nazwa_pliku != '' && $nazwa_miniatury != '') {
            return ekran_koncowy_wgrywania($nazwa_pliku, $nazwa_miniatury, $stara_wgrywajka);
        } elseif ($nazwa_pliku != '') {
            return ekran_koncowy_wgrywania($nazwa_pliku, $nazwa_miniatury, $stara_wgrywajka);
        } else {
            return '<table cellspacing="1" cellpading="0" class="forumline" align="center"  width="100%" style="margin-top: 0px;">
						<tr>
							<th style="padding-left: 7px;" width="100%" colspan="4">
								Informacja
							</th>
						</tr>
						</tr>
							<td class="row1" align="center" colspan="4">Brak wynik&#243;w</td>
						</tr>
					</table>';
        }
    }
}

/**
 * wyniki wgrywania prostego
 */
function wynik_prostego() {
    // var_dump($user->data);
    global $usdata;
    // var_dump($usdata);
    global $request;
    // var_dump($request->file('plik'));

    $plik = $request->file('plik');

    if (is_uploaded_file($plik['tmp_name'])) {
        $nazwa = $plik['name'];
        $dodano = time();
        if (walidacja_rozszerzenia($nazwa)) {
            if (is_obraz($nazwa)) {
                $nowa_nazwa = 'u' . $usdata['user_id'] . '_' . $dodano . '.' . pathinfo($nazwa, PATHINFO_EXTENSION);
                if (zapisz_obraz($plik, 'upload/' . $nowa_nazwa, 1024, true, "pd")) {
                    $nazwa_miniatury = 'upload/u' . $usdata['user_id'] . '_' . $dodano . '_thumb.jpg';

                    if (utworz_miniature('upload/' . $nowa_nazwa, $nazwa_miniatury)) {
                        if (!dodaj_wpis_bazy($nowa_nazwa, $nazwa, '', $dodano, $nazwa_miniatury)) {
                            return blad_ogolny();
                        }
                        return ekran_koncowy_wgrywania($nowa_nazwa, $nazwa_miniatury);
                    } else {
                        if (!dodaj_wpis_bazy($nowa_nazwa, $nazwa, '', $dodano, '')) {
                            return blad_ogolny();
                        }

                        return ekran_koncowy_wgrywania($nowa_nazwa, '');
                    }
                }
                return blad_ogolny_pliku();
            } else {

                $nowa_nazwa = 'u' . $usdata['user_id'] . '_' . $dodano . '.' . pathinfo($nazwa, PATHINFO_EXTENSION);
                przenies_plik_tmp($plik['tmp_name'], $nowa_nazwa);

                if (!dodaj_wpis_bazy($nowa_nazwa, $nazwa, '', $dodano, '')) {
                    return blad_ogolny();
                }

                return ekran_koncowy_wgrywania($nowa_nazwa, '');
            }
        } else {
            return blad_rozszerzenia();
        }
    }
}

/**
 * wyniki wgrwania zaawansowanego
 */
function wynik_zaawansowanego() {
    global $usdata;
    global $request;
    $plik = $request->file('plik');
    $pictitle = request_var('pic_title', '');
    $picdesc = request_var('pic_desc', '');
    $zmianarozmiaru = request_var('zmianarozmiaru', 0);
    $dodac = request_var('dodac', 0);
    $miejsceznaku = request_var('miejsceznaku', 'pd');

    if (is_uploaded_file($plik['tmp_name'])) {
        $nazwa = $plik['name'];
        $dodano = time();
        if (walidacja_rozszerzenia($plik['name'])) {
            if (is_obraz($nazwa)) {
                $nowa_nazwa = 'u' . $userdata['user_id'] . '_' . $dodano . '.' . pathinfo($nazwa, PATHINFO_EXTENSION);
                if (zapisz_obraz($plik, 'upload/' . $nowa_nazwa, intval($zmianarozmiaru), intval($dodac), $miejsceznaku)) {
                    $nazwa_miniatury = 'upload/u' . $userdata['user_id'] . '_' . $dodano . '_thumb.jpg';

                    if ($pictitle != '') {
                        $opis = $pictitle;
                    } else {
                        $opis = $nazwa;
                    }

                    if (utworz_miniature('upload/' . $nowa_nazwa, $nazwa_miniatury)) {
                        if (!dodaj_wpis_bazy($nowa_nazwa, $opis, $picdesc, $dodano, $nazwa_miniatury)) {
                            return blad_ogolny();
                        }
                        return ekran_koncowy_wgrywania($nowa_nazwa, $nazwa_miniatury);
                    } else {
                        if (!dodaj_wpis_bazy($nowa_nazwa, $opis, $picdesc, $dodano, '')) {
                            return blad_ogolny();
                        }

                        return ekran_koncowy_wgrywania($nowa_nazwa, '');
                    }
                }
                return blad_ogolny_pliku();
            } else {
                $nowa_nazwa = 'u' . $userdata['user_id'] . '_' . $dodano . '.' . pathinfo($nazwa, PATHINFO_EXTENSION);
                przenies_plik_tmp($plik['tmp_name'], $nowa_nazwa);

                if ($pictitle != '') {
                    $opis = $pictitle;
                } else {
                    $opis = $nazwa;
                }

                if (!dodaj_wpis_bazy($nowa_nazwa, $opis, $picdesc, $dodano, '')) {
                    return blad_ogolny();
                }

                return ekran_koncowy_wgrywania($nowa_nazwa, '');
            }
        } else {
            return blad_rozszerzenia();
        }
    }
}

/**
 * ekran koncowy wgrywania
 * @param unknown_type $nazwa_pliku - nazwa pliku
 * @param unknown_type $nazwa_miniatury - nazwa miniatury (jesli istnieje)
 */
function ekran_koncowy_wgrywania($nazwa_pliku, $nazwa_miniatury, $stara_wgrywajka = 0) {
    $zawartosc .= '<table cellspacing="1" cellpading="0" class="forumline" align="center"  width="100%" style="margin-top: 0px;">
                        <tr>
                        <th style="padding-left: 7px;" width="100%" colspan="2">
                        Wyniki wgrywania
                        </th>
                        </tr>
                        <tr>
                                <td class="row1" style="padding: 7px;">Skopiuj poni&#380;szy tekst i wklej na forum: </td>
                        </tr>
                        <tr>
                                <td class="row1" style="padding: 7px;">
                                        <textarea cols="100" rows="5" style="width: 90%;">';

    if ($stara_wgrywajka == 1) {
        if (is_obraz($nazwa_pliku) && $nazwa_miniatury != '') {
            $zawartosc .= '[URL=http://podforak.rzeszow.pl/upload/' . $nazwa_pliku . '][img]http://podforak.rzeszow.pl/' . $nazwa_miniatury . '[/img][/URL]';
        } else if (is_obraz($nazwa_pliku)) {
            $zawartosc .= '[IMG]http://podforak.rzeszow.pl/upload/' . $nazwa_pliku . '[/img]';
        } else {
            $zawartosc .= 'http://podforak.rzeszow.pl/upload/' . $nazwa_pliku;
        }
    } else {

        if (is_obraz($nazwa_pliku) && $nazwa_miniatury != '') {
            $zawartosc .= '[URL=' . generate_board_url() . '/upload/' . $nazwa_pliku . '][img]' . generate_board_url() . '/' . $nazwa_miniatury . '[/img][/URL]';
        } else if (is_obraz($nazwa_pliku)) {
            $zawartosc .= '[IMG]' . generate_board_url() . '/upload/' . $nazwa_pliku . '[/img]';
        } else {
            $zawartosc .= generate_board_url() . '/upload/' . $nazwa_pliku;
        }
    }

    $zawartosc .= '</textarea>
						</td>
					</tr>
					<tr>
						<td class="catBottom" align="center" colspan="2">
						</td>
					</tr>
					</table>';

    return $zawartosc;
}

/**
 * zapisuje plik w miescu przeznaczenia z odpowiednia nazwa
 * @param unknown_type $nazwa_tmp - nazwa pliku tmp
 * @param unknown_type $nazwa_koncowa - nazwa pod ktora ma zostac zapisany plik
 */
function przenies_plik_tmp($nazwa_tmp, $nazwa_koncowa) {
    move_uploaded_file($nazwa_tmp, 'upload/' . $nazwa_koncowa);
}

/**
 * sprawdza czy plik jest obrazem
 * @param unknown_type $nazwa
 * @return boolean
 */
function is_obraz($nazwa) {
    $ext = pathinfo($nazwa, PATHINFO_EXTENSION);
    $rozszerzenia_obrazow = array('png', 'gif', 'jpeg', 'jpg');

    return contains($ext, $rozszerzenia_obrazow);
}

/**
 * blad zabronionego rozszerzenia pliku
 * @return string
 */
function blad_rozszerzenia() {
    return '<div class="messages error"> Wybrano niedozwolony typ pliku!</div>';
}

/**
 * blad ogolny zapisu do bazy
 * @return string
 */
function blad_ogolny() {
    return '<div class="messages error"> B&#322;&#261;d og&#243;lny tworzenia nowego wpisu do bazy!</div>';
}

/**
 * blad ogolny zapisu pliku
 * @return string
 */
function blad_ogolny_pliku() {
    return '<div class="messages error"> B&#322;&#261;d og&#243;lny zapisu pliku!</div>';
}

/**
 * funkcja zapisuje porzeskalowany obraz na dysku
 * @param unknown_type $plik_tmp
 * @param unknown_type $miejsce_zapisu
 * @param unknown_type $szerokosc_docelowa
 * @return boolean
 */
function zapisz_obraz($plik_tmp, $miejsce_zapisu, $szerokosc_docelowa, $znak_wodny, $polozenie_znaczka) {
    $nazwa = $plik_tmp['name'];
    $typ = pathinfo($nazwa, PATHINFO_EXTENSION);
    $typ = strtolower($typ);

    switch ($typ) {
        case 'gif':
            $obraz_dyskowy = imagecreatefromgif($plik_tmp['tmp_name']);
            break;
        case 'jpg':
            $obraz_dyskowy = imagecreatefromjpeg($plik_tmp['tmp_name']);
            break;
        case 'jpeg':
            $obraz_dyskowy = imagecreatefromjpeg($plik_tmp['tmp_name']);
            break;
        case 'png':
            $obraz_dyskowy = imagecreatefrompng($plik_tmp['tmp_name']);
            break;
        default:
            $obraz_dyskowy = null;
            break;
    }

    if ($obraz_dyskowy != null) {
        list($szerokosc, $wysokosc) = getimagesize($plik_tmp['tmp_name']);

        if ($szerokosc_docelowa <= 0 && $szerokosc > 1920) {
            $szerokosc_docelowa = 1920;
        }

        if ($szerokosc >= $szerokosc_docelowa && $szerokosc_docelowa > 0) {
            $dzielnik = ($szerokosc_docelowa / $szerokosc);
            $nowawysokosc = round($wysokosc * $dzielnik);
            $nowaszerokosc = $szerokosc_docelowa;

            $nowy_obrazek = imagecreatetruecolor($nowaszerokosc, $nowawysokosc);
            imagecopyresampled($nowy_obrazek, $obraz_dyskowy, 0, 0, 0, 0, $nowaszerokosc, $nowawysokosc, $szerokosc, $wysokosc);

            if ($znak_wodny) {
                $znaczek = "upload/wymagane/znakwodny.png";
                $zmienna_znaku = imagecreatefrompng($znaczek);
                list($szerokoscznaczka, $wysokoscznaczka) = getimagesize($znaczek);

                $nowaszerokosc_znaczka = $nowaszerokosc * 0.2;
                $dzielnik_znaczka = ($nowaszerokosc_znaczka / $szerokoscznaczka);
                $nowawysokosc_znaczka = round($wysokoscznaczka * $dzielnik_znaczka);

                $nowy_znaczek = imagecreatetruecolor($nowaszerokosc_znaczka, $nowawysokosc_znaczka);
                imagecopyresampled($nowy_znaczek, $zmienna_znaku, 0, 0, 0, 0, $nowaszerokosc_znaczka, $nowawysokosc_znaczka, $szerokoscznaczka, $wysokoscznaczka);
                imagecolortransparent($nowy_znaczek, imagecolorexact($nowy_znaczek, 0, 0, 0));

                $od_x = $nowaszerokosc - $nowaszerokosc_znaczka;
                $od_y = $nowawysokosc - $nowawysokosc_znaczka;

                switch ($polozenie_znaczka) {
                    case 'lg':
// 						lg
                        imageCopyMerge($nowy_obrazek, $nowy_znaczek, 0, 0, 0, 0, $nowaszerokosc_znaczka, $nowawysokosc_znaczka, 40);
                        break;
                    case 'ld':
// 						ld
                        imageCopyMerge($nowy_obrazek, $nowy_znaczek, 0, $od_y, 0, 0, $nowaszerokosc_znaczka, $nowawysokosc_znaczka, 40);
                        break;
                    case 'pg':
// 						pg
                        imageCopyMerge($nowy_obrazek, $nowy_znaczek, $od_x, 0, 0, 0, $nowaszerokosc_znaczka, $nowawysokosc_znaczka, 40);
                        break;
                    case 'pd':
// 						pd
                        imageCopyMerge($nowy_obrazek, $nowy_znaczek, $od_x, $od_y, 0, 0, $nowaszerokosc_znaczka, $nowawysokosc_znaczka, 40);
                        break;
                    default:
                        imageCopyMerge($nowy_obrazek, $nowy_znaczek, $od_x, $od_y, 0, 0, $nowaszerokosc_znaczka, $nowawysokosc_znaczka, 40);
                        break;
                }
            }


            switch ($typ) {
                case 'gif':
                    imagegif($nowy_obrazek, $miejsce_zapisu, 50);
                    break;
                case 'jpg':
                    imagejpeg($nowy_obrazek, $miejsce_zapisu, 80);
                    break;
                case 'jpeg':
                    imagejpeg($nowy_obrazek, $miejsce_zapisu, 80);
                    break;
                case 'png':
                    imagepng($nowy_obrazek, $miejsce_zapisu, 2);
                    break;
                default:
                    break;
            }
            imagedestroy($nowy_obrazek);
            imagedestroy($nowy_znaczek);
            imagedestroy($zmienna_znaku);
            return true;
        } else {
            if ($znak_wodny) {
                list($szerokosc, $wysokosc) = getimagesize($plik_tmp['tmp_name']);
                $nowaszerokosc = $szerokosc;
                $nowawysokosc = $wysokosc;
                $nowy_obrazek = imagecreatetruecolor($nowaszerokosc, $nowawysokosc);
                imagecopyresampled($nowy_obrazek, $obraz_dyskowy, 0, 0, 0, 0, $nowaszerokosc, $nowawysokosc, $szerokosc, $wysokosc);

                $znaczek = "upload/wymagane/znakwodny.png";
                $zmienna_znaku = imagecreatefrompng($znaczek);
                list($szerokoscznaczka, $wysokoscznaczka) = getimagesize($znaczek);

                $nowaszerokosc_znaczka = $nowaszerokosc * 0.2;
                $dzielnik_znaczka = ($nowaszerokosc_znaczka / $szerokoscznaczka);
                $nowawysokosc_znaczka = round($wysokoscznaczka * $dzielnik_znaczka);

                $nowy_znaczek = imagecreatetruecolor($nowaszerokosc_znaczka, $nowawysokosc_znaczka);
                imagecopyresampled($nowy_znaczek, $zmienna_znaku, 0, 0, 0, 0, $nowaszerokosc_znaczka, $nowawysokosc_znaczka, $szerokoscznaczka, $wysokoscznaczka);
                imagecolortransparent($nowy_znaczek, imagecolorexact($nowy_znaczek, 0, 0, 0));

                $od_x = $nowaszerokosc - $nowaszerokosc_znaczka;
                $od_y = $nowawysokosc - $nowawysokosc_znaczka;

                switch ($polozenie_znaczka) {
                    case 'lg':
// 						lg
                        imageCopyMerge($nowy_obrazek, $nowy_znaczek, 0, 0, 0, 0, $nowaszerokosc_znaczka, $nowawysokosc_znaczka, 40);
                        break;
                    case 'ld':
// 						ld
                        imageCopyMerge($nowy_obrazek, $nowy_znaczek, 0, $od_y, 0, 0, $nowaszerokosc_znaczka, $nowawysokosc_znaczka, 40);
                        break;
                    case 'pg':
// 						pg
                        imageCopyMerge($nowy_obrazek, $nowy_znaczek, $od_x, 0, 0, 0, $nowaszerokosc_znaczka, $nowawysokosc_znaczka, 40);
                        break;
                    case 'pd':
// 						pd
                        imageCopyMerge($nowy_obrazek, $nowy_znaczek, $od_x, $od_y, 0, 0, $nowaszerokosc_znaczka, $nowawysokosc_znaczka, 40);
                        break;
                    default:
                        imageCopyMerge($nowy_obrazek, $nowy_znaczek, $od_x, $od_y, 0, 0, $nowaszerokosc_znaczka, $nowawysokosc_znaczka, 40);
                        break;
                }


                switch ($typ) {
                    case 'gif':
                        imagegif($nowy_obrazek, $miejsce_zapisu, 50);
                        break;
                    case 'jpg':
                        imagejpeg($nowy_obrazek, $miejsce_zapisu, 80);
                        break;
                    case 'jpeg':
                        imagejpeg($nowy_obrazek, $miejsce_zapisu, 80);
                        break;
                    case 'png':
                        imagepng($nowy_obrazek, $miejsce_zapisu, 2);
                        break;
                    default:
                        break;
                }
                imagedestroy($nowy_obrazek);
                return true;
            } else {
                move_uploaded_file($plik_tmp['tmp_name'], $miejsce_zapisu);
                return true;
            }

            return false;
        }
    } else {
        return false;
    }
}

/**
 * tworzy miniature
 * @param unknown_type $wgrany_plik - plik przeslany przez uzytkownika
 * @param unknown_type $nazwa_miniatury - nazwa pod jaka zapisana bedzie miniatura
 * @return boolean - flaga poprawnosci
 */
function utworz_miniature($wgrany_plik, $nazwa_miniatury) {
    $obraz_dyskowy = null;
    $typ = pathinfo($wgrany_plik, PATHINFO_EXTENSION);
    $typ = strtolower($typ);

    switch ($typ) {
        case 'gif':
            $obraz_dyskowy = imagecreatefromgif($wgrany_plik);
            break;
        case 'jpg':
            $obraz_dyskowy = imagecreatefromjpeg($wgrany_plik);
            break;
        case 'jpeg':
            $obraz_dyskowy = imagecreatefromjpeg($wgrany_plik);
            break;
        case 'png':
            $obraz_dyskowy = imagecreatefrompng($wgrany_plik);
            break;
        default:
            $obraz_dyskowy = null;
            break;
    }

    if ($obraz_dyskowy != null) {
        list($szerokosc, $wysokosc) = getimagesize($wgrany_plik);
        if ($szerokosc >= 400) {
            $dzielnik = (400 / $szerokosc);
            $nowawysokosc = round($wysokosc * $dzielnik);
            $nowaszerokosc = 400;

            $nowy_obrazek = imagecreatetruecolor($nowaszerokosc, $nowawysokosc);
            imagecopyresampled($nowy_obrazek, $obraz_dyskowy, 0, 0, 0, 0, $nowaszerokosc, $nowawysokosc, $szerokosc, $wysokosc);

            imagejpeg($nowy_obrazek, $nazwa_miniatury, 80);
            imagedestroy($nowy_obrazek);

            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * zmienia status pliku na prywatny/publiczny
 * @global type $userdata
 * @global type $db
 * @param type $plik_id
 * @param type $status
 * @return string
 */
function oznacz_prywatny($plik_id, $status = 0) {
    global $usdata;
    global $db;
    $uid = NULL;
    $zawartosc = "";

    $sql = "SELECT uid FROM " . PODFORAK_UPLOAD_TABLE . " WHERE id = $plik_id  ORDER BY dodany DESC";
    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not query posts table', '', __LINE__, __FILE__, $sql);
    }

    while ($row = $db->sql_fetchrow($result)) {
        $uid = $row['uid'];
    }

    if (isset($uid) && $uid == $usdata['user_id']) {
        $sql = "UPDATE phpbb_podforak_upload SET prywatny =  $status WHERE id = $plik_id";

        if (!$db->sql_query($sql)) {
            $zawartosc = '<div id="komunikaty"><div class="messages error">B&#322;&#261;d</div></div>';
        } else {
            $zawartosc = '<div id="komunikaty"><div class="messages status">Poprawnie zmieniono status pliku. Za chwil&#281; nast&#261;pi przekierowanie, prosz&#281 czeka&#263;...</div></div>';
        }
    } else {
        $zawartosc = '<div id="komunikaty"><div class="messages error">Musisz by&#263; w&#322;a&#347;cicielem pliku!</div></div>';
    }

    return $zawartosc;
}

/**
 * wyszukiwnanie postow z z plikiem o zadanym id
 * @global type $userdata
 * @global type $db
 * @global type $tree
 * @param type $plik_id
 * @return string
 */
function wyszukiwanie_postow_id($plik_id) {
    global $usdata;
    global $db;
    global $auth;
    $nazwa_pliku = null;
    $uid = NULL;
    $prywatny = NULL;
    $zawartosc = "";
    $sql = "SELECT nazwa_pliku, uid, prywatny FROM " . PODFORAK_UPLOAD_TABLE . " WHERE id = $plik_id  ORDER BY dodany DESC";

    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not query posts table', '', __LINE__, __FILE__, $sql);
    }

    while ($row = $db->sql_fetchrow($result)) {
        $nazwa_pliku = $row['nazwa_pliku'];
        $uid = $row['uid'];
        $prywatny = $row['prywatny'];
    }

    if (isset($nazwa_pliku)) {
        $zawartosc = '<br /><span class="maintitle">Wyniki dla: ' . $nazwa_pliku . '</span>';
    }

    $zawartosc .= naglowek_wyszukiwania_listy_postow();

    if ($prywatny == 0 || $userdata['user_id'] == $uid) {
        if (isset($nazwa_pliku) && $nazwa_pliku != "") {
            $sql = "SELECT ps.post_id, t.topic_title , u.user_id, u.user_posts, ps.post_subject, ps.topic_id, ps.post_username, u.username, ps.forum_id, f.forum_name, u.user_colour, u.user_id
            FROM podf3_posts ps
            LEFT JOIN podf3_users u ON u.user_id  = ps.poster_id
            LEFT JOIN podf3_topics t ON t.topic_id = ps.topic_id
            LEFT JOIN podf3_forums f ON f.forum_id = ps.forum_id
            WHERE ps.post_text LIKE '%" . przygotuj_nazwe_pliku($nazwa_pliku) . "%'";

//            var_dump($sql);

            if (!($result = $db->sql_query($sql))) {
                message_die(GENERAL_ERROR, 'Could not query posts table', '', __LINE__, __FILE__, $sql);
            }

            $i = 0;
            while ($row = $db->sql_fetchrow($result)) {

                if ($auth->acl_get('f_read', $row['forum_id']) || $auth->acl_gets('a_', 'm_')) {

                    $forum_style = "";
                    $temat_style = "";
//                    $colored_username = color_username($row['user_level'], $row['user_jr'], $row['user_id'], $row['username']);
                    $colored_username = pobierz_pokolorowana_nazwe($row['user_id']);
                    if (isset($row['forum_color']) && $row['forum_color'] != '') {
                        $forum_style = 'style="color: #' . $row['forum_color'] . ';"';
                    }
                    if (isset($row['topic_color']) && $row['topic_color'] != '') {
                        $temat_style = 'style="color: ' . $row['topic_color'] . ';"';
                    }

                    $zawartosc .= wiersz_wyszukiwania_listy_postow($row, $forum_style, $temat_style, $colored_username);

                    $i++;
                }
            }
            if ($i == 0) {
                $zawartosc .= wyszukiwanie_postow_id_brak_wynikow();
            }
        } else {
            $zawartosc .= wyszukiwanie_postow_id_brak_wynikow();
        }
    } else {
        $zawartosc .= '<tr><td class="row1" style="padding: 7px;" width="100%" colspan="3">
                                Plik jest prywatny.
                        </td></tr>';
    }

    $zawartosc .= '<tr>
                        <td class="catBottom" align="center" colspan="4">
                        </td>
                    </tr>
            </table>';

    return $zawartosc;
}

function przygotuj_nazwe_pliku($nazwa_pliku) {

    $nazwa_pliku = str_replace(".", "%", 'upload/' . $nazwa_pliku);
//    var_dump($nazwa_pliku);
    return $nazwa_pliku;
}

/**
 * wyszukiwnanie postow z z plikiem o zadanym id
 * @global type $userdata
 * @global type $db
 * @global type $tree
 * @param type $plik_id
 * @return string
 */
function wyszukiwanie_postow_uid_fid($plik_id, $wyszukiwany_user) {
    $userdata = $user->data;
    global $db;
    global $tree;
    $nazwa_pliku = null;
    $uid = NULL;
    $prywatny = NULL;
    $zawartosc = "";
    $sql = "SELECT nazwa_pliku, uid, prywatny FROM " . PODFORAK_UPLOAD_TABLE . " WHERE id = $plik_id  ORDER BY dodany DESC";
    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not query posts table', '', __LINE__, __FILE__, $sql);
    }

    while ($row = $db->sql_fetchrow($result)) {
        $nazwa_pliku = $row['nazwa_pliku'];
        $uid = $row['uid'];
        $prywatny = $row['prywatny'];
    }

    $poster_data = get_userdata($uid, false, "username");

    if (isset($nazwa_pliku)) {
        $zawartosc = '<br /><span class="maintitle">Wyniki dla: ' . $nazwa_pliku . ', napisane przez: ' . $poster_data['username'] . '</span>';
    }

    $zawartosc .= naglowek_wyszukiwania_listy_postow();

    if ($prywatny == 0 || $userdata['user_id'] == $uid) {
        if (isset($nazwa_pliku) && $nazwa_pliku != "") {
            $sql = "SELECT ps.post_id, t.topic_title , t.topic_color, u.special_rank, u.user_id, u.user_level, u.user_posts, u.user_jr,  pst.post_subject, ps.topic_id, ps.post_username, u.username, ps.forum_id, f.forum_name, f.forum_color, u.user_colour
            FROM podf3_posts ps
            LEFT JOIN podf3_users u ON u.user_id  = ps.poster_id
            LEFT JOIN podf3_topics t ON t.topic_id = ps.topic_id
            LEFT JOIN podf3_forums f ON f.forum_id = ps.forum_id
            WHERE ps.poster_id = $wyszukiwany_user AND pst.post_text LIKE '%$nazwa_pliku%'";

            if (!($result = $db->sql_query($sql))) {
                message_die(GENERAL_ERROR, 'Could not query posts table', '', __LINE__, __FILE__, $sql);
            }

            $i = 0;
            while ($row = $db->sql_fetchrow($result)) {

                $is_auth = $tree['auth'][POST_FORUM_URL . $row['forum_id']];
                if ($is_auth['auth_view'] == 1 || $userdata['user_level'] == ADMIN || $userdata['user_jr'] || $is_auth['auth_mod']) {
                    $forum_style = "";
                    $temat_style = "";
                    $colored_username = $row['username'];
//                            color_username($row['user_level'], $row['user_jr'], $row['user_id'], $row['username']);
                    if (isset($row['forum_color']) && $row['forum_color'] != '') {
                        $forum_style = 'style="color: #' . $row['forum_color'] . ';"';
                    }
                    if (isset($row['topic_color']) && $row['topic_color'] != '') {
                        $temat_style = 'style="color: ' . $row['topic_color'] . ';"';
                    }

                    $zawartosc .= wiersz_wyszukiwania_listy_postow($row, $forum_style, $temat_style, $colored_username);

                    $i++;
                }
            }
            if ($i == 0) {
                $zawartosc .= wyszukiwanie_postow_id_brak_wynikow();
            }
        } else {
            $zawartosc .= wyszukiwanie_postow_id_brak_wynikow();
        }
    } else {
        $zawartosc .= '<tr><td class="row1" style="padding: 7px;" width="100%" colspan="3">
                                Plik jest prywatny.
                        </td></tr>';
    }

    $zawartosc .= '<tr>
                        <td class="catBottom" align="center" colspan="4">
                        </td>
                    </tr>
            </table>';

    return $zawartosc;
}

/**
 * naglowek listy postow zawierajacych obrazek
 * @return string
 */
function naglowek_wyszukiwania_listy_postow() {
    $zawartosc = '<table cellspacing="1" cellpading="0" class="forumline" align="center"  width="100%" style="margin-top: 0px;">			
                    <tr>
                            <th class="thTop" nowrap="nowrap">Forum</td>
                            <th class="thTop" nowrap="nowrap">Temat</td>
                            <th class="thTop" nowrap="nowrap">Autor</td>
                    </tr>
                    ';
    return $zawartosc;
}

/**
 * wiersz listy postow zawierajacych obrazek
 * @param type $row
 * @param type $forum_style
 * @param type $temat_style
 * @param type $colored_username
 * @return string
 */
function wiersz_wyszukiwania_listy_postow($row, $forum_style, $temat_style, $colored_username) {
    $zawartosc = '<tr><td class="row1" style="padding: 7px;">
                                <a class="forumtitle" ' . $forum_style . ' href="viewforum.php?f=' . $row['forum_id'] . '">'
            . $row['forum_name'] .
            '</a></td>
                                <td class="row1" style="padding: 7px;">
                                    <a class="topictitle" ' . $temat_style . ' href="viewtopic.php?p=' . $row['post_id'] . '#p' . $row['post_id'] . '">'
            . $row['topic_title'] .
            '</a></td>
                                <td class="row1" style="padding: 7px;">';
    if ($row['post_username'] != "") {
        $zawartosc .= $row['post_username'];
    } else {
        $zawartosc .= $colored_username;
    }

    $zawartosc .= '</td>
                    </tr>';

    return $zawartosc;
}

/**
 * zwraca wiersz braku wynikow
 * @return string
 */
function wyszukiwanie_postow_id_brak_wynikow() {
    $zawartosc = '<tr><td class="row1" style="padding: 7px;" width="100%" colspan="3">
                        Brak post&#243;w spe&#322;niaj&#261;cych kryteria.
                </td></tr>';

    return $zawartosc;
}

/**
 * formularz wyszukiwania pliku/plikow uzytkownika
 * @return string
 */
function szukaj_form() {
    $zawartosc = '<form action="?action=szukaj&level=2" method="POST" ENCTYPE="multipart/form-data" id="wgrajform">
                    <table cellspacing="1" cellpading="0" class="forumline" align="center"  width="100%" style="margin-top: 0px;">
                        <tr>
                            <th style="padding-left: 7px;" width="100%" colspan="2">Szukaj pliku</th>
                        </tr>
                        <tr>
                                <td class="row1" style="padding: 7px; width: 20%;"><b>Nazwa pliku:</b></td>
                                <td class="row2" style="padding: 7px;"><input class="post" type="text" size="30" name="pic_name"></td>
                        </tr>
                        <tr>
                                <td class="row1" style="padding: 7px;"><b>Plik u&#380;ytkownika:</b></td>
                                <td class="row2" style="padding: 7px;"><input class="post" type="text" size="30" name="pic_owner"></td>
                        </tr>
                        <tr>
                                <td class="catBottom" align="center" colspan="2"><input type="submit" class="liteoption" value="Szukaj"/></td>
                        </tr>
                    </table>
                </form>';
    return $zawartosc;
}

/**
 * zwraca url wynikow wyszukiwania na podastawie danych z formularza
 * @global type $userdata
 * @global type $db
 * @param type $filename
 * @param type $username
 * @return type
 */
function url_wyszukiwania($filename, $username) {
    $url = "";
    if ((isset($filename) && $filename != "") && (isset($username) && $username != "")) {
        $filename = przetworzenie_jesli_miniatura($filename);
        $uid = pobierz_id_usera($username);
        $fid = pobierz_id_pliku($filename);
        $url = "upload.php?action=szukaj&uid=$uid&fid=$fid";
    } elseif (isset($username) && $username != "") {
        $id = pobierz_id_usera($username);
        $url = "upload.php?action=szukaj&uid=$id";
    } else {
        if (strpos(strtolower($filename), '*') !== false) {
            $url = "upload.php?action=szukaj&nazwa=$filename";
        } else {
            $filename = przetworzenie_jesli_miniatura($filename);
            $id = pobierz_id_pliku($filename);
            $url = "upload.php?action=szukaj&fid=$id";
        }
    }

    return $url;
}

/**
 * obcina przedrostek (pliki starej wygrywajki) 
 * i przyrostek (pliki nowej wgrywajki) miniatury,
 * dzieki czemu dostajemy nazwe pliku wyjsciowego
 * @param type $filename
 * @return type
 */
function przetworzenie_jesli_miniatura($filename) {
    if (strpos(strtolower($filename), 'thumb_') !== false) {
        $filename = str_replace('thumb_', '', $filename);
    }

    if (strpos(strtolower($filename), '_thumb') !== false) {
        $filename = str_replace('_thumb', '', $filename);
    }

    return $filename;
}

/**
 * pobiera id uzytkownika na podstawie nazwy
 * @global type $db
 * @param type $username
 * @return type
 */
function pobierz_id_usera($username) {
    global $db;
    $id = 0;
    $sql = "SELECT user_id  FROM " . USERS_TABLE . " WHERE username = '$username'";
    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not query posts table', '', __LINE__, __FILE__, $sql);
    }

    while ($row = $db->sql_fetchrow($result)) {
        $id = $row['user_id'];
    }
    return $id;
}

/**
 * pobiera id pliku na podstawie nazwy
 * @global type $db
 * @param type $filename
 * @return type
 */
function pobierz_id_pliku($filename) {
    global $db;
    $id = 0;
    $sql = "SELECT id FROM " . PODFORAK_UPLOAD_TABLE . " WHERE nazwa_pliku = '$filename'  ORDER BY dodany DESC";

    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not query posts table', '', __LINE__, __FILE__, $sql);
    }

    while ($row = $db->sql_fetchrow($result)) {
        $id = $row['id'];
    }
    return $id;
}

/**
 * zwraca komunikat o braku zwynikow w wyszukiwaniu
 * @return string
 */
function brak_wynikow() {
    $zawartosc = '<table class="forumline" width="100%" align="center" cellspacing="1" style="margin-top: 0px;" cellpading="0">
                        <tbody>
                            <tr>
                                <th class="thTop" nowrap="nowrap">Komunikat</th>
                            </tr>
                            <tr>
                                <td class="row1" width="100%" colspan="3" style="padding: 7px;"> Brak post&#243;w spe&#322;niaj&#261;cych kryteria. </td>
                            </tr>
                            <tr>
                                <td class="catBottom" align="center" colspan="4"> </td>
                            </tr>
                        </tbody>
                    </table>';
    return $zawartosc;
}

function pobierz_pliki_nazwa($filename, $start) {
    global $db;
    global $per_page;
    global $total_match_count;
    $pliki = array();
    $edycja = false;
    $zawartosc = "";

    $filename = str_replace('*', '%', $filename);
    $sql = "SELECT * FROM " . PODFORAK_UPLOAD_TABLE . " WHERE nazwa_pliku LIKE '$filename' OR tytul LIKE '$filename' ORDER BY dodany DESC";

    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not query posts table', '', __LINE__, __FILE__, $sql);
    }

    while ($row = $db->sql_fetchrow($result)) {
        $pliki[] = $row;
    }

    if (isset($filename)) {
        $zawartosc = '<br /><span class="maintitle">Wyniki dla: ' . str_replace("%", "*", $filename) . '</span>';
    }

    $zawartosc .= lista_plikow_naglowek("Wyniki");
    $total_match_count = count($pliki);
    $index = $start + 1;

    $end = $start + $per_page;
    if ($end > count($pliki)) {
        $end = count($pliki);
    }

    for ($i = $start; $i < $end; $i++) {
        $zawartosc .= lista_plikow_wiersz($pliki[$i], $index, $edycja, true);
        $index++;
    }
    $zawartosc .= lista_plikow_stopka();

    return $zawartosc;
}

/**
 * usuwa plik
 * @global type $db
 * @global type $userdata
 * @param type $fid
 * @return string
 */
function usun_plik($fid, $grupowe = false) {
    global $db;
    global $usdata;
    global $auth;
    $uid = 0;
    $nazwa_pliku = "";
    $miniatura = "";
    $zawartosc = "";
    $tytul = "";
    $stara_wgrywajka = 0;

    $sql = "SELECT nazwa_pliku, uid, stara_wgrywajka, miniatura, tytul FROM " . PODFORAK_UPLOAD_TABLE . " WHERE id = $fid  ORDER BY dodany DESC";
    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not query posts table', '', __LINE__, __FILE__, $sql);
    }

    while ($row = $db->sql_fetchrow($result)) {
        $uid = $row['uid'];
        $nazwa_pliku = $row['nazwa_pliku'];
        $miniatura = $row['miniatura'];
        $tytul = $row['tytul'];
        $stara_wgrywajka = $row['stara_wgrywajka'];
    }

    if ($usdata['user_id'] == $uid || $auth->acl_gets('a_', 'm_')) {

        $sql = "SELECT count(*) as lpostow FROM phpbb_posts_text WHERE post_text LIKE '%upload/" . pathinfo($nazwa_pliku, PATHINFO_FILENAME) . "%'";
        if (!($result = $db->sql_query($sql))) {
            message_die(GENERAL_ERROR, 'Could not query posts table', '', __LINE__, __FILE__, $sql);
        }
        $liczba_postow = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $liczba_postow += $row['lpostow'];
        }

        $sql = "SELECT count(*) as lpostow FROM phpbb_posts_text_history th WHERE th.th_post_text LIKE '%upload/" . pathinfo($nazwa_pliku, PATHINFO_FILENAME) . "%'";
//        var_dump($sql);
        if (!($result = $db->sql_query($sql))) {
            message_die(GENERAL_ERROR, 'Could not query posts table', '', __LINE__, __FILE__, $sql);
        }
        while ($row = $db->sql_fetchrow($result)) {
            $liczba_postow += $row['lpostow'];
        }

        $sql = "SELECT count(*) as lpostow FROM phpbb_users u WHERE u.user_sig LIKE '%upload/" . pathinfo($nazwa_pliku, PATHINFO_FILENAME) . "%'";
//        var_dump($sql);
        if (!($result = $db->sql_query($sql))) {
            message_die(GENERAL_ERROR, 'Could not query posts table', '', __LINE__, __FILE__, $sql);
        }
        while ($row = $db->sql_fetchrow($result)) {
            $liczba_postow += $row['lpostow'];
        }

        $sql = "SELECT count(*) as lpostow FROM phpbb_privmsgs_text pt WHERE pt.privmsgs_text LIKE '%upload/" . pathinfo($nazwa_pliku, PATHINFO_FILENAME) . "%'";
//        var_dump($sql);
        if (!($result = $db->sql_query($sql))) {
            message_die(GENERAL_ERROR, 'Could not query posts table', '', __LINE__, __FILE__, $sql);
        }
        while ($row = $db->sql_fetchrow($result)) {
            $liczba_postow += $row['lpostow'];
        }

        $sql = "SELECT count(*) as lpostow FROM phpbb_shoutbox WHERE msg LIKE '%upload/" . pathinfo($nazwa_pliku, PATHINFO_FILENAME) . "%'";
//        var_dump($sql);
        if (!($result = $db->sql_query($sql))) {
            message_die(GENERAL_ERROR, 'Could not query posts table', '', __LINE__, __FILE__, $sql);
        }
        while ($row = $db->sql_fetchrow($result)) {
            $liczba_postow += $row['lpostow'];
        }

//        var_dump($liczba_postow);
//        exit;

        if ($liczba_postow > 0) {
            $zawartosc = '<div id="komunikaty"><div class="messages error">Nie mo&#380;esz usun&#261;&#263; ' . $tytul . ', poniewa&#380; jest u&#380;ywany lub nie istnieje!</div></div>';
        } else {
            $ex = false;
            if ($stara_wgrywajka == 1) {
                $nazwa_pliku = iconv("ISO-8859-2", "UTF-8", $nazwa_pliku);
                if (file_exists("upload/" . $nazwa_pliku)) {
                    $unlink = unlink("upload/" . $nazwa_pliku);
                    $miniatura = iconv("ISO-8859-2", "UTF-8", $miniatura);
                    if ($miniatura != "") {
                        $unlink = unlink($miniatura);
                    }
                } else {
                    $ex = true;
                }
            } else {
                if (file_exists("upload/" . $nazwa_pliku)) {
                    $unlink = unlink("upload/" . $nazwa_pliku);
                    // if na miniature
                    if ($miniatura != "") {
                        $unlink = unlink($miniatura);
                    }
                } else {
                    $ex = true;
                }
            }
            if (!$ex) {
                usun_wpis_pliku($fid);
                $zawartosc = '<div id="komunikaty"><div class="messages status2">Pomy&#347;lnie suni&#281;to plik ' . $tytul . '!</div></div>';
            } else {
                $zawartosc = '<div id="komunikaty"><div class="messages error">Nie znaleziono pliku  ' . $tytul . '! ';
                if ($grupowe) {
                    $zawartosc .= '</div></div>';
                } else {
                    $zawartosc .= 'Za chwil&#281; nast&#261;pi przekierowanie, prosz&#281 czeka&#263;...</div></div>';
                }
            }
        }
    } else {
        $zawartosc = '<div id="komunikaty"><div class="messages error">Brak uprawnie&#324;</div></div>';
    }

    return $zawartosc;
}

/**
 * usuwa wpis bazy danych pliku
 * @global type $db
 * @param type $fid
 */
function usun_wpis_pliku($fid) {
    global $db;

    $sql2 = "DELETE FROM " . PODFORAK_UPLOAD_TABLE . " WHERE id = $fid";
    if (!($db->sql_query($sql2))) {
        message_die(GENERAL_ERROR, 'Couldnt idelete from ' . PODFORAK_UPLOAD_TABLE . ' table', '', __LINE__, __FILE__, $sql2);
    }
}

/**
 * link do administracji
 * @global type $userdata
 * @return string
 */
function administracja_plikami() {
    global $auth;
    $zawartosc = "";
    if ($auth->acl_gets('a_', 'm_')) {
        $zawartosc = '<td class="row1" width="20%" style="padding: 8px;">
                        <a class="forumlink" href="upload.php?action=admin">Administracja</a>
                    </td>';
    }

    return $zawartosc;
}

/**
 * Wyszukuje posty zawierajace %allegro.pl%
 * @global type $userdata
 * @global type $db
 * @return string
 */
function szukaj_allegro() {
    global $db;
    $zawartosc = "";

    $sql = 'SELECT p.post_id, t.topic_id, t.topic_title, t.forum_id, f.forum_name, p.post_time, p.poster_id 
               FROM podf3_posts p 
                LEFT JOIN podf3_topics t ON p.topic_id = t.topic_id
                LEFT JOIN podf3_forums f ON t.forum_id = f.forum_id
                WHERE f.forum_id NOT IN(280,137,35,28,28,49,162,304) AND post_text like "%allegro.pl/%"
                ORDER BY f.forum_name, p.post_time ASC';

    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not query posts table', '', __LINE__, __FILE__, $sql);
    }
    $zawartosc .= '<table cellspacing="1" cellpading="0" class="forumline" align="center"  width="100%" style="margin-top: 0px;">';
    $zawartosc .= '<tr>
				<th style="padding-left: 7px;"> # </th>
				<th width="55%" style="padding-left: 7px;"> Plik </th>
				<th width="15%" style="padding-left: 7px;"> Forum </th>
				<th width="15%" style="padding-left: 7px;"> Napisal </th>
				<th width="15%" style="padding-left: 7px;"> Dodany </th>
		</tr>';
    $i = 1;
    while ($row = $db->sql_fetchrow($result)) {
        $zawartosc .= formularz_administracyjny_do_reupu($row, $i);
        $i++;
    }
    $zawartosc .= '<tr>
						<td class="catBottom" align="center" colspan="6"></td>
				   </tr>
				</table>';

    return $zawartosc;
}

/**
 * Wyszukuje posty zwierajace pliki do ponownego wgrania
 * @global type $userdata
 * @global type $db
 * @return string
 */
function pliki_do_reupu() {
    global $db;
    $zawartosc = "";
//    print_r($userdata['user_level']);
//    echo '<pre>';
//    print_r($userdata);
//    echo '</pre>';
//    if($userdata['user_level'] == ADMIN || $userdata['user_jr'] || $userdata['user_id'] == 88) {
    $sql = 'SELECT p.post_id, t.topic_id, t.topic_title, t.forum_id, f.forum_name, p.post_time, p.poster_id 
                FROM podf3_posts p
                LEFT JOIN podf3_topics t ON p.topic_id = t.topic_id
                LEFT JOIN podf3_forums f ON t.forum_id = f.forum_id
                WHERE f.forum_id NOT IN(280,137,35,28,28,49,162,123,304) AND (post_text like "%zapodaj.net/%" OR  post_text like "%fotosik.pl/%" OR  post_text like "%tinypic.pl/%"  OR  post_text like "%tinypic.com/%" OR  post_text like "%photobucket.com/%" OR  post_text like "%imgur.com/%" OR   post_text like "%imageshack.us/%" OR  post_text like "%twitpic.com/%"  OR  post_text like "%dropbox.com/%")
                ORDER BY f.forum_name, p.post_time ASC';
    // f.forum_id NOT IN(111,112,113,114,115,28,49,162,123,304,35,150,31,32,33,280,30,189,225,265,191,306,192,278,26,285,151,45,150,149,31,148,36,104,210,276,188,249,255,253,251,254,257,252,250,245,51,108,110,216,238,259,279,290,22,23,25,196,190,34,237) 
    // AND 
    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not query posts table', '', __LINE__, __FILE__, $sql);
    }
    $zawartosc .= '<table cellspacing="1" cellpading="0" class="forumline" align="center"  width="100%" style="margin-top: 0px;">';
    $zawartosc .= '<tr>
                    <th style="padding-left: 7px;"> # </th>
                    <th width="55%" style="padding-left: 7px;"> Plik </th>
                    <th width="15%" style="padding-left: 7px;"> Forum </th>
                    <th width="15%" style="padding-left: 7px;"> Napisal </th>
                    <th width="15%" style="padding-left: 7px;"> Dodany </th>
            </tr>';
    $i = 1;
    while ($row = $db->sql_fetchrow($result)) {
        $zawartosc .= formularz_administracyjny_do_reupu($row, $i);
        $i++;
    }
    $zawartosc .= '<tr>
                            <td class="catBottom" align="center" colspan="6"></td>
                       </tr>
                    </table>';
//    }
    return $zawartosc;
}

/**
 * wiersz formularza z lista postow
 * @param type $row
 * @param type $i
 * @return string
 */
function formularz_administracyjny_do_reupu($row, $i) {
    $zawartosc .= '';

    $zawartosc .= '<tr>
                <td class="row1" style="padding: 7px;">' . $i . '</td>
                <td class="row2" style="padding: 7px;">';
    $zawartosc .= '<a href ="viewtopic.php?p=' . $row['post_id'] . '#p' . $row['post_id'] . '">' . $row['topic_title'] . '</a>';
    $zawartosc .= '</td>';
    $zawartosc .= '<td class="row1" style="padding: 7px;"><a href ="viewforum.php?f=' . $row['forum_id'] . '">' . $row['forum_name'] . '</a></td>';
    $zawartosc .= '<td class="row1" style="padding: 7px;">';
    $nazwa_usera = pobierz_pokolorowana_nazwe($row['poster_id']);
    if ($nazwa_usera != "") {
        $zawartosc .= $nazwa_usera;
    } else {
        $zawartosc .= "brak usera";
    }
    $zawartosc .= '</td>';
    $zawartosc .= '<td class="row2" style="padding: 7px;">' . date('d-m-Y h:i', $row['post_time']) . '</td>
                    </tr>';
    return $zawartosc;
}

/**
 * uruchamia procedure wyszukujaca wolne pliki
 * i wyswietla formularz administracji plikami
 * @global type $userdata
 * @global type $db
 * @return string
 */
function formularz_administracyjny() {
    $userdata = $user->data;
    global $auth;
    global $db;
    global $nie_skanowano_od;
    $zawartosc .= '<script type="text/javascript">
                        $(document).ready(function () {
                            $(document).on("change", "#zaznaczall input[type=checkbox]", function (e) {
                                var status = $(this).is(":checked") ? true : false;
				$("input[type=checkbox]").prop("checked", status);
                            });
                        });
                    </script>';

    if (!$auth->acl_gets('a_', 'm_')) {
        $zawartosc .= '<div id="komunikaty"><div class="messages error">Nie jeste&#347; administratorem</div></div>';
    } else {
        if (!formularz_administracyjny_kiedy_skanowano()) {
            $dni = $nie_skanowano_od / (3600 * 24);
            $zawartosc .= '<div id="komunikaty"><div class="messages error">Pliki nie by&#322;y skanowane conajmniej ' . $dni . ' dni, przed usni&#281;ciem plik&#243;w uruchom <a href="upload_sprawdz_pliki.php" >procedur&#281; sprawdzania wolnych plik&#243;w!</a></div></div>';
        } else {
            $dni = $nie_skanowano_od / (3600 * 24);
            $zawartosc .= '<div id="komunikaty"><div class="messages status2">Pliki by&#322;y skanowane mniej ni&#380; ' . $dni . ' dni temu, jednak w ka&#380;dej chwili mo&#380;esz uruchomi&#263; <a href="upload_sprawdz_pliki.php" >procedur&#281; sprawdzania wolnych plik&#243;w!</a></div></div>';
        }

        $sql = "SELECT id, nazwa_pliku, miniatura, tytul, dodany, uid, stara_wgrywajka, sprawdzony FROM phpbb_podforak_upload WHERE uzyty = 0 AND sprawdzony != 0 ORDER BY dodany DESC, sprawdzony DESC";
//        $sql = "SELECT id, nazwa_pliku, miniatura, tytul, dodany, uid, stara_wgrywajka, sprawdzony FROM phpbb_podforak_upload WHERE uzyty = 0 AND sprawdzony != 0 AND dodany < 1432598400 ORDER BY uid ASC, sprawdzony DESC, dodany DESC";
//        $sql = "SELECT id, nazwa_pliku, miniatura, tytul, dodany, uid, stara_wgrywajka, sprawdzony FROM phpbb_podforak_upload WHERE uzyty = 0 AND sprawdzony != 0 AND uid <= 1 ORDER BY uid ASC, sprawdzony DESC, dodany DESC";

        if (!($result = $db->sql_query($sql))) {
            message_die(GENERAL_ERROR, 'Could not query posts table', '', __LINE__, __FILE__, $sql);
        }

        $zawartosc .= '<form id="adminform" enctype="multipart/form-data" method="POST" action="upload.php?action=admin&level=2">';
        $zawartosc .= '<table cellspacing="1" cellpading="0" class="forumline" align="center"  width="100%" style="margin-top: 0px;">';
        $zawartosc .= '<tr>
                    <th style="padding-left: 7px;"> # </th>
                    <th style="padding-left: 7px;" id="zaznaczall"> <input type="checkbox" name="zaznaczall" value="-1" /> </th>
                    <th width="55%" style="padding-left: 7px;"> Plik </th>
                    <th width="15%" style="padding-left: 7px;"> Doda&#322; </th>
                    <th width="15%" style="padding-left: 7px;"> Dodany </th>
                    <th width="15%" style="padding-left: 7px;"> Sprawdzony </th>
            </tr>';
        $i = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $zawartosc .= formularz_administracyjny_rekord($row, $i);
            $i++;
        }
        $zawartosc .= '<tr>
                            <td class="catBottom" align="center" colspan="6"><input type="submit" class="liteoption" value="Delete"/></td>
                       </tr>
                    </table>';
        $zawartosc .= '</form>';
    }

    return $zawartosc;
}

/**
 * uruchamia procedure sprawdzajaca uzycie plikow
 * @global type $db
 */
function formularz_administracyjny_procedura() {
    global $db;
    $uruchomiona = false;

    $sql = "SHOW FULL PROCESSLIST";
    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not call wgrywajka_sprawdzanie_plikow()', '', __LINE__, __FILE__, $sql);
    }

    while ($row = $db->sql_fetchrow($result)) {
        if (strpos($row['Info'], 'sprawdzanie_tmp') !== false) {
            $uruchomiona = true;
        }
    }

    if (!$uruchomiona) {
        $sql2 = "CALL wgrywajka_sprawdzanie_plikow();";
        if (!($result3 = $db->sql_query($sql2))) {
            message_die(GENERAL_ERROR, 'Could not call wgrywajka_sprawdzanie_plikow()', '', __LINE__, __FILE__, $sql2);
        }
        $zawartosc = '<div id="komunikaty"><div class="messages status2">Pomy&#347;lnie przeskanowano pliki! Za 5s zostaniesz przekierowany do formularza usuwania plik&#243;w...</div></div>';
    } else {
        $zawartosc = '<div id="komunikaty"><div class="messages error">Skanowanie jest ju&#380; uruchomione! Za 5s zostaniesz przekierowany do formularza usuwania plik&#243;w...</div></div>';
    }

    return $zawartosc;
}

function formularz_administracyjny_kiedy_skanowano() {
    global $db;
    global $nie_skanowano_od;
    $najstarszy = NULL;

    $sql = "SELECT MIN(sprawdzony) as najstarszy FROM phpbb_podforak_upload WHERE sprawdzony != 0";
    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not query phpbb_podforak_upload table', '', __LINE__, __FILE__, $sql);
    }

    while ($row = $db->sql_fetchrow($result)) {
        $najstarszy = $row['najstarszy'];
    }

    $dzis = time();
    if ($najstarszy != NULL && (($dzis - $najstarszy) < $nie_skanowano_od)) {
        return true;
    } else {
        return false;
    }
}

/**
 * wpis pojedynczego pliku w formularzu zarzadzania plikami wolnymi
 * @param type $plik
 * @return string
 */
function formularz_administracyjny_rekord($plik, $i) {
    $zawartosc .= '';
    $zawartosc .= '<tr>
                <td class="row1" style="padding: 7px;">' . $i . '</td>
                <td class="row1" style="padding: 7px;"><input type="checkbox" name="chk_group[]" value="' . $plik['id'] . '" /></td>
                <td class="row2" style="padding: 7px;">';
    if ($plik['stara_wgrywajka'] == 1) {
        $tytul = $plik['nazwa_pliku'];
        if ($plik['tytul'] != '') {
            $tytul = $plik['tytul'];
        }
        $zawartosc .= '<a href="http://podforak.rzeszow.pl/upload/' . $plik['nazwa_pliku'] . '" target="_blank">' . $tytul . '</a>';
    } else {
        $tytul = $plik['nazwa_pliku'];
        if ($plik['tytul'] != '') {
            $tytul = $plik['tytul'];
        }
        $zawartosc .= '<a href="upload/' . $plik['nazwa_pliku'] . '" target="_blank">' . $tytul . '</a>';
    }

    $zawartosc .= '</td>
                <td class="row1" style="padding: 7px;">';

    $nazwa_usera = pobierz_pokolorowana_nazwe($plik['uid']);
    if ($nazwa_usera != "") {
        $zawartosc .= $nazwa_usera;
    } else {
        $zawartosc .= "brak usera";
    }

    $zawartosc .= '</td>
                <td class="row2" style="padding: 7px;">' . date('d-m-Y h:i', $plik['dodany']) . '</td>
                <td class="row2" style="padding: 7px;">' . date('d-m-Y h:i', $plik['sprawdzony']) . '</td>
            </tr>';

    return $zawartosc;
}

/**
 * wyswietla panel statystyk
 * @global type $userdata
 * @global type $db
 * @return string
 */
function statystyki_administracja() {
    global $auth;
    $zawartosc = "";

    if (!$auth->acl_gets('a_', 'm_')) {
        $zawartosc = '<div id="komunikaty"><div class="messages error">Nie jeste&#347; administratorem</div></div>';
    } else {
        $zawartosc = '<table class="forumline" width="100%" align="center" cellspacing="1" style="margin-top: 0px;" cellpading="0">
            <tr>
                    <th width="100%" colspan="3" style="padding-left: 7px;"> Statystyki </th>
            </tr>
            <tr>
                    <td class="row1" style="padding: 7px; width: 22%;"> <b>Liczba dodanych plik&#243;w:</b></td>
                    <td class="row2" style="padding: 7px; width: 58%;">' . ststystyki_liczba_plikow() . '</td>
                    <td class="row2" rowspan="2" style="padding: 7px; width: 20%;"><a href="upload.php?action=admin&level=1">Zarz&#261;dzaj wolnymi plikami</a></td>
            </tr>
            <tr>
                    <td class="row1" style="padding: 7px; width: 22%;"> <b>&#321;&#261;czny rozmiar plik&#243;w na dysku:</b></td>
                    <td class="row2" style="padding: 7px; width: 58%;">' . statystyki_rozmiar_plikow() . '</td>
            </tr>
        </table>';

        $zawartosc .= '<br /><br /><center><a href="upload.php?action=doreupu">PLIKI DO REUPU</a> <a href="upload.php?action=allegro">AUKCJE DO ARCHIWIZACJI</a></center>';
    }

    return $zawartosc;
}

/**
 * masowe usuwanie plikow (np. z formularza dministracyjnego)
 * @param type $pliki
 * @return string
 */
function usuwanie_plikow($pliki) {
    $zawartosc = "";
    foreach ($pliki as $plik) {
        $zawartosc .= usun_plik($plik, true);
        $zawartosc .= "<br />";
    }

    $zawartosc .= '<center><a href ="upload.php?action=admin&level=1" class="liteoption">Klinij aby wr&#243;&#263; do poprzedniego ekranu</a></center>';

    return $zawartosc;
}

/**
 * zlicza liczbe plikow i miniatur na podstawie wpisow z bazy
 * @global type $db
 * @return string
 */
function ststystyki_liczba_plikow() {
    global $db;
    $pliki = array();

    $zawartosc = "";
    $sql = "SELECT COUNT('x') as liczba FROM phpbb_podforak_upload UNION SELECT COUNT('x') FROM phpbb_podforak_upload WHERE miniatura != ''";

    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not query posts table', '', __LINE__, __FILE__, $sql);
    }

    while ($row = $db->sql_fetchrow($result)) {
        $pliki[] = $row['liczba'];
    }

    if (count($pliki) > 1) {
        $zawartosc = $pliki[0] . " (" . $pliki[1] . " z miniaturami)";
    } else {
        $zawartosc = "blad pobierania danych";
    }

    return $zawartosc;
}

/**
 * oblicza rozmiar plikow zapisanych w folderze upload
 * @return type
 */
function statystyki_rozmiar_plikow() {
    $files = array();
    $zawartosc = "";
    $dir = @opendir("./upload");
    if ($dir) {
        while ($file = readdir($dir)) {
            if (substr($file, 0, 1) != "." && substr($file, 0, 1) != "..") {
                if (strpos($file, '.') !== false) {
                    $files[] = $file;
                }
            }
        }
        closedir($dir);
    } else {
        $zawartosc .= 'Brak katalogu plik&#243;w';
    }

    if (!isset($files)) {
        $zawartosc .= 'Brak plik&#243;w';
    } else {
        $size = 0;
        foreach ($files as $file) {
            $size += filesize("./upload/" . $file);
        }

        $zawartosc .= formatSizeUnits($size);
    }

    return $zawartosc;
}

/**
 * formatuje rozmiar odpowiednio dopasowujac jednostke
 * @param type $bytes
 * @return string
 */
function formatSizeUnits($bytes) {
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }

    return $bytes;
}

/**
 * zwraca microtime
 * @return type
 */
function getmicrotime() {
    list($usec, $sec) = explode(' ', microtime());
    return ((float) $usec + (float) $sec);
}

function przenies_na_strone($start) {
    if ($start > 0) {
        $str = "&start=$start";
    } else {
        $str = "";
    }
    header('Refresh: 4; URL=upload.php?action=mojepliki' . $str);
}

function przenies_do_administracji() {
    header('Refresh: 5; URL=upload.php?action=admin&level=1');
}
