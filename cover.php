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
 * $Id: control.php 66 2009-05-06 01:21:44Z stefan@ott.net $
 */

require_once('config/config.inc.php');

if (!album_cover_path)
{
	header('HTTP/1.1 403 Forbidden');
	die('Permission denied');
}

function rb_clean($filename)
{
	// Substitute characters as explained on
	// http://www.rockbox.org/wiki/AlbumArt
	return preg_replace('/[\/:<>\?*|]/', '_', stripslashes($filename));
}

function show_file($filename)
{
	header('Content-type: image/jpeg');
	$file = fopen($filename, 'ro');
	while(!feof($file)) echo fgetc($file);
	fclose($file);
}

$artist = rb_clean($_GET['artist']);
$album = rb_clean($_GET['album']);

$path = sprintf("%s/%s-%s.jpg", album_cover_path, $artist, $album);

if (!is_file($path)) {
	header('HTTP/1.1 404 Not found');
	die('Not found');
}

show_file($path);
