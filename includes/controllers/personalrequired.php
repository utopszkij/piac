<?php
include_once __DIR__.'/../../vendor/controller.php';
include_once __DIR__.'/../models/personalrequiredmodel.php';
/**
/**
 * PersonalRequied controller
 *
 * igényel {name}form 
 *      a {name}form legyen alkalmas show funkcióra is record,loged,logedAdmin alapján
 * 
 */
class Personalrequired extends Controller {
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
        $this->model = new \RATWEB\PersonalrequiredModel();
        $this->name = 'personalrequired';
        $this->browserURL = 'index.php?task=product.products';
        $this->addURL = '';
        $this->browserTask = '';
        $this->isAdmin = true;
    }

    /**
     * rekord ellenörzés (update vagy insert előtt)
     * @param RecordObject $record
     * @return string üres ha minden OK, egyébként hibaüzenet
     */    
    protected function validator($record): string {
        $result = '';
        return $result;
    }

    /**
     * GET: id
     */
    public function form() {
        $id = $this->request->input('id',0);
        $q = new \RATWEB\DB\Query('products');
        $product = $q->where('id','=',$id)->first();
        $this->session->set('product', JSON_encode($product));
        $records = $this->model->getBy('product_id',$id);
        if (count($records) == 0) {
            $record = $this->model->emptyRecord();
        } else {
            $record = $records[0];
            $record->parent_name = $product->name;
            $record->parent_unit = $product->unit;
        }

		view('personalrequiredform',[
				"flowKey" => $this->newFlowKey(),
				"record" => $record,
				"logedAdmin" => $this->logedAdmin,
				"loged" => $this->loged,
				"previous" => $this->browserURL,
				"errorMsg" => $this->session->input('errorMsg',''),
		]);
        $this->session->delete('errorMsg');
    }

    public function savepersonalrequired() {
        $record = new \RATWEB\DB\Record();
        $record->id = $this->request->input('id');
        $record->product_id = $this->request->input('product_id');
        $record->quantity = $this->request->input('quantity');
        $this->save($record);
    }
}
?>
