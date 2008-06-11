{assign var=artists value=$mpd->getArtists()}
{assign var=genres value=$mpd->getGenres()}
<div id="browse-menu">
	<ul id="selectType">
		<li id="artistTab" class="active"><a href="#" onclick="javascript:setTab ('artist'); return false">Artists</a></li>
		<li id="genreTab" class="inactive"><a href="#" onclick="javascript:setTab ('genre'); return false">Genres</a></li>
{if $smarty.const.show_search_tab}
		<li id="searchTab" class="inactive"><a href="#" onclick="javascript:setTab ('search'); return false">Search</a></li>
{/if}
	</ul>
	<div id="artistList">
		<div id="letterSelector">
{section name=li loop=26}
{	assign var=key value=$smarty.section.li.index+65|chr}
			<a href="#{$key}">{$key}</a>
{/section}
		</div>
	<ul>
{foreach from=$artists item=artist}
{	assign var=char value=$artist|substr:0:1|upper}
		<li{if $prevChar && $char != $prevChar} class="seperator"{/if}><a {if $char != $prevChar}name="{$char}" {/if}href="#" onclick="artist{if $smarty.const.find_over_search}Find{else}Search{/if}('{$artist|addslashes}'); return false">{$artist}</a></li>
{	assign var=prevChar value=$char}
{/foreach}
	</ul>
	</div>
	<ul id="genreList" style="display: none">
{foreach from=$genres item=genre}
		<li><a href="#" onclick="genre{if $smarty.const.find_over_search}Find{else}Search{/if}('{$genre}'); return false">{$genre}</a></li>
{/foreach}
	</ul>
{if $smarty.const.show_search_tab}
	<ul id="searchList" style="display: none">
		<form name="searchform" action="">
			<select name="searchType">
				<option selected value="artist">Artist</option>
				<option value="album">Album</option>
				<option value="title">Title</option>
				<option value="filename">Filename</option>
				<option value="any">Any</option>
			</select>
			<input type="text" name="searchTerm" />
			<input type="submit" value="Search!" onclick="javascript:search(); return false">
		</form>
	</ul>
{/if}
</div>
