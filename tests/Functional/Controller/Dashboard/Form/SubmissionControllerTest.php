<?php

namespace App\Tests\Functional\Controller\Dashboard\Form;

use App\Tests\Functional\WebTestCase;

class SubmissionControllerTest extends WebTestCase
{
    public function testSendNotificationRequiresAuthentication(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);
        $submission = $this->createFormSubmission($form);

        $this->client->request('POST', '/submission/' . $submission->getId() . '/notifications/send');

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertRouteSame('app_security_login');
    }

    public function testSendNotificationEndpointOnlyAllowsPost(): void
    {
        $user = $this->createAndLoginUser();
        $form = $this->createFormDefinition($user);
        $submission = $this->createFormSubmission($form);

        // GET method should not be allowed (only POST)
        $this->client->request('GET', '/submission/' . $submission->getId() . '/notifications/send');

        $this->assertResponseStatusCodeSame(405);
    }
}
