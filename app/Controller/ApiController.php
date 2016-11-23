<?php

/**
 * Api controller.
 *
 *
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 */
App::uses('AppController', 'Controller');

class ApiController extends AppController {

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('User');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow();
        $this->autoRender = false;
    }

    public function merchantSignup() {
        if ($this->request->is('post')) {
            $role = $this->User->Role->findByName('merchant');
            if (!empty($role)) {
                $already = $this->User->findByEmail($this->request->data['email']);
                if (empty($already)) {
                    $this->request->data['role_id'] = $role['Role']['id'];
                    $this->request->data['status'] = 1;
                    $this->request->data['password'] = AuthComponent::password($this->request->data['password']);
                    $this->User->set($this->request->data);
                    if ($this->User->save()) {
                        $response['status'] = true;
                        $response['data'] = $this->User->getInsertID();
                    } else {
                        $response['status'] = false;
                        $response['message'] = 'Account can not create';
                    }
                } else {
                    $response['status'] = false;
                    $response['message'] = 'Email address already exists';
                }
            } else {
                $response['status'] = false;
                $response['message'] = 'Role does not found';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function updateMerchantProfile() {
        if ($this->request->is('post')) {
            if (!isset($this->request->data['type'])) {
                $response['status'] = false;
                $response['message'] = 'Please select parlor type';
            } else if ($this->request->data['offering'][0] == '') {
                $response['status'] = false;
                $response['message'] = 'Please enter parlor products';
            } else if ($this->request->data['offering'][0] == '') {
                $response['status'] = false;
                $response['message'] = 'Please enter parlor products';
            } else {
                $already = $this->User->Merchant->findByUserId($this->request->data['user_id']);
                if (!empty($already)) {
                    $this->User->Merchant->id = $already['Merchant']['id'];
                }
                if ($this->User->Merchant->save($this->request->data)) {
                    if (!empty($already)) {
                        $merchantId = $already['Merchant']['id'];
                    } else {
                        $merchantId = $this->User->Merchant->getInsertID();
                    }
                    $this->User->Merchant->MerchantType->deleteAll(array('MerchantType.merchant_id' => $merchantId));
                    foreach ($this->request->data['type'] as $key => $value) {
                        $this->User->Merchant->MerchantType->create();
                        $merchantType['merchant_id'] = $merchantId;
                        $merchantType['name'] = $key;
                        $this->User->Merchant->MerchantType->save($merchantType);
                    }
                    $this->User->Merchant->MerchantOffering->deleteAll(array('MerchantOffering.merchant_id' => $merchantId));
                    foreach ($this->request->data['offering'] as $key => $value) {
                        $this->User->Merchant->MerchantOffering->create();
                        $merchantType['merchant_id'] = $merchantId;
                        $merchantType['name'] = $value;
                        $this->User->Merchant->MerchantOffering->save($merchantType);
                    }
                    $this->User->Merchant->MerchantImage->deleteAll(array('MerchantImage.merchant_id' => $merchantId));
                    foreach ($this->request->data['images'] as $key => $value) {
                        $this->User->Merchant->MerchantImage->create();
                        $merchantType['merchant_id'] = $merchantId;
                        $merchantType['image'] = $value;
                        $this->User->Merchant->MerchantImage->save($merchantType);
                    }
                    $response['status'] = true;
                } else {
                    $response['status'] = false;
                    $response['message'] = 'Can not save information';
                }
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function merchantLogin() {
        if ($this->request->is('post')) {
            $role = $this->User->Role->findByName('merchant');
            if (!empty($role)) {
                $this->request->data['password'] = AuthComponent::password($this->request->data['password']);
                $login = $this->User->find('first', array('conditions' => array(
                        'User.email' => $this->request->data['email'],
                        'User.password' => $this->request->data['password'],
                        'User.role_id' => $role['Role']['id']
                )));
                if (!empty($login)) {
                    if ($login['User']['status'] == 1) {
                        $response['status'] = true;
                        $response['data'] = $login;
                    } else {
                        $response['status'] = false;
                        $response['message'] = 'Your account is not activated';
                    }
                } else {
                    $response['status'] = false;
                    $response['message'] = 'Either email or password is wrong';
                }
            } else {
                $response['status'] = false;
                $response['message'] = 'Role does not found';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function merchantForget() {
        if ($this->request->is('post')) {
            $role = $this->User->Role->findByName('user');
            $user = $this->User->find('first', array('conditions' => array(
                    'User.email' => $this->request->data['email'],
                    'User.role_id' => $role['Role']['id']
            )));
            if (!empty($user)) {
                $vcode = uniqid();
                $userPassword['User']['password'] = AuthComponent::password($vcode);
                $this->User->id = $user['User']['id'];
                if ($this->User->save($userPassword)) {
                    $this->loadModel('EmailTemplate');
                    $emailTemplate = $this->EmailTemplate->find('first', array('conditions' => array(
                            'EmailTemplate.slug' => 'forget-password'
                    )));
                    if (!empty($emailTemplate)) {
                        $settings = $this->viewVars['settings'];
                        App::uses('CakeEmail', 'Network/Email');
                        $Email = new CakeEmail($settings['Setting']['email_config']);
                        $Email->from(array($emailTemplate['EmailTemplate']['from_email'] => $emailTemplate['EmailTemplate']['from_name']));
                        $Email->to($this->request->data['email']);
                        $Email->subject($emailTemplate['EmailTemplate']['subject']);

                        $emailTemplate['EmailTemplate']['description'] = str_replace("{USERNAME}", $this->request->data['email'], $emailTemplate['EmailTemplate']['description']);
                        $emailTemplate['EmailTemplate']['description'] = str_replace("{PASSWORD}", $vcode, $emailTemplate['EmailTemplate']['description']);
                        $emailTemplate['EmailTemplate']['description'] = str_replace("{SITENAME}", $settings['Setting']['site_name'], $emailTemplate['EmailTemplate']['description']);
                        if ($Email->send($emailTemplate['EmailTemplate']['description'])) {
                            $response['status'] = true;
                            $response['message'] = 'An email has been sent to you to resetting the password';
                        } else {
                            $response['status'] = false;
                            $response['message'] = 'Email can not send';
                        }
                    }
                } else {
                    $response['status'] = false;
                    $response['message'] = 'Can not reset the password';
                }
            } else {
                $response['status'] = false;
                $response['message'] = 'Email address does not found';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function merchantProfile() {
        if ($this->request->is('post')) {
            $merchant = $this->User->Merchant->find('first', array('conditions' => array(
                    'Merchant.user_id' => $this->request->data['user_id']
            )));
            if (!empty($merchant)) {
                $response['status'] = true;
                $response['data'] = $merchant;
            } else {
                $response['status'] = false;
                $response['message'] = 'Merchant details can not found';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function updateMerchantAvailability() {
        if ($this->request->is('post')) {
            if (!isset($this->request->data['days']) || empty($this->request->data['days'])) {
                $response['status'] = false;
                $response['message'] = 'Please select working days';
            } else {
                $merchant = $this->User->Merchant->find('first', array('conditions' => array(
                        'Merchant.user_id' => $this->request->data['user_id']
                )));
                if (!empty($merchant)) {
                    $this->User->Merchant->id = $merchant['Merchant']['id'];
                    $merchantInfo['other_information'] = $this->request->data['other'];
                    $merchantInfo['start_time'] = date("H:i:s", strtotime($this->request->data['from']));
                    $merchantInfo['end_time'] = date("H:i:s", strtotime($this->request->data['to']));
                    if ($this->User->Merchant->save($merchantInfo)) {
                        $this->User->Merchant->MerchantWorkingDay->deleteAll(array('MerchantWorkingDay.merchant_id' => $merchant['Merchant']['id']));
                        foreach ($this->request->data['days'] as $key => $value) {
                            $this->User->Merchant->MerchantWorkingDay->create();
                            $merchantType['merchant_id'] = $merchant['Merchant']['id'];
                            $merchantType['day'] = $value;
                            $this->User->Merchant->MerchantWorkingDay->save($merchantType);
                        }
                        $response['status'] = true;
                    } else {
                        $response['status'] = false;
                        $response['message'] = 'Merchant details can not save';
                    }
                } else {
                    $response['status'] = false;
                    $response['message'] = 'Merchant details can not found';
                }
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function changePassword() {
        if ($this->request->is('post')) {
            $password = AuthComponent::password($this->request->data['old_password']);
            $already = $this->User->find('first', array('conditions' => array(
                    'User.id' => $this->request->data['user_id'],
                    'User.password' => $password
            )));
            if (!empty($already)) {
                $newPassword = AuthComponent::password($this->request->data['new_password']);
                $this->User->id = $already['User']['id'];
                $user['password'] = $newPassword;
                if ($this->User->save($user)) {
                    $response['status'] = true;
                } else {
                    $response['status'] = false;
                    $response['message'] = 'New password can not update';
                }
            } else {
                $response['status'] = false;
                $response['message'] = 'Old password is not correct';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function staticPages() {
        if ($this->request->is('post')) {
            $this->loadModel('Page');
            if (isset($this->request->data['id']) && $this->request->data['id'] != '') {
                $pages = $this->Page->findById($this->request->data['id']);
            } else {
                $pages = $this->Page->find('all');
            }
            if (!empty($pages)) {
                $response['status'] = true;
                $response['data'] = $pages;
            } else {
                $response['status'] = false;
                $response['message'] = 'Pages can not found';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function saveFeedback() {
        if ($this->request->is('post')) {
            if ($this->User->UserFeedback->save($this->request->data)) {
                $response['status'] = true;
            } else {
                $response['status'] = false;
                $response['message'] = 'Feedback can not save';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function merchantBookings() {
        if ($this->request->is('post')) {
            $merchantDetails = $this->User->Merchant->findByUserId($this->request->data['user_id']);
            if (!empty($merchantDetails)) {
                $this->User->Appointment->recursive = -1;
                $appointments = $this->User->Appointment->find('all', array('conditions' => array(
                        'Appointment.merchant_id' => $merchantDetails['Merchant']['id'],
                        'Appointment.status' => $this->request->data['status']
                )));
                if (!empty($appointments)) {
                    $bookings = array();
                    foreach ($appointments as $k => $appointment) {
                        $bookings[$k]['Appointment'] = $appointment['Appointment'];
                        $userDetail = $this->User->UserDetail->findByUserId($appointment['Appointment']['user_id']);
                        if (!empty($userDetail)) {
                            $bookings[$k]['UserDetail'] = $userDetail['UserDetail'];
                        }
                    }
                    $response['status'] = true;
                    $response['data'] = $bookings;
                } else {
                    $response['status'] = false;
                    $response['message'] = 'Appointments can not found';
                }
            } else {
                $response['status'] = false;
                $response['message'] = 'Merchant can not found';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function changeMerchantBookingStatus() {
        if ($this->request->is('post')) {
            $appointments = $this->User->Appointment->findById($this->request->data['id']);
            if (!empty($appointments)) {
                $this->User->Appointment->id = $this->request->data['id'];
                $appointment['status'] = $this->request->data['status'];
                if ($this->User->Appointment->save($appointment)) {
                    $response['status'] = true;
                } else {
                    $response['status'] = false;
                    $response['message'] = 'Appointment status can not change';
                }
            } else {
                $response['status'] = false;
                $response['message'] = 'Appointment can not found';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function merchantReviews() {
        if ($this->request->is('post')) {
            $merchantDetails = $this->User->Merchant->findByUserId($this->request->data['user_id']);
            if (!empty($merchantDetails)) {
                $this->User->Review->recursive = -1;
                $reviews = $this->User->Review->findAllByMerchantId($merchantDetails['Merchant']['id']);
                if (!empty($reviews)) {
                    $bookings = array();
                    foreach ($reviews as $k => $review) {
                        $bookings[$k]['Review'] = $review['Review'];
                        $userDetail = $this->User->UserDetail->findByUserId($review['Review']['user_id']);
                        if (!empty($userDetail)) {
                            $bookings[$k]['UserDetail'] = $userDetail['UserDetail'];
                        }
                    }
                    $response['status'] = true;
                    $response['data'] = $bookings;
                } else {
                    $response['status'] = false;
                    $response['message'] = 'Reviews can not found';
                }
            } else {
                $response['status'] = false;
                $response['message'] = 'Merchant detail can not found';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function saveReply() {
        if ($this->request->is('post')) {
            if ($this->User->Review->Reply->save($this->request->data)) {
                $response['status'] = true;
            } else {
                $response['status'] = false;
                $response['message'] = 'Reply can not save';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function customerSignup() {
        if ($this->request->is('post')) {
            $role = $this->User->Role->findByName('user');
            if (!empty($role)) {
                $already = $this->User->findByEmail($this->request->data['email']);
                if (empty($already)) {
                    $this->request->data['role_id'] = $role['Role']['id'];
                    $this->request->data['status'] = 1;
                    $this->request->data['password'] = AuthComponent::password($this->request->data['password']);
                    $this->User->set($this->request->data);
                    if ($this->User->save()) {
                        $response['status'] = true;
                        $response['data'] = $this->User->getInsertID();
                    } else {
                        $response['status'] = false;
                        $response['message'] = 'Account can not create';
                    }
                } else {
                    $response['status'] = false;
                    $response['message'] = 'Email address already exists';
                }
            } else {
                $response['status'] = false;
                $response['message'] = 'Role does not found';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function customerLogin() {
        if ($this->request->is('post')) {
            $role = $this->User->Role->findByName('user');
            if (!empty($role)) {
                $this->request->data['password'] = AuthComponent::password($this->request->data['password']);
                $login = $this->User->find('first', array('conditions' => array(
                        'User.email' => $this->request->data['email'],
                        'User.password' => $this->request->data['password'],
                        'User.role_id' => $role['Role']['id']
                )));
                if (!empty($login)) {
                    if ($login['User']['status'] == 1) {
                        $response['status'] = true;
                        $response['data'] = $login;
                    } else {
                        $response['status'] = false;
                        $response['message'] = 'Your account is not activated';
                    }
                } else {
                    $response['status'] = false;
                    $response['message'] = 'Either email or password is wrong';
                }
            } else {
                $response['status'] = false;
                $response['message'] = 'Role does not found';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function customerForget() {
        if ($this->request->is('post')) {
            $role = $this->User->Role->findByName('user');
            $user = $this->User->find('first', array('conditions' => array(
                    'User.email' => $this->request->data['email'],
                    'User.role_id' => $role['Role']['id']
            )));
            if (!empty($user)) {
                $vcode = uniqid();
                $userPassword['User']['password'] = AuthComponent::password($vcode);
                $this->User->id = $user['User']['id'];
                if ($this->User->save($userPassword)) {
                    $this->loadModel('EmailTemplate');
                    $emailTemplate = $this->EmailTemplate->find('first', array('conditions' => array(
                            'EmailTemplate.slug' => 'forget-password'
                    )));
                    if (!empty($emailTemplate)) {
                        $settings = $this->viewVars['settings'];
                        App::uses('CakeEmail', 'Network/Email');
                        $Email = new CakeEmail($settings['Setting']['email_config']);
                        $Email->from(array($emailTemplate['EmailTemplate']['from_email'] => $emailTemplate['EmailTemplate']['from_name']));
                        $Email->to($this->request->data['email']);
                        $Email->subject($emailTemplate['EmailTemplate']['subject']);

                        $emailTemplate['EmailTemplate']['description'] = str_replace("{USERNAME}", $this->request->data['email'], $emailTemplate['EmailTemplate']['description']);
                        $emailTemplate['EmailTemplate']['description'] = str_replace("{PASSWORD}", $vcode, $emailTemplate['EmailTemplate']['description']);
                        $emailTemplate['EmailTemplate']['description'] = str_replace("{SITENAME}", $settings['Setting']['site_name'], $emailTemplate['EmailTemplate']['description']);
                        if ($Email->send($emailTemplate['EmailTemplate']['description'])) {
                            $response['status'] = true;
                            $response['message'] = 'An email has been sent to you to resetting the password';
                        } else {
                            $response['status'] = false;
                            $response['message'] = 'Email can not send';
                        }
                    }
                } else {
                    $response['status'] = false;
                    $response['message'] = 'Can not reset the password';
                }
            } else {
                $response['status'] = false;
                $response['message'] = 'Email address does not found';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function updateCustomerProfile() {
        if ($this->request->is('post')) {
            $already = $this->User->UserDetail->findByUserId($this->request->data['user_id']);
            if (!empty($already)) {
                $this->User->UserDetail->id = $already['UserDetail']['id'];
            }
            if ($this->User->UserDetail->save($this->request->data)) {
                $response['status'] = true;
            } else {
                $response['status'] = false;
                $response['message'] = 'Can not save information';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function customerProfile() {
        if ($this->request->is('post')) {
            $merchant = $this->User->UserDetail->find('first', array('conditions' => array(
                    'UserDetail.user_id' => $this->request->data['user_id']
            )));
            if (!empty($merchant)) {
                $this->User->Review->recursive = 2;
                $reviews = $this->User->Review->findAllByUserId($this->request->data['user_id']);
                $response['status'] = true;
                $response['data'] = $merchant;
                $response['data']['Review'] = $reviews;
            } else {
                $response['status'] = false;
                $response['message'] = 'User details can not found';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function customerBookings() {
        if ($this->request->is('post')) {
            $appointments = $this->User->Appointment->find('all', array('conditions' => array(
                    'Appointment.user_id' => $this->request->data['user_id']
            )));
            if (!empty($appointments)) {
                $bookings = array();
                foreach ($appointments as $k => $appointment) {
                    $bookings[$k]['Appointment'] = $appointment['Appointment'];
                    $bookings[$k]['Review'] = $appointment['Review'];
                    $userDetail = $this->User->Merchant->findById($appointment['Appointment']['merchant_id']);
                    if (!empty($userDetail)) {
                        $bookings[$k]['Merchant'] = $userDetail;
                    }
                }
                $response['status'] = true;
                $response['data'] = $bookings;
            } else {
                $response['status'] = false;
                $response['message'] = 'Appointments can not found';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function addReview() {
        if ($this->request->is('post')) {
            $appointmentDetails = $this->User->Appointment->findById($this->request->data['appointment_id']);
            if (!empty($appointmentDetails)) {
                $this->request->data['merchant_id'] = $appointmentDetails['Appointment']['merchant_id'];
                if ($this->User->Review->save($this->request->data)) {
                    if (isset($this->request->data['images']) && !empty($this->request->data['images'])) {
                        foreach ($this->request->data['images'] as $image) {
                            $image['user_id'] = $this->request->data['user_id'];
                            $image['review_id'] = $this->User->Review->getInsertID();
                            ;
                            $image['image'] = $image;
                            $this->User->Review->create();
                            $this->User->Review->save($image);
                        }
                    }
                    $response['status'] = true;
                } else {
                    $response['status'] = false;
                    $response['message'] = 'Can not save';
                }
            } else {
                $response['status'] = false;
                $response['message'] = 'Appointment can not found';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function addParlorRequest() {
        if ($this->request->is('post')) {
            if (!isset($this->request->data['type'])) {
                $response['status'] = false;
                $response['message'] = 'Please select parlor type';
            } else {
                $merchantType = array();
                foreach ($this->request->data['type'] as $key => $value) {
                    $merchantType[] = $key;
                }
                $merchantType = implode(",", $merchantType);
                $this->request->data['merchant_type'] = $merchantType;
                $this->request->data['status'] = 1;
                if ($this->User->Request->save($this->request->data)) {
                    $response['status'] = true;
                } else {
                    $response['status'] = false;
                    $response['message'] = 'Can not send request';
                }
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function getNearBySaloons() {
        if ($this->request->is('post')) {
            $lat = $this->request->data['lat'];
            $lng = $this->request->data['lng'];
            $radius = 5;
            $sql = "SELECT
                    Merchant.*,
                    ( 6371 * acos( cos( radians({$lat}) ) * cos( radians( `lat` ) ) * cos( radians( `lng` ) - "
                    . "radians({$lng}) ) + sin( radians({$lat}) ) * sin( radians( `lat` ) ) ) ) AS distance "
                    . "FROM merchants as Merchant"
                    . " HAVING distance <= {$radius}
                ORDER BY distance ASC";
            $merchants = $this->User->Merchant->query($sql);
            if (!empty($merchants)) {
                $merchantSearch = $this->User->MerchantSearch->findByUserId($this->request->data['user_id']);
                if (!empty($merchantSearch)) {
                    foreach ($merchantSearch as $k => $s) {
                        if ($k >= 5) {
                            $this->User->MerchantSearch->delete($s['MerchantSearch']['id']);
                        }
                    }
                }
                $insertSearch = $this->User->MerchantSearch->save($this->request->data);
                $merchant = array();
                foreach ($merchants as $k => $m) {
                    $this->User->Merchant->MerchantType->recursive = 0;
                    $merchantType = $this->User->Merchant->MerchantType->findAllByMerchantId($m['Merchant']['id']);
                    $merchantImage = $this->User->Merchant->MerchantImage->findByMerchantId($m['Merchant']['id']);
                    $merchant[$k] = $m;
                    $merchant[$k]['MerchantType'] = $merchantType;
                    $merchant[$k]['MerchantImage'] = $merchantImage;
                }
                $response['status'] = true;
                $response['data'] = $merchant;
            } else {
                $response['status'] = false;
                $response['message'] = 'Saloons can not found at this location';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function getSaloonDetail() {
        if ($this->request->is('post')) {
            $detail = $this->User->Merchant->findById($this->request->data['id']);
            if (!empty($detail)) {
                $response['status'] = true;
                $response['data'] = $detail;
            } else {
                $response['status'] = false;
                $response['message'] = 'Can not found detail';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function getCustomerStartedBooking() {
        if ($this->request->is('post')) {
            $detail = $this->User->Appointment->find('first', array('conditions' => array(
                    'Appointment.user_id' => $this->request->data['user_id'],
                    'Appointment.id' => $this->request->data['id']
            )));
            if (!empty($detail)) {
                $response['status'] = true;
                $response['data'] = $detail;
            } else {
                $response['status'] = false;
                $response['message'] = 'Can not found detail';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function getInvoiceDetail() {
        if ($this->request->is('post')) {
            $detail = $this->User->Invoice->find('first', array('conditions' => array(
                    'Invoice.user_id' => $this->request->data['user_id'],
                    'Invoice.appointment_id' => $this->request->data['appointment_id']
            )));
            if (!empty($detail)) {
                $response['status'] = true;
                $response['data'] = $detail;
            } else {
                $response['status'] = false;
                $response['message'] = 'Can not found invoice details, please contact to administrator';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function updateInvoice() {
        if ($this->request->is('post')) {
            $this->User->Invoice->id = $this->request->data['id'];
            if ($this->User->Invoice->save($this->request->data)) {
                $response['status'] = true;
            } else {
                $response['status'] = false;
                $response['message'] = 'Can not send request';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function bookSlot() {
        if ($this->request->is('post')) {
            $this->request->data['status'] = 0;
            $this->request->data['appointment_date'] = date("Y-m-d", strtotime($this->request->data['date']));
            $this->request->data['appointment_time'] = date("H:i:s", strtotime($this->request->data['time']));
            if ($this->User->Appointment->save($this->request->data)) {
                $response['status'] = true;
                $response['data'] = $this->User->Appointment->getInsertID();
            } else {
                $response['status'] = false;
                $response['message'] = 'Can not send request';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

}
