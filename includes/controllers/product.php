<?php
include_once __DIR__.'/../../vendor/controller.php';
include_once __DIR__.'/../models/productmodel.php';
/**
/**
 * Product controller
 *
 * igényelt model methodusok: emptyRecord(), save($record), 
 *      getById($id), delteById($id), getItems($page,$limit,$filter,$order), 
 *      getTotal($filter)
 * igényel viewerek {name}browser, {name}form 
 *      a {name}form legyen alkalmas show funkcióra is record,loged,logedAdmin alapján
 * 
 * A taskok public function -ként legyenek definiálva.
 * FIGYELEM az összes komponensben nézve egyedinek kell a task neveknek lenniük!
 */
class Product extends Controller {
    protected $request;
    protected $session;
    protected $loged = 0;
    protected $logedName = 'Látogató';
    protected $logedAdmin = false;
    protected $logedGroup = '';
    protected $logedAvatar = '';
    protected $model;
    protected $name;
    protected $browserURL;
    protected $addURL;
    protected $editURL;
    protected $browserTask;

    function __construct() {
        $this->request = new Request();
        $this->session = new Session();
        $this->loged = $this->session->input('loged',0,INTEGER);
        $this->logedName = $this->session->input('logedName','Látogató');
        $this->logedAdmin = isAdmin();
        $this->logedAvatar = $this->session->input('logedAvatar');
        $this->model = new \RATWEB\ProductModel();
        $this->name = 'product';
        $this->browserURL = 'index.php?task=product.products';
        $this->addURL = 'index.php?task=product.newproduct';
        $this->browserTask = 'product.products';
        $this->isAdmin = true;
        
        // ha még nincsenek termékei, akkor a "user_id=0"csoportot másoljuk neki
        $this->model->init($this->loged); 
    }

    /**
     * rekord ellenörzés (update vagy insert előtt)
     * @param RecordObject $record
     * @return string üres ha minden OK, egyébként hibaüzenet
     */    
    protected function validator($record): string {
        $result = '';
        if ($record->name == '') {
            $result = 'Nevet meg kell adni!';
        }
        if ($record->unit == '') {
            $result .= '<br />Mértékegységet meg kell adni!';
        }
        if ($record->workHours <= 0) {
            $result .= '<br />Munkaóra szükségletet meg kell adni!';
        }
        return $result;
    }

    public function products() {
        $this->items();
    }

    public function newproduct() {
		if ($this->loged <= 0) {
			echo '<div class="alert alert-danger">Felvitelhez be kell jelentkezni!</div>';
		} else {
			$this->new();
		}	
    }

    public function editproduct() {
		$id = $this->request->input('id',0);
		if ($this->loged <= 0) {
			echo '<div class="alert alert-danger">Módosításhoz be kell jelentkezni!</div>';
		} else {
			$this->edit();
		}	
    }

    public function deleteproduct() {
		$id = $this->request->input('id');
		if ($this->loged <= 0) {
			echo '<div class="alert alert-danger">Törléshezz be kell jelentkezni!</div>';
		} else {
			$q = new \RATWEB\DB\query('subproducts');
			$q->where('product_id','=',$id)->delete();
			$q = new \RATWEB\DB\query('personal_required');
			$q->where('product_id','=',$id)->delete();
			$this->delete();
		}	
    }

    public function saveproduct() {
        $record = new \RATWEB\ProductRecord();
        $record->id = $this->request->input('id');
        $record->name = $this->request->input('name');
        $record->type = $this->request->input('type');
        $record->unit = $this->request->input('unit');
        $record->workHours = $this->request->input('workHours');
        $record->required = $this->request->input('required');
        $record->user_id = $this->loged;
        $this->save($record);
    }

}
?>
