#!/bin/bash

mail -aFrom:NO_REPLY_EMAIL -s 'Message non délivré' "$SENDER" <<EOF
Bonjour,

Ceci est une réponse automatique.
Vous avez envoyé un email sur une adresse email n'acceptant pas de message.
Votre message ne sera donc pas lu par un humain.

- Si vous souhaitez transmettre des signalements à l'équipe de maraude du VINCI, vous pouvez utiliser l'adresse : SIGNALEMENTS_EMAIL
- Si vous souhaitez joindre le secrétariat du VINCI, vous pouvez utiliser l'adresse : SECRETARIAT_EMAIL
- Si vous souhaitez joindre le responsable de la plateforme logiciel, vous pouvez utiliser l'adresse : ADMIN_EMAIL

Bonne journée à vous

Bien cordialement

EOF
