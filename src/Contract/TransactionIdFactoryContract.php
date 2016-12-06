<?php

namespace Clapp\OtpHu\Contract;

interface TransactionIdFactoryContract{
    /**
     * Generate a new, unique transaction ID to be used for a new purchase
     *
     * The transaction ID should be unique to the shopID
     *
     * @param  array $parameters merged list of gateway purchase parameters
     * @return string new, unique transaction ID
     */
    public function generateTransactionId($parameters = []);
}
