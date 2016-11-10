<?php
App::uses('EmailTemplate', 'Model');

/**
 * EmailTemplate Test Case
 */
class EmailTemplateTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.email_template'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->EmailTemplate = ClassRegistry::init('EmailTemplate');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->EmailTemplate);

		parent::tearDown();
	}

}
