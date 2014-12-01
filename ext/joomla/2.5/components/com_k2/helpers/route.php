<?php
/**
 * @version		$Id: route.php 1339 2011-11-25 16:00:20Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2011 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.helper');

class K2HelperRoute
{

	function getItemRoute($id, $catid = 0) {
		$needles = array (
			'item'=>(int)$id,
			'category'=>(int)$catid,
		);
		$link = 'index.php?option=com_k2&view=item&id='.$id;
		if ($item = K2HelperRoute::_findItem($needles)) {
			$link .= '&Itemid='.$item->id;
		}
		return $link;
	}

	function getCategoryRoute($catid)	{
		$needles = array (
			'category'=>(int)$catid
		);
		$link = 'index.php?option=com_k2&view=itemlist&task=category&id='.$catid;
		if ($item = K2HelperRoute::_findItem($needles)) {
			$link .= '&Itemid='.$item->id;
		}
		return $link;
	}

	function getUserRoute($userID) {
		$needles = array (
			'user'=>(int)$userID
		);
		$user = &JFactory::getUser($userID);
		if(K2_JVERSION == '16' && JFactory::getConfig()->get('unicodeslugs') == 1) {
			$alias = JApplication::stringURLSafe($user->name);
		}
		else if(JPluginHelper::isEnabled('system', 'unicodeslug') || JPluginHelper::isEnabled('system', 'jw_unicodeSlugsExtended')){
			$alias = JFilterOutput::stringURLSafe($user->name);
		} else {
			mb_internal_encoding("UTF-8");
			mb_regex_encoding("UTF-8");
			$alias = trim(mb_strtolower($user->name));
			$alias = str_replace('-', ' ', $alias);
			$alias = mb_ereg_replace('[[:space:]]+', ' ', $alias);
			$alias = trim(str_replace(' ', '', $alias));
			$alias = str_replace('.', '', $alias);

			$stripthese = ',|~|!|@|%|^|(|)|<|>|:|;|{|}|[|]|&|`|â€ž|â€¹|â€™|â€˜|â€œ|â€�|â€¢|â€º|Â«|Â´|Â»|Â°|«|»|…';
			$strips = explode('|', $stripthese);
			foreach ($strips as $strip) {
				$alias = str_replace($strip, '', $alias);
			}
			$params = &K2HelperUtilities::getParams('com_k2');
			$SEFReplacements = array();
			$items = explode(',', $params->get('SEFReplacements', NULL));
			foreach ($items as $item) {
				if (! empty($item)) {
					@list($src, $dst) = explode('|', trim($item));
					$SEFReplacements[trim($src)] = trim($dst);
				}
			}
			foreach ($SEFReplacements as $key=>$value) {
				$alias = str_replace($key, $value, $alias);
			}
			$alias = trim($alias, '-.');
			if (trim(str_replace('-', '', $alias)) == '') {
				$datenow = &JFactory::getDate();
				$alias = $datenow->toFormat("%Y-%m-%d-%H-%M-%S");
			}
		}
		$link = 'index.php?option=com_k2&view=itemlist&task=user&id='.$userID.':'.$alias;
		if ($item = K2HelperRoute::_findItem($needles)) {
			$link .= '&Itemid='.$item->id;
		}
		return $link;
	}

	function getTagRoute($tag) {
		$needles = array (
			'tag'=>$tag
		);
		$link = 'index.php?option=com_k2&view=itemlist&task=tag&tag='.urlencode($tag);
		if ($item = K2HelperRoute::_findItem($needles)) {
			$link .= '&Itemid='.$item->id;
		}
		return $link;
	}

	function _findItem($needles)	{
		$component = & JComponentHelper::getComponent('com_k2');
		$menus = & JApplication::getMenu('site', array ());
		if(K2_JVERSION=='16'){
			$items = $menus->getItems('component_id', $component->id);
		} else {
			$items = $menus->getItems('componentid', $component->id);
		}
		$match = null;
		foreach ($needles as $needle=>$id) {
			if (count($items)){
				foreach ($items as $item) {
					if ($needle=='user' || $needle == 'category') {
						if ((@$item->query['task'] == $needle) && (@$item->query['id'] == $id)) {
							$match = $item;
							
							
							// added by pp@sweetgroup.ru
							$exp = explode('/', $_SERVER['REQUEST_URI']);
							if (isset($exp[2]) && isset($exp[3])) {

								// itemlist/category
								if ($exp[2] == 'itemlist' && $exp[3] == 'category') {
									header('Location: /'.$match->route.'.html',TRUE,301);
									exit;
								}
							} else {
								if (isset($exp[1]) and isset($exp[2])) {
									if ($exp[1] == 'putklin') {
										$exp_num = explode('-', $exp[2]);
										if (isset($exp_num[0]) && is_numeric($exp_num[0])) {
											//print_r($match->route.'/'.$exp[2]);
											header('Location: /'.$match->route.'/'.$exp[2].'.html',TRUE,301);
											exit;
										}

									}
								}
							}
							// eof pp@sweetgroup.ru
							break;
						}
					} else if ($needle=='tag') {
						if ((@$item->query['task'] == $needle) && (@$item->query['tag'] == $id)) {
							$match = $item;
							break;
						}
					} else {
						if ((@$item->query['view'] == $needle) && (@$item->query['id'] == $id)) {
							$match = $item;
							// added by pp@sweetgroup.ru
							//print_r($match);
							//exit;
							// eof pp@sweetgroup.ru
							break;
						}
					}
					if (!is_null($match)) {
						break;
					}
				}
				// Second pass [START]
				// Only for multiple categories links. Triggered only if we do not have find any match (link to direct category)
				if(is_null($match)) {
					foreach ($items as $item) {
						if ($needle=='category') {
							if(!isset($item->K2Categories)) {
								if(K2_JVERSION == '15') {
									$menuparams = explode("\n", $item->params);
									foreach($menuparams as $param) {
										if(strpos($param, 'categories=')===0) {
											$array = explode('categories=', $param);
											$item->K2Categories = explode('|', $array[1]);
										}
									}
								} else {
									$menuparams = json_decode($item->params);
									$item->K2Categories = isset($menuparams->categories)? $menuparams->categories: array();
								}
							}
							if(isset($item->K2Categories) && is_array($item->K2Categories)) {
								foreach ($item->K2Categories as $catid)	{
									if ((@$item->query['view'] == 'itemlist') && (@$item->query['task'] == '') && (@(int)$catid == $id)) {
										$match = $item;
										break;
									}
								}
							}
						}
						if (!is_null($match)) {
							break;
						}
					}
				}
				// Second pass [END]
			}
			if (!is_null($match)) {
				break;
			}
		}
		return $match;
	}
}
