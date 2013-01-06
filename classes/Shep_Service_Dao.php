<?php
abstract class Shep_Service_Dao
{
	protected $last_error = NULL;

	public function __construct($config, $dao)
	{
		$this->config = $config;
		$this->dao = $dao;
	}

	abstract public function uploadFile($fileObject);

	abstract public function checkUploadStatus($fileObject);

	abstract public function isUploaded($fileObject);

	abstract public static function getSupportedFileTypes();

	public function getLastError()
	{
		return $this->last_error;
	}
}
?>
