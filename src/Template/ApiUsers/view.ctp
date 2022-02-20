<?php
/**
  * @var \App\View\AppView $this
  */
use Cake\Utility\Hash;
?>
<div class="news-view container">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="toolbar">
                    <div class="pull-right">
                        <?= $this->Html->link('<i class="ion ion-edit"></i>', ['action' => 'edit', $api_user->id], [
                            'class' => 'btn btn-subtle',
                            'title' => __d('Ccasanovas/CognitoSDK', 'Edit API User'),
                            'data-toggle' => 'tooltip',
                            'escape' => false
                        ]) ?>
                        <?= $this->element('Ccasanovas/CognitoSDK.ApiUsers/contextual-menu', ['api_user' => $api_user]) ?>
                    </div>
                </div>
                <div class="header">
                    <div class="media">
                        <div class="media-left">
                            <?php $src = $api_user->avatar_url ?? '/evil_corp/aws_cognito/img/default-user.png' ?>
                            <?= $this->Html->image($src, [
                                'class' => 'media-object img-circle',
                                'style' => 'width: 72px; height: 72px;',
                                'url' => $src
                            ]); ?>
                        </div>
                        <div class="media-body">
                            <h3 class="title media-heading">
                            <?= $api_user->full_name ?>
                            </h3>
                            <h4 class="media-heading text-muted">
                                <?= $api_user->email ?>
                                <?php if(Hash::get($api_user, 'aws_cognito_attributes.email_verified')): ?>
                                    <span class="small" data-toggle="tooltip" data-placement="right" title="<?= __d('Ccasanovas/CognitoSDK', 'Email Verified') ?>">
                                        <i class="ion ion-checkmark-circled"></i>
                                    </span>
                                <?php else: ?>
                                <?php endif; ?>
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="content">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th><?= __d('Ccasanovas/CognitoSDK', 'Aws Cognito Username') ?></th>
                                <td colspan="2"><?= $api_user->aws_cognito_username ?></td>
                            </tr>
                            <tr>
                                <th><?= __d('Ccasanovas/CognitoSDK', 'Aws Cognito Synced') ?></th>
                                <td colspan="2">
                                    <?php if($api_user->aws_cognito_synced): ?>
                                    <span class="text-success">
                                        <?= __d('Ccasanovas/CognitoSDK', 'Yes') ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="text-danger">
                                        <?= __d('Ccasanovas/CognitoSDK', 'No') ?>
                                    </span>
                                    <div class="text-muted">
                                    <?= __d('Ccasanovas/CognitoSDK', 'This user is not synced with Aws Cognito. Please check in with a System Administrator to solve this issue.') ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?= __d('Ccasanovas/CognitoSDK', 'Active') ?></th>
                                <td>
                                    <?php if($api_user->active): ?>
                                    <span class="text-success">
                                        <?= __d('Ccasanovas/CognitoSDK', 'Yes') ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="text-danger">
                                        <?= __d('Ccasanovas/CognitoSDK', 'No') ?>
                                    </span>
                                    <?php endif; ?>
                                    <div class="text-muted">
                                    <?= __d('Ccasanovas/CognitoSDK', 'Only Active Users can Log In.') ?>
                                    </div>
                                </td>
                                <td class="text-right" style="vertical-align: middle;">
                                    <?php if($api_user->active): ?>
                                        <?= $this->Form->postLink(
                                            __d('Ccasanovas/CognitoSDK', 'Deactivate API User'),
                                            ['action' => 'deactivate', $api_user->id],
                                            ['class' => 'btn btn-default']
                                        ) ?>
                                    <?php else: ?>
                                        <?= $this->Form->postLink(
                                            __d('Ccasanovas/CognitoSDK', 'Activate API User'),
                                            ['action' => 'activate', $api_user->id],
                                            ['class' => 'btn btn-default']
                                        ) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?= __d('Ccasanovas/CognitoSDK', 'Status') ?></th>
                                <td colspan="2"><?= $api_user->aws_cognito_status['title'] ?>
                                    <div class="text-muted">
                                    <?= $api_user->aws_cognito_status['description'] ?>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="footer">
                    <?= $this->Html->link(
                        '<i class="ion ion-chevron-left"></i> &nbsp;' .
                        __d('Ccasanovas/CognitoSDK', 'Back to the API Users index'),
                        ['action' => 'index'],
                        ['escape' => false]
                    ) ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <h5 class="text-muted"><?= __d('Ccasanovas/CognitoSDK', 'Metadata') ?></h5>
            <p>
                <?= __d('Ccasanovas/CognitoSDK', 'Created') ?>:<br>
                <?php if($api_user->created_at): ?>
                    <?= $api_user->created_at->timeAgoInWords() ?>
                    <?php if($api_user->creator): ?>
                        <?= __d('Ccasanovas/CognitoSDK', 'by') ?> <?= $api_user->creator->full_name ?>
                    <?php endif; ?>
                <?php else: ?>
                    &nbsp;
                <?php endif; ?>
            </p>
            <p>
                <?= __d('Ccasanovas/CognitoSDK', 'Modified') ?>:<br>
                <?php if($api_user->modified_at): ?>
                    <?= $api_user->modified_at->timeAgoInWords() ?>
                    <?php if($api_user->modifier): ?>
                        <?= __d('Ccasanovas/CognitoSDK', 'by') ?> <?= $api_user->modifier->full_name ?>
                    <?php endif; ?>
                <?php else: ?>
                    &nbsp;
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>
