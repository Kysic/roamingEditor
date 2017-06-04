<?php

function sendMailStub($to, $subject, $body, $from) {
    file_put_contents('tests/tmp/mail-'.$to,
        json_encode(array(
            'to'=>$to,
            'from'=>$from,
            'subject'=>$subject,
            'body'=>$body
        ))
    );
}

