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

require_once('config/config.inc.php');

if (!album_cover_path)
{
	header('HTTP/1.1 403 Forbidden');
	die('Permission denied');
}

function show_file($filename)
{
	header('Content-type: image/jpeg');
	$file = fopen($filename, 'ro');
	while(!feof($file)) echo fgetc($file);
	fclose($file);
}

$artist = stripslashes($_GET['artist']);

if ($artist == 'current')
{
	$jukebox = new Jukebox();
	$current = $jukebox->getCurrentTrack();
	$album = new Album($current->artist, $current->albumName);
	$cover = $album->getCover();
}
else
{
	$albumName = stripslashes($_GET['album']);

	$album = new Album($artist, $albumName);
	$cover = $album->getCover();
}

if (!$cover)
{
	header('HTTP/1.1 404 Not found');
	die('Not found');
}

show_file($cover);
