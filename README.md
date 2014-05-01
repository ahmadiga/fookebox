## About

fookebox is a jukebox-style web-frontend to mpd.

![Screenshot](http://freecode.com/screenshots/e8/ba/e8baf6a7bff08e622a2f4e545340848f_medium.png?1292032266)

## Installation and Setup

### Packages

fookebox can be downloaded from [PyPI](http://pypi.python.org/pypi/fookebox/) and there are packages available for [Debian GNU/Linux](http://packages.debian.org/fookebox) and [Ubuntu](http://packages.ubuntu.com/fookebox).

### Using easy_install

```
easy_install fookebox
```

Make a config file as follows::

```
paster make-config fookebox config.ini
```

Tweak the config file as appropriate and then setup the application::

```
paster setup-app config.ini
```

Then you are ready to go.

### Release notification

To be informed about new releases, please subscribe to the project at the [freecode project page](http://freecode.com/projects/fookebox).
