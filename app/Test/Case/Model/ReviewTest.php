<?php
App::uses('Review', 'Model');

/**
 * Review Test Case
 */
class ReviewTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.review',
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
		'app.follower',
		'app.request',
		'app.user_detail',
		'app.user_feedback',
		'app.user_image'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Review = ClassRegistry::init('Review');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Review);

		parent::tearDown();
	}

}
