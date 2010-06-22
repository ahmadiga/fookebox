/*
 * fookebox 0.5.0-PRE
 * Copyright (C) 2007-2010 Stefan Ott. All rights reserved.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * $Id: fookebox.js 135 2010-06-16 01:38:35Z stefan@ott.net $
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
		requestHeaders: { 'Accept': 'application/json' },
		onSuccess: onsucces,
		onFailure: function(transport) {
			showErrorMessage(transport);
		}
	});
}

function ajax_post(url, data, onsucces) {
	var time = new Date().getTime();

	new Ajax.Request(url + '?ms=' + time,
	{
		method: 'post',
		requestHeaders: { 'Accept': 'application/json' },
		postBody: data.toJSON(),
		contentType: 'application/json',
		onSuccess: onsucces,
		onFailure: function(transport) {
			showErrorMessage(transport);
		}
	});
}

function applyURL(url)
{
	url = unescape(url);
	currentURL = url;

	if (url.indexOf("#") > -1)
	{
		var parts = url.split('#');
		var params = parts[1].split('=');
		var key = params[0];
		//var value = params[1];
		var value = parts[1].substring(key.length + 1);

		if (key == 'artist')
			showArtist(value);
		else if (key == 'genre')
			showGenre(value);
		else if (key == 'tab')
			setTab(value);
	}
}

function parseLocation()
{
	var url = window.location.href;
	applyURL(url);
	setTimeout("updateURL()", 400);
}

function updateURL()
{
	var url = window.location.href;

	if (url != currentURL)
		applyURL(url);

	setTimeout("updateURL()", 400);
}

function showProgressbar()
{
	$('progress').show();
}

function hideProgressbar()
{
	if ($('progress'))
		$('progress').hide();
}

function setTab(name)
{
	if (name == currentTab) return;

	$(name + 'List').show();
	$(currentTab + 'List').hide();

	$(name + 'Tab').className = 'active';
	$(currentTab + 'Tab').className = 'inactive';

	currentTab = name;

	window.location = "#tab=" + name;
	currentURL = window.location.href;
}

function updateStatus()
{
	setTimeout("updateStatus()", 1000);

	ajax_get('status', function(transport)
	{
		var data = transport.responseJSON;
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
		var album = data.album;
		var hasCover = data.has_cover;

		if (artist != $('artist').innerHTML)
			$('artist').update(artist);
		if (track != $('track').innerHTML)
			$('track').update(track);
		if (timeTotal != $('timeTotal').innerHTML)
			$('timeTotal').update(timeTotal);
		$('timePassed').update(data.timePassed);

		var img = $('nowPlayingCover');
		if (hasCover)
		{
			var coverURI = data.cover_uri;
			img.src = 'cover/' + coverURI;
			img.show();
		}
		else
		{
			img.hide();
			img.src = '';
		}

		if (serverQueue != queueLength)
			updatePlaylist();
	});
}

function setPlaylist(data)
{
	queueLength = data.length;
	var div = $('playlist');
	var lis = div.select('li');

	for (var i=0; i < lis.length; i++)
	{
		var li = lis [i];
		var item = data [i];
		if (item)
			li.update(item);
		else
			li.update('<span class="freeSlot">-- empty --</span>');
	}
}

function showMessage(message)
{
	if (messageTimeout)
		clearTimeout(messageTimeout);

	var element = $('messageText');
	element.update(message);
	messageTimeout = setTimeout("fadeMessage()", 3000);
	Effect.Appear('message', { 'duration' : '0.1' });
	hideProgressbar();
}

function fadeMessage()
{
	Effect.Fade('message', { 'duration' : '0.4' });
}

function showSearchResults(transport)
{
	var response = transport.responseText;
	$('searchResult').update(response);
	hideProgressbar();
}

function showErrorMessage(transport)
{
	var response = transport.responseText;

	if (response)
		showMessage(response);
	else
		showMessage('Something bad happened');

	hideProgressbar();
}

function showArtist(artist)
{
	showProgressbar();

	window.location = "#artist=" + artist;
	currentURL = window.location.href;

	ajax_get('artist/' + artist, function(transport)
	{
		showSearchResults(transport);
	});
}

function showGenre(genre)
{
	showProgressbar();

	window.location = "#genre=" + genre;
	currentURL = window.location.href;

	ajax_get('genre/' + genre, function(transport)
	{
		showSearchResults(transport);
	});
}

function search()
{
	showProgressbar();

	var form = document.forms["searchform"];
	var searchType = $F(form.searchType);
	var searchTerm = $F(form.searchTerm);

	var data = $H({
		'where': searchType,
		'what':  searchTerm,
		'forceSearch': true
	});

	ajax_post('search', data, function(transport)
	{
		showSearchResults(transport);
	});
}

function removeTrack(id)
{
	var data = $H({'id': id});

	ajax_post('remove', data, function(transport)
	{
		updatePlaylist();
	});
}

function queueFile(file)
{
	var data = $H({'file': file});

	ajax_post('queue', data, function(transport)
	{
		updatePlaylist();
	});
}

function refreshProgram()
{
	setTimeout('refreshProgram()', 1000);

	ajax_get(base_url + '/program/status', function(transport)
	{
		var response = transport.responseText;
		var data = response.evalJSON();

		$('clock').update(data.time);
		$('currentTitle').update(data.currentTitle);
		$('currentState').update(data.currentState);

		if (data.nextTitle) {
			$('next').show();
			$('nextTitle').update(data.nextTitle);
			$('nextTime').update(data.nextTime);
		} else {
			$('next').hide();
		}
	});
}

function updateDisabledJukebox()
{
	setTimeout('updateDisabledJukebox()', 1000);

	ajax_get('status', function(transport)
	{
		var data = transport.responseJSON;
		var jukebox = data.jukebox;

		if (jukebox)
			window.location = base_url
	});
}

function control(action)
{
	var data = $H({'action': action});

	ajax_post('control', data, function(transport) {});
}

function updatePlaylist()
{
	ajax_get('queue', function(transport)
	{
		var queue = transport.responseJSON;
		setPlaylist(queue);
	});
}
