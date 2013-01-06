<?php
abstract class Shep_Service_Dao
{
	public function __construct($config, $db)
	{
		$this->config = $config;
		$this->db = $db;
	}

	abstract public function uploadFile($fileObject);

	abstract public function checkUploadStatus($fileObject);

	abstract public function isUploaded($fileObject);

	abstract public static function getSupportedFileTypes();
}
?>
