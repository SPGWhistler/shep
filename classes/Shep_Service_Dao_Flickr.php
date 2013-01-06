<?php
class Shep_Service_Dao_Flickr extends Shep_Service_Dao
{
	$this->flickr = NULL;

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
			$fileObject['title'] = ($fileObject['title']) ?: NULL;
			$fileObject['description'] = ($fileObject['description']) ?: NULL;
			$fileObject['tags'] = ($fileObject['tags']) ?: NULL;
			$result = $this->getFlickr()->async_upload($fileObject['path'],
				$fileObject['title'],
				$fileObject['description'],
				$fileObject['tags'],
				$this->config['public_allowed'],
				$this->config['friend_allowed'],
				$this->config['family_allowed']);
			if ($result !== FALSE)
			{
				//Add upload status to item.
				$fileObject['upload_token'] = $result;
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
}
?>
