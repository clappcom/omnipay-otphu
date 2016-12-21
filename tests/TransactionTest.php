<?php

use Clapp\OtpHu\Transaction;
use Carbon\Carbon;

class TransactionTest extends TestCase
{
    /**
     * @expectedException \Exception
     */
    public function testInvalidTransaction()
    {
        $transaction = new Transaction();
        $transaction->getTransactionId();
    }

    public function testTransactionId()
    {
        $transaction = Transaction::fromXml(self::$transactionDetailsPendingResponseBody);
        $this->assertNotNull($transaction->getTransactionId());
    }

    public function testValidStartDateInvalidEndDate()
    {
        $transaction = Transaction::fromXml(self::$transactionDetailsPendingResponseBody);
        $this->assertNotNull($transaction->getStartDate());
        $this->assertInstanceof(Carbon::class, $transaction->getStartDate());

        $this->assertNull($transaction->getEndDate());
        //$this->assertInstanceof(Carbon::class, $transaction->getEndDate());
    }

    public function testValidStartDateValidEndDate()
    {
        $transaction = Transaction::fromXml(self::$transactionDetailsCompletedResponseBody);
        $this->assertNotNull($transaction->getStartDate());
        $this->assertInstanceof(Carbon::class, $transaction->getStartDate());

        $this->assertNotNull($transaction->getEndDate());
        $this->assertInstanceof(Carbon::class, $transaction->getEndDate());
    }

    public function testSetRawTransactionWithInvalidTransaction()
    {
        $transaction = new Transaction();
        $transaction->setRawTransaction('foobar');
    }

    public function testSetRawTransactionWithEmptyTransaction()
    {
        $transaction = new Transaction();
        $transaction->setRawTransaction(new SimpleXMLElement('<a></a>'));
    }

    public function testEmptyStartDate()
    {
        $transaction = new Transaction();
        $transaction->setRawTransaction(new SimpleXMLElement('<a></a>'));
        $this->assertNull($transaction->getStartDate());
    }
}
