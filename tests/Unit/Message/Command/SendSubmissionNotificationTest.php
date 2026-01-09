<?php

namespace App\Tests\Unit\Message\Command;

use App\Message\Command\SendSubmissionNotification;
use PHPUnit\Framework\TestCase;

class SendSubmissionNotificationTest extends TestCase
{
    public function testConstructorSetsSubmissionId(): void
    {
        $message = new SendSubmissionNotification(123);

        $this->assertSame(123, $message->getSubmissionId());
    }

    public function testGetSubmissionIdReturnsCorrectValue(): void
    {
        $message = new SendSubmissionNotification(456);

        $this->assertSame(456, $message->getSubmissionId());
    }

    public function testSubmissionIdCanBeZero(): void
    {
        $message = new SendSubmissionNotification(0);

        $this->assertSame(0, $message->getSubmissionId());
    }

    public function testSubmissionIdCanBeLargeNumber(): void
    {
        $largeId = 999999999;
        $message = new SendSubmissionNotification($largeId);

        $this->assertSame($largeId, $message->getSubmissionId());
    }

    public function testMultipleInstancesAreIndependent(): void
    {
        $message1 = new SendSubmissionNotification(1);
        $message2 = new SendSubmissionNotification(2);

        $this->assertSame(1, $message1->getSubmissionId());
        $this->assertSame(2, $message2->getSubmissionId());
    }
}
