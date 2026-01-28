# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

This is the **Core Admin Package** (`host-uk/core-admin`) - an admin panel and service layer for the Core PHP Framework. It provides the Hub dashboard, form components with authorization, global search, and Livewire modals.

## Commands

```bash
php artisan serve             # Laravel dev server
npm run dev                   # Vite dev server
./vendor/bin/pint --dirty     # Format changed files only
./vendor/bin/pest             # Run all tests
./vendor/bin/pest --filter=SearchTest  # Run specific test
```

## Architecture

This package contains three Boot.php files that wire up different concerns:

| File | Namespace | Purpose |
|------|-----------|---------|
| `src/Boot.php` | `Core\Admin\` | Main package provider - form components, search registry |
| `src/Website/Hub/Boot.php` | `Website\Hub\` | Admin dashboard frontend - routes, Livewire components, menu |
| `src/Mod/Hub/Boot.php` | `Core\Admin\Mod\Hub\` | Admin backend - models, migrations, 20+ Livewire modals |
| `Service/Boot.php` | `Core\Service\Admin\` | Service definition for platform_services table |

**Event-driven registration pattern:**
```php
class Boot extends ServiceProvider
{
    public static array $listens = [
        AdminPanelBooting::class => 'onAdminPanel',
        DomainResolving::class => 'onDomainResolving',
    ];
}
```

### Key Systems

**Form Components** (`src/Forms/`) - Blade components with authorization via `HasAuthorizationProps` trait:
- `<x-core-forms.input />`, `<x-core-forms.select />`, `<x-core-forms.toggle />`, etc.
- Props: `canGate`, `canResource`, `canHide` for permission-based disable/hide

**Search System** (`src/Search/`) - Extensible provider-based search:
- Implement `SearchProvider` interface with `search()`, `searchType()`, `getUrl()`
- Register providers via `SearchProviderRegistry`

**Admin Menu** - Implement `AdminMenuProvider` interface to add menu items

### Directory Structure

```
src/
├── Boot.php                    # Package service provider
├── Forms/                      # Form components with authorization
├── Search/                     # Global search system
├── Website/Hub/                # Admin dashboard frontend
│   ├── Routes/admin.php        # Admin web routes
│   └── View/                   # Blade templates + Livewire components
└── Mod/Hub/                    # Admin backend module
    ├── Models/                 # Service, HoneypotHit
    ├── Migrations/             # platform_services, honeypot_hits
    └── Boot.php                # Module registration + 20 Livewire modals
```

## Conventions

- **UK English** - colour, organisation, centre (never American spellings)
- **Strict types** - `declare(strict_types=1);` in every PHP file
- **Type hints** - All parameters and return types
- **Flux Pro** - Use Flux components, not vanilla Alpine
- **Font Awesome Pro** - Use FA icons, not Heroicons
- **Pest** - Write tests using Pest syntax

## Packages

| Package | Purpose |
|---------|---------|
| `host-uk/core` | Core framework, events, module discovery |
| `host-uk/core-admin` | This package - admin panel, modals |
| `host-uk/core-api` | REST API, scopes, rate limiting |
| `host-uk/core-mcp` | Model Context Protocol for AI agents |

## License

EUPL-1.2 (copyleft) - See LICENSE for details.