<?php

namespace Lion\Auth\Exception;

use Exception;
use Throwable;

class AzureException extends Exception implements \Throwable
{
    public function __construct($message = '', $description = '', $code = 0, Throwable $previous = null)
    {
        $message .= ":<pre>".print_r($description, true)."</pre><br/><br/>";
        parent::__construct($message, $code, $previous);
    }
}