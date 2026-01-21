<h2>Change Role</h2>
<p>
    <b>User:</b> <?= h($user->first_name . ' ' . $user->last_name) ?><br>
    <b>Email:</b> <?= h($user->email) ?>
</p>
<?= $this->Form->create($user) ?>
<div class="mb-3">
    <?= $this->Form->control('role', [
        'type' => 'select',
        'options' => $roles,
        'class' => 'form-select',
        'label' => 'Role'
    ]) ?>
</div>
<?= $this->Form->button('Save', ['class' => 'btn btn-primary']) ?>
<?= $this->Html->link('Back', ['action' => 'index'], ['class' => 'btn btn-secondary ms-2']) ?>
<?= $this->Form->end() ?>
