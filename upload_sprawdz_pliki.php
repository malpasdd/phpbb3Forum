<?php

ob_start();
header('Content-Type: text/html; charset=utf-8');
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

$user->session_begin();
$auth->acl($user->data);
$user->setup();

global $db;

$id = request_var('id', 0);

if (isset($id) && !is_null($id) && $id != 0) {
    $sql = "SELECT COUNT('x') as liczba FROM phpbb_podforak_upload WHERE id < $id";

    $wykonanych = 0;
    if (($result = $db->sql_query($sql))) {
        while ($row = $db->sql_fetchrow($result)) {
            $wykonanych = $row['liczba'];
        }
    }

    $sql = "SELECT COUNT('x') as liczba FROM phpbb_podforak_upload";

    $ogolem = 0;
    if (($result = $db->sql_query($sql))) {
        while ($row = $db->sql_fetchrow($result)) {
            $ogolem = $row['liczba'];
        }
    }

    $postep = $wykonanych / $ogolem * 100;

    echo '<table width="200px" style="border: 1px solid #000;" cellspacing="0" cellpadding="0">'
    . '<tr>'
    . '<td style="width: ' . ceil($postep) . '%; background: green; height: 20px;">'
    . '</td>'
    . '<td>'
    . '</td>'
    . '</tr>'
    . '</table>';

    echo "postep=" . ceil($postep) . "%<br />";
    echo 'plik id=' . $id . '<br />';
    $sprawdzony = time();

    $sql = "SELECT nazwa_pliku as file_name, miniatura FROM phpbb_podforak_upload WHERE id = $id";
    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Couldnt Could not query phpbb_podforak_upload table', '', __LINE__, __FILE__, $sql);
    } else {
        $filename = "";
        $miniatura = "";
        while ($row = $db->sql_fetchrow($result)) {
            $filename = $row['file_name'];
            $miniatura = $row['miniatura'];
        }
        if ($filename != "") {
            $sql = "SELECT poster_id, post_time, COUNT('x') as liczba
                        FROM sprawdzanie_tmp
                        WHERE  post_text LIKE '%$filename%'";

            if (isset($miniatura) && !is_null($miniatura) && $miniatura != "") {
                $sql .= " OR post_text LIKE '%$miniatura%'";
            }

            $sql .= " ORDER BY post_id ASC LIMIT 0,1";

            $liczba_wystapien = 0;
            if (($result = $db->sql_query($sql))) {
                while ($row = $db->sql_fetchrow($result)) {
                    $liczba_wystapien = $row['liczba'];
                }
            }

            $sql = "UPDATE phpbb_podforak_upload SET uzyty = $liczba_wystapien, sprawdzony = $sprawdzony WHERE id = $id";
            if (!($db->sql_query($sql))) {
                message_die(GENERAL_ERROR, 'Couldnt insert new entry into phpbb_podforak_upload table', '', __LINE__, __FILE__, $sql);
            }
        } else {
            echo 'file_name puste';
        }
    }

    $next_id = 0;
    $sql = "SELECT MIN(id) as idx FROM phpbb_podforak_upload WHERE id > $id";

    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Couldnt Could not query phpbb_podforak_upload table', '', __LINE__, __FILE__, $sql);
    } else {
        $next_id = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $next_id = $row['idx'];
        }
        if (isset($next_id)) {
            if ($next_id > 0) {
                echo '$next_id = ' . $next_id;
                header("refresh: 0; url=./upload_sprawdz_pliki.php?id=$next_id");
            } else {
                echo 'blad, wartosc next_id jest zerem';
            }
        } else {
            $max_id = 0;
            $sql = "SELECT MAX(id) as idx FROM phpbb_podforak_upload";
            if (!($result = $db->sql_query($sql))) {
                message_die(GENERAL_ERROR, 'Couldnt Could not query phpbb_podforak_filescan table', '', __LINE__, __FILE__, $sql);
            } else {
                $max_id = 0;
                while ($row = $db->sql_fetchrow($result)) {
                    $max_id = $row['idx'];
                }
            }
            if ($id == $max_id) {
                echo 'koniec :) <br />';

                $sql = "DROP TABLE IF EXISTS sprawdzanie_tmp";
                if (!($db->sql_query($sql))) {
                    message_die(GENERAL_ERROR, 'Could not drop sprawdzanie_tmp table', '', __LINE__, __FILE__, $sql);
                }

                echo '<a href="upload.php?action=admin&level=1">Wroc do panelu administracyjnego</a>';
            } else {
                echo 'blad, wartosc next_id jest pusta';
            }
        }
    }
} else {
    echo 'rozpoczynam przygotowania do pracy, prosze czekac...<br />';
    echo 'dodaje tabele sprawdzanie_tmp,<br />';

    $sql = "DROP TABLE IF EXISTS sprawdzanie_tmp";
    if (!($db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not drop sprawdzanie_tmp table', '', __LINE__, __FILE__, $sql);
    }

//    $sql = "SELECT COUNT(*) as total
//			FROM sprawdzanie_tmp";
//    if (!$result = $db->sql_query($sql)) {
    $sql = "CREATE TABLE sprawdzanie_tmp (
                    post_id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                    poster_id mediumint(8) unsigned NOT NULL DEFAULT '0',
                    post_text text,
                    post_time int(11),
                    PRIMARY KEY (post_id)
                    ) ENGINE=MyISAM DEFAULT CHARSET=latin2;";
    if (!($db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not create sprawdzanie_tmp table', '', __LINE__, __FILE__, $sql);
    }
//    }

    echo 'dodano tabele sprawdzanie_tmp,<br />';

    echo 'dodaje wpisy do sprawdzanie_tmp,<br />';

    $sql = "INSERT INTO sprawdzanie_tmp (post_id, post_text, poster_id, post_time)
            SELECT p.post_id, pt.post_text, p.poster_id, p.post_time FROM phpbb_posts_text pt
            JOIN phpbb_posts p ON p.post_id = pt.post_id
            WHERE pt.post_text LIKE '%rzeszow.pl/upload%' OR pt.post_text LIKE '%ayz.pl/upload%'";
    if (!($db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not create INSERT posts INTO sprawdzanie_tmp', '', __LINE__, __FILE__, $sql);
    }
    echo 'dodano wpisy postow,<br />';

    $sql = "INSERT INTO sprawdzanie_tmp (post_text, poster_id, post_time) SELECT th.th_post_text, p.poster_id, p.post_time FROM phpbb_posts_text_history th LEFT JOIN phpbb_posts p ON p.post_id = th.th_post_id WHERE th.th_post_text LIKE '%rzeszow.pl/upload%' OR th.th_post_text LIKE '%ayz.pl/upload%'";

    if (!($db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not create phpbb_podforak_upload table', '', __LINE__, __FILE__, $sql);
    }
    echo 'dodano wpisy historii pliikow,<br />';

    $sql = "INSERT INTO sprawdzanie_tmp (post_text, poster_id, post_time) SELECT u.user_sig, u.user_id, u.user_regdate  FROM phpbb_users u WHERE u.user_sig LIKE '%rzeszow.pl/upload%' OR u.user_sig LIKE '%ayz.pl/upload%'";

    if (!($db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not create phpbb_podforak_upload table', '', __LINE__, __FILE__, $sql);
    }
    echo 'dodano wpisy sygnatur,<br />';

    $sql = "INSERT INTO sprawdzanie_tmp (post_text, poster_id, post_time) SELECT pt.privmsgs_text, p.privmsgs_from_userid, p.privmsgs_date FROM phpbb_privmsgs_text pt LEFT JOIN phpbb_privmsgs p ON p.privmsgs_id = pt.privmsgs_text_id WHERE pt.privmsgs_text LIKE '%rzeszow.pl/upload%' OR pt.privmsgs_text LIKE '%ayz.pl/upload%'";

    if (!($db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not create phpbb_podforak_upload table', '', __LINE__, __FILE__, $sql);
    }
    echo 'dodano wpisy prywatnych wiadomosci,<br />';

    $sql = "INSERT INTO sprawdzanie_tmp (post_text, poster_id, post_time) SELECT msg, sb_user_id, timestamp FROM phpbb_shoutbox WHERE msg LIKE '%rzeszow.pl/upload%' OR msg LIKE '%ayz.pl/upload%'";

    if (!($db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not create phpbb_podforak_upload table', '', __LINE__, __FILE__, $sql);
    }
    echo 'dodano wpisy shoutboxa wiadomosci,<br />';

    echo 'dodano wpisy do sprawdzanie_tmp,<br />';

    $sql = "SELECT MIN(id) as liczba FROM  phpbb_podforak_upload";

    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not query phpbb_podforak_upload table', '', __LINE__, __FILE__, $sql);
    }

    $id = 1;

    while ($row = $db->sql_fetchrow($result)) {
        $id = $row['liczba'];
    }
    echo 'koniec przygotowan :)<br />';
    echo 'Za chwile nastapi przekierowanie...<br />';
    header("refresh: 5; url=./upload_sprawdz_pliki.php?id=$id");
}