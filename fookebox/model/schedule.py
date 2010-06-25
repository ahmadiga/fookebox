import sqlalchemy as sa
from sqlalchemy import orm

from pylons import config
from fookebox.model import meta

t_event = sa.Table("Events", meta.metadata,
	sa.Column("id", sa.types.Integer, primary_key=True),
	sa.Column("type", sa.types.String(100), nullable=False),
	sa.Column("name", sa.types.String(100), nullable=False),
	sa.Column("time", sa.types.Time, nullable=False),
)

class Event(object):
	def getAsCurrent(self):
		if self.type == 0:
			currentSong = self.jukebox.getCurrentSong()
			return "%s - %s" % (currentSong['artist'],
					currentSong['title'])
		elif self.type == 1:
			return "%s [LIVE]" % self.name
		elif self.type == 2:
			return "%s [DJ]" % self.name

	def getAsCurrentState(self):
		if self.type == 0:
			return "%s jukebox" % config.get('site_name')
		else:
			return "live @ %s" % config.get('site_name')

	def getAsNext(self):
		if self.type == 0:
			return self.getAsCurrentState()
		elif self.type == 1:
			return "LIVE BAND: %s" % self.name
		elif self.type == 2:
			return "%s [DJ]" % self.name

	def getTime(self):
		return self.time.strftime("%H:%M")

orm.mapper(Event, t_event)

