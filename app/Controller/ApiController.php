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
    public $components = array("Common");

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

                    $exists = array();
                    $tobeAdd = array();
                    foreach ($this->request->data['images'] as $key => $value) {
                        if (strpos($value, Configure::read("App.baseUrl") . "/files/") !== false) {
                            $exists[] = str_replace(Configure::read("App.baseUrl") . "/files/", "", $value);
                        } else {
                            $tobeAdd[] = $value;
                        }
                    }

                    $toBeDelete = $this->User->Merchant->MerchantImage->find("all", array("conditions" => array(
                            "MerchantImage.merchant_id" => $merchantId,
                            "NOT" => array("MerchantImage.image" => $exists)
                    )));
                    if (!empty($toBeDelete)) {
                        foreach ($toBeDelete as $a) {
                            if (file_exists("files/" . $a['MerchantImage']['image'])) {
                                unlink("files/" . $a['MerchantImage']['image']);
                                $this->User->Merchant->MerchantImage->delete($a['MerchantImage']['id']);
                            }
                        }
                    }

                    //$this->User->Merchant->MerchantImage->deleteAll(array('MerchantImage.merchant_id' => $merchantId));
                    if (!empty($tobeAdd)) {
                        foreach ($tobeAdd as $key => $value) {
                            $filename = uniqid() . ".jpg";
                            $dest = "files";
                            if (!is_dir($dest)) {
                                mkdir($dest);
                            }

                            $data = explode(',', $value);

                            if (file_put_contents($dest . DIRECTORY_SEPARATOR . $filename, base64_decode($data[1]))) {
                                $this->User->Merchant->MerchantImage->create();
                                $merchantType['merchant_id'] = $merchantId;
                                $merchantType['image'] = $filename;
                                $this->User->Merchant->MerchantImage->save($merchantType);
                            }
                        }
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
                $password = AuthComponent::password($this->request->data['password']);
                $login = $this->User->find('first', array('conditions' => array(
                        'User.email' => $this->request->data['email'],
                        'User.password' => $password,
                        'User.role_id' => $role['Role']['id']
                )));
                if (!empty($login)) {
                    if ($login['User']['status'] == 1) {
                        $merchantImage = $this->User->Merchant->MerchantImage->findByMerchantId($login['Merchant']['id']);
                        $login['MerchantImage']['image'] = Configure::read('App.baseUrl') . "/files/" . $merchantImage['MerchantImage']['image'];
                        ;
                        $response['status'] = true;
                        $response['data'] = $login;
                    } else {
                        $response['status'] = false;
                        $response['message'] = 'Your account is not activated';
                    }
                } else {
                    $role = $this->User->Role->findByName('barber');
                    if (!empty($role)) {
                        $password = AuthComponent::password($this->request->data['password']);
                        $login = $this->User->find('first', array('conditions' => array(
                                'User.email' => $this->request->data['email'],
                                'User.password' => $password,
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
                $merchant['Merchant']['working_day'] = $this->Common->getWorkingDays($merchant['MerchantWorkingDay']);
                $merchant['Merchant']['start_time'] = date("h:i a", strtotime($merchant['Merchant']['start_time']));
                $merchant['Merchant']['end_time'] = date("h:i a", strtotime($merchant['Merchant']['end_time']));
                $merchant['Merchant']['set_start_time'] = date("H:i:s", strtotime($merchant['Merchant']['start_time']));
                $merchant['Merchant']['set_end_time'] = date("H:i:s", strtotime($merchant['Merchant']['end_time']));
                $images = array();
                foreach ($merchant['MerchantImage'] as $k => $m) {
                    $images[$k] = $m;
                    $images[$k]['image'] = Configure::read('App.baseUrl') . "/files/" . $m['image'];
                }
                $merchant['MerchantImage'] = $images;
                $response['status'] = true;
                $response['data'] = $merchant;
            } else {
                $response['status'] = false;
                $response['message'] = 'Can not found your details, please complete the form';
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
                            $bookings[$k]['UserDetail']['profile_picture'] = $userDetail['UserDetail']['profile_picture'] != '' ? Configure::read('App.baseUrl') . "/files/" . $userDetail['UserDetail']['profile_picture'] : '';
                        }
                    }
                    $response['status'] = true;
                    $response['data'] = $bookings;
                } else {
                    $response['status'] = false;
                    $response['message'] = 'Appointments can not found';
                }
            } else {
                $this->loadModel('Barber');
                $merchantId = $this->Barber->findByUserId($this->request->data['user_id']);
                if (!empty($merchantId)) {
                    $merchantDetails = $this->User->Merchant->findById($merchantId['Barber']['merchant_id']);
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
                                    $bookings[$k]['UserDetail']['profile_picture'] = $userDetail['UserDetail']['profile_picture'] != '' ? Configure::read('App.baseUrl') . "/files/" . $userDetail['UserDetail']['profile_picture'] : '';
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
                    $response['message'] = 'Super merchant can not found';
                }
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
                    if ($this->request->data['status'] == 1) {
                        $subject = 'accepted';
                    } else if ($this->request->data['status'] == 2) {
                        $subject = 'rejected';
                    } else if ($this->request->data['status'] == 3) {
                        $subject = 'started';
                    } else if ($this->request->data['status'] == 4) {
                        $subject = 'completed';
                    } else {
                        $subject = 'canceled';
                    }

                    $saloon = $appointments['Merchant']['name'];
                    $time = date("d M, h:i a", strtotime($appointments['Appointment']['created']));

                    $description = 'Your appointment to ' . $saloon . ' on ' . $time . ' has been ' . $subject;
                    $subject = 'Appointment ' . $subject;

                    $device = $this->User->Device->findByUserId($appointments['Appointment']['user_id']);
                    /* $response['status'] = false;
                      $response['message'] = print_r($device, true);
                      echo json_encode($response);die; */
                    if (!empty($device)) {
                        $androidIds = array($device['Device']['registrationid']);
                        $this->loadModel('PushMessage');
                        $this->PushMessage->create();
                        $msg['user_id'] = $device['Device']['user_id'];
                        $msg['subject'] = $subject;
                        $msg['description'] = $description;
                        $this->PushMessage->save($msg);
                        if (!empty($androidIds)) {
                            $message = array
                                (
                                'message' => $description,
                                'title' => $subject,
                                'vibrate' => 1,
                                'sound' => 1
                            );
                            $this->sendPushToAndroid($androidIds, $message);
                        }
                    }
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
                        $bookings[$k]['Review']['created_date'] = date("d M y", strtotime($review['Review']['created']));
                        $bookings[$k]['Review']['created_time'] = date("h:i a", strtotime($review['Review']['created']));
                        $userDetail = $this->User->UserDetail->findByUserId($review['Review']['user_id']);
                        if (!empty($userDetail)) {
                            $bookings[$k]['UserDetail'] = $userDetail['UserDetail'];
                            $bookings[$k]['UserDetail']['profile_picture'] = $userDetail['UserDetail']['profile_picture'] != '' ? Configure::read('App.baseUrl') . "/files/" . $userDetail['UserDetail']['profile_picture'] : '';
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
                if (strpos($this->request->data['profile_picture'], Configure::read("App.baseUrl") . "/files/") == false) {
                    if (file_exists("files/" . $already['UserDetail']['profile_picture'])) {
                        unlink("files/" . $already['UserDetail']['profile_picture']);
                    }
                }
            }
            if (strpos($this->request->data['profile_picture'], Configure::read("App.baseUrl") . "/files/") == false) {
                $filename = uniqid() . ".jpg";
                $dest = "files";
                if (!is_dir($dest)) {
                    mkdir($dest);
                }

                $data = explode(',', $this->request->data['profile_picture']);
                if (file_put_contents($dest . DIRECTORY_SEPARATOR . $filename, base64_decode($data[1]))) {
                    $this->request->data['profile_picture'] = $filename;
                } else {
                    unset($this->request->data['profile_picture']);
                }
            } else {
                unset($this->request->data['profile_picture']);
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
            $merchant['UserDetail']['profile_picture'] = $merchant['UserDetail']['profile_picture'] != '' ? Configure::read('App.baseUrl') . "/files/" . $merchant['UserDetail']['profile_picture'] : '';
            if (!empty($merchant)) {
                $this->User->Review->recursive = 2;
                $reviews = $this->User->Review->findAllByUserId($this->request->data['user_id']);
                $newArray = array();
                foreach ($reviews as $k => $review) {
                    $newArray[$k] = $review;
                    $newArray[$k]['Review']['created_date'] = date("d M y", strtotime($review['Review']['created']));
                    $newArray[$k]['Review']['created_time'] = date("h:i a", strtotime($review['Review']['created']));
                }
                $response['status'] = true;
                $response['data'] = $merchant;
                $response['data']['Review'] = $newArray;
            } else {
                $response['status'] = false;
                $response['message'] = 'We do not have your details, please fill the form to continue with us';
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
                            $filename = uniqid() . ".jpg";
                            $dest = "files";
                            if (!is_dir($dest)) {
                                mkdir($dest);
                            }

                            $data = explode(',', $image);

                            if (file_put_contents($dest . DIRECTORY_SEPARATOR . $filename, base64_decode($data[1]))) {
                                $image['user_id'] = $this->request->data['user_id'];
                                $image['review_id'] = $this->User->Review->getInsertID();
                                $image['image'] = $filename;
                                $this->User->Review->UserImage->create();
                                $this->User->Review->UserImage->save($image);
                            }
                        }
                    }
                    $avg = $this->User->Review->find("all", array(
                        "fields" => array("AVG(Review.rating) AS AverageRating"),
                        "conditions" => array('Review.merchant_id' => $appointmentDetails['Appointment']['merchant_id'])
                    ));
                    $avg = $avg[0]['AverageRating'];
                    $this->User->Merchant->id = $appointmentDetails['Appointment']['merchant_id'];
                    $merchant['current_rating'] = $avg;
                    $this->User->Merchant->save($merchant);
                    $this->User->id = $this->request->data['user_id'];
                    $user['current_rating'] = $avg;
                    $this->User->save($user);
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
                    $merchantImage['MerchantImage']['image'] = Configure::read('App.baseUrl') . "/files/" . $merchantImage['MerchantImage']['image'];
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
                $already = $this->User->MerchantView->find("first", array("conditions" => array(
                        "MerchantView.merchant_id" => $this->request->data['id'],
                        "MerchantView.user_id" => $this->request->data['user_id']
                )));
                if (empty($already)) {
                    $this->User->MerchantView->query("DELETE FROM `merchant_views`
                                                WHERE id NOT IN (
                                                  SELECT id
                                                  FROM (
                                                    SELECT id
                                                    FROM `merchant_views`
                                                    ORDER BY id DESC
                                                    LIMIT 4
                                                  ) foo
                                    );");
                    $merchantView['merchant_id'] = $this->request->data['id'];
                    $merchantView['user_id'] = $this->request->data['user_id'];
                    $this->User->MerchantView->save($merchantView);
                }
                $image = array();
                foreach ($detail['MerchantImage'] as $i => $images) {
                    $image[$i] = $images;
                    $image[$i]['image'] = Configure::read('App.baseUrl') . "/files/" . $images['image'];
                }
                $detail['MerchantImage'] = $image;
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
                $this->User->Appointment->id = $this->request->data['appointment_id'];
                $payment['payment_status'] = 1;
                if ($this->User->Appointment->save($payment)) {
                    $response['status'] = true;
                } else {
                    $response['status'] = false;
                    $response['message'] = 'Can not update your payment status, please contact to administrator immediately';
                }
            } else {
                $response['status'] = false;
                $response['message'] = 'Can not update your payment status, please contact to administrator immediately';
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
                $merchantId = $this->request->data['merchant_id'];
                $merchantDetails = $this->User->Merchant->findById($merchantId);
                if (!empty($merchantDetails)) {
                    $userId = array();
                    if (!empty($merchantDetails['Barber'])) {
                        foreach ($merchantDetails['Barber'] as $barber) {
                            $userId[] = $barber['user_id'];
                        }
                    }
                    $userId[] = $merchantDetails['Merchant']['user_id'];
                    $devices = $this->User->Device->find('all', array('conditions' => array(
                            'Device.user_id' => $userId
                    )));
                    if (!empty($devices)) {
                        $androidIds = array();
                        $this->loadModel('PushMessage');
                        $subject = 'Appointment';
                        $description = 'A new appointment has been received, please go to dashboard page and check the details';
                        foreach ($devices as $device) {
                            $androidIds[] = $device['Device']['registrationid'];
                            $this->PushMessage->create();
                            $msg['user_id'] = $device['Device']['user_id'];
                            $msg['subject'] = $subject;
                            $msg['description'] = $description;
                            $this->PushMessage->save($msg);
                        }
                        if (!empty($androidIds)) {
                            $message = array
                                (
                                'message' => $description,
                                'title' => $subject,
                                'vibrate' => 1,
                                'sound' => 1
                            );
                            $this->sendPushToAndroid($androidIds, $message);
                        }
                    }
                }
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

    public function createInvoice() {
        if ($this->request->is('post')) {
            $appointment = $this->User->Appointment->findById($this->request->data['appointment_id']);
            $this->request->data['user_id'] = $appointment['Appointment']['user_id'];
            if ($this->User->Invoice->save($this->request->data)) {
                $response['status'] = true;
            } else {
                $response['status'] = false;
                $response['message'] = 'Can not create invoice';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function getBarberAccounts() {
        if ($this->request->is('post')) {
            $merchantDetail = $this->User->Merchant->findByUserId($this->request->data['user_id']);
            if (!empty($merchantDetail)) {
                $this->loadModel("Barber");
                $allBarbers = $this->Barber->findAllByMerchantId($merchantDetail['Merchant']['id']);
                if (!empty($allBarbers)) {
                    $response['status'] = true;
                    $response['data'] = $allBarbers;
                } else {
                    $response['status'] = false;
                    $response['message'] = 'Barbers can not found, please add new barber by clicking on add button';
                }
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

    public function addBarber() {
        if ($this->request->is('post')) {
            $role = $this->User->Role->findByName('barber');
            if (!empty($role)) {
                $already = $this->User->findByEmail($this->request->data['email']);
                if (empty($already)) {
                    $merchantDetail = $this->User->Merchant->findByUserId($this->request->data['merchant_user_id']);
                    $this->request->data['role_id'] = $role['Role']['id'];
                    $this->request->data['status'] = 1;
                    $this->request->data['password'] = AuthComponent::password($this->request->data['password']);
                    $this->User->set($this->request->data);
                    if ($this->User->save()) {
                        $this->loadModel('Barber');
                        $barber['user_id'] = $this->User->getInsertID();
                        $barber['merchant_id'] = $merchantDetail['Merchant']['id'];
                        $barber['name'] = $this->request->data['name'];
                        if ($this->Barber->save($barber)) {
                            $response['status'] = true;
                        } else {
                            $this->User->delete($this->User->getInsertID());
                            $response['status'] = false;
                            $response['message'] = 'Can not create account, please try again later';
                        }
                    } else {
                        $response['status'] = false;
                        $response['message'] = 'Can not create account, please try again later';
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

    public function getFilterSaloons() {
        if ($this->request->is('post')) {
            $lat = $this->request->data['lat'];
            $lng = $this->request->data['lng'];
            $radius = $this->request->data['distance'];
            $rate = $this->request->data['rate'];
            $sql = "SELECT
                    Merchant.*,
                    ( 6371 * acos( cos( radians({$lat}) ) * cos( radians( `lat` ) ) * cos( radians( `lng` ) - "
                    . "radians({$lng}) ) + sin( radians({$lat}) ) * sin( radians( `lat` ) ) ) ) AS distance "
                    . "FROM merchants as Merchant"
                    . " WHERE current_rating>='{$rate}'"
                    . " HAVING distance <= {$radius}
                ORDER BY distance ASC";
            $merchants = $this->User->Merchant->query($sql);
            if (!empty($merchants)) {
                $merchant = array();
                foreach ($merchants as $k => $m) {
                    if (isset($this->request->data['type']) && !empty($this->request->data['type'])) {
                        $mt = array();
                        foreach ($this->request->data['type'] as $k => $v) {
                            $mt[] = $k;
                        }
                        $merchantType = $this->User->Merchant->MerchantType->find('first', array('conditions' => array(
                                'MerchantType.name' => $mt,
                                'MerchantType.merchant_id' => $m['Merchant']['id']
                        )));
                        if (!empty($merchantType)) {
                            $this->User->Merchant->MerchantType->recursive = 0;
                            $merchantType = $this->User->Merchant->MerchantType->findAllByMerchantId($m['Merchant']['id']);
                            $merchantImage = $this->User->Merchant->MerchantImage->findByMerchantId($m['Merchant']['id']);
                            $merchant[$k] = $m;
                            $merchant[$k]['MerchantType'] = $merchantType;
                            $merchant[$k]['MerchantImage'] = $merchantImage;
                        }
                    } else {
                        $this->User->Merchant->MerchantType->recursive = 0;
                        $merchantType = $this->User->Merchant->MerchantType->findAllByMerchantId($m['Merchant']['id']);
                        $merchantImage = $this->User->Merchant->MerchantImage->findByMerchantId($m['Merchant']['id']);
                        $merchant[$k] = $m;
                        $merchant[$k]['MerchantType'] = $merchantType;
                        $merchant[$k]['MerchantImage'] = $merchantImage;
                    }
                }
                if (!empty($merchant)) {
                    $response['status'] = true;
                    $response['data'] = $merchant;
                } else {
                    $response['status'] = false;
                    $response['message'] = 'Saloons can not found in these merchant types';
                }
            } else {
                $response['status'] = false;
                $response['message'] = 'Saloons can not found in these filter options';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function registerDevice() {
        if ($this->request->is('post')) {
            $this->loadModel('Device');
            $already = $this->Device->findByUserId($this->request->data['user_id']);
            if (!empty($already)) {
                $this->Device->id = $already['Device']['id'];
            }
            if ($this->Device->save($this->request->data)) {
                $response['status'] = true;
            } else {
                $response['status'] = false;
                $response['message'] = 'Device can not register';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function sendPushToAndroid($ids = array(), $msg = array()) {
        define('API_ACCESS_KEY', 'AIzaSyDxV5hyU6S0zjvNQbdJOUIA7tMw1udQaiM');
        $registrationIds = $ids;
        /* $msg = array
          (
          'message' => 'here is a message. message',
          'title' => 'This is a title. title',
          'subtitle' => 'This is a subtitle. subtitle',
          'tickerText' => 'Ticker text here...Ticker text here...Ticker text here',
          'vibrate' => 1,
          'sound' => 1
          ); */
        $fields = array
            (
            'registration_ids' => $registrationIds,
            'data' => $msg
        );

        $headers = array
            (
            'Authorization: key=' . API_ACCESS_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function bankDetails() {
        if ($this->request->is('post')) {
            $merchant = $this->User->Merchant->find('first', array('conditions' => array(
                    'Merchant.user_id' => $this->request->data['merchant_user_id']
            )));
            if (!empty($merchant)) {
                $this->User->Merchant->id = $merchant['Merchant']['id'];
                if ($this->User->Merchant->save($this->request->data)) {
                    $merchant = $this->User->Merchant->find('first', array('conditions' => array(
                            'Merchant.user_id' => $this->request->data['merchant_user_id']
                    )));
                    $response['status'] = true;
                    $response['data'] = $merchant;
                } else {
                    $response['status'] = false;
                    $response['message'] = 'Can not update';
                }
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

    public function getUnreadNotificationCount() {
        $response['status'] = false;
        $response['message'] = 'Request is not valid';
        if ($this->request->is('post')) {
            $notification = $this->User->PushMessage->find('count', array('conditions' => array(
                    'PushMessage.user_id' => $this->request->data['user_id'],
                    'PushMessage.is_read' => 0
            )));
            if (!empty($notification)) {
                $response['status'] = true;
                $response['data'] = $notification;
            }
        }
        echo json_encode($response);
    }

    public function getAllNotifications() {
        if ($this->request->is('post')) {
            $notification = $this->User->PushMessage->find('all', array('conditions' => array(
                    'PushMessage.user_id' => $this->request->data['user_id']
            )));
            if (!empty($notification)) {
                $this->User->PushMessage->updateAll(
                        array('PushMessage.is_read' => 1), array('PushMessage.user_id' => $this->request->data['user_id'])
                );
                $response['status'] = true;
                $response['data'] = $notification;
            } else {
                $response['status'] = false;
                $response['message'] = 'Can not found notifications';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function makeReviewLike() {
        if ($this->request->is('post')) {
            $alreadyLike = $this->User->Review->ReviewLike->find("first", array("conditions" => array(
                    "ReviewLike.review_id" => $this->request->data['review_id'],
                    "ReviewLike.user_id" => $this->request->data['user_id']
            )));
            if (!empty($alreadyLike)) {
                $this->User->Review->ReviewLike->id = $alreadyLike['ReviewLike']['id'];
                $this->User->Review->ReviewLike->delete();
            } else {
                $this->User->Review->ReviewLike->save($this->request->data);
            }
            $total = $this->User->Review->ReviewLike->find("count", array("conditions" => array(
                    "ReviewLike.review_id" => $this->request->data['review_id']
            )));
            $this->User->Review->id = $this->request->data['review_id'];
            $this->User->Review->save(array("current_likes" => $total));
            $response['status'] = true;
            $response['data'] = $total;
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function makeReviewComment() {
        if ($this->request->is('post')) {
            $alreadyComment = $this->User->Review->ReviewComment->find("first", array("conditions" => array(
                    "ReviewComment.review_id" => $this->request->data['review_id'],
                    "ReviewComment.user_id" => $this->request->data['user_id']
            )));
            if (!empty($alreadyComment)) {
                $response['status'] = false;
                $response['message'] = 'You have already commented';
            } else {
                $this->User->Review->ReviewComment->save($this->request->data);
                $total = $this->User->Review->ReviewComment->find("count", array("conditions" => array(
                        "ReviewComment.review_id" => $this->request->data['review_id']
                )));
                $this->User->Review->id = $this->request->data['review_id'];
                $this->User->Review->save(array("current_comments" => $total));
                $response['status'] = true;
                $response['data'] = $total;
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function makeReviewShare() {
        if ($this->request->is('post')) {
            $this->User->Review->ReviewShare->save($this->request->data);
            $total = $this->User->Review->ReviewShare->find("count", array("conditions" => array(
                    "ReviewShare.review_id" => $this->request->data['review_id']
            )));
            $this->User->Review->id = $this->request->data['review_id'];
            $this->User->Review->save(array("current_shares" => $total));
            $response['status'] = true;
            $response['data'] = $total;
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function getRecentlyViewed() {
        if ($this->request->is('post')) {
            $saloons = $this->User->MerchantView->find("all", array("conditions" => array(
                    "MerchantView.user_id" => $this->request->data['user_id']
            )));
            if (!empty($saloons)) {
                $newArray = array();
                foreach ($saloons as $k => $saloon) {
                    $types = $this->User->Merchant->MerchantType->findAllByMerchantId($saloon['MerchantView']['merchant_id']);
                    $images = $this->User->Merchant->MerchantImage->findByMerchantId($saloon['MerchantView']['merchant_id']);
                    $newArray[$k] = $saloon;
                    $newArray[$k]['MerchantType'] = $types;
                    $newArray[$k]['MerchantImage'] = $images;
                    $newArray[$k]['MerchantImage']['MerchantImage']['image'] = Configure::read("App.baseUrl") . "/files/" . $images['MerchantImage']['image'];
                }
                $saloons = $newArray;
                $response['status'] = true;
                $response['data'] = $saloons;
            } else {
                $response['status'] = false;
                $response['message'] = 'Can not find saloons';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function getMyAllReviews() {
        if ($this->request->is('post')) {
            $this->User->Review->recursive = -1;
            $reviews = $this->User->Review->findAllByUserId($this->request->data['user_id']);
            if (!empty($reviews)) {
                $newArray = array();
                foreach ($reviews as $k => $review) {
                    $newArray[$k] = $review;
                    $newArray[$k]['Review']['created'] = date("d M - h:i a", strtotime($review['Review']['created']));
                    $newArray[$k]['UserImage'] = $this->User->UserImage->findAllByReviewId($review['Review']['id']);
                    $image = array();
                    foreach ($newArray[$k]['UserImage'] as $i => $images) {
                        $image[$i] = $images;
                        $image[$i]['UserImage']['image'] = Configure::read("App.baseUrl") . "/files/" . $images['UserImage']['image'];
                    }
                    $newArray[$k]['UserImage'] = $image;
                    $this->User->Merchant->recursive = -1;
                    $newArray[$k]['Merchant'] = $this->User->Merchant->findById($review['Review']['merchant_id']);
                    $newArray[$k]['MerchantType'] = $this->User->Merchant->MerchantType->findAllByMerchantId($review['Review']['merchant_id']);
                    /* $newArray[$k]['Like'] = $this->User->Review->ReviewLike->find('count', array('conditions' => array(
                      'ReviewLike.review_id' => $review['Review']['id']
                      )));
                      $newArray[$k]['Comment'] = $this->User->Review->ReviewComment->find('count', array('conditions' => array(
                      'ReviewComment.review_id' => $review['Review']['id']
                      )));
                      $newArray[$k]['Share'] = $this->User->Review->ReviewShare->find('count', array('conditions' => array(
                      'ReviewShare.review_id' => $review['Review']['id']
                      ))); */
                }
                $reviews = $newArray;
                $response['status'] = true;
                $response['data'] = $reviews;
            } else {
                $response['status'] = false;
                $response['message'] = 'Can not found reviews';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function getAllComments() {
        if ($this->request->is('post')) {
            $comments = $this->User->Review->ReviewComment->findAllByReviewId($this->request->data['id']);
            if (!empty($comments)) {
                $newArray = array();
                foreach ($comments as $k => $comment) {
                    $newArray[$k] = $comment;
                    $newArray[$k]['ReviewComment']['created'] = date("d M y h:i a", strtotime($comment['ReviewComment']['created']));
                    $newArray[$k]['User'] = $this->User->UserDetail->findByUserId($comment['ReviewComment']['user_id']);
                    $newArray[$k]['User']['UserDetail']['profile_picture'] = $newArray[$k]['User']['UserDetail']['profile_picture'] != '' ? Configure::read("App.baseUrl") . "/files/" . $newArray[$k]['User']['UserDetail']['profile_picture'] : '';
                }
                $comments = $newArray;
                $response['status'] = true;
                $response['data'] = $comments;
            } else {
                $response['status'] = false;
                $response['message'] = 'Can not found comments';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function getReviewDetail() {
        if ($this->request->is('post')) {
            $review = $this->User->Review->findById($this->request->data['id']);
            if (!empty($review)) {
                $response['status'] = true;
                $response['data'] = $review;
            } else {
                $response['status'] = false;
                $response['message'] = 'Can not found review';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function getAllCommunityReviews() {
        if ($this->request->is('post')) {
            $getReviews = $this->User->Review->query('select Review.* from reviews as Review, users as User, followers as Follower '
                    . 'where Follower.follower_id=' . $this->request->data["user_id"] . ' and Follower.user_id=User.id '
                    . 'and User.id=Review.user_id');
            //pr($getReviews);die;
            if (!empty($getReviews)) {
                $newArray = array();
                foreach ($getReviews as $k => $reviews) {
                    $this->User->Merchant->recursive = -1;
                    $merchant = $this->User->Merchant->findById($reviews['Review']['merchant_id']);
                    //pr($merchant);die;
                    $merchantTypes = $this->User->Merchant->MerchantType->findAllByMerchantId($merchant['Merchant']['id']);
                    $userImages = $this->User->UserImage->findAllByReviewId($reviews['Review']['id']);
                    $image = array();
                    foreach ($userImages as $i => $images) {
                        $image[$i] = $images;
                        $image[$i]['UserImage']['image'] = Configure::read("App.baseUrl") . "/files/" . $images['UserImage']['image'];
                    }
                    $newArray[$k]['Review'] = $reviews['Review'];
                    $newArray[$k]['Merchant'] = $merchant;
                    $newArray[$k]['MerchantType'] = $merchantTypes;
                    $newArray[$k]['UserImage'] = $image;
                }
                $getReviews = $newArray;
                $response['status'] = true;
                $response['data'] = $getReviews;
            } else {
                $response['status'] = false;
                $response['message'] = 'Reviews can not found';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function findFriends() {
        if ($this->request->is('post')) {
            $allFollowers = $this->User->Follower->find("list", array("conditions" => array(
                    "Follower.follower_id" => $this->request->data['user_id']
                ), "fields" => array("Follower.id", "Follower.user_id")));
            $allFollowers[] = $this->request->data['user_id'];
            sort($allFollowers);
            $users = $this->User->UserDetail->find("all", array("conditions" => array(
                    "UserDetail.name like " => $this->request->data['name'] . "%",
                    "UserDetail.user_id <>" => $allFollowers,
            )));
            $image = array();
            if (!empty($users['UserDetail'])) {
                foreach ($users['UserDetail'] as $i => $user) {
                    $image[$i] = $user;
                    $image[$i]['profile_picture'] = $user['profile_picture'] != '' ? Configure::read("App.baseUrl") . "/files/" . $user['profile_picture'] : '';
                }
                $users['UserDetail'] = $image;
            }
            if (!empty($users)) {
                $response['status'] = true;
                $response['data'] = $users;
            } else {
                $response['status'] = false;
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

    public function deleteBarber() {
        if ($this->request->is('post')) {
            if ($this->User->Merchant->Barber->delete($this->request->data['id'])) {
                $response['status'] = true;
            } else {
                $response['status'] = false;
                $response['message'] = 'Can not delete';
            }
        } else {
            $response['status'] = false;
            $response['message'] = 'Request is not valid';
        }
        echo json_encode($response);
    }

}
