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
		if (isset($fileObject['path']) && file_exists($fileObject['path']))
		{
			$fileObject['title'] = (isset($fileObject['title'])) ? $fileObject['title'] : NULL;
			$fileObject['description'] = (isset($fileObject['description'])) ? $fileObject['description'] : NULL;
			$fileObject['tags'] = (isset($fileObject['tags'])) ? $fileObject['tags'] : NULL;
			$fileObject['public_allowed'] = (isset($fileObject['public_allowed'])) ? $fileObject['public_allowed'] : $this->config['public_allowed'];
			$fileObject['friend_allowed'] = (isset($fileObject['friend_allowed'])) ? $fileObject['friend_allowed'] : $this->config['friend_allowed'];
			$fileObject['family_allowed'] = (isset($fileObject['family_allowed'])) ? $fileObject['family_allowed'] : $this->config['family_allowed'];
			$result = $this->getFlickr()->async_upload(
				$fileObject['path'],
				$fileObject['title'],
				$fileObject['description'],
				$fileObject['tags'],
				$fileObject['public_allowed'],
				$fileObject['friend_allowed'],
				$fileObject['family_allowed']
			);
			if ($result !== FALSE)
			{
				$fileObject['upload_token'] = $result;
				$fileObject['uploaded'] = TRUE;
				$this->db->updateQueue($fileObject);
				return TRUE;
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
	}

	public static function getSupportedFileTypes()
	{
		return array(
			'image/jpeg',
		);
	}
}
?>
