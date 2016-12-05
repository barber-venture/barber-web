<?php

App::uses('AppModel', 'Model');

/**
 * MerchantView Model
 *
 * @property Merchant $Merchant
 * @property User $User
 */
class MerchantView extends AppModel {
    // The Associations below have been created with all possible keys, those that are not needed can be removed

    /**
     * belongsTo associations
     *
     * @var array
     */
    public $belongsTo = array(
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
