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

$dir = dirname (__FILE__);
require_once ($dir . '/general.conf.php');

if (is_file ($dir . '/site.conf.php'))
{
	require_once ($dir . '/site.conf.php');
	require_once ($dir . '/defaults.inc.php');
	define ('smarty_template_dir', $base . '/skins/' . skin .'/templates');
	require_once ($dir . '/urls.conf.php');
}
else
{
	die ('Please rename config/site.conf.php-dist to site.conf.php and fill it with your site configuration.');
}

?>
