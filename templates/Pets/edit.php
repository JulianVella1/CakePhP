<h2>Edit Pet</h2>
<?= $this->Form->create($pet, ['type' => 'file']) ?>
<div class="mb-3"><?= $this->Form->control('name', ['class' => 'form-control']) ?></div>
<div class="mb-3"><?= $this->Form->control('type', ['class' => 'form-control']) ?></div>
<div class="mb-3"><?= $this->Form->control('image', ['type' => 'file','label' => 'New Image (optional)','class' => 'form-control']) ?></div>

<?php if (!empty($pet->image)): ?><p>Current image:</p><img src="<?= $this->Url->image('pets/' . $pet->image) ?>"style="max-width: 250px;" class="img-thumbnail"><?php endif; ?>

<div class="mt-3">
    <?= $this->Form->button('Save', ['class' => 'btn btn-primary']) ?>
    <?= $this->Html->link('Back', ['action' => 'index'], ['class' => 'btn btn-secondary ms-2']) ?>
</div>

<?= $this->Form->end() ?>
