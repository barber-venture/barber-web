<?php
/**
 * Review Fixture
 */
class ReviewFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'merchant_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'appointment_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'rating' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'comment' => 'upto 5'),
		'review' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'user_id' => 1,
			'merchant_id' => 1,
			'appointment_id' => 1,
			'rating' => 1,
			'review' => 'Lorem ipsum dolor sit amet',
			'created' => '2016-11-13 13:34:24',
			'modified' => '2016-11-13 13:34:24'
		),
	);

}
