<?php
class Shep_Txt_Logger
{
	protected $path = NULL;
	protected $handle = NULL;

	public function __construct($config, $path)
	{
		$this->config = $config;
		$this->path = $path;
	}

	public function setHandle($handle)
	{
		$this->handle = $handle;
		return TRUE;
	}

	public function getHandle()
	{
		if ($this->handle === NULL)
		{
			$this->handle = fopen($this->path, 'a');
		}
		return $this->handle;
	}

	public function logMessage($message)
	{
		return fwrite($this->getHandle(), $this->formatMessage($message));
	}

	public function formatMessage($message)
	{
		$message =
			//Human date format
			date($this->config['date_format']) .
			$this->config['seperator'] .
			//Unix time stamp
			date('U') .
			$this->config['seperator'] .
			//Process Id
			getmypid() .
			$this->config['seperator'] .
			//Message
			'"' . $message . '"' .
			//End of line
			$this->config['eol'];
		return $message;
	}
}
?>
