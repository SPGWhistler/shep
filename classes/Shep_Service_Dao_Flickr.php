<?php
class Shep_Service_Dao_Flickr extends Shep_Service_Dao
{
	protected $flickr = NULL;

	public function getFlickr()
	{
		if (is_null($this->flickr))
		{
			$this->flickr = new phpFlickr($this->config['api_key'], $this->config['secret']);
			$this->flickr->setToken($this->config['auth_token']);
		}
		return $this->flickr;
	}

	public function uploadFile($fileObject)
	{
		if (file_exists($fileObject->path))
		{
			$fileObject->title = (isset($fileObject->title)) ? $fileObject->title : $fileObject->name;
			$result = $this->getFlickr()->async_upload(
				$fileObject->path,
				$fileObject->title,
				$fileObject->description,
				$fileObject->tags,
				$fileObject->public_allowed,
				$fileObject->friend_allowed,
				$fileObject->family_allowed
			);
			if ($result !== FALSE)
			{
				$fileObject->upload_token = $result;
				$fileObject->uploaded = 1;
				$this->queue->addItem($fileObject);
				return TRUE;
			}
			else
			{
				$this->last_error = $this->getFlickr()->getErrorMsg();
			}
		}
		return FALSE;
	}

	public function checkUploadStatus($fileObject)
	{
		/*
		$status = $f->photos_upload_checkTickets($result);
		if (isset($status[0]['photoid']))
		*/
	}

	public function isUploaded($fileObject)
	{
		$exif = exif_read_data($fileObject->path);
		$date_time = strtotime($exif['DateTime']);
		print_r($this->getFlickr()->photos_search(array('min_taken_date' => $date_time, 'max_taken_date' => $date_time)));
		/*
		foreach ($exif as $key=>$value)
		{
			echo $key . " ";
			if (strtolower(substr(trim(fgets(STDIN)), 0, 1)) === "")
			{
				print_r($value);
				echo "\n";
			}
		}
		*/
	}

	public static function getSupportedFileTypes()
	{
		return array(
			'image/jpeg',
		);
	}
}
?>
