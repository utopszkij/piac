<?php
use \RATWEB\DB\Query;
use \RATWEB\DB\Record;

include_once __DIR__.'/../models/groupmodel.php';

/**
 * group rekord kezelő osztály
 * views: groupform, groupbrowser
 */
class Group extends Controller {

	function __construct() {
		parent::__construct();
        $this->model = new GroupModel();
		$this->model = new GroupModel();
        $this->name = "group";
        $this->browserTask = 'group.groups';
        $this->browserURL = Fw::HREF($this->browserTask);
        $this->addURL = Fw::HREF('group.add');
        $this->editURL = Fw::HREF('group.update');
	}

    /**
     * loged user hozzáférés ellenörzése
     * @param string $action  'new'|'edit'|'delete'|'show'
     * @param RecordObject $record
     * @return bool
     */    
    protected function accessRight(string $action, $record): bool {
        $result = false;
        if ($action == 'show') {
            $result = true;
        } else if (isAdmin()) {
            $result = true;
        }
        if (($action == 'delete') & ($record->name == 'admin')) {
            $result = false;
        }
        return $result;
    }

    /**
     * rekord ellenörzés (update vagy insert előtt)
     * @param RecordObject $record
     * @return string üres ha minden OK, egyébként hibaüzenet
     */    
    protected function validator($record): string {
        $result = '';
        if ($record->name == '') {
            $result = 'GROUP_NAME_REQUED';
        } else {
            $recs = $this->model->getBy('name',$record->name);
            if (count($recs) > 0) {
                if ($recs[0]->id != $record->id) {
                    $result = 'GROUP_EXISTS<br>'.$record->name;
                }
            } 
        }
        return $result;
    }

    /**
     * Új felvitel képernyő
     */
	public function add() {
        $this->new();
	}
	
    /**
     * módosító képernyő
     */
	public function update() {
        $this->edit();
	}
	
    /**
     * törlés végrehajtása
     */
	public function remove() {
        $this->delete();
	}
	
    /**
     * Új felvitel vagy módosítás tárolása
     */
    public function store() {
        if ($this->logedAdmin) {
            $record = $this->model->emptyRecord();
            $record->id = $this->request->input('id',0,INTEGER);
            $record->name = $this->request->input('name','');
            $record->parent = 0;
            if ($record->id > 0) {
                $old = $this->model->getById($record->id);
                if ($old->name != 'admin') {
                    $this->save($record);
                } else {
                    echo '<script>
                    location="HREF('.$this->browserTask.',{errorMsg:"ADMIN_CANNOT_UPDATE"};
                    </script>
                    ';
                    return;
                }
            } else {
                $this->save($record);
            }
        } else {
            echo '<script>
            location="HREF('.$this->browserTask.',{errorMsg:"ACCESSDENIED"};
            </script>
            ';
        }
    }

    /**
     * Böngészés
     */
	public function groups() {
        $this->items('name');
	}

}


?>