"use strict";

function QueueView()
{
	var getMaxLength = function()
	{
		return $('#queue').find('li').length;
	}

	var getCurrentLength = function()
	{
		return $('#queue').find('li:not(.disabled)').length;
	}

	Object.defineProperty(this, 'maxLength',
	{
		get: getMaxLength
	});

	Object.defineProperty(this, 'currentLength',
	{
		get: getCurrentLength
	});
}

QueueView.prototype.update = function()
{
	var req = $.get('queue');
	req.done($.proxy(this.load, this));
}

QueueView.prototype.updateLabel = function()
{
	var len = this.currentLength;
	var qlen = this.maxLength;
	var label = $('#queueStatus');

	if (len == qlen)
	{
		label.text('full');
		label.removeClass('label-success');
		label.addClass('label-warning');
		label.removeClass('label-info');
	}
	else if (len == 0)
	{
		label.text('empty');
		label.addClass('label-success');
		label.removeClass('label-warning');
		label.removeClass('label-info');
	}
	else
	{
		label.text(len + '/' + qlen);
		label.removeClass('label-success');
		label.removeClass('label-warning');
		label.addClass('label-info');
	}
	this.pulsate();
}

QueueView.prototype.pulsate = function()
{
	var label = $('#queueStatus');
	label.hide();
	label.show('highlight');
}

QueueView.prototype.load = function(data)
{
	if (!('queue' in data))
		return;

	var len = data.queue.length;
	var els = $('#queue li');

	for (var i=0; i < this.maxLength; i++)
	{
		var el = $(els[i]);
		var link = el.find('a');

		if (i >= len)
		{
			el.addClass('disabled');
			link.find('.artist').text('');
			link.find('.title').text('');
			continue;
		}

		var track = data.queue[i];
		link.find('.artist').text(track.artist);
		link.find('.title').text(track.title);
		el.removeClass('disabled');
	}

	this.updateLabel();
}

function AlbumCover(artist, name)
{
	this.artist = artist;
	this.name = name;
}

AlbumCover.prototype.load = function(target)
{
	var data = { 'artist': this.artist, 'album': this.name };

	var req = $.ajax('findcover', {
		'data': JSON.stringify(data),
		'type': 'POST',
		'processData': false,
		'contentType': 'application/json'
	});
	req.done($.proxy(function(data)
	{
		target.css('backgroundImage', 'url(cover/' + data.uri + ')');
	}, this));
	req.fail($.proxy(function()
	{
		target.css('background-image', 'url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAQAAAAm93DmAAAAAmJLR0QA/4ePzL8AAAAJcEhZcwAADdcAAA3XAUIom3gAAAAHdElNRQfeCRwCAi9pXNYcAAAGY0lEQVRIx62Wy48cVxWHv3Pvra5+TY89k3nYnrGZiWcyOCYGEccR5ikkskFZgISEIiHEIn8BiiIUQRAPCRYsQOzYsAAJIcGGRQIbi4CFBMixjR2ZxGPH9kzG9ry6Z/pVj3tY1J2eTkwMSNTtUrVUt74659bv/s6B//MhD7tZlecfPXBKZvNaFmXO+ihxHbm/dXHl2q/6/zPw5Xn/uEzOTB2cL40bV0wVVNPm9vLKaudedOPKxV/rfwn83vTO7OQn6mfLpUM8QoMYR4QnJ6XDOqvsaO/yyiuVW6U7L+T/EfiDSflKfuZo+SRT9OiTqhePYHFEWpI6Zd2Qf3AZc1F/8fU33v20fSDVE+aFA0tPx49huM8ufTK8KCAoHhUlpSQzLLAz0XrybHLuzYcAX/zk6NcWZ0/aGm1tkUoOqChgAEXEYUBUDRWZMnG1N/dE/bXL7wP85pnJL8zOL+Jo6S6pCIKiaFgbRYgKtChCnYb4aj59Ot2Pcgj4w2n31WMnThDrGm3x8sBqoFhKSDFEJZW6jopWN+efufbM1u+VkAkA3y3nX56a+wANXRfEAjkeHyJUMnJ8WEkNd6ArI3qc2QPyXG+m4ATg85JO9z+yVJuiieJwCD6MjIycLl2yAayI16P0afCkzU91jn5RhlJ+drrxpaWFOYPuBCntRdanQ4qS0MdjMRjMXtpAisdJRj4ysnFubRBh+og+/UEb00ekeMhiyNilTa+QDgkpOX16ZHh8+N+XPsoS8YfSuUHKJ2xlql4ZE6+ZOGyIQ0nokmgh6ioxMY5cE+2TkNKnS4+MXDId15qUJ5+LwQF89vDBxUfxFF9WMRggI1FBxarDEgtawZGJp6uJGMDiMHi8pEyze3j7MS45gLG52qkZFMGgWDyWnASDp0xEP7nd2x2Nd2dkptZgk12ECvVgGCCkTLAxP3oqAGXUTI4iGHUCHkOORxGJKOu6rO1mf01upBPXP9qqL2pGIjlQCUDBa0ZNolEmQspZrFGZBBE70JiGfdFJtje2X3evllbTA+2OUp88UhVSUpKByI1kxIhk1QBMrScmxQaFCQo4hBrvyM710rnvLANrL72W1tY+dxxDTk6C3QNisEBa2otQPBEWj6Dk5HgsNRwjZKVeO14txBVtp/d6I4JB8GTkxSfBkxZeZIJsIm/JCr8jwgXp5mR4Rnq1sXSxACaHS3ONHasEu8jDy3OUPorLQoSl3IaEDY4IUPokKDDuWkvbvETpn+mh7mcaTy1UCtGDRcmDD1l6eFwagLZner1yDau5GByKEuEQDJOOhlna1PZT5frR48fG5mkiGosragyeDKPQlTx37QDM17Pbmwt1rJiwVx0VADVUZJZqdfVkq1bNDusRSiQYytjgj4rHC7Tor+udAFy9MfH6zYWjuLDQDoujTEcyumoZcxPOgMtp6wYGJzYooTg8wn02lrcuBODP7n3rOnycCIMLsk5xRChCjxadYK/Fq9xg/fZ8CTbYXPvR9YHb6Ea+taIJJS2mCwZLiTJOUukNXDGXTJA9SCF/o4410r65P2Sw9q5/9W95kxirEmRjcDgcJSrUqFKlSoVSkMvebkItyg26f3JXhgz2XO/03e0zkyMHJcLLvr0WijNERMSUcLiBVxc7qoSRLZZ7zV++fPVdNaWxVT1/dfMtrFg1aoInCjkQEVMmJgqRGwg7RNRKm+XE/aW+8p6q9wf/+aubi8xWGQvlvChHKXloLyQob+/MMVRpywobq/LjF+8+UEZf8R97u3+wOVuTMTFaqCsnQ4eAun/ViCq7cpv1W8lPv3Hz3xb6P7Y+vK7VzmwuI1IFcimA+0a650RWY4xsyCrbb7R+8+2/v28r8uf1J261x3dGKFuJJMIDqsggRjVicSrSk01ud9bfbP72++eHm65hoIjFnm91LxxuXBttlmtSEY9Q/EyYIhiMNPWmv7bTvXjr5z+5QozdB+63c2ZQcs1SfHz89KdGPu1GZzhEgwiLI6FHlx3WuUu3l1649Ltr96920hylOD2KPggsKqg7OzVxpDJx5NjsQm3COA3mm/l+887ynVvNuzv3zq/4NiqqRc0o1D4U4Z6Xm4GzR4w8fmhptjau5ayUWasmo5+23lq59E7epI+SBDnut0Hv6WALqISt7IJ5WyIcLphfGqJJyciHOid9eNMuQ4MhxSjDjY/yQNv+LyTuBxz38h2yAAAAAElFTkSuQmCC)');
	}, this));
}

function SearchResult(jukebox, tracks)
{
	this.tracks = tracks;
	this.jukebox = jukebox;
	this.albums = new Object();

	this.parseAlbums();
}

SearchResult.prototype.parseAlbums = function()
{
	$(this.tracks).each($.proxy(function(i, track)
	{
		var album;

		if (!(track.album in this.albums))
		{
			this.albums[track.album] = new Array();
		}

		album = this.albums[track.album];
		album.push(track);
	}, this));
}

SearchResult.prototype.show = function()
{
	$('#result').empty();

	for (name in this.albums)
	{
		this.render(this.albums[name]);
	}
}

SearchResult.prototype.render = function(album)
{
	var panel = $('<div class="panel-heading album-heading"></div>');
	var title = $('<h4 class="panel-title">');
	var albumTitle = $('<div class="albumTitle"></div>');
	var albumArtist = $('<div class="albumArtist"></div>');

	new AlbumCover(album[0].artist, album[0].album).load(panel);

	albumTitle.text(album[0].album);
	albumArtist.text(album[0].artist);

	title.append(albumTitle);
	title.append(albumArtist);

	panel.append(title);

	var pbody = $('<div class="panel-body">');
	var list = $('<ul class="list-unstyled"></ul>');
	pbody.append(list);

	for (var i=0; i < album.length; i++)
	{
		var track = album[i];
		this.renderTrack(track, list);
	}

	$('#result').append(panel);
	$('#result').append(pbody);
}

SearchResult.prototype.renderTrack = function(track, list)
{
	function tracknum(input)
	{
		if (input == '')
			return '00';
		else if (input.indexOf('/') >= 0)
			return tracknum(input.replace(/\/.*/, ''));
		else if (input.length < 2)
			return '0' + input;
		else
			return input
	}

	var li = $('<li></li>');
	var link = $('<a href="#"></a>');
	var num = $('<span class="trackNum">a</span>');
	var artist = $('<span class="trackArtist"></span>');
	var title = $('<span class="trackName"></span>');

	num.text(tracknum(track.track));
	artist.text(track.artist);
	title.text(track.title);

	link.append(num);
	link.append(' &ndash; ');
	link.append(artist);
	link.append(' &ndash; ');
	link.append(title);
	li.append(link);
	list.append(li);

	link.click($.proxy(function(event)
	{
		event.preventDefault();
		var data = {'files': [track.file]}

		var req = $.ajax('queue', {
			'data': JSON.stringify(data),
			'type': 'POST',
			'processData': false,
			'contentType': 'application/json'
		});
		req.done($.proxy(function()
		{
			this.jukebox.queue.update();
		}, this));
		req.error($.proxy(function(data)
		{
			switch(data.status)
			{
				case 409:
					this.jukebox.queue.pulsate();
					break;
				default:
					console.error(data);
			}
		}, this));
	}, this));
}

function Jukebox()
{
	this.queue = new QueueView();
}

Jukebox.prototype.sync = function()
{
	window.setTimeout($.proxy(this.sync, this), 1000);

	var req = $.get('status');
	req.done($.proxy(function(data)
	{
		if ('artist' in data)
			$('.currentArtist').text(data.artist);
		else
			$('.currentArtist').empty();

		if ('track' in data)
			$('.currentTitle').text(data.track);
		else
			$('.currentTitle').empty();

		if (('queueLength' in data) && (data.queueLength != this.queue.currentLength))
			this.queue.update();
	}, this));
}

Jukebox.prototype.showItems = function(type, name)
{
	var req = $.getJSON(type + '/' + name);

	req.done($.proxy(function(data)
	{
		var result = new SearchResult(this, data.tracks);
		result.show();
	}, this));

	req.fail(function()
	{
		console.error('failed');
	});
}

Jukebox.prototype.showGenre = function(name)
{
	this.showItems('genre', name);
}

Jukebox.prototype.showArtist = function(name)
{
	this.showItems('artist', name);
}

$(document).ready(function()
{
	var jukebox = new Jukebox();
	jukebox.sync();

	$('#artistSearch').keyup(function(event)
	{
		var el = $(event.currentTarget);
		var val = el.val().toLowerCase();

		$('li.artist').each(function(i, item)
		{
			var link = $(item).find('a');
			var artist = link.text().toLowerCase();

			if (artist.indexOf(val) > -1)
				link.show();
			else
				link.hide();
		});
	});
	$('#artistSearchForm').submit(function(event)
	{
		event.preventDefault();
	});
	$('#genreSearchForm').submit(function(event)
	{
		event.preventDefault();
	});
	$('li.artist a').click(function(event)
	{
		event.preventDefault();
		var target = $(event.target);
		jukebox.showArtist(target.data('base64'));
	});
	$('li.genre a').click(function(event)
	{
		event.preventDefault();
		var target = $(event.target);
		jukebox.showGenre(target.data('base64'));
	});
});