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
require_once (src_path . '/Album.inc.php');
require_once (src_path . '/RootPage.inc.php');
require_once (libdesire_path . 'view/Page.inc.php');
require_once (libdesire_path . 'util/io.inc.php');
require_once (libdesire_path . 'util/util.inc.php');

function compare_artists_and_albums ($a, $b)
{
	if ($a->getName () == '') return 1;
	if ($b->getName () == '') return -1;
	$aName = $a->getArtist () . " - " . $a->getName ();
	$bName = $b->getArtist () . " - " . $b->getName ();
	return strcasecmp ($aName, $bName);
}

function compare_albums ($a, $b)
{
	if ($a->getName () == '') return 1;
	if ($b->getName () == '') return -1;
	return strcasecmp ($a->getName (), $b->getName ());
}

$mpd = new mpd (mpd_host, mpd_port, mpd_pass);
$data = json_get_post ();

$where = require_attribute ('where', $data);
$what = require_attribute ('what', $data);
$searchArtist = try_attribute ('artist', $data);

// force_in_array ($where, array (MPD_SEARCH_GENRE, MPD_SEARCH_ARTIST,
// 							MPD_SEARCH_ALBUM));
// TODO: re-activate this, check that search still works

$albums = array ();

foreach ($mpd->Search ($where, $what) as $item)
{
	$album = NULL;
	$found = false;

	$albumName = $item ['Album'];
	$artist = $item ['Artist'];

	for ($i=0; $i < count ($albums); $i++)
	{
		if ($albums [$i]->equals (new Album ($artist, $albumName)))
		{
			$albums [$i]->addTrack ($item);
			$found = true;
		}
	}
	if (!$found) {
		$album = new Album ($artist, $albumName);
		$album->addTrack ($item);
		$albums [] = $album;
	}
}

if ($where == 'genre')
{
	usort ($albums, 'compare_artists_and_albums');
}
else
{
	usort ($albums, 'compare_albums');
}

$root = new RootPage ();
$page = new Page ();
$page->assign ('where', $where);
$page->assign ('what', $what);
$page->assign ('albums', $albums);
if ($where == 'album')
	$page->assign('searchArtist', $searchArtist);
json_data ('searchResult', $page->fetch ('search.tpl'));
?>
