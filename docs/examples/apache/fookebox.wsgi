BASE_PATH = '/home/stefan/projects/fookebox/trunk'
APP_CONFIG = '/home/stefan/projects/fookebox/trunk/development-wsgi.ini'

import sys

if BASE_PATH not in sys.path:
	sys.path.append(BASE_PATH)

from paste.deploy import loadapp
application = loadapp('config:' + APP_CONFIG)
