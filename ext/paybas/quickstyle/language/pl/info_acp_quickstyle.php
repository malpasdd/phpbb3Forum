<?php
/**
 *
 * @package Quick Style
 * English translation by PayBas
 *
 * @copyright (c) 2015 PayBas
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * Based on the original Prime Quick Style by Ken F. Innes IV (primehalo)
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

$lang = array_merge($lang, array(
	'QUICK_STYLE'						=> 'Quick Style',
	'QUICK_STYLE_EXPLAIN'				=> 'Dodaje pole rozwijane stylów w nagłówku każdej strony na szybkie przełączenie pomiędzy stylami. Na podstawie oryginalnego Prime Quick Style by primehalo.',
	'QUICK_STYLE_SETTINGS'				=> 'Ustawienia Quick Style',
	'QUICK_STYLE_DEFAULT_LOC'			=> 'Korzystanie z domyślnej lokalizacji szablonów',
	'QUICK_STYLE_DEFAULT_LOC_EXPLAIN'	=> 'Domyślnie Quick Style będzie wstawić przełącznik stylu po prawej nawigacji breadcrumb w nagłówku. Ustawienie tej opcji na "nie" pozwoli na użycie quickstyle_event gdzieś indziej w Twoim stylu.',
	'QUICK_STYLE_ALLOW_GUESTS'			=> 'Pozwól gościom zmieniać style',
	'QUICK_STYLE_ALLOW_GUESTS_EXPLAIN'	=> 'To ustawienie pozwoli gościom zmieniać styl. Ponieważ nie są zalogowani, plik cookie zostanie wykorzystany do zapamiętania ich wyboru.',
	'QUICK_STYLE_OVERRIDE_ENABLED'		=> 'Ustawienie "Zastąp styl użytkownika" jest włączone na tym forum. Przełączanie stylu nie będzie działało do wyłącznia tego ustawienia.',
));
