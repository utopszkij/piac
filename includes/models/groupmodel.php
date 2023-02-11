<?php
    use \RATWEB\Model;
    use \RATWEB\DB\Query;
    use \RATWEB\DB\Record;

    class GroupModel extends Model  {

        function __construct() {
            parent::__construct();
            $this->setTable('groups');
            $this->errorMsg = ''; 
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
            $db = new Query('groups','u');
            $result = $db->select(['id','name','parent'])
                    ->offset((($page - 1) * $limit))
                    ->limit($limit)
                    ->orderBy('name')
                    ->all();
            return $result;        
        }

        /**
         * Összes rekord száma
         * @return int
         */
        public function getTotal($filter): int {
            $db = new Query('groups');
            return $db->count();
        }

        /**
         * üres group rekord
         */
        public function emptyRecord(): Record {
            $result = new Record();
            $result->id = 0;
            $result->name = '';
            $result->parent = 0;
            return $result;
        }

        public function deleteById(\integer $id) {
            parent::deleteById($id);
            $q = new Query('user_group');
            $q->where('group_id','=',$id)->delete();
        }

}    
?>