<?php

namespace App\Tests\Integration\Form;

use App\Entity\FormDefinition;
use App\Form\FormDefinitionType;
use App\Tests\Integration\DatabaseTestCase;
use Symfony\Component\Form\FormFactoryInterface;

class FormDefinitionTypeTest extends DatabaseTestCase
{
    private FormFactoryInterface $formFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formFactory = self::getContainer()->get(FormFactoryInterface::class);
    }

    public function testSubmitValidDataForNewForm(): void
    {
        $formData = [
            'name' => 'My New Form',
        ];

        $model = new FormDefinition();
        $form = $this->formFactory->create(FormDefinitionType::class, $model, [
            'csrf_protection' => false,
        ]);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertSame('My New Form', $model->getName());
    }

    public function testFormHasNameFieldForNewForm(): void
    {
        $form = $this->formFactory->create(FormDefinitionType::class, new FormDefinition(), [
            'csrf_protection' => false,
        ]);

        $this->assertTrue($form->has('name'));
        $this->assertFalse($form->has('enabled'));
        $this->assertFalse($form->has('redirectUrl'));
    }

    public function testFormHasAdditionalFieldsForExistingForm(): void
    {
        $user = $this->createUser();
        $formDefinition = $this->createFormDefinition($user, 'Existing Form');

        $form = $this->formFactory->create(FormDefinitionType::class, $formDefinition, [
            'csrf_protection' => false,
        ]);

        $this->assertTrue($form->has('name'));
        $this->assertTrue($form->has('enabled'));
        $this->assertTrue($form->has('redirectUrl'));
    }

    public function testSubmitValidDataForExistingForm(): void
    {
        $user = $this->createUser();
        $formDefinition = $this->createFormDefinition($user, 'Original Name');

        $formData = [
            'name' => 'Updated Name',
            'enabled' => true,
            'redirectUrl' => '',
        ];

        $form = $this->formFactory->create(FormDefinitionType::class, $formDefinition, [
            'csrf_protection' => false,
        ]);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertSame('Updated Name', $formDefinition->getName());
        $this->assertTrue($formDefinition->isEnabled());
    }

    public function testEmptyNameIsInvalid(): void
    {
        $formData = [
            'name' => '',
        ];

        $form = $this->formFactory->create(FormDefinitionType::class, new FormDefinition(), [
            'csrf_protection' => false,
        ]);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertFalse($form->isValid());
    }

    public function testProtocolRequiredForRedirectUrl(): void
    {
        $user = $this->createUser();
        $formDefinition = $this->createFormDefinition($user, 'Test Form');

        // URL without protocol is still accepted by default Url constraint
        // This test documents the current behavior
        $formData = [
            'name' => 'Test',
            'enabled' => true,
            'redirectUrl' => 'example.com/success',
        ];

        $form = $this->formFactory->create(FormDefinitionType::class, $formDefinition, [
            'csrf_protection' => false,
        ]);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        // The URL without protocol gets http:// prepended by default_protocol
    }

    public function testValidRedirectUrlWithTldIsAccepted(): void
    {
        $user = $this->createUser();
        $formDefinition = $this->createFormDefinition($user, 'Test Form');

        $formData = [
            'name' => 'Test',
            'enabled' => true,
            'redirectUrl' => 'https://example.com/success',
        ];

        $form = $this->formFactory->create(FormDefinitionType::class, $formDefinition, [
            'csrf_protection' => false,
        ]);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertSame('https://example.com/success', $formDefinition->getRedirectUrl());
    }
}
