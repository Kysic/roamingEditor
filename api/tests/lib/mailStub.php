<?php

function sendMailStub($to, $subject, $body, $from) {
    file_put_contents(ROAMING_API_DIR.'/tests/tmp/mail-'.$to,
        json_encode(array(
            'to'=>$to,
            'from'=>$from,
            'subject'=>$subject,
            'body'=> explode("\n", $body)
        ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );
}

