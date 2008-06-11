<?php
	require_once (realpath (dirname (__FILE__) . '/config.inc.php'));
	require_once (src_path . '/JukeboxEvent.inc.php');
	require_once (src_path . '/BandEvent.inc.php');
	require_once (src_path . '/DJEvent.inc.php');

	define ('CURRENT_EVENT', 0);

	$_PROGRAM [0] = new JukeboxEvent('19:30');
	$_PROGRAM [3] = new BandEvent	('21:30', 'Licon');
	$_PROGRAM [2] = new JukeboxEvent('23:00');
	$_PROGRAM [1] = new BandEvent	('23:30', 'The Freak');
	$_PROGRAM [4] = new JukeboxEvent('01:00');
	$_PROGRAM [5] = new DJEvent	('02:00', 'Colonel Panique');
	$_PROGRAM [6] = new JukeboxEvent('03:30', 'EOF (end of foobar)');

	global $_PROGRAM;
?>
