<?php
require('../../DNMVCS/DNMVCS.php');
$path=realpath('../');

DNMVCS::G()->autoload($path);

DNMVCS::G(CoreMVCS::G())->init($path)->run();


