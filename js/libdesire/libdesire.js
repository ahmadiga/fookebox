/*
 * libdesire 0.1 RC-2+fkb
 * Copyright (C) 2006/2007 Stefan Ott. All rights reserved.
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
 * $Id: libdesire.js 394 2007-03-14 15:43:28Z stefan $
 */

// The element which is supposed to get focus
var scheduledFocus = '';

// Whether to use DEBUG mode or not
var DEBUG = false;

// Connection-pool
var cons = new Array ();

// ------------------- data transport -------------------

function handleResponse (con)
{
	if (con.http.readyState == 4)
	{
		var result = con.http.responseText.parseJSON ();
		process_result (result);
		con.ready = true;
	}
}

function process_result (result)
{
	switch (result.type)
	{
		case 'data':
			apply_data (result);
			break;
		case 'window':
			window.open (result.url);
			break;
		case 'multi':
			for (var i=0; i < result.items.length; i++) {
				process_result (result.items [i]);
			}
			break;
		case 'error':
			if (DEBUG) alert (result.error);
			break;
		case 'message':
			process_message (result.message);
			break;
		case undefined:
			if (DEBUG) alert ('Invalid response: ' + result);
			break;
		default:
			if (DEBUG) alert ('Invalid type: ' + result.type);
	}
}

function requestObject ()
{
	this.ready = true;

	try
	{
		this.http = new ActiveXObject ("Msxml2.XMLHTTP");
	}
	catch (e)
	{
		try
		{
			this.http = new ActiveXObject ("Microsoft.XMLHTTP");
		}
		catch (e)
		{
			this.http = new XMLHttpRequest();
		}
	}
}

function getConnection ()
{
	var con = null;

	for (var i=0; i < cons.length; i++)
	{
		var candidate = cons [i];
		if (candidate.ready)
		{
			con = candidate;
			break;
		}
	}
	if (con == null)
	{
		con = new requestObject();
		con.num = i;
		cons [cons.length] = con;
	}
	con.ready = false;
	return con;
}

function http_post (url, data)
{
	var con = getConnection ();
	con.http.open ('post', url + '?ms=' + new Date ().getTime (), true);
	con.http.setRequestHeader ('Content-Type', 'application/x-www-form-urlencoded;');
	con.http.onreadystatechange = function () {
		handleResponse (con);
	}
	con.http.send (data);
}

function http_get (url, additional)
{
	if (!additional)
	{
		additional = ''
	}
	var con = getConnection ();
	con.http.open ('get', url + '?ms=' + new Date ().getTime () + additional, true);
	con.http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;');
	con.http.onreadystatechange = function ()
	{
		handleResponse (con);
	}
	con.http.send (null);
}

// ------------------- content manipulation -------------------

function apply_focus ()
{
	if (scheduledFocus != '')
	{
		var element = document.getElementById (scheduledFocus);
		if (!element && DEBUG) alert ('Could not set focus - element '
					+ scheduledFocus + ' not found');

		// This is a firefox workaround. See
		// https://bugzilla.mozilla.org/show_bug.cgi?id=236791
		element.setAttribute ('autocomplete','off');

		element.focus ();
		scheduledFocus = '';
	}
}


function showElement (elementID)
{
	var element = document.getElementById (elementID);
	element.style.display = '';
}

function hideElement (elementID)
{
	var element = document.getElementById (elementID);
	element.style.display = 'none';
}

function clear (elementID)
{
	var element = document.getElementById (elementID);	
	element.innerHTML = '';
}

function enable (elementID)
{
	var element = document.getElementById (elementID);
	element.disabled = false;
}

function disable (elementID)
{
	var element = document.getElementById (elementID);
	element.disabled = true;
}

function select (elementID)
{
	var element = document.getElementById (elementID);
	element.selected = true;
}

function setContent (elementID, content)
{
	var element = document.getElementById (elementID);
	element.innerHTML = content;
}

function getContent (elementID)
{
	var element = document.getElementById (elementID);
	if (!element) return '';
	return element.innerHTML;
}

function setValue (elementID, content)
{
	var element = document.getElementById (elementID);
	element.value = content;
}

function getValue (elementID)
{
	var element = document.getElementById (elementID);
	if (!element) return '';
	return element.value;
}

function isEmpty (value)
{
	var expr = /^[ \n]*$/
	return expr.exec (value) != null;
}

function save (elementID)
{
	var element = document.getElementById (elementID);
	element.prevHTML = element.innerHTML;
}

function restore (elementID)
{
	var element = document.getElementById (elementID);
	element.innerHTML = element.prevHTML;
}

// ------------------- hooks -------------------

/*
function process_message (message);
function apply_data (result);
*/
