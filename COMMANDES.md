# TP PKI & IAM - Guide des commandes

> Ce document contient **toutes les commandes à exécuter** dans l'ordre, étape par étape.
> Exécuter dans **PowerShell** avec Docker Desktop lancé.

---

## 📁 Structure des fichiers créés

```
compose/
├── dns/
│   ├── compose.yml          ← CoreDNS container
│   ├── Corefile              ← Configuration des zones DNS
│   └── db.tpiam.internal     ← Fichier de zone DNS
├── dockhand/
│   └── compose.yml           ← Interface gestion Docker
├── mailhog/
│   └── compose.yml           ← Intercepteur d'emails
├── caddy/
│   ├── compose.yml           ← Reverse proxy HTTPS
│   └── Caddyfile             ← Configuration des virtual hosts
├── dashy/
│   ├── compose.yml           ← Portail web
│   └── conf.yml              ← Configuration du portail
├── simpleapp/
│   ├── compose.yml           ← Container Flask
│   ├── Dockerfile            ← Build de l'image Python
│   ├── app.py                ← Application HTTP Headers viewer
│   └── requirements.txt      ← Dépendances Python
├── simplesaml/
│   └── compose.yml           ← Application SAML SP
├── stepca/
│   └── compose.yml           ← PKI StepCA (Smallstep)
└── keycloak/
    ├── compose.yml           ← IdP Keycloak + PostgreSQL
    └── .env                  ← Variables d'environnement
```

---

## Étape 0 : Prérequis Windows

### Vérifier que Docker fonctionne
```powershell
docker run hello-world
```
> Attendu : "Hello from Docker! This message shows that your installation appears to be working correctly."

### Ajouter les entrées DNS dans le fichier hosts Windows
> Puisqu'on est sur Windows (pas de VM Ubuntu), on utilise le fichier `hosts` au lieu de CoreDNS pour la résolution DNS côté client.

**Ouvrir PowerShell en Administrateur** et exécuter :
```powershell
Add-Content -Path C:\Windows\System32\drivers\etc\hosts -Value "`n# TP PKI & IAM`n127.0.0.1 portail.tpiam.internal`n127.0.0.1 dashy.tpiam.internal`n127.0.0.1 simpleapp.tpiam.internal`n127.0.0.1 simplesaml.tpiam.internal`n127.0.0.1 dockhand.tpiam.internal`n127.0.0.1 idp.tpiam.internal`n127.0.0.1 mail.tpiam.internal`n127.0.0.1 pki.tpiam.internal`n127.0.0.1 dns.tpiam.internal"
```

Vérifier :
```powershell
Get-Content C:\Windows\System32\drivers\etc\hosts | Select-String "tpiam"
```

---

## Étape 1 : Créer les réseaux Docker

```powershell
docker network create --driver=bridge --subnet=192.168.101.0/25 NetApp
docker network create --driver=bridge --subnet=192.168.101.128/25 NetAuth
docker network create --driver=bridge --subnet=192.168.102.128/25 NetAdmin
docker network create --driver=bridge --subnet=192.168.102.0/25 NetAccess
```

Vérifier :
```powershell
docker network ls
```
> Attendu : les 4 réseaux NetApp, NetAuth, NetAdmin, NetAccess doivent apparaître.

---

## Étape 2 : Lancer le DNS (CoreDNS)

```powershell
docker compose -f C:\Users\Mathis\Documents\dev\TP_PKI_IAM\compose\dns\compose.yml up -d
```

Vérifier :
```powershell
docker ps --filter name=coredns
```

Tester la résolution DNS :
```powershell
nslookup portail.tpiam.internal 127.0.0.1
nslookup dockhand.tpiam.internal 127.0.0.1
nslookup google.com 127.0.0.1
```

> ⚠️ **Si le port 53 est déjà utilisé** (service "Client DNS" de Windows), il faut le désactiver :
> ```powershell
> # PowerShell Administrateur
> Stop-Service -Name "Dnscache"
> Set-Service -Name "Dnscache" -StartupType Disabled
> ```
> Après le TP, le réactiver :
> ```powershell
> Set-Service -Name "Dnscache" -StartupType Automatic
> Start-Service -Name "Dnscache"
> ```

---

## Étape 3 : Lancer Dockhand

```powershell
docker compose -f C:\Users\Mathis\Documents\dev\TP_PKI_IAM\compose\dockhand\compose.yml up -d
```

Vérifier :
```powershell
docker ps --filter name=dockhand
```

Accéder à : **http://localhost:3001**

> Configuration initiale : Settings → tab Environments → créer un environnement avec **Unix Socket** comme type de connexion.

---

## Étape 4 : Lancer Mailhog

```powershell
docker compose -f C:\Users\Mathis\Documents\dev\TP_PKI_IAM\compose\mailhog\compose.yml up -d
```

Vérifier :
```powershell
docker ps --filter name=mailhog
```

> Web UI accessible à : **http://localhost:8025** (directement, avant Caddy)

---

## Étape 5 : Lancer Caddy (Reverse Proxy)

```powershell
docker compose -f C:\Users\Mathis\Documents\dev\TP_PKI_IAM\compose\caddy\compose.yml up -d
```

Vérifier :
```powershell
docker ps --filter name=RP-caddy
docker logs RP-caddy --tail 20
```

> ⚠️ Caddy va générer des certificats TLS auto-signés. Le navigateur affichera un avertissement — c'est normal, on ajoutera notre propre PKI plus tard.

---

## Étape 6 : Lancer Dashy (Portail)

```powershell
docker compose -f C:\Users\Mathis\Documents\dev\TP_PKI_IAM\compose\dashy\compose.yml up -d
```

Vérifier :
```powershell
docker ps --filter name=dashy
```

Accéder à : **https://portail.tpiam.internal** (via Caddy, accepter le certificat auto-signé)

---

## Étape 7 : Build + Lancer SimpleApp (Flask)

```powershell
docker compose -f C:\Users\Mathis\Documents\dev\TP_PKI_IAM\compose\simpleapp\compose.yml build
docker compose -f C:\Users\Mathis\Documents\dev\TP_PKI_IAM\compose\simpleapp\compose.yml up -d
```

Vérifier :
```powershell
docker ps --filter name=simpleapp
```

Accéder à : **https://simpleapp.tpiam.internal** (tous les HTTP headers s'affichent)

---

## Étape 8 : Lancer SimpleSAML

```powershell
docker compose -f C:\Users\Mathis\Documents\dev\TP_PKI_IAM\compose\simplesaml\compose.yml up -d
```

Vérifier :
```powershell
docker ps --filter name=simplesaml
```

> Pour le moment, seul l'URL `/health` sera accessible.

---

## 🔍 Point d'étape — Vérification globale

```powershell
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
```

| Container attendu | Réseau   | Port publié   |
|--------------------|----------|---------------|
| coredns            | NetAccess| 53:53         |
| dockhand           | NetAdmin | 3001:3000     |
| mailhog            | NetApp   | 1025, 8025    |
| RP-caddy           | Multi    | 80, 443       |
| dashy              | NetApp   | (via Caddy)   |
| simpleapp          | NetApp   | (via Caddy)   |
| simplesaml         | NetApp   | (via Caddy)   |

> ✅ Si tous les containers sont UP, **l'infrastructure de base est prête**.
> On peut maintenant passer à la PKI.

---

## Étape 9 : PKI — Lancer StepCA

⚠️ **Premier lancement en mode attaché** (pour voir les logs et le mot de passe JWK) :
```powershell
docker compose -f C:\Users\Mathis\Documents\dev\TP_PKI_IAM\compose\stepca\compose.yml up
```
> 📝 **NOTER le mot de passe du provisioner JWK** qui apparaît dans les logs !
> Puis `Ctrl+C` pour arrêter, et relancer en mode détaché :

```powershell
docker compose -f C:\Users\Mathis\Documents\dev\TP_PKI_IAM\compose\stepca\compose.yml up -d
```

### Vérifier les certificats (via la console Dockhand)
Ouvrir la console du container `step-ca` dans Dockhand et exécuter :
```bash
step certificate inspect $(step path)/certs/intermediate_ca.crt
step certificate inspect $(step path)/certs/root_ca.crt
```

### Changer le mot de passe de la clé intermédiaire
Dans la console du container `step-ca` :
```bash
step crypto change-pass $(step path)/secrets/intermediate_ca_key
```
> Saisir l'ancien mot de passe (celui noté précédemment), puis le nouveau.
> Mettre à jour le fichier `secrets/password` avec le nouveau mot de passe.

### Créer un certificat de test
```bash
step certificate create test.subject test.csr test.key --csr
step certificate inspect test.csr
```

---

## Étape 10 : PKI — Configurer ACME avec Caddy

### Récupérer le certificat root
Depuis la console du container `step-ca` :
```bash
cat $(step path)/certs/root_ca.crt
```
Copier le contenu et le sauvegarder dans :
```
C:\Users\Mathis\Documents\dev\TP_PKI_IAM\compose\caddy\root-ca.pem
```

### Modifier le compose.yml de Caddy
Ajouter le volume `root-ca.pem` — la modification sera faite dans le fichier directement.

### Modifier le Caddyfile
Remplacer `import tls-int-48h` par le bloc TLS ACME dans chaque host.

### Relancer Caddy
```powershell
docker compose -f C:\Users\Mathis\Documents\dev\TP_PKI_IAM\compose\caddy\compose.yml down
docker compose -f C:\Users\Mathis\Documents\dev\TP_PKI_IAM\compose\caddy\compose.yml up -d
```

### Vérifier
Dans le navigateur, vérifier que le certificat de https://portail.tpiam.internal est bien émis par **TPiam PKI**.

---

## Étape 11 : Lancer Keycloak (IdP)

```powershell
docker compose -f C:\Users\Mathis\Documents\dev\TP_PKI_IAM\compose\keycloak\compose.yml up -d
```

Vérifier :
```powershell
docker ps --filter name=keycloak
docker ps --filter name=postgres
```

Accéder à : **https://idp.tpiam.internal**
> Login admin : `admin` / `admin`

### Configuration du Realm
1. Créer un nouveau realm nommé **tpiam** (menu Manage Realm)
2. Realm Settings → tab General :
   - Signature algorithm SAML IdP metadata → **RSA_SHA256** minimum
3. Realm Settings → tab **Email** :
   - **Host** : `mailhog`
   - **Port** : `1025`
   - **From** : `keycloak@tpiam.internal`
   - **From Display Name** : `Keycloak TP IAM`
   - **Envelope From** : `keycloak@tpiam.internal`
   - Laisser **Enable SSL** et **Enable StartTLS** décochés (Mailhog ne supporte pas TLS)
   - Laisser **Enable Authentication** décoché
   - Cliquer **Save**
   - Cliquer **Test connection** → un message "Success" devrait apparaître
   - Aller sur **https://mail.tpiam.internal** (Mailhog) → vérifier qu'un email de test est bien arrivé dans la boîte de réception

4. Créer **3 utilisateurs** dans le menu **Users** (barre latérale gauche) :

   **Utilisateur 1 :**
   - Cliquer **Add user**
   - Username : `alice`
   - Email : `alice@tpiam.internal`
   - First Name : `Alice`
   - Last Name : `Dupont`
   - Email Verified : **ON**
   - Cliquer **Create**
   - Aller dans l'onglet **Credentials** → **Set password**
   - Saisir un mot de passe (ex: `Alice123!`)
   - **Temporary** : mettre à **OFF** (sinon l'utilisateur devra le changer à la première connexion)
   - Cliquer **Save** → **Save password**

   **Utilisateur 2 :**
   - Cliquer **Add user**
   - Username : `bob`
   - Email : `bob@tpiam.internal`
   - First Name : `Bob`
   - Last Name : `Martin`
   - Email Verified : **ON**
   - Cliquer **Create**
   - Onglet **Credentials** → **Set password** → `Bob12345!` → Temporary **OFF** → **Save**

   **Utilisateur 3 :**
   - Cliquer **Add user**
   - Username : `charlie`
   - Email : `charlie@tpiam.internal`
   - First Name : `Charlie`
   - Last Name : `Durand`
   - Email Verified : **ON**
   - Cliquer **Create**
   - Onglet **Credentials** → **Set password** → `Charlie1!` → Temporary **OFF** → **Save**

   > ⚠️ Bien vérifier que tu es dans le **realm tpiam** (pas master) en haut à gauche du menu.

---

## Étape 12 : SAML — Relier SimpleSAML à Keycloak

### Dans Keycloak (admin, realm tpiam) :
1. Menu **Clients** → **Create Client**
   - Client type : **SAML**
   - ClientID : `https://idp.tpiam.internal/realms/tpiam`
   - Name : SimpleSAML
   - Root URL : `https://simplesaml.tpiam.internal`
   - Valid Redirect URL : `/saml/acs`
   - Sign Documents & Assertions : ✅
   - Signature algorithm : **SHA256**
   - SAML signature key name : **keyID**
   - Canonicalization method : **exclusive**

---

## Étape 13 : OIDC — Relier Dockhand à Keycloak

### 13.1 — Créer le client OIDC dans Keycloak

1. Aller sur **https://idp.tpiam.internal** → se connecter en admin
2. Vérifier qu'on est dans le **realm tpiam** (sélecteur en haut à gauche)
3. Menu **Clients** (barre latérale) → **Create client**
4. **Étape 1 — General Settings** :
   - Client type : **OpenID Connect** (par défaut)
   - Client ID : `dockhand`
   - Name : `Dockhand`
   - Cliquer **Next**
5. **Étape 2 — Capability config** :
   - Client authentication : **ON** (cela active le mode "confidential" avec un secret)
   - Authorization : laisser OFF
   - Cocher : ✅ Standard flow, ✅ Direct access grants
   - Cliquer **Next**
6. **Étape 3 — Login settings** :
   - Root URL : `https://dockhand.tpiam.internal`
   - Home URL : `https://dockhand.tpiam.internal`
   - Valid redirect URIs : `https://dockhand.tpiam.internal/api/auth/oidc/callback`
   - Valid post logout redirect URIs : `https://dockhand.tpiam.internal`
   - Web origins : `https://dockhand.tpiam.internal`
   - Admin URL : `https://dockhand.tpiam.internal`
   - Cliquer **Save**

### 13.2 — Récupérer le Client Secret

1. Dans le client `dockhand` qu'on vient de créer, aller dans l'onglet **Credentials**
2. Copier le **Client secret** (bouton copier à droite) → **le noter quelque part**
   > Exemple : `aBcDeFgHiJkLmNoPqRsTuVwXyZ123456`

### 13.3 — Configurer Dockhand pour OIDC

Les fichiers ont déjà été modifiés :
- `compose.yml` : ajout de `NODE_EXTRA_CA_CERTS` + montage du certificat root CA
- `root_ca.crt` : copie du certificat root de StepCA

Relancer Dockhand :
```powershell
docker compose -f C:\Users\Mathis\Documents\dev\TP_PKI_IAM\compose\dockhand\compose.yml down
docker compose -f C:\Users\Mathis\Documents\dev\TP_PKI_IAM\compose\dockhand\compose.yml up -d
```

### 13.4 — Configurer OIDC dans l'interface Dockhand

1. Aller sur **https://dockhand.tpiam.internal**
2. Dans les **Settings** de Dockhand, chercher la section **Authentication / OIDC**
3. Remplir :
   - **Issuer URL** : `https://idp.tpiam.internal/realms/tpiam`
   - **Client ID** : `dockhand`
   - **Client Secret** : le secret copié à l'étape 13.2
4. Sauvegarder et tester la connexion avec un des utilisateurs créés (alice, bob, charlie)

### 13.5 — Vérifier

- Cliquer sur **Login with OIDC** (ou équivalent) dans Dockhand
- On est redirigé vers la page de login Keycloak
- Se connecter avec `alice` / `Alice123!`
- On est redirigé vers Dockhand, connecté en tant qu'Alice ✅

---

## Étape 14 : Supprimer le port direct de Dockhand

> ✅ **Déjà fait** : le port 3001 a été commenté dans le compose.yml de Dockhand à l'étape 13.
> Dockhand n'est désormais accessible que via le reverse proxy Caddy : **https://dockhand.tpiam.internal**

Vérifier que http://localhost:3001 ne répond plus :
```powershell
curl http://localhost:3001
```
> Attendu : erreur de connexion (le port n'est plus publié)

---

## Étape 15 : Password Policy

### 15.1 — Configurer la politique de mot de passe

1. Aller sur **https://idp.tpiam.internal** → realm **tpiam**
2. Menu **Authentication** (barre latérale)
3. Onglet **Policies** (en haut)
4. Sous-onglet **Password policy**
5. Cliquer sur **Add policy** et ajouter les règles suivantes :

   | Politique                    | Valeur recommandée |
   |-----------------------------|--------------------|
   | **Minimum Length**           | `8`                |
   | **Uppercase Characters**     | `1`                |
   | **Lowercase Characters**     | `1`                |
   | **Digits**                   | `1`                |
   | **Special Characters**       | `1`                |
   | **Not Recently Used**        | `3`                |

6. Cliquer **Save**

### 15.2 — Vérifier la politique

1. Menu **Users** → cliquer sur **alice**
2. Onglet **Credentials** → **Reset password**
3. Essayer un mot de passe faible : `abc` → Keycloak doit **refuser**
4. Essayer un mot de passe conforme : `Alice2026!` → Keycloak doit **accepter**

> ✅ La politique s'applique aussi quand les utilisateurs changent leur mot de passe eux-mêmes.

---

## Étape 16 : MFA / OTP

### 16.1 — Rendre l'OTP obligatoire

1. Aller sur **https://idp.tpiam.internal** → realm **tpiam**
2. Menu **Authentication** (barre latérale)
3. Onglet **Flows** (en haut)
4. Sélectionner le flux **browser** dans la liste
5. Trouver la ligne **Browser - Conditional OTP** (ou **OTP Form**)
6. Changer son requirement de **Conditional** à **Required**
7. Cliquer **Save** (si nécessaire)

> Cela signifie que **tous les utilisateurs** devront configurer un OTP à leur prochaine connexion.

### 16.2 — Tester avec un utilisateur

1. Ouvrir un **navigateur en mode privé** (pour ne pas être connecté en admin)
2. Aller sur **https://dockhand.tpiam.internal** (ou toute app reliée à Keycloak)
3. Cliquer sur Login → redirection vers Keycloak
4. Se connecter avec `bob` / `Bob12345!`
5. Keycloak affiche une page **"Configure OTP"** avec un **QR code**
6. Scanner le QR code avec une application d'authentification :
   - **Google Authenticator** (Android/iOS)
   - **Microsoft Authenticator** (Android/iOS)
   - **Authy** (Desktop/Mobile)
   - **FreeOTP** (open source)
7. L'app génère un **code à 6 chiffres** qui change toutes les 30 secondes
8. Saisir le code dans Keycloak → **Submit**
9. Bob est connecté ✅

### 16.3 — Vérifier le MFA aux connexions suivantes

1. Se déconnecter de Dockhand
2. Se reconnecter avec `bob` / `Bob12345!`
3. Keycloak demande maintenant le **code OTP** (2ème facteur)
4. Ouvrir l'app d'authentification → saisir le code à 6 chiffres
5. Connexion réussie ✅

> [!TIP]
> Pour rendre l'OTP optionnel au lieu d'obligatoire, remettre la ligne **OTP Form** sur **Conditional** au lieu de Required dans le flux browser.

---

## 🧹 Nettoyage (après le TP)

### Arrêter tous les containers
```powershell
$dirs = @("dns","dockhand","mailhog","caddy","dashy","simpleapp","simplesaml","stepca","keycloak")
foreach ($d in $dirs) {
    docker compose -f "C:\Users\Mathis\Documents\dev\TP_PKI_IAM\compose\$d\compose.yml" down
}
```

### Supprimer les réseaux
```powershell
docker network rm NetApp NetAuth NetAdmin NetAccess
```

### Restaurer le fichier hosts
Supprimer les lignes `tpiam.internal` de `C:\Windows\System32\drivers\etc\hosts`

### Réactiver le cache DNS Windows (si désactivé)
```powershell
Set-Service -Name "Dnscache" -StartupType Automatic
Start-Service -Name "Dnscache"
```
