import re
import os
import sys
import mpd
import base64
import random
import logging
from threading import BoundedSemaphore

from pylons import config

log = logging.getLogger(__name__)

class Lock(object):

	class __impl:

		def __init__(self):
			self.semaphore = BoundedSemaphore(value=1)

		def acquire(self):
			return self.semaphore.acquire(False)

		def release(self):
			return self.semaphore.release()

	__instance = None

	def __init__(self):
		if Lock.__instance is None:
			Lock.__instance = Lock.__impl()

		self.__dict__['_Lock__instance'] = Lock.__instance

	def __getattr__(self, attr):
		return getattr(self.__instance, attr)

class Album(object):

	def __init__(self, artist, albumName, disc=None):
		self.artist = str(artist)

		if albumName == None:
			self.name = ''
		else:
			self.name = str(albumName)

		self.disc = disc
		self.tracks = []

	def add(self, track):
		self.tracks.append(track)

	def hasCover(self):
		return self.getCover() != None

	def getCover(self):
		pattern = re.compile('[\/:<>\?*|]')

		if self.name == None:
			album = ''
		else:
			album = pattern.sub('_', self.name)

		if self.artist == None:
			artist = ''
		else:
			artist = pattern.sub('_', self.artist)

		path = '%s/%s-%s.jpg' % (config.get('album_cover_path'),
				artist, album)

		if not os.path.exists(path):
			path = '%s/%s-%s.jpg' % (config.get('album_cover_path'),
					config.get('compliations_name'), album)

			if not os.path.exists(path):
				return None

		return path

	def getCoverURI(self):
		return "%s/%s" % (base64.urlsafe_b64encode(self.artist),
				base64.urlsafe_b64encode(self.name))

class Genre(object):

	def __init__(self, name):
		self.name = name
		self.base64 = base64.urlsafe_b64encode(name)

class Artist(object):

	def __init__(self, name):
		self.name = name
		self.base64 = base64.urlsafe_b64encode(name)

class Track(object):
	artist = 'Unknown artist'
	title = 'Unnamed track'
	album = None
	track = 0
	file = ''
	b64 = ''
	disc = 0
	queuePosition = 0

	def load(self, song):
		if 'artist' in song:
			self.artist = song['artist']
		if 'title' in song:
			self.title = song['title']
		if 'file' in song:
			self.file = song['file']
			self.b64 = base64.urlsafe_b64encode(self.file)
		if 'track' in song:
			# possible formats:
			#  - '12'
			#  - '12/21'
			#  - ['12', '21']
			t = song['track']
			if '/' in t:
				tnum = t.split('/')[0]
				self.track = int(tnum)
			elif isinstance(t, list):
				self.track = int(t[0])
			else:
				self.track = int(t)
		if 'disc' in song:
			self.disc = song['disc']
		if 'album' in song:
			self.album = str(song['album'])
		if 'pos' in song:
			self.queuePosition = int(song['pos'])

class Jukebox(object):

	client = None
	lastAutoQueued = -1

	def __init__(self, mpd=None):
		self._connect(mpd)

	def __del__(self):
		self._disconnect()

	def _connect(self, to=None):
		log.debug("Connecting to mpd")

		if not to == None:
			self.client = to
			return

		host = config.get('mpd_host')
		port = config.get('mpd_port')
		password = config.get('mpd_pass')

		self.client = mpd.MPDClient()
		self.client.connect(host, port)

		if password:
			self.client.password(password)

	def _disconnect(self):
		log.debug("Disconnecting from mpd")
		self.client.close()
		self.client.disconnect()

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

	def search(self, where, what):
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
