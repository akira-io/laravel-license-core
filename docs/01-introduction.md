# Introduction

Laravel License is a comprehensive licensing system for Laravel applications. It provides all the necessary components to manage software licenses, activations, usage tracking, and event logging.

## Overview

This package offers a complete domain logic for implementing a licensing system without forcing any specific UI, API, or dashboard layer. You have full control over how you integrate and present the licensing functionality to your users.

## Key Features

- **Type-safe license management** with PHP enums
- **Multiple license types**: Lifetime, Annual, Subscription, Trial, and Credits-based
- **License status management**: Active, Expired, Suspended, and Revoked
- **Activation tracking** with domain, machine hash, IP address, and user agent
- **Usage monitoring** with consumed units and limits for credit-based licenses
- **Event logging** for complete audit trail of all license actions
- **Grace period support** for expired licenses to allow temporary access
- **Encrypted metadata storage** for sensitive license information
- **Fully configurable** table names and model classes
- **Rich factory support** for testing and development
- **100% test coverage** ensuring reliability and stability

## Use Cases

This package is ideal for:

- **SaaS applications** requiring subscription management
- **Software licensing** for desktop or mobile applications
- **API access control** with usage limits and quotas
- **Multi-tenant applications** with per-tenant licensing
- **Plugin/extension systems** with activation management
- **Credit-based services** with usage tracking

## Requirements

- PHP 8.4 or higher
- Laravel 12.x or higher
- MySQL 5.7+ / PostgreSQL 9.6+ / SQLite 3.8+

## Package Structure

The package is organized into several components:

- **Models**: Core Eloquent models for License, Activation, Usage, and Event
- **Enums**: Type-safe enumerations for license types, statuses, and events
- **Factories**: Comprehensive factories for testing and seeding
- **Migrations**: Database schema definitions
- **Service Provider**: Laravel service provider for package integration
- **Config Manager**: Centralized configuration management

## Philosophy

The package follows these design principles:

1. **Separation of Concerns**: Business logic is separated from presentation
2. **Type Safety**: Extensive use of PHP 8.4+ features including enums and typed properties
3. **Testability**: Every component is fully tested with 100% coverage
4. **Flexibility**: Configurable and extendable without modifying core code
5. **Security**: Encryption for sensitive data and comprehensive audit logging
6. **Performance**: Optimized queries with proper indexing and relationships

## What's Next?

Continue to the next sections to learn how to install, configure, and use Laravel License:

- [Installation](02-installation.md) - Get started with installing the package
- [Configuration](03-configuration.md) - Learn about configuration options
- [Models](04-models.md) - Understand the data models
- [Usage Guide](05-usage-guide.md) - Learn how to use the package

---

**Navigation**: Next: [Installation](02-installation.md)
