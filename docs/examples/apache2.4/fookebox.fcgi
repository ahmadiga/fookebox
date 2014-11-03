#!/usr/bin/python
#
# requires the python flup module

BASE_PATH = '/usr/share/fookebox'
APP_CONFIG = '/etc/fookebox/config.ini'

import sys
if BASE_PATH not in sys.path:
	sys.path.append(BASE_PATH)

from paste.deploy import loadapp
app = loadapp('config:' + APP_CONFIG)

from flup.server.fcgi import WSGIServer
WSGIServer(app).run()
