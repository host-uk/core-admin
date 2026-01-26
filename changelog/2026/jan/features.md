# Core-Admin - January 2026

## Features Implemented

### Form Authorization Components

Authorization-aware form components that automatically disable/hide based on permissions.

**Files:**
- `src/Forms/Concerns/HasAuthorizationProps.php` - Authorization trait
- `src/Forms/View/Components/` - Input, Textarea, Select, Checkbox, Button, Toggle, FormGroup
- `resources/views/components/forms/` - Blade templates

**Components:**
- `<x-core-forms.input />` - Text input with label, helper, error
- `<x-core-forms.textarea />` - Textarea with auto-resize
- `<x-core-forms.select />` - Dropdown with grouped options
- `<x-core-forms.checkbox />` - Checkbox with description
- `<x-core-forms.button />` - Button with variants, loading state
- `<x-core-forms.toggle />` - Toggle with instant save
- `<x-core-forms.form-group />` - Wrapper for spacing

**Usage:**
```blade
<x-core-forms.input
    id="name"
    label="Name"
    canGate="update"
    :canResource="$model"
    wire:model="name"
/>

<x-core-forms.button variant="danger" canGate="delete" :canResource="$model" canHide>
    Delete
</x-core-forms.button>
```

---

### Global Search (⌘K)

Unified search across resources with keyboard navigation.

**Files:**
- `src/Search/Contracts/SearchProvider.php` - Provider interface
- `src/Search/SearchProviderRegistry.php` - Registry with fuzzy matching
- `src/Search/SearchResult.php` - Result DTO
- `src/Search/Providers/AdminPageSearchProvider.php` - Built-in provider
- `src/Website/Hub/View/Modal/Admin/GlobalSearch.php` - Livewire component

**Features:**
- ⌘K / Ctrl+K keyboard shortcut
- Arrow key navigation, Enter to select
- Fuzzy matching support
- Recent searches
- Grouped results by provider

**Usage:**
```php
// Register custom provider
app(SearchProviderRegistry::class)->register(new MySearchProvider());
```

---

## Design Decisions

### Soketi (Real-time WebSocket)

Excluded per project decision. Self-hosted Soketi integration not required at this time.
