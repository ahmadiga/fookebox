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
	require_once (src_path . '/Jukebox.inc.php');
	require_once (src_path . '/RootPage.inc.php');
	require_once (libdesire_path . 'view/Page.inc.php');
	require_once (libdesire_path . 'util/io.inc.php');

	$jukebox = new Jukebox ();
	if (!$jukebox->isActive ())
	{
		json_msg ('JUKEBOX_DISABLED');
		die ();
	}

	$refreshQueue = false;

	$mpd = new mpd (mpd_host, mpd_port, mpd_pass);

	$page = new Page ();
	$page->assign ('mpd', $mpd);

	$status = $mpd->getStatus ();

	while ($status ['song'] > 0) {
		// Some songs finished playing but are still in the playlist
		$mpd->PLRemove(0);
		$status = $mpd->getStatus ();
		$refreshQueue = true;
	}
	if (($status ['song'] !== '0') && ($mpd->playlist_count > 0)) {
		// We ran out of songs - clear the playlist
		$mpd->PLRemove(0);
		$refreshQueue = true;
	}

	$playlist = $mpd->getPlaylist ();

	$current = $playlist [$status ['song']];
	$time = $status ['time'];

	list ($timePassed, $timeTotal) = split (':', $time);

	if (empty ($timePassed))
			$timePassed = 0;

	$data = array (
		'artist'	=> $current ['Artist'],
		'track'		=> $current ['Title'],
		'timePassed'	=> date ('i:s', $timePassed),
		'timeTotal'	=> date ('i:s', $timeTotal),
	);

	if ($refreshQueue) {
		$pldata = array ();
		// TODO: this is copied from playlist.php
		foreach ($playlist as $item)
		{
			$page = new Page ();
			$page->assign ('artist', $item ['Artist']);
			$page->assign ('title', $item ['Title']);
			$page->assign ('position', $item ['Pos']);
			$pldata[] = $page->fetch ('playlist-entry.tpl');
		}

		$data ['queue'] = $pldata;
	}

	json_data ('status', $data);
?>
