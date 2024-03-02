<?php

class Autoloader
{
	public static function register()
	{
		spl_autoload_register(function ($class) {
			$folders = array_diff(scandir('classes'), array('.', '..'));
			foreach ($folders as $folder) {
			if(is_dir($folder)){
				continue;
			}
			$file = "classes/{$folder}/{$class}.php";
			if (file_exists($file)) {
				require $file;
				return true;
			}
			}
		
			return false;
		});
	}
}