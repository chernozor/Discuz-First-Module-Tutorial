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
define('ADMINSCRIPT', 'admin.php');
//È¡±¾µØ¿ª·¢µÄ²å¼þÁÐ±í $develop_list
$develop_data = DISCUZ_ROOT.'/data/develop_data.php';
if(file_exists($develop_data)) {
	require_once $develop_data;
} else {
	$develop_list = array();
}
require_once libfile('function/admincp');
require_once libfile('function/plugin');
loadcache('plugin');
$outputsubmit = false;
$plugins = $addonids = array();
$plugins = C::t('common_plugin')->fetch_all_data();
if(empty($_G['cookie']['addoncheck_plugin'])) {
	foreach($plugins as $plugin) {
		$addonids[$plugin['pluginid']] = $plugin['identifier'].'.plugin';
	}
	$checkresult = dunserialize(cloudaddons_upgradecheck($addonids));
	savecache('addoncheck_plugin', $checkresult);
	dsetcookie('addoncheck_plugin', 1, 3600);
} else {
	loadcache('addoncheck_plugin');
	$checkresult = $_G['cache']['addoncheck_plugin'];
}
$splitavailable = $plugin_list = array();

foreach($plugins as $plugin) {
	if(!in_array($plugin['identifier'], $develop_list)) {
		continue;
	}
	$addonid = $plugin['identifier'].'.plugin';
	$updateinfo = '';
	list(, $newver) = explode(':', $checkresult[$addonid]);
	if($newver) {
		$plugin['updateinfo'] = '<a href="'.ADMINSCRIPT.'?action=cloudaddons&id='.$addonid.'" title="'.$devlang['plugins_online_update'].'" target="_blank"><font color="red">'.$devlang['plugins_find_newversion'].' '.$newver.'</font></a>';
	}
	$plugins[] = $plugin['identifier'];
	$hookexists = FALSE;
	$plugin['modules'] = dunserialize($plugin['modules']);
	if((empty($_GET['system']) && $plugin['modules']['system'] && !$updateinfo || !empty($_GET['system']) && !$plugin['modules']['system'])) {
		continue;
	}
	$submenuitem = array();
	if(is_array($plugin['modules'])) {
		foreach($plugin['modules'] as $k => $module) {
			if($module['type'] == 11) {
				$hookorder = $module['displayorder'];
				$hookexists = $k;
			}
			if($module['type'] == 3) {
				$plugin['submenuitem'][] = '<a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$plugin['pluginid'].'&identifier='.$plugin['identifier'].'&pmod='.$module['name'].'" target="_blank">'.$module['menu'].'</a>';
			}
		}
	}
	$outputsubmit = $hookexists !== FALSE && $plugin['available'] || $outputsubmit;
	$hl = !empty($_GET['hl']) && $_GET['hl'] == $plugin['pluginid'];
	$intro = $title = '';
	$order = !$updateinfo ? intval($plugin['modules']['system']) + 1 : 0;
	if($plugin['available']) {
		if(empty($splitavailable[0])) {
			//$title = '<tr><th colspan="15" class="partition">'.$devlang['plugins_list_available'].'</th></tr>';
			$plugin['title'] = $devlang['plugins_list_available'];
			$plugin['splitavailable'][0] = 1;
		}
	} else {
		if(empty($splitavailable[1])) {
			//$title = '<tr><th colspan="15" class="partition">'.$devlang['plugins_list_unavailable'].'</th></tr>';
			$plugin['title'] = $devlang['plugins_list_unavailable'];
			$plugin['splitavailable'][1] = 1;
		}
	}
	$plugin['imgsrc'] = cloudaddons_pluginlogo_url($plugin['identifier']);
	$plugin['name'] = dhtmlspecialchars($plugin['name']);
	$plugin['version'] = dhtmlspecialchars($plugin['version']);
	$plugin['copyright'] = dhtmlspecialchars($plugin['copyright']);
	$plugin['submenuitem'] = implode('', $plugin['submenuitem']);
	$plugin_list[$plugin['identifier']] = $plugin;
}

include template('header', 0, 'develop/template/common');
include template('list', 0, 'develop/template');
include template('footer', 0, 'develop/template/common');
?>