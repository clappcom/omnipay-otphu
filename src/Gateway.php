<?php

namespace Clapp\OtpHu;

use Omnipay\Common\AbstractGateway;
use SimpleXMLElement;

class Gateway extends AbstractGateway{

    protected $endpoint = "https://www.otpbankdirekt.hu/mwaccesspublic/mwaccess";

    public function getName(){
        return "otphu";
    }

    public function purchase($options){
        $this->generateTransactionId();
    }

    protected function generateTransactionId(){

        //WEBSHOPTRANZAZONGENERALAS

        $request = $this->httpClient->post(
            $this->endpoint,
            [
                'content-type' => 'text/xml; charset=utf-8',
                'SOAPAction' => 'urn:startWorkflowSynch',
            ],
            '<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
 <soapenv:Body>
  <ns1:startWorkflowSynch soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:ns1="urn:MWAccess">
   <arg0 xsi:type="xsd:string">WEBSHOPTRANZAZONGENERALAS</arg0>
   <arg1 xsi:type="xsd:string">&lt;?xml version=&quot;1.0&quot; encoding=&quot;ISO-8859-2&quot;?&gt;
&lt;StartWorkflow&gt;
  &lt;TemplateName&gt;WEBSHOPTRANZAZONGENERALAS&lt;/TemplateName&gt;
  &lt;Variables&gt;
    &lt;isClientCode&gt;WEBSHOP&lt;/isClientCode&gt;
    &lt;isPOSID&gt;#02299991&lt;/isPOSID&gt;
    &lt;isClientSignature&gt;09DE5911BEA178FBC9EF95B300572DBDC72FC9A861A0A87B7C6FB2B6975BD5766FA3E269EEA0534A0A17521F9CF2DB4CEA6F96C985DD1C152C676629CCBBCB5FE61161EDB9B51BDEF20D507E389390F341E0F470A5E2D9307241BDB94AA724A319742FC0B99C024DE737A07AE540225CB16C6A3C32ACA3AD645EE322D990D863&lt;/isClientSignature&gt;
  &lt;/Variables&gt;
&lt;/StartWorkflow&gt;
</arg1>
  </ns1:startWorkflowSynch>
 </soapenv:Body>
</soapenv:Envelope>'
        );

        $response = $request->send();


        var_dump(
            $response->getStatusCode(),
            $response->getHeaders()->toArray(),
            $response->getBody()->__toString(),
            (new SimpleXMLElement(base64_decode($response->xml()->xpath('//result')[0]->__toString())))->xpath('//id')[0]->__toString()
        ); exit;


        /**
         * <env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><env:Header></env:Header><env:Body env:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><m:startWorkflowSynchResponse xmlns:m="java:hu.iqsoft.otp.mw.access"><return xmlns:n1="java:hu.iqsoft.otp.mw.access" xsi:type="n1:WorkflowState"><completed xsi:type="xsd:boolean">true</completed><endTime xsi:type="xsd:string">2016.06.20. 23:54:36</endTime><instanceId xsi:type="xsd:string">zOZoWRysJLZx4539574399</instanceId><result xsi:type="xsd:base64Binary">PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPGFuc3dlcj48cmVzdWx0c2V0PjxyZWNvcmQ+PHBvc2lkPiMwMjI5OTk5MTwvcG9zaWQ+PGlkPjM2NjM4MzY2NjI5ODg4MDUzMzU1MDI4Mzc1MzYzNjg3PC9pZD48dGltZXN0YW1wPjIwMTYuMDYuMjAgMjMuNTQuMzYgMTE0PC90aW1lc3RhbXA+PC9yZWNvcmQ+PC9yZXN1bHRzZXQ+PG1lc3NhZ2VsaXN0PjxtZXNzYWdlPlNJS0VSPC9tZXNzYWdlPjwvbWVzc2FnZWxpc3Q+PGluZm9saXN0Lz48L2Fuc3dlcj4=</result><startTime xsi:type="xsd:string">2016.06.20. 23:54:36</startTime><templateName xsi:type="xsd:string">WEBSHOPTRANZAZONGENERALAS</templateName><timeout xsi:type="xsd:boolean">false</timeout></return></m:startWorkflowSynchResponse></env:Body></env:Envelope>
         */

        var_dump($response->xml()->xpath('//instanceId')[0]->asXml()); exit;

        return $request;
    }

    public function completePurchase($options){

    }

    public function setShopId($value){
        return $this->setParameter("shop_id", $value);
    }
    public function getShopId(){
        return $this->getParameter("shop_id");
    }
    public function setPrivateKey($value){
        return $this->setParameter("private_key", $value);
    }
    public function getPrivateKey(){
        return $this->getParameter("private_key");
    }
}
