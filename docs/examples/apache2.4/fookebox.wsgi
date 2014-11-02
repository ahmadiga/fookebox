BASE_PATH = '/usr/share/fookebox'
APP_CONFIG = '/etc/fookebox/config.ini'

import sys

if BASE_PATH not in sys.path:
	sys.path.append(BASE_PATH)

from paste.deploy import loadapp
application = loadapp('config:' + APP_CONFIG)
