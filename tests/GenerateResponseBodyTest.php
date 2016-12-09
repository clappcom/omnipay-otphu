<?php



class GenerateResponseBodyTest extends TestCase{

    public function testGenerateResponseBodyWithPayload(){

        $payload = [
            'resultset' => [
                'record' => [
                    'posid' => '74179',
                    'transactionid' => '5',
                ],
            ],
            'messagelist' => [
                'message' => 'HIANYZIKSHOPPUBLIKUSKULCS'
            ]
        ];

        $this->assertEquals(self::$unknownShopIdResponseBody, $this->generateResponseBody($payload));


        $payload = [
            'resultset' => [
                'record' => [
                    'posid' => '#02299991',
                    'transactionid' => '17db8fc54a3733c4898a67e0dbbd8996',
                ],
            ],
            'messagelist' => [
                'message' => 'SIKERESWEBSHOPFIZETESINDITAS'
            ]
        ];

        $this->assertEquals(self::$successfulPurchaseResponseBody, $this->generateResponseBody($payload));

    }
}
