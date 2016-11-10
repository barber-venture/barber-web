<?php
App::uses('MerchantWorkingDay', 'Model');

/**
 * MerchantWorkingDay Test Case
 */
class MerchantWorkingDayTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.merchant_working_day',
		'app.merchant',
		'app.user',
		'app.role',
		'app.appointment',
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
		'app.merchant_offering'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->MerchantWorkingDay = ClassRegistry::init('MerchantWorkingDay');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->MerchantWorkingDay);

		parent::tearDown();
	}

}
