<?php
abstract class Shep_Service_Dao
{
	protected $last_error = NULL;

	public function __construct($config, $queue)
	{
		$this->config = $config;
		$this->queue = $queue;
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
