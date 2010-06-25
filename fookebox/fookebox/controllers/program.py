import logging
import datetime
import simplejson

from pylons import response
from fookebox.lib.base import BaseController, render
from fookebox.model.jukebox import Jukebox

log = logging.getLogger(__name__)

class ProgramController(BaseController):

	def index(self):
		jukebox = Jukebox()
		artists = jukebox.getArtists()
		genres = jukebox.getGenres()

		return render('/program.tpl', extra_vars={
			'current': jukebox.getCurrentEvent(),
			'genres': genres,
			'artists': artists,
		})

	def status(self):
		jukebox = Jukebox()
		event = jukebox.getCurrentEvent()
		next = jukebox.getNextEvent()

		now = datetime.datetime.now()
		if now.second % 2 > 0:
			format = "%H %M"
		else:
			format = "%H:%M"

		response.headers['content-type'] = 'application/json'
		data = {
			'time': now.strftime(format),
			'currentTitle': event.getAsCurrent(),
			'currentState': event.getAsCurrentState(),
			'hasNext': False,
		}

		if next != None:
			data['hasNext'] = True
			data['nextTitle'] = next.getAsNext()
			data['nextTime'] = next.getTime()

		return simplejson.dumps(data)
