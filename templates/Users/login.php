<h2>Login</h2>

<?= $this->Form->create() ?>
<div class="mb-3"><?= $this->Form->control('email', ['class' => 'form-control']) ?></div>
<div class="mb-3"><?= $this->Form->control('password', ['type' => 'password', 'class' => 'form-control']) ?></div>
<?= $this->Form->button('Login', ['class' => 'btn btn-success']) ?>
<?= $this->Form->end() ?>
<hr>
<p>No account?<?= $this->Html->link('Register', ['action' => 'add']) ?>
</p>
