<?php

/**
 *
 * Podforakowa wgrywajka. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2016, SDD, http://podforak.pl
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */
if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = array();
}

$lang = array_merge($lang, array(
    'WGRYWAJKA_PAGE' => 'Demo',
    'WGRYWAJKA_HELLO' => 'Hello %s!',
    'WGRYWAJKA_GOODBYE' => 'Goodbye %s!',
    'ACP_WGRYWAJKA_GOODBYE' => 'Should say goodbye?',
    'ACP_WGRYWAJKA_SETTING_SAVED' => 'Settings have been saved successfully!',
    'WGRYWAJKA_NOTIFICATION' => 'Acme demo notification',
        ));
