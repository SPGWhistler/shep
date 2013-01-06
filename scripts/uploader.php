<?php
/*
 * Uploader Cli Script
 * This script uploads media files to a third party service via http.
 *
 * Usage:
 * php uploader.php [-D]
 *
 * -D Do not daemonize.
 */

require '../classes/autoloader.php';

//Make sure we can find the Zend stuff
$path = '../classes/';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
require_once 'Zend/Loader.php';

//Get configuration
$cfg = new Shep_Config();
$config = $cfg->get('uploader');

//Make sure no other uploader script is already running.
$lock = fopen(SHEP_BASE_PATH . $config['pid_path'], 'c+');
if (!flock($lock, LOCK_EX | LOCK_NB)) {
	die('already running');
}

//Initialize objects
$logger = new Shep_Txt_Logger($cfg->get('txt_logger'), SHEP_BASE_PATH . $config['error_log_path']);
$db = new Shep_Db_Mongo($cfg->get('db_mongo'));
$dao = new Shep_Dao_Queue($cfg->get('dao_queue'), $db);
$flickr = new Shep_Service_Dao_Flickr($cfg->get('php_flickr'), $dao);

//Set timezone
if ($config['timezone'] !== "PHP")
{
	date_default_timezone_set($config['timezone']);
}

//Get command line options
$options = getopt('D');

$logger->logMessage('starting');

//Daemonize
if (!isset($options['D']))
{
	switch ($pid = pcntl_fork()) {
		case -1:
			$logger->logMessage('unable to fork');
			die('unable to fork');
			break;
		case 0: // this is the child process
			break;
		default: // otherwise this is the parent process
			$logger->logMessage('forking to ' . $pid);
			fseek($lock, 0);
			ftruncate($lock, 0);
			fwrite($lock, $pid);
			fflush($lock);
			exit;
			break;
	}
	if (posix_setsid() === -1) {
		$logger->logMessage('could not setsid');
		die('could not setsid');
	}
	fclose(STDIN);
	fclose(STDOUT);
	fclose(STDERR);
	$stdIn = fopen('/dev/null', 'r'); // set fd/0
	$stdOut = fopen('/dev/null', 'w'); // set fd/1
	pcntl_signal(SIGTSTP, SIG_IGN);
	pcntl_signal(SIGTTOU, SIG_IGN);
	pcntl_signal(SIGTTIN, SIG_IGN);
	pcntl_signal(SIGHUP, SIG_IGN);
}

$queue = $dao->getQueue();
foreach ($queue as $file)
{
	if (isset($file['path']) && file_exists($file['path']))
	{
		/*
		echo "Uploading file\n";
		$flickr->uploadFile($file);
		echo "done.\n";
		exit;
		*/
	}
}

/*
$zg_config = $cfg->get('zend_gdata');
Zend_Loader::loadClass('Zend_Gdata_YouTube');
$httpClient = Zend_Gdata_AuthSub::getHttpClient($zg_config['yt_session_token']);
$yt = new Zend_Gdata_YouTube($httpClient, $zg_config['application_id'], $zg_config['client_id'], $zg_config['developer_key']);
$yt->setMajorProtocolVersion(2);
*/
/*
//$videoFeed = $yt->getVideoFeed(Zend_Gdata_YouTube::VIDEO_URI);
$videoFeed = $yt->getuserUploads('default');
$count = 1;
foreach ($videoFeed as $videoEntry) {
	echo "Entry # " . $count . "\n";
	printVideoEntry($videoEntry);
	echo "\n";
	$count++;
}

function printVideoEntry($videoEntry) 
{
  // the videoEntry object contains many helper functions
  // that access the underlying mediaGroup object
  echo 'Video: ' . $videoEntry->getVideoTitle() . "\n";
  echo 'Video ID: ' . $videoEntry->getVideoId() . "\n";
  echo 'Updated: ' . $videoEntry->getUpdated() . "\n";
  echo 'Description: ' . $videoEntry->getVideoDescription() . "\n";
  echo 'Category: ' . $videoEntry->getVideoCategory() . "\n";
  echo 'Tags: ' . implode(", ", $videoEntry->getVideoTags()) . "\n";
  echo 'Watch page: ' . $videoEntry->getVideoWatchPageUrl() . "\n";
  echo 'Flash Player Url: ' . $videoEntry->getFlashPlayerUrl() . "\n";
  echo 'Duration: ' . $videoEntry->getVideoDuration() . "\n";
  echo 'View count: ' . $videoEntry->getVideoViewCount() . "\n";
  echo 'Rating: ' . $videoEntry->getVideoRatingInfo() . "\n";
  echo 'Geo Location: ' . $videoEntry->getVideoGeoLocation() . "\n";
  echo 'Recorded on: ' . $videoEntry->getVideoRecorded() . "\n";
  
  // see the paragraph above this function for more information on the 
  // 'mediaGroup' object. in the following code, we use the mediaGroup
  // object directly to retrieve its 'Mobile RSTP link' child
  foreach ($videoEntry->mediaGroup->content as $content) {
    if ($content->type === "video/3gpp") {
      echo 'Mobile RTSP link: ' . $content->url . "\n";
    }
  }
  
  echo "Thumbnails:\n";
  $videoThumbnails = $videoEntry->getVideoThumbnails();

  foreach($videoThumbnails as $videoThumbnail) {
    echo $videoThumbnail['time'] . ' - ' . $videoThumbnail['url'];
    echo ' height=' . $videoThumbnail['height'];
    echo ' width=' . $videoThumbnail['width'] . "\n";
  }
}
*/
/*
$myVideoEntry = new Zend_Gdata_YouTube_VideoEntry();
$filesource = $yt->newMediaFileSource('/home/tpetty/uploads/MVI_4023.MOV');
$filesource->setContentType('video/quicktime');
$filesource->setSlug('/home/tpetty/uploads/MVI_4023.MOV');
$myVideoEntry->setMediaSource($filesource);
$myVideoEntry->setVideoTitle('My Test Movie');
$myVideoEntry->setVideoDescription('My Test Movie');
$myVideoEntry->setVideoCategory('Autos');
$myVideoEntry->SetVideoTags('cars, funny');
$myVideoEntry->setVideoDeveloperTags(array('mydevtag', 'anotherdevtag'));
$uploadUrl = 'http://uploads.gdata.youtube.com/feeds/api/users/default/uploads';
try {
	$newEntry = $yt->insertEntry($myVideoEntry, $uploadUrl, 'Zend_Gdata_YouTube_VideoEntry');
} catch (Zend_Gdata_App_HttpException $httpException) {
	echo $httpException->getRawResponseBody();
} catch (Zend_Gdata_App_Exception $e) {
	echo $e->getMessage();
}
*/

$logger->logMessage('exiting');
?>
