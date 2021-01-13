<?php
require __DIR__ . '/../autoload.php';
require __DIR__ . '/MyCodeCoverage.php';


$options=[
	//
];

MyCodeCoverage::G()->init($options);

//生成文档
//RefFileGenerator::Run();
return;
//*/
//-----------------------------------------------



// 生成 doc 文档的类
class RefFileGenerator
{
    public static function Run()
    {
        $source=realpath(__DIR__.'/../src').'/';
        $dest=realpath(__DIR__.'/../doc/ref').'/';
        //$source=realpath($source).'/';
        //$dest=realpath($dest).'/';
        
        $directory = new \RecursiveDirectoryIterator($source, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = \iterator_to_array($iterator, false);
        $classes=[];
        foreach ($files as $file) {
            $short_file=substr($file, strlen($source));
            if(substr($short_file,0,1)==='.'){
                continue;
            }
            $class =str_replace(['/','.php'],['\\',''],$short_file);
            //$classes[]=$class;
            
            $file_name=$dest.str_replace(['/','.php'],['-','.md'],$short_file);
            if (is_file($file_name)) {
                echo "Skip Existed File:".$file_name."\n";
                continue;
            }
            $data=static::getTemplate($class);
            file_put_contents($file_name,$data);
        }
        /*
        $index_file=$dest.'index.md';
        if(is_file($index_file)){
            return;
        }
        $str="# Index\n ## Classes\n";
        foreach($classes as $class){
            $str.="* $class \n";
        }
        file_put_contents($index_file,$str);
        //*/
    }
    public static function genIndex()
    {
        $source=realpath(__DIR__.'/../src').'/';
        $dest=realpath(__DIR__.'/../doc/ref').'/';
        //$source=realpath($source).'/';
        //$dest=realpath($dest).'/';
        
        $directory = new \RecursiveDirectoryIterator($source, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = \iterator_to_array($iterator, false);
        $classes=[];
        foreach ($files as $file) {
            $short_file=substr($file, strlen($source));
            if(substr($short_file,0,1)==='.'){
                continue;
            }
            $class =str_replace(['/','.php'],['\\',''],$short_file);
            $classes[]=$class;
        }
        $str='';
        foreach($classes as $class){
            $file=str_replace('\\','-',$class).'.md';
            $str.="* [$class]({$file}) \n";
        }
        echo $str;
    }
    public static function getTemplate($class)
    {
        $ret=<<<'EOT'
# {ClassName}

## 简介

## 选项

## 公开方法


## 详解


EOT;
'EOT';
        $ret=str_replace('{ClassName}',$class,$ret);
        return $ret;
    }
}