<?php

class HttpStatusException extends Exception {
    public $statusCode;
    public $statusMsg;
    public $content;
    public function __construct($statusCode, $statusMsg, $content) {
        $this->statusCode = $statusCode;
        $this->statusMsg = $statusMsg;
        $this->content = $content;
        parent::__construct('HTTP error '.$statusCode.' '.$statusMsg.' '.print_r($content, true), $statusCode);
    }
}

class Browser {

    const TRACE = false;
    public $debug;
    public $cookies;
    public $optionalHeaders;

    public function __construct($debug = false) {
        $cookies = array();
        $this->optionalHeaders = array(
            'User-Agent' => 'Mozilla/5.0 (Android; Tablet; rv:19.0) Gecko/19.0 Firefox/19.0'
        );
        $this->debug = $debug;
    }

    public function get($url) {
        return $this->call($url);
    }

    public function post($url, $data) {
        return $this->call($url, $data);
    }

    public function call($url, $postData = NULL) {
        if ($this->debug) {
            echo '[DEBUG] Call '.$url.($postData?' with '.$this->toOneLine($postData):'')."\r\n";
        }
        $urlp = parse_url($url);
        if (@$urlp['port'] == '') $urlp['port'] = 80;
        if (@$urlp['path'] == '') $urlp['path'] = '/';
        $sock = @fsockopen($urlp['host'], $urlp['port']);
        $protocol = $postData ? 'POST' : 'GET';
        $this->puts($sock, $protocol.' '.$urlp['path'].($urlp['query']?'?'.$urlp['query']:'').' HTTP/1.1');
        $this->puts($sock, 'Host: '.$urlp['host'].':'.$urlp['port']);
        $this->putsCookies($sock);
        $this->putsOptionalHeaders($sock);
        $this->puts($sock, 'Cache-Control: no-cache');
        $this->puts($sock, 'Connection: Close');
        if ($postData) {
            $jsonData = json_encode($postData);
            $this->puts($sock, 'Content-length: '.strlen($jsonData));
            $this->puts($sock, 'Content-type: application/json;charset=utf-8');
            $this->puts($sock, '');
            $this->puts($sock, $jsonData);
        }
        $this->puts($sock, '');

        $headerStart = false;
        $headerEnd = false;
        $nbLines = 0;
        $httpStatus = -1;
        $httpMsg = 'NO MSG';
        $isJson = false;
        while (!feof($sock)) {
            $currentLine = fgets($sock, 1024);
            if (self::TRACE) {
                echo '[TRACE] '.$currentLine;
            }
            if ($headerEnd) $content .= $currentLine;
            else if (preg_match('/^HTTP/', $currentLine)) {
                $headerStart = true;
                if (preg_match('/^HTTP\/[0-9\.]* ([0-9]+) ([^\r\n]*)[\r\n]*$/i', $currentLine, $matches)) {
                    $httpStatus = $matches[1];
                    $httpMsg = $matches[2];
                }
            } else if ($headerStart && preg_match("/^[\n\r\t ]*$/", $currentLine)) $headerEnd = true;
            else {
                if (preg_match('/^Content-Type: application\/json'."\r\n".'/', $currentLine)) {
                    $isJson = true;
                }
                $this->parseCookies($currentLine);
            }
            if ($headerStart && !$headerEnd) $header .= $currentLine;
            $nbLines++;
            if ($nbLines > 10000) {
                throw new Exception('Too much lines retrieved in Browser');
            }
        }
        fclose($sock);
        if (self::TRACE) {
            echo "\r\n";
            echo "\r\n";
        }
        if ($isJson) {
            $content = json_decode($content);
        }
        if ($this->debug) {
            echo '[DEBUG] Response '.$httpStatus.' '.$httpMsg.' '.$this->toOneLine($content)."\r\n";
        }
        if ($httpStatus != 200) {
            throw new HttpStatusException($httpStatus, $httpMsg, $content);
        }
        return $content;
    }

    private function toOneLine($obj) {
        return str_replace(array("\r", "\n"), '', print_r($obj, true));
    }

    private function parseCookies($str) {
        if (preg_match("/Set-Cookie: (.+?)=(.+?)(;.*)+?/is", $str, $matches)) {
            if ($matches[2] == 'deleted') {
                unset($this->cookies[$matches[1]]);
            } else {
                $this->cookies[$matches[1]] = urldecode($matches[2]);
            }
        }
    }

    private function putsCookies($sock) {
        if ( !empty($this->cookies) ) {
            $cookiesStr = http_build_query($this->cookies, NULL ,'; ');
            $this->puts($sock, 'Cookie: '.$cookiesStr.';');
        }
    }

    private function putsOptionalHeaders($sock) {
        if ( !empty($this->optionalHeaders) ) {
            foreach ($this->optionalHeaders as $headerKey => $headerValue) {
                $this->puts($sock, $headerKey.': '.$headerValue);
            }
        }
    }

    private function puts($sock, $data) {
        if (self::TRACE) {
            echo '[TRACE] '.$data."\r\n";
        }
        fputs($sock, $data."\r\n");
    }

}

