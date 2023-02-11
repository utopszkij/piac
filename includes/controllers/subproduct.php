<?php
include_once __DIR__.'/../../vendor/controller.php';
include_once __DIR__.'/../models/subproductmodel.php';
/**
/**
 * SubProduct controller
 *
 * igényel viewerek {name}browser, {name}form 
 *      a {name}form legyen alkalmas show funkcióra is record,loged,logedAdmin alapján
 * 
 */
class Subproduct extends Controller {
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
        $this->model = new \RATWEB\SubproductModel();
        $this->name = 'subproduct';
        $this->browserURL = 'index.php?task=subproduct.subproducts';
        $this->addURL = 'index.pgp?task=subproduct.newsubproduct';
        $this->browserTask = 'subproduct.subproducts';
        $this->isAdmin = true;
    }

    /**
     * rekord ellenörzés (update vagy insert előtt)
     * @param RecordObject $record
     * @return string üres ha minden OK, egyébként hibaüzenet
     */    
    protected function validator($record): string {
        $result = '';
        if ($record->quantity <= 0) {
            $result = 'Mennyiséget meg kell adni!';
        }
        if ($record->product_id == $record->subproduct_id) {
            $result .= '<br />Hivatkozási hurok!';
        }
        return $result;
    }

    /**
     * GET: product_id
     */
    public function subproducts() {
        $q = new \RATWEB\DB\Query('products');
        $product = $q->where('id','=', $this->request->input('id',0))->first();
        $this->session->set('product',JSON_encode($product));
        $this->items();
    }

    public function newsubproduct() {
        $product = JSON_decode($this->session->input('product'));
        $this->new();
    }

    public function editsubproduct() {
        $product = JSON_decode($this->session->input('product'));
        $this->edit();
    }

    public function deletesubproduct() {
        $product = JSON_decode($this->session->input('product'));
        $this->delete();
    }

    public function savesubproduct() {
        $product = JSON_decode($this->session->input('product'));
        $record = new \RATWEB\DB\Record();
        $record->id = $this->request->input('id');
        $record->product_id = $this->request->input('product_id');
        $record->subproduct_id = $this->request->input('subproduct_id');
        $record->quantity = $this->request->input('quantity');
        $this->session->set($this->name.'_oldRecord',JSON_encode($record));
        $this->checkFlowKey($this->browserURL);
        if ($record->id == 0) {
            if (!$this->accessRight('new',$record)) {
                $this->session->set('errorMsg','ACCESSDENIED');
                echo '<script>
                location="'.$this->browserURL.'";
                </script>
                ';
            }
        } else {
            if (!$this->accessRight('edit',$record)) {
                $this->session->set('errorMsg','ACCESSDENIED');
                echo '<script>
                location="'.$this->browserURL.'";
                </script>
                ';
            }
        }   
        $error = $this->validator($record);
        if ($error != '') {
            $this->session->set('errorMsg',$error);
            if ($record->id == 0) {
                echo '<script>
                location="'.$this->addURL.'";
                </script>
                ';
            } else {
                echo '<script>
                location="'.$this->editURL.'";
                </script>
                ';
            }    
        } else {
            $this->session->delete($this->name.'_oldRecord');
            $this->model->save($record);
            if ($this->model->errorMsg == '') {
                $this->session->delete('errorMsg');
                $this->session->set('successMsg','SAVED');
                echo '<script>
                    location="'.$this->browserURL.'&id='.$record->product_id.'";
                </script>
                ';
            } else {
                echo $this->model->errorMsg; exit();
            }
        }
    }

}
?>
