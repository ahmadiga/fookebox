try:
    from setuptools import setup, find_packages
except ImportError:
    from ez_setup import use_setuptools
    use_setuptools()
    from setuptools import setup, find_packages

setup(
    name='fookebox',
    version='0.5.0',
    description='A jukebox-style web-frontend to mpd',
    long_description="""fookebox is a jukebox-style web-frontend to mpd.

It can be used as a keyboard-less jukebox, as a powerful
mpd control frontend or as anything in between.""",
    author='Stefan Ott',
    author_email='stefan@ott.net',
    url='http://fookebox.googlecode.com/',
    install_requires=[
        "Pylons>=0.10",
    ],
    keywords='mpd jukebox web music party',
    setup_requires=["PasteScript>=1.6.3"],
    packages=find_packages(exclude=['ez_setup']),
    include_package_data=True,
    test_suite='nose.collector',
    package_data={'fookebox': ['i18n/*/LC_MESSAGES/*.mo']},
    license='GPLv3',
    #message_extractors={'fookebox': [
    #        ('**.py', 'python', None),
    #        ('templates/**.mako', 'mako', {'input_encoding': 'utf-8'}),
    #        ('public/**', 'ignore', None)]},
    zip_safe=False,
    paster_plugins=['PasteScript', 'Pylons'],
    entry_points="""
    [paste.app_factory]
    main = fookebox.config.middleware:make_app

    [paste.app_install]
    main = pylons.util:PylonsInstaller
    """,
)