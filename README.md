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
* The Backup Script
* The Upload Script
* The Process Script
* The Browse Front End
