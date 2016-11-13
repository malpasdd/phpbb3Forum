<?php
/**
 *
 * Podforakowa wgrywajka. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2016, SDD, http://podforak.pl
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sdd\wgrywajka\acp;

/**
 * Podforakowa wgrywajka ACP module info.
 */
class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\sdd\wgrywajka\acp\main_module',
			'title'		=> 'ACP_DEMO_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'ACP_DEMO',
					'auth'	=> 'ext_sdd/wgrywajka && acl_a_board',
					'cat'	=> array('ACP_DEMO_TITLE')
				),
			),
		);
	}
}
