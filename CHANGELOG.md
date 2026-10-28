# Changelog

All notable changes to this package are documented here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the package follows [Semantic Versioning](https://semver.org/).

Earlier releases are listed on the [releases page](https://github.com/Pollora/colt/releases).

## [Unreleased]

## [11.0.0] - 2026-10-28

A major release: the package requires PHP 8.3 and drops two traits. Read **Upgrading** below before updating.

### Changed

- **Requires PHP 8.3 or later** (PHP 8.2 is no longer maintained). CI runs on PHP 8.3 and 8.4, with Laravel 12 and 13.
- **`declare(strict_types=1)` in every file.** A scalar of the wrong type passed to a typed parameter of this package now throws a `TypeError` instead of being converted. The package has many untyped parameters, which keep accepting what they accepted.
- **Return types on public methods**, added where the code returns a single type: `isApproved(): bool`, `isReply(): bool`, `hasReplies(): bool`, `hasTerm(): bool`, `getAvatarAttribute(): string`, `getKeywordsStrAttribute(): string`, `getFormat(): string|array|false`, `createModel(): object`, `__toString(): string` and others. A model that overrides one of them with another return type must be updated.
- **Declared dependencies match what the code uses**: `illuminate/contracts` and `nesbot/carbon` are required, `illuminate/filesystem` is not (nothing used it), and `fakerphp/faker` is a development dependency (only the test factories use it).
- `PasswordService` declares its `$hasher` property: creating it dynamically is deprecated since PHP 8.2.
- `Post::getImageAttribute()` is documented as `string|null` and returns `null` explicitly when the post has no thumbnail (same value as before); `getPostInstance()` and `newFromBuilder()` are documented as returning `static`.
- The models declare the columns and relations the code reads with `@property` tags, for static analysis.
- The package is checked by the [Pollora harness](https://github.com/Pollora/harness): every gate blocks a merge (code style, PHPStan with a baseline of 52 reports that can only go down, Rector, dependency analysis, security patterns, public surface, tests).

### Removed

- **`Pollora\Colt\Concerns\AdvancedCustomFields`**: deprecated, it relied on a `Pollora\Colt\Acf\AdvancedCustomFields` class that was never part of the package and threw a `LogicException`. Read ACF values with `get_field()` or through the model's meta.
- **`Pollora\Colt\Laravel\Auth\ResetsPasswords`**: the trait that stored a reset password with WordPress's hashing. A controller that used it must hash the password itself with `Pollora\Colt\Services\PasswordService::makeHash()` and save the `user_pass` of the user.

### Upgrading

1. Run PHP 8.3 or later.
2. Remove `use AdvancedCustomFields` and `use ResetsPasswords` from your classes.
3. If you override one of the methods listed under "Return types", give it a compatible return type.
4. Run your tests: a `TypeError` points at a call that relied on a scalar being converted.
