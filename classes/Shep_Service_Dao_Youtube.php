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
			$fileObject['type'] = (isset($fileObject['type'])) ? $fileObject['type'] : 'video/quicktime';
			$fileObject['name'] = (isset($fileObject['name'])) ? $fileObject['name'] : '';
			$fileObject['title'] = (isset($fileObject['title'])) ? $fileObject['title'] : $fileObject['name'];
			$fileObject['description'] = (isset($fileObject['description'])) ? $fileObject['description'] : '';
			$fileObject['category'] = (isset($fileObject['category'])) ? $fileObject['category'] : 'People';
			$fileObject['tags'] = (isset($fileObject['tags'])) ? $fileObject['tags'] : '';

			$result = FALSE;
			$myVideoEntry = new Zend_Gdata_YouTube_VideoEntry();
			$filesource = $this->getYoutube()->newMediaFileSource($fileObject['path']);
			$filesource->setContentType($fileObject['type']);
			$filesource->setSlug($fileObject['path']); //@TODO Needed?
			$myVideoEntry->setMediaSource($filesource);
			$myVideoEntry->setVideoTitle($fileObject['title']);
			$myVideoEntry->setVideoDescription($fileObject['description']);
			$myVideoEntry->setVideoCategory($fileObject['category']);
			$myVideoEntry->SetVideoTags($fileObject['tags']);
			$uploadUrl = 'http://uploads.gdata.youtube.com/feeds/api/users/default/uploads';
			try {
				$newEntry = $this->getYoutube()->insertEntry($myVideoEntry, $uploadUrl, 'Zend_Gdata_YouTube_VideoEntry');
				$newEntry->setMajorProtocolVersion(2);
				$result = $newEntry->getVideoId();
			} catch (Zend_Gdata_App_HttpException $httpException) {
				$this->last_error = $httpException->getRawResponseBody();
			} catch (Zend_Gdata_App_Exception $e) {
				$this->last_error = $e->getMessage();
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
			'video/quicktime',
		);
	}
}
?>
