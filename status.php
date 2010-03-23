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

require_once ('config/config.inc.php');

if (!Jukebox::isActive())
{
	die(json_encode(array('jukebox' => false)));
}

$jukebox = new Jukebox();
$jukebox->cleanQueue();

$playlist = $jukebox->getPlaylist();
$queueLength = max(count($playlist) - 1, 0);

$time = $jukebox->getCurrentTrackTime();
$time['left'] = $time['total'] - $time['passed'];

if (auto_queue && $queueLength == 0 && $time['left'] < auto_queue_time_left)
{
	$jukebox->autoQueue();
}

$current = $jukebox->getCurrentTrack();
$album = new Album($current->artist, $current->albumName);

echo json_encode(array(
	'artist'	=> $current->artist,
	'track'		=> $current->title,
	'album'		=> $current->albumName,
	'has_cover'	=> $album->getCover() != NULL,
	'timePassed'	=> date('i:s', $time['passed']),
	'timeTotal'	=> date('i:s', $time['total']),
	'queueLength'	=> $queueLength,
	'jukebox'	=> true
));
?>
