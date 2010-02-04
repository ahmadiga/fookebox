<?php

require_once (realpath (dirname (__FILE__) . '/config.inc.php'));

define ('CURRENT_EVENT', 0);

global $_PROGRAM;

$_PROGRAM [0] = new JukeboxEvent('19:30');
$_PROGRAM [1] = new BandEvent	('23:30', 'The Freak');
$_PROGRAM [2] = new JukeboxEvent('01:00');
$_PROGRAM [3] = new DJEvent	('02:00', 'Colonel Panique');
$_PROGRAM [4] = new JukeboxEvent('03:30');

?>
