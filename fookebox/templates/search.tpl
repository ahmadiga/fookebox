<!-- TODO: DELETE THIS FILE -->
<h2>${what}</h2>
% if len(albums) < 1:
	Nothing found
% endif
<ul id="searchResultList">
% for album in sorted(albums, key=lambda album: "%s %s %s" % (album.artist, album.name, album.disc)):
	<li class="searchResultItem">
% if config.get('album_cover_path'):
%	if album.hasCover():
		<img src="cover/${album.getCoverURI()}" width="200" class="coverArt" alt="" />
% 	endif
% endif
		<h3 class="album">
% if config.get('enable_queue_album'):
			<a href="#">
% endif
${ album.artist } - ${album.name}
% if album.disc != None:
				(Disc ${album.disc})
% endif
% if config.get('enable_queue_album'):
				</a>
% endif
		</h3>
		<ul class="trackList">
% for track in album.tracks:
			<li class="track"><a href="#" id="track-${track.b64}">${"%02d" % track.track} - ${track.artist} - ${track.title}</a></li>
% endfor
		</ul>
	</li>
% endfor
</ul>
