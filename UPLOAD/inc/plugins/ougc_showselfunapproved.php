<?php

/***************************************************************************
 *
 *   OUGC Show Self Unaproved plugin (/inc/plugins/ougc_showselfunapproved.php)
 *	 Author: Omar Gonzalez
 *   Copyright: © 2012 Omar Gonzalez
 *   
 *   Website: http://community.mybb.com/user-25096.html
 *
 *   Allow users to see their own unapproved post/threads from the showthread/forumdisplay/search pages.
 *
 ***************************************************************************
 
****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('This file cannot be accessed directly.');

// Run the hooks.
if(defined('IN_ADMINCP'))
{
	defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', MYBB_ROOT.'inc/plugins/pluginlibrary.php');

	// Necessary plugin information for the ACP plugin manager.
	function ougc_showselfunapproved_info()
	{
		global $lang, $plugins_cache;
		$lang->load('ougc_showselfunapproved');

		if(is_array($plugins_cache) && is_array($plugins_cache['active']) && isset($plugins_cache['active']['ougc_showselfunapproved']))
		{
			global $PL;
			$PL or require_once(PLUGINLIBRARY);
			ougc_showselfunapproved_edits();
			if(ougc_showselfunapproved_applyedits() !== true)
			{
				$action = 'apply';
				$lang_val = 'ougc_showselfunapproved_edits_apply';
			}
			elseif(ougc_showselfunapproved_revertedits() !== true)
			{
				$action = 'revert';
				$lang_val = 'ougc_showselfunapproved_edits_revert';
			}
			$lang->ougc_showselfunapproved_d .= '<ul><li style="list-style-image: url(styles/default/images/icons/custom.gif)"><a href="'.$PL->url_append('index.php', array('module' => 'config-plugins', 'ougc_showselfunapproved' => $action, 'my_post_key' => $GLOBALS['mybb']->post_code)).'">'.$lang->$lang_val.'</a></li></ul>';
		}
		return array(
			'name'			=> 'OUGC Show Self Unaproved threads',
			'description'	=> $lang->ougc_showselfunapproved_d,
			'website'		=> 'http://udezain.com.ar/',
			'author'		=> 'Omar Gonzalez',
			'authorsite'	=> 'http://udezain.com.ar/',
			'version'		=> '1.0',
			'compatibility'	=> '16*'
		);
	}

	// Activate the plugin
	function ougc_showselfunapproved_activate()
	{
		global $mybb, $lang;
		$lang->load('ougc_showselfunapproved');

		if($mybb->version_code < 1604)
		{
			flash_message($lang->ougc_showselfunapproved_mybbtoold, "error");
			admin_redirect("index.php?module=config-plugins");
		}

		if(!file_exists(PLUGINLIBRARY))
		{
			flash_message($lang->ougc_showselfunapproved_pluginlibrarymissing, "error");
			admin_redirect("index.php?module=config-plugins");
		}

		global $PL;
		$PL or require_once(PLUGINLIBRARY);

		if($PL->version < 6)
		{
			flash_message($lang->ougc_showselfunapproved_pluginlibraryold, "error");
			admin_redirect("index.php?module=config-plugins");
		}
	}

	// Deactivate the plugin and make sure edits are reverted
	function ougc_showselfunapproved_deactivate()
	{
		ougc_showselfunapproved_revertedits(true);
	}

	// Apply core edits
	function ougc_showselfunapproved_applyedits($apply=false)
	{
		global $PL;
		$PL or require_once PLUGINLIBRARY;

		$edits = array(
			array(
				'search' => array('if(!$thread[\'tid\'] || ($thread[\'visible\'] == 0 && $ismod == false) || ($thread[\'visible\'] > 1 && $ismod == true))', '{'),
				'replace' => array(
					'if(!$thread[\'tid\'] || (!$thread[\'visible\'] && !$ismod && (!$thread[\'uid\'] || $thread[\'uid\'] != $mybb->user[\'uid\'])) || ($thread[\'visible\'] > 1 && $ismod))',
					'{',
				),
			),
		);

		return $PL->edit_core('ougc_showselfunapproved', 'showthread.php', $edits, $apply);
	}

	// Rever core edits
	function ougc_showselfunapproved_revertedits($apply=false)
	{
		global $PL;
		$PL or require_once PLUGINLIBRARY;

		return $PL->edit_core('ougc_showselfunapproved', 'showthread.php', array(), $apply);
	}

	// Here we check if user is trying to apply/rever edits
	function ougc_showselfunapproved_edits()
	{
		global $mybb;

		// Check for core file edit action
		if($mybb->input['my_post_key'] == $mybb->post_code)
		{
			if($mybb->input['ougc_showselfunapproved'] == 'apply')
			{
				global $lang;
				$lang->load('ougc_showselfunapproved');

				if(ougc_showselfunapproved_applyedits(true) === true)
				{
					flash_message($lang->ougc_showselfunapproved_apply_success, 'success');
					admin_redirect('index.php?module=config-plugins');
				}
				else
				{
					flash_message($lang->ougc_showselfunapproved_apply_error, 'error');
					admin_redirect('index.php?module=config-plugins');
				}
			}
			elseif($mybb->input['ougc_showselfunapproved'] == 'revert')
			{
				global $lang;
				$lang->load('ougc_showselfunapproved');

				if(ougc_showselfunapproved_revertedits(true) === true)
				{
					flash_message($lang->ougc_showselfunapproved_revert_success, 'success');
					admin_redirect('index.php?module=config-plugins');
				}
				else
				{
					flash_message($lang->ougc_showselfunapproved_revert_error, 'error');
					admin_redirect('index.php?module=config-plugins');
				}
			}
		}
	}
}
else
{
	// Add our hooks to run our necessary functions
	$plugins->add_hook('forumdisplay_start', 'ougc_showselfunapproved_forumdisplay');
	function ougc_showselfunapproved_forumdisplay()
	{
		global $mybb;
		$fid = intval($mybb->input['fid']);

		if(is_moderator($fid))
		{
			return;
		}

		$visibleonly_search = "AND visible=\'1\'";
		$visibleonly_replace = "AND (visible=\'1\' OR (uid=\'{$mybb->user['uid']}\' AND visible!=\'1\'))";
		$tvisibleonly_search = "AND t.visible=\'1\'";
		$tvisibleonly_replace = "AND (t.visible=\'1\' OR (t.uid=\'{$mybb->user['uid']}\' AND t.visible!=\'1\'))";

		// Do it!
		control_object($GLOBALS['db'], '
			function query($string, $hide_errors=0, $write_query=0) {
				if(!$write_query && strpos($string, \''.$visibleonly_search.'\') && !strpos($string, \''.$visibleonly_replace.'\')) {
					$string = strtr($string, array(
						\''.$visibleonly_search.'\' => \''.$visibleonly_replace.'\'
					));
				}
				if(!$write_query && strpos($string, \''.$tvisibleonly_search.'\') && !strpos($string, \''.$tvisibleonly_replace.'\')) {
					$string = strtr($string, array(
						\''.$tvisibleonly_search.'\' => \''.$tvisibleonly_replace.'\'
					));
				}
				return parent::query($string, $hide_errors, $write_query);
			}
		');
	}

	$plugins->add_hook('showthread_start', 'ougc_showselfunapproved_showthread');
	function ougc_showselfunapproved_showthread()
	{
		global $mybb, $fid;

		if(is_moderator($fid) || !$mybb->user['uid'])
		{
			return;
		}

		$visibleonly_search1 = "t.visible=1 AND";
		$visibleonly_replace1 = "(t.visible=1 OR (t.visible!=1 AND t.uid={$mybb->user['uid']})) AND";

		$visibleonly_search2 = "AND p.visible=\'1\'";
		$visibleonly_replace2 = "AND (p.visible=\'1\' OR (p.visible!=\'1\' AND p.uid=\'{$mybb->user['uid']}\'))";

		$visibleonly_search3 = "AND t.visible=\'1\'";
		$visibleonly_replace3 = "AND (t.visible=\'1\' OR (t.visible!=\'1\' AND t.uid=\'{$mybb->user['uid']}\'))";

		// Do it!
		control_object($GLOBALS['db'], '
			function query($string, $hide_errors=0, $write_query=0) {
				if(!$write_query && strpos($string, \''.$visibleonly_search1.'\') && !strpos($string, \''.$visibleonly_replace1.'\'))
				{
					$string = strtr($string, array(
						\''.$visibleonly_search1.'\' => \''.$visibleonly_replace1.'\'
					));
				}
				if(!$write_query && strpos($string, \''.$visibleonly_search2.'\') && !strpos($string, \''.$visibleonly_replace2.'\'))
				{
					$string = strtr($string, array(
						\''.$visibleonly_search2.'\' => \''.$visibleonly_replace2.'\'
					));
				}
				if(!$write_query && strpos($string, \''.$visibleonly_search3.'\') && !strpos($string, \''.$visibleonly_replace3.'\'))
				{
					$string = strtr($string, array(
						\''.$visibleonly_search3.'\' => \''.$visibleonly_replace3.'\'
					));
				}
				return parent::query($string, $hide_errors, $write_query);
			}
		');
	}

	$plugins->add_hook('search_results_start', 'ougc_showselfunapproved_search');
	function ougc_showselfunapproved_search()
	{
		global $mybb;

		if(!$mybb->user['uid'])
		{
			return;
		}

		$visibleonly_search1 = "t.visible>0";
		$visibleonly_replace1 = "(t.visible>0 OR (t.visible<1 AND t.uid={$mybb->user['uid']}))";

		$visibleonly_search2 = "visible=1";
		$visibleonly_replace2 = "(visible=1 OR (visible!=1 AND uid={$mybb->user['uid']}))";

		$visibleonly_search3 = "visible < 1";
		$visibleonly_replace3 = "(visible < 1 OR (visible > 0 AND uid={$mybb->user['uid']}))";

		// Do it!
		control_object($GLOBALS['db'], '
			function query($string, $hide_errors=0, $write_query=0) {
				if(!$write_query && strpos($string, \''.$visibleonly_search1.'\') && !strpos($string, \''.$visibleonly_replace1.'\'))
				{
					$string = strtr($string, array(
						\''.$visibleonly_search1.'\' => \''.$visibleonly_replace1.'\'
					));
				}
				if(!$write_query && strpos($string, \''.$visibleonly_search2.'\') && !strpos($string, \''.$visibleonly_replace2.'\'))
				{
					$string = strtr($string, array(
						\''.$visibleonly_search2.'\' => \''.$visibleonly_replace2.'\'
					));
				}
				if(!$write_query && strpos($string, \''.$visibleonly_search3.'\') && !strpos($string, \''.$visibleonly_replace3.'\'))
				{
					$string = strtr($string, array(
						\''.$visibleonly_search3.'\' => \''.$visibleonly_replace3.'\'
					));
				}
				return parent::query($string, $hide_errors, $write_query);
			}
		');
	}

	// Control object written by Zinga Burga / Yumi from the MyBBHacks community (http://mybbhacks.zingaburga.com)
	if(!function_exists('control_object'))
	{
		function control_object(&$obj, $code)
		{
			static $cnt = 0;
			$newname = '_objcont_'.(++$cnt);
			$objserial = serialize($obj);
			$classname = get_class($obj);
			$checkstr = 'O:'.strlen($classname).':"'.$classname.'":';
			$checkstr_len = strlen($checkstr);
			if(substr($objserial, 0, $checkstr_len) == $checkstr)
			{
				$vars = array();
				foreach((array)$obj as $k => $v)
				{
					if($p = strrpos($k, "\0"))
						$k = substr($k, $p+1);
					$vars[$k] = $v;
				}
				if(!empty($vars))
				{
					$code .= '
						function ___setvars(&$a) {
							foreach($a as $k => &$v)
								$this->$k = $v;
						}
					';
				}
				eval('class '.$newname.' extends '.$classname.' {'.$code.'}');
				$obj = unserialize('O:'.strlen($newname).':"'.$newname.'":'.substr($objserial, $checkstr_len));
				if(!empty($vars))
				{
					$obj->___setvars($vars);
				}
			}
		}
	}
}