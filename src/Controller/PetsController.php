<?php

namespace App\Controller;

use Cake\Utility\Text;
use Cake\Utility\Inflector;
use Cake\View\JsonView;

class PetsController extends AppController
{

    public function index()
    {
        $petsTable = $this->fetchTable('Pets');
        $pet = $petsTable->newEmptyEntity();

        if ($this->request->is('post')) {
            $identity = $this->Authentication->getIdentity();

            if ($this->request->is('json')) {
                $userId = $identity ? $identity->id:1; //for api use only, to allow access
            } else {
                if (!$identity) {
                    $this->writeLog('warning','pets','User not logged in, adding pet blocked');
                    $this->Flash->error('Need to be logged in to add a pet');
                    return $this->redirect(['controller' => 'Users', 'action' => 'login']);
                }
                $userId = $identity->id;
            }

            $data = $this->request->getData();
            $data['user_id'] = $userId;
            $data['url'] = strtolower(Text::slug($data['name']));
            $data['image'] = $this->processImageUpload($this->request->getData('upload'));

            $pet = $petsTable->patchEntity($pet, $data);

            if ($petsTable->save($pet)) {
                //rest api - to test
                if ($this->request->is('json')) {
                    $this->set(compact('pet'));
                    $this->viewBuilder()->setOption('serialize', ['pet']);
                    return;
                }
                $this->writeLog('info','pet_added','New pet added',['pet_id'=>$pet->id,'pet_name'=>$pet->name,'user_id'=>$userId]);
                $this->Flash->success('Pet added');
                return $this->redirect(['action' => 'index']);
            }
            $this->writeLog('error','pet_add_failed','Failed to add pet',['user_id'=>$userId, 'errors'=>json_encode($pet->getErrors()), 'pet_name'=>$data['name']??'null']);
            $this->Flash->error('ERROR: Failed to add pet');
        }
        $pets = $petsTable->find()
            ->contain(['Users', 'Likes' => ['Users']])
            ->orderBy(['Pets.created' => 'DESC'])
            ->all();
        if ($this->request->is('json')) {
            $this->set('pets', $pets);
            $this->viewBuilder()->setOption('serialize', ['pets']);
            return;
        }
        $this->set(compact('pet', 'pets'));
    }

    public function add()
    {
        $this->request->allowMethod(['post']);
        $petsTable = $this->fetchTable('Pets');
        $pet = $petsTable->newEmptyEntity();

        $data = $this->request->getData();
        $data['user_id'] = $data['user_id'] ?? 1;
        $data['url'] = strtolower(Text::slug($data['name'] ?? ''));
        $data['image'] = $data['image'] ?? '';

        $pet = $petsTable->patchEntity($pet, $data);

        if ($petsTable->save($pet)) {
            $this->set(compact('pet'));
            $this->viewBuilder()->setOption('serialize', ['pet']);
            return;
        }

        $this->set(['errors' => $pet->getErrors()]);
        $this->viewBuilder()->setOption('serialize', ['errors']);
    }

    public function edit($id = null)
    {

        $redirect = $this->isLoggedIn();
        if ($redirect) {
            return $redirect;
        }

        $petsTable = $this->fetchTable('Pets');
        $pet = $petsTable->get($id);
        $user = $this->Authentication->getIdentity();
        if ($pet->user_id !== $user->id) {
            $this->Flash->error("You are not allowed to edit this pet");
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $data = $this->request->getData();
            $data['url'] = strtolower(Text::slug($data['name']));
            $data['image'] = $this->processImageUpload($this->request->getData('upload'), $pet->image);

            $pet = $petsTable->patchEntity($pet, $data);
            if ($petsTable->save($pet)) {
                $this->Flash->success("Pet updated");
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error("ERROR: Updating pet");
        }
        $this->set(compact('pet'));
    }

    //Used AI on this as I was having trouble with file uploads
    protected function processImageUpload($file, ?string $currentImage = null): ?string
    {
        if (!$file || !$file->getSize()) {
            return $currentImage;
        }

        $ext = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowed, true)) {
            $this->Flash->error('Only jpg, jpeg, png, or webp images allowed.');
            return $currentImage;
        }

        $newName = Text::uuid() . '.' . $ext;
        $target = WWW_ROOT . 'img' . DS . 'pets' . DS . $newName;

        if (!is_dir(WWW_ROOT . 'img' . DS . 'pets')) {
            mkdir(WWW_ROOT . 'img' . DS . 'pets', 0775, true);
        }

        $file->moveTo($target);
        return $newName;
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $petsTable = $this->fetchTable('Pets');
        $pet = $petsTable->get($id);

        if ($this->request->is('json')) {//API USE ONLY
            $success = $petsTable->delete($pet);
            $message = $success ? 'Deleted' : 'Error';
            $this->set('message', $message);
            $this->viewBuilder()->setOption('serialize', ['message']);
            return;
        }

        $user = $this->Authentication->getIdentity();
        if (!$user) {
            $this->Flash->error('You need to be logged in...');
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        if ($pet->user_id !== $user->id && $user->role !== 'admin') {
            $this->Flash->error('You can only delete your own pets');
            return $this->redirect(['action' => 'index']);
        }

        if ($petsTable->delete($pet)) {
            $this->Flash->success("Pet deleted");
        } else {
            $this->Flash->error("ERROR: Deleting pet");
        }

        return $this->redirect(['action' => 'index']);
    }

    public function view($slug = null)
    {

        $petsTable = $this->fetchTable('Pets');
        $pet = $petsTable->find()
            ->where(['Pets.url' => $slug])
            ->contain(['Users'])
            ->first();

        if (!$pet) {
            $this->Flash->error("Pet not found");
            return $this->redirect(['action' => 'index']);
        }
        $this->set(compact('pet'));


    }

    public function myPets()
    {
        $redirect = $this->isLoggedIn();
        if ($redirect) {
            return $redirect;
        }

        //$user = $this->getUser();

        //issue with my pets, null value but in db everything seems to be filled correctly
        //print_r($user);
        $user = $this->Authentication->getIdentity();


        $petsTable = $this->fetchTable('Pets');
        $pets = $petsTable->find()
            ->where(['user_id' => $user->id])
            ->orderBy(['created' => 'DESC'])
            ->all();

        $this->set(compact('pets'));
    }

    //only for Rest API
    public function getUsersPets($id=null){
        $this->request->allowMethod(['get']);
        $petsTable = $this->fetchTable('Pets');
        $pets = $petsTable->find()
            ->where(['Pets.user_id' => $id])
            ->contain(['Users'])
            ->orderBy(['Pets.created' => 'DESC'])
            ->all();
        $this->set(['pets' => $pets, 'user_id'=>$id]);
        $this->viewBuilder()->setOption('serialize', ['pets','user_id']);

    }

    //used this page from cookbook -> https://book.cakephp.org/5/en/development/rest.html
    public function viewClasses(): array
    {
        return [JsonView::class];
    }
}
