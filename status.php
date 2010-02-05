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

require_once ('config/config.inc.php');

if (!Jukebox::isActive())
{
	die(json_encode(array('jukebox' => false)));
}

$jukebox = new Jukebox();
$jukebox->cleanQueue();
$time = $jukebox->getCurrentTrackTime();

$playlist = $jukebox->getPlaylist();
$queueLength = max(count($playlist) - 1, 0);

if ($queueLength == 0 && $jukebox->isStopped() && auto_queue)
{
	$jukebox->autoQueue();
}

$current = $jukebox->getCurrentTrack();

echo json_encode(array(
	'artist'	=> $current['Artist'],
	'track'		=> $current['Title'],
	'timePassed'	=> date('i:s', $time['passed']),
	'timeTotal'	=> date('i:s', $time['total']),
	'queueLength'	=> $queueLength,
	'jukebox'	=> true
));
?>
