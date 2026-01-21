<?php

declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use Cake\Log\Log;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/4/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication');

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/4/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');
    }

    //https://book.cakephp.org/5/en/tutorials-and-examples/cms/authentication.html for reference
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['index', 'view', 'add', 'login', 'pet', 'getUsersPets', 'delete']);
        $user = $this->Authentication->getIdentity();
        $this->set('authUser', $user);
    }

    /* #region Main - Basic Auth*/

    // used for previos authentication, changed to the plugin
    // protected function getUser():?array{
    //    $user=$this->request->getSession()->read('Auth');
    //    if(is_array($user)){
    //        return $user;
    //    }else{
    //        return null;
    //    }
    //}
    // protected function getUser(){
    //    $auth = $this->Authentication->getIdentity();
    //    if($auth){
    //        return [
    //        'id' => $auth->id,
    //        'first_name' => $auth->first_name,
    //        'last_name' => $auth->last_name,
    //        'email' => $auth->email,
    //        'role' => $auth->role,
    //        'is_banned' => $auth->is_banned
    //        ];
    //    }
    //    else{
    //        return null;
    //    }
    //}

    /* #endregion */


    protected function isLoggedIn(){
        //if(!$this->getUser())
        $user = $this->Authentication->getIdentity();
        if(!$user){
            $this->Flash->error("Seems you are not logged....");
            return $this->redirect(['controller'=>'Users','action'=>'login']);
        }
        return null;
    }

    protected function isAdmin(){
        //$user=$this->getUser();
        $user = $this->Authentication->getIdentity();
        if(!$user){
            $this->Flash->error("Seems you are not logged....");
            return $this->redirect(['controller'=>'Users','action'=>'login']);
        }

        if($user->role !== 'admin'){
            $this->Flash->error("Seems you are not an admin ....");
            return $this->redirect(['controller'=>'Pets','action'=>'index']);
        }
        return null;
    }

    //https://book.cakephp.org/5/en/core-libraries/logging.html
    protected function writeLog(string $level, string $scope, string $msg, array $context = []): void{
        //$auth=$this->request->getSession()->read('Auth');
        //$user=$this->getUser();
        $user =$this->Authentication->getIdentity();

        $userId =$user?$user->get('id'):'guest';
        //$logmsg = "User: $userId - Scope: $scope - MSG: $msg";
        //Log::write($level, $logmsg,['scope'=>'julian']);

        //I know that I am using scope different just I see it clearer this way
        //Every log will be save in the julian log file
        $logmsg=['user_id'=>$userId, 'scope'=>$scope]+$context;
        Log::write($level, 'MSG: '.$msg.' -> '.json_encode($logmsg,JSON_UNESCAPED_SLASHES),['scope'=>'julian']);

    }


}
