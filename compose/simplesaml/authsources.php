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

        // Quel IdP utiliser
        'idp' => 'https://idp.tpiam.internal/realms/tpiam',

        // Certificat SP pour signer les AuthnRequests
        'privatekey'  => 'saml.pem',
        'certificate' => 'saml.crt',
    ],
];
