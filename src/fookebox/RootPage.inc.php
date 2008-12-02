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

require_once (realpath (dirname (__FILE__) . '/../../config/config.inc.php'));
require_once (smarty_src_path . '/Smarty.class.php');
require_once (libdesire_path . 'view/Page.inc.php');
require_once (libdesire_path . 'util/browser.inc.php');

class RootPage extends Page
{
	function RootPage ($title = site_name)
	{
		parent::Page ();
		$this->assign ('title', $title);
		$this->assign ('version', planster_version);
		$this->assign ('base_url', base_url);
		$this->assign ('css_url', base_url . 'style.css');
		$browser = browser_detection ('full');
		if ($browser [0] == 'ie')
		{
			$this->assign ('ie', true);
		}
		$this->_id = -1;
	}

	function setTitle ($title)
	{
		$this->assign ('title', $title);
	}

	function setID ($id)
	{
		$this->assign ('id', $id);
	}

	function display()
	{
		parent::display ('root.tpl');
	}
}
?>
