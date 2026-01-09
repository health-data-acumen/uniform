<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DefaultControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = self::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains('UniForm');
        $this->assertSelectorTextContains('h1', 'UniForm');
    }
}
