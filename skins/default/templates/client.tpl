<div id="meta">
	<a href="http://fookebox.googlecode.com/">fookebox</a> v{$smarty.const.VERSION}<br />
	&copy; 2007-2008 <a href="http://www.ott.net/">Stefan Ott</a>
</div>
{include file="browse-menu.tpl"}
<div id="status">
{include file="status.tpl"}
</div>
<div id="progress" style="display: none">
	<img src="skins/{$smarty.const.skin}/img/progress.gif" alt="" />
	<div id="text">Please wait...</div>
</div>
<div id="searchResult"></div>
<script type="text/javascript">
<!--
	prepareProgressbar ();
	parseLocation();
-->
</script>
