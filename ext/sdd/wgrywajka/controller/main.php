<?php

/**
 *
 * Podforakowa wgrywajka. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2016, SDD, http://podforak.pl
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sdd\wgrywajka\controller;

/**
 * Podforakowa wgrywajka main controller.
 */
class main {

    private $PODFORAK_UPLOAD_TABLE = 'phpbb_podforak_upload';

    /* @var \phpbb\config\config */
    protected $config;

    /* @var \phpbb\controller\helper */
    protected $helper;

    /* @var \phpbb\template\template */
    protected $template;

    /* @var \phpbb\user */
    protected $user;

    /* @var \phpbb\request\request */
    private $request;

    /* @var \phpbb\db\driver\driver_interface */
    private $db;

    /* @var \phpbb\auth\auth */
    private $auth;

    /* tutaj ustalasz rozmiar mnożone przez 1024 żeby było w kilo bajtach  */
    private $max_rozmiar;

    /* dozwolone rozszerzenia plików */
    private $dozowlone_pliki;

    /** @var string phpBB root path */
    protected $root_path;

    /**
     * Constructor
     *
     * @param \phpbb\config\config                 $config
     * @param \phpbb\controller\helper             $helper
     * @param \phpbb\template\template             $template
     * @param \phpbb\user                          $user
     * @param \phpbb\request\request               $request
     * @param \phpbb\db\driver\driver_interface    $db
     * @param \phpbb\auth\auth                     $auth
     */
    public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, \phpbb\request\request $request, \phpbb\db\driver\driver_interface $db, \phpbb\auth\auth $auth, $root_path) {
        $this->config = $config;
        $this->helper = $helper;
        $this->template = $template;
        $this->user = $user;
        $this->request = $request;
        $this->db = $db;
        $this->auth = $auth;
        $this->root_path = $root_path;
        $this->max_rozmiar = 1024 * 1024;
        $this->dozowlone_pliki = '"png", "gif", "jpeg", "jpg", "pdf", "zip", "xls", "xlsx", "ppt", "pps", "pptx", "ppsx", "doc", "docx"';
    }

    /**
     * Demo controller for route /demo/{name}
     *
     * @param string $name
     *
     * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
     */
    public function handle($name) {
        $l_message = !$this->config['sdd_wgrywajka_wlaczona'] ? 'DEMO_HELLO' : 'DEMO_GOODBYE';
        $this->template->assign_var('DEMO_MESSAGE', $this->user->lang($l_message, $name));

        return $this->helper->render('demo_body.html', $name);
    }

    /**
     * wyswietalanie formularza wgrywania prostego
     * @return string - htmlformularza
     */
    public function proste() {
        page_header('Wgrywanie proste');

        $this->template->assign_block_vars('intro', array(
            'dozowlone_pliki' => $this->dozowlone_pliki,
            'dozowlone_pliki_label' => $this->dozwolone_pliki(),
            'max_rozmiar' => $this->max_rozmiar,
            'max_rozmiar_label' => round($this->max_rozmiar / 1048576, 2)
        ));

        return $this->helper->render('proste_body.html');
    }

    public function zaawansowane() {
        page_header('Wgrywanie zaawansowane');

        $this->template->assign_block_vars('intro', array(
            'dozowlone_pliki' => $this->dozowlone_pliki,
            'dozowlone_pliki_label' => $this->dozwolone_pliki(),
            'max_rozmiar' => $this->max_rozmiar * 10,
            'max_rozmiar_label' => round($this->max_rozmiar * 10 / 1048576, 2)
        ));

        return $this->helper->render('zaawansowane_body.html');
    }

    public function wynik() {
        page_header('Wynik wgrywania');

        $plik = $this->request->file('plik');
        $pictitle = $this->request->variable('pic_title', '');
        $picdesc = $this->request->variable('pic_desc', '');
        $zmianarozmiaru = $this->request->variable('zmianarozmiaru', 0);
        $dodac = $this->request->variable('dodac', 1);
        $miejsceznaku = $this->request->variable('miejsceznaku', 'pd');
        $userdata = $this->user->data;

        if (is_uploaded_file($plik['tmp_name'])) {
            $nazwa = $plik['name'];
            $dodano = time();
            if ($this->walidacja_rozszerzenia($plik['name'])) {
                if ($this->is_obraz($nazwa)) {
                    $nowa_nazwa = 'u' . $userdata['user_id'] . '_' . $dodano . '.' . pathinfo($nazwa, PATHINFO_EXTENSION);
                    if ($this->zapisz_obraz($plik, $this->root_path . 'upload/' . $nowa_nazwa, intval($zmianarozmiaru), intval($dodac), $miejsceznaku)) {
                        $nazwa_miniatury = 'upload/u' . $userdata['user_id'] . '_' . $dodano . '_thumb.jpg';

                        if ($pictitle != '') {
                            $opis = $pictitle;
                        } else {
                            $opis = $nazwa;
                        }

                        if ($this->utworz_miniature($this->root_path . 'upload/' . $nowa_nazwa, $this->root_path . $nazwa_miniatury)) {
                            if (!$this->dodaj_wpis_bazy($nowa_nazwa, $opis, $picdesc, $dodano, $nazwa_miniatury)) {
                                return $this->blad_ogolny();
                            }
                            return $this->ekran_koncowy_wgrywania($nowa_nazwa, $nazwa_miniatury);
                        } else {
                            if (!$this->dodaj_wpis_bazy($nowa_nazwa, $opis, $picdesc, $dodano, '')) {
                                return $this->blad_ogolny();
                            }

                            return $this->ekran_koncowy_wgrywania($nowa_nazwa, '');
                        }
                    }
                    return $this->blad_ogolny_pliku();
                } else {
                    $nowa_nazwa = 'u' . $userdata['user_id'] . '_' . $dodano . '.' . pathinfo($nazwa, PATHINFO_EXTENSION);
                    $this->przenies_plik_tmp($plik['tmp_name'], $nowa_nazwa);

                    if ($pictitle != '') {
                        $opis = $pictitle;
                    } else {
                        $opis = $nazwa;
                    }

                    if (!$this->dodaj_wpis_bazy($nowa_nazwa, $opis, $picdesc, $dodano, '')) {
                        return blad_ogolny();
                    }

                    return $this->ekran_koncowy_wgrywania($nowa_nazwa, '');
                }
            } else {
                return $this->blad_rozszerzenia();
            }
        } else {
            return $this->blad_ogolny();
        }
    }

    public function mojepliki() {
        $name = "mojepliki";
        $l_message = !$this->config['sdd_wgrywajka_wlaczona'] ? 'DEMO_HELLO' : 'DEMO_GOODBYE';
        $this->template->assign_var('DEMO_MESSAGE', $this->user->lang($l_message, $name));

        return $this->helper->render('demo_body.html', $name);
    }

    public function szukaj() {
        $name = "mojepliki";
        $l_message = !$this->config['sdd_wgrywajka_wlaczona'] ? 'DEMO_HELLO' : 'DEMO_GOODBYE';
        $this->template->assign_var('DEMO_MESSAGE', $this->user->lang($l_message, $name));

        return $this->helper->render('demo_body.html', $name);
    }

    private function dozwolone_pliki() {
        return str_replace('"', '', $this->dozowlone_pliki);
    }

    /**
     * dodatkowa walidacja rozszerzenia
     * na wszelki wypadek gdyby w js cos poszlo nie tak
     * lub ktos probowal go oszukac
     * @param unknown_type $nazwa - nazwa pliku
     */
    function walidacja_rozszerzenia($nazwa) {
        $rozszerzenia = explode(',', str_replace('"', '', $this->dozowlone_pliki));
        $ext = pathinfo($nazwa, PATHINFO_EXTENSION);

        return $this->contains($ext, $rozszerzenia);
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
     * blad zabronionego rozszerzenia pliku
     * @return string
     */
    function blad_rozszerzenia() {
        $this->template->assign_block_vars('intro', array(
            'wynik' => '<div class="messages error"> Wybrano niedozwolony typ pliku!</div>'
        ));

        return $this->helper->render('error_body.html');
    }

    /**
     * blad ogolny zapisu do bazy
     * @return string
     */
    function blad_ogolny() {
        $this->template->assign_block_vars('intro', array(
            'wynik' => '<div class="messages error"> B&#322;&#261;d og&#243;lny tworzenia nowego wpisu do bazy!</div>'
        ));

        return $this->helper->render('error_body.html');
    }

    /**
     * blad ogolny zapisu pliku
     * @return string
     */
    function blad_ogolny_pliku() {
        $this->template->assign_block_vars('intro', array(
            'wynik' => '<div class="messages error"> B&#322;&#261;d og&#243;lny zapisu pliku!</div>'
        ));

        return $this->helper->render('error_body.html');
    }

    /**
     * zapisuje plik w miescu przeznaczenia z odpowiednia nazwa
     * @param unknown_type $nazwa_tmp - nazwa pliku tmp
     * @param unknown_type $nazwa_koncowa - nazwa pod ktora ma zostac zapisany plik
     */
    function przenies_plik_tmp($nazwa_tmp, $nazwa_koncowa) {
        move_uploaded_file($nazwa_tmp, $this->root_path . 'upload/' . $nazwa_koncowa);
    }

    /**
     * sprawdza czy plik jest obrazem
     * @param unknown_type $nazwa
     * @return boolean
     */
    function is_obraz($nazwa) {
        $ext = pathinfo($nazwa, PATHINFO_EXTENSION);
        $rozszerzenia_obrazow = array('png', 'gif', 'jpeg', 'jpg');

        return $this->contains($ext, $rozszerzenia_obrazow);
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
        $sql = "INSERT INTO " . $this->PODFORAK_UPLOAD_TABLE . " (miniatura, nazwa_pliku, tytul, opis, uid, dodany)
				VALUES ('" . $miniatura . "', '" . $nazwa_pliku . "', '" . $tytul . "', '" . $opis . "', " . $this->user->data['user_id'] . ", " . $dodano . ")";

        $result = $this->db->sql_query($sql);

        return $result;
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
        $typ = strtolower(pathinfo($nazwa, PATHINFO_EXTENSION));

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
                    $znaczek = $this->root_path . "upload/wymagane/znakwodny.png";
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
        $typ = strtolower(pathinfo($wgrany_plik, PATHINFO_EXTENSION));

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
     * ekran koncowy wgrywania
     * @param unknown_type $nazwa_pliku - nazwa pliku
     * @param unknown_type $nazwa_miniatury - nazwa miniatury (jesli istnieje)
     */
    function ekran_koncowy_wgrywania($nazwa_pliku, $nazwa_miniatury = '', $stara_wgrywajka = 0) {
        $zawartosc = '';
        $url = generate_board_url();
        if ($stara_wgrywajka == 1) {
            $url = 'http://podforak.rzeszow.pl/upload/';
        }

        if ($this->is_obraz($nazwa_pliku) && $nazwa_miniatury != '') {
            $zawartosc .= '[URL=' . $url . '/upload/' . $nazwa_pliku . '][img]' . $url . '/' . $nazwa_miniatury . '[/img][/URL]';
        } else if ($this->is_obraz($nazwa_pliku)) {
            $zawartosc .= '[IMG]' . $url . '/upload/' . $nazwa_pliku . '[/img]';
        } else {
            $zawartosc .= $url . '/upload/' . $nazwa_pliku;
        }

        $this->template->assign_block_vars('intro', array(
            'wynik' => $zawartosc
        ));

        return $this->helper->render('wynik_body.html');
    }

}
