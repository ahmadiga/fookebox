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

// the currently open tab
var currentTab = 'artist';

// the message timeout
var messageTimeout;

// when the playlist was last updated
var lastPlaylistUpdate = 0;

// length of our currently known queue
var queueLength = 0;

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
	http_get ('status', '&updated=' + lastPlaylistUpdate + '&qlen=' +
								queueLength);
}

function setPlaylist (data)
{
	queueLength = data.length;
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
			if (artist != getContent ('artist'))
				setContent ('artist', artist);
			if (track != getContent ('track'))
				setContent ('track', track);
			if (timeTotal != getContent ('timeTotal'))
				setContent ('timeTotal', timeTotal);
			setContent ('timePassed', data.timePassed);
			break;
		case 'playlist':
			var data = result.data;
			setPlaylist (data.queue.splice (1));
			lastPlaylistUpdate = data.updated;
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
			window.location = base_url;
			break;
		case 'SONG_QUEUED':
			updatePlaylist ();
			break;
		case 'SONG_CHANGED':
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
		'what'	: artist
	}

	http_post (command, data);
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
		'what'	: genre
	}
	http_post (command, data);
}

function albumSearch (album, artist)
{
	showAlbum (album, 'search', artist);
}

function albumFind (album, artist)
{
	showAlbum (album, 'find', artist);
}

function showAlbum (album, command, artist)
{
	var data = {
		'where' : 'album',
		'what'	: album,
		'artist': artist
	}
	http_post (command, data);
}

function removeTrack (id)
{
	var data = {
		'id' : id
	}
	http_post ('remove', data);
}

function queueFile (file)
{
	var data = {
		'file'	: file
	}
	http_post ('queue', data);
}

function refreshProgram ()
{
	setTimeout ('refreshProgram()', 1000);
	http_get (base_url + '/program/status');
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
		'what'	: searchTerm
	}

	http_post ('search', data);
}

function control(action)
{
	var data = {
		'action' : action
	}
	http_post ('control', data);
}

function updatePlaylist ()
{
	http_get ('playlist');
}
