<?php

namespace App\Tests\Functional\Controller\Dashboard\Form;

use App\Entity\FormDefinition;
use App\Tests\Functional\WebTestCase;

class EndpointControllerTest extends WebTestCase
{
    public function testIndexListsUserForms(): void
    {
        $user = $this->createAndLoginUser();
        $this->createFormDefinition($user, 'Form A');
        $this->createFormDefinition($user, 'Form B');

        $this->client->request('GET', '/dashboard/forms');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Form A');
        $this->assertSelectorTextContains('body', 'Form B');
    }

    public function testIndexRequiresAuthentication(): void
    {
        $this->client->request('GET', '/dashboard/forms');

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertRouteSame('app_security_login');
    }

    public function testCreateFormRedirectsWithoutTurboFrame(): void
    {
        $this->createAndLoginUser();

        $this->client->request('GET', '/dashboard/forms/new');

        $this->assertResponseRedirects('/dashboard/forms');
    }

    public function testCreateFormDisplaysFormWithTurboFrame(): void
    {
        $this->createAndLoginUser();

        $this->client->request('GET', '/dashboard/forms/new', [], [], [
            'HTTP_TURBO_FRAME' => 'turbo-modal',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testCreateFormFormIsRendered(): void
    {
        $user = $this->createAndLoginUser();

        // Access the form page with Turbo frame header
        $crawler = $this->client->request('GET', '/dashboard/forms/new', [], [], [
            'HTTP_TURBO_FRAME' => 'turbo-modal',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#endpoint-form');
        $this->assertSelectorExists('input[name="form_definition[name]"]');
    }

    public function testSetupPageDisplaysFields(): void
    {
        $user = $this->createAndLoginUser();
        $form = $this->createFormDefinition($user, 'Test Form');

        $this->client->request('GET', '/dashboard/forms/' . $form->getId() . '/setup');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Test Form');
    }

    public function testSubmissionsPageWithPagination(): void
    {
        $user = $this->createAndLoginUser();
        $form = $this->createFormDefinition($user, 'Test Form');

        for ($i = 0; $i < 5; $i++) {
            $this->createFormSubmission($form, ['email' => "user{$i}@example.com"]);
        }

        $this->client->request('GET', '/dashboard/forms/' . $form->getId() . '/submissions');

        $this->assertResponseIsSuccessful();
    }

    public function testGeneralSettingsFormSubmission(): void
    {
        $user = $this->createAndLoginUser();
        $form = $this->createFormDefinition($user, 'Original Name');

        $crawler = $this->client->request('GET', '/dashboard/forms/' . $form->getId() . '/settings/general');

        $settingsForm = $crawler->selectButton('Save')->form([
            'form_definition[name]' => 'Updated Name',
            'form_definition[enabled]' => true,
        ]);

        $this->client->submit($settingsForm);

        $this->assertResponseRedirects();

        $this->clearEntityManager();

        $updatedForm = $this->entityManager->getRepository(FormDefinition::class)
            ->find($form->getId());

        $this->assertSame('Updated Name', $updatedForm->getName());
    }

    public function testDeleteRequiresAuthentication(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user, 'Test Form');

        // Without login, POST to delete should redirect to login
        $this->client->request('POST', '/dashboard/forms/' . $form->getId() . '/delete', [
            '_token' => 'any_token',
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertRouteSame('app_security_login');
    }
}
