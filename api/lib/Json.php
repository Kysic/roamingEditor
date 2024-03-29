<?php

if (!function_exists('http_response_code')) {
    function http_response_code($code) {
        switch ($code) {
            case 100: $text = 'Continue'; break;
            case 101: $text = 'Switching Protocols'; break;
            case 200: $text = 'OK'; break;
            case 201: $text = 'Created'; break;
            case 202: $text = 'Accepted'; break;
            case 203: $text = 'Non-Authoritative Information'; break;
            case 204: $text = 'No Content'; break;
            case 205: $text = 'Reset Content'; break;
            case 206: $text = 'Partial Content'; break;
            case 300: $text = 'Multiple Choices'; break;
            case 301: $text = 'Moved Permanently'; break;
            case 302: $text = 'Moved Temporarily'; break;
            case 303: $text = 'See Other'; break;
            case 304: $text = 'Not Modified'; break;
            case 305: $text = 'Use Proxy'; break;
            case 400: $text = 'Bad Request'; break;
            case 401: $text = 'Unauthorized'; break;
            case 402: $text = 'Payment Required'; break;
            case 403: $text = 'Forbidden'; break;
            case 404: $text = 'Not Found'; break;
            case 405: $text = 'Method Not Allowed'; break;
            case 406: $text = 'Not Acceptable'; break;
            case 407: $text = 'Proxy Authentication Required'; break;
            case 408: $text = 'Request Time-out'; break;
            case 409: $text = 'Conflict'; break;
            case 410: $text = 'Gone'; break;
            case 411: $text = 'Length Required'; break;
            case 412: $text = 'Precondition Failed'; break;
            case 413: $text = 'Request Entity Too Large'; break;
            case 414: $text = 'Request-URI Too Large'; break;
            case 415: $text = 'Unsupported Media Type'; break;
            case 500: $text = 'Internal Server Error'; break;
            case 501: $text = 'Not Implemented'; break;
            case 502: $text = 'Bad Gateway'; break;
            case 503: $text = 'Service Unavailable'; break;
            case 504: $text = 'Gateway Time-out'; break;
            case 505: $text = 'HTTP Version not supported'; break;
            default:
                exit('Unknown http status code "' . htmlentities($code) . '"');
            break;
        }
        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        header($protocol . ' ' . $code . ' ' . $text);
    }
}

class Json {

    private $lazyMail;

    public function __construct($lazyMail) {
        $this->lazyMail = $lazyMail;
    }

    public function mergeJsonParameterToPost() {
        if ( isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], "application/json") !== false ) {
            $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
        }
    }

    public function returnResult($result) {
        header("Content-Type: application/json");
        echo json_encode($result);
    }

    public function returnError($exception) {
        if ($exception instanceof BadRequestException) {
            $errorCode = 400; // 400 BadRequest
        } else if ($exception instanceof UnauthenticatedException) {
            $errorCode = 401; // 401 Unauthorized (Unauthenticated)
        } else if ($exception instanceof ForbiddenException) {
            $errorCode = 403; // 403 Forbidden
        } else if ($exception instanceof SecurityException) {
            $this->handleSecurityException($exception);
            $errorCode = 403; // 403 Forbidden
        }else if ($exception instanceof NotFoundException) {
            $errorCode = 404; // 404 Not Found
        }  else {
            $errorCode = 500; // 500 Internal Server Error
        }
        http_response_code($errorCode);
        header("Content-Type: application/json");
        echo json_encode(array(
            'status' => 'error',
            'errorCode' => $errorCode,
            'errorMsg' => $exception->getMessage()
        ));
    }

    private function handleSecurityException($exception) {
        $this->lazyMail->get()->sendMail(
            ADMIN_EMAIL,
            '[AMICI] Security alert',
            'IP: '.$_SERVER['REMOTE_ADDR']."\r\n".
            'Navigateur: '.$_SERVER['HTTP_USER_AGENT']."\r\n".
            'Message: '.$exception->getMessage()."\r\n".
            'Security details:'."\r\n".$exception->getDetails()
        );
    }

}
