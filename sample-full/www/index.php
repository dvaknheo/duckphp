<?php
require('../../DNMVCS/DNMVCS.php');
$path=realpath('../');
DNMVCS::G()->init($path);
DNMVCS::G()->run();
