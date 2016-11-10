<?php
App::uses('MerchantType', 'Model');

/**
 * MerchantType Test Case
 */
class MerchantTypeTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.merchant_type',
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
		'app.merchant_working_day',
		'app.product'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->MerchantType = ClassRegistry::init('MerchantType');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->MerchantType);

		parent::tearDown();
	}

}
