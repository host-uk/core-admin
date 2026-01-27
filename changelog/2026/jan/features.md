# Core-Admin - January 2026

## Features Implemented

### Hades Admin Navigation Audit (TASK-005)

Complete reorganisation of admin panel navigation.

**Structure:**
```
Dashboard
├── Services (per-product sections)
│   ├── BioHost
│   ├── SocialHost
│   ├── AnalyticsHost
│   ├── NotifyHost
│   ├── TrustHost
│   └── SupportHost
├── Commerce
│   ├── Subscriptions
│   ├── Orders
│   └── Coupons
├── Platform
│   ├── Users
│   ├── Workspaces
│   └── Activity
└── Developer
    ├── API Keys
    ├── Webhooks
    └── Logs
```

**Changes:**
- Grouped by domain (services, commerce, platform, developer)
- Consistent iconography
- Permission-based visibility
- Mobile-responsive sidebar

---

### Admin Menu Provider

Interface for modules to register admin navigation.

**Files:**
- `Contracts/AdminMenuProvider.php`
- Permission checks per item
- Configurable TTL caching
- Priority constants for ordering
