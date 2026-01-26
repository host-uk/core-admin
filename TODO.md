# Core-Admin TODO

## Testing & Quality Assurance

### High Priority

- [ ] **Test Coverage: Search System** - Test global search functionality
  - [ ] Test SearchProviderRegistry with multiple providers
  - [ ] Test AdminPageSearchProvider query matching
  - [ ] Test SearchResult highlighting
  - [ ] Test search analytics tracking
  - [ ] Test workspace-scoped search results
  - **Estimated effort:** 3-4 hours

- [ ] **Test Coverage: Form Components** - Test authorization props
  - [ ] Test Button component with :can/:cannot props
  - [ ] Test Input component with authorization
  - [ ] Test Select/Checkbox/Toggle with permissions
  - [ ] Test workspace context in form components
  - **Estimated effort:** 2-3 hours

- [ ] **Test Coverage: Livewire Modals** - Test modal system
  - [ ] Test modal opening/closing
  - [ ] Test file uploads in modals
  - [ ] Test validation in modals
  - [ ] Test nested modals
  - [ ] Test modal events and lifecycle
  - **Estimated effort:** 3-4 hours

### Medium Priority

- [ ] **Test Coverage: Admin Menu System** - Test menu building
  - [ ] Test AdminMenuRegistry with multiple providers
  - [ ] Test MenuItemBuilder with badges
  - [ ] Test menu authorization (can/canAny)
  - [ ] Test menu active state detection
  - [ ] Test IconValidator
  - **Estimated effort:** 2-3 hours

- [ ] **Test Coverage: HLCRF Components** - Test layout system
  - [ ] Test HierarchicalLayoutBuilder parsing
  - [ ] Test nested layout rendering
  - [ ] Test self-documenting IDs (H-0, C-R-2, etc.)
  - [ ] Test responsive breakpoints
  - **Estimated effort:** 4-5 hours

### Low Priority

- [ ] **Test Coverage: Teapot/Honeypot** - Test anti-spam
  - [ ] Test TeapotController honeypot detection
  - [ ] Test HoneypotHit recording
  - [ ] Test automatic IP blocking
  - [ ] Test hit pruning
  - **Estimated effort:** 2-3 hours

## Features & Enhancements

### High Priority

- [ ] **Feature: Data Tables Component** - Reusable admin tables
  - [ ] Create sortable table component
  - [ ] Add bulk action support
  - [ ] Implement column filtering
  - [ ] Add export to CSV/Excel
  - [ ] Test with large datasets (1000+ rows)
  - **Estimated effort:** 6-8 hours
  - **Files:** `src/Admin/Tables/`

- [ ] **Feature: Dashboard Widgets** - Composable dashboard
  - [ ] Create widget system with layouts
  - [ ] Add drag-and-drop widget arrangement
  - [ ] Implement widget state persistence
  - [ ] Create common widgets (stats, charts, lists)
  - [ ] Test widget refresh and real-time updates
  - **Estimated effort:** 8-10 hours
  - **Files:** `src/Admin/Dashboard/`

- [ ] **Feature: Notification Center** - In-app notifications
  - [ ] Create notification inbox component
  - [ ] Add real-time notification delivery
  - [ ] Implement notification preferences
  - [ ] Add notification grouping
  - [ ] Test with high notification volume
  - **Estimated effort:** 6-8 hours
  - **Files:** `src/Admin/Notifications/`

### Medium Priority

- [ ] **Enhancement: Form Builder** - Dynamic form generation
  - [ ] Create form builder UI
  - [ ] Support custom field types
  - [ ] Add conditional field visibility
  - [ ] Implement form templates
  - [ ] Test complex multi-step forms
  - **Estimated effort:** 8-10 hours
  - **Files:** `src/Forms/Builder/`

- [ ] **Enhancement: Activity Feed Component** - Visual activity log
  - [ ] Create activity feed Livewire component
  - [ ] Add filtering by event type/user/date
  - [ ] Implement infinite scroll
  - [ ] Add export functionality
  - [ ] Test with large activity logs
  - **Estimated effort:** 4-5 hours
  - **Files:** `src/Activity/Components/`

- [ ] **Enhancement: File Manager** - Media browser
  - [ ] Create file browser component
  - [ ] Add upload with drag-and-drop
  - [ ] Implement folder organization
  - [ ] Add image preview and editing
  - [ ] Test with S3/CDN integration
  - **Estimated effort:** 10-12 hours
  - **Files:** `src/Media/Manager/`

### Low Priority

- [ ] **Enhancement: Theme Customizer** - Visual theme editor
  - [ ] Create color picker for brand colors
  - [ ] Add font selection
  - [ ] Implement logo upload
  - [ ] Add CSS custom property generation
  - [ ] Test theme persistence per workspace
  - **Estimated effort:** 6-8 hours
  - **Files:** `src/Theming/`

- [ ] **Enhancement: Keyboard Shortcuts** - Power user features
  - [ ] Implement global shortcut system
  - [ ] Add command palette (Cmd+K)
  - [ ] Create shortcut configuration UI
  - [ ] Add accessibility support
  - **Estimated effort:** 4-5 hours
  - **Files:** `src/Shortcuts/`

## Security & Authorization

- [ ] **Audit: Admin Route Security** - Verify all admin routes protected
  - [ ] Audit all admin controllers for authorization
  - [ ] Ensure #[Action] attributes on sensitive operations
  - [ ] Verify middleware chains
  - [ ] Test unauthorized access attempts
  - **Estimated effort:** 3-4 hours

- [ ] **Enhancement: Action Audit Log** - Track admin actions
  - [ ] Log all admin operations
  - [ ] Track who/what/when for compliance
  - [ ] Add audit log viewer
  - [ ] Implement tamper-proof logging
  - **Estimated effort:** 4-5 hours
  - **Files:** `src/Audit/`

## Documentation

- [x] **Guide: Creating Admin Panels** - Step-by-step guide
  - [x] Document menu registration
  - [x] Show modal creation examples
  - [x] Explain authorization integration
  - [x] Add complete example module
  - **Completed:** January 2026
  - **File:** `docs/packages/admin/creating-admin-panels.md`

- [x] **Guide: HLCRF Deep Dive** - Advanced layout patterns
  - [x] Document all layout combinations
  - [x] Show responsive design patterns
  - [x] Explain ID system in detail
  - [x] Add complex real-world examples
  - **Completed:** January 2026
  - **File:** `docs/packages/admin/hlcrf-deep-dive.md`

- [x] **API Reference: Components** - Component prop documentation
  - [x] Document all form component props
  - [x] Add prop validation rules
  - [x] Show authorization prop examples
  - [x] Include accessibility notes
  - **Completed:** January 2026
  - **File:** `docs/packages/admin/components-reference.md`

## Code Quality

- [ ] **Refactor: Extract Modal Manager** - Separate concerns
  - [ ] Extract modal state management
  - [ ] Create dedicated ModalManager service
  - [ ] Add modal queue support
  - [ ] Test modal lifecycle
  - **Estimated effort:** 3-4 hours

- [ ] **Refactor: Standardize Component Props** - Consistent API
  - [ ] Audit all component props
  - [ ] Standardize naming (can/cannot/canAny)
  - [ ] Add prop validation
  - [ ] Update documentation
  - **Estimated effort:** 2-3 hours

- [ ] **PHPStan: Fix Level 5 Errors** - Improve type safety
  - [ ] Fix property type declarations
  - [ ] Add missing return types
  - [ ] Fix array shape types
  - **Estimated effort:** 2-3 hours

## Performance

- [ ] **Optimization: Search Indexing** - Faster admin search
  - [ ] Profile search performance
  - [ ] Add search result caching
  - [ ] Implement debounced search
  - [ ] Optimize query building
  - **Estimated effort:** 2-3 hours

- [ ] **Optimization: Menu Rendering** - Reduce menu overhead
  - [ ] Cache menu structure
  - [ ] Lazy load menu icons
  - [ ] Optimize authorization checks
  - **Estimated effort:** 1-2 hours

---

## Completed (January 2026)

- [x] **Forms: Authorization Props** - Added :can/:cannot/:canAny to all form components
- [x] **Search: Provider System** - Global search with multiple providers
- [x] **Search: Analytics** - Track search queries and results
- [x] **Documentation** - Complete admin package documentation
- [x] **Guide: Creating Admin Panels** - Menu registration, modals, authorization, example module
- [x] **Guide: HLCRF Deep Dive** - Layout combinations, ID system, responsive patterns
- [x] **API Reference: Components** - Form component props with authorization examples

*See `changelog/2026/jan/` for completed features.*
