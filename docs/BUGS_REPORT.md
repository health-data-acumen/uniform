# Identified Bugs Report - Uniform

**Generation Date**: January 9, 2026
**Project**: Uniform - Symfony 7 Form Backend
**Detection Method**: Automated Tests (Unit, Integration, Functional)

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Bugs Detected by Unit Tests](#2-bugs-detected-by-unit-tests)
3. [Bugs Detected by Integration/Functional Tests](#3-bugs-detected-by-integrationfunctional-tests)
4. [Prioritization and Correction Plan](#4-prioritization-and-correction-plan)
5. [Correction Checklist](#5-correction-checklist)

---

## 1. Executive Summary

### 1.1 Overview

| Severity | Count | % |
|----------|-------|---|
| **CRITICAL** | 1 | 14% |
| **Medium** | 2 | 29% |
| **Low** | 4 | 57% |
| **TOTAL** | **7** | 100% |

### 1.2 By Detection Source

| Source | Bugs |
|--------|------|
| Unit Tests | 3 |
| Integration Tests | 1 |
| Functional Tests | 3 |
| **TOTAL** | **7** |

### 1.3 Global Status

| Bug | Severity | Status |
|-----|----------|--------|
| Ineffective AccountSettingsService cache | Low | ❌ Not fixed |
| TailwindRuntime type incompatibility | Low | ❌ Not fixed |
| FormDefinitionOwnerVoter deprecation | Low | ❌ Not fixed |
| Login redirect inconsistency | Low | ❌ Not fixed |
| Multi-tenant SMTP bug | **CRITICAL** | ❌ Not fixed |
| Missing null check in Handler | Medium | ❌ Not fixed |
| Unhandled exception in EmailChannel | Medium | ❌ Not fixed |

---

## 2. Bugs Detected by Unit Tests

### 2.1 BUG-001: Ineffective cache for null value

| Attribute | Value |
|-----------|-------|
| **ID** | BUG-001 |
| **Severity** | Low |
| **File** | `src/Service/AccountSettingsService.php` |
| **Line** | 16-23 |
| **Detected by** | `tests/Unit/Service/AccountSettingsServiceTest.php` |

#### Description

The service's cache mechanism doesn't work correctly when `findOneBy()` returns `null`. The condition `null === $this->settings` will always be true, causing a database query on each call.

#### Problematic Code

```php
public function getSettings(): ?AccountSettings
{
    if (null === $this->settings) {  // ❌ Always true if findOneBy returns null
        $this->settings = $this->accountSettingsRepository->findOneBy([]);
    }
    return $this->settings;
}
```

#### Impact

- Degraded performance: SQL query on each call if no settings exist
- Unnecessary database load

#### Proposed Fix

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

#### Validation Test

```php
public function testGetSettingsCachesNullValue(): void
{
    $repository = $this->createMock(AccountSettingsRepository::class);
    $repository->expects($this->once()) // Called only once
        ->method('findOneBy')
        ->willReturn(null);

    $service = new AccountSettingsService($repository);

    $service->getSettings(); // First call
    $service->getSettings(); // Second call - should not query the database
}
```

---

### 2.2 BUG-002: CacheInterface type incompatibility

| Attribute | Value |
|-----------|-------|
| **ID** | BUG-002 |
| **Severity** | Low |
| **File** | `src/Twig/Runtime/TailwindRuntime.php` |
| **Line** | 18-22 |
| **Detected by** | `tests/Unit/Twig/Runtime/TailwindRuntimeTest.php` |

#### Description

The constructor accepts `CacheInterface` but `Psr16Cache` expects `CacheItemPoolInterface`. The code works by coincidence because `FilesystemAdapter` implements both interfaces.

#### Problematic Code

```php
public function __construct(?CacheInterface $cache = null)
{
    $cache ??= new FilesystemAdapter();
    $this->factory = TailwindMerge::factory()->withCache(new Psr16Cache($cache));
    //                                                   ❌ Expects CacheItemPoolInterface
}
```

#### Impact

- Fragile code: will only work with implementations that support both interfaces
- Potential error if another type of cache is injected

#### Proposed Fix

```php
use Psr\Cache\CacheItemPoolInterface;

public function __construct(?CacheItemPoolInterface $cache = null)
{
    $cache ??= new FilesystemAdapter();
    $this->factory = TailwindMerge::factory()->withCache(new Psr16Cache($cache));
}
```

---

### 2.3 BUG-003: Symfony 7 Deprecation - Voter

| Attribute | Value |
|-----------|-------|
| **ID** | BUG-003 |
| **Severity** | Low |
| **File** | `src/Security/Voter/FormDefinitionOwnerVoter.php` |
| **Line** | 19 |
| **Detected by** | `tests/Unit/Security/Voter/FormDefinitionOwnerVoterTest.php` |

#### Description

The `voteOnAttribute()` method doesn't have the new `?Vote $vote` parameter required by Symfony 7.

#### Problematic Code

```php
protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
//                                                                    ❌ Missing ?Vote $vote = null
```

#### PHPUnit Warning

```
The "App\Security\Voter\FormDefinitionOwnerVoter::voteOnAttribute()" method
will require a new "Vote|null $vote" argument in the next major version of
"Symfony\Component\Security\Core\Authorization\Voter\Voter"
```

#### Impact

- Deprecation warning on each test execution
- Incompatibility with Symfony 8

#### Proposed Fix

```php
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

protected function voteOnAttribute(
    string $attribute,
    mixed $subject,
    TokenInterface $token,
    ?Vote $vote = null
): bool
{
    // ... existing code
}
```

---

## 3. Bugs Detected by Integration/Functional Tests

### 3.1 BUG-004: Login redirect inconsistency

| Attribute | Value |
|-----------|-------|
| **ID** | BUG-004 |
| **Severity** | Low |
| **Files** | `src/Controller/SecurityController.php:16` + `config/packages/security.yaml:23` |
| **Detected by** | `tests/Functional/Controller/SecurityControllerTest.php` |

#### Description

Inconsistent behavior between the redirect of an already authenticated user visiting `/login` and the redirect after a successful login.

#### Problematic Code

```php
// SecurityController.php - Already logged-in user visits /login
if ($this->isGranted('IS_AUTHENTICATED')) {
    return $this->redirectToRoute('app_default_index');  // → Homepage "/"
}
```

```yaml
# security.yaml - After successful login
form_login:
    default_target_path: app_dashboard_index  # → Dashboard "/dashboard"
```

#### Impact

- Inconsistent UX: different destinations depending on the scenario
- User confusion

#### Proposed Fix

```php
// SecurityController.php
if ($this->isGranted('IS_AUTHENTICATED')) {
    return $this->redirectToRoute('app_dashboard_index');  // Consistent with security.yaml
}
```

---

### 3.2 BUG-005: Multi-tenant SMTP Bug (CRITICAL)

| Attribute | Value |
|-----------|-------|
| **ID** | BUG-005 |
| **Severity** | **CRITICAL** |
| **File** | `src/Service/AccountSettingsService.php:19` |
| **Detected by** | `tests/Integration/Repository/UserRepositoryTest.php` + Code Analysis |

#### Description

`AccountSettingsService::getSettings()` uses `findOneBy([])` which returns the first `AccountSettings` found in the database, without filtering by owner. In a multi-user context, a user could use another user's SMTP settings.

#### Problematic Code

```php
// AccountSettingsService.php
public function getSettings(): ?AccountSettings
{
    if (null === $this->settings) {
        $this->settings = $this->accountSettingsRepository->findOneBy([]);
        //                                                          ❌ No owner filter
    }
    return $this->settings;
}

// EmailChannel.php - Uses settings without verification
$config = $this->settingsManager->getSettings();
$sender = new Address(
    address: $config->getEmailFromAddress(),  // ❌ Could be another user's settings
    name: $config->getEmailFromName()
);
```

#### Impact

- **Security**: SMTP credentials leak from one user to another
- **Integrity**: Emails sent with wrong sender
- **Confidentiality**: Access to another account's settings

#### Exploitation Scenario

1. User A configures their SMTP settings (host, user, password)
2. User B creates a form with email notification
3. When B receives a submission, the system uses A's SMTP settings
4. B can potentially see A's credentials in error logs

#### Proposed Fix

```php
// AccountSettingsService.php
class AccountSettingsService
{
    public function __construct(
        private readonly AccountSettingsRepository $accountSettingsRepository,
        private readonly Security $security  // Add injection
    ) {}

    public function getSettings(): ?AccountSettings
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return null;
        }

        return $user->getAccountSettings();
    }
}

// Or for EmailChannel, go through the form owner
public function getSettingsForForm(FormDefinition $form): ?AccountSettings
{
    return $form->getOwner()?->getAccountSettings();
}
```

#### Validation Tests

```php
public function testGetSettingsReturnsCurrentUserSettings(): void
{
    $userA = $this->createUser('a@test.com');
    $userB = $this->createUser('b@test.com');

    $settingsA = $this->createAccountSettings($userA);
    $settingsA->setSmtpHost('smtp.usera.com');

    $settingsB = $this->createAccountSettings($userB);
    $settingsB->setSmtpHost('smtp.userb.com');

    $this->loginUser($userB);

    $service = self::getContainer()->get(AccountSettingsService::class);
    $settings = $service->getSettings();

    $this->assertSame('smtp.userb.com', $settings->getSmtpHost());
}
```

---

### 3.3 BUG-006: Missing null check in Handler

| Attribute | Value |
|-----------|-------|
| **ID** | BUG-006 |
| **Severity** | Medium |
| **File** | `src/MessageHandler/Command/SendSubmissionNotificationHandler.php` |
| **Line** | 30-31 |
| **Detected by** | Analysis during functional tests |

#### Description

The handler doesn't check if the submission exists before calling methods on it. If the submission was deleted between the message dispatch and its processing (async), an exception will be thrown.

#### Problematic Code

```php
public function __invoke(SendSubmissionNotification $message): void
{
    $submission = $this->submissionRepository->find($message->getSubmissionId());
    $form = $submission->getForm();  // ❌ Crash if $submission is null
    // ...
}
```

#### Impact

- Unhandled exception: `Call to a member function getForm() on null`
- Messenger message failure (retry or dead letter depending on config)
- Polluted error logs

#### Proposed Fix

```php
public function __invoke(SendSubmissionNotification $message): void
{
    $submission = $this->submissionRepository->find($message->getSubmissionId());

    if (null === $submission) {
        $this->logger->warning('Submission not found, skipping notification', [
            'submission_id' => $message->getSubmissionId()
        ]);
        return;
    }

    $form = $submission->getForm();
    // ...
}
```

#### Validation Test

```php
public function testHandlerSkipsDeletedSubmission(): void
{
    $message = new SendSubmissionNotification(99999); // Non-existent ID

    $handler = self::getContainer()->get(SendSubmissionNotificationHandler::class);

    // Should not throw an exception
    $handler($message);

    // Verify warning log
}
```

---

### 3.4 BUG-007: Unhandled exception by the Handler

| Attribute | Value |
|-----------|-------|
| **ID** | BUG-007 |
| **Severity** | Medium |
| **Files** | `src/Service/Notification/EmailChannel.php:61` + `src/MessageHandler/Command/SendSubmissionNotificationHandler.php:44` |
| **Detected by** | Analysis during functional tests |

#### Description

`EmailChannel::triggerNotification()` re-throws the `TransportExceptionInterface` exception after logging it, but the handler doesn't catch it. This causes problems with Messenger.

#### Problematic Code

```php
// EmailChannel.php
try {
    $mailer->send($email);
} catch (TransportExceptionInterface $e) {
    $this->logger->error('An error occurred...', ['exception' => $e]);
    throw $e;  // ❌ Re-throw
}

// SendSubmissionNotificationHandler.php
foreach ($enabledNotifications as $enabledNotification) {
    // ...
    $channel->triggerNotification($submission, $enabledNotification);
    // ❌ No try/catch - Exception bubbles up to Messenger
}
```

#### Impact

- If one email fails, other notifications for the same form are not sent
- Messenger may retry indefinitely or lose the message
- One failed notification blocks the entire batch

#### Proposed Fix

**Option A: Don't re-throw in EmailChannel**

```php
// EmailChannel.php
try {
    $mailer->send($email);
    $this->logger->info('Email notification sent successfully.');
} catch (TransportExceptionInterface $e) {
    $this->logger->error('An error occurred...', ['exception' => $e]);
    // Don't re-throw, let other notifications continue
}
```

**Option B: Catch in the Handler**

```php
// SendSubmissionNotificationHandler.php
foreach ($enabledNotifications as $enabledNotification) {
    try {
        $channel->triggerNotification($submission, $enabledNotification);
    } catch (\Throwable $e) {
        $this->logger->error('Notification failed', [
            'channel' => $enabledNotification->getType(),
            'exception' => $e
        ]);
        // Continue with other notifications
    }
}
```

---

## 4. Prioritization and Correction Plan

### 4.1 Priority Order

| Priority | Bug | Reason |
|----------|-----|--------|
| 1 | BUG-005 | **CRITICAL** - Multi-tenant security flaw |
| 2 | BUG-006 | Potential crash in production |
| 3 | BUG-007 | Lost notifications |
| 4 | BUG-003 | Deprecation - prepare for Symfony 8 |
| 5 | BUG-004 | Inconsistent UX |
| 6 | BUG-001 | Performance |
| 7 | BUG-002 | Fragile code |

### 4.2 Effort Estimation

| Bug | Complexity | Estimated Time |
|-----|------------|----------------|
| BUG-001 | Simple | 15 min |
| BUG-002 | Simple | 10 min |
| BUG-003 | Simple | 10 min |
| BUG-004 | Simple | 5 min |
| BUG-005 | Medium | 1h |
| BUG-006 | Simple | 15 min |
| BUG-007 | Simple | 20 min |
| **TOTAL** | - | **~2h30** |

---


*Report automatically generated - Uniform Project - January 9, 2026*
