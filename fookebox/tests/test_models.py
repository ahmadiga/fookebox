import logging
import unittest

from mpd import CommandError
from pylons import config
from fookebox.model.jukebox import Jukebox, QueueFull
from fookebox.model.mpdconn import Track

log = logging.getLogger(__name__)

class FakeMPD(object):

	def __init__(self):

		self.db = []
		self.fillDB()
		self.stop()
		self.queue = []
		self._pos = 0

	def fillDB(self):

		self.db.append({
			'file': 'song1',
			'time': '120',
			'album': 'Album 1',
			'title': 'Track 1',
			'track': '1',
			'genre': 'Genre 1',
			'artist': 'Artist 1',
			'date': '2005',
		})
		self.db.append({
			'file': 'song2',
			'artist': 'Artist 2',
			'title': 'Track 2',
		})
		self.db.append({
			'file': 'song3',
			'time': '144',
			'album': 'Album 1',
			'title': 'Track 2',
			'track': '2/4',
			'genre': 'Genre 1',
			'artist': 'Artist 1',
			'date': '2005',
		})
		self.db.append({
			'file': 'song4',
			'time': '123',
			'album': 'Album 2',
			'title': 'Track 3',
			'track': ['11', '12'],
			'genre': 'Genre 1',
			'artist': 'Artist 1',
			'date': '2005',
		})
		self.db.append({
			'file': 'song5',
			'time': '555',
			'album': 'Album 5',
			'title': 'Track 5',
			'genre': 'Genre 5',
			'artist': 'Artist 5',
			'date': '2005',
		})

	def stop(self):

		self._status = {
			'playlistlength': '0',
			'playlist': '5',
			'state': 'stop',
			'volume': '100',
		}

	def play(self):

		if not self.queue[0] == None:
			self._status = {
				'playlistlength': '1',
				'playlist': '5',
				'state': 'play',
				'volume': '100',
			}

			song = self.queue[self._pos]
			if 'time' in song:
				self._status['time'] = '0:%s' % song['time']
			else:
				self._status['time'] = '0:0'

	def load(self, playlist):

		if playlist == 'test01':
			self.queue = [ 'song5', 'song5' ]
		else:
			raise CommandError

	def status(self):

		return self._status

	def playDummy(self, index):

		self.queue = [self.db[index]]
		self._pos = 0
		self.play()

	def close(self):

		pass

	def disconnect(self):

		pass

	def skipTime(self, interval):

		(time, total) = self._status['time'].split(':')
		time = int(time)
		time = min(time + interval, int(total))
		self._status['time'] = "%s:%s" % (time, total)

	def add(self, file):

		log.info('Trying to add file "%s"' % file)

		for song in self.db:
			log.debug('Checking "%s"', song['file']);
			if song['file'] == file:
				self.queue.append(song)
				log.info('File queued')
				return

		log.error('File not found')

	def delete(self, index):

		self.queue.remove(self.queue[index])
		if self._pos > index:
			self._pos -= 1

		if len(self.queue) < 1:
			self.stop()

	def playlist(self):

		return [ x for x in self.queue]

	def playlistinfo(self):

		return self.queue

	def currentsong(self):

		song = self.queue[self._pos]

		if song != None:
			song['pos'] = self._pos

		return song

	def next(self):

		self._pos += 1

	def search(self, field, value):

		result = []
		field = field.lower()

		for song in self.db:
			if field in song and value in song[field]:
				result.append(song)

		return result

	def find(self, field, value):

		result = []
		field = field.lower()

		for song in self.db:
			if field in song and value == song[field]:
				result.append(song)

		return result

	def list(self, attr):

		result = []

		for song in self.db:
			if attr in song:
				val = song[attr]

				if val not in result:
					result.append(val)

		return result

	def listall(self):

		return self.db

	def clear(self):

		self.queue = []
		self.pos = 0


class TestJukebox(unittest.TestCase):

	def setUp(self):

		self.mpd = FakeMPD()
		self.jukebox = Jukebox(self.mpd)
		config['max_queue_length'] = 3

	def test_timeLeft(self):

		self.assertEqual(0, self.jukebox.timeLeft())

		self.mpd.playDummy(0)
		self.mpd.skipTime(30)
		self.assertEqual(90, self.jukebox.timeLeft())

		self.mpd.playDummy(1)
		self.assertEqual(0, self.jukebox.timeLeft())

		self.mpd.skipTime(30)
		self.assertEqual(0, self.jukebox.timeLeft())

	def test_queue(self):

		self.assertFalse(self.jukebox.isPlaying())

		self.jukebox.queue(self.mpd.db[0]['file'])
		self.assertEqual(1, len(self.jukebox.getPlaylist()))
		self.assertTrue(self.jukebox.isPlaying())

		self.jukebox.queue(self.mpd.db[1]['file'])
		self.assertEqual(2, len(self.jukebox.getPlaylist()))
		self.assertTrue(self.jukebox.isPlaying())

		self.jukebox.queue(self.mpd.db[1]['file'])
		self.assertEqual(3, len(self.jukebox.getPlaylist()))
		self.assertTrue(self.jukebox.isPlaying())

		self.jukebox.queue(self.mpd.db[1]['file'])
		self.assertEqual(4, len(self.jukebox.getPlaylist()))
		self.assertTrue(self.jukebox.isPlaying())

		with self.assertRaises(QueueFull):
			self.jukebox.queue(self.mpd.db[1]['file'])

		self.assertEqual(4, len(self.jukebox.getPlaylist()))
		self.assertTrue(self.jukebox.isPlaying())

	def test_autoQueueRandom(self):

		config['auto_queue_genre'] = None
		config['auto_queue_playlist'] = None

		self.assertEqual(0, len(self.jukebox.getPlaylist()))

		self.jukebox.autoQueue()
		self.assertEqual(1, len(self.jukebox.getPlaylist()))

	def test_autoQueueGenre(self):

		config['auto_queue_genre'] = 'Genre 5'
		config['auto_queue_playlist'] = None

		self.assertEqual(0, len(self.jukebox.getPlaylist()))

		self.jukebox.autoQueue()
		self.assertEqual(1, len(self.jukebox.getPlaylist()))

		track = self.jukebox.getCurrentSong()
		self.assertEqual(track.title, 'Track 5')

	def test_autoQueuePlaylist(self):

		config['auto_queue_genre'] = None
		config['auto_queue_playlist'] = 'test01'
		self.assertEqual(0, len(self.jukebox.getPlaylist()))

		self.jukebox.autoQueue()
		self.assertEqual(1, len(self.jukebox.getPlaylist()))

		config['auto_queue_playlist'] = 'test_does_not_exist'
		self.jukebox.autoQueue()

		self.assertEqual(1, len(self.jukebox.getPlaylist()))

	def test_searchGenre(self):

		config['find_over_search'] = False

		tracks = self.jukebox.search('genre', 'Genre 5')
		self.assertEqual(1, len(tracks));

		tracks = self.jukebox.search('Genre', 'Genre 5')
		self.assertEqual(1, len(tracks));

	def test_find_vs_search(self):

		config['find_over_search'] = False

		tracks = self.jukebox.search('genre', 'Genre')
		self.assertNotEqual(0, len(tracks))

		tracks = self.jukebox.search('genre', 'Genre 5')
		self.assertNotEqual(0, len(tracks))

		config['find_over_search'] = True

		tracks = self.jukebox.search('genre', 'Genre')
		self.assertEqual(0, len(tracks))

		tracks = self.jukebox.search('genre', 'Genre 5')
		self.assertNotEqual(0, len(tracks))

	def test_forceSearch(self):

		config['find_over_search'] = True

		tracks = self.jukebox.search('genre', 'Genre', forceSearch = True)
		self.assertNotEqual(0, len(tracks))

		tracks = self.jukebox.search('genre', 'Genre 5', forceSearch = True)
		self.assertNotEqual(0, len(tracks))

	def test_searchArtist(self):

		config['find_over_search'] = False

		tracks = self.jukebox.search('artist', 'Artist 1')
		self.assertEqual(3, len(tracks));

	def test_getPlaylist(self):

		self.jukebox.queue(self.mpd.db[0]['file'])

		playlist = self.jukebox.getPlaylist()
		log.info(playlist)

		self.assertEqual(1, len(playlist))
		self.assertEqual('Track 1', playlist[0].get('title'))

	def test_getGenres(self):

		genres = [x.name for x in self.jukebox.getGenres()]

		self.assertIn('Genre 1', genres)
		self.assertIn('Genre 5', genres)

	def test_getArtists(self):

		artists = [x.name for x in self.jukebox.getArtists()]

		self.assertIn('Artist 1', artists)
		self.assertIn('Artist 2', artists)
		self.assertIn('Artist 5', artists)

	def test_isPlaying(self):

		self.assertFalse(self.jukebox.isPlaying())
		self.jukebox.queue(self.mpd.db[0]['file'])
		self.assertTrue(self.jukebox.isPlaying())

	def test_getCurrentSong(self):

		self.jukebox.queue(self.mpd.db[0]['file'])
		song = self.jukebox.getCurrentSong()

		self.assertEqual('Track 1', song.title)

	def test_getQueueLength(self):

		self.assertEqual(0, self.jukebox.getQueueLength())

		self.jukebox.queue(self.mpd.db[0]['file'])
		self.assertEqual(0, self.jukebox.getQueueLength())

		self.jukebox.queue(self.mpd.db[0]['file'])
		self.assertEqual(1, self.jukebox.getQueueLength())

		self.jukebox.queue(self.mpd.db[0]['file'])
		self.assertEqual(2, self.jukebox.getQueueLength())

	def test_remove(self):

		song1 = self.mpd.db[0]
		song2 = self.mpd.db[1]
		song3 = self.mpd.db[2]

		self.assertEqual(0, len(self.jukebox.getPlaylist()))

		self.jukebox.queue(song1['file'])
		self.jukebox.queue(song2['file'])
		self.jukebox.queue(song3['file'])

		self.assertEqual(2, self.jukebox.getQueueLength())
		playlist = self.jukebox.getPlaylist()
		self.assertEqual(song1, playlist[0])
		self.assertEqual(song2, playlist[1])
		self.assertEqual(song3, playlist[2])

		self.jukebox.remove(1)

		self.assertEqual(1, self.jukebox.getQueueLength())
		playlist = self.jukebox.getPlaylist()
		self.assertEqual(song1, playlist[0])
		self.assertEqual(song3, playlist[1])

		self.jukebox.remove(0)

		self.assertEqual(0, self.jukebox.getQueueLength())
		playlist = self.jukebox.getPlaylist()
		self.assertEqual(song3, playlist[0])

class TestTrack(unittest.TestCase):

	def setUp(self):

		self.mpd = FakeMPD()

	def test_trackNum(self):

		track = Track()
		track.load(self.mpd.db[0])
		assert track.track == 1

		track = Track()
		track.load(self.mpd.db[2])
		assert track.track == 2

		track = Track()
		track.load(self.mpd.db[3])
		assert track.track == 11
