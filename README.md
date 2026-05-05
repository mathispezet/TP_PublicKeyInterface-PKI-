# TP PKI & IAM — Infrastructure d'authentification avec Docker

## 🎯 Objectif du TP

Ce TP met en œuvre une **infrastructure complète de PKI (Public Key Infrastructure)** et d'**IAM (Identity and Access Management)** à l'aide de conteneurs Docker. L'objectif est de comprendre et de pratiquer :

- La **gestion de certificats TLS** via une autorité de certification privée
- Le protocole **ACME** pour l'émission automatique de certificats
- L'**authentification centralisée** avec un Identity Provider (IdP)
- Les protocoles **SAML 2.0** et **OpenID Connect (OIDC)**
- La mise en place de **MFA (Multi-Factor Authentication)** avec OTP
- Les **politiques de mots de passe**

---

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        POSTE CLIENT (Windows)                    │
│                  Navigateur → *.tpiam.internal                   │
└──────────────────────────┬──────────────────────────────────────┘
                           │ HTTPS (port 443)
                           ▼
              ┌────────────────────────┐
              │   Caddy (Reverse Proxy) │ ← Certificats émis par StepCA
              │      RP-caddy          │       via ACME
              └──────┬───┬───┬───┬────┘
                     │   │   │   │
        ┌────────────┘   │   │   └──────────────┐
        ▼                ▼   ▼                  ▼
   ┌─────────┐    ┌──────────┐  ┌──────────┐  ┌──────────┐
   │  Dashy   │    │ SimpleApp │  │ Dockhand │  │ Mailhog  │
   │ (Portail)│    │  (Flask)  │  │ (Docker) │  │  (Mail)  │
   └─────────┘    └──────────┘  └────┬─────┘  └──────────┘
                                     │ OIDC
        ┌────────────────────────────┘
        ▼
   ┌──────────┐         ┌──────────┐
   │ Keycloak │────────→│ Postgres │
   │   (IdP)  │         │  (BDD)   │
   └────┬─────┘         └──────────┘
        │ SAML
        ▼
   ┌───────────┐
   │ SimpleSAML │
   │   (SP)     │
   └───────────┘

   ┌──────────┐
   │  StepCA   │ ← PKI (Autorité de Certification)
   │  (PKI)    │    Émet les certificats via ACME
   └──────────┘

   ┌──────────┐
   │ CoreDNS  │ ← Résolution DNS pour *.tpiam.internal
   └──────────┘
```

---

## 🧩 Rôle de chaque service

### 🔐 Infrastructure de sécurité

| Service | Image Docker | Rôle |
|---------|-------------|------|
| **StepCA** | `smallstep/step-ca` | **Autorité de Certification (CA)** privée. Émet des certificats TLS X.509 pour tous les services internes. Supporte le protocole ACME pour l'émission et le renouvellement automatique de certificats, comme Let's Encrypt mais pour une infrastructure privée. |
| **Keycloak** | `quay.io/keycloak/keycloak` | **Identity Provider (IdP)** et serveur SSO. Gère l'authentification centralisée des utilisateurs via les protocoles SAML 2.0 et OpenID Connect. Permet la fédération d'identité, les politiques de mots de passe et le MFA. |
| **PostgreSQL** | `postgres` | Base de données de Keycloak. Stocke les utilisateurs, sessions, clients, realms et configurations. Les mots de passe y sont hashés et salés. |

### 🌐 Infrastructure réseau

| Service | Image Docker | Rôle |
|---------|-------------|------|
| **Caddy** | `caddy` | **Reverse proxy** HTTPS. Point d'entrée unique pour tous les services. Termine le TLS et redirige les requêtes vers le bon conteneur en fonction du nom de domaine. Obtient automatiquement ses certificats auprès de StepCA via ACME. |
| **CoreDNS** | `coredns/coredns` | **Serveur DNS** interne. Résout les noms `*.tpiam.internal` vers l'infrastructure locale. Forwarde les autres requêtes DNS vers DNS4EU (86.54.11.1). |

### 📱 Applications

| Service | Image Docker | Rôle |
|---------|-------------|------|
| **Dashy** | `lissy93/dashy` | **Portail web** centralisant les liens vers tous les services. Accessible via `https://portail.tpiam.internal`. |
| **SimpleApp** | Build local (Flask) | **Application de debug** qui affiche tous les headers HTTP reçus. Utile pour observer les headers ajoutés par le reverse proxy et l'IdP (tokens, informations d'authentification). |
| **SimpleSAML** | `venatorfox/simplesamlphp` | **Service Provider SAML**. Application de test qui délègue son authentification à Keycloak via le protocole SAML 2.0. Permet de comprendre les assertions SAML. |
| **Dockhand** | `fnsys/dockhand` | **Interface de gestion Docker**. Permet de visualiser les conteneurs, accéder à leurs logs et ouvrir des consoles. Configuré avec authentification OIDC via Keycloak. |

### 📧 Outils

| Service | Image Docker | Rôle |
|---------|-------------|------|
| **Mailhog** | `mailhog/mailhog` | **Piège à emails** (SMTP sink). Intercepte tous les emails envoyés par Keycloak (vérification d'email, reset de mot de passe, etc.) sans les envoyer réellement. Interface web pour visualiser les emails reçus. |

---

## 🔗 URLs des services

| Service | URL | Accès |
|---------|-----|-------|
| Portail (Dashy) | `https://portail.tpiam.internal` | Public |
| SimpleApp | `https://simpleapp.tpiam.internal` | Public |
| Dockhand | `https://dockhand.tpiam.internal` | OIDC (Keycloak) |
| Mailhog | `https://mail.tpiam.internal` | Public |
| Keycloak (Admin) | `https://idp.tpiam.internal` | admin / admin |
| SimpleSAML | `https://simplesaml.tpiam.internal` | SAML (Keycloak) |
| StepCA (ACME) | `https://pki.tpiam.internal:9443` | API uniquement |

---

## 🔀 Réseaux Docker

L'infrastructure est segmentée en 4 réseaux isolés, simulant des zones de sécurité :

| Réseau | Subnet | Rôle | Services |
|--------|--------|------|----------|
| **NetAccess** | `192.168.102.0/25` | Zone d'accès client | CoreDNS |
| **NetApp** | `192.168.101.0/25` | Zone applicative | Dashy, SimpleApp, SimpleSAML, Mailhog |
| **NetAuth** | `192.168.101.128/25` | Zone d'authentification | Keycloak, PostgreSQL, StepCA, Mailhog |
| **NetAdmin** | `192.168.102.128/25` | Zone d'administration | Dockhand |

> **Caddy** est raccordé à NetApp, NetAuth et NetAdmin pour pouvoir atteindre tous les services en tant que reverse proxy.

---

## 🔄 Flux d'authentification

### SAML 2.0 (SimpleSAML)

```
Utilisateur → SimpleSAML → Redirection vers Keycloak
                                    ↓
                           Page de login Keycloak
                                    ↓
                           Assertion SAML signée
                                    ↓
                          Retour vers SimpleSAML
                                    ↓
                           Utilisateur connecté
```

### OpenID Connect (Dockhand)

```
Utilisateur → Dockhand → Redirection vers Keycloak
                                    ↓
                           Page de login Keycloak
                                    ↓
                         Authorization Code + ID Token
                                    ↓
                          Callback vers Dockhand
                                    ↓
                           Utilisateur connecté
```

---

## 📜 Concepts clés illustrés

### PKI (Public Key Infrastructure)
- **CA Root** : autorité racine, stockée hors ligne idéalement
- **CA Intermédiaire** : signe les certificats des services
- **ACME** : protocole d'émission automatique (HTTP-01 challenge)
- **Chaîne de confiance** : Root → Intermédiaire → Certificat serveur

### IAM (Identity and Access Management)
- **Realm** : espace d'identité isolé dans Keycloak
- **SSO (Single Sign-On)** : une seule authentification pour plusieurs apps
- **SAML 2.0** : protocole XML d'échange d'assertions d'identité
- **OIDC** : protocole moderne basé sur OAuth 2.0 et JWT
- **MFA** : authentification multi-facteurs avec OTP (TOTP)
- **Password Policy** : règles de complexité des mots de passe

---

## 📁 Structure du projet

```
TP_PKI_IAM/
├── README.md                    ← Ce fichier
├── COMMANDES.md                 ← Guide pas-à-pas avec toutes les commandes
├── Overview.md                  ← Énoncé original du TP
├── compose/
│   ├── dns/                     ← CoreDNS
│   │   ├── compose.yml
│   │   ├── Corefile
│   │   └── db.tpiam.internal
│   ├── caddy/                   ← Reverse proxy
│   │   ├── compose.yml
│   │   ├── Caddyfile
│   │   └── root-ca.pem
│   ├── dockhand/                ← Gestion Docker + OIDC
│   │   ├── compose.yml
│   │   └── root_ca.crt
│   ├── dashy/                   ← Portail
│   │   ├── compose.yml
│   │   └── conf.yml
│   ├── simpleapp/               ← App Flask (headers HTTP)
│   │   ├── compose.yml
│   │   ├── Dockerfile
│   │   ├── app.py
│   │   └── requirements.txt
│   ├── simplesaml/              ← SAML Service Provider
│   │   └── compose.yml
│   ├── mailhog/                 ← Intercepteur d'emails
│   │   └── compose.yml
│   ├── stepca/                  ← PKI / Autorité de certification
│   │   └── compose.yml
│   └── keycloak/                ← Identity Provider
│       ├── compose.yml
│       └── .env
└── client_secret.txt            ← Secret OIDC pour Dockhand
```

---

## ⚠️ Limitations (hors scope du TP)

Ce TP est à but pédagogique. En production, il faudrait ajouter :

- Un **firewall (FW)** et un **WAF** en frontal
- Un **bastion d'administration** pour l'accès aux services sensibles
- Des **images Docker hardenées** (pas de root, scan de vulnérabilités)
- Une **PKI avec CP et CPS** (Certificate Policy / Certification Practice Statement)
- La **clé root CA hors ligne** (HSM ou stockage sécurisé)
- Des **secrets gérés** (Vault, sealed secrets) au lieu de fichiers en clair
- Du **monitoring** et de la **journalisation centralisée**
- De la **haute disponibilité** pour les services critiques
