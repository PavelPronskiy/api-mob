<?
/**
 * Этот класс добавляет новые элементы из бд рейтинга
 * Обновляет значения рейтинга
 * @version 0.10
 * @param type 
 * @return string
 * @copyright copyright information
 */

class doctorsSyncer
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
		$ret = new stdClass();
		$this->K2Helper = new K2Helper();
		$this->RatingSQLHelper = new RatingSQLHelper($options);
	}


	function getDoctorBio($objects)
	{
		$sql = "SELECT 
			a.entity_id,
			a.field_doctor_experience_value AS experience
		FROM field_data_field_doctor_experience AS a
		WHERE a.entity_id = {$objects->contentId}";

		$ret->conn = $this->db->setQuery($sql);
		$ret->data = $this->db->loadObject();

		if (isset($ret->data->entity_id))
		{
			$ret->newdata->entity_id = $ret->data->entity_id;
			$ret->newdata->introtext = $ret->data->experience;
			return $ret->newdata;
		}
		else
		{
			return false;
		}
	}

	function getDoctorsData()
	{
		$sql = "SELECT 
			a.nid AS nid,
			a.title AS fio,
			from_unixtime(a.created) AS createdAt,
			from_unixtime(a.changed) AS updatedAt,
			b.field_doctor_rating_value AS currentValue,
			c.field_doctor_profession_value AS specialty,
			d.field_doctor_job_nid AS clinicNid,
			e.title AS clinicName,
			f.id AS clinicId,
			h.uri AS uri,
			i.field_votes_count_online_value AS estimationCount
		FROM node AS a
		LEFT JOIN field_data_field_doctor_rating AS b
		ON (a.nid=b.entity_id)
		LEFT JOIN field_data_field_doctor_profession AS c
		ON (a.nid=c.entity_id)
		LEFT JOIN field_data_field_doctor_job AS d
		ON (a.nid=d.entity_id)
		LEFT JOIN node AS e
		ON (d.field_doctor_job_nid=e.nid)
		LEFT JOIN idevels_probirka_integration AS f
		ON (d.field_doctor_job_nid=f.nid)
		LEFT JOIN field_data_field_doctor_photo AS g
		ON (a.nid=g.entity_id)
		LEFT JOIN file_managed AS h
		ON (g.field_doctor_photo_fid=h.fid)
		LEFT JOIN field_data_field_votes_count_online AS i
		ON (i.entity_id=a.nid)
		WHERE a.type = 'doctor'
		AND a.status = '1'";

		$this->db->setQuery($sql);
		$this->doctorsList = $this->db->loadObjectList();

		foreach ($this->doctorsList AS $k=>$item)
		{
			$getClinic = $this->K2Helper->getK2ContentById($item->clinicId);
			if (isset($getClinic->id))
			{
				$ret->{$k}->nid = $item->nid;
				$ret->{$k}->imageURL = isset($item->uri) ? str_replace('public://', RATING_PHOTOS_PATH.DS, $item->uri) : RATING_PHOTOS_PATH.DS.'default_images/doctor-logo_0.png';
				$ret->{$k}->fio = $item->fio;
				$ret->{$k}->clinicName = $item->clinicName;
				$ret->{$k}->clinicId = $item->clinicId;
				$ret->{$k}->regionTitle = $this->K2Helper->getCategoryById($getClinic->catid)->name;
				$ret->{$k}->regionId = $getClinic->catid;
				$ret->{$k}->currentValue = isset($item->currentValue) ? $item->currentValue : 0;
				$ret->{$k}->dailyChange = 0;
				$ret->{$k}->feedbackCount = $this->RatingSQLHelper->getDoctorsFeedbacks($item->nid)->count;
				$ret->{$k}->estimationCount = $item->estimationCount;
				$ret->{$k}->updatedAt = $item->updatedAt;
				$ret->{$k}->createdAt = $item->createdAt;
				$ret->{$k}->specialty = $item->specialty;
			}
		}

		return $ret;
	}

	function insertDoctorsTable($objects)
	{
		$db = &JFactory::getDBO();
		$timestamp = date("r", time());
		$items = array();

		foreach ($objects AS $key => $item)
		{
			if (isset($item->nid))
			{
				if (doctorsModelHelper::getDoctorByNid($item->nid) === false)
				{
					$db->insertObject('#__doctors_rating', $item, null);
					$items[] = $timestamp.' | новый доктор: '.$item->fio.' добавлен!';
				}
			}
		}

		if (empty($items))
			$items = 'no inserts';

		return $items;
	}

	function updateDoctorsTable($objects)
	{
		$db = JFactory::getDbo();
		$items = array();
		$timestamp = date("r", time());
		$doctorsModelHelper = new doctorsModelHelper();

		foreach ($objects AS $item)
		{
			if (isset($item->nid))
			{
				if ($doctorsModelHelper->getDoctorByNid($item->nid)->currentValue !== $item->currentValue)
				{
					$item->dailyChange = $doctorsModelHelper->getDoctorByNid($item->nid)->currentValue - $item->currentValue;
					$db->updateObject('#__doctors_rating', $item, 'nid');
					$items[] = $timestamp. ' | '.$item->fio.' изменено: '.$item->currentValue.' dailyChange: '.$item->dailyChange;
				}
			}
		}

		if (empty($items))
			$items = 'no updates';

		return $items;
	}

	function initSyncer()
	{
		$objects = new stdClass();
		try
		{
			$data = $this->getDoctorsData();
			$objects->insertDoctorsTable = $this->insertDoctorsTable($data);
			$objects->updateDoctorsTable = $this->updateDoctorsTable($data);
		}
		catch (CodesExceptionHandler $e)
		{
			die('Internal error');
		}

		return $objects;
	}
	
}

?>