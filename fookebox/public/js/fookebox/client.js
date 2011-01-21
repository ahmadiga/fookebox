/*
 * fookebox, http://fookebox.googlecode.com/
 *
 * Copyright (C) 2007-2011 Stefan Ott. All rights reserved.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

var QueueView = Class.create(AjaxView,
{
	initialize: function()
	{
		this.queueLength = -1;
	},
	sync: function()
	{
		this.get('queue', this.update.bind(this));
	},
	update: function(transport)
	{
		var queue = transport.responseJSON;
		this.queueLength = queue.length;

		var div = $('playlist');
		var lis = div.select('li');

		lis.each(function(li, index) {
			var item = queue[index];

			if (item) {
				li.update(item);
			} else {
				li.update('<span class="freeSlot">-- empty --</span>');
			}
		});
	},
	setLength: function(length)
	{
		if (length != this.queueLength)
			this.sync();
	},
});

var TrackView = Class.create(AjaxView,
{
	initialize: function()
	{
		this.artist = "";
		this.track = "";
		this.timeTotal = "00:00";

		this.time = new Date(0, 0, 0);
	},
	attach: function(jukebox)
	{
		this.artistView = $('artist');
		this.trackView = $('track');
		this.timeView = $('timeTotal');
		this.timePassedView = $('timePassed');

		function addControl(key) {
			$('control-' + key).onclick = function() {
				jukebox.control(key); return false
			};
		}

		if ($('control')) {
			addControl('prev');
			addControl('pause');
			addControl('play');
			addControl('next');
			addControl('voldown');
			addControl('volup');
			addControl('rebuild');
		}
	},
	updateTime: function()
	{
		var seconds = this.time.getSeconds();
		var minutes = this.time.getMinutes();

		if (seconds < 10)
			seconds = "0" + seconds;
		if (minutes < 10)
			minutes = "0" + minutes;

		this.timePassedView.update(minutes + ":" + seconds);
	},
	tick: function()
	{
		this.time.setSeconds(this.time.getSeconds() + 1);
		this.updateTime();
	},
	adjustTime: function(time)
	{
		// sync our client-side track time with the one from the
		// server. update the seconds only if we differ by more than
		// one second (force-update the display in that case).
		//
		// this should fix the 'jumpy' track time display
		var parts = time.split(':');
		if (parts.length == 2)
		{
			var minutes = parts[0];
			var seconds = parts[1];

			var diff = seconds - this.time.getSeconds();

			this.time.setMinutes(minutes);

			if (diff > 1 || diff < -1)
			{
				this.time.setSeconds(seconds);
				this.updateTime();
			}
		}

	},
	update: function(artist, track, timeTotal)
	{
		this.artist = artist;
		this.track = track;
		this.timeTotal = timeTotal;

		if (this.artist != this.artistView.innerHTML)
			this.artistView.update(this.artist);
		if (this.track != this.trackView.innerHTML)
			this.trackView.update(this.track);
		if (this.timeTotal != this.timeView.innerHTML)
			this.timeView.update(this.timeTotal);
	},
});

var CoverView = Class.create(AjaxView,
{
	setCover: function(hasCover, coverURI)
	{
		var img = $('nowPlayingCover');
		if (hasCover) {
			img.src = 'cover/' + coverURI;
			img.show();
		} else {
			img.hide();
		}
	}
});

var MusicView = Class.create(AjaxView,
{
	showSearchResult: function(transport)
	{
		$('searchResult').update(transport.responseText);
		this.hideProgressbar();
	},
});

var JukeboxView = Class.create(AjaxView,
{
	initialize: function()
	{
		this.queueView = new QueueView();
		this.trackView = new TrackView();
		this.coverView = new CoverView();
		this.musicView = new MusicView();
	},
	attach: function()
	{
		this.trackView.attach(this);
	},
	disable: function()
	{
		window.location = 'disabled';
	},
	readStatus: function(transport)
	{
		var data = transport.responseJSON;
		var enabled = data.jukebox;

		if (!enabled)
		{
			this.disable();
			return
		}

		this.trackView.update(data.artist, data.track, data.timeTotal);
		this.trackView.adjustTime(data.timePassed);
		this.coverView.setCover(data.has_cover, data.cover_uri);
		this.queueView.setLength(data.queueLength);
	},
	sync: function()
	{
		window.setTimeout(this.sync.bind(this), 1000);

		// update time
		this.trackView.tick();

		this.get('status', this.readStatus.bind(this));
	},
	control: function(action)
	{
		var data = $H({'action': action});
		this.post('control', data, function(transport) {});
	},
	queue: function(file)
	{
		var data = $H({'file': file});
		this.post('queue', data,
			this.queueView.sync.bind(this.queueView));
	},
	unqueue: function(id)
	{
		var data = $H({'id': id});
		this.post('remove', data,
			this.queueView.sync.bind(this.queueView));
	},
	showArtist: function(artist)
	{
		this.showProgressbar();

		window.location = "#artist=" + artist;
		currentURL = window.location.href;

		this.get('artist/' + artist,
			this.musicView.showSearchResult.bind(this.musicView));
	},
	showGenre: function(genre)
	{
		this.showProgressbar();

		window.location = "#genre=" + genre;
		currentURL = window.location.href;

		this.get('genre/' + genre,
			this.musicView.showSearchResult.bind(this.musicView));
	},
	search: function(form)
	{
		this.showProgressbar();

		var type = $F(form.searchType);
		var term = $F(form.searchTerm);

		var data = $H({
			'where': type,
			'what':  term,
			'forceSearch': true
		});

		this.post('search', data,
			this.musicView.showSearchResult.bind(this.musicView));
	},
});

var CurrentPage = Class.create(
{
	initialize: function()
	{
		this.tab = 'artist';
		this.url = '';
	},
	watch: function()
	{
		this.url = window.location.href;
		this.apply();
		this.update();
	},
	update: function()
	{
		setTimeout(this.update.bind(this), 400);

		var url = window.location.href;

		if (url != this.url)
		{
			this.url = url;
			this.apply();
		}
	},
	apply: function()
	{
		url = unescape(this.url);

		if (url.indexOf("#") > -1)
		{
			var parts = url.split('#');
			var params = parts[1].split('=');
			var key = params[0];
			var value = parts[1].substring(key.length + 1);

			if (key == 'artist')
				jukebox.showArtist(value);
			else if (key == 'genre')
				jukebox.showGenre(value);
			else if (key == 'tab')
				this.setTab(value);
		}
	},
	setTab: function(name)
	{
		if (name == this.tab) return;

		$(name + 'List').show();
		$(this.tab + 'List').hide();

		$(name + 'Tab').classname = 'active';
		$(this.tab + 'Tab').classname = 'inactive';

		this.tab = name;

		window.location = "#tab=" + name;
		this.url = window.location.href;
	}
});

document.observe("dom:loaded", function()
{
	jukebox = new JukeboxView();
	jukebox.attach();
	jukebox.sync();

	page = new CurrentPage();
	page.watch();
});
