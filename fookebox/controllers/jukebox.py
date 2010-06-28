import sys
import mpd
import base64
import logging
import simplejson

from pylons import request, response, session, tmpl_context as c, url
from pylons import config
from pylons.controllers.util import abort, redirect

from fookebox.lib.base import BaseController, render
from fookebox.model.jukebox import Jukebox
from fookebox.model.mpdconn import Track
from fookebox.model.albumart import Album

log = logging.getLogger(__name__)

class JukeboxController(BaseController):

	def index(self):
		jukebox = Jukebox()
		artists = jukebox.getArtists()
		genres = jukebox.getGenres()

		jukebox.close()

		return render('/client.tpl', extra_vars={
			'genres': genres,
			'artists': artists,
			'config': config,
		})

	def status(self):
		log.debug("STATUS: Client updating")
		jukebox = Jukebox()
		jukebox.cleanQueue()

		song = jukebox.getCurrentSong()
		queueLength = jukebox.getQueueLength()

		if (config.get('auto_queue') and queueLength == 0 and
			jukebox.timeLeft() <= config.get(
				'auto_queue_time_left')):
			jukebox.autoQueue()

		data = {}

		if song:
			log.debug("STATUS: Playing %s" % song)
			songPos = int(song['timePassed'])

			if 'time' in song:
				songTime = int(song['time'])
			else:
				songTime = 0

			total = "%02d:%02d" % (songTime / 60, songTime % 60)
			position = "%02d:%02d" % (songPos / 60, songPos % 60)

			track = Track()
			track.load(song)

			album = Album(track.artist, track.album)

			data['artist'] = track.artist
			data['track'] = track.title
			data['album'] = track.album
			data['has_cover'] = album.hasCover()
			data['cover_uri'] = album.getCoverURI()
			data['timePassed'] = position
			data['timeTotal'] = total

		log.debug("STATUS: Queue length: %d" % queueLength)
		data['queueLength'] = queueLength
		data['jukebox'] = jukebox.isEnabled()

		jukebox.close()

		response.headers['content-type'] = 'application/json'
		return simplejson.dumps(data)

	def _showQueue(self):
		jukebox = Jukebox()
		output = []
		items = jukebox.getPlaylist()

		jukebox.close()

		for item in items[1:]:
			track = Track()
			track.load(item)
			output.append(render('/playlist-entry.tpl',
				extra_vars={
				'entry': track,
				'config': config,
			}))

		log.debug("QUEUE: Contains %d item(s)" % len(items[1:]))

		response.headers['content-type'] = 'application/json'
		return simplejson.dumps(output)

	def _addToQueue(self):
		try:
			post = simplejson.load(request.environ['wsgi.input'])
		except simplejson.JSONDecodeError:
			log.error("QUEUE: Could not parse JSON data")
			abort(400, 'Malformed JSON data')

		jukebox = Jukebox()

		if jukebox.getQueueLength() >= config.get('max_queue_length'):
			log.error('QUEUE: Full, aborting')
			abort(409, 'The queue is full')

		if 'file' not in post:
			log.error('QUEUE: No file specified in JSON data')
			abort(400, 'Malformed JSON data')

		b64 = post['file']

		try:
			file = base64.urlsafe_b64decode(b64)
		except TypeError:
			log.error("QUEUE: Failed to decode base64 data: %s" %
					b64)
			abort(400, 'Malformed base64 encoding')

		if file == '' or file == None:
			log.error("QUEUE: No file specified")
			abort(400, 'No file specified')

		jukebox.queue(file)
		jukebox.close()

	def queue(self):
		if request.method == 'GET':
			log.debug("QUEUE: GET")
			return self._showQueue()
		elif request.method == 'POST':
			log.debug("QUEUE: POST")
			return self._addToQueue()

	def _search(self, where, what, forceSearch = False):
		log.debug("SEARCH: '%s' in '%s'" % (what, where))

		jukebox = Jukebox()
		albums = jukebox.search(where, what, forceSearch)
		jukebox.close()

		log.debug("SEARCH: found %d album(s)" % len(albums))

		return render('/search.tpl', extra_vars={
			'what': what,
			'albums': albums.values()
		})

	def genre(self, genreBase64):
		try:
			genre = genreBase64.decode('base64')
		except:
			log.error("GENRE: Failed to decode base64 data: %s" %
				genreBase64)
			abort(400, 'Malformed request data')

		return self._search('genre', genre)

	def artist(self, artistBase64):
		try:
			artist = artistBase64.decode('base64')
		except:
			log.error("ARTIST: Failed to decode base64 data: %s" %
				artistBase64)
			abort(400, 'Malformed request data')

		return self._search('artist', artist)

	def search(self):
		try:
			post = simplejson.load(request.environ['wsgi.input'])
		except simplejson.JSONDecodeError:
			log.error("SEARCH: Failed to parse JSON data")
			abort(400, 'Malformed JSON data')

		try:
			what = post['what']
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
			abort(400, 'Song removal disabled')

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
			abort(400, 'Controls disabled')

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
			abort(400, 'Invalid command')

		commands[action]()
		jukebox.close()

	def cover(self, artist, album):
		try:
			artist = base64.urlsafe_b64decode(str(artist))
			album = base64.urlsafe_b64decode(str(album))
		except:
			log.error("COVER: Failed to decode base64 data")
			abort(400, 'Malformed base64 encoding')

		album = Album(artist, album)
		path = album.getCover()

		if path == None:
			log.error("COVER: missing for %s/%s" % (artist, album))
			abort(404, 'No cover found for this album')

		file = open(path, 'r')
		data = file.read()
		file.close()

		response.headers['content-type'] = 'image/jpeg'
		return data

	def disabled(self):
		return render('/disabled.tpl', extra_vars={
			'config': config,
			'base_url': request.url.replace('disabled', ''),
		})