<?php
/*
 * fookebox
 * Copyright (C) 2008 Stefan Ott. All rights reserved.
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
 * $Id: site.conf.php-dist 6 2008-06-12 00:05:43Z stefan@ott.net $
 */

$defaults = array
(
	'mpd_host'		=> 'localhost',
	'mpd_port'		=> 6600,
	'mpd_pass'		=> NULL,
	'site_name'		=> 'fookebox',
	'skin'			=> 'default',
	'auto_queue'		=> false,
	'max_queue_length'	=> 3,
	'show_search_tab'	=> true,
	'enable_controls'	=> false,
	'enable_song_removal'	=> false,
	'enable_queue_album'	=> false,
	'find_over_search'	=> false
);

foreach ($defaults as $key => $value)
{
	if (!defined ($key))
	{
		define ($key, $value);
	}
}
?>