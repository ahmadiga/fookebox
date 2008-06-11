<h2>{$where}:
<a href="#" onclick="javascript:
{foreach from=$albums item=album}
{	if $album->getName() == "" || $where == "album"}
{		foreach from=$album->getTracks() item=track}
queueFile ('{$track.file|addslashes}');
{		/foreach}
{	/if}
{/foreach}
return false" title="{$what}">{$what}</a>
</h2>
<ul>
{foreach from=$albums item=album}
{	if $album->getName() == "" || $where == "album"}
{		foreach from=$album->getTracks() item=track}
	<li class="track"><a href="#" onclick="javascript:queueFile ('{$track.file|addslashes}'); return false" title="{$track.file}">{if $track.Track && $where == "album"}{$track.Track} - {/if}{$track.Artist} - {$track.Title}</a></li>
{		/foreach}
{	else}
	<li class="album"><a href="#" onclick="javascript:album{if $smarty.const.find_over_search}Find{else}Search{/if}('{$album->getName()|addslashes}'); return false">{if $where == "genre"}{$album->getArtist()} - {/if}{$album->getName()}</a>{if $where != "genre"} <span style="font-style: italic">[{$album->getArtist()}]</span>{/if}</li>
{	/if}
{/foreach}
</ul>
