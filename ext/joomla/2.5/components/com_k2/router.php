<?php
/**
 * @version		$Id: router.php 1492 2012-02-22 17:40:09Z joomlaworks@gmail.com $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2012 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
//SELECT alias FROM `j25_k2_items` WHERE id = "1" LIMIT 1
//SELECT id FROM `j25_k2_items` WHERE alias = "test" LIMIT 1
function K2BuildRoute( & $query) {

    $segments = array ();
	
    $menu = & JSite::getMenu();
    if ( empty($query['Itemid'])) {
        $menuItem = & $menu->getActive();
    }
    else {
        $menuItem = & $menu->getItem($query['Itemid']);
    }
    $mView = ( empty($menuItem->query['view']))?null:$menuItem->query['view'];
    $mTask = ( empty($menuItem->query['task']))?null:$menuItem->query['task'];
    $mId = ( empty($menuItem->query['id']))?null:$menuItem->query['id'];
    $mTag = ( empty($menuItem->query['tag']))?null:$menuItem->query['tag'];

    if ( isset ($query['layout'])) {
        unset ($query['layout']);
    }

    if ( $mView == @$query['view'] && $mTask == @$query['task'] && $mId == @intval($query['id']) &&  @intval($query['id']) > 0 ) {
        unset ($query['view']);
        unset ($query['task']);
        unset ($query['id']);
    }

    if ( $mView == @$query['view'] && $mTask == @$query['task'] && $mTag == @$query['tag'] && isset($query['tag']) ) {
        unset ($query['view']);
        unset ($query['task']);
        unset ($query['tag']);
    }

    if ( isset ($query['view'])) {
        $view = $query['view'];
        $segments[] = $view;
        unset ($query['view']);
    }

    if (@ isset ($query['task'])) {
        $task = $query['task'];
        $segments[] = $task;
        unset ($query['task']);
    }

    if ( isset ($query['id'])) {
        $id = $query['id'];
        $segments[] = $id;
        unset ($query['id']);
    }

    if ( isset ($query['cid'])) {
        $cid = $query['cid'];
        $segments[] = $cid;
        unset ($query['cid']);
    }

    if ( isset ($query['tag'])) {
        $tag = $query['tag'];
        $segments[] = $tag;
        unset ($query['tag']);
    }

    if ( isset ($query['year'])) {
        $year = $query['year'];
        $segments[] = $year;
        unset ($query['year']);
    }

    if ( isset ($query['month'])) {
        $month = $query['month'];
        $segments[] = $month;
        unset ($query['month']);
    }

    if ( isset ($query['day'])) {
        $day = $query['day'];
        $segments[] = $day;
        unset ($query['day']);
    }

    if ( isset ($query['task'])) {
        $task = $query['task'];
        $segments[] = $task;
        unset ($query['task']);
    }

	// Изменения.
	if(isset($segments[0]))
	{
		if($segments[0]=='item')
		{
			$alias = explode(':',$segments[1]);
			$segments[0]=$segments[1];
			unset($segments[1]);
		}
	}
	// Изменения. Конец.
	
    return $segments;
}

function K2ParseRoute($segments) {
    $vars = array ();

	// Изменения.
	$id = false;
	if(count($segments)==1)
	{
		$alias = $segments[0];
		$alias = str_replace ( ':' , '-' , $alias);
		$als = explode("-", $alias);
		unset($als[0]);
		$alias = implode("-", $als);
		
		$db = &JFactory::getDBO();
		$query = 'SELECT id FROM `#__k2_items` WHERE alias = "'.$alias.'" LIMIT 1';
		$db->setQuery($query);
		$id = $db->loadResult();
	}
	
	if($id)
	{
		$vars['view'] = 'item'; 
		$vars['task'] = $id.':'.$alias;
		$vars['id'] = $vars['task'];
	}else{
	// Изменения. Конец.
	
    $vars['view'] = $segments[0];
    if (!isset($segments[1]))
        $segments[1]='';
    $vars['task'] = $segments[1];

    if ($segments[0] == 'itemlist') {

        switch($segments[1]) {

            case 'category':
                $vars['id'] = $segments[2];
                break;

            case 'tag':
                if (isset($segments[2]))
                    $vars['tag'] = $segments[2];
                break;

            case 'user':
                if (isset($segments[2]))
                    $vars['id'] = $segments[2];
                break;

            case 'date':
                if (isset($segments[2]))
                    $vars['year'] = $segments[2];
                if (isset($segments[3]))
                    $vars['month'] = $segments[3];
                if (isset($segments[4])) {
                    $vars['day'] = $segments[4];
                }
                break;

        }

    }

    else if ($segments[0] == 'item') {

        switch($segments[1]) {

            case 'edit':
                if (isset($segments[2]))
                    $vars['cid'] = $segments[2];
                break;

            case 'download':
                if (isset($segments[2]))
                    $vars['id'] = $segments[2];
                break;

            default:
                $vars['id'] = $segments[1];
                break;

        }

    }

	if($segments[0] == 'comments' && isset($segments[1]) && $segments[1] == 'reportSpammer') {
		$vars['id'] = $segments[2];
	}
	
	// Изменения.
	}
	// Изменения. Конец.
	
    return $vars;
}
