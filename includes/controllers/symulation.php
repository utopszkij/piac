<?php
include_once __DIR__.'/../../vendor/controller.php';
include_once __DIR__.'/../models/initmodel.php';
/**
 * Symulation controller
 *
 */
class Symulation extends Controller {
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
        $this->model = new \RATWEB\InitModel();
        $this->name = 'symulation';
        $this->browserURL = 'index.php?task=symulation.symulations';
        $this->addURL = 'index.php?task=symulation.newsymulation';
        $this->browserTask = 'symulation.symulations';
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

    public function symulations() {
        $this->items();
    }

    public function newsymulation() {
        $this->new();
    }

    public function showsymulation() {
        $id = $this->request->input('id',INTEGER);
        $record = $this->model->getById((int)$id);

		// a szimulációban érintett productumok
		$q = new \RATWEB\DB\Query('product_time','pt');
		$products = $q->select(['p.id','max(p.name) name'])
			->join('LEFT','products','p','p.id','=','pt.product_id')
			->where('pt.init_id','=',$id)
			->groupBy(['p.id'])
			->orderBy('name')->all();
		
		// kereslet, kinálat cikkenként
		$q = new \RATWEB\DB\Query('product_time','prt');
		$q->select(['prt.product_id','prt.day','prt.quantity kinalat', 
			'prt.required szukseglet', 'prt.price', 'p.name'])
			->join('left','products','p','p.id','=','prt.product_id')
			->where('prt.init_id','=',$id)
			->orderBy('p.name,prt.day');
		$productResult = $q->all();	
		
		// orabérek alakulása
		$q = new \RATWEB\DB\Query('many_time');
		$workHourPrices = $q->where('init_id','=',$id)
			->orderBy('day')
			->all();
			
        // $y1 összes termék átlag kinálat/szükséglet, 
        $q = new \RATWEB\DB\Query('product_time','pt');
        $q->select(['pt.day','avg(pt.quantity / pt.required)*100 y'])
                ->where('pt.init_id','=',$id)
                ->where('pt.required','<>',0)
                ->groupBy(['pt.day'])
                ->orderBy('pt.day');
         $y1 = $q->all();

        // $y2 fogy,cikkek átlag kinálat/szükséglet
        $q = new \RATWEB\DB\Query('product_time','pt');
        $y2 = $q->select(['pt.day','avg(pt.quantity / pt.required)*100 y'])
                ->join('left','products','p','p.id','=','pt.product_id')
                ->where('pt.init_id','=',$id)
                ->where('pt.required','<>',0)
                ->where('p.type','=','fogy.cikk')
                ->groupBy(['pt.day'])
                ->orderBy('pt.day')
                ->all();

        // $y3 fogycikk fizetőképes kereslet / szükséglet  
        $q = new \RATWEB\DB\Query('many_time');
        $y3 = $q->select(['day','(validMany * 100 / requiredMany) y'])
                ->where('init_id','=',$id)
                ->orderBy('day')
                ->all();

		// y4 munkaerő kereslet / kinálat	
        $q = new \RATWEB\DB\Query('product_time','pt');
		$q->select(['pt.day', '(sum(pt.quantity * p.workHours) * 100 / '.($record->population * $record->hourperday).') y'])
				->join('LEFT','products','p','p.id','=','pt.product_id')
				->where('pt.init_id','=',$id)
				->groupBy(['pt.day'])
				->orderBy('pt.day');	
		// echo '<br /> '.$q->getSql().'<br />';		
		$y4 = $q->all();

        view('result',["y1" => $y1, 
						"y2" => $y2, 
						"y3" => $y3,
						"y4" => $y4,
						"init" => $record,
						"products" => $products,
						"productResult" => $productResult,
						"workHourPrices" => $workHourPrices]);
                
    }

    /**
     * összetevők kigyüjtése a $productsTxt -be, REKURZIV eljárás
     * @param string $productTxt
     * @parm int $productId
     * @paramstring $margin 
     */
    protected function  _getSubProducts(&$productsTxt, $productId, $margin) {       
        $margin .= '&nbsp;&nbsp;&nbsp;';
        $q = new \RATWEB\DB\Query('subproducts','sp');
        $subproducts = $q->select(['p.id', 'p.name', 'p.unit', 'sp.quantity', 'p.workHours'])
            ->join('LEFT','products','p','p.id','=','sp.subproduct_id')
            ->where('sp.product_id','=',$productId)
            ->orderBy('p.name')
            ->all();
        foreach ($subproducts as $subproduct) {
            $productsTxt .= $margin.'+-- #'.$subproduct->id.
            ' "'.$subproduct->name.'" termeléshez igény:'.
            $subproduct->quantity.'/nap '.
            $subproduct->unit.' munkaóra igény:'.$subproduct->workHours.'<br />';
            $this->_getSubProducts($productsTxt, $subproduct->id, $margin);
        } 	
    }


    public function startsymulation() {
		if ($this->loged <= 0) {
				echo '<div class="alert alert-danger">Szimuláció indításhoz be kell jelentkezni,</div>';
				exit();
		}
        // ha nincsenek termékek, összetevők akkor másolás a "0" -ból
        $this->model->productsInit($this->loged);

   		// termékek, szükségletek és összetevők kigyüjtése
		$q = new \RATWEB\DB\Query('products');
		$products = $q->where('type','=','fogy.cikk')
			->where('user_id','=',$this->loged)
			->orderBy('name')
			->all();
		$productsTxt = '';
        $margin = '';
		foreach ($products as $product) {
			$productsTxt .= '#'.$product->id.' "'.$product->name.'['.$product->unit.']'.
                '" szükséglet:'.	$product->required.'/nap/fő '.' munkaóra igény:'.$product->workHours.'<br />';
                $this->_getSubProducts($productsTxt, $product->id, $margin);
        }  

        $record = new \RATWEB\DB\Record();
        $record->id = $this->request->input('id');
        $record->name = $this->request->input('name');
        $record->priceup = $this->request->input('priceup');
        $record->pricedown = $this->request->input('pricedown');
        $record->quantityup = $this->request->input('quantityup');
        $record->quantitydown = $this->request->input('quantitydown');
        $record->workhourprice = $this->request->input('workhourprice');
        $record->population = $this->request->input('population');
        $record->hourperday = $this->request->input('hourperday');
        $record->products = $productsTxt;
        $record->days = $this->request->input('days');
        $record->set = $this->loged;
        $record->algorithm = $this->request->input('algorithm','',HTML);
        $record->minworkhourprice = $this->request->input('minworkhourprice');

        // php hack kivédési kisérlet
        $record->algorithm = str_replace('Query','',$record->algorithm);
        $record->algorithm = str_replace('Table','',$record->algorithm);
        $record->algorithm = str_replace('mysql','',$record->algorithm);
        $record->algorithm = str_replace('file','',$record->algorithm);
        $record->algorithm = str_replace('fwrite','',$record->algorithm);
        $record->algorithm = str_replace('unlink','',$record->algorithm);
        $record->algorithm = str_replace('fopen','',$record->algorithm);
        $record->algorithm = str_replace('echo','',$record->algorithm);
        $record->algorithm = str_replace('print','',$record->algorithm);
        $record->algorithm = str_replace('dump','',$record->algorithm);
        $record->algorithm = str_replace('curl','',$record->algorithm);

        $id = $this->model->save($record);
        $days = $record->days;

        $fp = fopen('includes/controllers/algorithm_'.$this->loged.'.php','w+');
        fwrite($fp, $record->algorithm);
        fclose($fp);

		echo '<h2>Szimuláció init...</h2>
            <p style="text-align:center">
                <img src="images/waiting-icon.gif" style="width:32px" />
            </p>
            <script type="text/javascript">
            function gostep() {
                location = "index.php?task=symulation.step&id='.$id.'&days='.$days.'&day=0";
            }
            setTimeout("gostep()",1000);
            </script>
            ';
	}
	
	public function step() {
		$init_id = $this->request->input('id',0);
		$days = $this->request->input('days',0);
		$day = $this->request->input('day',0);
		
		include_once __DIR__.'/algorithm_'.$this->loged.'.php';

        $w = ($day/$days)*100;
        echo '<h2>Szimuláció</h2>
                <p> </p>
                <table style="width:100%">
                    <tr style="border-style:solid; border-width:2px;">
                    <td style="background-color:green; width:'.$w.'%">&nbsp;</td>
                    <td style="background-color:silver">&nbsp;</td>
                    </tr>
                </table>
                <p style="text-align:center">'.$day.'/'.$days.'</p>
                <p style="text-align:center">
                    <img src="images/waiting-icon.gif" style="width:32px" />
                </p>
        ';
		
		
		$algorithm = new Algorithm($init_id);
		if ($day == 0) {
			$algorithm->init($init_id,$days);
            $day++;
		}
        $i = 0;
        while (($i < DAYPERSTEP) & ($day < $days)) {
            $algorithm->step((int) $init_id,(int)$day);
            $i++;
            $day++;
        }
        if ($day < $days) {
            echo '
            <script type="text/javascript">
            function gostep() {
                location = "index.php?task=symulation.step&id='.$init_id.'&days='.$days.'&day='.$day.'";
            }
            setTimeout("gostep()",1000);
            </script>
            ';
        } else {
            // eredmény megjelenítése
            echo '
            <script type="text/javascript">
            location = "index.php?task=symulation.showsymulation&id='.$init_id.'";
            </script>
            ';
        }
    }
}
?>
