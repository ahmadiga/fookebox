<div id="now">
	<span class="label">now playing</span><br />
	<span id="currentTitle">{$current->getAsCurrent()}</span>
</div>
{assign var=state value=$current->getState()}
<div class="state">
	<span id="currentState">
		{$state}
	</span>
</div>
{if $next}
<div id="next">
	<span class="label">coming up</span><br />
	<span id="nextTitle">{$next->getAsNext()}</span>
	<br /><span class="time">@<span id="nextTime">{$next->getTime()}</span></span>
</div>
{else}
<div id="next" style="display: none">
	<span class="label">coming up</span><br />
	<span id="nextTitle"></span>
	<br /><span class="time">@<span id="nextTime"></span></span>
</div>
{/if}
<div id="clock"></div>
<script type="text/javascript">
<!--
	setTimeout('refreshProgram()', 1000);
-->
</script>
