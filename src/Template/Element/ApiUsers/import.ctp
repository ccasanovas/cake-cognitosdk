<?php
use Cake\Core\Configure;
?>

<?php
$allowed_fields = $allowed_fields ?? [
    __d('Ccasanovas/CognitoSDK', 'Username'),
    __d('Ccasanovas/CognitoSDK', 'First Name'),
    __d('Ccasanovas/CognitoSDK', 'Last Name'),
    __d('Ccasanovas/CognitoSDK', 'Email')
];

$required_fields = $required_fields ?? [
    __d('Ccasanovas/CognitoSDK', 'Username'),
    __d('Ccasanovas/CognitoSDK', 'First Name'),
    __d('Ccasanovas/CognitoSDK', 'Last Name'),
    __d('Ccasanovas/CognitoSDK', 'Email')
];

$example_users = $example_users ?? [
    ['roberto.gonzalez', 'roberto_gonzalez@gmail.com', 'Roberto', 'Gonzalez'],
    ['pedro.pereyra', 'pedro_pereyra@gmail.com', 'Pedro', 'Pereyra Gomez'],
    ['carlos.chamorro', 'carlos_chamorro@gmail.com', 'Carlos', 'Chamorro'],
    ['maria.rodriguez', 'maria_rodriguez@gmail.com', 'Maria', 'Rodriguez'],
    ['eugenia.cruz', 'eugenia_cruz@gmail.com', 'Eugenia', 'Cruz'],
    ['rodrigo.castelli', 'rodrigo_castelli@gmail.com', 'Rodrigo', 'Castelli'],
];

$example_data = implode("\n", array_map(function($user){
    $fields = array_map(function($field){
        return "\"$field\"";
    }, array_values($user));
    return implode(', ', $fields);
}, $example_users));

?>

<div class="APIUsers-import container">
    <?= $this->Form->create(null, [
        'align' => 'default',
        'method' => 'post'
    ]) ?>
    <div class="card">
        <div class="header">
            <h3 class="title"><?= __d('Ccasanovas/CognitoSDK', 'Import API Users') ?></h3>
            <p><?= __d('Ccasanovas/CognitoSDK', 'You can add or edit API Users by uploading a CSV dataset.') ?></p>
        </div>
        <div class="content">
            <h4><?= __d('Ccasanovas/CognitoSDK', 'Importing Options') ?></h4>
                <ul>
                    <?= $this->Form->control('max_rows_allowed', [
                        'label' => __d('Ccasanovas/CognitoSDK', '{0} max rows allowed.',
                            Configure::read('ApiUsers.import_max_rows')),
                        'type' => 'checkbox',
                        'value' => 1,
                        'checked' => true,
                        'disabled' => true
                    ]) ?>
                    <?= $this->Form->control('max_errors', [
                        'label' => __d('Ccasanovas/CognitoSDK', 'Stop processing after {0} errors.',
                            Configure::read('ApiUsers.import_max_errors')),
                        'type' => 'checkbox',
                        'value' => 1,
                        'checked' => true,
                    ]) ?>
                </ul>
            <hr>
            <h4><?= __d('Ccasanovas/CognitoSDK', 'Instructions') ?></h4>
                <ul>
                    <li><?= __d('Ccasanovas/CognitoSDK', 'Each line represents one user only and it must contain all fields separated by a comma (,). All fields must be escaped using double quotes (").') ?></li>
                    <li><?= __d('Ccasanovas/CognitoSDK', 'Please ensure there are no quotes inside the field values that could break the data structure.') ?></li>
                    <li><?= __d('Ccasanovas/CognitoSDK', 'The fields are expected in the following order:') ?>
                        <?= $this->Text->toList(
                            array_map(function($f){ return "<strong>$f</strong>"; },
                            $allowed_fields)) ?>
                    </li>
                    <li><?= __d('Ccasanovas/CognitoSDK', 'For new users, the following fields cannot be empty:') ?>
                        <?= $this->Text->toList(
                            array_map(function($f){ return "<strong>$f</strong>"; },
                            $required_fields)) ?>
                    </li>
                    <li><?= __d('Ccasanovas/CognitoSDK', 'To edit users, the system will look up matching usernames.') ?></li>
                    <li><?= __d('Ccasanovas/CognitoSDK', 'You will be able to review the users before they are imported into the system.') ?></li>
                </ul>

            <br>
            <h4><?= __d('Ccasanovas/CognitoSDK', 'Example API User Data') ?></h4>
            <pre><?= $example_data ?></pre>
            <br>

            <h4><?= __d('Ccasanovas/CognitoSDK', 'Importing Data') ?></h4>
            <?= $this->Form->textarea('csv_data', [
                'label' => false,
                'rows' => '12',
            ]) ?>
        </div>
        <div class="footer">
            <?= $this->Form->submit(__d('Ccasanovas/CognitoSDK', 'Validate API User Data'), ['class' => 'btn btn-lg btn-primary']); ?>
        </div>
    </div>
    <?= $this->Form->end() ?>

</div>
