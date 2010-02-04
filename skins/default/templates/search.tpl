<h2>{$what}</h2>
{if !$albums}
Nothing found
{/if}
<ul style="list-style-type: none; padding-left: 0">
{foreach from=$albums item=album}
<li style="overflow: auto; width: 99%; background: #222222; margin: 2px">
{if $smarty.const.album_cover_path}
	<img src="cover/{$album->getartist()|escape:'url'|escape:'url'}/{$album->getname()|escape:'url'|escape:'url'}" width="200" style="float: right; clear: both; margin: 10px" alt="" />
{/if}
<h3 class="album">{if $where == "genre"}{$album->getartist()} - {/if}{if $smarty.const.enable_queue_album}<a href="#" onclick="{foreach from=$album->getTracks() item=track}queueFile('{$track.file|addslashes}');{/foreach}return false">{/if}{$album->getname()}{if $album->getDisc()} (Disc {$album->getDisc()}){/if}{if $smarty.const.enable_queue_album}</a>{/if}</h3>
<ul style="padding-bottom: 10px">
{	foreach from=$album->getTracks() item=track}
	<li class="track"><a href="#" onclick="javascript:queueFile ('{$track.file|addslashes}'); return false">{$track.Track|string_format:"%02d"} - {$track.Artist} - {$track.Title}</a></li>
{	/foreach}
</ul>
</li>
{/foreach}
</ul>
