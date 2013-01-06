<?php
class Shep_Service_Dao_Youtube extends Shep_Service_Dao
{
	protected $youtube = NULL;

	public function getYoutube()
	{
		if (is_null($this->youtube))
		{
			Zend_Loader::loadClass('Zend_Gdata_YouTube');
			$httpClient = Zend_Gdata_AuthSub::getHttpClient($this->config['yt_session_token']);
			$this->youtube = new Zend_Gdata_YouTube($httpClient, $this->config['application_id'], $this->config['client_id'], $this->config['developer_key']);
			$this->youtube->setMajorProtocolVersion(2);
		}
		return $this->youtube;
	}

	public function uploadFile($fileObject)
	{
		if (isset($fileObject['path']) && file_exists($fileObject['path']))
		{
			$fileObject['name'] = (isset($fileObject['name'])) ? $fileObject['name'] : '';
			$fileObject['title'] = (isset($fileObject['title'])) ? $fileObject['title'] : $fileObject['name'];
			$fileObject['description'] = (isset($fileObject['description'])) ? $fileObject['description'] : '';
			$fileObject['category'] = (isset($fileObject['category'])) ? $fileObject['category'] : '';
			$fileObject['tags'] = (isset($fileObject['tags'])) ? $fileObject['tags'] : '';

			$result = FALSE;
			$myVideoEntry = new Zend_Gdata_YouTube_VideoEntry();
			$filesource = $this->getYoutube()->newMediaFileSource($fileObject['path']);
			$filesource->setContentType('video/quicktime'); //@TODO
			$filesource->setSlug($fileObject['path']); //@TODO
			$myVideoEntry->setMediaSource($filesource);
			$myVideoEntry->setVideoTitle($fileObject['title']);
			$myVideoEntry->setVideoDescription($fileObject['description']);
			$myVideoEntry->setVideoCategory($fileObject['category']);
			$myVideoEntry->SetVideoTags($fileObject['tags']);
			$uploadUrl = 'http://uploads.gdata.youtube.com/feeds/api/users/default/uploads';
			try {
				$newEntry = $this->getYoutube()->insertEntry($myVideoEntry, $uploadUrl, 'Zend_Gdata_YouTube_VideoEntry');
				$result = $newEntry->getVideoId();
			} catch (Exception $e) {
			}
			if ($result !== FALSE)
			{
				$fileObject['upload_id'] = $result;
				$fileObject['uploaded'] = TRUE;
				$this->dao->updateQueue($fileObject);
				return TRUE;
			}
		}
		return FALSE;
	}

	public function checkUploadStatus($fileObject)
	{
	}

	public function isUploaded($fileObject)
	{
	}

	public static function getSupportedFileTypes()
	{
		return array(
		);
	}
}
?>
