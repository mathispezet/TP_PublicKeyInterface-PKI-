<?php
$config = [
    'admin' => [
        'core:AdminPassword',
    ],

    // SP par défaut, relié à Keycloak
    'default-sp' => [
        'saml:SP',

        // EntityID du SP (SimpleSAML lui-même)
        'entityID' => 'https://simplesaml.tpiam.internal',

        // Quel IdP utiliser (doit correspondre à la clé dans saml20-idp-remote.php)
        'idp' => 'https://idp.tpiam.internal/realms/tpiam',

        // URLs de callback
        'AssertionConsumerService' => 'https://simplesaml.tpiam.internal/saml/acs',
        'SingleLogoutService' => 'https://simplesaml.tpiam.internal/saml/sls',
    ],
];
