<?php
App::uses('UserDetail', 'Model');

/**
 * UserDetail Test Case
 */
class UserDetailTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.user_detail',
		'app.user',
		'app.role',
		'app.appointment',
		'app.merchant',
		'app.merchant_document',
		'app.merchant_leave',
		'app.merchant_type',
		'app.merchant_image',
		'app.merchant_view',
		'app.merchant_working_day',
		'app.merchant_offering',
		'app.appointment_social',
		'app.review',
		'app.user_image',
		'app.reply',
		'app.follower',
		'app.request',
		'app.user_feedback'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->UserDetail = ClassRegistry::init('UserDetail');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->UserDetail);

		parent::tearDown();
	}

}
