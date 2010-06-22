<h2>${what}</h2>
% if len(albums) < 1:
	Nothing found
% endif
<ul style="list-style-type: none; padding-left: 0">
% for album in albums:
	<li style="overflow: auto; width: 99%; background: #222222; margin: 2px">
% if config.get('album_cover_path'):
%	if album.hasCover():
		<img src="cover/${album.getCoverURI()}" width="200" style="float: right; clear: both; margin: 10px" alt="" />
% 	endif
% endif
		<h3 class="album">
% if where == 'genre':
	${album.artist}
% endif
% if config.get('enable_queue_album'):
			<a href="#" onclick="
% for track in album.tracks:
				queueFile('${track.b64}');
% endfor
				return false
			">
% endif
				${album.name}
% if album.disc != None:
				(Disc ${album.disc})
% endif
% if config.get('enable_queue_album'):
				</a>
% endif
		</h3>
		<ul style="padding-bottom: 10px">
% for track in album.tracks:
			<li class="track"><a href="#" onclick="javascript:queueFile('${track.b64}'); return false">${"%02d" % track.track} - ${track.artist} - ${track.title}</a></li>
% endfor
		</ul>
	</li>
% endfor
</ul>
