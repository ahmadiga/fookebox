#!/bin/sh

VERSION=$( grep VERSION config/general.conf.php | cut -d "'" -f 4 )

echo "Preparing files"

rm -f skins/compiled/* || exit 1
find . -type d -exec chmod 0755 {} \; || exit 2
find . -type f -exec chmod 0644 {} \; || exit 3
chmod 0777 skins/compiled/ || exit 4
chmod +x release.sh || exit 5

tarfile="fookebox-${VERSION}.tar"

echo "Verison: ${VERSION}"

echo "Creating archive: ${tarfile}"
tar cf fookebox-${VERSION}.tar . \
	--exclude=.svn \
	--exclude=skins/compiled/* \
	--exclude=config/site.conf.php \
	--exclude=fookebox-${VERSION}.tar \
	--exclude=*.swp \
	--exclude=release.sh \
	|| exit 6

echo "Compressing archive"
bzip2 fookebox-${VERSION}.tar || exit 7

echo "All done"
