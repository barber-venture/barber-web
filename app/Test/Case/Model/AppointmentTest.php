<?php
App::uses('Appointment', 'Model');

/**
 * Appointment Test Case
 */
class AppointmentTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.appointment',
		'app.merchant',
		'app.user',
		'app.role',
		'app.follower',
		'app.merchant_view',
		'app.request',
		'app.review',
		'app.user_detail',
		'app.user_feedback',
		'app.user_image',
		'app.merchant_document',
		'app.merchant_leave',
		'app.merchant_type',
		'app.merchant_image',
		'app.merchant_working_day',
		'app.merchant_offering',
		'app.appointment_social'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Appointment = ClassRegistry::init('Appointment');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Appointment);

		parent::tearDown();
	}

}
