<?php

ini_set('max_execution_time', 10800);
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'upload_functions.php');

$user->session_begin();
$auth->acl($user->data);
$user->setup();

global $usdata;
$usdata = $user->data;

page_header('Wgrywajka');
/**
 * wymaganie logowania
 */
// if (!$userdata['session_logged_in']) {
// redirect(append_sid("login.$phpEx?redirect=upload.$phpEx", true));
// exit;
// }

/**
 * akcja oraz inne prametry
 */
$level = request_var('level', 0);
$fid = request_var('fid', 0);
$uid = request_var('uid', 0);
$uname = request_var('uname', '');
$fname = request_var('fname', '');
$action = request_var('action', 'proste');
$status = request_var('status', 0);
$start = request_var('start', 0);
$nazwa = request_var('nazwa', '');

$per_page = 20;
$total_match_count = 0;
$nie_skanowano_od = 2 * 24 * 3600;
if (!isset($start)) {
    $start = 0;
}
/**
 * ustawienia rozmiaru i dozwolonych plikow
 */
global $max_rozmiar;
$max_rozmiar = 1024 * 1024; /* tutaj ustalasz rozmiar mnożone przez 1024 żeby było w kilo bajtach */
global $max_zaawansowana;
$max_zaawansowana = 10 * 1024 * 1024; /* tutaj ustalasz rozmiar mnożone przez 1024 żeby było w kilo bajtach */
$dozowlone_pliki = '"png", "gif", "jpeg", "jpg", "pdf", "zip", "xls", "xlsx", "ppt", "pps", "pptx", "ppsx", "doc", "docx"';

if ($action == 'zaawansowane') {
    //wyswietlenie zaawansowanego wgrywania
    $content = wyswietl_zaawansowane();
} else if ($action == 'mojepliki') {
    //wyswietlenie plikow usera
    $content = pliki_usera($uid, $start);
} else if ($action == 'doreupu') {
    //wyswietlenie plikow usera
    $content = pliki_do_reupu();
} else if ($action == 'allegro') {
    //wyswietlenie plikow usera
    $content = szukaj_allegro();
} else if ($action == 'admin') {
    if (isset($level) && $level == 1) {
        //formularz kasowania plikow
        $content = formularz_administracyjny();
    } else if (isset($level) && $level == 2) {
        //kasowanie plikow wybranych w formularzu
        $content = usuwanie_plikow(request_var('chk_group', array()));
    } else {
        //statystyki wyswietlane w administracji
        $content = statystyki_administracja();
    }
} else if ($action == 'szukaj') {
//    var_dump($nazwa);
//    var_dump($fid);
//    var_dump($uid);

    if (isset($nazwa) && $nazwa != '') {
        //po nazwie like
        $content = pobierz_pliki_nazwa($nazwa, $start);
    } else if ((isset($fid) && $fid != 0) && (isset($uid) && $uid != 0)) {
        //wyszujowanie dla konkretnego pliku, po id (ekran moje pliki)
        $content = wyszukiwanie_postow_uid_fid($fid, $uid);
    } else if (isset($fid) && $fid != 0) {
        //wyszujowanie dla konkretnego pliku, po id (ekran moje pliki)
        $content = wyszukiwanie_postow_id($fid);
    } else if (isset($uid) && $uid != 0) {
        //pliki usera
        $content = pliki_usera($uid, $start);
    } elseif (($fid == 0 || $uid == 0) && $nazwa != '') {
        $content = brak_wynikow();
    } elseif (isset($level) && $level == 2) {
        //przetworzenie kryteriow z formularza wyszukiwania
        $url = url_wyszukiwania(request_var('pic_name', ''), request_var('pic_owner', ''));
        redirect($url);
        exit;
    } else {
        //wyswietlenie wyszukiwarki
        $content = szukaj_form();
    }
} else if ($action == 'usun') {
    $content = usun_plik($fid);
    przenies_na_strone($start);
} else if ($action == 'upload' && $level == 1) {
    //wyswietlenie wynikow wgrywania prostego
    $content = wynik_prostego();
} else if ($action == 'upload' && $level == 2) {
    //wyswietlenie wynikow wgrywania zaawansowanego
    $content = wynik_zaawansowanego();
} else if ($action == 'prywatny' && $fid != '') {
    //oznacznie pliku jako prywatny
    $content = oznacz_prywatny($fid, $status);
    przenies_na_strone($start);
} else if ($fid != '') {
    //pobranie kodu do wstawienia na forum
    $content = pobierz_kod($fid);
} else {
    //wyswietlenie wgrywania prostego
    $content = wyswietl_proste();
}

$template->set_filenames(array(
    'body' => 'upload_body.html')
);

//if ($total_match_count > $per_page) {
if (!empty($uid)) {
    $base_url = "upload.php?action=szukaj&uid=$uid";
} else if (!empty($nazwa)) {
    $base_url = "upload.php?action=szukaj&nazwa=$nazwa";
} else {
    $base_url = "upload.php?action=mojepliki";
}
$pagination = $phpbb_container->get('pagination');

$pagination->generate_template_pagination($base_url, 'pagination', 'start', $total_match_count, $per_page, $start);

$template->assign_vars(array(
    'PAGE_NUMBER' => $pagination->on_page($total_match_count, $per_page, $start),
    'TOTAL_FILES' => $total_match_count,
));
//}

$template->assign_block_vars('intro', array(
    'nawigacja' => wyswietl_nawigacje($action),
    'content' => $content,
    'admin' => administracja_plikami()
));

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();
