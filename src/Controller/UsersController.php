<?php

namespace App\Controller;

use App\Model\Entity\User;
use Authentication\PasswordHasher\DefaultPasswordHasher;

class UsersController extends AppController
{
    public function add()
    {
        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $user = $usersTable->patchEntity($user, $data);
            $user->role = 'user';
            $user->is_banned = 0;

            if ($usersTable->save($user)) {
                $this->Flash->success('Account created. Try to log on');
                return $this->redirect(['action' => 'login']);
            }
            $this->Flash->error('ERROR: Failed to sign up');
        }
        $this->set(compact('user'));
    }

    public function login()
    {
        if ($this->request->is(['get','post'])){
            $authResult = $this->Authentication->getResult();
            $result=$this->Authentication->getResult();
            //
           // $email = trim($this->request->getData('email'));
           // $password = $this->request->getData('password');

           //Bugging issues due to change to auth plugin
            //echo "Valid ".($authResult->isValid() ? 'yes':'no');
            //echo "<br>Errors ".print_r($authResult->getErrors(),true);
            //wrong path in the -> 'unauthenticatedRedirect' => Router::url('/users/login'),  in Application.php

            //$usersTable = $this->fetchTable('Users');
            //$user = $usersTable->find()->where(['email' => $email])->first();

            if ($authResult->isValid()) {
                $user=$authResult->getData();

            if ($user) {
                if ($user->is_banned == 1) {
                    $this->Authentication->logout(); //need to test
                    $this->writeLog('warning', 'user_banned_login_attempt', 'User banned login attempt', ['user_id' => $user->id, 'email' => $user->email]);
                    $this->Flash->error("Unfortuantely you are banned. Please email us so we can explain further why");
                    return $this->redirect(['action' => 'login']);
                }

                    //$hasher = new DefaultPasswordHasher();
                    //if ($hasher->check($password, $user->password)) {
                    //    $session = $this->request->getSession();
                    //    $session->write('Auth', [
                    //        'id' => $user->id,
                    //        'email' => $user->email,
                    //        'first_name' => $user->first_name,
                    //        'last_name' => $user->last_name,
                    //        'role' => $user->role
                    //    ]);
//
                    $this->writeLog('info', 'user_login', 'User logged in', ['user_id' => $user->id, 'email' => $user->email]);

                    $this->Flash->success('Welcome back '.$user->first_name);
                    return $this->redirect(['controller' => 'Pets', 'action' => 'index']);
                }
                $this->writeLog('warning', 'user_login_failed', 'User login failed', ['user_id' => $user->id, 'email' => $user->email]);
            }

            $this->Flash->error('Invalid email or password');
        }
    }

    public function logout()
    {
        $user = $this->Authentication->getIdentity();// need to test
        if ($user) {
            $this->writeLog('info', 'user_logout', 'User logged out', ['user_id' => $user['id'], 'email' => $user['email']]);
        }
        $this->Authentication->logout();
        $this->Flash->success('Logged out');
        return $this->redirect(['action' => 'login']);
    }




    /* #region ADMIN Actions */

    public function index()
    {
        $result = $this->isLoggedIn();
        if ($result)
            return $result;

        $usersTable = $this->fetchTable('Users');
        $users = $usersTable->find()->orderBy(['created' => 'DESC'])->all();
        $this->set(compact('users'));
    }


    public function banUser($id = null)
    {
        $redirect = $this->isAdmin();
        if ($redirect) {
            return $redirect;
        }

        $this->request->allowMethod(['post']);
        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->get($id);

        //not to self ban by accident :)
        $myself = $this->Authentication->getIdentity(); // need to test
        if ($myself['id'] === $user->id) {
            $this->Flash->error('Dont banned yourself!!');
            return $this->redirect(['action' => 'index']);
        }

        $user->is_banned = 1;
        if ($usersTable->save($user)) {
            $this->Flash->success("User banned");
        } else {
            $this->Flash->error("ERROR: Banning user");
        }
        return $this->redirect(['action' => 'index']);
    }

    public function unBanUser($id = null)
    {
        $redirect = $this->isAdmin();
        if ($redirect) {
            return $redirect;
        }
        $this->request->allowMethod(['post']);
        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->get($id);
        $user->is_banned = 0;
        if ($usersTable->save($user)) {
            $this->Flash->success("User unbanned");
        } else {
            $this->Flash->error("ERROR: Unbanning user");
        }
        return $this->redirect(['action' => 'index']);
    }

    public function changeRole($id = null)
    {
        $redirect = $this->isAdmin();
        if ($redirect) {
            return $redirect;
        }

        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->get($id);

        $myself = $this->Authentication->getIdentity();
        if ($myself->id === $user->id) {
            $this->Flash->error('You cannot change your own role');
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is(['post', 'put'])) {
            $role = $this->request->getData('role');

            if (!in_array($role, ['user', 'admin'], true)) {
                $this->Flash->error('Invalid role');
                return $this->redirect(['action' => 'index']);
            }

            $user->role = $role;
            if ($usersTable->save($user)) {
                $this->Flash->success('Role updated');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('ERROR: Role failed to update');
        }
        $roles = ['user' => 'user', 'admin' => 'admin'];
        $this->set(compact('user', 'roles'));
    }
    /* #endregion */

}


