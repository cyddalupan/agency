<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use \Eventviva\ImageResize;
use Storage;

class OptimizeImage extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'optimizeimage';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Reduce Image Size Of Images in files';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		//Settings
		$dir = '../files';


		$this->info("Optimization Started");
		$this->info("Getting List of All Images");
		function listFolderFiles($dir){
			$allimage = array();
			$ffs = scandir($dir);
			foreach($ffs as $ff){
				if($ff != '.' && $ff != '..' && $ff != '.DS_Store'){
					if(is_dir($dir.'/'.$ff)){
						$currlist = listFolderFiles($dir.'/'.$ff);
						foreach ($currlist as $curr) {
							$allimage[] = $ff.'/'.$curr;
						}
					}else{
						$allimage[] = $ff;
					}
				}
			}
			return $allimage;
		}
		$allimage = listFolderFiles($dir);
		$totalcount = count($allimage);
		$this->info("Done.");

		$currentcount = 0;
		foreach ($allimage as $image) {
			$currentcount++;
			$imgpath = $dir.'/'.$image;
			$this->info("-----------------------------------");
			$this->info("Checking : ".$imgpath);
			$size = getimagesize( $imgpath );
			$this->info("Width = ".$size[0]."| Height = ".$size[1]);

			//check extension
			if($size[2] < 4){
				if($size[0] < 701 && $size[1] < 701){
					$this->info("Passed!");
				}else{
					$this->error('Converting...');
	
					$image = new ImageResize($imgpath);
					$image->resizeToBestFit(700, 700);
					$image->save($imgpath);
					$this->info('Converted!');
				}
				$percent = ($currentcount / $totalcount) * 100;
				$this->info($percent.'%');
			}else{
				$this->info("Skipping bmp");
			}
		}

		$this->info('Everything is Done.');

	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
		];
	}

}
