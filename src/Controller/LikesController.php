<?php

namespace App\Controller;

use App\Model\Entity\User;

class LikesController extends AppController{

    public function toggle($petId=null){
        $redirect=$this->isLoggedIn();
        if($redirect){
            $this->writeLog('warning','like_toggle_blocked','User not logged in, un/like blocked',['pet_id'=>$petId]);
            return $redirect;
        }

        $this->request->allowMethod(['post']);
        //$user=$this->getUser();
        $user = $this->Authentication->getIdentity();
        $userId = $user->id;
        $likesTable=$this->fetchTable('Likes');

        $preliked=$likesTable->find()->where(['pet_id'=>$petId, 'user_id'=>$userId])->first();

        if($preliked){
            if($likesTable->delete($preliked)){
                $this->writeLog('info','pet_unliked','Pet unliked',['pet_id'=>$petId,'user_id'=>$userId]);
                $this->Flash->success('Unliked');
            }else{
                $this->writeLog('error','pet_unlike_failed','Failed unlike pet',['pet_id'=>$petId,'user_id'=>$userId]);
                $this->Flash->error('ERROR: Failed to unlike');
            }
        }else
        {
            $newLike=$likesTable->newEmptyEntity();
            $newLike->pet_id=$petId;
            $newLike->user_id=$userId;

            if($likesTable->save($newLike)) {
                $this->writeLog('info','pet_liked','Pet liked',['pet_id'=>$petId,'user_id'=>$userId]);
                $this->Flash->success('Liked');
            } else {
                $this->writeLog('error','pet_like_failed','Failed to like',['pet_id'=>$petId,'user_id'=>$userId]);
                $this->Flash->error('ERROR: Failed to like');
            }
        }
        return $this->redirect($this->referer(['controller'=>'Pets','action'=>'index']));
    }

}
