
<?php					
use \RATWEB\DB\Query;
use \RATWEB\DB\Record;
use \RATWEB\DB\Table;

/*

a githubról file elérési példa includes/napimenu.php:

https://raw.githubusercontent.com/utopszkij/szakacskonyv/main/includes/napimenu.php

a github/readm.md -t használja:
## verzió v#.#
... 
### *************

*/
class Upgrade {

	protected $github =       'https://raw.githubusercontent.com/utopszkij/utopszkij_fw/main/';
	protected $githubReadme = ''; // __constructor állítja be
	public $branch = 'main';
	protected $msg = '';
	protected $errorCount = 0;
	protected $info = '';

	function __construct() {
		// fejlesztő környezetben ?branch=xxx URL paraméterrel cserélhető
		// a github alapértelmezett "main" branch
		if (isset($_GET['branch'])) {
			$this->branch = $_GET['branch'];
			$_SESSION['branch'] = $this->branch;
			$this->github = 'https://raw.githubusercontent.com/utopszkij/utopszkij_fw/'.$this->branch.'/';
		} else if (isset($_SESSION['branch'])) {
			$this->branch = $_SESSION['branch'];
			$_SESSION['branch'] = $this->branch;
			$this->github = 'https://raw.githubusercontent.com/utopszkij/utopszkij_fw/'.$this->branch.'/';
		}
		$this->githubReadme = $this->github.'readme.md';
	}

	/**
	 * verzió átalakitása v##.##.## formára (az összehasonlíthatóság kedvéért)
	 */
	public static function versionAdjust(string $version):string {
		$w = explode('.',substr($version,1,100));
		foreach ($w as $i => $w1) {
			if (strlen($w1) < 2) {
				$w[$i] = ' '.$w1;
			}
		}
		return 'v'.implode('.',$w);
	}

	/**
	 * last verzio olvasása github readme -ből
	 */
	public function getLastVersion() {
		$result = 'v0.0';
		$lines = file($this->githubReadme);
		// keresi az új verio sort
		for ($i=0; (($i<count($lines)) & 
		            (strpos(strtolower($lines[$i]), '# verzió ') <= 0)); $i++) {
						//echo 'ciklusban '.$lines[$i].'<br>';
		}
		// echo 'ciklus után '.$lines[$i].'<br>'; exit();
		if ($i < count($lines)) {
			$w = explode(' ', strtolower($lines[$i]));
			if (count($w) > 2) {
				$result = trim($w[2]);
			}	
		}
		return $result;
	}
	
	/**
	 * meglévő fájl felülírása
	 * images -ben nem csináljuk
	 */
	protected function updateFile($path) {
		if (strpos(' '.$path,'images/') > 0) { 
			return;
		}	
		try {
			if (!is_dir(dirname(DOCROOT.'/'.$path))) {
				mkdir(dirname(DOCROOT.'/'.$path),0777);
			}
			if (file_exists(DOCROOT.'/'.$path.'.old')) {
				unlink(DOCROOT.'/'.$path.'.old');
			}	
			rename(DOCROOT.'/'.$path, 
			       DOCROOT.'/'.$path.'.old');
			$this->downloadFile($path);
			// végül is hiba esetén jól jöhet az old file... unlink(DOCROOT.'/'.$path.'.old');
		} catch (Exception $e) {	
			$this->errorCount++;
			$this->msg .= 'ERROR update '.$path.' '.JSON_encode($e).'<br>';
		}		
	}

	/**
	 * Új fájl letöltése
	 * images -ben nem csináljuk
	 */
	protected function downloadFile($path) {
		if (strpos(' '.$path,'images/') > 0) { 
			return;
		}	
		try {
			if (!is_dir(dirname(DOCROOT.'/'.$path))) {
				mkdir(dirname(DOCROOT.'/'.$path),0777);
			}
			$lines = file($this->github.$path);		
			$fp = fopen(DOCROOT.'/'.$path,'w+');
			fwrite($fp, implode("",$lines));
			fclose($fp);
		} catch (Exception $e) {	
			$this->errorCount++;
			$this->msg .= 'ERROR download '.$path.' '.JSON_encode($e).'<br>';
		}		
	}

	/**
	 * meglévő fájl törlése
	 * images -ben nem csináljuk
	 */
	protected function delFile($path) {
		if ((strpos($path,'images/') === false) & (file_exists($path))) { 
			unlink($path);
		}
	}

	/**
	 * adatbázis verzió lekérdezése
	 * @return string
	 */
	public function getDBVersion():string {
		$q = new Query('dbverzio');
		$q->exec('create table if not exists dbverzio (
			verzio varchar(32)
		)');
		$q = new Query('dbverzio');
		$w = $q->first();
		if (isset($w->verzio)) {
			$dbverzio = $w->verzio;
		} else {
			$dbverzio = 'v0.0';
			$r = new Record();
			$r->verzio = 'v0.0';
			$q->insert($r);
		}
		return $dbverzio;
	}

	/**
	 * könyvtár írható? kiirása a képernyőre
	 */
	protected function echoWritable(string $path) {
		if (is_writable($path.'/index.php')) {
			echo $path.' írható<br />';
		} else {
			echo $path.' nem írható<br>';
		}
	}
	
	/**
	 * változott fájlok listázása
	 * GET param: version
 	*/
	public function upgrade1() {
		$version = $_GET['version'];
		// a files.txt és a github -on lévő files.txt -ből változás infó olvasása
		$files = $this->getNewFilesList($this->githubReadme, $version);
		
		?>
		<div class="upgrade">
			<h2>Új verzió <?php echo $version; ?></h2>
			<div><?php echo $this->info; ?></div>
			<p> </p>
			<div class="changedFiles">
				<?php $actions = $this->listChangedFiles(); ?>
			</div>
			<div style="background-color:silver; padding:10px;">
				<?php
				$this->echoWritable(DOCROOT);
				$this->echoWritable(DOCROOT.'/includes');
				$this->echoWritable(DOCROOT.'/vendor');
				$this->echoWritable(DOCROOT.'/images');
				$this->echoWritable(DOCROOT.'/includes/controllers');
				$this->echoWritable(DOCROOT.'/includes/models');
				$this->echoWritable(DOCROOT.'/includes/views');
				?>
			</div>
			<p> </p>
			<?php if ($actions > 0) : ?>
				<p>
					<a class="btn btn-secondary" href="index.php">Késöbb</a>&nbsp;
					<a class="btn btn-secondary" href="index.php?task=upgrade2&version=<?php echo $version; ?>">
						A fájlok frissitése most</a>
					<a class="btn btn-secondary" href="index.php?task=upgrade3&version=<?php echo $version; ?>">
						A fájlok frissitést megcsináltam</a>
				</p>				
				<p><strong>A "fájlok frissitése most" funkció csak akkor használható, ha a web szervernek joga 
					van a könyvtárak, fájlok írásához, törléséhez! 
					Ennek a funkciónak a használata előtt csinálj mentést a működő program változatról!</strong>
				</p>
			<?php else : ?>
				<p>
					<a class="btn btn-secondary" href="index.php?task=upgrade3&version=<?php echo $version; ?>">
						OK
					</a>
				</p>
			<?php endif; ?>
		</div>	
		<?php
	}

	/**
	 * változott fájlok frissitése
	 * GET: version
	 * 
	 * TEST egyenlőre nem aktiv
	 * 
	 */
	public function upgrade2() {
		error_reporting(E_ERROR | E_PARSE);
		$this->errorCount = 0;
		try {	
			$this->upgradeChangedFiles();
		} catch (Exception $e) {	
			$this->errorCount++;
			$this->msg = JSON_encode($e);
		};	
		if ($this->errorCount == 0) {
			echo '
			<div>
				<p>Fájlok frissitése megtörtént</p>
				<a class="btn btn-secondary" href="index.php">
				Tovább
				</a>
			</div>
			';
		} else {
			echo '<p>Hiba lépett fel a fájlok frissitése közben!</p>'.$this->msg;
		}
	}

	/**
	 * file download kézzel meg lett csinálva
	 * GET param: version
	 */
	public function upgrade3() {
		?>
		<script>location="index.php";</script>
		<?php
	}


	/**
	 * Változott  infó olvasása a redame.md -ből
	 */
	protected function getNewFilesList(string $fileUrl, string $newVersion): array {
		$result = [];
		$lines = file($fileUrl);

		// keresi az új verzio sort
		for ($i=0; (($i<count($lines)) & 
		        (strpos(strtolower($lines[$i]), ' verzió '.strtolower($newVersion)) <= 0)); $i++) {
		}

		// keresi a ### sort
		for ($j=$i+1; (($j<count($lines)) & (substr($lines[$j],0,4) != '### ')); $j++) {
			$this->info .= $lines[$j].'<br />';
		}

		/* olvassa a változott fájlokat
		for ($k=$j+1; $k<count($lines); $k++) {
			if (substr($lines[$k],0,1)=='-') {
				$result[] = trim(substr($lines[$k],1,100));
			} else {
				$k = count($lines); // kiléptet a ciklusból
			}
		}
		*/
		return $result;
	} 

	protected function do_v1_0($dbverzio) {
		if ($this->versionAdjust($dbverzio) < 'v 1. 0') {
			/*
			$q = new Query('users');
			$q->exec('CREATE TABLE IF NOT EXISTS `users` (
				`id` int NOT NULL AUTO_INCREMENT,
				`username` varchar(80) default "",
				`password` varchar(80) default "",
				`realname` varchar(80) default "",
				`email` varchar(80) default "",
				`avatar` varchar(128) default "",
				`email_verifyed` int(1) default 0,
				`enabled` int(1) default 1,
				`deleted` int(1) default 0,
				PRIMARY KEY (`id`)
			  ) DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci
			');
			$q->exec('CREATE TABLE IF NOT EXISTS `groups` (
				`id` int NOT NULL AUTO_INCREMENT,
				`parent` int,
				`name` varchar(80) default "",
				PRIMARY KEY (`id`)
			  ) DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci
			');
			$q->exec('CREATE TABLE IF NOT EXISTS `user_group` (
				`id` int NOT NULL AUTO_INCREMENT,
				`user_id` int,
				`group_id` int,
				PRIMARY KEY (`id`)
			  ) DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci
			');
			$q->exec('INSERT INTO `groups` (`id`,`parent`,`name`) VALUES (1,0,"admin")');
			$q->exec('INSERT INTO `groups` (`id`,`parent`,`name`) VALUES (2,0,"moderator")');
			*/
			$table = new Table('users');
			$table->id();
			$table->string('username');
			$table->string('password');
			$table->string('realname');
			$table->string('email');
			$table->string('avatar')->nullable();
			$table->bool('email_verifyed');
			$table->bool('enabled');
			$table->bool('deleted');
			$table->createInDB();
			if ($table->error != '') {
				echo $table->error.'<br>';
			}	
			$table = new Table('groups');
			$table->id();
			$table->bigint('parent');
			$table->string('name');
			$table->createInDB();
			if ($table->error != '') {
				echo $table->error.'<br>';
			}	

			$table = new Table('user_group');
			$table->id();
			$table->bigint('user_id');
			$table->bigint('group_id');
			$table->createInDB();
			if ($table->error != '') {
				echo $table->error.'<br>';
			}	
			
			$r = new Record();
			$r->id = 1;
			$r->parent = 0;
			$r->name = 'admin';	
			$q = new Query('groups');
			$q->insert($r);
			if ($q->error != '') {
				echo $q->error.'<br>';
			}	

			$r->id = 2;
			$r->parent = 0;
			$r->name = 'moderator';	
			$q = new Query('groups');
			$q->insert($r);
			$q->insert($r);
			if ($q->error != '') {
				echo $q->error.'<br>';
			}	

			$r = new Record();			
			$r->verzio = 'v1.0.0';
			$q = new Query('dbverzio');
			$q->update($r);
			if ($q->error != '') {
				echo $q->error.'<br>';
			}	

		}	
	}

	/**
	 * szükség szerint adatbázis alterek, új táblák létrehozása
	 * adatbázisban tárolt dbverzio frissitése
	 * @param string $dbverzio jelenlegi telepitett adatbázis verzió
	 */
	public function dbUpgrade(string $dbverzio) {
		$this->do_v1_0($dbverzio);
		// ide jönek a későbbi verziokhoz szükséges db alterek növekvő verzió szerint
	}

	/**
	 * files.txt fájl feldolgozása
	 * @param string $fileName
	 * @return array [relativfilePath => dátum,...]
	 */
	protected function processFilesTxt(string $fileName): array {
		$result = [];
		$lines = file($fileName);
		$path = '';
		$fname = '';
		$date = '';
		if (is_array($lines)) {
			foreach ($lines as $line) {
				if (substr($line,0,1) == '.') {
					$path = trim(str_replace(':','',$line)).'/';
					$path = str_replace('./','',$path);
				} else if (trim($line) != ''){
					$line = preg_replace('/\ +/i','|',trim($line),6);
					$line = str_replace("\n",'',$line);
					$line = str_replace('\n','',$line);
					$w = explode('|',$line,7);
					if (count($w) > 6) {
						if ((strpos($w[6],'.') > 0) & ($w[6] != 'config.php')) {
							$result[$path.$w[6]] = $w[5];
						}	
					}
				}
			}
		}
		return $result;
	} 

	/**
	 * A githubon lévő, -  és a telepitetten files.txt összehasonlitása, 
	 * eltérések képernyőre listázása
	 * @return elvégzendó müveletek száma
	 */
	public function listChangedFiles(): int {
		$result = 0;
		$myFiles = $this->processFilesTxt(DOCROOT.'/files.txt');
		if (is_array($myFiles)) {
			$gitFiles = $this->processFilesTxt($this->github.'files.txt');
			if (is_array($gitFiles)) {
				// új fájlok kivéve config.php
				foreach($gitFiles as $gitFile => $v) {
					if (!isset($myFiles[$gitFile]) & ($gitFile != 'config.php')) {
						echo '<p style="color:green"><em class="fas fa-plus-square"></em> Új '.$gitFile.'</p>';
						$result++;
					}
				}
				// változott fájlok kivéve config.php
				foreach($gitFiles as $gitFile => $v) {
					if (isset($myFiles[$gitFile]) & ($gitFile != 'config.php')) {
						if ($myFiles[$gitFile] != $gitFiles[$gitFile]) {
							echo '<p style="color:orange"><em class="fas fa-edit"></em> Változott '.$gitFile.'</p>';
							$result++;
						}	
					}
				}
				// törölt fájlok  kivéve config.php
				foreach($myFiles as $myFile => $v) {
					if (!isset($gitFiles[$myFile]) & ($myFile != 'config.php')) {
						echo '<p style="color:red"><em class="fas fa-minus-square"></em> Törölt '.$myFile.'</p>';
						$result++;
					}
				}
			}
		}
		return $result;
	}

	/**
	 * A githubon lévő, -  és a telepitetten lévő files.txt összehasonlitása, 
	 * eltérések frissitése
	 */
	public function upgradeChangedFiles() {
		$myFiles = $this->processFilesTxt(DOCROOT.'/files.txt');
		if (is_array($myFiles)) {
			$gitFiles = $this->processFilesTxt($this->github.'files.txt');
			if (is_array($gitFiles)) {
				// új fájlok kivéve config.php
				foreach($gitFiles as $gitFile => $v) {
					if (!isset($myFiles[$gitFile]) & ($gitFile != 'config.php')) {
						$this->downloadFile($gitFile);
					}
				}
				// változott fájlok kivéve config.php
				foreach($gitFiles as $gitFile => $v) {
					if (isset($myFiles[$gitFile]) & ($gitFile != 'config.php')) {
						if ($myFiles[$gitFile] != $gitFiles[$gitFile]) {
							$this->updateFile($gitFile);
						}	
					}
				}
				// törölt fájlok  kivéve config.php
				foreach($myFiles as $myFile => $v) {
					if (!isset($gitFiles[$myFile]) & ($myFile != 'config.php')) {
						$this->delFile($myFile); 
					}
				}
			}
		}

	}	
}
?>
