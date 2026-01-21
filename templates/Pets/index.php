<!-- Hero Section -->
<div class="bg-primary text-white p-4 rounded mb-4">
    <h1>Welcome to Purrfect</h1>
    <p class="mb-0">Find your perfect companion</p>
</div>

<?php if (!empty($authUser)): ?>
    <!-- Add Pet Form -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">Add new pet</h4>
        </div>
        <div class="card-body">
            <?= $this->Form->create($pet, ['type' => 'file']) ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3"><?= $this->Form->control('name', ['class' => 'form-control']) ?></div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3"><?= $this->Form->control('type', ['class' => 'form-control']) ?></div>
                </div>
            </div>
            <div class="mb-3">
                <?= $this->Form->control('upload', [
                    'type' => 'file',
                    'label' => 'Image',
                    'class' => 'form-control'
                ]) ?>
            </div>
            <?= $this->Form->button('Add Pet', ['class' => 'btn btn-success btn-lg']) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info text-center">
        <h5>Join our community!</h5>
        <p>Please <?= $this->Html->link('login', ['controller' => 'Users', 'action' => 'login'], ['class' => 'alert-link']) ?> to add a pet.</p>
    </div>
<?php endif; ?>

<!-- Pets Section -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Available Pets</h3>
    <span class="badge bg-secondary"><?= count($pets) ?> pets</span>
</div>
<div class="row row-cols-1 row-cols-md-3 g-4">
    <?php foreach ($pets as $p): ?>
        <div class="col">
            <div class="card h-100 shadow-sm">

                <?php if (!empty($p->image)): ?>
                    <img src="<?= $this->Url->image('pets/' . $p->image) ?>"
                         class="card-img-top" style="height: 200px; object-fit: cover;">
                <?php else: ?>
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        <span class="text-muted">No photo</span>
                    </div>
                <?php endif; ?>

                <div class="card-body">
                    <h5 class="card-title text-primary"><?= h($p->name) ?></h5>
                    <div class="mb-2">
                        <span class="badge bg-info"><?= h($p->type) ?></span>
                    </div>
                    <p class="card-text small text-muted">
                        <?= h($p->user->first_name . ' ' . $p->user->last_name) ?><br>
                        <?= $p->created->format('M j, Y') ?>
                    </p>

                    <?php $likeCount = !empty($p->likes) ? count($p->likes) : 0; ?>
                    <p class="mb-0">
                        <span class="badge bg-danger"> <?= $likeCount ?> likes</span>
                    </p>
                </div>

                <div class="card-footer bg-light">
                    <div class="d-flex flex-wrap gap-1">
                        <?= $this->Html->link(
                            'View',
                            ['_name' => 'pet:view', 'slug' => ($p->url ?: $p->id)],
                            ['class' => 'btn btn-sm btn-outline-primary']
                        ) ?>
                        <?php
                        $userLiked = false;
                        if (!empty($authUser) && !empty($p->likes)) {
                            foreach ($p->likes as $l) {
                                if ((int)$l->user_id === (int)$authUser['id']) {
                                    $userLiked = true;
                                    break;
                                }
                            }
                        }
                        ?>

                        <?php if (!empty($authUser)): ?>
                            <?= $this->Form->postLink(
                                $userLiked ? 'Unlike' : 'Like',
                                ['controller' => 'Likes', 'action' => 'toggle', $p->id],
                                ['class' => 'btn btn-sm btn-outline-danger', 'title' => $userLiked ? 'Unlike' : 'Like']
                            ) ?>

                            <?php if ($p->user_id === $authUser['id']): ?>
                                <?= $this->Html->link('Edit', ['action' => 'edit', $p->id], ['class' => 'btn btn-sm btn-outline-warning', 'title' => 'Edit']) ?>
                            <?php endif; ?>

                            <?php if ($p->user_id === $authUser['id'] || ($authUser['role'] ?? '') === 'admin'): ?>
                                <?= $this->Form->postLink(
                                    'Delete',
                                    ['action' => 'delete', $p->id],
                                    ['class' => 'btn btn-sm btn-outline-danger', 'confirm' => 'Are you sure?', 'title' => 'Delete']
                                ) ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
