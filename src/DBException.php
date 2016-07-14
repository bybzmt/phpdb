<?php
namespace bybzmt\DB;

class DBException extends \Exception
{

    public function __construct($statusCode, $message = null, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
