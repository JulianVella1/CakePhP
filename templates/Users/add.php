<h2>Register</h2>
<!--https://book.cakephp.org/5/en/views/helpers/form.html used to do the forms troughout-->
<?= $this->Form->create($user) ?>
<div class="mb-3"><?= $this->Form->control('first_name', ['class' => 'form-control']) ?></div>
<div class="mb-3"><?= $this->Form->control('last_name', ['class' => 'form-control']) ?></div>
<div class="mb-3"><?= $this->Form->control('email', ['class' => 'form-control']) ?></div>
<div class="mb-3"><?= $this->Form->control('password', ['type' => 'password', 'class' => 'form-control']) ?></div>
<div class="mb-3"><?= $this->Form->control('confirm_password', ['type' => 'password', 'class' => 'form-control']) ?></div>
<?= $this->Form->button('Register', ['class' => 'btn btn-primary']) ?>
<?= $this->Form->end() ?>
<hr>
<p>Already have an account?<?= $this->Html->link('Login', ['action' => 'login']) ?>
</p>
