<?php

namespace App\Controller;

use Cake\Routing\Router;

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

        if (!$this->request->is('post')) {
            return $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $data = (array)$this->request->getData();
        $idToken = $data['idToken'] ?? null;

        if (!$idToken) {
            return $this->jsonResponse(['error' => 'No token provided'], 400);
        }

        $firebaseUser = $data['user'] ?? null;
        if (!$firebaseUser) {
            return $this->jsonResponse(['error' => 'Invalid user data'], 400);
        }

        $user = $this->getOrCreateGoogleUser($firebaseUser);
        if (!$user) {
            return $this->jsonResponse(['error' => 'Failed to create user'], 500);
        }

        if ($user->is_banned == 1) {
            $this->writeLog('warning', 'user_banned_login_attempt', 'User banned (google auth)', ['user_id' => $user->id, 'user_email' => $user->email]);
            return $this->jsonResponse(['error' => 'Your have been banned, please contact support'], 403);
        }

        $this->writeLog('info', 'user_login_google', 'User logged in via Google', ['user_id' => $user->id, 'user_email' => $user->email]);
        $this->Authentication->setIdentity($user);

        $redirectUrl = Router::url(['controller' => 'Pets', 'action' => 'index'], true);

        return $this->jsonResponse(['success' => true, 'redirect' => $redirectUrl]);
    }

    private function getOrCreateGoogleUser(array $firebaseUser)
    {
        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->find()
            ->where(['email' => $firebaseUser['email']])
            ->first();

        if ($user) {
            if (!$user->google_id) {
                $user->google_id = $firebaseUser['uid'];
                $usersTable->save($user);
            }
            return $user;
        }

        $user = $usersTable->newEmptyEntity();
        $user->email = $firebaseUser['email'];
        $user->first_name = $firebaseUser['firstName'] ?? 'User';
        $user->last_name = $firebaseUser['lastName'] ?? '';
        $user->google_id = $firebaseUser['uid'];
        $user->role = 'user';
        $user->is_banned = 0;
        //small issue due to required passwird, it seems that auth plugins dont provide password so I have to generate a random one
        // https://www.php.net/manual/en/function.random-bytes.php
        //need to test - done
        $user->password = bin2hex(random_bytes(16));

        // Issue due to validation rules, skipping validation when user going trough auth
        //Used some AI to assist with the skip -> line below
        if (!$usersTable->save($user, ['checkRules' => true, 'validate' => false])) {
            return null;
        }

        return $user;
    }

    private function jsonResponse(array $body, int $status = 200)
    {
        return $this->response
            ->withType('application/json')
            ->withStatus($status)
            ->withStringBody(json_encode($body));
    }


    public function facebookCallback()
    {
        //really tried to do facebook but cannot get my account approved
        // https://developers.facebook.com/async/registration/dialog/?src=default
    }

}
