<?php
namespace DNMVCS;
class ComposerScripts
{
	protected static function DumpDir($source, $dest) 
	{
		$source=realpath($source);
		$dest=rtrim($dest,DIRECTORY_SEPARATOR);
		$directory = new \RecursiveDirectoryIterator($source,\FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS );
		$iterator = new \RecursiveIteratorIterator($directory);
		$files = \iterator_to_array($iterator,false);
		foreach($files as $file){
			
			$short_file_name=substr($file,strlen($source)+1);
			$blocks=explode(DIRECTORY_SEPARATOR,$short_file_name);
			array_pop($blocks);
			$full_dir=$dest;
			foreach($blocks as $t){
				$full_dir.=DIRECTORY_SEPARATOR.$t;
				if(!is_dir($full_dir)){
					mkdir($full_dir);
				}
			}
			copy($file,$dest.DIRECTORY_SEPARATOR.$short_file_name);
		}
	}
	protected static function ChangeFlag($file)
	{
		$data=file_get_contents($file);
		$data=str_replace('$IN_COMPOSER=false;','$IN_COMPOSER=true;',$data);
		file_put_contents($file,$data);
	}
	protected static function DumpTemplateFiles()
	{
		if(is_file('dnmvcs-installed.lock')){return;}
		if(is_file('public/index.php')){return;}
		
		$source=__DIR__.DIRECTORY_SEPARATOR.'template';
		$dest=getcwd();
		self::DumpDir($source, $dest);
		
		self::ChangeFlag('public/index.php');
		self::ChangeFlag('public/OneFile.php');
		self::ChangeFlag('bin/start_server.php');
		copy('config/setting.sample.php','config/setting.php');
		$data="DNMVCS Installed at ".DATE(DATE_ATOM)."\n";
		file_put_contents('dnmvcs-installed.lock',$data);
	}
	public static function PostCreateProject()
	{
		self::DumpTemplateFiles();
	}
	public static function PostUpdate()
	{
		self::DumpTemplateFiles();
	}
}
