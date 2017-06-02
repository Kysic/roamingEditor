<?php

require_once('conf/smtp.php');

function sendMail($to, $sujet, $body, $from=false) {
    if (!$from) {
        $from = ADMIN_EMAIL;
    }
    require_once('lib/ext/class.phpmailer.php');
    require_once('lib/ext/class.smtp.php');

    $mail = new PHPMailer;

    // $mail->SMTPDebug = 3;

    $mail->isSMTP();
    $mail->CharSet = 'UTF-8';
    $mail->Timeout = 10;
    $mail->Host = 'smtp.free.fr';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASSWORD;
    
    $mail->setFrom($from, 'Vinci CR');
    $mail->addAddress($to);

    $mail->Subject = $sujet;
    $mail->Body    = $body;

    if(!$mail->send()) {
        // echo 'Mailer Error: ' . $mail->ErrorInfo;
        $headers = "From: ".$from."\r\nReply-to: ".$from."\r\n";
        $headers .= 'Content-Type: text/plain; charset="utf-8"'."\r\n";
        if ( !@mail($to, $sujet, $body, $headers) ) {
            throw new Exception('Erreur lors de l\'envoi du mail, veuillez contacter l\'administrateur du site.');
        }
    }
}

function getLoginURL($userMail) {
    return 'http://'.$_SERVER['HTTP_HOST'].APPLICATION_PATH.'/viewer/#!/login/'.$userMail;
}

function getPasswordURL($userId, $mailToken) {
    return 'http://'.$_SERVER['HTTP_HOST'].APPLICATION_PATH.'/viewer/#!/setPassword?userId='.$userId.'&mailToken='.urlencode($mailToken);
}

function sendSigninToken($to, $firstname, $lastname, $userId, $mailToken) {
    sendMail($to, '[VINCI] Inscription sur le site de comptes rendus de maraudes du VINCI',
            'Bonjour '.$firstname.' '.$lastname.','."\n".
            "\n".
            'Bienvenue sur le site de consultations des comptes rendus de maraudes du VINCI.'."\n".
            'Pour finaliser votre inscription, veuillez vous rendre à l\'adresse suivante afin de choisir votre mot de passe :'."\n".
            getPasswordURL($userId, $mailToken)."\n".
            "\n".
            'Par la suite, vous pourrez vous connecter au site via l\'adresse suivante : '."\n".
            getLoginURL($to)
            );
}

function sendResetPasswordToken($to, $firstname, $lastname, $userId, $mailToken) {
    sendMail($to, '[VINCI] Réinitialisation de votre mot de passe',
            'Bonjour '.$firstname.' '.$lastname.','."\n".
            "\n".
            'Vous venez de demander la réinitialisation de votre mot de passe sur le site de comptes rendus de maraudes du VINCI'."\n".
            'Pour choisir votre nouveau mot de passe, veuillez vous rendre à l\'adresse suivante :'."\n".
            getPasswordURL($userId, $mailToken)."\n".
            "\n".
            'Par la suite, vous pourrez vous connecter au site via l\'adresse suivante : '."\n".
            getLoginURL($to)."\n".
            "\n".
            'Si vous n\'êtes pas à l\'origine de ce message, merci de prévenir l\'administrateur du site en répondant à ce mail.'
            );
}

?>
