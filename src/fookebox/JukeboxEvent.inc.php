<?php
/*
 * fookebox
 * Copyright (C) 2007-2008 Stefan Ott. All rights reserved.
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

require_once (realpath (dirname (__FILE__) . '/../../config/config.inc.php'));
require_once (src_path . '/Event.inc.php');
require_once (src_path . '/mpd.inc.php');

class JukeboxEvent extends Event
{
	function JukeboxEvent ($time, $name = site_name)
	{
		parent :: Event ($time, $name);
	}

	function getPlaylistItem ($index)
	{
		$mpd = new mpd (mpd_host, mpd_port, mpd_pass);
		$playlist = $mpd->getPlaylist ();

		if (count ($playlist) < $index + 1)
		{
			return NULL;
		}

		$current = $playlist [$index];

		list ($timePassed, $timeTotal) = split (':', $time);

		$artist	= $current ['Artist'];
		$track = $current ['Title'];

		return $artist . ' - ' . $track;
	}

	function getAsCurrent ()
	{
		if ($cur = $this->getPlaylistItem (0))
		{
			return $cur;
		} else {
			return $this->getName () . " jukebox";
		}
	}

	function getAsNext ()
	{
		return $this->getName () . " jukebox";
	}

	function getState ()
	{
		if ($nxt = $this->getPlaylistItem (1))
		{
			return "next @ " . $this->getName () . " jukebox: $nxt";
		} else {
			return $this->getName () . " jukebox";
		}
	}

	function isJukebox ()
	{
		return true;
	}
}
?>
