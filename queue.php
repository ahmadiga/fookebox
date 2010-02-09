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

$data = json_decode(file_get_contents("php://input"));

if (!$data)
{
	header('HTTP/1.1 400 Bad Request');
	die('Bad Request');
}

if (!array_key_exists('file', $data))
{
	header('HTTP/1.1 400 Bad Request');
	die('Bad Request');
}

$file = $data->file;

$jukebox = new Jukebox();
$pl = $jukebox->getPlayList();

if (count($pl) >= max_queue_length)
{
	header("HTTP/1.1 409 Conflict");
	die('The playlist is full');
}

$jukebox->queue($file);
?>
