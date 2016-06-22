<?php

use Faker\Factory as Faker;

abstract class TestCase extends PHPUnit_Framework_TestCase{

    public function __get($field){
        if ($field === "faker"){
            if (!isset($this->_faker)){
                $this->_faker = Faker::create();
            }
            return $this->_faker;
        }else {
            return parent::__get($field);
        }
    }

    protected $lastException = null;

    public function setLastException($e){
        $this->lastException = $e;
    }

    public function assertLastException($className){
        if (empty($this->lastException) || !is_a($this->lastException, $className)){
            $this->fail('Failed to assert that last exception is subclass of '.$className);
        }
    }

    public function getDummyRsaPrivateKey(){
        return "-----BEGIN RSA PRIVATE KEY-----
MIICWgIBAAKBgQCUnRq1I95d2PxR+RwCa+BT8GxeH9t7qCna+cDRnJDfNbgrosUM
n9VYGBSAG4S2KqEgNgA6eh9w0xQgNQ/pVKLPgdCjENBnwrZcH+NMyqO9ERHlhMXO
ddkDCMfVqjQIehfD68kiAPd+S4FWVZ1Efcy6twnr7KRignDz9q7F+VqoiQIBJQKB
gCgqdevEgUnL8SrpYYQdJ99VvGx3UBOVO76kXaBvgRm7feJHquDRQJRZiQAHb/ni
Agi0ph2kdzM/95oAgNdHTpiIPjDtK5ovr5Wg2N6xzHV0hOQsR6m6N4CUjVR5WrR5
PWcq7rnyDOgzEzZjRF6T8LV4sKAbcON5EAKh/M88KWlNAkEA33daMgpxAXJGXRBE
pCg11+asDygpd7IlV0nXVcumYvufMW3JfhGRRCOi5qsuIhv31kGLEYT1tUQVsY+x
ouQIFQJBAKo/+j4LCRDUBbx9acfT1KOl7TguF2a/6FimRcaYxlaFwJuBqINPxRcw
Nv+oUgUcidVuEHWXVloLOu3Ee/fdZ6UCQQCQ83jGg1A4Sh/Nqa/7xw4rLddjxwYk
IIbsguyKrZxb4XwEYuOQC2UlR4xCmIyggNgcRjCxawA+OgA70tQWocANAkBFBS4Z
JxGDXN+7HhYglCXFzaVb9wKRcGUdBSM01ibkznCtvvFJ/b6asq6DUhNp2yMfLJ7j
kGFHGU89zDJB5CMZAkAD6gCd9i74NNmXjp6w1xl/4ngIYpZsAG5oqQu4a15h03yX
UPNeFSinFvysmiUWiVCSIO1GjSHctPrr4Sx8lJTG
-----END RSA PRIVATE KEY-----
";
    }

}
