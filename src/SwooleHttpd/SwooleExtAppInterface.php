<?php
interface SwooleExtAppInterface
{
    public static function G($object=null);
    public function init($options=[], $context=null);
    public function run();
    public function addBeforeRunHandler();
    public function getDynamicComponentClasses();
    public function getStaticComponentClasses();
}
