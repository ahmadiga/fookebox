<?php
/*
 * fookebox
 * Copyright (C) 2007-2009 Stefan Ott. All rights reserved.
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
require_once (src_path . '/mpd.inc.php');
require_once (src_path . '/Jukebox.inc.php');
require_once (src_path . '/RootPage.inc.php');
require_once (libdesire_path . 'view/Page.inc.php');
require_once (libdesire_path . 'util/io.inc.php');

function get_auto_queue_lock()
{
	// Make sure nobody else can be running this function at the same time
	//
	// Yes, this could be done in a less annoying way if php's sem_acquire
	// were non-blocking

	// System V IPC functionality is not available on all OS' (eg. Windows)
	// If it isn't, good luck...
	if (!function_exists ('shm_remove_var'))
		return true;

	$AUTO_QUEUE_LOCK = 1;

	$sem = sem_get ($AUTO_QUEUE_LOCK, 1);
	sem_acquire ($sem);

	// Now that we're alone, check whether anyone else is updating
	$shm = shm_attach ($AUTO_QUEUE_LOCK);
	$update = @shm_get_var ($shm, 'queue_update');

	$time = time ();

	if ($update)
	{
		// Remove the lock if it's older than 10 seconds
		if ($time - $update < 5)
		{
			sem_release ($sem);
			return false;
		}
		shm_remove_var ($shm, 'queue_update');
	}
	shm_put_var ($shm, 'queue_update', $time);

	sem_release ($sem);
	return true;
}

function release_auto_queue_lock ()
{
	$AUTO_QUEUE_LOCK = 1;
	$shm = shm_attach ($AUTO_QUEUE_LOCK);
	shm_remove_var ($shm, 'queue_update');
}

$lastUpdate = require_key ('updated', $_GET);
$clientQueueLength = require_key ('qlen', $_GET);

$jukebox = new Jukebox ();
if (!$jukebox->isActive ())
{
	json_msg ('JUKEBOX_DISABLED');
	die ();
}

$mpd = new mpd (mpd_host, mpd_port, mpd_pass);

$page = new Page ();
$page->assign ('mpd', $mpd);

$status = $mpd->getStatus ();

while ($status ['song'] > 0) {
	// Some songs finished playing but are still in the playlist
	$mpd->PLRemove(0);
	$status = $mpd->getStatus ();
}
if (($status ['song'] !== '0') && ($mpd->playlist_count > 0)) {
	// We ran out of songs - clear the playlist
	$mpd->PLRemove(0);
}

$playlist = $mpd->getPlaylist ();

$current = $playlist [$status ['song']];
$time = $status ['time'];

list ($timePassed, $timeTotal) = split (':', $time);

if (empty ($timePassed))
		$timePassed = 0;

$currentTime = time ();
$queueLength = max (count ($playlist) - 1, 0);


if (($timePassed > 0) && ($currentTime - $lastUpdate > $timePassed))
{
	// the song has changed
	json_msg ('SONG_CHANGED');
}
else if ($clientQueueLength < $queueLength)
{
	// somebody queued a song
	json_msg ('SONG_QUEUED');
}
else if ($clientQueueLength > $queueLength)
{
	// somebody removed a song from the queue
	json_msg ('SONG_REMOVED');
}
else if ($queueLength == 0 && $status['state'] == 'stop' && auto_queue
	&& get_auto_queue_lock ())
{
	// we were running out of songs, queue a random one
	$files = $mpd->listAll();
	$length = sizeof($files);
	$chosen = rand(0, $length - 1);
	$file = $files[$chosen];
	$mpd->PLAdd ($file['file']);
	$mpd->Play ();
	release_auto_queue_lock ();
	json_msg ('SONG_CHANGED');
}
else
{
	$data = array (
		'artist'	=> $current ['Artist'],
		'track'		=> $current ['Title'],
		'timePassed'	=> date ('i:s', $timePassed),
		'timeTotal'	=> date ('i:s', $timeTotal),
	);

	json_data ('status', $data);
}
?>
