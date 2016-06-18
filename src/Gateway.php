<?php

namespace Clapp\OtpHu;

use Omnipay\Common\AbstractGateway;

class Gateway extends AbstractGateway{
    public function getName(){
        return "otphu";
    }

    public function purchase($options){

    }

    public function completePurchase($options){

    }
}
