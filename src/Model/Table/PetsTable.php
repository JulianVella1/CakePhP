<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Pets Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\LikesTable&\Cake\ORM\Association\HasMany $Likes
 *
 * @method \App\Model\Entity\Pet newEmptyEntity()
 * @method \App\Model\Entity\Pet newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Pet> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Pet get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Pet findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Pet patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Pet> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Pet|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Pet saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Pet>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Pet>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Pet>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Pet> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Pet>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Pet>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Pet>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Pet> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class PetsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('pets');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('Likes', [
            'foreignKey' => 'pet_id',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('name')
            ->maxLength('name', 50)
            ->minLength('name', 2, 'Name to short, min if 2 chars')
            ->requirePresence('name', 'create')
            ->notEmptyString('name')
            ->add('name', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('url')
            ->maxLength('url', 255)
            ->requirePresence('url', 'create')
            ->notEmptyString('url')
            ->add('url', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('type')
            ->maxLength('type', 100)
            ->requirePresence('type', 'create')
            ->notEmptyString('type');

        $validator
            ->uploadedFile('upload', [
                'optional' => true
            ], 'Please upload a valid image file')
            ->notEmptyFile('upload', 'Please upload an image file', 'create')
            ->allowEmptyFile('upload', 'update');


        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['name']), ['errorField' => 'name']);
        $rules->add($rules->isUnique(['url']), ['errorField' => 'url']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
