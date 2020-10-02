<?php declare(strict_types=1);

use Just\Http\GlobalRequest;

class DummyRequest extends GlobalRequest
{
    protected $_uri;
    protected $_method;
    public function setUri(string $uri)
    {
        $this->_uri = $uri;
    }
    public function setMethod(string $method)
    {
        $this->_method = $method;
    }
}
