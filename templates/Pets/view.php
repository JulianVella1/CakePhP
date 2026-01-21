<h2><?= h($pet->name) ?></h2>

<p>
    <b>Type:</b> <?= h($pet->type) ?><br>
    <b>Owner:</b> <?= h($pet->user->first_name.' '.$pet->user->last_name) ?><br>
    <b>Created:</b> <?= $pet->created?>
</p>

<?php if (!empty($pet->image)): ?>
    <img src="<?= $this->Url->image('pets/' . $pet->image) ?>"
         class="img-thumbnail" style="max-width: 350px;">
<?php endif; ?>

<div class="mt-3">
    <?= $this->Html->link('Back to Home', ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
</div>
