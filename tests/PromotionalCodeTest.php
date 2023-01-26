<?php

use PHPUnit\Framework\TestCase;
use Alimerka\PromotionalCode;

class PromotionalCodeTest extends TestCase
{
    /**
     * @group promotional-code
     * @group production
     * @covers Alimerka\PromotionalCode
     */
    public function testValidations()
    {
        $testCase1 = [
            'code' => 18182760224,
            'supermarket' => 164,
            'promotion_code' => 22,
        ];

        $promotionalCode = new PromotionalCode($testCase1['code']);
        $this->assertTrue($promotionalCode->isValid());
        $this->assertEquals($testCase1['supermarket'], $promotionalCode->getSupermarket());
        $this->assertTrue($promotionalCode->isOfSupermarket($testCase1['supermarket']));
        $this->assertFalse($promotionalCode->isOfSupermarket(165));
        $this->assertEquals($testCase1['promotion_code'], $promotionalCode->getPromotionCode());

        $testCase2 = [
            'code' => 19182760224,
            'supermarket' => 164,
            'promotion_code' => 22,
        ];

        $promotionalCode = new PromotionalCode($testCase2['code']);
        $this->assertFalse($promotionalCode->isValid());
        $this->assertNull($promotionalCode->getSupermarket());
        $this->assertFalse($promotionalCode->isOfSupermarket($testCase2['supermarket']));
        $this->assertFalse($promotionalCode->isOfSupermarket(165));
        $this->assertNull($promotionalCode->getPromotionCode());
    }

    /**
     * @group promotional-code
     * @covers Alimerka\PromotionalCode
     */
    public function testValidCases()
    {
        $validTestCases = [
            [
                'code' => 10101896001,
                'promotion_code' => 10,
            ],
            [
                'code' => 11081608011,
                'promotion_code' => 10,
            ],
            [
                'code' => 15131391089,
                'promotion_code' => 10,
            ],
            [
                'code' => 17511557012,
                'promotion_code' => 10,
            ],
            [
                'code' => 15511848077,
                'promotion_code' => 10,
            ],
            [
                'code' => 18021226113,
                'promotion_code' => 11,
            ],
            [
                'code' => 13021303156,
                'promotion_code' => 11,
            ],
            [
                'code' => 19121588101,
                'promotion_code' => 11,
            ],
            [
                'code' => 13521443189,
                'promotion_code' => 11,
            ],
            [
                'code' => 17131771146,
                'promotion_code' => 11,
            ]
        ];

        foreach ($validTestCases as $validTestCase) {
            $promotionalCode = new PromotionalCode($validTestCase['code']);

            $this->assertTrue($promotionalCode->isValid());
            // $this->assertEquals($validTestCase['supermarket'], $promotionalCode->getSupermarket()); //Unknown value supermarket
            // $this->assertTrue($promotionalCode->isOfSupermarket($validTestCase['supermarket'])); //Unknown value supermarket
            $this->assertTrue(strlen($promotionalCode->getSupermarket()) == 3);
            $this->assertTrue($promotionalCode->isOfSupermarket($promotionalCode->getSupermarket()));
            $this->assertEquals($validTestCase['promotion_code'], $promotionalCode->getPromotionCode());
        }
    }
}
