<?php

# Exceptions
class UnauthenticatedException extends Exception { }
class ForbiddenException extends Exception { }
class BadRequestException extends Exception { }
class NotFoundException extends Exception { }
class InternalException extends Exception { }

class SecurityException extends Exception {

    private $details;

    public function __construct($message, $details = "no details") {
        parent::__construct($message);
        $this->details = $details;
    }

    public function getDetails() {
        return $this->details;
    }

}


