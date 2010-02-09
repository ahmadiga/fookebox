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

function index_handle_error($errno, $errstr, $errfile, $errline)
{
	header("HTTP/1.1 500 Internal Server Error");
	echo "<h1>Error: Could not connect to mpd</h1>";
	$error = error_get_last();
	printf("line %s of %s: %s", $errline, $errfile, $errstr);
	die();
}

set_error_handler('index_handle_error');
$jukebox = new Jukebox();
restore_error_handler();

$root = new RootPage();
$page = new Page();
$page->assign('jukebox', $jukebox);

$root->assign('body', $page->fetch ('client.tpl'));
$root->display();
?>
