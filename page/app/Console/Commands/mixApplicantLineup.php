<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use App\applicant;
use App\multiple_lineup;

class mixApplicantLineup extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'command:dbclean1';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Clean Database, Move Applicants table to Line_up table, (Should be done once)';

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
		$applicants = applicant::where('applicant_status', 5)->get();
		$this->info('Cleaning Table');
		foreach ($applicants as $key =>$applicant) {
			$this->info('Looping ='.$applicant->applicant_id);
			if($applicant->applicant_employer != 0){
				$this->info($applicant->applicant_id.' adding to lineup');
				$multiple_lineup = new multiple_lineup;
				$multiple_lineup->applicant_id = $applicant->applicant_id;
				$multiple_lineup->applicant_employer = $applicant->applicant_employer;
				$multiple_lineup->save();
				$this->info($applicant->applicant_id.' updated');
			}
		}
	}

}
