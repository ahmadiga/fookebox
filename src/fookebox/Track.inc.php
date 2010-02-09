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
 * $Id: Album.inc.php 109 2010-02-05 02:18:00Z stefan@ott.net $
 */

class Track
{
	public $artist;
	public $track;
	public $title;
	public $file;
	public $disc;
	public $pos;
	public $albumName;

	public function __construct(array $data)
	{
		$this->artist = 'Unknown Artist';
		$this->track = 0;
		$this->pos = 0;
		$this->title = '';

		$this->file = $data['file'];

		if (array_key_exists('Artist', $data))
			$this->artist = $data['Artist'];
		if (array_key_exists('Track', $data))
			$this->track = $data['Track'];
		if (array_key_exists('Title', $data))
			$this->title = $data['Title'];
		if (array_key_exists('Disc', $data))
			$this->disc = $data['Disc'];
		if (array_key_exists('Pos', $data))
			$this->pos = $data['Pos'];
		if (array_key_exists('Album', $data))
			$this->albumName = $data['Album'];
	}
}
