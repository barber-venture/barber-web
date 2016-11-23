<?php
App::uses('AppModel', 'Model');
/**
 * Invoice Model
 *
 * @property Appointment $Appointment
 * @property Merchant $Merchant
 * @property User $User
 */
class Invoice extends AppModel {


	// The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Appointment' => array(
			'className' => 'Appointment',
			'foreignKey' => 'appointment_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Merchant' => array(
			'className' => 'Merchant',
			'foreignKey' => 'merchant_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
