<div id="nowPlaying">
	<h2><img src="{$smarty.const.base_url}skins/{$smarty.const.skin}/img/sound.png" /> Now playing</h2>
	<ol>
		<li id="artist"></li>
		<li id="track"></li>
		<li id="time"><span id="timePassed"></span> / <span id="timeTotal"></span></li>
{if $smarty.const.enable_controls}
		<li id="control">
			<a href="#" onclick="javascript:control('prev'); return false"><img src="{$smarty.const.base_url}skins/{$smarty.const.skin}/img/control_prev.png" alt="back" title="back" /></a>
			<a href="#" onclick="javascript:control('pause'); return false"><img src="{$smarty.const.base_url}skins/{$smarty.const.skin}/img/control_pause.png" alt="pause" title="pause" /></a>
			<a href="#" onclick="javascript:control('play'); return	false"><img src="{$smarty.const.base_url}skins/{$smarty.const.skin}/img/control_play.png" alt="play" title="play" /></a>
			<a href="#" onclick="javascript:control('next'); return false"><img src="{$smarty.const.base_url}skins/{$smarty.const.skin}/img/control_next.png" alt="next" title="next" /></a>
			<a href="#" onclick="javascript:control('voldown'); return false"><img src="{$smarty.const.base_url}skins/{$smarty.const.skin}/img/control_voldown.png" alt="volume down" title="volume down" /></a>
			<a href="#" onclick="javascript:control('volup'); return false"><img src="{$smarty.const.base_url}skins/{$smarty.const.skin}/img/control_volup.png" alt="volume up" title="volume up" /></a>
			<a href="#" onclick="javascript:control('rebuild'); return false"><img src="{$smarty.const.base_url}skins/{$smarty.const.skin}/img/control_rebuild.png" alt="rebuild database" title="rebuild database" /></a>
		</li>
{/if}
	</ol>
</div>
