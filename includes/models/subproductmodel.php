<?php
/**
 * Product model
 */
    namespace RATWEB;

    use \RATWEB\DB\Query;
    use \RATWEB\DB\Record;

    include_once __DIR__.'/../../vendor/model.php';
    include_once __DIR__.'/../../vendor/database/db.php';

    class SubproductRecord extends Record {
        public $id = 0;
        public $product_id = 0;
        public $subproduct_id = 0;
        public $quantity = 0;
        public $unit = '';
        public $name = '';
        public $parent_name = '';
        public $parent_unit = '';
        public $products = [];  // [{id,name},.... ]

        function __construct() {
            $parent = JSON_decode($_SESSION['product']);
            $this->product_id = $parent->id;
            $this->parent_name = $parent->name;
            $this->parent_unit = $parent->unit;
            $q = new \RATWEB\DB\Query('products');
            $this->products = $q->select(['id','name','unit'])->orderBy('name')->all();
        }
    }

    class SubproductModel  extends Model {
        public $table = 'subproducts';
        public $errorMsg = '';
        
        function __construct() {
            $this->errorMsg = '';
            $this->table = 'subproducts';
        }

        public function deleteById($id) {
            $this->delById($id);
        }


        public function emptyRecord() {
            return new SubproductRecord();
        }

        public function getById(int $id): Record {
            $result = parent::getById($id);
            $q = new \RATWEB\DB\Query('products');
            $parent = $q->where('id','=',$result->product_id)->first();
            $q = new \RATWEB\DB\Query('products');
            $p = $q->where('id','=',$result->subproduct_id)->first();
            $result->unit = $p->unit;
            $result->name = $p->name;
            $result->parent_name = $parent->name;
            $result->parent_unit = $parent->unit;
            $q = new \RATWEB\DB\Query('products');
            $result->products = $q->select(['id','name','unit'])->orderBy('name')->all();
            return $result; 
        }

        public function getItems($page,$limit,$filter,$order) {

            if ($page < 1) $page = 1;
            $parent = JSON_decode($_SESSION['product']);
            $db = new \RATWEB\DB\Query('subproducts','sp');
            $db->select(['sp.id','p2.name','sp.quantity','p2.unit','p.name pname','p.unit punit'])
                    ->join('LEFT OUTER','products','p','p.id','=','sp.product_id')
                    ->join('LEFT OUTER','products','p2','p2.id','=','sp.subproduct_id')
                    ->where('product_id','=',$parent->id)
                    ->offset((($page - 1) * $limit))
                    ->limit($limit)
                    ->orderBy('p2.name');
            $result = $db->all();
            
            if (count($result) == 0) {
                $result = [new \stdClass()];
                $result[0]->id = 0;
                $result[0]->product_id = $parent->id;
                $result[0]->name = 'Nincs összetevő';
                $result[0]->unit = 'db';
                $result[0]->pname = $parent->name;
                $result[0]->punit = $parent->unit;
            }

            // product szerepel már szimulációban?
			$q = new \RATWEB\DB\Query('product_time');
			$recs = $q->where('product_id','=',$parent->id)->all();
			if (count($recs) == 0) {
				for ($i = 0; $i < count($result); $i++) {
					$result[$i]->used = false;
				}
			} else {	
				for ($i = 0; $i < count($result); $i++) {
					$result[$i]->used = true;
				}
			}

            return $result;        
        } 

        public function getTotal($filter) {
            $parent = JSON_decode($_SESSION['product']);
            $db = new \RATWEB\DB\Query('subproducts','sp');
            $result = $db->where('product_id','=',$parent->id)->count();
            return $result;        
        }
        
    }
?>
