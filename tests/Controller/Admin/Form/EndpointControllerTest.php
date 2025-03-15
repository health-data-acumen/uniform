<?php

namespace App\Tests\Controller\Admin\Form;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EndpointControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/form/endpoint');

        self::assertResponseIsSuccessful();
    }
}
