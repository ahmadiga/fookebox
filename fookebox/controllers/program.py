import logging
import simplejson

from datetime import datetime
from pylons import request, response, config, app_globals as g
from pylons.controllers.util import abort
from fookebox.lib.base import BaseController, render
from fookebox.model import meta
from fookebox.model.jukebox import Jukebox
from fookebox.model.schedule import Event, EVENT_TYPE_JUKEBOX

log = logging.getLogger(__name__)

class ProgramController(BaseController):

	def index(self):
		jukebox = Jukebox()
		artists = jukebox.getArtists()
		genres = jukebox.getGenres()

		return render('/program.tpl')

	def status(self):
		jukebox = Jukebox()
		event = jukebox.getCurrentEvent()
		next = jukebox.getNextEvent()

		now = datetime.now()
		if now.second % 2 > 0:
			format = "%H %M"
		else:
			format = "%H:%M"

		event = jukebox.getCurrentEvent()
		currentEvent = {}

		if event.type == EVENT_TYPE_JUKEBOX:
			currentSong = jukebox.getCurrentSong()
			currentEvent['type'] = event.type
			currentEvent['title'] = event.name

			track = {}
			if 'artist' not in currentSong:
				currentSong['artist'] = ''
			if 'title' not in currentSong:
				currentSong['title'] = ''

			track['artist'] = currentSong['artist']
			track['title'] = currentSong['title']
			currentEvent['tracks'] = [ track ]

			playlist = jukebox.getPlaylist()
			if len(playlist) > 1:
				track = {}
				track['artist'] = playlist[1]['artist']
				track['title'] = playlist[1]['title']
				currentEvent['tracks'].append(track)
		else:
			currentEvent['type'] = event.type
			currentEvent['title'] = event.name

		events = {}
		events['current'] = currentEvent

		next = jukebox.getNextEvent()
		if next != None:
			events['next'] = {
				'type': next.type,
				'title': next.name,
				'time': next.time.strftime("%H:%M")
			}

		data = {
			'events': events,
			'time': now.strftime(format),
		}

		response.headers['content-type'] = 'application/json'
		return simplejson.dumps(data)

	def edit(self):
		if request.method == 'POST':
			name = request.params['name']
			type = int(request.params['type'])
			hour = request.params['hour']
			minute = request.params['minute']
			time = datetime.strptime("%s:%s" % (hour, minute),
					"%H:%M")
			if 'id' in request.params:
				id = request.params['id']
				Event.update(id, name, type, time)
			else:
				Event.add(name, type, time)

		event_q = meta.Session.query(Event)
		#vents = event_q.all()

		vars = {
			'events': Event.all(),
			'current': Event.getCurrent()
		}

		params = request.params
		if 'edit' in params:
			vars['edit'] = int(params['edit'])

		return render('/program-edit.tpl', vars)


	def current(self):
		if request.method != 'POST':
			abort(400, 'Nothing to see here')

		try:
			post = simplejson.load(request.environ['wsgi.input'])
		except simplejson.JSONDecodeError:
			log.error("QUEUE: Could not parse JSON data")
			abort(400, 'Malformed JSON data')

		id = post['id']
		g.eventID = int(id)

	def delete(self):
		if request.method != 'POST':
			abort(400, 'Nothing to see here')

		try:
			post = simplejson.load(request.environ['wsgi.input'])
		except simplejson.JSONDecodeError:
			log.error("QUEUE: Could not parse JSON data")
			abort(400, 'Malformed JSON data')

		id = post['id']
		Event.delete(int(id))

	def move(self):
		if request.method != 'POST':
			abort(400, 'Nothing to see here')

		try:
			post = simplejson.load(request.environ['wsgi.input'])
		except simplejson.JSONDecodeError:
			log.error("QUEUE: Could not parse JSON data")
			abort(400, 'Malformed JSON data')

		id = post['id']
		direction = post['direction']

		if direction == 'up':
			Event.up(id)
		else:
			Event.down(id)