<?php
/*
 * libdesire little helpers
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
 * $Id: util.inc.php 53 2006-11-07 00:48:10Z stefan $
 */

require_once (dirname (__FILE__) . '/../config/libdesire.inc.php');

function require_key ($key, $array)
{
	if (array_key_exists ($key, $array))
	{
		return $array [$key];
	}
	else
	{
		header ('location: ' . base_url);
		die ();
	}
}

function try_key ($key, $array)
{
	if (array_key_exists ($key, $array))
	{
		return $array [$key];
	}
	else
	{
		return NULL;
	}
}

function require_attribute ($attribute, $object)
{
	if (isset ($object->$attribute))
	{
		return $object->$attribute;
	}
	else
	{
		header ('location: ' . base_url);
		die ();
	}
}

function try_attribute ($attribute, $object)
{
	if (isset ($object->$attribute))
	{
		return $object->$attribute;
	}
	else
	{
		return NULL;
	}
}

function force_in_array ($item, $array)
{
	if (!in_array ($item, $array))
	{
		header ('location: ' . base_url);
		die ();
	}
}
