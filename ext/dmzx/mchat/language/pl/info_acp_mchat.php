<?php

/**
 *
 * @package phpBB Extension - mChat
 * @copyright (c) 2016 dmzx - http://www.dmzx-web.net
 * @copyright (c) 2016 kasimi - https://kasimi.net
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters for use
// ’ » “ ” …

$lang = array_merge($lang, array(
	// Module titles
	'ACP_CAT_MCHAT'					=> 'mChat',
	'ACP_CAT_MCHAT_USER_CONFIG'		=> 'mChat w UCP',
	'ACP_MCHAT_GLOBALSETTINGS'		=> 'Ustawienia ogólne',
	'ACP_MCHAT_GLOBALUSERSETTINGS'	=> 'Globalne ustawienia użytkownika',

	// Log entries (%1$s is replaced with the user name who triggered the event)
	'LOG_MCHAT_CONFIG_UPDATE'		=> '<strong>konfiguracja zaktualizowana</strong><br />» %1$s',
	'LOG_MCHAT_TABLE_PRUNED'		=> '<strong>mChat messages pruned: %2$d</strong><br />» %1$s',
	'LOG_MCHAT_TABLE_PURGED'		=> '<strong>mChat messages purged</strong><br />» %1$s',
	'LOG_DELETED_MCHAT'				=> '<strong>wiadomość usunięta</strong><br />» %1$s',
	'LOG_EDITED_MCHAT'				=> '<strong>wiadomość edytowana</strong><br />» %1$s',
));
