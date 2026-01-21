<h2>User List</h2>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>First</th>
            <th>Last</th>
            <th>Email</th>
            <th>Role</th>
            <th>Banned</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $a): ?>
        <tr>
            <td><?= h($a->first_name) ?></td>
            <td><?= h($a->last_name) ?></td>
            <td><?= h($a->email) ?></td>
            <td><?= h($a->role) ?></td>
            <td><?= ((int)$a->is_banned === 1) ? 'Yes' : 'No' ?></td>
            <td>
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
                            ['class' => 'btn btn-sm btn-success ms-1', 'confirm' => 'Unban this user?']
                        ) ?>
                    <?php else: ?>
                        <?= $this->Form->postLink(
                            'Ban',
                            ['action' => 'banUser', $a->id],
                            ['class' => 'btn btn-sm btn-danger ms-1', 'confirm' => 'Ban this user?']
                        ) ?>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
