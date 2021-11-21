# Roaming Editor
## Editeur de maraude pour tablette pour le Samu Social de Grenoble (AMICI).

### Presentation
- Le projet est composé en trois parties :
>- La partie "tablet" dédiée à la création du compte rendu sur la tablette durant la maraude.
>- La partie "portal" dédiée à fournir l'accès à ces comptes rendus à tous les membres de l'association.
>- La partie "webservice" dans le répertoir api utilisée par les deux autres parties pour le stockage en base de donnée
    des comptes rendus, la récupération/conversion vers et depuis les documents sur google.

### Installation
- Copier le repertoire conf_template en conf et particulariser les différents paramètres de configuration
- Renseigner la clé d'API google dans la page index.html
- Déposer les fichiers sur le serveur web/php
- Jouer le script api/sqlscripts/create.sql sur la base de donnée
- Modifier les permissions pour les dossier cr et api/tmp pour permettre au web serveur d'écrire dedans
- Interdire l'accès au dossier cr dans la configuration du serveur web (see extra/apache.conf)

### Outils externes
- [angular js](https://angularjs.org/) avec les modules angular-route et [angular-loading-bar](https://github.com/chieffancypants/angular-loading-bar).
- La librairie [mcx-dialog-mobile](https://github.com/code-mcx/mcx-dialog-mobile)
- maps.googleapis pour la recuperation d'une adresse à partir de la localisation GPS
- [PHPMailer](https://github.com/PHPMailer/PHPMailer) avec le module smtp

### Informations techniques
- Utilise le local storage pour éviter de perdre un CR en cas de plantage de la tablette
- Utilise un [Web App manifest](https://w3c.github.io/manifest/) pour être installé en tant qu'application android via chrome

### Informations pour le développement
- Pour lancer les tests d'intégrations :
    - faire un lien symbolique de conf vers conf_it
    - lancer le script "launchdevenv.sh" pour deployer le site dans un environnement docker
    - accéder à la page "http://localhost/api/tests".
- Pour mettre en place les hooks git sur le projet :
  ```bash
  ln -s -f ../../git-hooks/pre-commit .git/hooks/pre-commit
  ```
- Pour ne pas commiter la clé d'API google mais l'avoir dans le fichier index.html automatiquement,
  on peut définir les filtres git suivants (en remplacant bien sûr ${MY_API_KEY} par votre clé de l'API google) :
  ```bash
  git config filter.googleapikey.clean "sed 's/${MY_API_KEY}/GOOGLE_API_KEY/'"
  git config filter.googleapikey.smudge "sed 's/GOOGLE_API_KEY/${MY_API_KEY}/'"
  ```
- Pour éviter d'avoir des adresses email public, définir les filtres git suivants :
  ```bash
  export MY_NO_REPLY_EMAIL=""
  export MY_SECRETARIAT_EMAIL=""
  export MY_ADMIN_EMAIL=""
  export MY_SIGNALEMENTS_EMAIL=""
  git config filter.emails.clean "sed -e 's/${MY_NO_REPLY_EMAIL}/NO_REPLY_EMAIL/' -e 's/${MY_SECRETARIAT_EMAIL}/SECRETARIAT_EMAIL/' -e 's/${MY_ADMIN_EMAIL}/ADMIN_EMAIL/' -e 's/${MY_SIGNALEMENTS_EMAIL}/SIGNALEMENTS_EMAIL/'"
  git config filter.emails.smudge "sed -e 's/NO_REPLY_EMAIL/${MY_NO_REPLY_EMAIL}/' -e 's/SECRETARIAT_EMAIL/${MY_SECRETARIAT_EMAIL}/' -e 's/ADMIN_EMAIL/${MY_ADMIN_EMAIL}/' -e 's/SIGNALEMENTS_EMAIL/${MY_SIGNALEMENTS_EMAIL}/'"
  ```

