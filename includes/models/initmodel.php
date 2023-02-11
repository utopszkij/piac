<?php
/**
 * Product model
 */
    namespace RATWEB;

    use \RATWEB\DB\Query;
    use \RATWEB\DB\Record;

    include_once __DIR__.'/../../vendor/model.php';
    include_once __DIR__.'/../../vendor/database/db.php';

    class InitRecord extends Record {
        public $id = 0;
        public $priceup = 110;
        public $pricedown = 90;
        public $quantityup = 110;
        public $quantitydown = 90;
        public $population = 1000;
        public $workhourprice = 2000;
        public $hourperday = 8;
        public $set = 0;
        public $name = '';
        public $algorithm = '';
        public $minworkhourprice = 1000;

        function __construct() {
            if (isset($_SESSION['loged'])) {
                $loged = $_SESSION['loged'];
            } else {
                $loged = 0;
            }
            if (file_exists('includes/controllers/algorithm_'.$loged.'.php'))
                $lines = file('includes/controllers/algorithm_'.$loged.'.php');
            else {
                $lines = file('includes/controllers/algorithm_0.php');
            }    
            $this->algorithm = implode('',$lines);
        }
    }

    class InitModel  extends Model {
        public $table = 'inits';
        public $errorMsg = '';
        
        function __construct() {
            $this->errorMsg = '';
            $this->table = 'inits';
        }

        public function deleteById($id) {
            $this->delById($id);
        }


        public function emptyRecord() {
            return new InitRecord();
        }

        public function getItems($page,$limit,$filter,$order) {
			if ($page < 1) $page = 1;
            $db = new Query('inits','p');
            $result = $db->offset((($page - 1) * $limit))
                    ->limit($limit)
                    ->orderBy('p.id')
                    ->all();
            return $result;        
        } 

        public function getTotal($filter) {
            $db = new Query('inits','p');
            $result = $db->count();
            return $result;        
        }

        public function productsInit($userId) {
            $q = new \RATWEB\DB\Query('products');
            $recs = $q->where('user_id','=',$userId)->all();
            if (count($recs) == 0) {
                $newIds = []; // [$oldId => $newId]
                $q = new \RATWEB\DB\Query('products');
                $products = $q->where('user_id','=',0)->all();
                foreach ($products as $product) {
                    $oldId = $product->id;
                    $product->id = 0;
                    $product->user_id = $userId;
                    $newIds[$oldId]  = $q->insert($product);
                }
                $q = new \RATWEB\DB\Query('products');
                $products = $q->where('user_id','=',0)->all();
                foreach ($products as $product) {
                    $q = new \RATWEB\DB\Query('subproducts');
                    $subproducts = $q->where('product_id','=',$product->id)->all();
                    foreach ($subproducts as $subproduct) {
                        $subproduct->id = 0;
                        $subproduct->product_id = $newIds[$subproduct->product_id];
                        $subproduct->subproduct_id = $newIds[$subproduct->subproduct_id];
                        $q->insert($subproduct);
                    }
                }
            }
        }


    }
?>
