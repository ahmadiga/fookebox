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

	require_once ('config/config.inc.php');
	require_once (src_path . '/mpd.inc.php');
	require_once (src_path . '/Album.inc.php');
	require_once (src_path . '/RootPage.inc.php');
	require_once (libdesire_path . 'view/Page.inc.php');
	require_once (libdesire_path . 'util/io.inc.php');
	require_once (libdesire_path . 'util/util.inc.php');

	$mpd = new mpd (mpd_host, mpd_port, mpd_pass);
	$data = json_get_post ();

	$file = require_attribute ('file', $data);
	$pl = $mpd->getPlayList ();

	if (count ($pl) >= max_queue_length)
	{
		json_msg ('PLAYLIST_FULL');
		die ();
	}
	$mpd->PLAdd ($file);
	$mpd->Play ();

	json_msg ('SONG_QUEUED');
?>