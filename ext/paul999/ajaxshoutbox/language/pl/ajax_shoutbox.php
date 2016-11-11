<?php
/**
 *
 * Ajax Shoutbox extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 Paul Sohier <http://www.ajax-shoutbox.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

/**
 * DO NOT CHANGE
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
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge(
	$lang, array(
		'AJAX_SHOUTBOX'                  => 'Shoutbox',
		'AJAX_SHOUTBOX_MESSAGE'          => 'Dodaj wiadomość',
		'AJAX_SHOUTBOX_ONLY_AJAX'        => 'Przepraszamy, pisanie w shoutbox jest możliwe jednynie gdy JavaScript jest włączony',
		'AJAX_SHOUTBOX_NO_PERMISSION'    => 'Brak uprawnień dla wybranej akcji',
		'AJAX_SHOUTBOX_MESSAGE_EMPTY'    => 'Wiadomość pusta',
		'AJAX_SHOUTBOX_ERROR'            => 'Błąd',
		'AJAX_SHOUTBOX_MISSING_ID'       => 'Nie można usunąć posta',
		'AJAX_SHOUTBOX_NO_SUCH_POST'     => 'Nie można znaleźć posta',

		'AJAXSHOUTBOX_BOARD_DATE_FORMAT'            => 'Mój format daty w shoutbox',
		'AJAXSHOUTBOX_BOARD_DATE_FORMAT_EXPLAIN'    => 'Określ format daty dla shoutbox. Nie należy stosować względnego formatu daty.',

		'AJAXSHOUTBOX_UNSUPPORTED_STYLE'    => 'It seems you are using a non prosilver based style, or the style doesn’t inherit prosilver correctly.
			<br />If you are using a style based on prosilver, make sure it inherits prosilver correctly.
			<br />If you are using a style not based on prosilver, you will need to create a template for the shoutbox,
				or ask the style author to provide a working template for the shoutbox.
			<br />I don’t provide support for non prosilver styles (Including subsilver2!). This message is only shown to admins.',
	)
);
