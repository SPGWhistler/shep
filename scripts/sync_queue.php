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
$queue = new Shep_Queue($cfg->get('queue'));
$list = new Shep_Service_List($cfg->get('service_list'), $cfg, $queue);

$addcfg = $cfg->get('add');

$items = $queue->getItems();
echo "The queue currently has " . count($items) . " files in it.\n";

$count = 0;
foreach ($items as $item)
{
	if (!isset($item->path) || !file_exists($item->path))
	{
		//File doesnt exist or has no path - remove from queue.
		$queue->removeItem($item->id);
		$count++;
	}
}
echo $count . " entries removed from queue.\n";

$unsupported_files = array();
$files = glob($addcfg['upload_path'] . "*");
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$count = 0;
if (is_array($files))
{
	foreach ($files as $file)
	{
		if ($queue->getItemByProperty('path', $file) === FALSE)
		{
			//File exists but is not in queue.
			$fileType = finfo_file($finfo, $file);
			$serviceName = $list->isSupportedFileType($fileType);
			if ($serviceName !== FALSE)
			{
				$service = $list->getService($serviceName);
				$fileObject = new Shep_Item_Queue(array(
					'path' => $file,
					'name' => basename($file),
					'size' => filesize($file),
					'uploaded' => 0,
					'service' => $serviceName,
					'type' => $fileType,
				));
				echo "Is " . $fileObject->name . " uploaded already? Checking... ";
				if ($newObject = $service->findFile($fileObject))
				{
					echo "Yes.\n";
					$fileObject = $newObject;
				}
				else
				{
					echo "No.\n";
				}
				$queue->addItem($fileObject);
				$count++;
			}
			else
			{
				$unsupported_files[] = array(
					'name' => basename($file),
					'type' => $fileType,
				);
			}
		}
	}
	echo $count . " files added to queue.\n";
}

$items = $queue->getItems();
echo "The queue now has " . count($items) . " files in it.\n";

if (count($unsupported_files))
{
	echo "Also found the following files that did not have a supporting service:\n";
	echo "File Name		Mime Type\n";
	foreach ($unsupported_files as $file)
	{
		echo $file['name'] . "			" . $file['type'] . "\n";
	}
}
?>
