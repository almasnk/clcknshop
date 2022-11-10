<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use DateTime;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Url;
use phpDocumentor\Reflection\Types\This;

/**
 * Customers Controller
 *
 * @property \App\Model\Table\CustomersTable $Customers
 *
 * @method \App\Model\Entity\Customer[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class CustomersController extends AppController
{
    /*
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Paginator');
        //$this->loadComponent('RequestHandler');
        Configure::write('Session', [ 'defaults' => 'php' ]);

        $this->loadComponent('Auth', [
            'loginAction' => [
                'controller' => 'Customers',
                'action' => 'login'
            ],
            'authError' => 'Unauthorized Access',
            'authenticate' => [
                'Form' => [
                    'fields' => [
                        'email' => 'email',
                        'passwd' => 'passwd'
                    ],
                    'userModel'=>'Customers'
                ]
            ]
        ]);

    }

    */

    private $customer = null;
    private $customer_id = null;

    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        if ($this->request->getSession()->check('customer')){
            $this->customer = $this->request->getSession()->read('customer');
            $this->customer_id = $this->customer->id;
        }
    }

    public function beforeRender(Event $event)
    {
       $this->viewBuilder()->setLayout('frontend');
       $theme = Configure::read("App.theme");
       $store_name = Configure::read('App.store_title',"Clcknshop");
       $this->set('theme',$theme);
       $this->set('store_name',$store_name);
       $this->set('theme_root',Router::url('/') . 'themes/' . STORENAME . "/" . $theme . "/assets/");
       $this->set('cdn_root',Configure::read("cdn_server",Router::url('/') . 'themestore/cdn/') . $theme . "/");

       $this->set('uri_root',Router::url('/'));
       $this->set('request_uri',$_SERVER['REQUEST_URI']);

        return parent::beforeRender($event); // TODO: Change the autogenerated stub
    }



    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */

    public function index()
    {
        if (!$this->request->session()->read('customer_logged_in')) return $this->redirect(['action' => 'login']);
        $customer = $this->Customers->get($this->customer_id, [
            'contain' => [
                'Orders' => ['sort' => ['Orders.id' => 'DESC']]
            ],

        ]);
        //pr($this->request->getSession()->read('customer'));
        $this->set('customer', $customer);

    }



    /**
     * View method
     *
     * @param string|null $id Customer id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id)
    {
        if (!$this->request->session()->read('customer_logged_in')) return $this->redirect(['action' => 'login']);
        $Orders = TableRegistry::getTableLocator()->get('Orders');
        $order = $Orders->find('all')->where([
            'Orders.id' => $id,
            'Orders.customers_id' => $this->customer_id
        ])->contain(
            ['Customers','OrderProducts', 'PaymentProcessor',
                'OrderLogs' => [
                    'sort' => ['OrderLogs.id' => 'DESC']
                ]
            ]
        )->first();



        if (count((array)$order) == 0) return $this->redirect(['controller' => 'Customers', 'action' => 'index']);

//        $shippingMethods = $Orders->ShippingMethods->find('all', ['limit' => 200]);
        $this->set(compact('order'));

    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.

    public function add()
    {
        $customer = $this->Customers->newEntity();
        if ($this->request->is('post')) {
            $customer = $this->Customers->patchEntity($customer, $this->request->getData());
            if ($this->Customers->save($customer)) {
                $this->Flash->success(__('The customer has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The customer could not be saved. Please, try again.'));
        }
        $this->set(compact('customer'));
    }
*/
    /**
     * Edit method
     *
     * @param string|null $id Customer id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.

    public function edit()
    {

        if ($this->request->is(['patch', 'post', 'put'])){

            $customer = $this->Customers->get($this->customer_id, [
                'contain' => [],
            ]);
            $customer = $this->Customers->patchEntity($customer, $this->request->getData());
            if ($this->Customers->save($customer)) {
                $this->request->session()->write([
                    'customer_logged_in' => true,
                    'customer' => $customer
                ]);
                $this->Flash->success(__('The customer has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The customer could not be saved. Please, try again.'));
        }
        return $this->redirect(['controller' => 'Customers', 'action' => 'index']);
    }
 */
    /**
     * Delete method
     *
     * @param string|null $id Customer id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $customer = $this->Customers->get($id);
        if ($this->Customers->delete($customer)) {
            $this->Flash->success(__('The customer has been deleted.'));
        } else {
            $this->Flash->error(__('The customer could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
 */

    private function ajaxresponse($status = 0,$message = "",$errors = []){
     if ($this->request->is('ajax')){
         $data['status'] = $status;
         $data['message'] = $message;
         $data['errors'] = $errors;
         $this->set('data',$data);
         $this->set('_serialize', 'data');
         $this->RequestHandler->renderAs($this, 'json');
         return true;
     }else{
         if($status == 0)
             $this->Flash->success($message);
         else
             $this->Flash->error($message);
         return false;
     }
    }

    public function login()
    {
        if ($this->request->session()->read('customer_logged_in')) return $this->redirect(['action' => 'index']);

        $loginConfig = json_decode(Configure::read('App.login'));

        if (isset($loginConfig->use_otp_login) && $loginConfig->use_otp_login == "on"){
            return $this->redirect(['action' => 'otpLogin']);
        }


        if ($this->request->is('post')) {
            $data = $this->request->getData();



            if (empty($data['username']) == false && empty($data['password']) == false){
                $customer = $this->Customers->find('all')->where([
                    'username' => trim($data['username']),
                    'passwd' => md5(trim($data['password']))
                ])->first();


//                pr($customer);
                if ($customer){
                    $this->request->session()->write([
                            'customer_logged_in' => true,
                            'customer' => $customer
                        ]);

                    $this->Flash->success("You are logged in successfully");
                    if (empty($data['referer']) || $data['referer'] == Router::url('/', true) || $data['referer'] == '/')
                        return $this->redirect(['controller' => 'Customers' ,'action' => 'index']);
                    else
                        $this->redirect($data['referer']);

                }else{
                    $this->Flash->error('Your username or password is incorrect.');
                }
            }else{
                $this->Flash->error('username or password must not be empty.');
            }
        }

        $this->render('login');
    }


    public function otpLogin()
    {
        $loginConfig = json_decode(Configure::read('App.login'));

        if ($loginConfig->use_otp_login != "on"){
            return $this->redirect(['action' => 'login']);
        }

        if ($this->request->is('post')){
            $phone = $this->request->getData('phone');
            $code = rand(100000, 999999);
            $date = new DateTime();
            //$timestamp = $date->getTimestamp();
            $exp = strtotime('now +2 minutes');
            $otp = [
                'phone' => $phone,
                'code' => $code,
                'exp' => $exp,
                'status' => 0,
                'next_action' => 'verify'
            ];
            $this->request->getSession()->write('otp', $otp);

            $shop_title = Configure::read('App.store_title');
            $msg = "Dear Customer, {$code} is your OTP. {$shop_title}";
            $this->SMS->send($phone, $msg);
            //pr($msg);
            //die();

            return $this->redirect(['action' => 'verifyOtp']);
        }

    }

    public function verifyOtp()
    {
        $otp = $this->request->getSession()->read('otp');
        $this->set('otp', $otp);
        $date = new DateTime();
        $timestamp = $date->getTimestamp();
//        pr($otp);
        if (!$otp || $otp['next_action'] != 'verify' || $timestamp > $otp['exp']){
            $this->Flash->error('Your session may be expired.');
            return $this->redirect(['action' => 'otpLogin']);
        }

        if ($this->request->is('post')){
            $code = $this->request->getData('code');

            if (empty($code)){
                $this->Flash->error('Code could not be Empty');
                return;
            }

            if ($code != $otp['code']){
                $this->Flash->error('Invalid OTP. Please enter your valid OTP');
                return;
            }

            $customer = $this->Customers->find('all')->where(['username' => $otp['phone']])->first();



            if($customer){
                $this->request->getSession()->delete('otp');
                $this->request->getSession()->write([
                    'customer_logged_in' => true,
                    'customer' => $customer
                ]);
                $this->Flash->success("You are logged in successfully");
                return $this->redirect(['action' => 'index']);
            }
            else{
                $otp['status'] = 1;
                $otp['next_action'] = 'add_customer';
                $this->request->getSession()->write('otp', $otp);
                return $this->redirect(['action' => 'addCustomer']);
            }
        }

    }

    public function resendOtp()
    {
        $otp = $this->request->getSession()->read('otp');
        $date = new DateTime();
        $timestamp = $date->getTimestamp();

        if (!$otp || $timestamp < $otp['exp']){
            return $this->redirect(['action' => 'verifyOtp']);
        }
        $shop_title = Configure::read('App.store_title');
        $msg = "Dear Customer, {$otp['code']} is your OTP. {$shop_title}";
        $this->SMS->send($otp['phone'], $msg);

        $otp['exp'] = strtotime('now +2 minutes');
        $this->request->getSession()->write('otp', $otp);
        $this->Flash->success('OTP Resent successfully');
        return  $this->redirect(['action' => 'verifyOtp']);

    }
    
    
    public function addCustomer()
    {
        $otp = $this->request->getSession()->read('otp');
        if (!$otp || $otp['status'] == 0 || $otp['next_action'] != 'add_customer'){
            return $this->redirect(['action' => 'verifyOtp']);
        }

        if ($this->request->is('post')){

            $data = $this->request->getData();
            $data['username'] = $otp['phone'];
            $data['phone']  = $otp['phone'];
            $customer = $this->Customers->newEntity();
            $customer = $this->Customers->patchEntity($customer, $data);

            if ($this->Customers->save($customer)){
                $session = $this->request->session();
                $session->delete('otp');
                $session->write([
                    'customer_logged_in' => true,
                    'customer' => $customer
                ]);
                $this->Flash->success("You are logged in successfully");
                return  $this->redirect(['action' => 'index']);
            }
            else{
                $this->Flash->error('Ops ! there was something wrong. please try again later.');
                return $this->redirect(['action' => 'otpLogin']);
            }
        }

    }


    public function register()
    {
        if ($this->request->session()->read('customer_logged_in')) return $this->redirect(['action' => 'index']);

        $errors = array();
        if ($this->request->is('post')){
            $data = $this->request->getData();

            if (!isset($data['first_name']) || empty($data['first_name'])){
                $errors['first_name'] = "First Name must not be empty";
            }
            if (!isset($data['last_name']) || empty($data['last_name'])){
                $errors['last_name'] = "Last Name must not be empty";
            }
            if (!isset($data['email']) || empty($data['email'])){
                $errors['email'] = "Email address must not be empty";
            }
//            if (!isset($data['phone']) || empty($data['phone'])){
//                $errors['phone'] = "Phone number is required";
//            }
            if (!isset($data['password']) || empty($data['password'])){
                $errors['password'] = "Password field is required";
            }else if ( strlen($data['password']) <6 ){
                $errors['password'] = "Password must be at least 6 characters";
            }

            if (!isset($data['confirm_password']) || empty($data['confirm_password'])){
                $errors['confirm_password'] = "Confirm Password field is required";
            }else if (!isset($data['password']) || ($data['password'] != $data['confirm_password'])){
                $errors['confirm_password'] = "Confirm password does not match";
            }



            if (count($errors) == 0){
                $data['username'] = trim(isset($data['username']) ? $data['username'] : $data['email']);
                $customer = $this->Customers->find('all')->where(['username' => $data['username']])->first();
                if ($customer){
                    $this->Flash->error("Username or email already exist. please forget your password");
                    return $this->redirect($this->referer());
                }

                $data['passwd'] = md5($data['password']);
                $customer = $this->Customers->newEntity();
                $customer = $this->Customers->patchEntity($customer, $data);
                if ($this->Customers->save($customer)) {
                    $this->Flash->success(__('Your registration has been completed.'));
                    $this->request->session()->write([
                        'customer_logged_in' => true,
                        'customer' => $customer
                    ]);

                    $store = json_decode(Configure::read('App.store'));
                    $this->Mail->send($customer->email, "Welcome to ".$store->title, ['customer' => $customer, 'store' => $store], 'welcome');

                    return $this->redirect(['action' => 'index']);
                }

                foreach ($customer->errors() as $key => $field){
                    $fld = ucfirst($key);
                    foreach ($field as $error) $this->Flash->error("{$fld} : {$error}");
                }
            }else{
                foreach ($errors as $error) $this->Flash->error($error);
                $this->set('customer', $data);
            }

            //$this->Flash->error(__('The customer could not be saved.'));

        }

//        pr($errors);
    }

    public function resetPassword($token = null)
    {
        if ($this->request->session()->read('customer_logged_in')) return $this->redirect(['action' => 'index']);

        if (isset($token) && empty($token) == false){
            if($this->validNonce($token) == false){
                $this->Flash->error("Reset Password token Invalid or Expired");
                return $this->redirect($this->referer());
            }

            $customer = $this->Customers->find('all')->where(['token' => trim($token)])->first();
            if (!$customer) {
                $this->Flash->error("Invalid Token. Please try to reset again");
                return $this->redirect($this->referer());
            }

            $this->set('token', $token);
            $this->render('new_password');
            return ;
        }


        if ($this->request->is('post')){
            $data = $this->request->getData();

            if(!empty($data['token'])){
                if (!isset($data['password']) || empty($data['password'])){
                    $this->Flash->error("Password field is required");
                    return $this->redirect($this->referer());
                }elseif (strlen($data['password']) <6 ){
                    $this->Flash->error("Password must be at least 6 characters");
                    return $this->redirect($this->referer());
                }
                else if (!isset($data['confirm_password']) || empty($data['confirm_password'])){
                    $this->Flash->error("Confirm Password field is required");
                    return $this->redirect($this->referer());

                }else if (!isset($data['password']) || ($data['password'] != $data['confirm_password'])){
                     $this->Flash->error("Confirm password does not match");
                    return $this->redirect($this->referer());
                }

                $customer = $this->Customers->find('all')->where(['token' => $data['token']])->first();

                if (!$customer){
                    $this->Flash->error("Invalid Token. Please try to reset again");
                    return $this->redirect($this->referer());
                }

                $customer->token = null;
                $customer->passwd = md5($data['password']);

                if ($this->Customers->save($customer)){
                    $this->Flash->success('Your password has been changed.');
                    return $this->redirect(Router::url('login', true));
                }

                $this->Flash->error('Ops There was something wrong. please try again later');
                return $this->redirect($this->referer());

            }



            $email = $this->request->getData('email');
            if (empty($email)){
                $this->Flash->error("The email address could not be empty.");
                return $this->redirect($this->referer());
            }

            $customer = $this->Customers->find('all')->where(['username' => $email])->first();
            if (!$customer){
               $this->Flash->error("The email address does not exists, Please enter valid email.");
               return $this->redirect($this->referer());
            }

            $token = $this->generateNonce();
            $customer->token = $token;
            if (!$this->Customers->save($customer)){
                $this->Flash->error("Ops! there was something wrong. please try again later.");
                return $this->redirect($this->referer());
            }

            $mail = $this->Mail->send($customer->email, 'Reset Password Link', ['customer_name' => $customer->first_name  . " " . $customer->last_name, 'link' => Router::url('reset-password/' . $token, true)], 'reset_password');

            if ($mail){
                $this->Flash->error("Ops! there was something wrong. please try again later.");
            }
            else{
                $this->Flash->success('An email has been sent to the email address. please follow the instruction to reset password.');
            }
        }
    }


    protected function generateNonce()
    {
        $expiryTime = microtime(true) + (3600 * 24);
        $secret = Configure::read('app_token',"clcknshop");
        $signatureValue = hash_hmac('sha256', $expiryTime . ':' . $secret, $secret);
        $nonceValue = $expiryTime . ':' . $signatureValue;

        return base64_encode($nonceValue);
    }

    /**
     * Check the nonce to ensure it is valid and not expired.
     *
     * @param string $nonce The nonce value to check.
     * @return bool
     */
    protected function validNonce($nonce)
    {
        $value = base64_decode($nonce);
        if ($value === false) {
            return false;
        }
        $parts = explode(':', $value);
        if (count($parts) !== 2) {
            return false;
        }
        $expires = $parts[0];
        $checksum = $parts[1];
        //[$expires, $checksum] = $parts;

        if ($expires < microtime(true)) {
            return false;
        }
        $secret = Configure::read('app_token',"clcknshop");
        $check = hash_hmac('sha256', $expires . ':' . $secret, $secret);

        return hash_equals($check, $checksum);
    }





    public function edit()
    {

        if ($this->request->is(['patch', 'post', 'put'])){

            $customer = $this->Customers->get($this->customer_id, [
                'contain' => [],
            ]);
            $customer = $this->Customers->patchEntity($customer, $this->request->getData());
            if ($this->Customers->save($customer)) {
                $this->request->session()->write([
                    'customer_logged_in' => true,
                    'customer' => $customer
                ]);
                $this->Flash->success(__('The customer has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The customer could not be saved. Please, try again.'));
        }
        return $this->redirect(['controller' => 'Customers', 'action' => 'index']);
    }


    public function changePassword()
    {

        if ($this->request->is('post')){
            $data = $this->request->getData();
            if (empty($data['new_password']) || empty($data['confirm_password']) || empty($data['old_password'])){
                $this->Flash->error('Field could not be empty!');
                return $this->redirect($this->referer());
            }
            if($data['new_password'] != $data['confirm_password']){
                $this->Flash->error('Confirm password does not match!');
                return $this->redirect($this->referer());
            }

            $customer = $this->request->session()->read('customer');
            $old_hash_password  = md5($data['old_password']);

            if ($customer->passwd != $old_hash_password){
                $this->Flash->error('Old password does not match!');
                return $this->redirect($this->referer());
            }

            $customer = $this->Customers->get($customer->id);
            $customer->passwd = md5($data['new_password']);
            if ($this->Customers->save($customer)){
                $this->Flash->error('Password changed successfully, please login again');
                $this->redirect(['controller' => 'Customers', 'action' => 'logout']);
            }
            return $this->redirect($this->referer());
        }
        return $this->redirect($this->referer());
    }

    public function profile()
    {
        if (!$this->request->session()->read('customer_logged_in')) return $this->redirect(['action' => 'login']);
        $customer = $this->Customers->get($this->customer_id);
        $this->set('customer', $customer);

    }


    public function logout(){
        $this->autoRender = false;
        $session = $this->request->getSession();
        $session->delete('customer_logged_in');
        $session->delete('customer');
        return $this->redirect(['action' => 'login']);
    }


}
