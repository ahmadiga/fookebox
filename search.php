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

function compare_artists_and_albums(Album $a, Album $b)
{
	if ($a->getName() == '') return 1;
	if ($b->getName() == '') return -1;
	$aName = sprintf("%s - %s %s", $a->getArtist(), $a->getName(),
		$a->getDisc());
	$bName = sprintf("%s - %s %s", $b->getArtist(), $b->getName(),
		$b->getDisc());
	return strcasecmp ($aName, $bName);
}

function compare_albums(Album $a, Album $b)
{
	if ($a->getName() == '') return 1;
	if ($b->getName() == '') return -1;
	$aDisc = sprintf("%s %s", $a->getName(), $a->getDisc());
	$bDisc = sprintf("%s %s", $b->getName(), $b->getDisc());
	return strcasecmp($aDisc, $bDisc);
}

$data = json_decode(file_get_contents("php://input"));

if (!$data)
{
	header('HTTP/1.1 400 Bad Request');
	die('Bad Request');
}

if (!array_key_exists('where', $data) || !array_key_exists('what', $data))
{
	header('HTTP/1.1 400 Bad Request');
	die('Bad Request');
}

if (!property_exists($data, 'forceSearch'))
	$data->forceSearch = false;

$where = $data->where;
$what = $data->what;
$forceSearch = $data->forceSearch;

$jukebox = new Jukebox();
$result = $jukebox->search($where, $what, find_over_search && !$forceSearch);

$albums = array();
foreach ($result as $item)
{
	$track = new Track($item);

	$albumHash = $track->albumName . 'disc' . $track->disc;

	if (!array_key_exists($albumHash, $albums))
	{
		$albums[$albumHash] = new Album($track->artist,
			$track->albumName, $track->disc);
	}

	$album = $albums[$albumHash];
	$album->addTrack($track);
}

$func = $where == 'genre' ? 'compare_artists_and_albums' : 'compare_albums';
usort($albums, $func);

$root = new RootPage();
$page = new Page();
$page->assign('where', $where);
$page->assign('what', $what);
$page->assign('albums', $albums);

echo($page->fetch('search.tpl'));
?>
