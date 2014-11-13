<?
/**
 * Этот класс добавляет новые элементы из бд рейтинга
 * Обновляет значения рейтинга
 * @version 0.10
 * @param type 
 * @return string
 * @copyright copyright information
 */

class ratingSyncer
{
	function __construct($options)
	{
		$option = array();
		$option['driver']   = 'mysqli';
		$option['host']     = DB_HOSTNAME;
		$option['user']     = DB_USERNAME;
		$option['password'] = DB_PASSWORD;
		$option['database'] = DB_NAME;
		$option['prefix']   = '';

		$db = JDatabase::getInstance($option);
		$this->db = $db;
		$this->options = $options;
		$this->dataReturned = new stdClass();
	}

	function getRemoteRatingData()
	{
		$sql = "SELECT a.nid, a.id, b.field_clinic_rating_value AS currentValue
		FROM idevels_probirka_integration AS a
		LEFT JOIN field_data_field_clinic_rating AS b
		ON (a.nid=b.revision_id)";

		$this->db->setQuery($sql);
		$this->db->return = $this->db->loadObjectList();
	
		foreach ($this->db->return AS $item)
		{
			$itemData = K2Helper::getK2ContentById($item->id);
			if (isset($itemData->id))
			{
				$return[] = $item;
			}
		}

		return $return;
	}

	function insertClinicsTable($objects)
	{
		$db = JFactory::getDbo();
		$modelItem = new stdClass();

		foreach ($objects AS $key => $item)
		{
			$itemRating = clinicsModelHelper::getClinicsRating($item->id);

			if (isset($itemRating->clinic_id) === false)
			{
				$modelItem->clinic_id = $item->id;
				$modelItem->clinic_nid = $item->nid;
				$modelItem->currentValue = $item->currentValue;

				$db->insertObject('#__clinics_rating', $modelItem);

				$items[] = 'Новая клиника: '.$item->id.' добавлена!';
			}
		}

		if (!empty($items))
			return $items;
		else
			return 'Новых клиник не было обнаружено!';

	}

	function updateClinicsCurrentValue()
	{
		$db = JFactory::getDbo();
		$modelItem = new stdClass();

		$itemRatingClinics = clinicsModelHelper::getAllClinicsRating();
		$remoteDataRating = $this->getRemoteRatingData();

		foreach ($remoteDataRating AS $remoteItem)
		{
			$localItem = clinicsModelHelper::getClinicsRating($remoteItem->id);

			if ($remoteItem->currentValue !== $localItem->currentValue)
			{
				$dailyChange = $remoteItem->currentValue - $localItem->currentValue;
				$modelItem->clinic_id = $localItem->clinic_id;
				$modelItem->currentValue = $remoteItem->currentValue;
				$modelItem->dailyChange = $dailyChange;

				$db->updateObject('#__clinics_rating', $modelItem, 'clinic_id');

				$items[] = $remoteItem->id.' изменено '.$dailyChange;
			}
		}


		if (!empty($items))
			return $items;
		else
			return 'Нет изменений в рейтинге клиник за текущий период';

	}

	function initSyncer()
	{
		$objects = new stdClass();
		try
		{
			$data = $this->getRemoteRatingData();
			$objects->insertClinicsTable = $this->insertClinicsTable($data);
			$objects->updateClinicsCurrentValue = $this->updateClinicsCurrentValue();
		}
		catch (CodesExceptionHandler $e)
		{
			die('Internal error');
		}

		return $objects;
	}
	
}

?>