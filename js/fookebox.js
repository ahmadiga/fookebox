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
 * $Id: RootPage.inc.php 402 2007-03-15 02:43:03Z stefan $
 */

// the currently open tab
var currentTab = 'artist';

// the message timeout
var messageTimeout;

function showProgressbar ()
{
	Effect.Appear ('progress', { 'duration' : '0.0' });
}

function hideProgressbar ()
{
	if (document.getElementById ('progress'))
		Effect.Fade ('progress', { 'duration' : '0.4' });
}

function prepareProgressbar ()
{
	var element = document.getElementById ('progress');
	var x = (self.screen.width / 2) - 60;
	element.style.left = x + "px";
}

function setTab (name)
{
	if (name == currentTab) return;

	showElement (name + 'List');
	hideElement (currentTab + 'List');

	document.getElementById (name + 'Tab').className = 'active';
	document.getElementById (currentTab + 'Tab').className = 'inactive';

	currentTab = name;
}

function updateStatus ()
{
	setTimeout ("updateStatus()", 1000);
	http_get ('status');
}

function setPlaylist (data)
{
	var div = document.getElementById ('playlist');
	var ols = div.getElementsByTagName ('ol');
	var ol = ols [0];
	var lis = ol.getElementsByTagName ('li');

	for (var i=0; i < lis.length; i++)
	{
		var li = lis [i];
		var item = data [i];
		if (item)
			li.innerHTML = item;
		else
			li.innerHTML = '<span class="freeSlot">-- empty --</span>';
	}
}

function apply_data (result)
{
	switch (result.target)
	{
		case 'status':
			var data = result.data;

			var artist = data.artist;
			var track = data.track;
			var timeTotal = data.timeTotal;
			if (artist != getContent ('artist')) setContent ('artist', artist);
			if (track != getContent ('track')) setContent ('track', track);
			if (timeTotal != getContent ('timeTotal')) setContent ('timeTotal', timeTotal);
			setContent ('timePassed', data.timePassed);
			if (data.queue) {
				setPlaylist (data.queue.splice (1));
			}
			break;
		case 'playlist':
			var data = result.data;
			setPlaylist (data.queue.splice (1));
			break;
		case 'queue':
			var data = result.data;
			setPlaylist (data.queue.splice (1));
			break;
		case 'searchResult':
			setContent ('searchResult', result.data);
			break;
		case 'statusPage':
			setContent ('clock', result.data.time);
			setContent ('currentTitle', result.data.currentTitle);
			setContent ('currentState', result.data.currentState);
			if (result.data.nextTitle) {
				showElement ('next');
				setContent ('nextTitle', result.data.nextTitle);
				setContent ('nextTime', result.data.nextTime);
			} else {
				hideElement ('next');
			}
			break;
		default:
			if (DEBUG) alert ('Unknown target: ' + result.target);
	}
	hideProgressbar ();
}

function process_message (message)
{
	switch (message)
	{
		case 'PLAYLIST_FULL':
			showMessage ('The playlist is full');
			break;
		case 'JUKEBOX_DISABLED':
			window.location = 'disabled';
			break;
		case 'JUKEBOX_ENABLED':
			window.location = '/';
			break;
		case 'SONG_QUEUED':
			updatePlaylist ();
			break;
		case 'SONG_REMOVED':
			updatePlaylist ();
			break;
		default:
			showMessage (message);
	}
}

function showMessage (message)
{
	if (messageTimeout)
	{
		clearTimeout(messageTimeout);
	}
	var element = document.getElementById ('messageText');
	element.innerHTML = message;
	messageTimeout = setTimeout ("fadeMessage()", 3000);
	Effect.Appear ('message', { 'duration' : '0.1' });
}

function fadeMessage ()
{
	var element = document.getElementById ('message');
	Effect.Fade ('message', { 'duration' : '0.4' });
}

function artistSearch (artist)
{
	showArtist (artist, 'search');
}

function artistFind (artist)
{
	showArtist (artist, 'find');
}

function showArtist (artist, command)
{
	showProgressbar ();
	var data = {
		'where' : 'artist',
		'what'	: encodeURIComponent (artist)
	}
	http_post (command, data.toJSONString ());
}

function genreSearch (genre)
{
	showGenre (genre, 'search');
}

function genreFind (genre)
{
	showGenre (genre, 'find');
}

function showGenre (genre, command)
{
	showProgressbar ();
	var data = {
		'where' : 'genre',
		'what'	: encodeURIComponent (genre)
	}
	http_post (command, data.toJSONString ());
}

function albumSearch (album)
{
	showAlbum (album, 'search');
}

function albumFind (album)
{
	showAlbum (album, 'find');
}

function showAlbum (album, command)
{
	var data = {
		'where' : 'album',
		'what'	: encodeURIComponent (album)
	}
	http_post (command, data.toJSONString ());
}

function removeTrack (id)
{
	var data = {
		'id' : encodeURIComponent (id)
	}
	http_post ('remove', data.toJSONString ());
}

function queueFile (file)
{
	var data = {
		'file'	: encodeURIComponent (file)
	}
	http_post ('queue', data.toJSONString ());
}

function refreshProgram ()
{
	setTimeout('refreshProgram()', 1000);
	http_get ('/program/status');
}

function updateDisabledJukebox ()
{
	setTimeout ('updateDisabledJukebox()', 1000);
	http_get ('disabled/status');
}

function search ()
{
	showProgressbar ();

	var form = document.forms ["searchform"];
	var searchType = form.elements ["searchType"].value;
	var searchTerm = form.elements ["searchTerm"].value;

	var data = {
		'where'	: searchType,
		'what'	: encodeURIComponent (searchTerm)
	}

	http_post ('search', data.toJSONString ());
}

function control(action)
{
	var data = {
		'action' : encodeURIComponent(action)
	}
	http_post ('control', data.toJSONString ());
}

function updatePlaylist ()
{
	http_get ('playlist');
}
