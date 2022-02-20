<div class="dropdown">
    <button class="btn btn-subtle btn-narrow" id="dropdownApiUser-<?php $api_user->id ?>" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="glyphicon glyphicon-option-vertical"></span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownApiUser<?php $api_user->id ?>">

        <li><?= $this->Html->link(
            __d('Ccasanovas/CognitoSDK', 'View API User'),
            ['action' => 'view', $api_user->id])
            ?>
        </li>
        <li><?= $this->Html->link(
            __d('Ccasanovas/CognitoSDK', 'Edit API User'),
            ['action' => 'edit', $api_user->id])
            ?>
        </li>
        <li><?= $this->Html->link(
            __d('Ccasanovas/CognitoSDK', 'Change Email Address'),
            ['action' => 'changeEmail', $api_user->id])
            ?>
        </li>
        <?php if($api_user->active): ?>
        <li>
            <?= $this->Form->postLink(
                __d('Ccasanovas/CognitoSDK', 'Deactivate API User'),
                ['action' => 'deactivate', $api_user->id]
            ) ?>
        </li>
        <?php else: ?>
        <li>
            <?= $this->Form->postLink(
                __d('Ccasanovas/CognitoSDK', 'Activate API User'),
                ['action' => 'activate', $api_user->id]
            ) ?>
        </li>
        <?php endif; ?>
        <li class="divider"></li>
        <li>
            <?= $this->Form->postLink(
                __d('Ccasanovas/CognitoSDK', 'Delete API User'),
                ['action' => 'delete', $api_user->id],
                [
                    'confirm' => __d('Ccasanovas/CognitoSDK', 'Are you sure you want to delete the API User "{0}"?', $api_user->full_name)
                ]
            ) ?>
        </li>

    </ul>
</div>
