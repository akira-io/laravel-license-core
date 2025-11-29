# Exception Reference

Complete reference for all exceptions thrown by Laravel License Core.

## Exception Hierarchy

All license exceptions extend `LicenseException`.

## All Exceptions

**ActivationLimitReachedException** - Machine activation limit reached
**DomainBlockedException** - Domain is blocked
**DomainNotAllowedException** - Domain not in allowed list
**InsufficientCreditsException** - Not enough credits
**LicenseExpiredException** - License expired  
**LicenseNotFoundException** - License key not found
**LicenseNotLoadedException** - Internal error
**LicenseRevokedException** - License is revoked
**LicenseSuspendedException** - License is suspended
**UsageNotConfiguredException** - Credits usage not configured
**VersionNotCoveredException** - Version not covered

---

**Previous**: [Actions](07-actions.md) | **Next**: [Testing](09-testing.md)
