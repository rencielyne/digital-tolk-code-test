<?php

namespace Tests\Unit;

use DTApi\Helpers\TeHelper;
use Tests\TestCase;

class TeHelperWillExpireAtTest extends TestCase
{
    public function test_will_expire_at_less_than_90_hours()
    {
        $result = TeHelper::willExpireAt('2024-03-22 12:00:00', '2024-03-20 12:00:00');
        $this->assertEquals('2024-03-22 12:00:00', $result);
    }

    public function test_will_expire_at_less_than_24_hours()
    {
        $result = TeHelper::willExpireAt('2024-03-20 15:00:00', '2024-03-20 10:00:00');
        $this->assertEquals('2024-03-20 11:30:00', $result);
    }

    public function test_will_expire_at_between_24_and_72_hours()
    {
        $result = TeHelper::willExpireAt('2024-03-23 12:00:00', '2024-03-20 12:00:00');
        $this->assertEquals('2024-03-21 04:00:00', $result);
    }

    public function test_will_expire_at_greater_than_90_hours()
    {
        $result = TeHelper::willExpireAt('2024-03-25 12:00:00', '2024-03-20 12:00:00');
        $this->assertEquals('2024-03-23 12:00:00', $result);
    }
}
