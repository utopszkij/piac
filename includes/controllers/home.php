<?php
use \RATWEB\DB\Query;
use \RATWEB\DB\Record;

include_once __DIR__.'/../models/usermodel.php';

class Home extends Controller {

	function __construct() {
		parent::__construct();
		// $this->model = new HomeModel();
        $this->name = "home";
        //$this->browserURL = 'index.php?task=userek';
        //$this->addURL = 'index.php?task=regist';
        //$this->editURL = 'index.php?task=useredit';
        //$this->browserTask = 'userek';
	}

	public function show() {
		$errorMsg = $this->request->input('errorMsg', $this->session->input('errorMsg',''),NOFILTER);
		$successMsg = $this->request->input('successMsg', $this->session->input('successMsg',''),NOFILTER);
		$this->session->set('errorMsg','');
		$this->session->set('successMsg','');

		view('description',[
			"errorMsg" => $errorMsg,
			"successMsg" => $successMsg
		]);
	}

	/**
	 * web hely leírásának megjelenítése
	 */
	public function description() {
		$this->show();
	}
	
	/**
	 * web hely licensz megjelenítése
	 */
	public function licence() {
		view('licence',[]);
	}
	
	/**
	 * web hely adatkezelési leírás megjelenítése
	 */
	public function policy() {
		view('policy',["ADATKEZELO" => ADATKEZELO, 
		"ADATFELDOLGOZO" => ADATFELDOLGOZO, 
		"SIGNO" => SIGNO]);
	}
	
	/**
	 * web hely adatkezelési szabályzat megjelenítése
	 */
	public function policy2() {
		view('policy2',["ADATKEZELO" => ADATKEZELO, 
		"ADATFELDOLGOZO" => ADATFELDOLGOZO, 
		"SIGNO" => SIGNO]);
	}
	
	/**
	 * web hely adatkezelési folyamatok megjelenítése
	 */
	public function policy3() {
		view('policy3',["ADATKEZELO" => ADATKEZELO, 
		"ADATFELDOLGOZO" => ADATFELDOLGOZO, 
		"SIGNO" => SIGNO]);
	}
	
	/**
	 * Jogsértő tartalom jelentése
	 */
	public function protest() {
		view('protest',[]);
	}
	
	/**
	 * impresszum megjelenítése
	 */
	public function impressum() {
		view('impressum',[]);
	}

	/**
	 * szoftver dokumentáció megjelenítése
	 */
	public function swdoc() {
		view('swdoc',[]);
	}

	public function sponzor() {
		view('sponzor',[]);
	}
	
	
}


?>
