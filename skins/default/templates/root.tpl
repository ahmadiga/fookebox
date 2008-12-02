<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html xml:lang="en">
	<head>
		<title>{$smarty.const.site_name}</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" href="{$smarty.const.base_url}skins/{$smarty.const.skin}/style.css" type="text/css" media="screen" />
{if $ie}
		<link rel="stylesheet" href="{$smarty.const.base_url}style-ie.css" type="text/css" media="screen" />
{/if}
		<link rel="shortcut icon" href="{$smarty.const.base_url}favicon.ico" type="image/x-icon" />
{if $id}
		<link rel="alternate" title="News RSS" href="{$smarty.const.base_url}{$id}/feed" type="application/rss+xml" />
{/if}
		<script type="text/javascript" src="{$smarty.const.base_url}js/libdesire/libdesire.js"></script>
		<script type="text/javascript" src="{$smarty.const.base_url}js/fookebox.js"></script>
		<script type="text/javascript" src="{$smarty.const.base_url}js/prototype/prototype.js"></script>
		<script type="text/javascript" src="{$smarty.const.base_url}js/scriptaculous/scriptaculous.js?load=effects"></script>
		<script type="text/javascript">
		<!--
			var base_url="{$base_url}"
		-->
		</script>
	</head>
	<body id="body">
		<div id="message" style="display: none">
			<div class="corner tl"></div>
			<div class="corner tr"></div>
			<div class="corner bl"></div>
			<div class="corner br"></div>
			<div id="messageText">{$message}</div>
		</div>
{if !$hideHeader}
		<h1 id="h1"><a href="{$smarty.const.base_url}">{$title}</a></h1>
{/if}
{$body}
	</body>
</html>
