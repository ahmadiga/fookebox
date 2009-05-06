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

// length of our currently known queue
var queueLength = -1;

// the currently known url
var currentURL = '';

function ajax_get(url, onsucces) {
	var time = new Date().getTime();

	new Ajax.Request(url + '?ms=' + time,
	{
		method: 'get',
		onSuccess: onsucces,
		onFailure: function(transport) {
			showMessage ('Something bad happened');
		}
	});
}

function ajax_post(url, data, onsucces) {
	var time = new Date().getTime();

	new Ajax.Request(url + '?ms=' + time,
	{
		method: 'post',
		postBody: data.toJSON(),
		onSuccess: onsucces,
		onFailure: function(transport) {
			var response = transport.responseText;

			if (response)
				showMessage(response);
			else
				showMessage('Something bad happened');

		}
	});
}

function applyURL(url)
{
	currentURL = url;

	if (url.indexOf("#") > -1)
	{
		var parts = url.split('#');
		var params = parts[1].split('=');
		var key = params[0];
		var value = params[1];

		if (key == 'artist')
			artistSearch(value);
		else if (key == 'genre')
			genreSearch(value);
		else if (key == 'tab')
			setTab(value);
	}
}

function parseLocation()
{
	var url = window.location.href;
	applyURL(url);
	setTimeout ("updateURL()", 200);
}

function updateURL()
{
	var url = window.location.href;
	//alert(url);

	if (url != currentURL)
		applyURL(url);

	setTimeout ("updateURL()", 200);
}

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

	$(name + 'List').show();
	$(currentTab + 'List').hide();

	document.getElementById (name + 'Tab').className = 'active';
	document.getElementById (currentTab + 'Tab').className = 'inactive';

	currentTab = name;

	window.location = "#tab=" + name;
	currentURL = window.location.href;
}

function updateStatus ()
{
	setTimeout ("updateStatus()", 1000);

	ajax_get('status', function(transport)
	{
			var response = transport.responseText;
			var data = response.evalJSON();
			var jukebox = data.jukebox;

			if (!jukebox)
			{
				window.location = 'disabled';
				return
			}

			var artist = data.artist;
			var track = data.track;
			var timeTotal = data.timeTotal;
			var serverQueue = data.queueLength;

			if (artist != $('artist').innerHTML)
				$('artist').innerHTML = artist;
			if (track != $('track').innerHTML)
				$('track').innerHTML = track;
			if (timeTotal != $('timeTotal').innerHTML)
				$('timeTotal').innerHTML = timeTotal;
			$('timePassed').innerHTML = data.timePassed;

			if (serverQueue != queueLength)
				updatePlaylist();
	});
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
	// TODO: remove function
	showArtist(artist);
}

function genreSearch (genre)
{
	// TODO: remove function
	showGenre(genre);
}

function showArtist(artist)
{
	showProgressbar ();

	var data = new Hash();
	data.set('where', 'artist');
	data.set('what', artist);

	window.location = "#artist=" + artist;
	currentURL = window.location.href;

	ajax_post('search', data, function(transport)
	{
		var response = transport.responseText;
		$('searchResult').innerHTML = response;
		hideProgressbar();
	});
}

function showGenre(genre)
{
	showProgressbar ();

	var data = new Hash();
	data.set('where', 'genre');
	data.set('what', genre);

	window.location = "#genre=" + genre;

	ajax_post('search', data, function(transport)
	{
		var response = transport.responseText;
		$('searchResult').innerHTML = response;
		hideProgressbar();
	});
}

function search ()
{
	showProgressbar ();

	var form = document.forms["searchform"];
	var searchType = form.elements["searchType"].value;
	var searchTerm = form.elements["searchTerm"].value;

	var data = new Hash();
	data.set('where', searchType);
	data.set('what', searchTerm);

	ajax_post('search', data, function(transport)
	{
		var response = transport.responseText;
		$('searchResult').innerHTML = response;
		hideProgressbar();
	});
}

function removeTrack (id)
{
	var data = new Hash()
	data.set('id', id);

	ajax_post('remove', data, function(transport)
	{
		updatePlaylist();
	});
}

function queueFile (file)
{
	var data = new Hash();
	data.set('file', file);

	ajax_post('queue', data, function(transport)
	{
		updatePlaylist();
	});
}

function refreshProgram()
{
	setTimeout ('refreshProgram()', 1000);
	ajax_get(base_url + '/program/status', function(transport)
	{
		var response = transport.responseText;
		var content = response.evalJSON();
		var data = content.data;

		$('clock').innerHTML = data.time;
		$('currentTitle').innerHTML = data.currentTitle;
		$('currentState').innerHTML = data.currentState;

		if (data.nextTitle) {
			$('next').show();
			$('nextTitle').innerHTML = data.nextTitle;
			$('nextTime').innerHTML = data.nextTime;
		} else {
			$('next').hide();
		}
	});
}

function updateDisabledJukebox ()
{
	setTimeout ('updateDisabledJukebox()', 1000);

	ajax_get('status', function(transport)
	{
		var response = transport.responseText;
		var data = response.evalJSON();
		var jukebox = data.jukebox;

		if (jukebox)
			window.location = base_url
	});
}

function control(action)
{
	var data = new Hash()
	data.set('action', action);

	ajax_post('control', data, function(transport)
	{
		var response = transport.responseText;
		var content = response.evalJSON();
		process_message(content.message);
	});
}

function updatePlaylist ()
{
	var time = new Date().getTime();

	ajax_get('playlist', function(transport)
	{
		var response = transport.responseText;
		var data = response.evalJSON();

		setPlaylist(data.queue.splice (1));
	});
}
