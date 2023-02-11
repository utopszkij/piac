<?php
/**
* piac szimulációs algoitmus
*/
class _Algorithm {

	protected $init;
	protected $loged;

	function __construct($init_id) {
		// init beolvasása az adatbázisból
		$q = new \RATWEB\DB\Query('inits');
		$this->init = $q->where('id','=',$init_id)->first();
		$this->loged = $_SESSION['loged'];
	}

	/**
	 * product_time -ba tárolás (insert vagy update) 
	 */	
	protected function saveProduct_time(int $day, string $fieldName, array $values) {
		foreach ($values as $productId => $value) {
			$q = new \RATWEB\DB\Query('product_time');
			$record = $q->where('init_id','=',$this->init->id)
						->where('day','=',$day)
						->where('product_id','=',$productId)
						->first();
			if (isset($record->id)) {
				$record->$fieldName = $value;
				$q->where('id','=',$record->id)->update($record);
			} else {
				if ($day > 0) {
					$record = $q->where('init_id','=',$this->init->id)
					->where('day','=',($day - 1))
					->where('product_id','=',$productId)
					->first();
				} else {						
					$record = new \RATWEB\DB\Record();
					$record->id =0;
					$record->required = 0;
					$record->quantity = 0;
					$record->price = 0;
					$record->selfcost = 0;
				}	
				$record->init_id = $this->init->id;
				$record->product_id = $productId;
				$record->day = $day;
				$record->$fieldName = $value;
				$q->insert($record);
			}			
		} // foreach
	}

	/**
	 * fogyasztási cikk valós igény számítás és tárolás az adatbázisba
	 * @param int $day
	 * @return array [productId => required]
	 */
	protected function fszCalculate(int $day): array {
		$result = [];
		$q = new \RATWEB\DB\Query('products');
		$recs = $q->where('user_id','=',$this->loged)
				->where('type','=','fogy.cikk')
				->all();
		foreach ($recs as $rec) {
			$result[$rec->id] = $rec->required * $this->init->population;
		}		
		// tárolás az adatbázisba
		$this->saveProduct_time($day, 'required', $result);
		return $result;
	}

	/**
	 * termelő eszköz és alapanyag, alkatrész igény számítás és tárolás adatbázisba
	 * a paraméterben lévő $fsz mennyiségű fogyasztási cikk gyártásához
	 * @param int $day
	 * @param array $fsz
	 * @return array [productId => required]
	 */
	protected function tszCalculate(int $day,array $fsz):array {
		$result = [];
		// tresult tömb inicializálása
		$q = new \RATWEB\DB\Query('products');
		$recs = $q->where('user_id','=',$this->loged)
				->where('type','<>','fogy.cikk')
				->all();
		foreach ($recs as $rec) {
			$result[$rec->id] = 0;
		}		
		// szükséglet számítás az $fsz ben lévő termékek összetevöihez
		foreach ($fsz as $productId => $required) {
			$this->_tszCalculate($result, $productId, $required);
		}
		// tárolás az adatbázisba
		$this->saveProduct_time($day, 'required', $result);
		return $result;
	}

	/**
	 * $productId összetevőinek szükséges mennyiségét számolja és hozzáadja a result tömbben lévőhöz
	 * REKURZIV eljárás!
	 */
	protected function _tszCalculate(array &$result, $productId, $quantity) {
		$q = new \RATWEB\DB\Query('subproducts');
		$recs = $q->where('product_id','=',$productId)
				->all();
		foreach ($recs as $rec) {
			$result[$rec->subproduct_id] += $rec->quantity * $quantity;
			$this->_tszCalculate($result, $rec->subproduct_id, ($rec->quantity * $quantity));
		}
	}

	/**
	 * önköltségi ár számítása az $fsz, $tsz -ben lévő termékek mennyiségekhez
	 * és szükség esetén tárolás az adatbázisba véletlenszerű +-10% modosítással
	 * 
	 * @param int $day
	 * @param array $fsz
	 * @param array $tsz
	 * @param int $workHourPrice
	 * @param bool $ssave
	 * @return array [productId => price]
	 */
	protected function priceCalculate(int $day,array $fsz,array $tsz, 
		int $workHourPrice, bool $save = true): array {
		$result = [];
		// tresult tömb inicializálása
		foreach ($fsz as $productId => $fszi) {
			$result[$productId] = 0;
		}
		foreach ($tsz as $productId => $tszi) {
			$result[$productId] = 0;
		}
		// ár számítás 
		foreach ($result as $productId => $pricei) {
			$this->_priceCalculate($result, $productId, $workHourPrice);
		}
		$this->saveProduct_time($day, 'selfcost', $result);
		if ($save) {
			foreach ($result as $productId => $value) {
				$result[$productId] = $value * rand(90,110) / 100;
			}
			$this->saveProduct_time($day, 'price', $result);
		}
		return $result;
	}

	/**
	 * $productId önköltségi ár számítás REKURZIV eljárás
	 * REKURZIV eljárás!
	 * Ha már ki van számolva ez az önköltség, akkor nem csinál semmit
	 */
	protected function _priceCalculate(array &$result, $productId, $workHourPrice) {
		if ($result[$productId] == 0) {
			// saját product rekordban lévő munkaóra költsége
			$q = new \RATWEB\DB\Query('products');
			$product = $q->where('user_id','=',$this->loged)
						->where('id','=',$productId)
						->first();
			$result[$productId] = $product->workHours * $workHourPrice;			
			// plusz az összetevők árai
			$q = new \RATWEB\DB\Query('subproducts');
			$recs = $q->where('product_id','=',$productId)
					->all();
			foreach ($recs as $rec) {
				$this->_priceCalculate($result, $rec->subproduct_id, $workHourPrice);
				$result[$productId] += $rec->quantity * $result[$rec->subproduct_id];
			}
		}	
	}

	/**
	 * kifzettt összes munkabér és a fogyasztási cikkek megvásárlásához szükséges
	 * össz pénzmennyiség számítása és tárolása az adatbázisba
	 * amikor ez van hivva már ki van számolva a termelés mennyisége és a termékek ára
	 * @param int $day
	 * @param int $workHourPrice
	 * @return void
	 */
	protected function manyCalculate(int $day, int $workHourPrice) {
		$record = new \RATWEB\DB\Record();
		$record->id = 0;
		$record->init_id = $this->init->id;
		$record->day = $day;
		$record->workhourprice = $workHourPrice;
		// szükséges napi pénz mennyiség
		$q = new \RATWEB\DB\Query('product_time','pt');
		$rec = $q->join('LEFT','products','p','p.id','=','pt.product_id')
				->select(['sum(pt.price * p.required) msz1'])
				->where('pt.init_id','=',$this->init->id)
				->where('pt.day','=',$day)
				->where('p.type','=','fogy.cikk')
				->first();
		$record->requiredMany = $rec->msz1 * $this->init->population;		

		// kifizetett napi össz munkabér
		$q = new \RATWEB\DB\Query('product_time','pt');
		$rec = $q->join('LEFT','products','p','p.id','=','pt.product_id')
				->select(['sum(p.workHours * pt.quantity) mv'])
				->where('pt.init_id','=',$this->init->id)
				->where('pt.day','=',$day)
				->first();
		$record->validMany = $rec->mv * $workHourPrice;		

		// tárolás az adatbázisba
		$q = new \RATWEB\DB\Query('many_time');
		$q->insert($record);
	}

	/*
	* temelt mennyiség számítás és tárolás az adatbázisba szükségletek alapján
	* a tényleges igények szerint számított mennyiség véletlenszerüen (+- 10%)
	*         modosított értéke lesz letárolva
	* @param int $day
	* @param array $fsz
	* @param array $tsz
	* @param array $prices
	* @param int $workHourPrice
	* @return void
	*/
	protected function quantityCalculate(int $day,array $fsz, array $tsz, array $price, int $workHourPrice) {
		foreach ($fsz as $productId => $value) {
			$fsz[$productId] = $value * rand(90,110) / 100;
		}
		$this->saveProduct_time($day, 'quantity', $fsz);
		foreach ($tsz as $productId => $value) {
			$tsz[$productId] = $value * rand(90,110) / 100;
		}
		$this->saveProduct_time($day, 'quantity', $tsz);
	}

	/**
	 * fogyasztási cikk termelés adatbázisból
	 * @param int $day
	 * @return array [productId => quantity]
	 */
	protected function fszRead(int $day): array {
		$result = [];
		$q = new \RATWEB\DB\Query('product_time','pt');
		$recs = $q->join('LEFT','product','p','p.id','=','pt.product_id')
				->select(['p.id','pt.quantity'])
				->where('p.user_id','=',$this->loged)
				->where('p.type','=','fogy.cikk')
				->where('pt.init_id','=',$this->init->id)
				->where('pt.day','=',$day)
				->all();
		foreach ($recs as $rec) {
			$result[$rec->id] = $rec->quantity;
		}		
		return $result;
	}

	public function init(int $init_id) {
		$fsz = $this->fszCalculate(0);
		$tsz = $this->tszCalculate(0,$fsz);
		$workHourPrice = $this->init->workhourprice;
		$prices = $this->priceCalculate(0,$fsz,$tsz, $workHourPrice);
		$this->quantityCalculate(0,$fsz,$tsz, $prices, $workHourPrice);
		$this->manyCalculate(0,$workHourPrice);
	}
	
	/**
	* szimuláció naponkénti lépése
	* @param int $init_id szimuláció azonosító szám
	* @param int $day most ezt a napot kell feldolgozni
	*/
	public function  step(int $init_id, int $day) {
		if ($day > 0) {
			// előző napi fogy.cikk termelés beolvasása
			$ft = [];
			$q = new \RATWEB\DB\Query('product_time','pt');
			$recs = $q->join('LEFT','products','p','p.id','=','pt.product_id')
					->select(['p.id','pt.quantity'])
					->where('pt.init_id','=',$this->init->id)
					->where('p.type','=','fogy.cikk')
					->where('pt.day','=',($day - 1))
					->all();
			foreach ($recs as $rec) {
				$ft[$rec->id] = $rec->quantity;
			}		

			// keresletek számítása és tárolása
			//  - fogy.cikkek a tényleges szükséglet szerint
			$fsz = $this->fszCalculate($day);
			//  - termelő eszközök a fogy.cikkek elöző napi termelt mennyiségéhez
			$tsz = $this->tszCalculate($day,$ft);

			// ár változtatás elöző napi kereslet/kinálat alapján
			$prices = $this->priceChange($day); 

			// órabér változtatás kereslet/kinálat szerint
			$workHourPrice = $this->workHourPriceChange($day);

			// önköltségi ár számítása (az új órabérrel)
			$this->priceCalculate($day,$fsz,$tsz, $workHourPrice, false);

			// termelt mennyiség változtatás profit maximálásra törekedve
			$this->quantityChange($day,$workHourPrice);

			// kiadott/szükséges pénzmennyiség és az új órabér számítása, tárolása
			$this->manyCalculate($day, $workHourPrice);
		} else {
			$this->init($init_id);
		}	

	} 

	protected function getSumData(int $day, string $dataType): float {
		$result = 0;
		if ($dataType == 'workHourRequed') {
			$q = new \RATWEB\DB\Query('product_time','pt');
			$rec = $q->join('LEFT','products','p','p.id','=','pt.product_id')
			->select(['sum(p.workHours * pt.quantity) mk'])
			->where('pt.init_id','=',$this->init->id)
			->where('pt.day','=',$day)
			->first();
			$result = $rec->mk;
		}	
		if ($dataType == 'workHourPrice') {
			$q = new \RATWEB\DB\Query('many_time','pt');
			$rec = $q->where('pt.init_id','=',$this->init->id)
					 ->where('pt.day','=',$day)
					 ->first();
			$result = $rec->workhourprice;
		}	
		return $result;
	}

	/**
	 * product_time olvasása növekvő pritráta sorrendben
	 * @param int $day
	 * @return array [{product_id, price, selfcost, profitRata, quantity, required},...]
	 */
	protected function getProduct_time(int $day): array {
		$q = new \RATWEB\DB\Query('product_tyme');
		$q->setSql('select product_id, price, selfcost, ((price - selfcost) / selfcost) profitRata, quantity, required
		from product_time
		where init_id="'.$this->init->id.'" and day='.($day).'
		order by 3'
		);
		return $q->all(); // első a legkisebb, utolsó a legnagyobb profitráta !
	}


	// ---------------------------------------------------------------------------------

	/**
	 * órabér változtatás elöző napi munkaidő kereslet/kinálat és órabér függvényében
	 * az ini -ben beállított mértékü növeléssel vagy csökkentéssel
	 * @param int $day
	 * @return int
	 */
	protected function workHourPriceChange(int $day): int {
		// eloző napi munkaidő kereslet
		$kereslet = $this->getSumData($day - 1, 'workHourRequed');

		// munkaidő kinálat
		$kinalat = $this->init->population * $this->init->hourperday;

		// elöző napi órabér beolvasása
		$workHourPrice = $this->getSumData($day - 1,'workHourPrice');

		// órabér változtatás		 
		if ($kereslet > $kinalat) {
			// órabér növelés
			$result = $workHourPrice * $this->init->priceup / 100;
		}	
		if ($kereslet < $kinalat) {
			// órabér növelés
			$result = $workHourPrice * $this->init->pricedown / 100;
		}	
		return $result;
	}

	/*
	* temelt mennyiség változtatás és tárolás az adatbázisba profit maximálás szerint
	* profitráta számítás az elöző napi adatok alapján
	*         pozitiv és az átlag feletti profitráta esetén termelés bővités
	*         negativ vagy átlag alatti profitráta esetén termelés szükítés
	* @param int $day
	* @param int $workHourPrice
	* @return array [$productId => $quantity]
	*/
	protected function quantityChange(int $day, $workHourPrice) {
		// elöző napi termékenkénti profitráta számitás
		$products / $this->getProduct_time(($day - 1));
		// új termelési mennyiségek számítása
		$quantitys = []; // [productId => change] change árváltoztató szorzó.
		$i=0;
		foreach ($products as $product) {
			if (($product->profitRata > 0) and ($i > (count($products) / 2))) {
				$quantitys[$product->product_id] = $product->quantity * $this->init->quantityup / 100;
			}
			if (($product->profitRata < 0) or ($i < (count($products) / 2))) {
				$quantitys[$product->product_id] = $product->quantity * $this->init->quantitydown / 100;
			}
			$i++;
		}

		// új termelt mennyiségek tárolása az adatbázisba 
		$this->saveProduct_time($day, 'quantity', $quantitys);
		return $quantitys;
	}

	/**
	 * az elöző napi kereslet/kinálat alapján az ini-ben beállított  mértékű ár emelés vagy csökkentés
	 * új ár tárolása az adatbázisba
	 * @param int $day
	 * @return array [productId => price]
	 */
	protected function priceChange(int $day): array {
		$result = [];
		// elöző napi kereslet - kinálat adatok olvasása
		$q = new \RATWEB\DB\Query('product_time','pt');
		$recs = $q->select(['pt.product_id','pt.quantity','pt.required','pt.price'])
			->where('pt.init_id','=',$this->init->id)
			->where('pt.day','=',($day - 1))
			->all();

		// Új árak számítása	
		foreach ($recs as $rec) {
			if ($rec->required > $rec->quantity) {
				// ár növelés
				$result[$rec->product_id] = $rec->price * $this->init->priceup / 100;
			} else if ($rec->required < $rec->quantity) {
				// ár csökkentés
				$result[$rec->product_id] = $rec->price * $this->init->pricedown / 100;
			}
		}	

		// tárolás az adatbázisba
		$this->saveProduct_time($day, 'price',$result);
		return $result;
	}

}
?>
