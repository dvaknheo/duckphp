<?php
namespace DNMVCS;
class ComposerScripts
{
	function DumpDir($source, $dest) 
	{
		$source=realpath($source);
		$dest=rtrim($dest,DIRECTORY_SEPARATOR);
		$directory = new \RecursiveDirectoryIterator($source,FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS );
		$iterator = new \RecursiveIteratorIterator($directory);
		$files = iterator_to_array($iterator,false);
		foreach($files as $file){
			
			$short_file_name=substr($file,strlen($source)+1);
			$blocks=explode(DIRECTORY_SEPARATOR,$short_file_name);
			array_pop($blocks);
			$full_dir=$dest;
			foreach($blocks as $t){
				$full_dir.=DIRECTORY_SEPARATOR.$t;
				if(!is_dir($full_dir)){
					@mkdir($full_dir);
				}
			}
			copy($file,$dest.DIRECTORY_SEPARATOR.$short_file_name);
		}
	}
	public static function PostUpdate()
	{
		echo "Post Update!";
		if(is_file('public/index.php')){return;}
		$source=__DIR__.DIRECTORY_SEPARATOR.'sample';
		$dest=getcwd();
		self::DumpDir($source, $dest);
		var_dump(DATE(DATE_ATOM));
	}
}
