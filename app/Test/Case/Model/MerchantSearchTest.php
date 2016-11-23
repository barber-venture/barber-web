<?php
App::uses('MerchantSearch', 'Model');

/**
 * MerchantSearch Test Case
 */
class MerchantSearchTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.merchant_search',
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
		'app.user_detail',
		'app.user_feedback',
		'app.user_search'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->MerchantSearch = ClassRegistry::init('MerchantSearch');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->MerchantSearch);

		parent::tearDown();
	}

}
