import sys
import random
import logging

from pylons import config, app_globals as g

from mpdconn import *
from schedule import Event, EVENT_TYPE_JUKEBOX
from albumart import Album

log = logging.getLogger(__name__)

class Jukebox(object):
	client = None
	lastAutoQueued = -1

	def __init__(self, mpd=None):
		self._connect(mpd)

	def _connect(self, to=None):
		if not to == None:
			self.client = to
			return

		if g.mpd == None:
			g.mpd = MPDPool()

		self.client = g.mpd.getWorker()

	def close(self):
		self.client.release()

	def timeLeft(self):
		status = self.client.status()

		if 'time' not in status:
			return 0

		(timePlayed, timeTotal) = status['time'].split(':')
		timeLeft = int(timeTotal) - int(timePlayed)
		return timeLeft

	def queue(self, file):
		log.info("Queued %s" % file)
		self.client.add(file)

		# Prevent (or reduce the probability of) a race-condition where
		# the auto-queue functionality adds a new song *after* the last
		# one stopped playing (which would re-play the previous song)
		if not self.isPlaying() and len(self.getPlaylist()) > 1:
			self.client.delete(0)

		self.client.play()

	def _autoQueueRandom(self):
		songs = self.client.listall()

		file = []

		while 'file' not in file:
			# we might have to try several times in case we get
			# a directory instead of a file
			index = random.randrange(len(songs))
			file = songs[index]

		self.queue(file['file'])

	def _autoQueuePlaylist(self, playlist):
		self.client.load(playlist)

		if len(playlist) < 1:
			return

		if config.get('auto_queue_random'):
			self.client.shuffle()
			playlist = self.client.playlist()
			song = playlist[0]
		else:
			playlist = self.client.playlist()
			index = (Jukebox.lastAutoQueued + 1) % len(playlist)
			song = playlist[index]
			Jukebox.lastAutoQueued += 1

		self.client.clear()
		self.queue(song)
		log.debug(Jukebox.lastAutoQueued)

	def autoQueue(self):
		log.info("Auto-queuing")
		lock = Lock()
		if not lock.acquire():
			return

		try:
			playlist = config.get('auto_queue_playlist')
			if playlist == None:
				self._autoQueueRandom()
			else:
				self._autoQueuePlaylist(playlist)

		except Exception:
			log.error(sys.exc_info())

		lock.release()

	# This can be removed once python-mpd supports the 'consume' command
	# (see http://www.musicpd.org/doc/protocol/ch02s02.html)
	def cleanQueue(self):
		current = self.client.currentsong()
		if current and 'pos' in current:
			if int(current['pos']) > 0:
				self.remove(0)

	def search(self, where, what, forceSearch = False):
		if config.get('find_over_search') and not forceSearch:
			data = self.client.find(where, what)
		else:
			data = self.client.search(where, what)

		albums = {}

		for song in data:
			track = Track()
			track.load(song)

			if track.disc > 0:
				album = "%s-%s" % (track.album, track.disc)
			elif track.album != None:
				album = track.album
			else:
				album = ''

			if album not in albums:
				albums[album] = Album(track.artist, track.album)
				if track.disc > 0:
					albums[album].disc = track.disc

			albums[album].add(track)

		return albums

	def getPlaylist(self):
		playlist = self.client.playlistinfo()
		return playlist

	def getGenres(self):
		genres = sorted(self.client.list('genre'))
		return [Genre(genre) for genre in genres]

	def getArtists(self):
		artists = sorted(self.client.list('artist'))
		return [Artist(artist) for artist in artists]

	def isPlaying(self):
		status = self.client.status()
		return status['state'] == 'play'

	def getCurrentSong(self):
		current = self.client.currentsong()

		if current:
			status = self.client.status()
			time = status['time'].split(':')[0]
			current['timePassed'] = time

		return current

	def getQueueLength(self):
		playlist = self.client.playlist()
		return max(len(playlist) - 1, 0)

	def getCurrentEvent(self):
		event = Event.getCurrent()
		event.jukebox = self
		return event

	def getNextEvent(self):
		event = Event.getNext()
		return event

	def isEnabled(self):
		event = self.getCurrentEvent()
		return event.type == EVENT_TYPE_JUKEBOX

	def remove(self, id):
		log.info("Removing playlist item #%d" % id)
		self.client.delete(id)

	def play(self):
		self.client.play()

	def pause(self):
		self.client.pause()

	def previous(self):
		self.client.previous()

	def next(self):
		self.client.next()

	def volumeDown(self):
		self.client.volume(-5)

	def volumeUp(self):
		self.client.volume(+5)

	def refreshDB(self):
		self.client.update()