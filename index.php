<?php
$username = '';
try {
  require_once('api/lib/Container.php');
  $container = new Container();
  $session = $container->getSession();
  if ($session->isLoggedIn()) {
    if (@$_POST['action'] == 'logout') {
      $auth = $container->getAuth();
      $auth->logout(@$_POST['sessionToken']);
    } else {
      $username = $session->getUser()->username;
    }
  }
} catch (Exception $e) {
    // Nothing
}
?>
<!doctype html>
<html lang="fr">
<!-- Roaming Editor - License GNU GPL - https://github.com/Kysic/roamingEditor -->
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AMICI - Accueil</title>
  <link rel="canonical" href="https://benevoles.amici-samu-social.fr/" />
  <link rel="stylesheet" href="material.teal-blue.min.css">
  <link rel="manifest" href="/manifest.json">
  <link rel="icon" type="image/svg+xml" sizes="any" href="/favicon.svg"/>
  <link rel="alternate icon" type="image/png" sizes="512x512" href="/favicon.png">
  <link rel="alternate icon" type="image/x-icon" href="/favicon.ico">
  <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
  <script defer src="material.min.js" type="text/javascript"></script>
<style>
.mdl-layout {
  min-width: 350px;
}
.main-content {
  display: flex;
  flex-flow: row wrap;
  padding: 20px;
  justify-content: center;
}
.mdl-menu__item a {
  font-weight: unset;
  text-decoration: none;
  color: unset;
}
.mdl-card {
  width: 450px;
  margin: 15px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.mdl-card__title-text {
  margin-right: 30px;
}
.mdl-card__title-text a {
  color: #000;
  text-decoration: none;
  font-weight: normal;
  cursor: pointer;
}
.mdl-card__title-text i.material-icons {
  color: #555;
  margin-right: 10px;
}
.mdl-card__supporting-text {
  padding: 0 15px 10px 15px;
  text-align: justify;
  text-justify: inter-word;
  width: unset;
}
.mdl-card__supporting-text a i.material-icons {
  color: #000;
}
.video {
  color: #555;
}
#modal-background {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: #000;
  opacity: 75%;
  z-index: 10;
}
#modal-content {
  display: none;
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50% , -50%);
  max-width: 90%;
  z-index: 11;
}
</style>
<script type="text/javascript">
function openYoutube(videoUrl) {
 document.getElementById('youtubePlayer').src = videoUrl;
 var height = 600;
 if (window.innerHeight < 600) {
    height = window.innerHeight - 50;
 }
 var width = Math.floor(height*800/600);
 document.getElementById('modal-content').style.width = width + 'px';
 document.getElementById('modal-content').style.height = height + 'px';
 document.getElementById('youtubePlayer').height = height + 'px';
 document.getElementById('youtubePlayer').width = width + 'px';

 document.getElementById('modal-background').style.display = 'initial';
 document.getElementById('modal-content').style.display = 'initial';
}
function closeModal() {
 document.getElementById('modal-background').style.display = 'none';
 document.getElementById('modal-content').style.display = 'none';
}
document.onkeydown = function(evt) {
    evt = evt || window.event;
    var isEscape = false;
    if ("key" in evt) {
        isEscape = (evt.key === "Escape" || evt.key === "Esc");
    } else {
        isEscape = (evt.keyCode === 27);
    }
    if (isEscape) {
        closeModal();
    }
};
function post(path, params, method) {
  method = method || 'post';
  const form = document.createElement('form');
  form.method = method;
  form.action = path;
  for (let key in params) {
    if (params.hasOwnProperty(key)) {
      const hiddenField = document.createElement('input');
      hiddenField.type = 'hidden';
      hiddenField.name = key;
      hiddenField.value = params[key];
      form.appendChild(hiddenField);
    }
  }
  document.body.appendChild(form);
  form.submit();
}
function logout() {
  post('/', { action: 'logout', sessionToken: '<?php echo $session->getToken();?>' });
}
</script>
</head>
<body>

<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
  <header class="mdl-layout__header">
    <div class="mdl-layout__header-row">
      <span class="mdl-layout-title mdl-layout--small-screen-only">Accueil</span>
      <span class="mdl-layout-title mdl-layout--large-screen-only">Outils informatique d'AMICI</span>
      <div class="mdl-layout-spacer"></div>
<?php
if ($username) {
?>
      <div id="user-menu-button">
        <span><?php echo $username;?></span>
        <button class="mdl-button mdl-js-button mdl-button--icon">
          <i class="material-icons">person</i>
        </button>
      </div>
      <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="user-menu-button">
        <li class="mdl-menu__item"><a href="/portal/#!/setPassword">Changer de mot de passe</a></li>
        <li class="mdl-menu__item" onclick="logout()">Se déconnecter</li>
      </ul>
<?php
} else {
  echo '<a href="/portal/#!/login//site:'.basename(__FILE__).'" class="mdl-navigation__link">Se connecter <i class="material-icons">person</i></a>';
}
?>
    </div>
  </header>
  <div class="mdl-layout__drawer">
    <span class="mdl-layout-title">Navigation</span>
    <nav class="mdl-navigation">
      <a class="mdl-navigation__link" href="/"><i class="material-icons">home</i> Accueil</a>
      <a class="mdl-navigation__link" href="/portal/#!/roamingsList/"><i class="material-icons">assignment</i> Compte-rendus</a>
      <a class="mdl-navigation__link" href="/redirectionPlanning.php"><i class="material-icons">date_range</i> Planning maraudes</a>
      <a class="mdl-navigation__link" href="/meetings.php"><i class="material-icons">event</i> Calendrier réunions</a>
      <a class="mdl-navigation__link" href="/dokuwiki/"><i class="material-icons">school</i> Base connaissances</a>
      <a class="mdl-navigation__link" href="/portal/#!/users"><i class="material-icons">contacts </i> Liste des membres</a>
      <a class="mdl-navigation__link" href="https://samu-social-grenoble.fr"><i class="material-icons">web</i> Site Web</a>
      <a class="mdl-navigation__link" href="https://www.facebook.com/AMICISamuSocialGrenoble"><i class="material-icons">face</i> Page Facebook</a>
      <a class="mdl-navigation__link" href="/contactForm.php"><i class="material-icons">live_help</i> Aide</a>
    </nav>
  </div>
  <main class="mdl-layout__content"><div class="main-content">

    <div class="mdl-card mdl-shadow--2dp">
      <div class="mdl-card__title">
        <h2 class="mdl-card__title-text"><a href="/portal/#!/register"><i class="material-icons">how_to_reg</i> Inscription au site</a></h2>
      </div>
      <div class="mdl-card__supporting-text">
        Pour vous inscrire sur le site, rendez-vous sur <a href="/portal/#!/register">cette page</a> et
        suivez les instructions.<br>
        Pour retrouver facilement ce site, pensez à l'ajouter à vos favoris ou votre écran d'accueil.
      </div>
      <div class="mdl-card__actions mdl-card--border">
        <a class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" href="/portal/#!/register">
          S'inscrire sur le site
        </a>
      </div>
      <div class="mdl-card__menu">
        <button class="mdl-button mdl-button--icon mdl-js-button mdl-js-ripple-effect" title="Voir la vidéo tutoriel" onClick="openYoutube('https://www.youtube.com/embed/LXNPehoScsg')">
          <i class="material-icons video">help_center</i>
        </button>
      </div>
    </div>

    <div class="mdl-card mdl-shadow--2dp">
      <div class="mdl-card__title">
        <h2 class="mdl-card__title-text"><a href="/portal/#!/roamingsList/"><i class="material-icons">assignment</i> Compte-rendus de maraudes</a></h2>
      </div>
      <div class="mdl-card__supporting-text">
        Pour consulter les compte-rendus des maraudes, rendez-vous sur <a href="/portal/#!/roamingsList/">cette page</a>
        et cliquer sur le lien dans la colonne compte-rendu.<br>
        Pour les tuteurs, vous pourrez aussi modifier le compte-rendu de la veille pour compléter ou corriger des infos.
      </div>
      <div class="mdl-card__actions mdl-card--border">
        <a class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" href="/portal/#!/roamingsList/">
          Consulter les compte-rendus
        </a>
      </div>
      <div class="mdl-card__menu">
        <button class="mdl-button mdl-button--icon mdl-js-button mdl-js-ripple-effect" title="Voir la vidéo tutoriel" onClick="openYoutube('https://www.youtube.com/embed/A9UIb97j9dw')">
          <i class="material-icons video">help_center</i>
        </button>
      </div>
    </div>

    <div class="mdl-card mdl-shadow--2dp">
      <div class="mdl-card__title">
        <h2 class="mdl-card__title-text"><a href="/redirectionPlanning.php"><i class="material-icons">date_range</i> Planning maraudes et soupes</a></h2>
      </div>
      <div class="mdl-card__supporting-text">
        Pour vous inscrire sur une maraude, vous pouvez le faire soit via la <a href="/portal/#!/roamingsList/">page des compte-rendus</a>
        <button class="mdl-button mdl-button--icon mdl-js-button mdl-js-ripple-effect" title="Voir la vidéo tutoriel" onClick="openYoutube('https://www.youtube.com/embed/Ns351UrhLlA')">
          <i class="material-icons">help_center</i>
        </button>,
        soit via ce <a href="/redirectionPlanning.php">google sheet</a>
        en mettant votre nom dans la case adequate.<br>
        Pour s'inscrire pour les soupes du dimanche, seul le <a href="/redirectionPlanning.php">fichier google sheet</a>
        <button class="mdl-button mdl-button--icon mdl-js-button mdl-js-ripple-effect" title="Voir la vidéo tutoriel" onClick="openYoutube('https://www.youtube.com/embed/C8dTPbqYrow')">
          <i class="material-icons">help_center</i>
        </button>
        le permet.
      </div>
      <div class="mdl-card__actions mdl-card--border">
        <a class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" href="/redirectionPlanning.php">
          S'inscrire à une maraude ou une soupe
        </a>
      </div>
      <div class="mdl-card__menu">
        <button class="mdl-button mdl-button--icon mdl-js-button mdl-js-ripple-effect" title="Voir la vidéo tutoriel" onClick="openYoutube('https://www.youtube.com/embed/YFrCS8IL_rs')">
          <i class="material-icons video">help_center</i>
        </button>
      </div>
    </div>

    <div class="mdl-card mdl-shadow--2dp">
      <div class="mdl-card__title">
        <h2 class="mdl-card__title-text"><a href="/meetings.php"><i class="material-icons">event</i> Agenda des réunions</a></h2>
      </div>
      <div class="mdl-card__supporting-text">
        La liste des réunions et autres évènements de l'association est disponible sur <a href="/meetings.php">ce calendrier google</a>.
        Tous les évènements ne concernent pas forcément tous les bénévoles, quand c'est le cas c'est généralement indiqué dans le titre.<br>
        N'hésitez pas à ajouter ce calendrier à votre propre agenda en cliquant sur le + afficher en bas à droite de celui-ci.
      </div>
      <div class="mdl-card__actions mdl-card--border">
        <a class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" href="/meetings.php">
          Accéder à l'agenda
        </a>
      </div>
    </div>

    <div class="mdl-card mdl-shadow--2dp">
      <div class="mdl-card__title">
        <h2 class="mdl-card__title-text"><a href="/dokuwiki"><i class="material-icons">school</i> Base de connaissances (WIKI)</a></h2>
      </div>
      <div class="mdl-card__supporting-text">
        Vous pouvez retrouver sur le <a href="/dokuwiki/">WIKI</a> des liens et informations sur le fonctionnement de notre association ainsi
        que sur les associations partenaires et le monde du social.<br>
        N'hésitez pas à consulter les
        <a href="https://docs.google.com/document/d/14nd4-hjs8ZgZ3STmCqo_Yb92afCJbe990GNXF6MAaVc">fiches infos de conseil d'orientation</a> notamment.
      </div>
      <div class="mdl-card__actions mdl-card--border">
        <a class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" href="/dokuwiki/">
          Accéder au WIKI
        </a>
      </div>
    </div>

    <div class="mdl-card mdl-shadow--2dp">
      <div class="mdl-card__title">
        <h2 class="mdl-card__title-text"><a href="/portal/#!/users"><i class="material-icons">contacts </i> Liste des membres</a></h2>
      </div>
      <div class="mdl-card__supporting-text">
        Pour trouver les informations de contact d'un autre membre de l'association,
        rendez-vous sur <a href="/portal/#!/users">la liste des membres</a> et rechercher
        le nom de cette personne.<br>N'hésitez pas à prendre contact, mais n'oubliez pas non plus que
        tout le monde est bénévole et n'as pas forcément de temps à vous accorder.
      </div>
      <div class="mdl-card__actions mdl-card--border">
        <a class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" href="/portal/#!/users">
          Accéder à la liste des membres
        </a>
      </div>
    </div>

    <div class="mdl-card mdl-shadow--2dp">
      <div class="mdl-card__title">
        <h2 class="mdl-card__title-text"><a href="https://www.facebook.com/AMICISamuSocialGrenoble"><i class="material-icons">web</i> Réseaux sociaux</a></h2>
      </div>
      <div class="mdl-card__supporting-text">
        Pour suivre l'actualité de l'association, la source la plus à jour est généralement notre <a href="https://www.facebook.com/AMICISamuSocialGrenoble">page Facebook</a>.<br>
        Pour discuter ou organiser des évènements entre membres, nous avons un <a href="https://www.facebook.com/groups/benevolesvinci">groupe privé Facebook</a>.<br>
        Pour retrouver des vidéos de reportages ou des articles sur l'association, il y a le <a href="https://www.samu-social-grenoble.fr/presse-actus/">site web</a>
        et notre <a href="https://www.youtube.com/channel/UCoLUJ4HGLVEzC7d3mS49MiQ"> chaîne youtube</a>.
      </div>
      <div class="mdl-card__actions mdl-card--border">
        <a class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" href="https://www.facebook.com/AMICISamuSocialGrenoble">Facebook</a>
        <a class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" href="https://samu-social-grenoble.fr">Site Web</a>
        <a class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" href="https://www.youtube.com/channel/UCoLUJ4HGLVEzC7d3mS49MiQ">Youtube</a>
        <a class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" href="https://twitter.com/SamuSocialVINCI">Twitter</a>
      </div>
    </div>

    <div class="mdl-card mdl-shadow--2dp">
      <div class="mdl-card__title">
        <h2 class="mdl-card__title-text"><a href="/contactForm.php"><i class="material-icons">live_help</i> Demander de l'aide</a></h2>
      </div>
      <div class="mdl-card__supporting-text">
        Si vous rencontrer une erreur, un problème d'accès ou n'arrivez pas à faire quelque chose sur le site,
        rendez-vous sur <a href="/contactForm.php">la page contact</a> pour signaler le problème et obtenir de l'aide.<br>
        Pensez à donner le plus d'informations possibles sur le soucis pour que l'on puisse vous aider.
        Et pour tout ce qui n'est pas lié au site, contacter le <a href="mailto:associationvinci@gmail.com">secrétariat de l'association</a>.
      </div>
      <div class="mdl-card__actions mdl-card--border">
        <a class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" href="/contactForm.php">
          Accéder au formulaire d'aide
        </a>
      </div>
    </div>


  </div></main>
</div>

<div id="modal-background" onclick="closeModal()">
</div>
<div id="modal-content">
  <iframe id="youtubePlayer" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
</div>


</body>
</html>

