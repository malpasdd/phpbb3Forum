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
	'ACL_U_MCHAT_USE'						=> 'Może używać mChat',
	'ACL_U_MCHAT_VIEW'						=> 'Może widzieć mChat',
	'ACL_U_MCHAT_EDIT'						=> 'Może edytować swoje wiadomości',
	'ACL_U_MCHAT_DELETE'					=> 'Może usuwać swoje wiadomości',
	'ACL_U_MCHAT_MODERATOR_EDIT'			=> 'Może edytować wiadomości innych',
	'ACL_U_MCHAT_MODERATOR_DELETE'			=> 'Może usuwać wiadomości innych',
	'ACL_U_MCHAT_IP'						=> 'Może widzieć adresy IP',
	'ACL_U_MCHAT_PM'						=> 'Może używać prywatnych wiadomości',
	'ACL_U_MCHAT_LIKE'						=> 'Może lubić wiadomości',
	'ACL_U_MCHAT_QUOTE'						=> 'Może cytować wiadomości',
	'ACL_U_MCHAT_FLOOD_IGNORE'				=> 'Can ignore flood limit',
	'ACL_U_MCHAT_ARCHIVE'					=> 'Może przeglądać archiwum',
	'ACL_U_MCHAT_BBCODE'					=> 'Może używać BBCodes',
	'ACL_U_MCHAT_SMILIES'					=> 'Może używać emotikon',
	'ACL_U_MCHAT_URLS'						=> 'Może postować automatycznie parsowane URLe',

	'ACL_U_MCHAT_AVATARS'					=> 'Can customise <em>Pokazuj avatary</em>',
	'ACL_U_MCHAT_CAPITAL_LETTER'			=> 'Can customise <em>Rozpocznij wiadomość od wielkiej litery</em>',
	'ACL_U_MCHAT_CHARACTER_COUNT'			=> 'Can customise <em>Display number of characters</em>',
	'ACL_U_MCHAT_DATE'						=> 'Can customise <em>Date format</em>',
	'ACL_U_MCHAT_INDEX'						=> 'Can customise <em>Display on index</em>',
	'ACL_U_MCHAT_INPUT_AREA'				=> 'Can customise <em>Input type</em>',
	'ACL_U_MCHAT_LOCATION'					=> 'Can customise <em>Location of mChat on the index page</em>',
	'ACL_U_MCHAT_MESSAGE_TOP'				=> 'Can customise <em>Location of new chat messages</em>',
	'ACL_U_MCHAT_PAUSE_ON_INPUT'			=> 'Can customise <em>Pause on input</em>',
	'ACL_U_MCHAT_POSTS'						=> 'Can customise <em>Display new post</em>',
	'ACL_U_MCHAT_RELATIVE_TIME'				=> 'Can customise <em>Display relative time</em>',
	'ACL_U_MCHAT_SOUND'						=> 'Can customise <em>Play sounds</em>',
	'ACL_U_MCHAT_WHOIS_INDEX'				=> 'Can customise <em>Display who is chatting below the chat</em>',
	'ACL_U_MCHAT_STATS_INDEX'				=> 'Can customise <em>Display who is chatting in the stats section</em>',

	'ACL_A_MCHAT'							=> 'Może zarządzać ustawieniami mChat',
));
