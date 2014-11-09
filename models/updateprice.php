<?php
//Модель работающая с данными компонента

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');
class UpdatePriceModelUpdatePrice extends JModel
{
	public $time;
	public $counter;
	
	//Работа с БД
	public function getData($query)
	{
        $db = & JFactory::getDBO();
		$db->setQuery($query);
		$data = $db->loadResult();
		
		return $data;		
    }
	
	public function getQue($query)
	{
        $db = & JFactory::getDBO();
		$db->setQuery($query);
		$data = $db->query();
		
		return $data;		
    }
	//Получение пользовательского файла
	public function getFile()
	{
		$path = JPATH_COMPONENT_ADMINISTRATOR.DS."data".DS."1c_data.xml";
		
		if(key_exists("infile", $_FILES)){
			move_uploaded_file($_FILES["infile"]["tmp_name"], $path);
			$msg = "Файл XML был успешно загружен";
		}else{
			$msg = "!!!! Ошибка загрузки XML файла";
		}
		
		return $msg;
	}
	
	//Удаление обработанного файла
	public function getUnlink()
	{
		$file = JPATH_COMPONENT_ADMINISTRATOR.DS."data".DS."1c_data.xml";
		if(file_exists($file))
		{
			unlink($file);
		}		
	}
		
	// "Размножение" OEM ремней
	public function allBelt($all_belt, $oid, $all)
	{
		$file_oem= file($all_belt, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
		foreach($file_oem as $id_oem)
		{
			if(strpos($id_oem, strval($oid)) !== false)
			{
				$price_id_min = $this->getData($this->getProdPriceId($id_oem));
				for($i=0; $i<=3; $i++)
				{
					$check = $this->getQueUpd($all[$i], $price_id_min+$i); 
				}
				echo "$this->counter- Позиция $id_oem обновлена<br />";
				$this->counter++;
			}
		}
		if(isset($check))
		{
			return true;
		}else{
			return false;
		}
	}
	public function getProdPriceId($id)
	{
		$query = "SELECT MIN(c.product_price_id) FROM jos_vm_product_price AS c LEFT JOIN jos_vm_product AS p ON c.product_id=p.product_id WHERE p.product_sku='".$id."'";
		
		return $query;
	}
	public function getQueUpd($price, $price_id)
	{
		$upd_query="UPDATE jos_vm_product_price SET product_price=".$price." WHERE product_price_id=".$price_id;
		$check = $this->getQue($upd_query);
		
		return $check;
	}
	//Фильтрация введенной даты и расчет интервала обновления
	protected function period($data)
	{
		$out = 	htmlspecialchars(strip_tags(trim($data)));
		$time = time();
		if(strlen($out) != 10 || $out{2} != "." || $out{5} != ".")
		{
			$msgperiod = "<h1>!!!! Указали неверный формат даты. Update period будет утановлен 8 дней </h1>";
			$upd_period = 691200;
		}else{	
			$upd_period = $time - strtotime($out);
			$msgperiod = "<h1>Update period утановлен ".($upd_period/60/60/24)."дней</h1>";
		}
		echo $msgperiod;
		
		return $upd_period;
	}
	
	//Определение как давно менялась цена
	protected function compare_date($ch_time_str, $time, $upd_period)
	{
		$timestamp = strtotime($ch_time_str);
		if(($time - $upd_period)>$timestamp)
		{
			$write_flag = true;
		}else{
			$write_flag = false;
		}
		
		return $write_flag;
	}
	
	public function getPostDate()
	{
		if(key_exists("lastdate", $_POST))
		{
			$upd_period = $this->period($_POST["lastdate"]);
		}else{
			$upd_period = $this->period(true);
		}
		
		return $upd_period;
	}
	
	//Обработка пользовательских данных
	public function getUpdate()
	{
		$start = microtime(true);
		$time = time();
		
		$upd_period = $this->getPostDate();
				
		//Подгружем файл OEM ремней
		$all_belt = JPATH_COMPONENT_ADMINISTRATOR.DS."data".DS."all_belt.txt";
		$xmlfile = JPATH_COMPONENT_ADMINISTRATOR.DS."data".DS."1c_data.xml";
		if(!file_exists($all_belt))
		{
			$err =  "!!!! Нет файла дополнительных категорий ремней all_belt.txt";
			
			return $err;
		}
		if(!file_exists($xmlfile))
		{
			$err =  "!!!! Нет файла файла выгрузок 1c_data.xml";
			
			return $err;
		}
		$file_oem= file($all_belt, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
		
		//Парсим XML	
		$parse = new SimpleXMLElement(file_get_contents($xmlfile));
		foreach($parse->item as $val)
		{
			$dt[0] = (string)$val->price1["date"];
			$dt[1] = (string)$val->price2["date"];
			$dt[2] = (string)$val->price3["date"];
			$dt[3] = (string)$val->price4["date"];
			//Запихиваем цены в массив, что бы потом удобнее было их брать при формировании запроса
			$all[0] = (string)$val->price1;
			$all[1] = (string)$val->price2;
			$all[2] = (string)$val->price3;
			$all[3] = (string)$val->price4;		
			
			//Если дата изменения цены меньше интервала, то пропускаем цикл. Для оптимизации сравниваются все 4 цены
			if(strcmp($dt[0], $dt[1]) === 0 && strcmp($dt[1], $dt[2]) === 0 && strcmp($dt[2], $dt[3]) === 0)
			{
				if($this->compare_date($dt[0], $time, $upd_period))
				{
					continue 1;
				}
			}else{
				if($this->compare_date($dt[0], $time, $upd_period) && $this->compare_date($dt[1], $time, $upd_period) && $this->compare_date($dt[2], $time, $upd_period) && $this->compare_date($dt[3], $time, $upd_period))
				{
					continue 1;
				}		
			}
			
			//Если нет остатков устанавливаем цену в 000.1
			if($val->balance == 0)
			{
				$all[0] = $all[1] = $all[2] = $all[3] = 0.001;
			}
			
			//Если ремень относится к "размноженным" размножаем его 
			if(substr($val->id, 0, 3) === "101")
			{
				if($val->oem == "true")
				{
					$all[3] = $all[2] = $all[1] = $all[0];
				}			
				if($this->allBelt($all_belt, $val->id, $all) === TRUE)
				{
					continue 1;
				}				
			}
			
			//Получение младшего идетификатора цены (одного из 4х), соотв розничной цене, остальные три идут с инкрементом 1 
			$price_id_min = $this->getData($this->getProdPriceId($val->id));
			
			//Счетчик и ограничение на кол-во обрабатываемых записей (при большом кол-ве экспоненциально растут тормоза, по свободе навесить триггеры на базу)
			$this->counter ++;
			if($this->counter > 1000)
			{
				$msg =  "Часть данных была обновлена, но изменений слишком много, воспользуйтесь альтернативной функцией загрузки через scv файл";
				
				return $msg;
			}
			
			//Отлавливане позиций которых нет в базе магазина
			if($price_id_min === NULL)
			{
				echo "<strong>$this->counter- Позиция $val->id нет в магазине<br /></strong>";
				continue 1;
			}else{
				echo "$this->counter- Позиция $val->id обновлена<br />";
			}
			for($i=0; $i<=3; $i++)
			{
				$this->getQueUpd($all[$i], $price_id_min+$i);
			}		
		}

		$time = microtime(true) - $start;
		$result = "Script work $time second";
		
		return $result;
	}
}
?>
