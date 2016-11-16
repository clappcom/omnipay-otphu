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
        $this->lastException = null;
    }

    public static $successfulTransactionIdGenerationResponseBody = '<env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><env:Header></env:Header><env:Body env:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><m:startWorkflowSynchResponse xmlns:m="java:hu.iqsoft.otp.mw.access"><return xmlns:n1="java:hu.iqsoft.otp.mw.access" xsi:type="n1:WorkflowState"><completed xsi:type="xsd:boolean">true</completed><endTime xsi:type="xsd:string">2016.11.16. 23:19:52</endTime><instanceId xsi:type="xsd:string">5v6LHfZa0Oox5175304950</instanceId><result xsi:type="xsd:base64Binary">PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPGFuc3dlcj48cmVzdWx0c2V0PjxyZWNvcmQ+PHBvc2lkPiMwMjI5OTk5MTwvcG9zaWQ+PGlkPjk0MTU4MDY5Mjg5OTkwNDEwNzgyNzU0MjA3Njg5OTEyPC9pZD48dGltZXN0YW1wPjIwMTYuMTEuMTYgMjMuMTkuNTIgNjk1PC90aW1lc3RhbXA+PC9yZWNvcmQ+PC9yZXN1bHRzZXQ+PG1lc3NhZ2VsaXN0PjxtZXNzYWdlPlNJS0VSPC9tZXNzYWdlPjwvbWVzc2FnZWxpc3Q+PGluZm9saXN0Lz48L2Fuc3dlcj4=</result><startTime xsi:type="xsd:string">2016.11.16. 23:19:52</startTime><templateName xsi:type="xsd:string">WEBSHOPTRANZAZONGENERALAS</templateName><timeout xsi:type="xsd:boolean">false</timeout></return></m:startWorkflowSynchResponse></env:Body></env:Envelope>';
    public static $invalidClientSignatureResponseBody = '<env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><env:Header></env:Header><env:Body env:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><m:startWorkflowSynchResponse xmlns:m="java:hu.iqsoft.otp.mw.access"><return xmlns:n1="java:hu.iqsoft.otp.mw.access" xsi:type="n1:WorkflowState"><completed xsi:type="xsd:boolean">true</completed><endTime xsi:type="xsd:string">2016.11.16. 22:52:10</endTime><instanceId xsi:type="xsd:string">umpwSabQy0Qx5175259813</instanceId><result xsi:type="xsd:base64Binary">PD94bWwgdmVyc2lvbj0nMS4wJyBlbmNvZGluZz0ndXRmLTgnPz48YW5zd2VyPjxyZXN1bHRzZXQ+PC9yZXN1bHRzZXQ+PG1lc3NhZ2VsaXN0PjxtZXNzYWdlPkhJQkFTS0xJRU5TQUxBSVJBUzwvbWVzc2FnZT48L21lc3NhZ2VsaXN0PjwvYW5zd2VyPg==</result><startTime xsi:type="xsd:string">2016.11.16. 22:52:10</startTime><templateName xsi:type="xsd:string">WEBSHOPTRANZAKCIOLEKERDEZES</templateName><timeout xsi:type="xsd:boolean">false</timeout></return></m:startWorkflowSynchResponse></env:Body></env:Envelope>';

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
