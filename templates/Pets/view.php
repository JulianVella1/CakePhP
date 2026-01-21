<?php
$isLoggedIn = !empty($authUser);
$isOwner = $isLoggedIn && ((int)$authUser['id'] === (int)$pet->user_id);
$isAdmin = $isLoggedIn && (($authUser['role'] ?? '') === 'admin');

$userLiked = false;
if ($isLoggedIn && !empty($pet->likes)) {
    foreach ($pet->likes as $like) {
        if ((int)$like->user_id === (int)$authUser['id']) {
            $userLiked = true;
            break;
        }
    }
}
?>

<h2><?= h($pet->name) ?></h2>

<p>
    <strong>Type:</strong> <?= h($pet->type) ?><br>
    <strong>Owner:</strong> <?= h($pet->user->first_name . ' ' . $pet->user->last_name)?><br>
    <!--https://book.cakephp.org/5/en/core-libraries/time.html
        This is the closest using cakephp format, the only way to get it to exact format is using php date function
        Will comment the cakephp way and leave the php way active

    <strong>Created:</strong> <?php // h($pet->created->i18nFormat(' dd MMMM yyyy HH:mm')) ?>

    https://stackoverflow.com/questions/77592196/formatting-php-date-function-to-properly-work-with-1st-2nd-3rd-4th-and-so
    -->
    <strong>Created:</strong> <?=h($pet->created->format('jS F Y H:i')) ?>
</p>

<?php if (!empty($pet->image)) : ?>
    <p>
        <img src="<?= $this->Url->image('pets/' . $pet->image) ?>" alt="<?= h($pet->name) ?>">
    </p>
<?php endif; ?>

<p>
    <strong>Likes:</strong> <?= count($pet->likes) ?>
</p>

<?php if (!empty($pet->likes)) : ?>
    <p><strong>Liked by:</strong></p>
    <ul>
        <?php foreach ($pet->likes as $like) : ?>
            <li><?= h($like->user->first_name . ' ' . $like->user->last_name) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<hr>

<p>
    <?php if ($isLoggedIn && !$isOwner) : ?>
        <?= $this->Form->postLink(
            $userLiked ? 'Unlike' : 'Like',
            ['controller' => 'Likes', 'action' => 'toggle', $pet->id]
        ) ?>
        |
    <?php endif; ?>

    <?php if ($isOwner) : ?>
        <?= $this->Html->link('Edit', ['action' => 'edit', $pet->id]) ?>
        |
    <?php endif; ?>

    <?php if ($isOwner || $isAdmin) : ?>
        <?= $this->Form->postLink(
            'Delete',
            ['action' => 'delete', $pet->id],
            ['confirm' => 'Are you sure?']
        ) ?>
        |
    <?php endif; ?>

    <?= $this->Html->link('Back to Home', ['action' => 'index']) ?>
</p>
<?php
