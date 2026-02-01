<?php

namespace App\Controller;

use Cake\Http\Response;

class OAuthController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->Authentication->addUnauthenticatedActions(['googleCallback', 'facebookCallback']);
    }

    public function googleCallback()
    {
        $this->autoRender = false; // Disable view rendering

        try {
            if ($this->request->is('post')) {
                $data = $this->request->getData();
                $idToken = $data['idToken'] ?? null;

                if (!$idToken) {
                     return $this->response
                        ->withType('application/json')
                        ->withStatus(400)
                        ->withStringBody(json_encode(['error' => 'No token provided']));
                }

                $usersTable = $this->fetchTable('Users');
                $firebaseUser = $data['user'] ?? null;

                if (!$firebaseUser) {
                     return $this->response
                        ->withType('application/json')
                        ->withStatus(400)
                        ->withStringBody(json_encode(['error' => 'Invalid user data']));
                }
                $user = $usersTable->find()
                    ->where(['email' => $firebaseUser['email']])
                    ->first();

                if (!$user) {
                    $user = $usersTable->newEmptyEntity();
                    $user->email = $firebaseUser['email'];
                    $user->first_name = $firebaseUser['firstName'] ?? 'User';
                    $user->last_name = $firebaseUser['lastName'] ?? '';
                    $user->google_id = $firebaseUser['uid'];
                    $user->role = 'user';
                    $user->is_banned = 0;
                    //small issue due to required passwird, it seems that auth plugins dont provide password so I have to generate a random one
                    // https://www.php.net/manual/en/function.random-bytes.php
                    //need to test
                    $user->password = bin2hex(random_bytes(16));

                    // Issue due to validation rules, skipping validation when user going trough auth
                    //Used some AI to assist with the skip
                    if (!$usersTable->save($user, ['checkRules' => true, 'validate' => false])) {
                        return $this->response
                            ->withType('application/json')
                            ->withStatus(500)
                            ->withStringBody(json_encode(['error' => 'Failed to create user', 'details' => $user->getErrors()]));
                    }
                } else {
                    if (!$user->google_id) {
                        $user->google_id = $firebaseUser['uid'];
                        $usersTable->save($user);
                    }
                }

                $this->writeLog('info', 'user_login_google', 'User logged in via Google', ['user_id' => $user->id, 'email' => $user->email]);
                $this->Authentication->setIdentity($user);

                // Use Router to get correct redirect URL including subfolder
                $redirectUrl = \Cake\Routing\Router::url(['controller' => 'Pets', 'action' => 'index'], true);

                return $this->response
                    ->withType('application/json')
                    ->withStatus(200)
                    ->withStringBody(json_encode(['success' => true, 'redirect' => $redirectUrl]));
            }

            return $this->response
                ->withType('application/json')
                ->withStatus(405)
                ->withStringBody(json_encode(['error' => 'Method not allowed']));

        } catch (\Exception $e) {
            return $this->response
                ->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode(['error' => 'Server error', 'message' => $e->getMessage()]));
        }
    }


    public function facebookCallback()
    {
        //really tried to do facebook but cannot get my account approved
        // https://developers.facebook.com/async/registration/dialog/?src=default
    }

}
