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

require_once (realpath (dirname (__FILE__) . '/../../config/config.inc.php'));

class Event
{
	var $_name;
	var $_time;

	function Event ($time, $name)
	{
		$this->_time = $time;
		$this->_name = $name;
	}

	function getName ()
	{
		return $this->_name;
	}

	function getTime ()
	{
		return $this->_time;
	}

	function getAsCurrent ()
	{
		return $this->_name;
	}

	function getAsNext ()
	{
		return $this->getAsCurrent ();
	}

	function getState ()
	{
		return '@' . site_name;
	}

	function isJukebox ()
	{
		return false;
	}
}
?>
