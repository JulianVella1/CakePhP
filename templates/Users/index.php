<h2>User List</h2>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <?php foreach ($users as $a): ?>
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="card-title"><?= h($a->first_name . ' ' . $a->last_name) ?></h5>
                            <p class="card-text mb-1">
                                <strong>Email:</strong> <?= h($a->email) ?><br>
                                <strong>Role:</strong> <span class="badge bg-info"><?= h($a->role) ?></span><br>
                                <strong>Status:</strong>
                                <?php if ((int)$a->is_banned === 1): ?>
                                    <span class="badge bg-danger">Banned</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Active</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6 text-end d-flex align-items-center justify-content-end gap-2">
                            <?php if (!empty($authUser) && $authUser->role === 'admin'): ?>
                                <?= $this->Html->link(
                                    'Change Role',
                                    ['action' => 'changeRole', $a->id],
                                    ['class' => 'btn btn-sm btn-warning']
                                ) ?>

                                <?php if ($a->is_banned === 1): ?>
                                    <?= $this->Form->postLink(
                                        'Unban',
                                        ['action' => 'unBanUser', $a->id],
                                        ['class' => 'btn btn-sm btn-success', 'confirm' => 'Unban this user?']
                                    ) ?>
                                <?php else: ?>
                                    <?= $this->Form->postLink(
                                        'Ban',
                                        ['action' => 'banUser', $a->id],
                                        ['class' => 'btn btn-sm btn-danger', 'confirm' => 'Ban this user?']
                                    ) ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
