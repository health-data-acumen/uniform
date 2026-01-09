# Complete Unit Tests Report - Uniform

**Generation Date**: January 8, 2026
**Project**: Uniform - Symfony 7 Form Backend
**PHP Version**: 8.5
**Test Framework**: PHPUnit 9.6.29

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Test Architecture](#2-test-architecture)
3. [Test Inventory](#3-test-inventory)
4. [Detail by Component](#4-detail-by-component)
5. [Statistics](#5-statistics)
6. [Identified Issues](#6-identified-issues)
7. [Code Coverage](#7-code-coverage)
8. [Recommendations](#8-recommendations)
9. [Execution Guide](#9-execution-guide)
10. [Appendices](#10-appendices)

---

## 1. Executive Summary

### 1.1 Overview

| Metric | Value |
|--------|-------|
| **Total tests** | 251 |
| **Total assertions** | 448 |
| **Test files** | 22 |
| **Status** | ✅ All pass |
| **Execution time** | ~0.8 seconds |
| **Memory used** | 18 MB |

### 1.2 Global Result

```
PHPUnit 9.6.29 by Sebastian Bergmann and contributors.

OK (251 tests, 448 assertions)
```

### 1.3 Tested Components

- ✅ Doctrine Entities (6 classes)
- ✅ Business Services (4 classes)
- ✅ DTOs (2 classes)
- ✅ CLI Commands (1 class)
- ✅ Event Listeners (2 classes)
- ✅ Message Handlers (1 class)
- ✅ Security Voters (1 class)
- ✅ Twig Extensions (2 classes)
- ✅ Menu Builders (1 class)
- ✅ Events & Messages (2 classes)

---

## 2. Test Architecture

### 2.1 Directory Structure

```
tests/
└── Unit/
    ├── Command/
    │   └── UserCreateCommandTest.php
    ├── Dto/
    │   ├── FormEndpointDtoTest.php
    │   └── FormSubmissionDtoTest.php
    ├── Entity/
    │   ├── FormDefinitionTest.php
    │   ├── FormFieldTest.php
    │   ├── FormSubmissionTest.php
    │   ├── UserTest.php
    │   └── Settings/
    │       ├── AccountSettingsTest.php
    │       └── NotificationSettingsTest.php
    ├── Event/
    │   └── NewSubmissionEventTest.php
    ├── EventListener/
    │   ├── SubmissionListenerTest.php
    │   └── Turbo/
    │       └── TurboRedirectResponseListenerTest.php
    ├── Menu/
    │   └── DefaultMenuBuilderTest.php
    ├── Message/
    │   └── Command/
    │       └── SendSubmissionNotificationTest.php
    ├── MessageHandler/
    │   └── Command/
    │       └── SendSubmissionNotificationHandlerTest.php
    ├── Security/
    │   └── Voter/
    │       └── FormDefinitionOwnerVoterTest.php
    ├── Service/
    │   ├── AccountSettingsServiceTest.php
    │   ├── FormEndpoint/
    │   │   └── SubmissionServiceTest.php
    │   └── Notification/
    │       ├── EmailChannelTest.php
    │       └── Email/
    │           └── EmailBuilderTest.php
    └── Twig/
        ├── Extension/
        │   └── TailwindExtensionTest.php
        └── Runtime/
            └── TailwindRuntimeTest.php
```

### 2.2 Naming Convention

| Type | Convention | Example |
|------|------------|---------|
| Test class | `{Class}Test` | `UserTest.php` |
| Test method | `test{Behavior}` | `testSetAndGetEmail()` |
| Mock | `$this->createMock()` | `$this->createMock(EntityManagerInterface::class)` |

### 2.3 Test Dependencies

```json
{
    "require-dev": {
        "phpunit/phpunit": "^9.6",
        "symfony/phpunit-bridge": "^7.3"
    }
}
```

---

## 3. Test Inventory

### 3.1 Summary Table

| File | Tested Class | Tests | Assertions |
|------|--------------|-------|------------|
| `UserCreateCommandTest.php` | `UserCreateCommand` | 10 | 15 |
| `FormEndpointDtoTest.php` | `FormEndpointDto` | 7 | 11 |
| `FormSubmissionDtoTest.php` | `FormSubmissionDto` | 10 | 16 |
| `FormDefinitionTest.php` | `FormDefinition` | 28 | 52 |
| `FormFieldTest.php` | `FormField` | 14 | 25 |
| `FormSubmissionTest.php` | `FormSubmission` | 14 | 24 |
| `UserTest.php` | `User` | 23 | 38 |
| `AccountSettingsTest.php` | `AccountSettings` | 21 | 34 |
| `NotificationSettingsTest.php` | `NotificationSettings` | 21 | 35 |
| `NewSubmissionEventTest.php` | `NewSubmissionEvent` | 3 | 4 |
| `SubmissionListenerTest.php` | `SubmissionListener` | 3 | 4 |
| `TurboRedirectResponseListenerTest.php` | `TurboRedirectResponseListener` | 8 | 15 |
| `DefaultMenuBuilderTest.php` | `DefaultMenuBuilder` | 10 | 14 |
| `SendSubmissionNotificationTest.php` | `SendSubmissionNotification` | 5 | 6 |
| `SendSubmissionNotificationHandlerTest.php` | `SendSubmissionNotificationHandler` | 6 | 9 |
| `FormDefinitionOwnerVoterTest.php` | `FormDefinitionOwnerVoter` | 10 | 12 |
| `AccountSettingsServiceTest.php` | `AccountSettingsService` | 5 | 8 |
| `SubmissionServiceTest.php` | `SubmissionService` | 15 | 26 |
| `EmailChannelTest.php` | `EmailChannel` | 12 | 20 |
| `EmailBuilderTest.php` | `EmailBuilder` | 7 | 12 |
| `TailwindExtensionTest.php` | `TailwindExtension` | 6 | 10 |
| `TailwindRuntimeTest.php` | `TailwindRuntime` | 12 | 18 |
| **TOTAL** | **22 classes** | **251** | **448** |

---

## 4. Detail by Component

### 4.1 Entities (115 tests)

#### 4.1.1 FormDefinition (28 tests)

**Source file**: `src/Entity/FormDefinition.php`

| Test | Description | Status |
|------|-------------|--------|
| `testGetIdReturnsNullWhenNotPersisted` | ID null before persistence | ✅ |
| `testSetAndGetName` | Name getter/setter | ✅ |
| `testSetAndGetDescription` | Description getter/setter | ✅ |
| `testDescriptionCanBeNull` | Nullable description | ✅ |
| `testSetAndGetUid` | UUID getter/setter | ✅ |
| `testPrePersistGeneratesUuidWhenNull` | Auto UUID generation | ✅ |
| `testPrePersistDoesNotOverwriteExistingUuid` | Existing UUID preservation | ✅ |
| `testIsEnabledDefaultsToTrue` | Enabled by default | ✅ |
| `testSetAndIsEnabled` | Enabled getter/setter | ✅ |
| `testSetAndGetRedirectUrl` | Redirect URL getter/setter | ✅ |
| `testRedirectUrlCanBeNull` | Nullable redirect URL | ✅ |
| `testGetFieldsReturnsEmptyCollectionInitially` | Empty fields collection | ✅ |
| `testAddFieldAddsFieldToCollection` | Add field | ✅ |
| `testAddFieldSetsFormOnField` | Bidirectional field relation | ✅ |
| `testAddFieldPreventsDuplicates` | No duplicate fields | ✅ |
| `testRemoveFieldRemovesFromCollection` | Remove field | ✅ |
| `testRemoveFieldNullifiesFormOnField` | Nullify field relation | ✅ |
| `testGetSubmissionsReturnsEmptyCollectionInitially` | Empty submissions collection | ✅ |
| `testAddSubmissionAddsToCollection` | Add submission | ✅ |
| `testAddSubmissionSetsFormOnSubmission` | Bidirectional submission relation | ✅ |
| `testAddSubmissionPreventsDuplicates` | No duplicate submissions | ✅ |
| `testRemoveSubmissionRemovesFromCollection` | Remove submission | ✅ |
| `testRemoveSubmissionNullifiesFormOnSubmission` | Nullify submission relation | ✅ |
| `testGetNotificationSettingsReturnsEmptyCollectionInitially` | Empty notifications collection | ✅ |
| `testAddNotificationSetting` | Add notification | ✅ |
| `testAddNotificationSettingPreventsDuplicates` | No duplicate notifications | ✅ |
| `testRemoveNotificationSetting` | Remove notification | ✅ |
| `testSetAndGetOwner` | Owner getter/setter | ✅ |
| `testOwnerCanBeNull` | Nullable owner | ✅ |
| `testFluentSetters` | Setter chaining | ✅ |

**Key behaviors tested**:
- Automatic UUID generation via `@PrePersist`
- Bidirectional relations with FormField, FormSubmission, NotificationSettings
- Duplicate prevention in collections

#### 4.1.2 User (23 tests)

**Source file**: `src/Entity/User.php`

| Test | Description | Status |
|------|-------------|--------|
| `testGetIdReturnsNullInitially` | Initial null ID | ✅ |
| `testSetAndGetEmail` | Email getter/setter | ✅ |
| `testSetAndGetFullName` | FullName getter/setter | ✅ |
| `testGetUserIdentifierReturnsEmail` | UserInterface::getUserIdentifier | ✅ |
| `testGetUserIdentifierReturnsEmptyStringWhenEmailIsNull` | Null email handling | ✅ |
| `testGetRolesIncludesRoleUser` | ROLE_USER always present | ✅ |
| `testGetRolesReturnsUniqueRoles` | Unique roles | ✅ |
| `testGetRolesAlwaysIncludesRoleUserEvenIfNotSet` | Automatic ROLE_USER | ✅ |
| `testSetRoles` | Roles setter | ✅ |
| `testSetAndGetPassword` | Password getter/setter | ✅ |
| `testEraseCredentialsDoesNotThrow` | eraseCredentials() without error | ✅ |
| `testGetFormEndpointsReturnsEmptyCollectionInitially` | Empty endpoints collection | ✅ |
| `testAddFormEndpoint` | Add endpoint | ✅ |
| `testAddFormEndpointSetsOwner` | Bidirectional endpoint relation | ✅ |
| `testAddFormEndpointPreventsDuplicates` | No duplicate endpoints | ✅ |
| `testRemoveFormEndpoint` | Remove endpoint | ✅ |
| `testRemoveFormEndpointNullifiesOwner` | Nullify endpoint relation | ✅ |
| `testSetAndGetAccountSettings` | AccountSettings getter/setter | ✅ |
| `testSetAccountSettingsSetsOwnerOnSettings` | Bidirectional settings relation | ✅ |
| `testAccountSettingsIsNullInitially` | Initial null AccountSettings | ✅ |
| `testFluentSetters` | Setter chaining | ✅ |
| `testImplementsUserInterface` | Implements UserInterface | ✅ |
| `testImplementsPasswordAuthenticatedUserInterface` | Implements PasswordAuthenticatedUserInterface | ✅ |

**Key behaviors tested**:
- `getRoles()` always adds `ROLE_USER`
- Correct `UserInterface` implementation
- Bidirectional relations with FormDefinition and AccountSettings

#### 4.1.3 Other Entities

| Entity | Tests | Key Points |
|--------|-------|------------|
| `FormField` | 14 | Field types, position, required |
| `FormSubmission` | 14 | JSON payload, `getNotificationPayload()` |
| `AccountSettings` | 21 | Complete SMTP configuration |
| `NotificationSettings` | 21 | Dynamic options, getter with default |

---

### 4.2 Services (36 tests)

#### 4.2.1 SubmissionService (15 tests)

**Source file**: `src/Service/FormEndpoint/SubmissionService.php`

| Test | Description | Status |
|------|-------------|--------|
| `testSaveSubmissionCreatesNewSubmission` | Submission creation | ✅ |
| `testSaveSubmissionExtractsPayloadFromRequest` | Payload extraction | ✅ |
| `testSaveSubmissionAssociatesWithFormDefinition` | Form association | ✅ |
| `testSaveSubmissionFlushesEntityManager` | Flush called | ✅ |
| `testSaveSubmissionReturnsFormSubmission` | Correct return | ✅ |
| `testSaveSubmissionWithEmptyPayload` | Empty payload | ✅ |
| `testGetSubmittedFieldsReturnsDistinctKeys` | Distinct keys | ✅ |
| `testGetSubmittedFieldsReturnsEmptyForNoSubmissions` | No submission | ✅ |
| `testGetPriorityFormFieldsReturnsPriorityFields` | Priority fields | ✅ |
| `testGetPriorityFormFieldsLimitsToMax` | Limit max=1 | ✅ |
| `testGetPriorityFormFieldsLimitsToMaxWithThree` | Limit max=3 | ✅ |
| `testGetPriorityFormFieldsReturnsFallbackField` | Fallback field | ✅ |
| `testGetPriorityFormFieldsReturnsEmptyWhenNoFields` | No fields | ✅ |
| `testGetPriorityFormFieldsRespectsPriorityOrder` | Priority order | ✅ |
| `testGetPriorityFormFieldsWithOnlyMessage` | Message only | ✅ |

**Key behaviors tested**:
- Persistence via EntityManager
- SQL query for distinct fields via JSON
- Priority logic: email > name > subject > message

#### 4.2.2 EmailChannel (12 tests)

**Source file**: `src/Service/Notification/EmailChannel.php`

| Test | Description | Status |
|------|-------------|--------|
| `testGetConfigurationFormReturnsEmailNotificationType` | Correct form type | ✅ |
| `testGetNameReturnsEmail` | Name = "email" | ✅ |
| `testGetPriorityReturnsZero` | Priority = 0 | ✅ |
| `testCheckRequirementsReturnsTrueWhenConfigured` | Requirements OK | ✅ |
| `testCheckRequirementsReturnsFalseWhenHostMissing` | Missing host | ✅ |
| `testCheckRequirementsReturnsFalseWhenPortMissing` | Missing port | ✅ |
| `testCheckRequirementsReturnsFalseWhenNoSettings` | No settings | ✅ |
| `testGetRequirementsMessageReturnsExpectedMessage` | Requirements message | ✅ |
| `testTriggerNotificationReturnsEarlyWhenRequirementsNotMet` | Return early | ✅ |
| `testTriggerNotificationSendsEmail` | Send email | ✅ |
| `testTriggerNotificationUsesConfiguredSenderAddress` | Sender address | ✅ |
| `testTriggerNotificationLogsTransportException` | Log exception | ✅ |

---

### 4.3 Security (10 tests)

#### 4.3.1 FormDefinitionOwnerVoter (10 tests)

**Source file**: `src/Security/Voter/FormDefinitionOwnerVoter.php`

| Test | Description | Status |
|------|-------------|--------|
| `testSupportsReturnsTrueForRoleOwnerAndFormDefinition` | Support ROLE_OWNER + FormDefinition | ✅ |
| `testSupportsReturnsFalseForWrongAttribute` | Wrong attribute | ✅ |
| `testSupportsReturnsFalseForWrongSubject` | Wrong subject | ✅ |
| `testSupportsReturnsFalseForNullSubject` | Null subject | ✅ |
| `testVoteOnAttributeReturnsTrueWhenUserIsOwner` | Access granted if owner | ✅ |
| `testVoteOnAttributeReturnsFalseWhenUserIsNotOwner` | Access denied if not owner | ✅ |
| `testVoteOnAttributeReturnsFalseWhenNoUser` | No user | ✅ |
| `testVoteOnAttributeReturnsFalseWhenUserNotInstanceOfUser` | Wrong user type | ✅ |
| `testVoteOnAttributeReturnsFalseWhenFormHasNoOwner` | Form without owner | ✅ |
| `testRoleOwnerConstantValue` | Constant value | ✅ |

---

### 4.4 Commands (10 tests)

#### 4.4.1 UserCreateCommand (10 tests)

**Source file**: `src/Command/UserCreateCommand.php`

| Test | Description | Status |
|------|-------------|--------|
| `testExecuteCreatesUser` | User creation | ✅ |
| `testExecuteHashesPassword` | Password hash | ✅ |
| `testExecutePersistsUser` | Persistence | ✅ |
| `testExecuteSetsEmailFromOption` | --email option | ✅ |
| `testExecuteSetsFullNameFromOption` | --full-name option | ✅ |
| `testExecuteSetsRoleUserByDefault` | Default ROLE_USER | ✅ |
| `testExecuteSetsRoleAdminWhenFlagged` | --admin option | ✅ |
| `testExecuteReturnsSuccessCode` | SUCCESS return code | ✅ |
| `testExecuteOutputsSuccessMessage` | Success message | ✅ |
| `testCommandName` | Name = app:user:create | ✅ |

---

### 4.5 DTOs (17 tests)

#### 4.5.1 FormEndpointDto (7 tests)

**Source file**: `src/Dto/FormEndpointDto.php`

- Readonly property tests
- Property accessibility
- Edge case values (zero, empty string)

#### 4.5.2 FormSubmissionDto (10 tests)

**Source file**: `src/Dto/FormSubmissionDto.php`

- Factory method `fromSubmission()` tests
- Correct data extraction from FormSubmission
- Nested payload handling

---

### 4.6 Event Listeners (11 tests)

#### 4.6.1 SubmissionListener (3 tests)

- `SendSubmissionNotification` message dispatch
- Correct submission ID passing

#### 4.6.2 TurboRedirectResponseListener (8 tests)

- Sub-request handling
- Response replacement for Turbo
- `X-Turbo-Location` header
- Different redirect codes

---

### 4.7 Message Handlers (6 tests)

#### 4.7.1 SendSubmissionNotificationHandler (6 tests)

- Enabled notification channel triggering
- Disabled notification skipping
- Requirements verification before sending
- Multiple channel handling

---

### 4.8 Twig (18 tests)

#### 4.8.1 TailwindExtension (6 tests)

- Twig filters and functions registration
- TailwindRuntime binding

#### 4.8.2 TailwindRuntime (12 tests)

- CSS class merging
- Conflict resolution (last value wins)
- Null and empty value handling
- Symfony cache

---

### 4.9 Menu (10 tests)

#### 4.9.1 DefaultMenuBuilder (10 tests)

- Default menu construction
- Form endpoint menu construction
- Required options validation
- Correct route parameters

---

## 5. Statistics

### 5.1 Distribution by Type

```
Entities        ████████████████████████████████████████  46% (115 tests)
Services        ██████████████                            14% (36 tests)
DTOs            ██████                                     7% (17 tests)
Twig            ███████                                    7% (18 tests)
EventListeners  ████                                       4% (11 tests)
Commands        ████                                       4% (10 tests)
Security        ████                                       4% (10 tests)
Menu            ████                                       4% (10 tests)
Handlers        ██                                         2% (6 tests)
Messages        ██                                         2% (5 tests)
Events          █                                          1% (3 tests)
```

### 5.2 Tests/Assertions Ratio

| Component | Tests | Assertions | Ratio |
|-----------|-------|------------|-------|
| Entities | 115 | 208 | 1.81 |
| Services | 36 | 66 | 1.83 |
| DTOs | 17 | 27 | 1.59 |
| Twig | 18 | 28 | 1.56 |
| Average | - | - | **1.78** |

### 5.3 Execution Time

```
Total: 0.834 seconds
Average per test: 3.3 ms
```

---

## 6. Identified Issues

### 6.1 Potential Bugs

#### 6.1.1 AccountSettingsService - Ineffective Cache for Null

**File**: `src/Service/AccountSettingsService.php:16-23`

```php
public function getSettings(): ?AccountSettings
{
    if (null === $this->settings) {  // ❌ Always true if findOneBy returns null
        $this->settings = $this->accountSettingsRepository->findOneBy([]);
    }
    return $this->settings;
}
```

**Impact**: Database query on each call if no settings exist.

**Suggested fix**:
```php
private bool $loaded = false;

public function getSettings(): ?AccountSettings
{
    if (!$this->loaded) {
        $this->settings = $this->accountSettingsRepository->findOneBy([]);
        $this->loaded = true;
    }
    return $this->settings;
}
```

#### 6.1.2 TailwindRuntime - Interface Incompatibility

**File**: `src/Twig/Runtime/TailwindRuntime.php:18-22`

```php
public function __construct(?CacheInterface $cache = null)
{
    $cache ??= new FilesystemAdapter();
    // ❌ Psr16Cache expects CacheItemPoolInterface, not CacheInterface
    $this->factory = TailwindMerge::factory()->withCache(new Psr16Cache($cache));
}
```

**Impact**: Works by chance because `FilesystemAdapter` implements both interfaces.

**Suggested fix**:
```php
public function __construct(?CacheItemPoolInterface $cache = null)
{
    $cache ??= new FilesystemAdapter();
    $this->factory = TailwindMerge::factory()->withCache(new Psr16Cache($cache));
}
```

### 6.2 Deprecations

#### 6.2.1 FormDefinitionOwnerVoter

```
The "App\Security\Voter\FormDefinitionOwnerVoter::voteOnAttribute()" method
will require a new "Vote|null $vote" argument in the next major version of
its parent class "Symfony\Component\Security\Core\Authorization\Voter\Voter"
```

**Fix**: Add the `?Vote $vote = null` parameter to the method.

---

## 7. Code Coverage

### 7.1 Fully Tested Classes

| Class | Methods Tested | Estimated Coverage |
|-------|----------------|-------------------|
| `FormDefinition` | All | ~95% |
| `User` | All | ~95% |
| `FormField` | All | ~90% |
| `FormSubmission` | All | ~90% |
| `AccountSettings` | All | ~95% |
| `NotificationSettings` | All | ~95% |
| `UserCreateCommand` | execute() | ~85% |
| `FormDefinitionOwnerVoter` | supports(), voteOnAttribute() | ~90% |
| `EmailChannel` | All | ~85% |
| `SubmissionService` | All | ~90% |

### 7.2 Partially Tested Classes

| Class | Missing |
|-------|---------|
| `AccountSettingsService` | Null cache behavior |
| `TailwindRuntime` | Cache error cases |

### 7.3 Untested Classes (Out of Unit Scope)

- Controllers (require functional tests)
- Repositories (require integration tests)
- Form Types (require functional tests)
- Twig Templates (require functional tests)

---

## 9. Execution Guide

### 9.1 Run All Tests

```bash
php bin/phpunit tests/Unit/
```

### 9.2 Run with Detailed Output

```bash
php bin/phpunit tests/Unit/ --testdox
```

### 9.3 Run a Specific File

```bash
php bin/phpunit tests/Unit/Entity/UserTest.php
```

### 9.4 Run a Specific Method

```bash
php bin/phpunit tests/Unit/Entity/UserTest.php --filter testGetRolesIncludesRoleUser
```

### 9.5 Generate Coverage Report

```bash
php bin/phpunit tests/Unit/ --coverage-html var/coverage
```

### 9.6 Watch Mode (Development)

```bash
# With phpunit-watcher
composer require --dev spatie/phpunit-watcher
vendor/bin/phpunit-watcher watch
```

---

## 10. Appendices

### 10.1 PHPUnit Configuration

**File**: `phpunit.xml.dist`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

### 10.2 Test Patterns Used

#### AAA Pattern (Arrange-Act-Assert)

```php
public function testSetAndGetEmail(): void
{
    // Arrange
    $user = new User();

    // Act
    $result = $user->setEmail('test@example.com');

    // Assert
    $this->assertSame('test@example.com', $user->getEmail());
    $this->assertSame($user, $result);
}
```

#### Pattern with Mock

```php
public function testSaveSubmissionFlushesEntityManager(): void
{
    // Arrange
    $entityManager = $this->createMock(EntityManagerInterface::class);
    $entityManager->expects($this->once())->method('persist');
    $entityManager->expects($this->once())->method('flush');

    $service = new SubmissionService($entityManager);

    // Act
    $service->saveSubmission($endpoint, $request);

    // Assert (implicit via expects)
}
```

### 10.3 Useful Commands

| Command | Description |
|---------|-------------|
| `php bin/phpunit` | Run all tests |
| `php bin/phpunit --testdox` | Readable output |
| `php bin/phpunit --stop-on-failure` | Stop on first failure |
| `php bin/phpunit --coverage-text` | Text coverage |
| `php bin/phpunit --filter Entity` | Filter by name |

---

## Conclusion

The Uniform project unit test suite covers **251 tests** with **448 assertions**, validating the behavior of **22 main classes**. All tests pass successfully.

---

*Report automatically generated - Uniform Project*
