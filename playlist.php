<?php
/*
 * fookebox
 * Copyright (C) 2007-2010 Stefan Ott. All rights reserved.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * $Id$
 */

require_once('config/config.inc.php');

if (!Jukebox::isActive())
{
	die();
}

$jukebox = new Jukebox();

$page = new Page();

$playlist = $jukebox->getPlaylist();
$data = array();

foreach($playlist as $item)
{
	$page = new Page();
	$page->assign('artist', $item->artist);
	$page->assign('title', $item->title);
	$page->assign('position', $item->pos);
	$data[] = $page->fetch('playlist-entry.tpl');
}

echo json_encode(array(
	'queue' => $data,
));
?>
