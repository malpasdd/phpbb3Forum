<?php
/**
 *
 * Podforakowa wgrywajka. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2016, SDD, http://podforak.pl
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sdd\wgrywajka\migrations;

class install_acp_module extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['acme_demo_goodbye']);
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v314');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('acme_demo_goodbye', 0)),

			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_DEMO_TITLE'
			)),
			array('module.add', array(
				'acp',
				'ACP_DEMO_TITLE',
				array(
					'module_basename'	=> '\sdd\wgrywajka\acp\main_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
}
