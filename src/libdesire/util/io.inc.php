<?php
/*
 * libdesire i/o subsystem
 * Copyright (C) 2006-2008 Stefan Ott. All rights reserved.
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
 * $Id: io.inc.php 97 2006-11-13 04:03:08Z stefan $
 */

require_once (dirname (__FILE__) . '/../config/libdesire.inc.php');
require_once (libdesire_path . 'util/util.inc.php');

function ld_json_encode($msg)
{
	if (version_compare(PHP_VERSION, "5.2.0", "ge"))
	{
		return json_encode ($msg);
	}
	else
	{
		require_once (libdesire_path . 'json/JSON.php');
		$json = new Services_JSON ();
		return $json->encode (json_msg_array ($msg));
	}
}

function ld_json_decode($msg)
{
	if (version_compare(PHP_VERSION, "5.2.0", "ge"))
	{
		return json_decode ($msg);
	}
	else
	{
		require_once (libdesire_path . 'json/JSON.php');
		$json = new Services_JSON ();
		return $json->decode (json_msg_array ($msg));
	}
}

function json_msg ($msg)
{
	echo ld_json_encode (json_msg_array ($msg));
}

function json_window ($url)
{
	echo ld_json_encode (array (
		'type'	=> 'window',
		'url'	=> $url
	));
}

function json_msg_array ($msg)
{
	return array (
		'type'		=> 'message',
		'message'	=> $msg
	);
}

function json_multi ($items)
{
	echo ld_json_encode (array (
		'type'	=> 'multi',
		'items'	=> $items
	));
}

function json_data_array ($target, $data, $extra = array ())
{
	$data = array (
		'type'	=> 'data',
		'target'=> $target,
		'data'	=> $data
	);

	foreach ($extra as $key => $value)
	{
		$data [$key] = $value;
	}

	return $data;
}

function json_data ($target, $data, $extra = array ())
{
	echo ld_json_encode (json_data_array ($target, $data, $extra));
}

function _json_decode_array ($array)
{
	foreach ($array as $key => $item)
	{
		if (is_array ($item))
		{
			_json_decode_array (&$item);
		}
		else
		{
			$array [$key] = rawurldecode ($item);
		}
	}
}

function json_get_post ()
{
	$postData = stripslashes($_POST['data']);
	$jsonData = ld_json_decode ($postData);

	foreach (get_object_vars ($jsonData) as $key => $item)
	{
		if (is_array ($item))
		{
			// TODO: probably broken - fix
//			_json_decode_array (&$item);
		}
		else
		{

			$jsonData->$key = rawurldecode ($item);
		}
	}

	return $jsonData;
}

function has_json_post_data ()
{
	if ($data = try_key ('data', $_POST))
	{
		return preg_match ('/^{.*}$/', $data);
	}
	return false;
}

function json_handle_errors ()
{
	set_error_handler ('json_error_handler');
}

function json_error_handler ($errno, $errstr, $errfile, $errline)
{
	json_msg ("ERROR: $errstr at $errfile:$errline"); die();
}
