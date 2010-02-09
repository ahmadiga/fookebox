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

global $_PROGRAM;

require_once(realpath(dirname(__FILE__) . '/../../config/status.conf.php'));

class Jukebox
{
	private $mpd;

	// cache
	private $currentTrack;
	private $playlist;

	public function __construct()
	{
		$this->mpd = new mpd(mpd_host, mpd_port, mpd_pass);
		$this->currentTrack = null;
		$this->playlist = null;
	}

	public function pause()
	{
		if (!enable_controls) return;
		$this->mpd->Pause();
	}

	public function play()
	{
		if (!enable_controls) return;
		$this->mpd->Play();
	}

	public function previous()
	{
		if (!enable_controls) return;
		$this->mpd->Previous();
	}

	public function next()
	{
		if (!enable_controls) return;
		$this->mpd->Next();
	}

	public function stop()
	{
		if (!enable_controls) return;
		$this->mpd->Stop();
	}

	public function volumeUp()
	{
		if (!enable_controls) return;
		$this->mpd->AdjustVolume(5);
	}

	public function volumeDown()
	{
		if (!enable_controls) return;
		$this->mpd->AdjustVolume(-5);
	}

	public function refreshDB()
	{
		if (!enable_controls) return
		$this->mpd->DBRefresh();
	}

	public function getArtists()
	{
		return $this->mpd->GetArtists();
	}

	public function getGenres()
	{
		return $this->mpd->GetGenres();
	}

	public function getPlaylist()
	{
		if ($this->playlist == null)
		{
			$this->playlist = array();
			foreach ($this->mpd->GetPlaylist() as $item)
			{
				$this->playlist[] = new Track($item);
			}
		}
		return $this->playlist;
	}

	public function queue($file)
	{
		$this->currentTrack = null;
		$this->playlist = null;

		$this->mpd->PLAdd($file);
		$this->mpd->Play();
	}

	public function unqueue($id)
	{
		$this->currentTrack = null;
		$this->playlist = null;

		$this->mpd->PLRemove($id);
	}

	public function isStopped()
	{
		$status = $this->mpd->getStatus();
		return $status['state'] == 'stop';
	}

	public function search($where, $what, $exact)
	{
		if (!in_array($where, array(
			MPD_SEARCH_GENRE, MPD_SEARCH_ARTIST, MPD_SEARCH_ALBUM,
			MPD_SEARCH_TITLE, MPD_SEARCH_FILENAME, MPD_SEARCH_ANY
		))) return array();

		if ($exact)
			return $this->mpd->Find($where, $what);
		else
			return $this->mpd->Search($where, $what);
	}

	public function cleanQueue()
	{
		$status = $this->mpd->getStatus();

		while (array_key_exists('song', $status) && $status['song'] > 0)
		{
			// Some songs finished playing but are still in the
			// playlist
			$this->unqueue(0);
			$status = $this->mpd->getStatus();
		}

		if (!array_key_exists('song', $status) &&
			($this->mpd->playlist_count > 0))
		{
			// We have run out of songs, clear the playlist
			$this->unqueue(0);
		}
	}

	public function getCurrentTrack()
	{
		if ($this->currentTrack != null)
			return $this->currentTrack;

		$status = $this->mpd->getStatus();
		$playlist = $this->getPlaylist();

		return $playlist[$status['song']];
	}

	public function getCurrentTrackTime()
	{
		$status = $this->mpd->getStatus();

		if (array_key_exists('time', $status))
		{
			$time = $status['time'];
			list($timePassed, $timeTotal) = split(':', $time);

			if (empty($timePassed))
				$timePassed = 0;

		}
		else
		{
			$timePassed = 0;
			$timeTotal = 0;
		}

		return array(
			'passed' => $timePassed,
			'total'  => $timeTotal
		);
	}

	private function getAutoQueueLock()
	{
		// Make sure nobody else can be running this function at the
		// same time
		//
		// Yes, this could be done in a less annoying way if php's
		// sem_acquire were non-blocking

		// System V IPC functionality is not available on all OS'
		// (eg. Windows). If it isn't, good luck...
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

	private function releaseAutoQueueLock()
	{
		$AUTO_QUEUE_LOCK = 1;
		$shm = shm_attach($AUTO_QUEUE_LOCK);
		shm_remove_var($shm, 'queue_update');
	}

	public function autoQueue()
	{
		if (!$this->getAutoQueueLock())
			return;

		if (auto_queue_playlist != '')
		{
			$this->mpd->PLLoad(auto_queue_playlist);

			if (auto_queue_random)
			{
				$this->mpd->PLShuffle();
				$files = $this->getPlaylist();
				$file = $files[0];
			}
			else
			{
				$files = $this->getPlaylist();
				$file = $files[0];
				$this->mpd->PLRemove(0);
				$this->mpd->PLAdd($file['file']);
				$this->mpd->rm(auto_queue_playlist);
				$this->mpd->PLSave(auto_queue_playlist);
			}
			$this->mpd->PLClear();
			$this->queue($file['file']);
		}
		else
		{
			$files = $this->mpd->listAll();
			$length = sizeof($files);
			$chosen = rand(0, $length - 1);
			$current = $files[$chosen];
			$this->queue($current['file']);
		}

		$this->releaseAutoQueueLock();
	}

	private static function getProgram()
	{
		global $_PROGRAM;
		return $_PROGRAM;
	}

	private static function getCurrentEventId()
	{
		return CURRENT_EVENT;
	}

	public static function getEvent()
	{
		$program = Jukebox::getProgram();
		$eventid = Jukebox::getCurrentEventId();
		return $program[$eventid];
	}

	public static function hasNextEvent()
	{
		$program = Jukebox::getProgram();
		$eventid = Jukebox::getCurrentEventId();
		return $eventid < count($program) - 1;
	}

	public static function getNextEvent()
	{
		$program = Jukebox::getProgram();
		$eventid = Jukebox::getCurrentEventId();
		return $program[$eventid + 1];
	}

	public static function isActive()
	{
		$current = Jukebox::getEvent();
		return $current->isJukebox();
	}
}
?>
