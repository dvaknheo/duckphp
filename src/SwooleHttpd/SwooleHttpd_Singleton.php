<?php
namespace SwooleHttpd;

trait SwooleHttpd_Singleton
{
    public static function ReplaceDefaultSingletonHandler()
    {
        return SwooleCoroutineSingleton::ReplaceDefaultSingletonHandler();
    }
    public static function EnableCurrentCoSingleton()
    {
        return SwooleCoroutineSingleton::EnableCurrentCoSingleton();
    }
    public function getDynamicClasses()
    {
        $classes=[
            SwooleSuperGlobal::class,
            SwooleContext::class,
        ];
        return $classes;
    }
    public function forkMasterInstances($classes, $exclude_classes=[])
    {
        return SwooleCoroutineSingleton::G()->forkMasterInstances($classes, $exclude_classes);
    }
    public function resetInstances()
    {
        $classes=$this->getDynamicClasses();
        $instances=[];
        foreach ($classes as $class) {
            $instances[$class]=$class::G();
        }
        
        SwooleCoroutineSingleton::G()->forkAllMasterClasses();
        
        foreach ($classes as $class) {
            $class::G($instances[$class]);
        }
    }
}
