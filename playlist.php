<?php
/*
 * fookebox
 * Copyright (C) 2007-2010 Stefan Ott. All rights reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * $Id$
 */

require_once('config/config.inc.php');

if (!Jukebox::isActive())
{
	die();
}

$mpd = new mpd(mpd_host, mpd_port, mpd_pass);

$page = new Page();
$page->assign('mpd', $mpd);

$playlist = $mpd->getPlaylist();
$data = array();

foreach($playlist as $item)
{
	$page = new Page();
	$page->assign('artist', $item ['Artist']);
	$page->assign('title', $item ['Title']);
	$page->assign('position', $item ['Pos']);
	$data[] = $page->fetch('playlist-entry.tpl');
}

echo json_encode(array(
	'queue' => $data,
));
?>
