<?php
/**
 * 2015 Webmaster Gambit
 * Discuz! Develop
 * http://webmaster-gambit.com/
 * 1.0.0
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$action = in_array($_GET['action'], array('create', 'edit', 'list', 'ajax')) ? $_GET['action'] : 'list';

require_once DISCUZ_ROOT.'develop/plugin.lang.php';
if(in_array($action, array('create', 'edit'))) {
	$operations = array('regplugin', 'script', 'hook', 'modules', 'setting', 'language', 'export', 'style', 'check_identifier');
	$operation = $_GET['operation'] && in_array($_GET['operation'], $operations) ? $_GET['operation'] : 'regplugin';
	if($operation == 'check_identifier') {
		if($_GET['id']) {
			$result = dfsockopen('http://addon.discuz.com/api/developercheck.php?ac=addonid&id='.$_GET['id']);
		} else {
			$result = '';
		}
		exit($result);
	}
	$cur_operation[$operation] = 'class="a"';
	// Gets the specified plug-in logs
	$plugin = array();
	$pluginid = intval($_GET['pluginid']);
	if($pluginid) {
		$plugin = DB::fetch_first('SELECT * FROM '.DB::table('common_plugin')." WHERE pluginid='$pluginid'");
		if($plugin) {
			$plugin['modules'] = unserialize($plugin['modules']);
		} else {
			$pluginid = 0;
			// Не можете найти плагин для перенаправления для создания плагинов)
			$operation = 'regplugin';
			$action = 'create';
		}
	}
	if($operation != 'regplugin' && empty($plugin)) {
		devmessage('None related plug-ins', '', 'error');
	}
	//Each step corresponds to the script // Load
	require_once DISCUZ_ROOT.'develop/include/'.$operation.'.php';

	include template('header', 0, 'develop/template/common');
	include template('plugin', 0, 'develop/template');
	include template('footer', 0, 'develop/template/common');
} else if($action == 'list') {
	require_once DISCUZ_ROOT.'develop/include/list.php';
} else if($action == 'ajax') {
	if($_GET['operation'] == 'gethook') {
		$sort = dhtmlspecialchars(preg_replace("/[^\[A-Za-z0-9_\]]/", '', $_GET['sort']));
		$page = dhtmlspecialchars(preg_replace("/[^\[A-Za-z0-9_\.\]]/", '', $_GET['page']));
		require_once DISCUZ_ROOT.'develop/include/hooklist.php';
		$hooklist = $_GET['type'] == 'mobile' ? $mobilehook : $generalhook;
		// List page
		$hooks = $pagelist = array();
		if(isset($hooklist[$sort]) && !empty($hooklist[$sort])) {
			foreach($hooklist[$sort] as $key => $value) {
				if($key == 'lang') {
					continue;
				}
				$pagelist[$key] = isset($value['lang']) ? $value['lang'] : $key;
				if($page && $key == $page) {
					$hooks = $value;
				}
			}
		}
		include template('ajax', 0, 'develop/template');
	}
}
?>