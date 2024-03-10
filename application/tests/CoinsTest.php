<?php

namespace Tests\Model;

use PHPUnit\Framework\TestCase;
use App\Model\Coins;

class CoinsTest extends TestCase
{
    public function testMinimumCoinsNeededToEqualAmount_PoundsFormat(): void
    {
        $coins = new Coins();
        $coins->minimumCoinsNeededToEqualAmount("£1.23");

        $this->assertEquals(0, $coins->getTwoPounds());
        $this->assertEquals(1, $coins->getOnePounds());
        $this->assertEquals(0, $coins->getFiftyPences());
        $this->assertEquals(1, $coins->getTwentyPences());
        $this->assertEquals(1, $coins->getTwoPences());
        $this->assertEquals(1, $coins->getOnePences());
    }
}