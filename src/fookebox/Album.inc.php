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

class Album
{
	private $artist;
	private $name;
	private $tracks;
	private $disc;

	public function __construct($artist, $name, $disc = '')
	{
		$this->artist = $artist == '' ? 'Unknown Artist' : $artist;
		$this->name = $name == '' ? 'Unknown Album' : $name;
		$this->tracks = array();
		$this->disc = $disc;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getArtist()
	{
		return $this->artist;
	}

	public function addTrack($track)
	{
		$artist = array_key_exists('Artist', $track) ?
			$track['Artist'] : 'Unknown Artist';

		if ($artist != $this->artist && $artist != '')
		{
			if (strpos($this->artist, $artist) === 0)
			{
				$this->artist = $artist;
			}
			else if (strpos($artist, $this->artist) !== 0)
			{
				$this->artist = 'Various Artists';
			}
		}
		$this->tracks[] = $track;
	}

	public function getTracks()
	{
		usort($this->tracks, array('Album', 'sortTracks'));
		return $this->tracks;
	}

	public function getDisc()
	{
		return $this->disc;
	}

	public function equals($other)
	{
		return ($this->name == $other->name &&
			$this->disc == $other->disc);
	}

	private function sortTracks($a, $b)
	{
		if ($a['Track'] && $b['Track'] && $a['Album'])
		{
			return $a['Track'] > $b['Track'];
		}
		$aName = sprintf("%s - %s", $a['Artist'], $a['Title']);
		$bName = sprintf("%s - %s", $b['Artist'], $b['Title']);

		return strcasecmp($aName, $bName);
	}
}
?>
