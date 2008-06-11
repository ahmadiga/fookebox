<div id="playlist">
	<h2><img src="{$smarty.const.base_url}skins/{$smarty.const.skin}/img/pictures.png" /> Queue</h2>
	<ol>
{section name=li loop=$smarty.const.max_queue_length-1}
		<li>&nbsp;</li>
{/section}
	</ol>
</div>
