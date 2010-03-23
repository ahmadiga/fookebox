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

	public function addTrack(Track $track)
	{
		$artist = $track->artist;

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

	public function equals(Album $other)
	{
		return ($this->name == $other->name &&
			$this->disc == $other->disc);
	}

	public function getCover()
	{
		$album = preg_replace('/[\/:<>\?*|]/', '_', $this->name);
		$artist = preg_replace('/[\/:<>\?*|]/', '_', $this->artist);

		$path = sprintf("%s/%s-%s.jpg", album_cover_path,
			$artist, $album);

		if (!is_file($path))
		{
			// Can't find the album cover by artist name.
			// Is it a compilation?
			$path = sprintf("%s/%s-%s.jpg", album_cover_path,
				compliations_name, $this->name);

			if (!is_file($path))
			{
				return NULL;
			}
		}

		return $path;

	}

	private function sortTracks(Track $a, Track $b)
	{
		if ($a->track != $b->track)
		{
			return $a->track > $b->track;
		}
		$aName = sprintf("%s - %s", $a->artist, $a->title);
		$bName = sprintf("%s - %s", $b->artist, $b->title);

		return strcasecmp($aName, $bName);
	}
}
?>
