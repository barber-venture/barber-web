<?php
App::uses('MerchantImage', 'Model');

/**
 * MerchantImage Test Case
 */
class MerchantImageTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.merchant_image',
		'app.merchnat'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->MerchantImage = ClassRegistry::init('MerchantImage');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->MerchantImage);

		parent::tearDown();
	}

}
