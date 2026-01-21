<?php

namespace App\Controller;

use Cake\Http\Response;

class OAuthController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->Authentication->allowUnauthenticatedActions(['googleCallback', 'facebookCallback']);
    }

    /**
     * Handle Google Firebase callback
     */
    public function googleCallback()
    {
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

            // Get user data from Firebase token (in real app, you'd verify this server-side)
            $firebaseUser = $data['user'] ?? null;

            if (!$firebaseUser) {
                 return $this->response
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode(['error' => 'Invalid user data']));
            }

            // Find or create user
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
                $user->password = bin2hex(random_bytes(16)); // Random password for OAuth users

                if (!$usersTable->save($user)) {
                    return $this->response
                        ->withType('application/json')
                        ->withStatus(500)
                        ->withStringBody(json_encode(['error' => 'Failed to create user']));
                }
            } else {
                // Update google_id if not set
                if (!$user->google_id) {
                    $user->google_id = $firebaseUser['uid'];
                    $usersTable->save($user);
                }
            }

            // Log the login
            $this->writeLog('info', 'user_login_google', 'User logged in via Google', ['user_id' => $user->id, 'email' => $user->email]);

            // Set session and redirect
            $this->Authentication->setIdentity($user);

            return $this->response
                ->withType('application/json')
                ->withStatus(200)
                ->withStringBody(json_encode(['success' => true, 'redirect' => '/pets']));
        }

        return $this->response
            ->withType('application/json')
            ->withStatus(405)
            ->withStringBody(json_encode(['error' => 'Method not allowed']));
    }

    /**
     * Handle Facebook callback
     */
    public function facebookCallback()
    {
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $facebookUser = $data['user'] ?? null;

            if (!$facebookUser) {
                 return $this->response
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode(['error' => 'Invalid user data']));
            }

            $usersTable = $this->fetchTable('Users');

            // Find or create user
            $user = $usersTable->find()
                ->where(['email' => $facebookUser['email']])
                ->first();

            if (!$user) {
                $user = $usersTable->newEmptyEntity();
                $user->email = $facebookUser['email'];
                $user->first_name = $facebookUser['firstName'] ?? 'User';
                $user->last_name = $facebookUser['lastName'] ?? '';
                $user->facebook_id = $facebookUser['uid'];
                $user->role = 'user';
                $user->is_banned = 0;
                $user->password = bin2hex(random_bytes(16)); // Random password for OAuth users

                if (!$usersTable->save($user)) {
                    return $this->response
                        ->withType('application/json')
                        ->withStatus(500)
                        ->withStringBody(json_encode(['error' => 'Failed to create user']));
                }
            } else {
                // Update facebook_id if not set
                if (!$user->facebook_id) {
                    $user->facebook_id = $facebookUser['uid'];
                    $usersTable->save($user);
                }
            }

            // Log the login
            $this->writeLog('info', 'user_login_facebook', 'User logged in via Facebook', ['user_id' => $user->id, 'email' => $user->email]);

            // Set session and redirect
            $this->Authentication->setIdentity($user);

            return $this->response
                ->withType('application/json')
                ->withStatus(200)
                ->withStringBody(json_encode(['success' => true, 'redirect' => '/pets']));
        }

        return $this->response
            ->withType('application/json')
            ->withStatus(405)
            ->withStringBody(json_encode(['error' => 'Method not allowed']));
    }
}
