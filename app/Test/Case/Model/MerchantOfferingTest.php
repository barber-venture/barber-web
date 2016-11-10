<?php
App::uses('MerchantOffering', 'Model');

/**
 * MerchantOffering Test Case
 */
class MerchantOfferingTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.merchant_offering',
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
		$this->MerchantOffering = ClassRegistry::init('MerchantOffering');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->MerchantOffering);

		parent::tearDown();
	}

}
