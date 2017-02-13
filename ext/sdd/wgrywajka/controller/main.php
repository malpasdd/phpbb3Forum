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

    /**  */
    protected $nie_skanowano_od;

    /**
     * Constructor
     * 
     * @param \phpbb\config\config $config
     * @param \phpbb\controller\helper $helper
     * @param \phpbb\template\template $template
     * @param \phpbb\user $user
     * @param \phpbb\request\request $request
     * @param \phpbb\db\driver\driver_interface $db
     * @param \phpbb\auth\auth $auth
     * @param type $root_path
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
        $this->nie_skanowano_od = 2 * 24 * 3600;
        $this->dozowlone_pliki = '"png", "gif", "jpeg", "jpg", "pdf", "zip", "xls", "xlsx", "ppt", "pps", "pptx", "ppsx", "doc", "docx"';
    }

    /**
     * wyswietalanie formularza wgrywania prostego
     * @return string - htmlformularza
     */
    public function proste() {
        page_header('Wgrywanie proste');
        if (!$this->czy_zalogowany()) {
            login_box();
        }

        $this->template->assign_block_vars('intro', array(
            'dozowlone_pliki' => $this->dozowlone_pliki,
            'dozowlone_pliki_label' => $this->dozwolone_pliki(),
            'max_rozmiar' => $this->max_rozmiar,
            'max_rozmiar_label' => round($this->max_rozmiar / 1048576, 2),
            'admin' => $this->daj_admin_link()
        ));

        return $this->helper->render('proste_body.html');
    }

    public function zaawansowane() {
        page_header('Wgrywanie zaawansowane');
        if (!$this->czy_zalogowany()) {
            login_box();
        }

        $this->template->assign_block_vars('intro', array(
            'dozowlone_pliki' => $this->dozowlone_pliki,
            'dozowlone_pliki_label' => $this->dozwolone_pliki(),
            'max_rozmiar' => $this->max_rozmiar * 10,
            'max_rozmiar_label' => round($this->max_rozmiar * 10 / 1048576, 2),
            'admin' => $this->daj_admin_link()
        ));

        return $this->helper->render('zaawansowane_body.html');
    }

    public function mojepliki() {
        page_header('Moje pliki');
        if (!$this->czy_zalogowany()) {
            login_box();
        }
        $start = $this->request->variable('start', 0);

        return $this->pliki_usera($this->user->data['user_id'], $start, 'mojepliki');
    }

    public function szukaj() {
        page_header('Szukaj');
        if (!$this->czy_zalogowany()) {
            login_box();
        }

        $pic_name = $this->request->variable('pic_name', '');
        $pic_owner = $this->request->variable('pic_owner', '');

        if (!empty($pic_name)) {

            return $this->pobierz_pliki_nazwa($pic_name);
        } else if (!empty($pic_owner)) {

            $uid = $this->pobierz_id_usera($pic_owner);
            if ($uid > 0) {
                redirect('wgrywajka/user?uid=' . $uid);
            } else {
                return $this->brak_wynikow();
            }
        } else {

            $this->template->assign_block_vars('intro', array(
                'wynik' => '',
                'admin' => $this->daj_admin_link()
            ));
            return $this->helper->render('szukaj_body.html');
        }
    }

    public function szukaj_pliki_usera() {
        page_header('Pliki użytkownika');
        if (!$this->czy_zalogowany()) {
            login_box();
        }
        $start = $this->request->variable('start', 0);
        $uid = $this->request->variable('uid', 0);

        return $this->pliki_usera($uid, $start, 'user?uid=' . $uid);
    }

    public function szukaj_posty_z_plikiem() {
        page_header('Posty z plikiem');
        if (!$this->czy_zalogowany()) {
            login_box();
        }

        $fid = $this->request->variable('fid', 0);

        return $this->wyszukiwanie_postow_id($fid);
    }

    public function kod() {
        page_header('Pobierz kod');
        if (!$this->czy_zalogowany()) {
            login_box();
        }

        $fid = $this->request->variable('fid', 0);

        return $this->pobierz_kod($fid);
    }

    public function prywatny() {
        page_header('Oznacz jako prywatny');
        if (!$this->czy_zalogowany()) {
            login_box();
        }
        $fid = $this->request->variable('fid', 0);
        $status = request_var('status', 0);

        return $this->oznacz_prywatny($fid, $status);
    }

    public function usun() {
        page_header('Usuń plik');
        if (!$this->czy_zalogowany()) {
            login_box();
        }
        $fid = $this->request->variable('fid', 0);

        $this->template->assign_block_vars('intro', array(
            'wynik' => $this->usun_plik($fid),
            'admin' => $this->daj_admin_link()
        ));

        return $this->helper->render('error_body.html');
    }

    public function admin() {
        page_header('Administracja');
        if (!$this->czy_zalogowany()) {
            login_box();
        }
        $zawartosc = '';

        if (!$this->auth->acl_gets('a_', 'm_')) {
            $zawartosc = $this->komunikat_brak_uprawnien();
        } else {
            $zawartosc = $this->statystyki_administracja();
        }
        $this->template->assign_block_vars('intro', array(
            'wynik' => $zawartosc,
            'admin' => $this->daj_admin_link()
        ));

        return $this->helper->render('error_body.html');
    }

    public function adminzarzadzaj() {
        page_header('Administracja');
        if (!$this->czy_zalogowany()) {
            login_box();
        }

        if (!$this->auth->acl_gets('a_', 'm_')) {
            $zawartosc = $this->komunikat_brak_uprawnien();
        } else {
            $krok = request_var('k', 0);
            if ($krok > 0) {
                $zaznaczone_pliki = $this->request->variable("chk_group", array('' => ''));
                $zawartosc = $this->usuwanie_plikow($zaznaczone_pliki);
            } else {
                $zawartosc = $this->formularz_administracyjny();
            }
        }
        $this->template->assign_block_vars('intro', array(
            'wynik' => $zawartosc,
            'admin' => $this->daj_admin_link()
        ));

        return $this->helper->render('error_body.html');
    }

    private function komunikat_brak_uprawnien() {
        return '<div id="komunikaty"><div class="messages error">Nie jeste&#347; administratorem</div></div>';
    }

    /**
     * masowe usuwanie plikow (np. z formularza dministracyjnego)
     * @param type $pliki
     * @return string
     */
    function usuwanie_plikow($pliki) {
        $zawartosc = "";
        foreach ($pliki as $plik) {
            $zawartosc .= $this->usun_plik($plik, true);
            $zawartosc .= "<br />";
        }
//        $zawartosc .= '<center><a href ="upload.php?action=admin&level=1" class="liteoption">Klinij aby wr&#243;&#263; do poprzedniego ekranu</a></center>';

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
        $zawartosc .= '<script type="text/javascript">
            function toggle(source) {
                checkboxes = document.getElementsByName(\'chk_group[]\');
                for (var i = 0, n = checkboxes.length; i < n; i++) {
                    checkboxes[i].checked = source.checked;
                }
            }
        </script>';

        if (!$this->auth->acl_gets('a_', 'm_')) {
            $zawartosc .= $this->komunikat_brak_uprawnien();
        } else {
            if (!$this->formularz_administracyjny_kiedy_skanowano()) {
                $dni = $this->nie_skanowano_od / (3600 * 24);
                $zawartosc .= '<div id="komunikaty"><div class="messages error">Pliki nie by&#322;y skanowane conajmniej ' . $dni . ' dni, przed usni&#281;ciem plik&#243;w uruchom <a href="upload_sprawdz_pliki.php" >procedur&#281; sprawdzania wolnych plik&#243;w!</a></div></div>';
            } else {
                $dni = $this->nie_skanowano_od / (3600 * 24);
                $zawartosc .= '<div id="komunikaty"><div class="messages status2">Pliki by&#322;y skanowane mniej ni&#380; ' . $dni . ' dni temu, jednak w ka&#380;dej chwili mo&#380;esz uruchomi&#263; <a href="upload_sprawdz_pliki.php" >procedur&#281; sprawdzania wolnych plik&#243;w!</a></div></div>';
            }

            $sql = "SELECT id, nazwa_pliku, miniatura, tytul, dodany, uid, stara_wgrywajka, sprawdzony FROM phpbb_podforak_upload WHERE uzyty = 0 AND sprawdzony != 0 ORDER BY dodany DESC, sprawdzony DESC";
//        $sql = "SELECT id, nazwa_pliku, miniatura, tytul, dodany, uid, stara_wgrywajka, sprawdzony FROM phpbb_podforak_upload WHERE uzyty = 0 AND sprawdzony != 0 AND dodany < 1432598400 ORDER BY uid ASC, sprawdzony DESC, dodany DESC";
//        $sql = "SELECT id, nazwa_pliku, miniatura, tytul, dodany, uid, stara_wgrywajka, sprawdzony FROM phpbb_podforak_upload WHERE uzyty = 0 AND sprawdzony != 0 AND uid <= 1 ORDER BY uid ASC, sprawdzony DESC, dodany DESC";

            $result = $this->db->sql_query($sql);

            $zawartosc .= '<form id="adminform" enctype="multipart/form-data" method="POST" action="./adminzarzadzaj?k=2">';
            $zawartosc .= '<table cellspacing="1" cellpading="0" class="forumline" align="center"  width="100%" style="margin-top: 0px;">';
            $zawartosc .= '<tr>
                    <th style="padding-left: 7px;"> # </th>
                    <th style="padding-left: 7px;" id="zaznaczall"> <input type="checkbox" name="zaznaczall" onClick="toggle(this)" value="-1" /> </th>
                    <th width="55%" style="padding-left: 7px;"> Plik </th>
                    <th width="15%" style="padding-left: 7px;"> Doda&#322; </th>
                    <th width="15%" style="padding-left: 7px;"> Dodany </th>
                    <th width="15%" style="padding-left: 7px;"> Sprawdzony </th>
            </tr>';
            $i = 1;
            while ($row = $this->db->sql_fetchrow($result)) {
                $zawartosc .= $this->formularz_administracyjny_rekord($row, $i);
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
     * wpis pojedynczego pliku w formularzu zarzadzania plikami wolnymi
     * @param type $plik
     * @return string
     */
    private function formularz_administracyjny_rekord($plik, $i) {
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

        $nazwa_usera = $this->pobierz_pokolorowana_nazwe($plik['uid']);
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
     * 
     * @global type $nie_skanowano_od
     * @return boolean
     */
    private function formularz_administracyjny_kiedy_skanowano() {
        global $nie_skanowano_od;
        $najstarszy = NULL;

        $sql = "SELECT MIN(sprawdzony) as najstarszy FROM phpbb_podforak_upload WHERE sprawdzony != 0";
        $result = $this->db->sql_query($sql);
        while ($row = $this->db->sql_fetchrow($result)) {
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
     * Sprawdza czy użytkownik jest zarejestrowany
     * @return type
     */
    private function czy_zalogowany() {
        return $this->user->data['is_registered'];
    }

    /**
     * Zwraca link do panelu administracji
     * @return string
     */
    private function daj_admin_link() {
        $admin_link = "";
        if ($this->auth->acl_gets('a_', 'm_')) {
            $admin_link = "<a class=\"forumlink\" href=\"./admin\">Administracja wgrywajki</a>";
        }

        return $admin_link;
    }

    /**
     * wyswietla panel statystyk
     * @return string
     */
    private function statystyki_administracja() {
        $zawartosc = '<table class="forumline" width="100%" align="center" cellspacing="1" style="margin-top: 0px;" cellpading="0">
            <tr>
                    <th width="100%" colspan="3" style="padding-left: 7px;"> Statystyki </th>
            </tr>
            <tr>
                    <td class="row1" style="padding: 7px; width: 22%;"> <b>Liczba dodanych plik&#243;w:</b></td>
                    <td class="row2" style="padding: 7px; width: 58%;">' . $this->ststystyki_liczba_plikow() . '</td>
                    <td class="row2" rowspan="2" style="padding: 7px; width: 20%;"><a href="./adminzarzadzaj">Zarz&#261;dzaj wolnymi plikami</a></td>
            </tr>
            <tr>
                    <td class="row1" style="padding: 7px; width: 22%;"> <b>&#321;&#261;czny rozmiar plik&#243;w na dysku:</b></td>
                    <td class="row2" style="padding: 7px; width: 58%;">' . $this->statystyki_rozmiar_plikow() . '</td>
            </tr>
        </table>';

//            $zawartosc .= '<br /><br /><center><a href="upload.php?action=doreupu">PLIKI DO REUPU</a> <a href="upload.php?action=allegro">AUKCJE DO ARCHIWIZACJI</a></center>';

        return $zawartosc;
    }

    /**
     * oblicza rozmiar plikow zapisanych w folderze upload
     * @return type
     */
    private function statystyki_rozmiar_plikow() {
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

            $zawartosc .= $this->formatSizeUnits($size);
        }

        return $zawartosc;
    }

    /**
     * formatuje rozmiar odpowiednio dopasowujac jednostke
     * @param type $bytes
     * @return string
     */
    private function formatSizeUnits($bytes) {
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
     * zlicza liczbe plikow i miniatur na podstawie wpisow z bazy
     * @return string
     */
    function ststystyki_liczba_plikow() {
        $pliki = array();

        $zawartosc = "";
        $sql = "SELECT COUNT('x') as liczba FROM phpbb_podforak_upload UNION SELECT COUNT('x') FROM phpbb_podforak_upload WHERE miniatura != ''";

        $result = $this->db->sql_query($sql);
        while ($row = $this->db->sql_fetchrow($result)) {
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
     * usuwa plik
     * @param type $fid
     * @return string
     */
    private function usun_plik($fid) {
        $userdata = $this->user->data;
        $uid = 0;
        $nazwa_pliku = "";
        $miniatura = "";
        $zawartosc = "";
        $tytul = "";
        $stara_wgrywajka = 0;

        $sql = "SELECT nazwa_pliku, uid, stara_wgrywajka, miniatura, tytul FROM " . $this->PODFORAK_UPLOAD_TABLE . " WHERE id = $fid  ORDER BY dodany DESC";
        $result = $this->db->sql_query($sql);

        while ($row = $this->db->sql_fetchrow($result)) {
            $uid = $row['uid'];
            $nazwa_pliku = $row['nazwa_pliku'];
            $miniatura = $row['miniatura'];
            $tytul = $row['tytul'];
            $stara_wgrywajka = $row['stara_wgrywajka'];
        }

        if ($userdata['user_id'] == $uid || $this->auth->acl_gets('a_', 'm_')) {

            $sql = "SELECT count(*) as lpostow FROM podf3_posts WHERE post_text LIKE '%upload/" . pathinfo($nazwa_pliku, PATHINFO_FILENAME) . "%'";
            $result = $this->db->sql_query($sql);
            $liczba_postow = 0;
            while ($row = $this->db->sql_fetchrow($result)) {
                $liczba_postow += $row['lpostow'];
            }

            $sql = "SELECT count(*) as lpostow FROM podf3_users u WHERE u.user_sig LIKE '%upload/" . pathinfo($nazwa_pliku, PATHINFO_FILENAME) . "%'";
//        var_dump($sql);
            $result = $this->db->sql_query($sql);
            while ($row = $this->db->sql_fetchrow($result)) {
                $liczba_postow += $row['lpostow'];
            }

            $sql = "SELECT count(*) as lpostow FROM podf3_privmsgs pt WHERE pt.message_text LIKE '%upload/" . pathinfo($nazwa_pliku, PATHINFO_FILENAME) . "%'";
//        var_dump($sql);
            $result = $this->db->sql_query($sql);
            while ($row = $this->db->sql_fetchrow($result)) {
                $liczba_postow += $row['lpostow'];
            }

            $sql = "SELECT count(*) as lpostow FROM podf3_mchat WHERE message LIKE '%upload/" . pathinfo($nazwa_pliku, PATHINFO_FILENAME) . "%'";
//        var_dump($sql);
            $result = $this->db->sql_query($sql);
            while ($row = $this->db->sql_fetchrow($result)) {
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
                    $this->usun_wpis_pliku($fid);
                    $zawartosc = '<div id="komunikaty"><div class="messages status2">Pomy&#347;lnie suni&#281;to plik ' . $tytul . '!</div></div>';
                } else {
                    $zawartosc = '<div id="komunikaty"><div class="messages error">Nie znaleziono pliku  ' . $tytul . '!</div></div>';
                }
            }
        } else {
            $zawartosc = $this->komunikat_brak_uprawnien();
        }

        return $zawartosc;
    }

    /**
     * usuwa wpis bazy danych pliku
     * @param type $fid
     */
    private function usun_wpis_pliku($fid) {

        $sql2 = "DELETE FROM " . $this->PODFORAK_UPLOAD_TABLE . " WHERE id = $fid";
        $this->db->sql_query($sql2);
    }

    /**
     * zmienia status pliku na prywatny/publiczny
     * @param type $plik_id
     * @param type $status
     * @return string
     */
    private function oznacz_prywatny($plik_id, $status) {
        $userdata = $this->user->data;
        $uid = NULL;
        $zawartosc = "";

        $sql = "SELECT uid FROM " . $this->PODFORAK_UPLOAD_TABLE . " WHERE id = $plik_id  ORDER BY dodany DESC";
        $result = $this->db->sql_query($sql);

        while ($row = $this->db->sql_fetchrow($result)) {
            $uid = $row['uid'];
        }

        if (isset($uid) && $uid == $userdata['user_id'] || $this->auth->acl_gets('a_', 'm_')) {
            $sql = "UPDATE phpbb_podforak_upload SET prywatny =  $status WHERE id = $plik_id";

            if (!$this->db->sql_query($sql)) {
                $zawartosc = '<div id="komunikaty"><div class="messages error">B&#322;&#261;d</div></div>';
            } else {
                $zawartosc = '<div id="komunikaty"><div class="messages status">Poprawnie zmieniono status pliku.</div></div>';
            }
        } else {
            $zawartosc = '<div id="komunikaty"><div class="messages error">Musisz by&#263; w&#322;a&#347;cicielem pliku!</div></div>';
        }

        $this->template->assign_block_vars('intro', array(
            'wynik' => $zawartosc,
            'admin' => $this->daj_admin_link()
        ));

        return $this->helper->render('error_body.html');
    }

    /**
     * pobiera id uzytkownika na podstawie nazwy
     * @param type $username
     * @return type
     */
    private function pobierz_id_usera($username) {
        $id = 0;
        $sql = "SELECT user_id  FROM " . USERS_TABLE . " WHERE username = '$username'";
        $result = $this->db->sql_query($sql);
        while ($row = $this->db->sql_fetchrow($result)) {
            $id = $row['user_id'];
        }
        return $id;
    }

    /**
     * zwraca kod zadanego pliku do wklejenia na forum
     * @param type $fid
     * @return string
     */
    private function pobierz_kod($fid) {
        $nazwa_pliku = '';
        $nazwa_miniatury = '';
        $stara_wgrywajka = 0;

        $sql = "SELECT * FROM " . $this->PODFORAK_UPLOAD_TABLE . " WHERE id = " . $fid . " ORDER BY dodany DESC";
        if (!($result = $this->db->sql_query($sql))) {
            return $this->brak_wynikow();
        } else {
            while ($row = $this->db->sql_fetchrow($result)) {
                $nazwa_pliku = $row['nazwa_pliku'];
                $nazwa_miniatury = $row['miniatura'];
                $stara_wgrywajka = $row['stara_wgrywajka'];
            }

            if ($nazwa_pliku != '' && $nazwa_miniatury != '') {
                return $this->ekran_koncowy_wgrywania($nazwa_pliku, $nazwa_miniatury, $stara_wgrywajka);
            } elseif ($nazwa_pliku != '') {
                return $this->ekran_koncowy_wgrywania($nazwa_pliku, $nazwa_miniatury, $stara_wgrywajka);
            } else {
                return $this->brak_wynikow();
            }
        }
    }

    /**
     * wyświetla komunikat o braku wyników
     * @return type
     */
    private function brak_wynikow() {
        $zawartosc = '<table cellspacing="1" cellpading="0" class="forumline" align="center"  width="100%" style="margin-top: 0px;">
						<tr>
							<th style="padding-left: 7px;" width="100%" colspan="4">
								Informacja
							</th>
						</tr>
						</tr>
							<td class="row1" align="center" colspan="4">Brak wynik&#243;w</td>
						</tr>
					</table>';

        $this->template->assign_block_vars('intro', array(
            'wynik' => $zawartosc,
            'admin' => $this->daj_admin_link()
        ));

        return $this->helper->render('error_body.html');
    }

    /**
     * Pobiera listę plików po nazwie
     * @param type $filename
     * @return type
     */
    private function pobierz_pliki_nazwa($filename) {

        $edycja = false;
        $zawartosc = "";

        $filename = str_replace('*', '%', $filename);
        $sql = "SELECT * FROM " . $this->PODFORAK_UPLOAD_TABLE . " WHERE LOWER(nazwa_pliku) LIKE '" . strtolower($filename) . "' OR LOWER(tytul) LIKE '" . strtolower($filename) . "' ORDER BY dodany DESC";

        $result = $this->db->sql_query($sql);
        $index = 0;
        while ($row = $this->db->sql_fetchrow($result)) {
            $index++;
            $zawartosc .= $this->lista_plikow_wiersz($row, $index, $edycja, true);
        }

        $this->template->assign_block_vars('intro', array(
            'naglowek' => 'Wyniki dla: ' . str_replace("%", "*", $filename),
            'content' => $zawartosc,
            'admin' => $this->daj_admin_link()
        ));

        return $this->helper->render('pliki_usera_body.html');
    }

    /**
     * wyszukiwnanie postow z z plikiem o zadanym id
     * @param type $plik_id
     * @return string
     */
    private function wyszukiwanie_postow_id($plik_id) {

        $userdata = $this->user->data;
        $nazwa_pliku = NULL;
        $prywatny = NULL;
        $zawartosc = '';
        $sql = "SELECT nazwa_pliku, uid, prywatny FROM " . $this->PODFORAK_UPLOAD_TABLE . " WHERE id = $plik_id  ORDER BY dodany DESC";

        $result = $this->db->sql_query($sql);
        while ($row = $this->db->sql_fetchrow($result)) {
            $nazwa_pliku = $row['nazwa_pliku'];
            $uid = $row['uid'];
            $prywatny = $row['prywatny'];
        }

        if ($prywatny == 0 || $userdata['user_id'] == $uid) {
            if (isset($nazwa_pliku) && $nazwa_pliku != "") {
                $sql = "SELECT ps.post_id, t.topic_title , u.user_id, u.user_posts, ps.post_subject, ps.topic_id, ps.post_username, u.username, ps.forum_id, f.forum_name, u.user_colour, u.user_id
            FROM podf3_posts ps
            LEFT JOIN podf3_users u ON u.user_id  = ps.poster_id
            LEFT JOIN podf3_topics t ON t.topic_id = ps.topic_id
            LEFT JOIN podf3_forums f ON f.forum_id = ps.forum_id
            WHERE ps.post_text LIKE '%" . $this->przygotuj_nazwe_pliku($nazwa_pliku) . "%'";

                $result = $this->db->sql_query($sql);
                $i = 0;
                while ($row = $this->db->sql_fetchrow($result)) {

                    if ($this->auth->acl_get('f_read', $row['forum_id']) || $this->auth->acl_gets('a_', 'm_')) {

                        $forum_style = "";
                        $temat_style = "";

                        $colored_username = $this->pobierz_pokolorowana_nazwe($row['user_id']);
                        if (isset($row['forum_color']) && $row['forum_color'] != '') {
                            $forum_style = 'style="color: #' . $row['forum_color'] . ';"';
                        }
                        if (isset($row['topic_color']) && $row['topic_color'] != '') {
                            $temat_style = 'style="color: ' . $row['topic_color'] . ';"';
                        }

                        $zawartosc .= $this->wiersz_wyszukiwania_listy_postow($row, $forum_style, $temat_style, $colored_username);

                        $i++;
                    }
                }
                if ($i == 0) {
                    $zawartosc .= $this->wyszukiwanie_postow_id_brak_wynikow();
                }
            } else {
                $zawartosc .= $this->wyszukiwanie_postow_id_brak_wynikow();
            }
        } else {
            $zawartosc .= '<tr><td class="row1" style="padding: 7px;" width="100%" colspan="3">
                                Plik jest prywatny.
                        </td></tr>';
        }

        $this->template->assign_block_vars('intro', array(
            'naglowek' => 'Wyniki',
            'content' => $zawartosc,
            'admin' => $this->daj_admin_link()
        ));

        return $this->helper->render('szukaj_wyniki_body.html');
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
                                <a class="forumtitle" ' . $forum_style . ' href="' . generate_board_url() . '/viewforum.php?f=' . $row['forum_id'] . '">'
                . $row['forum_name'] .
                '</a></td>
                                <td class="row1" style="padding: 7px;">
                                    <a class="topictitle" ' . $temat_style . ' href="' . generate_board_url() . '/viewtopic.php?p=' . $row['post_id'] . '#p' . $row['post_id'] . '">'
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
     * Przygoowuje nazę pliku
     * @param type $nazwa_pliku
     * @return type
     */
    private function przygotuj_nazwe_pliku($nazwa_pliku) {

        return str_replace(".", "%", 'upload/' . $nazwa_pliku);
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
     * zwraca zawartosc strony plikow zadanego usera
     * @global type $userdata
     * @global type $per_page
     * @global type $total_match_count
     * @param type $uid
     * @param type $start
     * @return type
     */
    private function pliki_usera($uid, $start, $base_url) {
        global $phpbb_container;
        $edycja = false;
        $per_page = 20;
        $zawartosc = '';

        $usdata = $this->user->data;

        if ($uid == $usdata['user_id']) {
            $edycja = true;
        }

        $pliki_usera = $this->pobierz_wpisy_usera($uid);
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
            $zawartosc .= $this->lista_plikow_wiersz($pliki_usera[$i], $index, $edycja);
            $index++;
        }

        $this->template->set_filenames(array(
            'body' => 'pliki_usera_body.html')
        );

        $this->template->assign_block_vars('intro', array(
            'naglowek' => 'Pliki dodane przez ' . $this->pobierz_pokolorowana_nazwe($uid),
            'content' => $zawartosc,
            'admin' => $this->daj_admin_link()
        ));

        $pagination = $phpbb_container->get('pagination');
        $pagination->generate_template_pagination($base_url, 'pagination', 'start', $total_match_count, $per_page, $start);

        $this->template->assign_vars(array(
            'PAGE_NUMBER' => $pagination->on_page($total_match_count, $per_page, $start),
            'TOTAL_FILES' => $total_match_count,
        ));

        return $this->helper->render('pliki_usera_body.html');
    }

    /**
     * pobiera wpisy z tabeli plikow uzytkownika
     * @param type $uid
     * @return string
     */
    private function pobierz_wpisy_usera($uid) {
        $pliki_usera = array();
        $sql = "SELECT * FROM " . $this->PODFORAK_UPLOAD_TABLE . " WHERE uid = " . $uid . " ORDER BY dodany DESC";
        if (!($result = $this->db->sql_query($sql))) {
            return '<tr><td class="row1" align="center" colspan="4">Brak wynik&#243;w</td></tr>';
        } else {
            while ($row = $this->db->sql_fetchrow($result)) {
                $pliki_usera[] = $row;
            }
            return $pliki_usera;
        }
    }

    /**
     * Wynik wgrywania
     * @return type
     */
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

    /**
     * zwraca listę dozwolonych typów plików
     * @return type
     */
    private function dozwolone_pliki() {
        return str_replace('"', '', $this->dozowlone_pliki);
    }

    /**
     * dodatkowa walidacja rozszerzenia
     * na wszelki wypadek gdyby w js cos poszlo nie tak
     * lub ktos probowal go oszukac
     * @param unknown_type $nazwa - nazwa pliku
     */
    private function walidacja_rozszerzenia($nazwa) {
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
    private function contains($str, array $arr) {
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
    private function blad_rozszerzenia() {
        $this->template->assign_block_vars('intro', array(
            'wynik' => '<div class="messages error"> Wybrano niedozwolony typ pliku!</div>',
            'admin' => $this->daj_admin_link()
        ));

        return $this->helper->render('error_body.html');
    }

    /**
     * blad ogolny zapisu do bazy
     * @return string
     */
    private function blad_ogolny() {
        $this->template->assign_block_vars('intro', array(
            'wynik' => '<div class="messages error"> B&#322;&#261;d og&#243;lny tworzenia nowego wpisu do bazy!</div>',
            'admin' => $this->daj_admin_link()
        ));

        return $this->helper->render('error_body.html');
    }

    /**
     * blad ogolny zapisu pliku
     * @return string
     */
    private function blad_ogolny_pliku() {
        $this->template->assign_block_vars('intro', array(
            'wynik' => '<div class="messages error"> B&#322;&#261;d og&#243;lny zapisu pliku!</div>',
            'admin' => $this->daj_admin_link()
        ));

        return $this->helper->render('error_body.html');
    }

    /**
     * zapisuje plik w miescu przeznaczenia z odpowiednia nazwa
     * @param unknown_type $nazwa_tmp - nazwa pliku tmp
     * @param unknown_type $nazwa_koncowa - nazwa pod ktora ma zostac zapisany plik
     */
    private function przenies_plik_tmp($nazwa_tmp, $nazwa_koncowa) {
        move_uploaded_file($nazwa_tmp, $this->root_path . 'upload/' . $nazwa_koncowa);
    }

    /**
     * sprawdza czy plik jest obrazem
     * @param unknown_type $nazwa
     * @return boolean
     */
    private function is_obraz($nazwa) {
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
    private function dodaj_wpis_bazy($nazwa_pliku, $tytul, $opis, $dodano, $miniatura) {
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
    private function zapisz_obraz($plik_tmp, $miejsce_zapisu, $szerokosc_docelowa, $znak_wodny, $polozenie_znaczka) {
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
    private function utworz_miniature($wgrany_plik, $nazwa_miniatury) {
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
    private function przetworzenie_jesli_miniatura($filename) {
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
    private function ekran_koncowy_wgrywania($nazwa_pliku, $nazwa_miniatury = '', $stara_wgrywajka = 0) {
        $zawartosc = '';
        $url = generate_board_url();
        if ($stara_wgrywajka == 1) {
            $url = 'http://podforak.rzeszow.pl';
        }

        if ($this->is_obraz($nazwa_pliku) && $nazwa_miniatury != '') {
            $zawartosc .= '[URL=' . $url . '/upload/' . $nazwa_pliku . '][img]' . $url . '/' . $nazwa_miniatury . '[/img][/URL]';
        } else if ($this->is_obraz($nazwa_pliku)) {
            $zawartosc .= '[IMG]' . $url . '/upload/' . $nazwa_pliku . '[/img]';
        } else {
            $zawartosc .= $url . '/upload/' . $nazwa_pliku;
        }

        $this->template->assign_block_vars('intro', array(
            'wynik' => $zawartosc,
            'admin' => $this->daj_admin_link()
        ));

        return $this->helper->render('wynik_body.html');
    }

    /**
     * dodaje wiersz listy plikow
     * @param type $plik
     * @param type $index
     * @param type $zarzadzanie
     * @return string
     */
    private function lista_plikow_wiersz($plik, $index, $zarzadzanie = false, $kto_dodal = false) {

        $zawartosc = "";
        $style = "";
        if ($plik['prywatny'] == 1) {
            $style = "background: #CCCCCC;";
        }
        $zawartosc .= '<tr><td class="row1" width="" style="padding: 4px;' . $style . '">' . $index . '</td>';
        $zawartosc .= '<td class="row1" width="25%" style="padding: 4px;' . $style . '">';

        $url = generate_board_url();
        if ($plik['stara_wgrywajka'] == 1) {
            $url = 'http://podforak.rzeszow.pl';
        }

        if ($plik['miniatura'] != '') {
            $zawartosc .= '<a href="' . $url . '/upload/' . $plik['nazwa_pliku'] . '" target="_blank"><img src="' . $url . '/' . $plik['miniatura'] . '" style="width: 233px;"/></a>';
        } else {
            $tytul = $plik['nazwa_pliku'];
            if ($plik['tytul'] != '') {
                $tytul = $plik['tytul'];
            }
            $zawartosc .= '<a href="' . $url . '/upload/' . $plik['nazwa_pliku'] . '" target="_blank">' . $tytul . '</a>';
        }

        $zawartosc .= '</td>';
        $zawartosc .= '<td class="row2" width="45%" style="padding: 4px;' . $style . '">' . $plik['opis'] . '</td><td class="row1" width="15%" style="padding: 4px; text-align: center;' . $style . '">' . date('d-m-Y h:i', $plik['dodany']);

        if ($kto_dodal) {
            $nazwa_usera = $this->pobierz_pokolorowana_nazwe($plik['uid']);
            if ($nazwa_usera != "") {
                $zawartosc .= '<br />przez: ';
                $zawartosc .= $nazwa_usera;
            }
        }

        $zawartosc .= '</td>';
        $zawartosc .= '<td class="row2 akcjeplikow" width="15%" style="padding: 4px;' . $style . '"><ul>';
        $zawartosc .= '<li><a href="plik?fid=' . $plik['id'] . '">Znajd&#378; posty z plikiem</a></li>';
        $zawartosc .= '<li><a href="kod?fid=' . $plik['id'] . '" target="_blank">Pobierz kod na forum</a></li>';

        if ($zarzadzanie) {
            if ($plik['prywatny'] == 0) {
                $zawartosc .= '<li><a href="prywatny?fid=' . $plik['id'] . '&status=1' . $str . '">Oznacz jako prywatny</a></li>';
            } else {
                $zawartosc .= '<li><a href="prywatny?fid=' . $plik['id'] . '&status=0' . $str . '">Oznacz jako publiczny</a></li>';
            }
            $zawartosc .= '<li><a href="usun?fid=' . $plik['id'] . '' . $str . '">Usu&#324; plik</a></li>';
        }
        $zawartosc .= '</ul></td></tr>';

        return $zawartosc;
    }

    /**
     * Pobiera pokolorowana nazwe uzytkownika
     * @param type $uid
     * @return string
     */
    private function pobierz_pokolorowana_nazwe($uid) {
        $zawartosc = "";

        $sql = "SELECT u.user_id, u.username, u.user_colour FROM podf3_users u WHERE u.user_id = $uid";
        if ($result = $this->db->sql_query($sql)) {
            while ($row = $this->db->sql_fetchrow($result)) {
                $style = 'class="username"';
                if (trim($row['user_colour']) != '') {
                    $style = 'class="username-coloured" style="color: #' . $row['user_colour'] . ';"';
                }
                if ($colored_username != 'Anonymous') {
                    $zawartosc = '<a ' . $style . ' href="' . generate_board_url() . '/memberlist.php?mode=viewprofile&u=' . $row['user_id'] . '">'
                            . $row['username'] .
                            '</a>';
                }
            }
        }

        return $zawartosc;
    }

}
