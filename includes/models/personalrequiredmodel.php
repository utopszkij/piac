<?php
/**
 * PersonalRequired model
 */
    namespace RATWEB;

    use \RATWEB\DB\Query;
    use \RATWEB\DB\Record;

    include_once __DIR__.'/../../vendor/model.php';
    include_once __DIR__.'/../../vendor/database/db.php';

    class PersonalrequiredRecord extends Record {
        public $id = 0;
        public $product_id = 0;
        public $quantity = 0;
        public $parent_name = '';
        public $parent_unit = '';

        function __construct() {
            $parent = JSON_decode($_SESSION['product']);
            $this->product_id = $parent->id;
            $this->parent_name = $parent->name;
            $this->parent_unit = $parent->unit;
        }
    }

    class PersonalrequiredModel  extends Model {
        public $table = 'personal_required';
        public $errorMsg = '';
        
        function __construct() {
            $this->errorMsg = '';
            $this->table = 'personal_required';
        }

        public function deleteById($id) {
            $this->delById($id);
        }


        public function emptyRecord() {
            return new PersonalrequiredRecord();
        }

        public function getById(int $id): Record {
            $result = parent::getById($id);
            $q = new \RATWEB\DB\Query('products');
            $parent = $q->where('id','=',$result->product_id)->first();
            $result->parent_name = $parent->name;
            $result->parent_unit = $parent->unit;
            return $result; 
        }
       
    }
?>