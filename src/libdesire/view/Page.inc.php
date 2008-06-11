<?php
/*
 * libdesire smarty interface
 * Copyright (C) 2006 Stefan Ott. All rights reserved.
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
 * $Id: DBItem.inc.php 55 2006-11-07 01:05:26Z stefan $
 */

require_once (dirname (__FILE__) . '/../config/libdesire.inc.php');
require_once (smarty_src_path . '/Smarty.class.php');

class Page extends Smarty
{
	function Page ()
	{
		parent::Smarty ();
		$this->template_dir = smarty_template_dir;
		$this->compile_dir = smarty_compile_dir;
	}
}
?>
