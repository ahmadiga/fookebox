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

global $_PROGRAM;

require_once(realpath(dirname(__FILE__) . '/../../config/status.conf.php'));

class Jukebox
{
	private static function getProgram()
	{
		global $_PROGRAM;
		return $_PROGRAM;
	}

	private static function getCurrentEventId()
	{
		return CURRENT_EVENT;
	}

	public static function getEvent()
	{
		$program = Jukebox::getProgram();
		$eventid = Jukebox::getCurrentEventId();
		return $program[$eventid];
	}

	public static function hasNextEvent()
	{
		$program = Jukebox::getProgram();
		$eventid = Jukebox::getCurrentEventId();
		return $eventid < count($program) - 1;
	}

	public static function getNextEvent()
	{
		$program = Jukebox::getProgram();
		$eventid = Jukebox::getCurrentEventId();
		return $program[$eventid + 1];
	}

	public static function isActive()
	{
		$current = Jukebox::getEvent();
		return $current->isJukebox();
	}
}
?>
