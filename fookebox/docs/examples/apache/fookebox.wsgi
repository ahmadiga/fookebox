BASE_PATH = '/home/stefan/projects/fookebox/trunk/fookebox'
APP_CONFIG = BASE_PATH + '/production.ini'

import os, sys
sys.path.append(BASE_PATH)

from paste.deploy import loadapp
application = loadapp('config:' + APP_CONFIG)
