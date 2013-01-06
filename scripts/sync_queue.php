<?php
/*
 * Sync Queue Cli Script
 * This script will erase the queue and create a new one with the files in the upload directory.
 *
 * Usage:
 * php sync_queue.php
 */

require '../classes/autoloader.php';

//Make sure we can find the Zend stuff
$path = '../classes/';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
require_once 'Zend/Loader.php';

//Initialize objects
$cfg = new Shep_Config();
$db = new Shep_Db_Mongo($cfg->get('db_mongo'));
$dao = new Shep_Dao_Queue($cfg->get('dao_queue'), $db);

$addcfg = $cfg->get('add');

$queue = $dao->getQueue();
echo "The queue currently has " . count($queue) . " files in it.\n";

$count = 0;
foreach ($queue as $key=>$file)
{
	if (!isset($file['path']) || !file_exists($file['path']))
	{
		//File doesnt exist or has no path - remove from queue.
		$dao->removeFromQueue($file);
		$count++;
	}
}
echo $count . " entries removed from queue.\n";

$files = glob($addcfg['upload_path'] . "*");
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$supported_types = array_merge(Shep_Service_Dao_Flickr::getSupportedFileTypes());
$count = 0;
if (is_array($files))
{
	foreach ($files as $file)
	{
		if ($dao->getQueue(array('path' => $file)) === array())
		{
			//File exists but is not in queue.
			$fileType = finfo_file($finfo, $file);
			if (array_search($fileType, $supported_types) !== FALSE)
			{
				$fileObject = array(
					'path' => $file,
					'name' => basename($file),
					'size' => filesize($file),
					'uploaded' => FALSE,
				);
				$dao->addToQueue($fileObject);
				$count++;
			}
		}
	}
	echo $count . " files added to queue.\n";
}

$queue = $dao->getQueue();
echo "The queue now has " . count($queue) . " files in it.\n";
?>
