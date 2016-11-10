<?php
/**
 * MerchantWorkingDay Fixture
 */
class MerchantWorkingDayFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'merchant_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'day' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
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
			'merchant_id' => 1,
			'day' => 1,
			'created' => '2016-11-08 12:48:20',
			'modified' => '2016-11-08 12:48:20'
		),
	);

}
