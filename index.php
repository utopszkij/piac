<?php
session_start();
global $components;

// server infok hozzáférhetővé tétele a php számára
define('DOCROOT',__DIR__);
$w1 = (int) str_replace('M', '', ini_get('post_max_size'));
$w2 = (int) str_replace('M','',ini_get('upload_max_filesize'));
define('UPLOADLIMIT',min($w1,$w2));
include_once 'config.php';
include_once 'vendor/database/db.php';
include_once('vendor/model.php');
include_once('vendor/view.php');
include_once('vendor/controller.php');
include_once('vendor/fw.php');

importComponent('upgrade');

if (!isset($_GET['task']) & !isset($_POST['task'])) {
	$_GET['task'] = 'home.show';
} 

$fw = new Fw();

//+ ----------- verzio kezelés start ------------
$fileVerzio = 'v1.0.0 ß';
$upgrade = new \Upgrade();
$dbverzio  = $upgrade->getDBVersion();
$lastVerzio = $upgrade->getLastVersion();
$upgrade->dbUpgrade($dbverzio);
$branch = $upgrade->branch;
//- ----------- verzio kezelés end ------------

// képernyő méretek elérése
if (isset($_COOKIE['screen_width'])) {
	$_SESSION['screen_width'] = $_COOKIE['screen_width'];
} else {
	$_SESSION['screen_width'] = 1024;
}
if (isset($_COOKIE['screen_height'])) {
	$_SESSION['screen_height'] = $_COOKIE['screen_height'];
} else {
	$_SESSION['screen_height'] = 800;
}

$task = $fw->task;
$comp = $fw->comp;
$title = SITETITLE;
if (method_exists($comp, 'getTitle')) {
	$title = $comp->getTitle($task);
} 

?>
<html lang="en">
<head>
  <meta>
    <meta charset="UTF-8">
	<meta property="og:title"  content="<?php echo $title; ?>" />
	<base href="<?php echo SITEURL; ?>/">
	<link rel="icon" type="image/x-icon" href="images/utopszkij_fw.ico">
    <title><?php echo $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- bootstrap -->	
	<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
	<!-- vue -->
    <script src="vendor/vue/vue.global.js"></script>
	<!-- fontawesome --> 
	<script src="vendor/fontawesome/js/all.min.js"></script>
	<link rel="stylesheet" href="vendor/fontawesome/css/all.min.css">

	<link rel="stylesheet" href="styles/<?php echo STYLE ?>/style.css?t=<?php echo $fileVerzio; ?>">
	<!-- multi language -->
	
    <script src="node_modules/chart.js/dist/chart.js"></script>
	
	<?php
		if (defined('LNG')) {
			if (file_exists(__DIR__.'/languages/'.LNG.'.js')) {
				echo '<script src="languages/'.LNG.'.js"></script>';
			} else {
				echo '<script> tokens = {}; </script>';
			}	
		} else {
			if (file_exists(__DIR__.'/languages/hu.js')) {
				echo '<script src="languages/hu.js"></script>';
			} else {
				echo '<script> tokens = {}; </script>';
			}	
		}
	?>
	<script type="text/javascript">
	    const { createApp } = Vue; 
		/**
		 * csoki beállítás
		 */
		function setCookie(name,value,days) {
			var expires = "";
			if (days) {
				var date = new Date();
				date.setTime(date.getTime() + (days*24*60*60*1000));
				expires = "; expires=" + date.toUTCString();
			}
			document.cookie = name + "=" + (value || "")  + expires + "; path=/";
		}
		/**
		 * user jováhagyás kérés popup ablakban
		 */
		function popupConfirm(txt, yesfun) {
			document.getElementById('popupOkBtn').style.display="inline-block";
			document.getElementById('popupNoBtn').style.display='inline-block';
			document.getElementById('popup').className='popupSimple';
			document.getElementById('popupTxt').innerHTML = txt;
			document.getElementById('popupOkBtn').onclick=yesfun;
			document.getElementById('popup').style.display='block';
		}
		/**
		 * poup ablak bezárása
		 */
		function popupClose() {
			document.getElementById('popup').style.display='none';
		}
		/**
		 * popup üzenet
		 */
		function popupMsg(txt,className) {
			if (className == undefined) {
				className = 'popupSimple';
			}
			document.getElementById('popupOkBtn').style.display="none";
			document.getElementById('popupNoBtn').style.display='none';
			document.getElementById('popup').className=className;
			document.getElementById('popupTxt').innerHTML = txt;
			document.getElementById('popup').style.display='block';
		}
		/**
		 * nyelvi fordítás
		 */
		function lng(token) {
			var result = token;
			var w = token.split('<br>');
			for (var i = 0; i < w.length; i++) {
				if (tokens[w[i]] != undefined) {
					w[i] = tokens[w[i]];
			    }
			}
			result = w.join('<br>');	
			return result;
		}
		/**
		 * felső menüben almenü megjelenés/elrejtés
		 */
		function submenuToggle() {
			var submenu = document.getElementById('submenu');
			if (submenu.style.display == 'block') {
				submenu.style.display = 'none';
			} else {
				submenu.style.display = 'block';
			}
		}

		var rewrite = <?php echo (int)REWRITE; ?>;
        var siteurl = "<?php echo SITEURL; ?>"; 

		/**
		 * seo barát url képzéshez segéd rutin
		 * @param string task
		 * @param object params {name:value,...}
		 */
		function HREF(task, params) {
			var result = siteurl;
			if (rewrite) {
				result += '/task/'+task;
				for (var fn in params) {
					result += '/'+fn+'/'+params[fn];
				}
			} else {
				result += '?task='+task;
				for (var fn in params) {
					result += '&'+fn+'='+params[fn];
				}
			}
			return result;
		}
		// képernyő méretek tárolása csokiba
		setCookie('screen_width',screen.width,100); 
		setCookie('screen_height',screen.height,100); 
	</script>	
	<?php 
	if (file_exists('languages/'.strtolower($fw->compName).'_'.LNG.'.js')) {
		echo '<script src="languages/'.strtolower($fw->compName).'_'.LNG.'.js"></script>'."\n";
		echo '<script>tokens = Object.assign(tokens, tokensComp);</script>'."\n";
	} 
	?>
</head>	 
<body>

	<div id="popup">
		<div style="text-align:right">
			<button type="button" onclick="popupClose()" 
				title="Bezár" style="margin:0px 0px 0px 0px; padding:0px 5px 0px 5px"
				class="btn btn-secondary">X</button>
		</div>
		<div id="popupTxt"></div>
		<div>
		<button type="button" id="popupOkBtn" class="btn btn-danger">Igen</button>
			&nbsp;
			<button type="button" id="popupNoBtn"class="btn btn-primary" onclick="popupClose()">Nem</button>
		</div>
	</div>

	<div class="container">
		<div>
			<div id="header" onclick="document.location='index.php';">
				<img src="images/utopszkij_fw_smal.png" style="margin:10px; height:120px; float:left" />
				<div style="float:right; margin:60px 20px 0px 0px; color:#655191; font-weight:bold">
					Piac szimuláció
				</divr>
			</div>
		</div>
		
		<?php 
			view('mainmenu',[
				'MULTIUSER' => MULTIUSER,
				'loged' => $_SESSION['loged'],
				'logedAvatar' => $_SESSION['logedAvatar'],
				'logedName' => $_SESSION['logedName'],
				'isAdmin' => isAdmin(),
				'lastVerzio' => Upgrade::versionAdjust($lastVerzio),
				'fileVerzio' => Upgrade::versionAdjust($fileVerzio)
				],'mainmenu'); 
		?>

		<div class="page">
			<?php
				$comp->$task ();			
			?>
		</div>
		<?php 
			view('footer',[],'footer');
		?>
	</div>
	<p><?php echo $_SESSION['screen_width'].' x '.$_SESSION['screen_height']; ?></p>	
	<script>
		if (document.cookie.search('cookieEnabled=2') >= 0) {
			document.write('<p>Süti kezelés engedélyezve van. Letiltásához kattints ide:'+
			'<a href="index.php" onclick="setCookie(\'cookieEnabled\',0,100);">Letilt</a></p>');
		} else if (document.location.href.search('home.policy') < 0) {
			popupConfirm('Ennek a web oldalnak a használatához un. "munkamenet sütik" használtata szükséges.'+
			'<br />Lásd: <a href="index.php?task=home.policy">Adatkezelési leírás</a>'+
			'<br />Kérjük engedélyezd a sütik kezelését!',
			function() {
				setCookie('cookieEnabled',2,100);
				document.location='index.php';
			})
		}
	</script>	
</body>
</html>
