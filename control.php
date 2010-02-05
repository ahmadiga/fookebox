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

require_once('config/config.inc.php');

if (!enable_controls)
{
	header('HTTP/1.1 403 Forbidden');
	die('Permission denied');
}

$data = json_decode(file_get_contents("php://input"));

if (!$data)
{
	header('HTTP/1.1 400 Bad Request');
	die('Bad Request');
}

if (!array_key_exists('action', $data))
{
	header('HTTP/1.1 400 Bad Request');
	die('Bad Request');
}

$jukebox = new Jukebox();

switch ($data->action)
{
	case 'play': 		$jukebox->play();
				break;
	case 'pause': 		$jukebox->Pause();
				break;
	case 'prev': 		$jukebox->previous();
				break;
	case 'next': 		$jukebox->next();
				break;
	case 'stop': 		$jukebox->stop();
				break;
	case 'voldown': 	$jukebox->volumeDown();
				break;
	case 'volup':	 	$jukebox->volumeUp();
				break;
	case 'rebuild': 	$jukebox->refreshDB();
				break;
}
?>
