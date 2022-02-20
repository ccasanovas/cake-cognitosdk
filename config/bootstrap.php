<?php
use Cake\Core\Configure;

Configure::load('Ccasanovas/CognitoSDK.awscognito');
collection((array)Configure::read('CognitoSDK.config'))->each(function ($file) {
    Configure::load($file);
});
