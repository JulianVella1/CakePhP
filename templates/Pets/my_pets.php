<h2>My Pets</h2>

<?php if ($pets->isEmpty()): ?>
    <div class="alert alert-info">You have not added any pets yet</div>
<?php else: ?>
    <ul class="list-group">
        <?php foreach ($pets as $p): ?>
            <li class="list-group-item">
                <?= $this->Html->link(
                    h($p->name),
                    '/pet/' . $p->url
                ) ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
