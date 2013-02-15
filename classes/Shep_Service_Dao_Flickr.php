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

	public function findFile($fileObject)
	{
		$exif = exif_read_data($fileObject->path);
		if (is_array($exif) && isset($exif['DateTime']))
		{
			$date_time = date('Y-m-d H:i:s', strtotime($exif['DateTime']));
			$result = $this->getFlickr()->photos_search(array('user_id' => 'me', 'min_taken_date' => $date_time, 'max_taken_date' => $date_time));
			if (is_array($result['photo']) && count($result['photo']))
			{
				$fileObject->title = $result['photo'][0]['title'];
				$fileObject->upload_token = $result['photo'][0]['id'];
				$fileObject->public_allowed = $result['photo'][0]['ispublic'];
				$fileObject->friend_allowed = $result['photo'][0]['isfriend'];
				$fileObject->family_allowed = $result['photo'][0]['isfamily'];
				$fileObject->uploaded = 1;
				if (count($result['photo']) > 1)
				{
					$duplicates = $fileObject->possible_uploaded_duplicates || array();
					foreach ($result['photo'] as $photo)
					{
						$duplicates[] = $photo['id'];
					}
					$fileObject->possible_uploaded_duplicates = $duplicates;
				}
				return $fileObject;
			}
		}
		return FALSE;
	}

	public static function getSupportedFileTypes()
	{
		return array(
			'image/jpeg',
		);
	}
}
?>
