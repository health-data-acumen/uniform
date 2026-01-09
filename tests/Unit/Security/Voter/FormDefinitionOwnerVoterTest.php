<?php

namespace App\Tests\Unit\Security\Voter;

use App\Entity\FormDefinition;
use App\Entity\User;
use App\Security\Voter\FormDefinitionOwnerVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class FormDefinitionOwnerVoterTest extends TestCase
{
    private FormDefinitionOwnerVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new FormDefinitionOwnerVoter();
    }

    public function testSupportsReturnsTrueForRoleOwnerAndFormDefinition(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $formDefinition = new FormDefinition();

        $result = $this->voter->vote($token, $formDefinition, [FormDefinitionOwnerVoter::ROLE_OWNER]);

        $this->assertNotSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testSupportsReturnsFalseForWrongAttribute(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $formDefinition = new FormDefinition();

        $result = $this->voter->vote($token, $formDefinition, ['WRONG_ATTRIBUTE']);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testSupportsReturnsFalseForWrongSubject(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $wrongSubject = new \stdClass();

        $result = $this->voter->vote($token, $wrongSubject, [FormDefinitionOwnerVoter::ROLE_OWNER]);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testSupportsReturnsFalseForNullSubject(): void
    {
        $token = $this->createMock(TokenInterface::class);

        $result = $this->voter->vote($token, null, [FormDefinitionOwnerVoter::ROLE_OWNER]);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testVoteOnAttributeReturnsTrueWhenUserIsOwner(): void
    {
        $user = new User();
        $user->setEmail('owner@example.com');

        $formDefinition = new FormDefinition();
        $formDefinition->setOwner($user);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $result = $this->voter->vote($token, $formDefinition, [FormDefinitionOwnerVoter::ROLE_OWNER]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testVoteOnAttributeReturnsFalseWhenUserIsNotOwner(): void
    {
        $owner = new User();
        $owner->setEmail('owner@example.com');

        $otherUser = new User();
        $otherUser->setEmail('other@example.com');

        $formDefinition = new FormDefinition();
        $formDefinition->setOwner($owner);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($otherUser);

        $result = $this->voter->vote($token, $formDefinition, [FormDefinitionOwnerVoter::ROLE_OWNER]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testVoteOnAttributeReturnsFalseWhenNoUser(): void
    {
        $formDefinition = new FormDefinition();

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(null);

        $result = $this->voter->vote($token, $formDefinition, [FormDefinitionOwnerVoter::ROLE_OWNER]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testVoteOnAttributeReturnsFalseWhenUserNotInstanceOfUser(): void
    {
        $formDefinition = new FormDefinition();

        // Create a mock that implements UserInterface but is NOT our User entity
        $wrongUserType = $this->createMock(\Symfony\Component\Security\Core\User\UserInterface::class);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($wrongUserType);

        $result = $this->voter->vote($token, $formDefinition, [FormDefinitionOwnerVoter::ROLE_OWNER]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testVoteOnAttributeReturnsFalseWhenFormHasNoOwner(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');

        $formDefinition = new FormDefinition();

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $result = $this->voter->vote($token, $formDefinition, [FormDefinitionOwnerVoter::ROLE_OWNER]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testRoleOwnerConstantValue(): void
    {
        $this->assertSame('ROLE_OWNER', FormDefinitionOwnerVoter::ROLE_OWNER);
    }
}
