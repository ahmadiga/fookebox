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

require_once ('../config/config.inc.php');
require_once ('../config/status.conf.php');
require_once (src_path . '/Jukebox.inc.php');
require_once (src_path . '/Event.inc.php');

$jukebox = new Jukebox ();
$state = $jukebox->isActive ();
global $_PROGRAM;

$current = $_PROGRAM [CURRENT_EVENT];

$dateFormat = date('s') % 2 == 0 ? 'H:i' : 'H i';
$data = array (
	'time'		=>	date($dateFormat),
	'currentTitle'	=>	$current->getAsCurrent (),
	'currentState'	=>	$current->getState ()
);

$data ['hasNext'] = CURRENT_EVENT < count($_PROGRAM) - 1;

if ($data ['hasNext'])
{
	$next = $_PROGRAM [CURRENT_EVENT + 1];
	$data ['nextTitle'] = $next->getAsNext ();
	$data ['nextTime'] = $next->getTime ();
}
echo json_encode($data);

?>
