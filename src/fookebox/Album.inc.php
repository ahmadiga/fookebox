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
require_once (src_path . '/mpd.inc.php');

class Album
{
	var $_artist;
	var $_name;
	var $_tracks;

	function Album ($artist, $name, $disc = '')
	{
		$this->_artist = $artist == '' ? 'Unknown Artist' : $artist;
		$this->_name = $name == '' ? 'Unknown Album' : $name;
		$this->_tracks = array ();
		$this->_disc = $disc;
	}

	function getName ()
	{
		return $this->_name;
	}

	function getArtist ()
	{
		return $this->_artist;
	}

	function addTrack ($track)
	{
		$artist = array_key_exists('Artist', $track) ?
			$track['Artist'] : 'Unknown Artist';

		if ($artist != $this->_artist && $artist != '')
		{
			if (strpos ($this->_artist, $artist) === 0)
			{
				$this->_artist = $artist;
			}
			else if (strpos ($artist, $this->_artist) === 0)
			{
			}
			else
			{
				$this->_artist = 'Various Artists';
			}
		}
		$this->_tracks [] = $track;
	}

	function sortTracks ($a, $b)
	{
		if ($a ['Track'] && $b ['Track'] && $a ['Album'])
		{
			return $a ['Track'] > $b ['Track'];
		}
		$aName = $a ['Artist'] . ' - ' . $a ['Title'];
		$bName = $b ['Artist'] . ' - ' . $b ['Title'];

		return strcasecmp ($aName, $bName);
	}

	function getTracks ()
	{
		usort ($this->_tracks, array ('Album', 'sortTracks'));
		return $this->_tracks;
	}

	function getDisc ()
	{
		return $this->_disc;
	}

	function equals ($other)
	{
		return ($this->_name == $other->getName () &&
			$this->_disc == $other->getDisc ());
	}
}
?>
