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

function get_auto_queue_lock()
{
	// Make sure nobody else can be running this function at the same time
	//
	// Yes, this could be done in a less annoying way if php's sem_acquire
	// were non-blocking

	// System V IPC functionality is not available on all OS' (eg. Windows)
	// If it isn't, good luck...
	if (!function_exists('shm_remove_var'))
		return true;

	$AUTO_QUEUE_LOCK = 1;

	$sem = sem_get($AUTO_QUEUE_LOCK, 1);
	sem_acquire($sem);

	// Now that we're alone, check whether anyone else is updating
	$shm = shm_attach($AUTO_QUEUE_LOCK);
	$update = @shm_get_var($shm, 'queue_update');

	$time = time();

	if ($update)
	{
		// Remove the lock if it's older than 10 seconds
		if ($time - $update < 5)
		{
			sem_release($sem);
			return false;
		}
		shm_remove_var($shm, 'queue_update');
	}
	shm_put_var($shm, 'queue_update', $time);

	sem_release($sem);
	return true;
}

function release_auto_queue_lock ()
{
	$AUTO_QUEUE_LOCK = 1;
	$shm = shm_attach($AUTO_QUEUE_LOCK);
	shm_remove_var($shm, 'queue_update');
}

function queue_random_song($mpd)
{
	if (auto_queue_playlist != '')
	{
		$mpd->PLLoad(auto_queue_playlist);

		if (!auto_queue_random)
		{
			$mpd->PLShuffle();
			$files = $mpd->GetPlaylist();
			$current = $files[0];
		}
		else
		{
			$files = $mpd->GetPlaylist();
			$current = $files[0];
			$mpd->PLRemove(0);
			$mpd->PLAdd($current['file']);
			$mpd->rm(auto_queue_playlist);
			$mpd->PLSave(auto_queue_playlist);
		}
		$mpd->PLClear();
		$mpd->PLAdd($current['file']);
		$mpd->Play();
	}
	else
	{
		$files = $mpd->listAll();
		$length = sizeof($files);
		$chosen = rand(0, $length - 1);
		$current = $files[$chosen];
		$mpd->PLAdd($current['file']);
		$mpd->Play();
	}
	return $current;
}

function remove_old_songs_from_the_queue($mpd)
{
	$status = $mpd->getStatus();

	while (array_key_exists('song', $status) && $status['song'] > 0)
	{
		// Some songs finished playing but are still in the playlist
		$mpd->PLRemove(0);
		$status = $mpd->getStatus();
	}

	return $status;
}

function clear_queue_if_no_more_songs($status, $mpd)
{
	if (!array_key_exists('song', $status) && ($mpd->playlist_count > 0))
	{
		// We ran out of songs - clear the playlist
		$mpd->PLRemove(0);
	}
}

if (!Jukebox::isActive())
{
	die(json_encode(array('jukebox' => false)));
}

$mpd = new mpd(mpd_host, mpd_port, mpd_pass);

$status = remove_old_songs_from_the_queue($mpd);
clear_queue_if_no_more_songs($status, $mpd);

$time = array_key_exists('time', $status) ? $status['time'] : '0:0';

list($timePassed, $timeTotal) = split(':', $time);

if (empty($timePassed))
	$timePassed = 0;

$playlist = $mpd->getPlaylist();
$queueLength = max(count($playlist) - 1, 0);

if ($queueLength == 0 && $status['state'] == 'stop' && auto_queue
	&& get_auto_queue_lock())
{
	// we were running out of songs, queue a random one
	$current = queue_random_song($mpd);
	release_auto_queue_lock();
}
else
{
	$current = $playlist[$status['song']];
}

echo json_encode(array(
	'artist'	=> $current['Artist'],
	'track'		=> $current['Title'],
	'timePassed'	=> date('i:s', $timePassed),
	'timeTotal'	=> date('i:s', $timeTotal),
	'queueLength'	=> $queueLength,
	'jukebox'	=> true
));
?>
