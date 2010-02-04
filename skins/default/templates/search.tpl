<h2>{$what}</h2>
{if !$albums}
Nothing found
{/if}
{foreach from=$albums item=album}
<h3 class="album">{if $where == "genre"}{$album->getartist()} - {/if}{if $smarty.const.enable_queue_album}<a href="#" onclick="{foreach from=$album->getTracks() item=track}queueFile('{$track.file|addslashes}');{/foreach}return false">{/if}{$album->getname()}{if $album->getDisc()} (Disc {$album->getDisc()}){/if}{if $smarty.const.enable_queue_album}</a>{/if}</h3>
<ul>
{	foreach from=$album->getTracks() item=track}
	<li class="track"><a href="#" onclick="javascript:queueFile ('{$track.file|addslashes}'); return false">{$track.Track|string_format:"%02d"} - {$track.Artist} - {$track.Title}</a></li>
{	/foreach}
</ul>
{/foreach}
