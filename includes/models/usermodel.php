<?php
    use \RATWEB\Model;
    use \RATWEB\DB\Query;
    use \RATWEB\DB\Record;

    class UserModel extends Model  {

        function __construct() {
            parent::__construct();
            $this->setTable('users');
            $this->errorMsg = ''; 
        }

        /**
         * logikai user rekord (users+profilok)
         */
        public function emptyRecord(): Record {
            $result = new Record();
            $result->id = 0;
            $result->username = '';
            $result->password = '';
            $result->avatar = '';
            $result->email = '';
            $result->realname = '';
            $result->group = '';
            return $result;
        }

        /**
         * rekordok lapozható listája
         * @param int $page
         * @param int $limit
         * @param string $filter - nincs használva
         * @param string $order - nincs használva
         * @return array
         */
        public function getItems(int $page, int $limit, string $filter, string $order): array {
			if ($page < 1) $page = 1;
			if ($limit < 5) $limit = 5;
            $db = new Query('users','u');
            $result = $db->select(['u.id','u.username','u.avatar'])
                    ->where('deleted','=',0)
                    ->offset((($page - 1) * $limit))
                    ->limit($limit)
                    ->orderBy('username')
                    ->all();
            return $result;        
        }

        /**
         * Összes rekord száma
         * @return int
         */
        public function getTotal($filter): int {
            $db = new Query('users');
            $recs = $db->all();
            return count($recs);
        }

        /**
         * logikai user rekord (users+profilok) törlése id szerint
         * @param int $id
         */
        public function delById(int $id): bool {
            $old = $this->getById($id);
            if (isset($old->id)) {
                $old->deleted = 1;
                $old->username = 'deleted';
                $old->realname = 'deleted';
                $old->password = rand(100000,999999);
                $this->save($old);
            }
            return $result;
        }

        /**
         * user rekord tárolása (insert vagy update)
         * ha ADMIN felvitel akkor generálja a user_group rekordot is
         * @param Record $record
         */
        public function save(Record $record): int {
            unset($record->password2);
            $recordId = $record->id;
            if ($record->password != '') {
                $id = parent::save($record);
                $record->id = $id;
                $record->password = hash('sha256',$record->password.$record->id);
                $id = parent::save($record);
            }  else {
                unset($record->password);
                $id = parent::save($record);
            }  

            if (($record->username == ADMIN) & ($recordId == 0)) {
                $q = new Query('user_group');
                $r = new Record();
                $r->id = 0;
                $r->user_id = $id;
                $r->group_id = 1;
                $q->insert($r);
            }

            $record->avatar = '';
            // avatr kép feltöltés
            $error = '';
            if (isset($_FILES['avatar'])) {
                if (file_exists($_FILES['avatar']['tmp_name'])) { 
                    $target_dir = DOCROOT.'/images/users';
                    if (!is_dir($target_dir.'/')) {
                        mkdir($target_dir,0777);
                    }
                    $target_dir .= '/';
                    $target_file = $target_dir.$id.'-'.basename($_FILES['avatar']["name"]);
                    $check = getimagesize($_FILES['avatar']["tmp_name"]);
                    if($check == false) {
                        $error = 'nem kép fájl';
                    }
                    if ($_FILES['avatar']['size'] > (UPLOADLIMIT * 1024 * 1024)) {
                        $error = 'túl nagy kép fájl';
                    }
                    if (file_exists($target_file) & ($error == '')) {
                        unlink($target_file);
                    }
                    if ($error == '') {
                        if (!move_uploaded_file($_FILES['avatar']["tmp_name"], $target_file)) {
                            $error = "Hiba a kép fájl feltöltés közben "; 
                        }
                        $record->avatar = $record->id.'-'.basename($_FILES['avatar']["name"]);
                        $this->save($record);
                    } else {
                        echo $error; exit();
                    }
                } 
            }
            return $id;
        }

        /**
         * adott userhez tartozó gruppok
         * @param int $id user_id
         * @return [{id, name},..]
         */
        public static function getGroups(int $id):array {
            $q = new Query('user_group','ug');
            $result = $q->select(['g.id, g.name'])
                ->join('INNER','groups','g','g.id','=','ug.group_id')
                ->where('ug.user_id','=',$id)
                ->orderBy('g.name')
                ->all();
            return $result;
        }

        /**
         * összes group
         * @return [{id, name},..]
         */
        public static function getAllGroups() {
            $q = new Query('groups');
            return  $q->select(['id, name'])
                ->orderBy('name')
                ->all();
        }

        /**
         * képernyőn beirt user groupok tárolása, feleslegesek törlése
         */
        public function saveUserGroups(int $id, Request $request) {
            $allGroups = $this->getAllGroups();
            $userGroups = $this->getGroups($id);
            foreach ($allGroups as $group) {
                $groupChecked = $request->input($group->name,0);
                if ($groupChecked == 1) {
                    $megvan = false;
                    foreach ($userGroups as $userGroup) {
                        if ($userGroup->name == $group->name) {
                            $megvan = true;
                        }
                    }
                    if (!$megvan) {
                        $record = new Record();
                        $record->user_id = $id;
                        $record->group_id = $group->id;
                        $q = new Query('user_group');
                        $q->insert($record);
                    }
                } else {
                    $q = new Query('user_group');
                    $q->where('user_id','=',$id)
                        ->where('group_id','=',$group->id)->delete();
                }
            }
        }
  }    
?>
