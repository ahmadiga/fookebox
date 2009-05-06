<?php
/*
 * fookebox
 * Copyright (C) 2007-2009 Stefan Ott. All rights reserved.
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

require_once ('config/config.inc.php');
require_once (src_path . '/mpd.inc.php');

if (!enable_controls)
{
	header('HTTP/1.1 403 Forbidden');
	die('Permission denied');
}

$mpd = new mpd(mpd_host, mpd_port, mpd_pass);

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

switch ($data->action)
{
	case 'play': 		$mpd->Play();
				break;
	case 'pause': 		$mpd->Pause();
				break;
	case 'prev': 		$mpd->Previous();
				break;
	case 'next': 		$mpd->Next();
				break;
	case 'stop': 		$mpd->Stop();
				break;
	case 'voldown': 	$mpd->AdjustVolume(-5);
				break;
	case 'volup': 		$mpd->AdjustVolume(5);
				break;
	case 'rebuild': 	$mpd->DBRefresh();
				break;
}
?>
