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
	$base = realpath (dirname (__FILE__) . '/../');

	define ('adodb_path', '/usr/share/adodb');
	define ('src_path', $base . '/src/fookebox');

	define ('smarty_src_path', $base . '/src/smarty');
	define ('smarty_compile_dir', $base . '/skins/compiled');

	define ('VERSION', '0.1.0+svn');

	// we don't use a database, thus ignore the db-related fields
	define ('libdesire_ignore', 'dbDriver:dbUser:dbPass:dbHost:dbName');
?>