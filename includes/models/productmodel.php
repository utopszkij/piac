<?php
/**
 * Product model
 */
    namespace RATWEB;

    use \RATWEB\DB\Query;
    use \RATWEB\DB\Record;

    include_once __DIR__.'/../../vendor/model.php';
    include_once __DIR__.'/../../vendor/database/db.php';

    class ProductRecord extends Record {
        public $id = 0;
        public $user_id = 0;
        public $name = '';
        public $type = 'fogy.cikk';
        public $unit = 'db';
        public $workHours = 0;
        public $required = 0;
    }

    class ProductModel  extends Model {
        public $table = 'products';
        public $errorMsg = '';
        
        function __construct() {
            $this->errorMsg = '';
            $this->table = 'products';
        }

        public function deleteById($id) {
            $this->delById($id);
        }


        public function emptyRecord() {
            return new ProductRecord();
        }

        public function getItems($page,$limit,$filter,$order) {
			if ($page < 1) $page = 1;
			if (isset($_SESSION['loged'])) {
				$loged = $_SESSION['loged'];
			} else {
				$loged = 0;
			}	
			if ($loged < 0) $loged = 0;
            $db = new Query('products','p');
            $result = $db->select(['p.id','p.name','p.type','p.required'])
					->where('user_id','=',$loged)
                    ->offset((($page - 1) * $limit))
                    ->limit($limit)
                    ->orderBy('p.name')
                    ->all();
            return $result;        
        } 

        public function getTotal($filter) {
            $db = new Query('products','p');
			if (isset($_SESSION['loged'])) {
				$loged = $_SESSION['loged'];
			} else {
				$loged = 0;
			}	
			if ($loged < 0) $loged = 0;
            $result = $db->where('user_id','=',$loged)->count();
            return $result;        
        }
        
        protected function getNewId(array $products, int $id): int {
			$result = 0;
			foreach ($products as $product) {
				if ($product->id == $id) {
					$result = $product->newId;
				}
			}
			return $result;
		}
        
        /**
         *  ha még nincsenek termékei akkor a user_id=0 csoportot másoljuk neki
         * @param int $loged
         */ 
        public function init(int $loged) {
			$recs = $this->getBy('user_id',$loged);
			
			// echo 'init '.$loged.' '.JSON_encode($recs); exit();
			
			if ((count($recs) == 0) & ($loged > 0)) {
				$products = $this->getBy('user_id',0);
				for ($i=0; $i<count($products); $i++) {
					$newProduct = clone $products[$i];
					$newProduct->id = 0;
					$newProduct->user_id = $loged;
					$newId = $this->save($newProduct);
					$products[$i]->newId = $newId;
				}
				// most a $products tömbben van $newId és a régi $id
				// most következik a subprodukt rekordok átvitele 
				for ($i=0; $i<count($products); $i++) {
					$product = $products[$i];
					$q = new \RATWEB\DB\Query('subproducts');
					$subproducts = $q->where('product_id','=',$product->id)->all();
					foreach ($subproducts as $subproduct) {
						$subproduct->id = 0;
						$subproduct->product_id = $product->newId;
						$subproduct->subproduct_id = $this->getNewId($products, $subproduct->subproduct_id);
						$q->insert($subproduct);
					}
				
				}	
			} // most kell inicializálni 
		} // init function
        
    }
?>
