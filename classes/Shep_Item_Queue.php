<?php
class Shep_Item_Queue extends Shep_Item
{
	protected $required = array(
		'path' => NULL,
		'name' => NULL,
		'size' => NULL,
		'uploaded' => NULL,
		'service' => NULL,
		'type' => NULL,
	);

	protected $optional = array(
		'id' => NULL,
		'title' => NULL,
		'description' => NULL,
		'tabs' => NULL,
		'category' => NULL,
		'public_allowed' => NULL,
		'friend_allowed' => NULL,
		'family_allowed' => NULL,
		'upload_token' => NULL,
	);
}
?>
