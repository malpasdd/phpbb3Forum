<?php

/**
 *
 * @package phpBB Extension - mChat
 * @copyright (c) 2016 dmzx - http://www.dmzx-web.net
 * @copyright (c) 2016 kasimi - https://kasimi.net
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace dmzx\mchat\controller;

use dmzx\mchat\core\functions;
use dmzx\mchat\core\settings;
use phpbb\cache\driver\driver_interface as cache_interface;
use phpbb\db\driver\driver_interface as db_interface;
use phpbb\event\dispatcher_interface;
use phpbb\log\log_interface;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;

class acp_controller
{
	/** @var functions */
	protected $functions;

	/** @var template */
	protected $template;

	/** @var log_interface */
	protected $log;

	/** @var user */
	protected $user;

	/** @var db_interface */
	protected $db;

	/** @var cache_interface */
	protected $cache;

	/** @var request_interface */
	protected $request;

	/** @var dispatcher_interface */
	protected $dispatcher;

	/** @var settings */
	protected $settings;

	/** @var string */
	protected $mchat_table;

	/** @var string */
	protected $mchat_log_table;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param functions				$functions
	 * @param template				$template
	 * @param log_interface			$log
	 * @param user					$user
	 * @param db_interface			$db
	 * @param cache_interface		$cache
	 * @param request_interface		$request
	 * @param dispatcher_interface 	$dispatcher
	 * @param settings				$settings
	 * @param string				$mchat_table
	 * @param string				$mchat_log_table
	 * @param string				$root_path
	 * @param string				$php_ext
	 */
	public function __construct(
		functions $functions,
		template $template,
		log_interface $log,
		user $user,
		db_interface $db,
		cache_interface $cache,
		request_interface $request,
		dispatcher_interface $dispatcher,
		settings $settings,
		$mchat_table,
		$mchat_log_table,
		$root_path, $php_ext
	)
	{
		$this->functions		= $functions;
		$this->template			= $template;
		$this->log				= $log;
		$this->user				= $user;
		$this->db				= $db;
		$this->cache			= $cache;
		$this->request			= $request;
		$this->dispatcher		= $dispatcher;
		$this->settings			= $settings;
		$this->mchat_table		= $mchat_table;
		$this->mchat_log_table	= $mchat_log_table;
		$this->root_path		= $root_path;
		$this->php_ext			= $php_ext;
	}

	/**
	 * Display the options the admin can configure for this extension
	 *
	 * @param string $u_action
	 */
	public function globalsettings($u_action)
	{
		add_form_key('acp_mchat');

		$error = array();

		$is_founder = $this->user->data['user_type'] == USER_FOUNDER;

		if ($this->request->is_set_post('submit'))
		{
			$mchat_new_config = array();
			$validation = array();
			foreach ($this->settings->global_settings() as $config_name => $config_data)
			{
				$default = $this->settings->cfg($config_name);
				settype($default, gettype($config_data['default']));
				$mchat_new_config[$config_name] = $this->request->variable($config_name, $default, is_string($default));
				if (isset($config_data['validation']))
				{
					$validation[$config_name] = $config_data['validation'];
				}
			}

			// Don't allow changing pruning settings for non founders
			if (!$is_founder)
			{
				unset($mchat_new_config['mchat_prune']);
				unset($mchat_new_config['mchat_prune_gc']);
				unset($mchat_new_config['mchat_prune_mode']);
				unset($mchat_new_config['mchat_prune_num']);
			}

			if (!function_exists('validate_data'))
			{
				include($this->root_path . 'includes/functions_user.' . $this->php_ext);
			}

			$error = array_merge($error, validate_data($mchat_new_config, $validation));

			if (!check_form_key('acp_mchat'))
			{
				$error[] = 'FORM_INVALID';
			}

			/**
			 * Event to modify ACP global settings data before they are updated
			 *
			 * @event dmzx.mchat.acp_globalsettings_update_data
			 * @var array	mchat_new_config		Array containing the ACP settings data that is about to be sent to the database
			 * @var array	error					Array with error lang keys
			 * @since 2.0.0-RC7
			 */
			$vars = array(
				'mchat_new_config',
				'error',
			);
			extract($this->dispatcher->trigger_event('dmzx.mchat.acp_globalsettings_update_data', compact($vars)));

			if (!$error)
			{
				// Set the options the user configured
				foreach ($mchat_new_config as $config_name => $config_value)
				{
					$this->settings->set_cfg($config_name, $config_value);
				}

				// Add an entry into the log table
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_MCHAT_CONFIG_UPDATE', false, array($this->user->data['username']));

				trigger_error($this->user->lang('MCHAT_CONFIG_SAVED') . adm_back_link($u_action));
			}

			// Replace "error" strings with their real, localised form
			$error = array_map(array($this->user, 'lang'), $error);
		}

		if (!$error)
		{
			if ($is_founder && $this->request->is_set_post('mchat_purge') && $this->request->variable('mchat_purge_confirm', false) && check_form_key('acp_mchat'))
			{
				$this->db->sql_query('TRUNCATE TABLE ' . $this->mchat_table);
				$this->db->sql_query('TRUNCATE TABLE ' . $this->mchat_log_table);
				$this->cache->destroy('sql', $this->mchat_log_table);
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_MCHAT_TABLE_PURGED', false, array($this->user->data['username']));
				trigger_error($this->user->lang('MCHAT_PURGED') . adm_back_link($u_action));
			}
			else if ($is_founder && $this->request->is_set_post('mchat_prune_now') && $this->request->variable('mchat_prune_now_confirm', false) && check_form_key('acp_mchat'))
			{
				$num_pruned_messages = count($this->functions->mchat_prune());
				trigger_error($this->user->lang('MCHAT_PRUNED', $num_pruned_messages) . adm_back_link($u_action));
			}
		}

		$template_data = array(
			'MCHAT_ERROR'							=> implode('<br />', $error),
			'MCHAT_VERSION'							=> $this->settings->cfg('mchat_version'),
			'MCHAT_FOUNDER'							=> $is_founder,
			'S_MCHAT_PRUNE_MODE_OPTIONS'			=> $this->get_prune_mode_options($this->settings->cfg('mchat_prune_mode')),
			'L_MCHAT_BBCODES_DISALLOWED_EXPLAIN'	=> $this->user->lang('MCHAT_BBCODES_DISALLOWED_EXPLAIN', '<a href="' . append_sid("{$this->root_path}adm/index.$this->php_ext", 'i=bbcodes', true, $this->user->session_id) . '">', '</a>'),
			'L_MCHAT_TIMEOUT_EXPLAIN'				=> $this->user->lang('MCHAT_TIMEOUT_EXPLAIN','<a href="' . append_sid("{$this->root_path}adm/index.$this->php_ext", 'i=board&amp;mode=load', true, $this->user->session_id) . '">', '</a>', $this->settings->cfg('session_length')),
			'U_ACTION'								=> $u_action,
		);

		foreach (array_keys($this->settings->global_settings()) as $key)
		{
			$template_data[strtoupper($key)] = $this->settings->cfg($key);
		}

		/**
		 * Event to modify ACP global settings template data
		 *
		 * @event dmzx.mchat.acp_globalsettings_modify_template_data
		 * @var array	template_data	Array containing the template data for the ACP settings
		 * @var array	error			Array with error lang keys
		 * @since 2.0.0-RC7
		 */
		$vars = array(
			'template_data',
			'error',
		);
		extract($this->dispatcher->trigger_event('dmzx.mchat.acp_globalsettings_modify_template_data', compact($vars)));

		$this->template->assign_vars($template_data);
	}

	/**
	 * @param string $u_action
	 */
	public function globalusersettings($u_action)
	{
		add_form_key('acp_mchat');

		$error = array();

		if ($this->request->is_set_post('submit'))
		{
			$mchat_new_config = array();
			$validation = array();
			foreach ($this->settings->ucp_settings() as $config_name => $config_data)
			{
				$default = $this->settings->cfg($config_name, true);
				settype($default, gettype($config_data['default']));
				$mchat_new_config[$config_name] = $this->request->variable('user_' . $config_name, $default, is_string($default));

				if (isset($config_data['validation']))
				{
					$validation[$config_name] = $config_data['validation'];
				}
			}

			if (!function_exists('validate_data'))
			{
				include($this->root_path . 'includes/functions_user.' . $this->php_ext);
			}

			$error = array_merge($error, validate_data($mchat_new_config, $validation));

			if (!check_form_key('acp_mchat'))
			{
				$error[] = 'FORM_INVALID';
			}

			$mchat_new_user_config = array();

			if ($this->request->variable('mchat_overwrite', 0) && $this->request->variable('mchat_overwrite_confirm', 0))
			{
				foreach ($mchat_new_config as $config_name => $config_value)
				{
					$mchat_new_user_config['user_' . $config_name] = $config_value;
				}
			}

			/**
			 * Event to modify ACP global user settings data before they are updated
			 *
			 * @event dmzx.mchat.acp_globalusersettings_update_data
			 * @var array	mchat_new_config		Array containing the ACP global user settings data that is about to be sent to the database
			 * @var array	mchat_new_user_config	Array containing the user settings data when overwriting all user settings
			 * @var array	error					Array with error lang keys
			 * @since 2.0.0-RC7
			 */
			$vars = array(
				'mchat_new_config',
				'mchat_new_user_config',
				'error',
			);
			extract($this->dispatcher->trigger_event('dmzx.mchat.acp_globalusersettings_update_data', compact($vars)));

			if (!$error)
			{
				if ($mchat_new_user_config)
				{
					$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $mchat_new_user_config);
					$this->db->sql_query($sql);
				}

				// Set the options the user configured
				foreach ($mchat_new_config as $config_name => $config_value)
				{
					$this->settings->set_cfg($config_name, $config_value);
				}

				// Add an entry into the log table
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_MCHAT_CONFIG_UPDATE', false, array($this->user->data['username']));

				trigger_error($this->user->lang('MCHAT_CONFIG_SAVED') . adm_back_link($u_action));
			}

			// Replace "error" strings with their real, localised form
			$error = array_map(array($this->user, 'lang'), $error);
		}

		// Force global date format for $selected_date value, not user-specific
		$selected_date = $this->settings->cfg('mchat_date', true);
		$template_data = $this->settings->get_date_template_data($selected_date);

		foreach (array_keys($this->settings->ucp_settings()) as $key)
		{
			$template_data[strtoupper($key)] = $this->settings->cfg($key, true);
		}

		$template_data = array_merge($template_data, array(
			'MCHAT_POSTS_ENABLED_LANG'	=> $this->settings->get_enabled_post_notifications_lang(),
			'MCHAT_ERROR'				=> implode('<br />', $error),
			'MCHAT_VERSION'				=> $this->settings->cfg('mchat_version'),
			'U_ACTION'					=> $u_action,
		));

		/**
		 * Event to modify ACP global user settings template data
		 *
		 * @event dmzx.mchat.acp_globalusersettings_modify_template_data
		 * @var array	template_data	Array containing the template data for the ACP user settings
		 * @var array	error			Array with error lang keys
		 * @since 2.0.0-RC7
		 */
		$vars = array(
			'template_data',
			'error',
		);
		extract($this->dispatcher->trigger_event('dmzx.mchat.acp_globalusersettings_modify_template_data', compact($vars)));

		$this->template->assign_vars($template_data);
	}

	/**
	 * @param $selected
	 * @return array
	 */
	protected function get_prune_mode_options($selected)
	{
		if (empty($this->settings->prune_modes[$selected]))
		{
			$selected = 0;
		}

		$prune_mode_options = '';

		foreach ($this->settings->prune_modes as $i => $prune_mode)
		{
			$prune_mode_options .= '<option value="' . $i . '"' . (($i == $selected) ? ' selected="selected"' : '') . '>';
			$prune_mode_options .= $this->user->lang('MCHAT_ACP_' . strtoupper($prune_mode));
			$prune_mode_options .= '</option>';
		}

		return $prune_mode_options;
	}
}
