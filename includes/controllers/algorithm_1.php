<?php
/**
* piac szimulációs algoitmus
*/
include_once __DIR__.'/_algorithm.php';
class Algorithm extends _Algorithm {

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
		// minimál órabér alá nem csökkenhet
		if ($result < $this->init->minworkhourprice) {
			$result = $this->init->minworkhourprice;
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
		$products = $this->getProduct_time(($day - 1));
		// új termelési mennyiségek számítása
		$quantitys = []; // [productId => change] change árváltoztató szorzó.
		$i=0;
		foreach ($products as $product) {
			if ($product->profitRata > 0) {
				$quantitys[$product->product_id] = $product->quantity * $this->init->quantityup / 100;
			}
			if ($product->profitRata < 0) {
				$quantitys[$product->product_id] = $product->quantity * $this->init->quantitydown / 100;
			}
			$i  ;
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
		$recs = $this->getProduct_time($day - 1);

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
