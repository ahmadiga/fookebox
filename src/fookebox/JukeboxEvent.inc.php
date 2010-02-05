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

class JukeboxEvent extends Event
{
	public function __construct($time)
	{
		parent::__construct($time, site_name);
	}

	public function getPlaylistItem($index)
	{
		$jukebox = new Jukebox();
		$playlist = $jukebox->getPlaylist();

		if (count($playlist) < $index + 1)
		{
			return NULL;
		}

		$current = $playlist[$index];

		list ($timePassed, $timeTotal) = split(':', $this->getTime());

		$artist	= $current['Artist'];
		$track = $current['Title'];

		return sprintf("%s - %s", $artist, $track);
	}

	public function getAsCurrent()
	{
		if ($cur = $this->getPlaylistItem(0))
		{
			return $cur;
		}
		else
		{
			return sprintf("%s jukebox", $this->getName());
		}
	}

	public function getAsNext()
	{
		return sprintf("%s jukebox", $this->getName());
	}

	public function getState()
	{
		if ($next = $this->getPlaylistItem(1))
		{
			return sprintf("next @ %s jukebox: %s", $this->getName(), $next);
		}
		else
		{
			return sprintf("%s jukebox", $this->getName());
		}
	}

	public function isJukebox()
	{
		return true;
	}
}
?>
