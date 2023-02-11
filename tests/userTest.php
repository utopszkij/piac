<?php

use PHPUnit\Framework\TestCase;
use RATWEB\DB\Query;
use RATWEB\DB\Record;
use RATWEB\DB\Table;
DEFINE('UNITTEST',1);

require __DIR__ . '/../vendor/autoload.php';
use \yidas\socketMailer\Mailer;

include_once __DIR__.'/mock.php';

include_once('./includes/controllers/user.php');
class UserTest extends TestCase {
	protected $controller;
	
	function __construct() {
		parent::__construct();
		$this->controller = new User();
	}
    
	public function test_start()  {
		$db = new Query('users');
		$db->delete();
		$db = new Query('user_group');
		$db->delete();
		$db = new Query('groups');
		$db->delete();
		$db->exec('INSERT INTO `groups` (`id`,`name`) VALUES (1,"admin")');
		$this->assertEquals($db->error,'');
    }

	// ez minden test rutin előtt fut
	public function setup():void {
		$_POST = [];
		$_GET = [];
		$_SESSION = [];
	}
   
    public function test_logout() {
		$this->controller->logout();
       	$this->assertEquals($_SESSION['loged'],-1);
		$this->expectOutputRegex('/index.php/'); // ha van php output akkor kell expectOutputRegex !
	}
	
	public function test_login() {
		$_GET['redirect'] = '';
		$this->controller->login();
       	$this->assertEquals(checkView()['name'],'login');
	}

	public function test_regist() {
		$_GET['redirect'] = '';
		$this->controller->regist();
		$this->assertEquals(checkView()['name'],'regist');
	}

	public function test_doregist_ok() {
		$_SESSION['key'] = '123456789';
		$_POST['key'] = '123456789';
		$_POST['redirect'] = base64_encode('abc');
		$_POST['username'] = 'test';
		$_POST['password'] = '123456';
		$_POST['password2'] = '123456';
		$_POST['realname'] = 'Test Elek';
		$_POST['email'] = 'test@test.test';
		$_POST['accept'] = 1;
		$this->controller->doregist();
		$this->expectOutputRegex('/SAVED/');
		$this->expectOutputRegex('/abc/');
	}
	
	public function test_doregist_admin_ok() {
		$_SESSION['key'] = '123456789';
		$_POST['key'] = '123456789';
		$_POST['redirect'] = base64_encode('abc');
		$_POST['username'] = ADMIN;
		$_POST['password'] = '123456';
		$_POST['password2'] = '123456';
		$_POST['realname'] = 'administrator';
		$_POST['email'] = 'admin@test.test';
		$_POST['accept'] = 1;
		$this->controller->doregist();
		$q = new Query('user_group');
		$this->assertEquals($q->count(),1);
		$this->expectOutputRegex('/SAVED/');
		$this->expectOutputRegex('/abc/');
	}
	

	public function test_doregist_emptydata() {
		$_SESSION['key'] = '123456789';
		$_POST['key'] = '123456789';
		$_POST['redirect'] = '';
		$_POST['username'] = '';
		$_POST['realname'] = '';
		$_POST['email'] = '';
		$_POST['password'] = '';
		$_POST['password2'] = '';
		$this->controller->doregist();
		$this->expectOutputRegex('/USERNAME_REQUED/');
		$this->expectOutputRegex('/REALNAME_REQUED/');
		$this->expectOutputRegex('/EMAIL_REQUED/');
		$this->expectOutputRegex('/PASSWORD_REQUED/');
		$this->assertEquals(checkView()['name'],'regist');
	}

	public function test_doregist_notequals() {
		$_SESSION['key'] = '123456789';
		$_POST['key'] = '123456789';
		$_POST['redirect'] = '';
		$_POST['username'] = 'test2';
		$_POST['password'] = '123456';
		$_POST['password2'] = '1234567';
		$_POST['realname'] = 'Test Elek';
		$_POST['email'] = 'test@test.test';
		$this->controller->doregist();
		$this->expectOutputRegex('/PASSWORDS_NOT_EQUALS/');
		$this->assertEquals(checkView()['name'],'regist');
	}

	public function test_doregist_exists_username() {
		$_SESSION['key'] = '123456789';
		$_POST['key'] = '123456789';
		$_POST['redirect'] = '';
		$_POST['username'] = 'test';
		$_POST['password'] = '123456';
		$_POST['password2'] = '123456';
		$_POST['realname'] = 'Test Elek';
		$_POST['email'] = 'test2@test.test';
		$this->controller->doregist();
		$this->expectOutputRegex('/USER_EXISTS/');
		$this->assertEquals(checkView()['name'],'regist');
	}

	public function test_doregist_exists_email() {
		$_SESSION['key'] = '123456789';
		$_POST['key'] = '123456789';
		$_POST['redirect'] = '';
		$_POST['username'] = 'test2';
		$_POST['password'] = '123456';
		$_POST['password2'] = '123456';
		$_POST['realname'] = 'Test Elek';
		$_POST['email'] = 'test@test.test';
		$this->controller->doregist();
		$this->expectOutputRegex('/EMAIL_EXISTS/');
		$this->assertEquals(checkView()['name'],'regist');
	}

	public function test_dologin_notfound() {
		$_POST['redirect'] = '';
		$_POST['username'] = 'test3';
		$_POST['password'] = '12345678';
		$this->controller->dologin();
		$this->expectOutputRegex('/USER_NOT_FOUND/');
	}

	public function test_dologin_wrongpsw() {
		$_POST['redirect'] = '';
		$_POST['username'] = 'test';
		$_POST['password'] = 'wrong';
		$this->controller->dologin();
		$this->expectOutputRegex('/WRONG_PASSWORD/');
	}

	public function test_dologin_notactivated_disabled() {
		$q = new Query('users');
		$record = new Record();
		$record->enabled = 0;
		$record->email_verifyed = 0;
		$q->update($record);
		$_POST['redirect'] = '';
		$_POST['username'] = 'test';
		$_POST['password'] = '123456';
		$this->controller->dologin();
		$this->expectOutputRegex('/NOT_ACTIVATED/');
		$this->expectOutputRegex('/DISABLED/');
	}

	public function test_dologin_ok() {
		$q = new Query('users');
		$record = new Record();
		$record->enabled = 1;
		$record->email_verifyed = 1;
		$q->update($record);
		global $viewData;
		$_POST['redirect'] = base64_encode('abc');
		$_POST['username'] = 'test';
		$_POST['password'] = '123456';
		$this->controller->dologin();
		$this->expectOutputRegex('/abc/');
	}

	public function test_sendActivator_emptyEmail() {
		$this->controller->sendactivator(''); 
		$this->expectOutputRegex('/NOT_FOUND/');
	}

	public function test_sendActivator_notfound() {
		$_GET['username'] = 'wrong';
		$this->controller->sendactivator(); 
		$this->expectOutputRegex('/NOT_FOUND/');
	}

	public function test_sendActivator_ok() {
		$_GET['username'] = 'test';
		$this->controller->sendactivator(); 
		$this->expectOutputRegex('/EMAIL_SENDED/');
	}

	public function test_forgetPsw_empty() {
		$_GET['username'] = '';
		$this->controller->forgetpsw(); 
		$this->expectOutputRegex('/USERNAME_REQUED/');
	}

	public function test_forgetPsw_notfoundl() {
		$_GET['username'] = 'wrong';
		$this->controller->forgetpsw(); 
		$this->expectOutputRegex('/NOT_FOUND/');
	}

	public function test_forgetpsw_ok() {
		$_GET['username'] = 'test';
		$this->controller->forgetpsw(); 
		$this->expectOutputRegex('/EMAIL_SENDED/');
	}

	
	public function test_profile_notfound() {
		$_GET['id'] = 0;
		$this->controller->profile(); 
		$this->expectOutputRegex('/NOT_FOUND/');
	}

	public function test_profile_ok() {
		$db = new Query('users');
		$user = $db->first();
		$_GET['id'] = $user->id;
		$this->controller->profile(); 
		$this->assertEquals(checkView()['name'],'profile');
	}

	public function test_doActivate_wrong() {
		$q = new Query('users');
		$rec = $q->where('username','=','test')->first();
		$rec->email_virifyed = 0;
		$q->where('id','=',$rec->id)->update($rec);
		$_GET['code'] = base64_encode('wrong'); 
		$this->controller->doactivate();
		$rec = $q->where('username','=','test')->first();
		$this->expectOutputRegex('/NOT_FOUND/');
	}

	public function test_doActivate_ok() {
		$q = new Query('users');
		$rec = $q->where('username','=','test')->first();
		$rec->email_virifyed = 0;
		$q->where('id','=',$rec->id)->update($rec);
		$_GET['code'] = base64_encode($rec->password.'-'.$rec->id); 
		$this->controller->doactivate();
		$this->expectOutputRegex('/SAVED/');
	}

	public function savePeofile_error() {
		$_POST['id'] = 0;
		$_POST['username'] = '';
		$_POST['realname'] = '';
		$_POST['email'] = '';
		$_POST['password'] = '';
		$_POST['password2'] = '';
		$this->controller->saveprofile();
		$this->expectOutputRegex('/NOT_FOUND/');
	} 

	public function savePeofile_ok() {
		$q = new Query('users');
		$rec = $q->where('username','=','test')->first();
		$_POST['id'] = $rec->id;
		$_POST['username'] = $rec->username;
		$_POST['realname'] = $rec->realname = 'updated';
		$_POST['email'] = $rec->email;
		$_POST['password'] = '';
		$_POST['password2'] = '';
		$this->controller->saveprofile();
		$q = new Query('users');
		$rec = $q->where('username','=','test')->first();
		$this->expectOutputRegex('/SAVED/');
		$this->assertEquals($rec->realname,'updated');
	} 

	public function doDelete_error() {
		$_GET['code'] = base64_encode('wrong'); 
		$this->controller->dodelete();
		$q = new Query('users');
		$rec = $q->where('username','=','test')->first();
		$this->expectOutputRegex('/NOT_FOUND/');
		$this->assertEquals(isset($rec->realname),true);
	}

	public function doDelete_ok() {
		$q = new Query('users');
		$rec = $q->where('username','=','test')->first();
		$_GET['code'] = base64_encode($rec->email.$rec->id); 
		$this->controller->dodelete();
		$q = new Query('users');
		$rec = $q->where('username','=','test')->first();
		$this->expectOutputRegex('/DELETED/');
		$this->assertEquals(isset($rec->realname),false);
	}

	public function createTable() {
		$table = new Table('unittest');
		$table->id();
		$table->string('data1')->nullable()->comment('data1');
		$table->integer('data2');
		$table->bigint('data3');
		$table->number('data4');
		$table->date('data5');
		$table->time('data6');
		$table->datettime('data7');
		$table->bool('data8');
		$table->index(['data1']);
		$table->index(['data2','data3']);
		$table->createInDB();
		$this->assertEquals($table->error,'');
	}

	public function dropTable() {
		$table = new Table('unittest');
		$table->dropIfExists();
		$this->assertEquals($table->error,'');
	}
}



