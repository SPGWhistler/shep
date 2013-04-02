shep
====

A media sheepherder that moves and manages the files to and from various places.


Installation
------------

* Checkout this repository some place outside of your document root.
* Cd into the repository.
* Run the following command: php install.php
* Follow the prompts.


Usage
-----

The Shep project contains (or will eventually) several parts. The main parts are:
* The Transfer Script
	The Transfer Script runs on the computer that contains the media files directly from the camera or source. It doesn't matter how they get to that computer - so long as they are added to a directory some place. Then the transfer script runs via cron and uploads these media files to the server - which is running the rest of the scripts.
* The REST Api
	All scripts access the queue and other things via the rest api.
* The Upload Script
	The Upload Script runs on the server via cron. It watches the queue and starts the uploading and processing of media added to it.
* The Sync Queue Script
	The Sync Queue Script is a manual script that can be run to clean up the queue if needed. It syncronizes the queue with the media upload directories.
* Add items here as they are developed.
