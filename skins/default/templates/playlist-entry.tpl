{$artist} - {$title}
{ if $smarty.const.enable_song_removal }
<a href="#" onclick="javascript:removeTrack ({$position}); return false;"> <img src="{$smarty.const.base_url}skins/{$smarty.const.skin}/img/delete.png" alt="x" title="remove this track from the playlist" /></a>
{ /if }
