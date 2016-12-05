<?php
App::uses('MerchantView', 'Model');

/**
 * MerchantView Test Case
 */
class MerchantViewTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.merchant_view',
		'app.merchant',
		'app.user',
		'app.role',
		'app.appointment',
		'app.appointment_social',
		'app.review',
		'app.user_image',
		'app.reply',
		'app.review_like',
		'app.review_comment',
		'app.follower',
		'app.request',
		'app.user_detail',
		'app.user_feedback',
		'app.merchant_search',
		'app.invoice',
		'app.device',
		'app.push_message',
		'app.merchant_document',
		'app.merchant_leave',
		'app.merchant_type',
		'app.merchant_image',
		'app.merchant_working_day',
		'app.merchant_offering',
		'app.barber'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->MerchantView = ClassRegistry::init('MerchantView');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->MerchantView);

		parent::tearDown();
	}

}
