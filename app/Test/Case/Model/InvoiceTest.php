<?php
App::uses('Invoice', 'Model');

/**
 * Invoice Test Case
 */
class InvoiceTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.invoice',
		'app.appointment',
		'app.merchant',
		'app.user',
		'app.role',
		'app.follower',
		'app.merchant_view',
		'app.request',
		'app.review',
		'app.user_image',
		'app.reply',
		'app.user_detail',
		'app.user_feedback',
		'app.merchant_search',
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
		$this->Invoice = ClassRegistry::init('Invoice');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Invoice);

		parent::tearDown();
	}

}
