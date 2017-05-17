# Roaming Editor
## Editeur de maraude pour tablette pour le Samu Social de Grenoble (VINCI).

### Installation
- Configurer le webservice php pour pointer vers le planning google doc de l'asso en renommant le fichier
  config_template.php en config.php et en le complétant avec les références des documents et onglets google
  doc.
- Renseigner la clé d'API google dans la page index.html
- Ajouter le type mime suivant dans la configuration de votre serveur web:
```
   AddType text/cache-manifest .cache-manifest
```
- Déposer les fichiers sur votre serveur web+php

### Astuce
- Pour ne pas commiter la clé d'API google mais l'avoir dans le fichier index.html automatiquement,
  on peut définir les filtres git suivants (en remplacant bien sûr ${MY_API_KEY} par votre clé de l'API google) :
  ```bash
  git config --global filter.googleapikey.clean "sed 's/${MY_API_KEY}/GOOGLE_API_KEY/'"
  git config --global filter.googleapikey.smudge "sed 's/GOOGLE_API_KEY/${MY_API_KEY}/'"
  ```

### Informations diverses
- basé sur angular js
- utilise le local storage pour éviter de perdre un CR en cas de plantage de la tablette
- utilise un Web App manifest (https://w3c.github.io/manifest/) pour être installé en tant qu'application android via chrome
- utilise l'application cache pour rester fonctionnel en cas de perte de la connexion réseau
