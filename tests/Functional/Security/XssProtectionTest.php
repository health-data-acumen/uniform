<?php

namespace App\Tests\Functional\Security;

use App\Tests\Functional\WebTestCase;

class XssProtectionTest extends WebTestCase
{
    public function testFormSubmissionWithXssPayloadIsEscaped(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user, 'Test Form');

        $xssPayload = '<script>alert("XSS")</script>';

        $this->client->request('POST', '/e/' . $form->getUid(), [
            'name' => $xssPayload,
            'message' => 'Test message',
        ]);

        $this->assertResponseRedirects();

        $this->clearEntityManager();

        $submissions = $this->entityManager
            ->getRepository(\App\Entity\FormSubmission::class)
            ->findBy(['form' => $form->getId()]);

        $this->assertCount(1, $submissions);
        $payload = $submissions[0]->getPayload();
        $this->assertSame($xssPayload, $payload['name']);
    }

    public function testFormNameWithXssIsEscapedInDisplay(): void
    {
        $user = $this->createAndLoginUser();
        $xssName = '<script>alert("XSS")</script>';

        $form = $this->createFormDefinition($user, $xssName);

        $crawler = $this->client->request('GET', '/dashboard/forms');

        $this->assertResponseIsSuccessful();

        $html = $this->client->getResponse()->getContent();

        $this->assertStringNotContainsString('<script>alert("XSS")</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function testSubmissionPayloadWithXssIsEscapedInDisplay(): void
    {
        $user = $this->createAndLoginUser();
        $form = $this->createFormDefinition($user, 'Test Form');

        $xssPayload = '<img src=x onerror=alert("XSS")>';
        $this->createFormSubmission($form, ['comment' => $xssPayload]);

        $crawler = $this->client->request('GET', '/dashboard/forms/' . $form->getId() . '/submissions');

        $this->assertResponseIsSuccessful();

        $html = $this->client->getResponse()->getContent();

        $this->assertStringNotContainsString('<img src=x onerror=alert("XSS")>', $html);
    }

    public function testFormDescriptionWithXssIsEscaped(): void
    {
        $user = $this->createAndLoginUser();
        $xssDescription = '"><script>alert(1)</script><"';

        $form = $this->createFormDefinition($user, 'Test Form', $xssDescription);

        $crawler = $this->client->request('GET', '/dashboard/forms/' . $form->getId() . '/setup');

        $this->assertResponseIsSuccessful();

        $html = $this->client->getResponse()->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }

    public function testRedirectUrlWithJavascriptProtocolIsRejected(): void
    {
        $user = $this->createAndLoginUser();
        $form = $this->createFormDefinition($user, 'Test Form');

        $crawler = $this->client->request('GET', '/dashboard/forms/' . $form->getId() . '/settings/general');

        $settingsForm = $crawler->selectButton('Save')->form([
            'form_definition[name]' => 'Test Form',
            'form_definition[enabled]' => true,
            'form_definition[redirectUrl]' => 'javascript:alert(1)',
        ]);

        $this->client->submit($settingsForm);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testEmailFieldWithXssPayload(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user, 'Test Form');

        $xssEmail = '"><script>alert(1)</script><input value="';

        $this->client->request('POST', '/e/' . $form->getUid(), [
            'email' => $xssEmail,
        ]);

        $this->assertResponseRedirects();

        $this->clearEntityManager();

        $submissions = $this->entityManager
            ->getRepository(\App\Entity\FormSubmission::class)
            ->findBy(['form' => $form->getId()]);

        $this->assertCount(1, $submissions);
        $payload = $submissions[0]->getPayload();
        $this->assertSame($xssEmail, $payload['email']);
    }
}
