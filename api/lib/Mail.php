<?php

require_once(ROAMING_API_DIR.'/conf/mail.php');
require_once(ROAMING_API_DIR.'/lib/ext/class.phpmailer.php');
require_once(ROAMING_API_DIR.'/lib/ext/class.smtp.php');

class Mail {

    public function sendRegisterToken($to, $firstname, $lastname, $userId, $mailToken) {
        $this->sendMail($to, '[AMICI] Inscription site Samu Social de Grenoble',
                'Bonjour '.$firstname.' '.$lastname.','."\n".
                "\n".
                'Vous recevez cet email en tant que membre de l\'association de Samu Social de Grenoble AMICI,'."\n".
                'afin de vous donner l\'accès au site de consultation des comptes rendus de maraudes.'."\n".
                "\n".
                'Pour finaliser votre inscription, veuillez vous rendre à l\'adresse suivante afin de choisir votre mot de passe :'."\n".
                $this->getPasswordURL($userId, $mailToken)."\n".
                "\n".
                'Par la suite, vous pourrez vous connecter au site via l\'adresse suivante : '."\n".
                $this->getLoginURL($to)
                );
    }

    public function sendResetPasswordToken($to, $firstname, $lastname, $userId, $mailToken) {
        $this->sendMail($to, '[AMICI] Réinitialisation de votre mot de passe',
                'Bonjour '.$firstname.' '.$lastname.','."\n".
                "\n".
                'Vous venez de demander la réinitialisation de votre mot de passe sur le site de comptes rendus de maraudes d\'AMICI'."\n".
                'Pour choisir votre nouveau mot de passe, veuillez vous rendre à l\'adresse suivante :'."\n".
                $this->getPasswordURL($userId, $mailToken)."\n".
                "\n".
                'Par la suite, vous pourrez vous connecter au site via l\'adresse suivante : '."\n".
                $this->getLoginURL($to)."\n".
                "\n".
                'Si vous n\'êtes pas à l\'origine de ce message, merci de prévenir l\'administrateur du site en répondant à ce mail.'
                );
    }

    private function getLoginURL($userMail) {
        return $this->getProtocol().$_SERVER['HTTP_HOST'].PORTAL_APPLICATION_PATH.'/#!/login/'.$userMail;
    }

    private function getPasswordURL($userId, $mailToken) {
        return $this->getProtocol().$_SERVER['HTTP_HOST'].PORTAL_APPLICATION_PATH.'/#!/setPassword?userId='.$userId.'&mailToken='.urlencode($mailToken);
    }

    private function getProtocol() {
        return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://';
    }

    public function sendMail($to, $subject, $body, $from=false, $isHTML=false) {
        if (!$from) {
            $from = ADMIN_EMAIL;
        }
        if (MAIL_MODE == 'STUB') {
            require_once(ROAMING_API_DIR.'/tests/lib/mailStub.php');
            sendMailStub($to, $subject, $body, $from, $isHTML);
        } elseif (MAIL_MODE == 'SMTP') {
            $this->sendMailSMTP($to, $subject, $body, $from, $isHTML);
        } else {
            $this->sendMailNative($to, $subject, $body, $from, $isHTML);
        }
    }

    private function sendMailNative($to, $subject, $body, $from, $isHTML) {
        $headers = "From: ".$from."\r\nReply-to: ".$from."\r\n";
        if ($isHTML) {
            $headers .= 'Content-type: text/html; charset="utf-8"'."\r\n";
        } else {
            $headers .= 'Content-Type: text/plain; charset="utf-8"'."\r\n";
        }
        if ( !@mail($to, $this->encodeRFC1342($subject), $body, $headers) ) {
            throw new Exception('Erreur lors de l\'envoi du mail, veuillez contacter l\'administrateur du site.');
        }
    }

    private function encodeRFC1342($txt) {
        return mb_encode_mimeheader($txt);
    }

    private function sendMailSMTP($to, $subject, $body, $from, $isHTML) {

        $mail = new PHPMailer;

        // $mail->SMTPDebug = 3;

        $mail->isSMTP();
        $mail->IsHTML($isHTML);
        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 10;
        $mail->Host = SMTP_HOST;
        $mail->SMTPSecure = 'tls';
        $mail->Port = SMTP_PORT;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASSWORD;

        $mail->setFrom($from, 'AMICI CR');
        $mail->addAddress($to);

        $mail->Subject = $subject;
        $mail->Body    = $body;

        if(!$mail->send()) {
            // echo 'Mailer Error: ' . $mail->ErrorInfo;
            sendMailNative($to, $subject, $body, $from, $isHTML);
        }
    }

}
