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

require_once ('../config/config.inc.php');
require_once ('../config/status.conf.php');
require_once (src_path . '/Event.inc.php');
require_once (src_path . '/RootPage.inc.php');
require_once (src_path . '/Page.inc.php');

global $_PROGRAM;

$root = new RootPage ('foobar Program');
$page = new Page ();
$page->assign ('current', $_PROGRAM [CURRENT_EVENT]);

if (CURRENT_EVENT < count($_PROGRAM) - 1)
{
	$page->assign ('next', $_PROGRAM [CURRENT_EVENT + 1]);
}

$root->assign ('body', $page->fetch ('program.tpl'));
$root->assign ('hideHeader', true);
$root->display ();

?>
