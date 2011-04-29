# fookebox, http://fookebox.googlecode.com/
#
# Copyright (C) 2007-2011 Stefan Ott. All rights reserved.
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.

import sys
import mpd
import errno
import base64
import logging
import simplejson

from pylons import request, response, session, tmpl_context as c, url
from pylons import config, cache
from pylons.decorators import jsonify, rest
from pylons.controllers.util import abort, redirect
from pylons.i18n.translation import _, ungettext

from fookebox.lib.base import BaseController, render
from fookebox.model.jukebox import Jukebox, QueueFull
from fookebox.model.mpdconn import Track, Album
from fookebox.model.albumart import AlbumArt

logging.basicConfig(level=logging.WARNING)
log = logging.getLogger(__name__)

import socket
from pylons.i18n.translation import _

class JukeboxController(BaseController):

	def __render(self, template, extra_vars):
		try:
			return render(template, extra_vars = extra_vars)
		except IOError:
			exctype, value = sys.exc_info()[:2]
			abort(500, value)

	def index(self):
		try:
			jukebox = Jukebox()
		except socket.error:
			log.error("Error on /index")
			return self.__render('/error.tpl', extra_vars={
				'error': 'Connection to MPD failed'})
		except mpd.CommandError:
			log.error("Error on /index")
			error = sys.exc_info()
			return self.__render('/error.tpl', extra_vars={
				'error': error[1]})
		except:
			log.error("Error on /index")
			return self.__render('/error.tpl', extra_vars={
				'error': sys.exc_info()})

		artists = jukebox.getArtists()
		genres = jukebox.getGenres()
		jukebox.close()

		user_agent = request.environ.get('HTTP_USER_AGENT')

		return self.__render('/client.tpl', extra_vars={
			'genres': genres,
			'artists': artists,
			'config': config,
			'mobile': 'mobile' in user_agent.lower(),
		})

	@jsonify
	def status(self):
		jukebox = Jukebox()

		try:
			queueLength = jukebox.getQueueLength()
			enabled = jukebox.isEnabled()
			timeLeft = jukebox.timeLeft()
		except:
			log.error("Could not read status")
			jukebox.close()
			raise

		if (config.get('auto_queue') and queueLength == 0 and enabled
			and timeLeft <= config.get('auto_queue_time_left')):
			try:
				jukebox.autoQueue()
			except:
				log.error("Auto-queue failed")
				jukebox.close()
				raise

		try:
			track = jukebox.getCurrentSong()
		except:
			log.error("Could not get the current song")
			raise
		finally:
			jukebox.close()

		data = {
			'queueLength': queueLength,
			'jukebox': enabled
		}

		if track:
			songPos = int(track.timePassed)
			songTime = track.time

			total = "%02d:%02d" % (songTime / 60, songTime % 60)
			position = "%02d:%02d" % (songPos / 60, songPos % 60)

			album = Album(track.artist, track.album)
			album.add(track)

			data['artist'] = track.artist
			data['track'] = track.title
			data['album'] = track.album
			data['has_cover'] = album.hasCover()
			data['cover_uri'] = album.getCoverURI()
			data['timePassed'] = position
			data['timeTotal'] = total

		return data

	def _addToQueue(self):
		try:
			post = simplejson.load(request.environ['wsgi.input'])
		except simplejson.JSONDecodeError:
			log.error("QUEUE: Could not parse JSON data")
			abort(400, 'Malformed JSON data')

		if 'files' not in post:
			log.error('QUEUE: No file specified in JSON data')
			abort(400, 'Malformed JSON data')

		files = post['files']

		if len(files) < 1:
			log.error("QUEUE: No files specified")
			abort(400, 'No files specified')

		jukebox = Jukebox()

		for file in files:
			if file == '' or file == None:
				log.error("QUEUE: No file specified")
				continue

			try:
				jukebox.queue(file)
			except QueueFull:
				jukebox.close()
				log.error('QUEUE: Full, aborting')
				abort(409, _('The queue is full'))

		jukebox.close()

	@rest.dispatch_on(POST='_addToQueue')
	@jsonify
	def queue(self):
		jukebox = Jukebox()
		items = jukebox.getPlaylist()
		jukebox.close()

		return {'queue': items[1:]}

	def _search(self, where, what, forceSearch = False):
		log.debug("SEARCH: '%s' in '%s'" % (what, where))

		jukebox = Jukebox()
		tracks = jukebox.search(where, what, forceSearch)
		jukebox.close()

		log.debug("SEARCH: found %d track(s)" % len(tracks))
		return {'meta': {'what': what }, 'tracks': tracks}

	@jsonify
	def genre(self, genreBase64=''):
		try:
			genre = genreBase64.decode('base64')
		except:
			log.error("GENRE: Failed to decode base64 data: %s" %
				genreBase64)
			abort(400, 'Malformed request data')

		return self._search('genre', genre)

	@jsonify
	def artist(self, artistBase64=''):
		try:
			artist = artistBase64.decode('base64')
		except:
			log.error("ARTIST: Failed to decode base64 data: %s" %
				artistBase64)
			abort(400, 'Malformed request data')

		return self._search('artist', artist)

	@jsonify
	def search(self):
		try:
			post = simplejson.load(request.environ['wsgi.input'])
		except simplejson.JSONDecodeError:
			log.error("SEARCH: Failed to parse JSON data")
			abort(400, 'Malformed JSON data')

		try:
			what = post['what'].encode('utf8')
			where = post['where']
		except KeyError:
			log.error("SEARCH: Incomplete JSON data")
			log.error(sys.exc_info())
			abort(400, 'Malformed JSON data')

		forceSearch = 'forceSearch' in post and post['forceSearch']

		return self._search(where, what, forceSearch)

	def remove(self):
		if not config.get('enable_song_removal'):
			log.error("REMOVE: Disabled")
			abort(400, _('Song removal disabled'))

		try:
			post = simplejson.load(request.environ['wsgi.input'])
		except simplejson.JSONDecodeError:
			log.error('REMOVE: Failed to parse JSON data')
			abort(400, 'Malformed JSON data')

		if 'id' not in post:
			log.error('REMOVE: No id specified in JSON data')
			abort(400, 'Malformed JSON data')

		id = post['id']

		jukebox = Jukebox()
		jukebox.remove(id)
		jukebox.close()

	def control(self):
		if not config.get('enable_controls'):
			log.error('CONTROL: Disabled')
			abort(400, _('Controls disabled'))

		try:
			post = simplejson.load(request.environ['wsgi.input'])
		except simplejson.JSONDecodeError:
			log.error('CONTROL: Failed to parse JSON data')
			abort(400, 'Malformed JSON data')

		if 'action' not in post:
			log.error('CONTROL: No action specified in JSON data')
			abort(400, 'Malformed JSON data')

		action = post['action']
		log.debug('CONTROL: Action=%s' % action)

		jukebox = Jukebox()
		commands = {
			'play': jukebox.play,
			'pause': jukebox.pause,
			'prev': jukebox.previous,
			'next': jukebox.next,
			'voldown': jukebox.volumeDown,
			'volup': jukebox.volumeUp,
			'rebuild': jukebox.refreshDB,
		}

		if action not in commands:
			log.error('CONTROL: Invalid command')
			jukebox.close()
			abort(400, 'Invalid command')

		try:
			commands[action]()
		except:
			log.error('Command %s failed' % action)
			jukebox.close()
			abort(500, _('Command failed'))

		jukebox.close()

	def findcover(self):
		try:
			post = simplejson.load(request.environ['wsgi.input'])
		except simplejson.JSONDecodeError:
			log.error("SEARCH: Failed to parse JSON data")
			abort(400, 'Malformed JSON data')

		artist = post.get('artist')
		album = post.get('album')

		album = Album(artist, album)
		if album.hasCover():
			return album.getCoverURI()

		abort(404, 'No cover')

	def cover(self, artist, album):
		try:
			artist = base64.urlsafe_b64decode(artist.encode('utf8'))
			album = base64.urlsafe_b64decode(album.encode('utf8'))
		except:
			raise
			log.error("COVER: Failed to decode base64 data")
			abort(400, 'Malformed base64 encoding')

		album = Album(artist, album)
		art = AlbumArt(album)
		path = art.get()

		if path == None:
			log.error("COVER: missing for %s/%s" % (artist,
				album.name))
			abort(404, 'No cover found for this album')

		file = open(path, 'r')
		data = file.read()
		file.close()

		response.headers['content-type'] = 'image/jpeg'
		return data

	def disabled(self):
		return self.__render('/disabled.tpl', extra_vars={
			'config': config,
			'base_url': request.url.replace('disabled', ''),
		})
