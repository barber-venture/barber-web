<?php
/**
 * MerchantImage Fixture
 */
class MerchantImageFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'merchnat_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'original_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'converted_name' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'is_default' => array('type' => 'boolean', 'null' => false, 'default' => null, 'comment' => '0=no, 1=yes'),
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
			'merchnat_id' => 1,
			'original_name' => 'Lorem ipsum dolor sit amet',
			'converted_name' => 1,
			'is_default' => 1,
			'created' => '2016-11-07 13:28:40',
			'modified' => '2016-11-07 13:28:40'
		),
	);

}
