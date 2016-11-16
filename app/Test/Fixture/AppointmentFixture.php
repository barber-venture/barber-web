<?php
/**
 * Appointment Fixture
 */
class AppointmentFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'merchant_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'appointment_date' => array('type' => 'date', 'null' => false, 'default' => null),
		'appointment_time' => array('type' => 'time', 'null' => false, 'default' => null),
		'message' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'status' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'comment' => '0=pending, 1=approved, 2=rejected, 3=started, 4=completed'),
		'current_likes' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'current_comments' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'current_shares' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
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
			'user_id' => 1,
			'appointment_date' => '2016-11-13',
			'appointment_time' => '09:40:35',
			'message' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'status' => 1,
			'current_likes' => 1,
			'current_comments' => 1,
			'current_shares' => 1,
			'created' => '2016-11-13 09:40:35',
			'modified' => '2016-11-13 09:40:35'
		),
	);

}
