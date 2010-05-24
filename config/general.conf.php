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

$base = realpath (dirname (__FILE__) . '/../');

define ('adodb_path', '/usr/share/adodb');
define ('src_path', $base . '/src/fookebox');

define ('smarty_src_path', $base . '/src/smarty');
define ('smarty_compile_dir', $base . '/skins/compiled');

define ('VERSION', '0.4.2');
?>
