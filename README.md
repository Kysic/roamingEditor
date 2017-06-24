# Roaming Editor
## Editeur de maraude pour tablette pour le Samu Social de Grenoble (VINCI).

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
- Interdire l'accès au dossier cr dans la configuration du serveur web

### Outils externes
- angular js avec le module angular-route
- maps.googleapis
- PHPMailer avec le module smtp

### Informations techniques
- Utilise le local storage pour éviter de perdre un CR en cas de plantage de la tablette
- Utilise un Web App manifest (https://w3c.github.io/manifest/) pour être installé en tant qu'application android via chrome

### Informations pour le développement
- Pour lancer les tests d'intégrations, faire un lien symbolique de conf vers conf_it, modifier la conf de la base
  de données puis accéder à la page "/api/tests".
- Pour mettre en place les hooks git :
  ```bash
  ln -s -f ../../git-hooks/pre-commit .git/hooks/pre-commit
  ```
- Pour ne pas commiter la clé d'API google mais l'avoir dans le fichier index.html automatiquement,
  on peut définir les filtres git suivants (en remplacant bien sûr ${MY_API_KEY} par votre clé de l'API google) :
  ```bash
  git config filter.googleapikey.clean "sed 's/${MY_API_KEY}/GOOGLE_API_KEY/'"
  git config filter.googleapikey.smudge "sed 's/GOOGLE_API_KEY/${MY_API_KEY}/'"
  ```

