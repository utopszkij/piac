<?php

use PHPUnit\Framework\TestCase;
use RATWEB\DB\Query;
use RATWEB\DB\Record;

include_once __DIR__.'/mock.php';

include_once('./includes/controllers/group.php');
class GroupTest extends TestCase {
	protected $controller;
	
	function __construct() {
		parent::__construct();
		$this->controller = new Group();
	}
    
	// ez csak egyszer fut
	public function test_start()  {
		$db = new Query('groups');
		$db->delete();

		$db = new Query('users');
		$db->delete();
		$record = new Record();
		$record->id = 0;
		$record->username = 'admin';
		$db->insert($record);
		$this->assertEquals(1,1);
    }

	// ez minden egyes test rutin előtt lefut
	public function setup():void {
		$_SESSION['errorMsg'] = '';
		$_SESSION['successrMsg'] = '';
		$_POST = [];
	}

	protected function loginAdmin() {
		$_SESSION['loged'] = 1;
		$_SESSION['logedName'] = ADMIN;
		$_SESSION['logedAdmin'] = true;
		$_SESSION['logedGroup'] = 'admin';
		$this->controller = new Group();
	}

	protected function logout() {
		$_SESSION['loged'] = 0;
		$_SESSION['logedName'] = 'guest';
		$_SESSION['logedAdmin'] = false;
		$_SESSION['logedGroup'] = '';
		$this->controller = new Group();
	}

	protected function loginNotAdmin() {
		$_SESSION['loged'] = 2;
		$_SESSION['logedName'] = 'user';
		$_SESSION['logedAdmin'] = false;
		$_SESSION['logedGroup'] = '';
		$this->controller = new Group();
	}

    public function test_add_notlogged() {
		$this->logout();
		$this->controller->add();
		$this->expectOutputRegex('/group.groups/'); 
	}

    public function test_add_logedNotAdmin() {
		$this->loginNotAdmin();
		$this->controller->add();
		$this->expectOutputRegex('/group.groups/'); 
	}

    public function test_add_logedAdmin() {
		$this->loginAdmin();
		$this->controller->add();
		$this->assertEquals(checkview()['name'],'groupform');
		$this->assertEquals(checkview()['params']['record']->name,'');
		$this->expectOutputRegex('//'); 
	}

    public function test_store_notlogedk() {
		$this->logout();		
        $_POST['id'] = 0;
        $_POST['name'] = 'admin';
		$_SESSION['key'] = '123';
		$_POST['key'] = '123';
		$this->controller->store();
		$this->expectOutputRegex('/ACCESSDENIED/'); 
	}

    public function test_store_logedNotAdmin() {
		$this->loginNotAdmin();		
        $_POST['id'] = 0;
        $_POST['name'] = 'admin';
		$_SESSION['key'] = '123';
		$_POST['key'] = '123';
		$this->controller->store();
		$this->expectOutputRegex('/ACCESSDENIED/'); 
	}

	public function test_store_new_ok() {
		$this->loginAdmin();		
        $_POST['id'] = 0;
        $_POST['name'] = 'admin';
		$_SESSION['key'] = '123';
		$_POST['key'] = '123';
		$this->controller->store();
        $_POST['id'] = 0;
        $_POST['name'] = 'moderator';
		$this->controller->store();
		$this->expectOutputRegex('/group.groups/'); 
        $q = new Query('groups');
        $rec = $q->where('name','=','admin')->first();
        $this->assertEquals($rec->name,'admin');
        $q = new Query('groups');
        $rec = $q->where('name','=','moderator')->first();
        $this->assertEquals($rec->name,'moderator');
	}

	public function test_store_editAdmin_logedAdmin() {
		$this->loginAdmin();		
		$_SESSION['key'] = '123';
		$_POST['key'] = '123';
		$db = new Query('groups');
		$adminGroup = $db->where('name','=','admin')->first();
        $_POST['id'] = $adminGroup->id;
        $_POST['name'] = 'admin_javitva';
		$this->controller->store();
		$this->expectOutputRegex('/ADMIN_CANNOT_UPDATE/'); 
		$db = new Query('groups');
		$adminGroup = $db->where('id','=',$adminGroup->id)->first();
        $this->assertEquals($adminGroup->name,'admin');
		$db = new Query('groups');
		$adminGroup = $db->where('name','=','admin_javitva')->first();
        $this->assertEquals(isset($adminGroup->name),false);
	}	

	public function test_update_notloged() {
		$this->logout();
		$db = new Query('groups');
		$adminGroup = $db->where('name','=','admin')->first();
		$_GET['id'] = $adminGroup->id;
		$this->controller->update();
		$this->assertEquals(checkview()['name'],'groupform');
	}

	public function test_update_logedNotAdmin() {
		$this->loginNotAdmin();
		$db = new Query('groups');
		$adminGroup = $db->where('name','=','admin')->first();
		$_GET['id'] = $adminGroup->id;
		$this->controller->update();
		$this->assertEquals(checkview()['name'],'groupform');
	}

	public function test_update_logedAdmin() {
		$this->loginAdmin();
		$db = new Query('groups');
		$adminGroup = $db->where('name','=','admin')->first();
		$_GET['id'] = $adminGroup->id;
		$this->controller->update();
		$this->assertEquals(checkview()['name'],'groupform');
	}	

	public function test_remove_notloged() {
		$_SESSION['key'] = '123';
		$_POST['key'] = '123';
		$this->logout();
		$db = new Query('groups');
		$moderatorGroup = $db->where('name','=','moderator')->first();
		$_GET['id'] = $moderatorGroup->id;
		$this->controller->remove();
		$this->assertEquals('ACCESDENIED',$_SESSION['errorMsg']); 
		$this->expectOutputRegex('/group.groups/'); 
	}

	public function test_remove_logedNotAdmin() {
		$_SESSION['key'] = '123';
		$_POST['key'] = '123';
		$this->loginNotAdmin();
		$db = new Query('groups');
		$moderatorGroup = $db->where('name','=','moderator')->first();
		$_GET['id'] = $moderatorGroup->id;
		$this->controller->remove();
		$this->assertEquals('ACCESDENIED',$_SESSION['errorMsg']); 
		$this->expectOutputRegex('/group.groups/'); 
	}

	public function test_remove_admin_logedAdmin() {
		$_SESSION['key'] = '123';
		$_POST['key'] = '123';
		$this->loginAdmin();
		$db = new Query('groups');
		$adminGroup = $db->where('name','=','admin')->first();
		$_GET['id'] = $adminGroup->id;
		$this->controller->remove();
		$this->assertEquals('ACCESDENIED',$_SESSION['errorMsg']); 
		$this->expectOutputRegex('/group.groups/'); 
	}

	public function test_remove_logedAdmin_ok() {
		$_SESSION['key'] = '123';
		$_POST['key'] = '123';
		$this->loginAdmin();
		$db = new Query('groups');
		$moderatorGroup = $db->where('name','=','moderator')->first();
		$_GET['id'] = $moderatorGroup->id;
		$this->controller->remove();
		$this->assertEquals('DELETED',$_SESSION['successMsg']); 
		$this->expectOutputRegex('/group.groups/'); 
	}


}
