<?php
/**
 * SAML 2.0 remote IdP metadata — Keycloak
 * Généré depuis : https://idp.tpiam.internal/realms/tpiam/protocol/saml/descriptor
 */

$metadata['https://idp.tpiam.internal/realms/tpiam'] = [
    'entityid' => 'https://idp.tpiam.internal/realms/tpiam',
    'name'     => ['en' => 'Keycloak tpiam'],

    'sign.authnrequest' => true,

    // Endpoint SSO (HTTP-Redirect)
    'SingleSignOnService'         => 'https://idp.tpiam.internal/realms/tpiam/protocol/saml',
    'SingleSignOnService.binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',

    // Endpoint SLO (HTTP-Redirect)
    'SingleLogoutService'         => 'https://idp.tpiam.internal/realms/tpiam/protocol/saml',
    'SingleLogoutService.binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',

    // Certificat de signature Keycloak (extrait du descriptor)
    'certData' => 'MIICmTCCAYECBgGd93ksITANBgkqhkiG9w0BAQsFADAQMQ4wDAYDVQQDDAV0cGlhbTAeFw0yNjA1MDUwOTI4MjZaFw0zNjA1MDUwOTMwMDZaMBAxDjAMBgNVBAMMBXRwaWFtMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAkRUrg/TlYcSRmsvg3SYhzo6kgYH+MNyWJvAqjwlX/biM3/NahWZL+3equzlN8GOlxBfvLmrdyMkMFZFyVKXJeGf4bh0jqdGDry0QBHql59zQngRUC8JIqGSinaXXDMMR7fcXRxqIIVq06Kg3iO9ZkD9m7JkteNVCxCi+7n19NGU/y6S8b/XzI6OSpKxcsLcI82C5DSp7DaaQ52l5aifEuwJ5XvFhclvSU8jTJc2sqmgxHXFr72B0ABKHdRPqy16sowOQz8JGsAgtPfQNCOPhdYe2o+0uWeos3ggqV92d5Q2a+q6sPCQ39Bid0TIH4Ufmdjejn/eyx6i+0BXgWrqTfwIDAQABMA0GCSqGSIb3DQEBCwUAA4IBAQBLCsD/bnk3V5SC2RkDDT79KNb+JlkiAgm6NJ7AVZvIZeiabFFPy3iy0iThgrXqTVraVE5PyzUYK5ievNp6+YniS5o3937rQjv0yr8qXutXuyk89NJP9ycaaJjSlbpdOkxCKcvWV43fU9X5ExRt9VR23xZq8Cg4sT+HRyUXSQab3TUVImW2JGJBQA9ZLy6tdVxiJJQBObvWsspn47uIwooDilLnSk4NoDCLERxfPr55qqZf9qSzxrxtSLqqd/x3GjYZvpOIEpm8Oueiqk84rMpYb2V7T1Xk4p0fK4MiYo0XuYhOau7II2c6a9MBhthqB3pjEIiPD6yaImUPYIlTUFXf',
];
