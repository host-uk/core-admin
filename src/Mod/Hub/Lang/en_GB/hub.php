<?php

declare(strict_types=1);

/**
 * Hub module translations (en_GB).
 *
 * Key structure: section.subsection.key
 */

return [
    'dashboard' => [
        'title' => 'Dashboard',
        'subtitle' => 'Your creator toolkit at a glance',
        'greeting' => 'Hello :name',
        'greeting_subtitle' => 'What would you like to work on today?',
        'your_workspaces' => 'Your Workspaces',
        'manage_all' => 'Manage All',
        'enabled_services' => 'Enabled services',
        'no_services' => 'No services enabled yet',
        'add_services' => 'Add Services',
        'manage_workspace' => 'Manage',
        'service_count' => 'service|services',
        'renews_on' => 'Renews :date',
        'manage_billing' => 'Manage Billing',
        'no_workspaces_title' => 'No workspaces yet',
        'no_workspaces_description' => 'Create your first workspace to get started with Host UK services.',
        'create_workspace' => 'Create Workspace',
        'learn_more' => 'Learn More',
    ],

    'actions' => [
        'edit_content' => 'Edit Content',
    ],

    'sections' => [
        'recent_activity' => 'Recent Activity',
        'quick_actions' => 'Quick Actions',
    ],

    'quick_actions' => [
        'edit_content' => [
            'title' => 'Edit Content',
            'subtitle' => 'Manage WordPress content',
        ],
        'manage_workspaces' => [
            'title' => 'Manage Workspaces',
            'subtitle' => 'View and configure workspaces',
        ],
        'server_console' => [
            'title' => 'Server Console',
            'subtitle' => 'Access server terminal',
        ],
        'view_analytics' => [
            'title' => 'View Analytics',
            'subtitle' => 'Traffic and performance',
        ],
        'profile' => [
            'title' => 'Profile',
            'subtitle' => 'Manage your account',
        ],
    ],

    // Console page
    'console' => [
        'title' => 'Server Console',
        'subtitle' => 'Secure terminal access to your hosted applications',
        'labels' => [
            'select_server' => 'Select Server',
            'terminal' => 'Terminal',
            'enter_command' => 'Enter command...',
            'connecting' => 'Connecting to :name...',
            'establishing_connection' => 'Establishing secure connection via Coolify API...',
            'connected' => 'Connected successfully.',
            'select_server_prompt' => 'Select a server from the list to open a terminal session',
            'terminal_disabled' => 'Terminal functionality will be enabled once Coolify API integration is complete',
        ],
        'coolify' => [
            'title' => 'Coolify Integration',
            'description' => 'This console will connect to your Coolify instance for secure terminal access to containers.',
        ],
    ],

    // AI Services page
    'ai_services' => [
        'title' => 'AI Services',
        'subtitle' => 'Configure AI providers for content generation in Host Social.',
        'labels' => [
            'api_key' => 'API Key',
            'secret_key' => 'Secret Key',
            'model' => 'Model',
            'active' => 'Active',
            'save' => 'Save',
            'saving' => 'Saving...',
        ],
        'providers' => [
            'claude' => [
                'name' => 'Claude',
                'title' => 'Claude (Anthropic)',
                'api_key_link' => 'Generate an API key from Anthropic Console',
            ],
            'gemini' => [
                'name' => 'Gemini',
                'title' => 'Gemini (Google)',
                'api_key_link' => 'Generate an API key from Google AI Studio',
            ],
            'openai' => [
                'name' => 'OpenAI',
                'title' => 'OpenAI',
                'api_key_link' => 'Generate an API key from OpenAI Platform',
            ],
        ],
    ],

    // Prompts page
    'prompts' => [
        'title' => 'Prompt Manager',
        'subtitle' => 'Manage AI prompts for content generation',
        'labels' => [
            'new_prompt' => 'New Prompt',
            'search_prompts' => 'Search prompts...',
            'all_categories' => 'All categories',
            'all_models' => 'All models',
            'empty' => 'No prompts found. Create your first prompt to get started.',
        ],
        'editor' => [
            'edit_title' => 'Edit Prompt',
            'new_title' => 'New Prompt',
            'name' => 'Name',
            'name_placeholder' => 'help-article-generator',
            'category' => 'Category',
            'description' => 'Description',
            'description_placeholder' => 'What does this prompt do?',
            'model' => 'Model',
            'temperature' => 'Temperature',
            'max_tokens' => 'Max Tokens',
            'system_prompt' => 'System Prompt',
            'user_template' => 'User Template',
            'user_template_hint' => 'Use @{{variable}} for template variables',
            'template_variables' => 'Template Variables',
            'add_variable' => 'Add Variable',
            'variable_name' => 'variable_name',
            'variable_description' => 'Description',
            'variable_default' => 'Default value',
            'no_variables' => 'No variables defined',
            'active' => 'Active',
            'active_description' => 'Enable this prompt for use in content generation',
            'version_history' => 'Version History',
            'cancel' => 'Cancel',
            'update_prompt' => 'Update Prompt',
            'create_prompt' => 'Create Prompt',
        ],
        'categories' => [
            'content' => 'Content',
            'seo' => 'SEO',
            'refinement' => 'Refinement',
            'translation' => 'Translation',
            'analysis' => 'Analysis',
        ],
        'models' => [
            'claude' => 'Claude (Anthropic)',
            'gemini' => 'Gemini (Google)',
        ],
        'versions' => [
            'title' => 'Version History',
            'version' => 'Version :number',
            'by' => 'by :name',
            'restore' => 'Restore',
            'no_history' => 'No version history available',
        ],
    ],

    // Services admin page translations
    'services' => [
        // Tab labels for each service
        'tabs' => [
            'dashboard' => 'Dashboard',
            'pages' => 'Pages',
            'projects' => 'Projects',
            'websites' => 'Websites',
            'goals' => 'Goals',
            'subscribers' => 'Subscribers',
            'campaigns' => 'Campaigns',
            'notifications' => 'Widgets',
            'accounts' => 'Accounts',
            'posts' => 'Posts',
            'inbox' => 'Inbox',
            'settings' => 'Settings',
            'orders' => 'Orders',
            'subscriptions' => 'Subscriptions',
            'coupons' => 'Coupons',
        ],

        // Table column headers
        'columns' => [
            'namespace' => 'Namespace',
            'type' => 'Type',
            'status' => 'Status',
            'clicks' => 'Clicks',
            'project' => 'Project',
            'pages' => 'Pages',
            'created' => 'Created',
            'website' => 'Mod',
            'name' => 'Name',
            'host' => 'Host',
            'pageviews_mtd' => 'Pageviews (MTD)',
            'subscribers' => 'Subscribers',
            'endpoint' => 'Endpoint',
            'subscribed' => 'Subscribed',
            'campaign' => 'Campaign',
            'stats' => 'Stats',
            'widgets' => 'Widgets',
            'widget' => 'Widget',
            'impressions' => 'Impressions',
            'conversions' => 'Conversions',
            'performance' => 'Performance',
        ],

        // Status labels
        'status' => [
            'active' => 'Active',
            'disabled' => 'Disabled',
            'inactive' => 'Inactive',
            'sent' => 'Sent',
            'sending' => 'Sending',
            'scheduled' => 'Scheduled',
            'draft' => 'Draft',
            'failed' => 'Failed',
        ],

        // Action buttons and links
        'actions' => [
            'manage_biohost' => 'Manage Bio',
            'manage_analytics' => 'Manage Analytics',
            'manage_notifyhost' => 'Manage Notify',
            'manage_trusthost' => 'Manage Trust',
            'manage_supporthost' => 'Manage Support',
            'manage_commerce' => 'Manage Commerce',
            'create_page' => 'Create Page',
            'manage_projects' => 'Manage Projects',
            'add_website' => 'Add Mod',
            'view_all' => 'View All',
            'create_campaign' => 'Create Campaign',
            'create_goal' => 'Create Goal',
        ],

        // Section headings
        'headings' => [
            'your_bio_pages' => 'Your Bio Pages',
            'all_pages' => 'All Pages',
            'projects' => 'Projects',
            'websites_by_pageviews' => 'Websites by Pageviews',
            'all_websites' => 'All Websites',
            'goals_coming_soon' => 'Goals management coming soon',
            'websites_by_subscribers' => 'Websites by Subscribers',
            'recent_subscribers' => 'Recent Subscribers',
            'campaigns' => 'Campaigns',
            'all_campaigns' => 'All Campaigns',
            'widgets_by_impressions' => 'Widgets by Impressions',
            'top_pages' => 'Top Pages',
            'pageviews_trend' => 'Pageviews Trend',
            'traffic_sources' => 'Traffic Sources',
            'devices' => 'Devices',
        ],

        // Empty state messages
        'empty' => [
            'bio_pages' => 'No bio pages found. Create your first one!',
            'pages' => 'No pages found',
            'projects' => 'No projects found',
            'websites' => 'No websites found',
            'subscribers' => 'No subscribers found',
            'campaigns' => 'No campaigns found',
            'widgets' => 'No widgets found',
            'tickets' => 'No tickets found',
            'orders' => 'No orders found',
            'subscriptions' => 'No subscriptions found',
            'coupons' => 'No coupons found',
            'page_data' => 'No page data yet',
            'no_websites_title' => 'No websites tracked',
            'no_websites_description' => 'Add a website to start tracking pageviews and visitor analytics.',
            'no_goals_title' => 'No goals defined',
            'no_goals_description' => 'Create conversion goals to track important actions on your websites.',
            'no_traffic_data' => 'No traffic data yet',
            'no_device_data' => 'No device data yet',
            'no_subscribers_title' => 'No subscribers yet',
            'no_campaigns_title' => 'No campaigns yet',
        ],

        // Miscellaneous
        'misc' => [
            'na' => 'N/A',
            'sent_count' => ':count sent',
        ],

        // Summary bar metrics
        'summary' => [
            'pageviews' => 'Pageviews',
            'visitors' => 'Visitors',
            'bounce_rate' => 'Bounce Rate',
            'avg_duration' => 'Avg. Duration',
        ],

        // Date range options
        'date_range' => [
            '7d' => 'Last 7 days',
            '30d' => 'Last 30 days',
            '90d' => 'Last 90 days',
            'all' => 'All time',
        ],

        // Analytics acquisition channels
        'analytics' => [
            'channels' => [
                'direct' => 'Direct',
                'search' => 'Search',
                'social' => 'Social',
                'referral' => 'Referral',
            ],
            'devices' => [
                'desktop' => 'Desktop',
                'mobile' => 'Mobile',
                'tablet' => 'Tablet',
            ],
        ],

        // Service names (for tabs and titles)
        'names' => [
            'bio' => 'Bio',
            'social' => 'Social',
            'analytics' => 'Analytics',
            'notify' => 'Notify',
            'trust' => 'Trust',
            'support' => 'Support',
            'commerce' => 'Commerce',
        ],

        // Support service contextual metrics
        'support' => [
            'inbox_health' => 'Inbox Health',
            'open_tickets' => 'Open Tickets',
            'avg_response_time' => 'Avg Response Time',
            'oldest' => 'Oldest',
            'todays_activity' => "Today's Activity",
            'new_today' => 'New Conversations',
            'resolved_today' => 'Resolved Today',
            'messages_sent' => 'Messages Sent',
            'performance' => 'Performance (This Month)',
            'first_response' => 'First Response Time',
            'resolution_time' => 'Resolution Time',
            'na' => 'N/A',
            'recent_conversations' => 'Recent Conversations',
            'view_inbox' => 'View Inbox',
            'empty_inbox' => 'No conversations yet',
            'empty_inbox_description' => 'Messages will appear here when customers reach out.',
            'unknown' => 'Unknown',
            'open_full_inbox' => 'Open full inbox',
            'open_settings' => 'Open settings',
        ],

        // Stat card labels - Bio
        'stats' => [
            'bio' => [
                'total_pages' => 'Total Pages',
                'active_pages' => 'Active Pages',
                'total_clicks' => 'Total Clicks',
                'projects' => 'Projects',
            ],
            'social' => [
                'total_accounts' => 'Total Accounts',
                'active_accounts' => 'Active Accounts',
                'scheduled_posts' => 'Scheduled Posts',
                'published_posts' => 'Published Posts',
            ],
            'analytics' => [
                'total_websites' => 'Total Websites',
                'active_websites' => 'Active Websites',
                'pageviews_today' => 'Pageviews Today',
                'sessions_today' => 'Sessions Today',
            ],
            'notify' => [
                'websites' => 'Websites',
                'active_subscribers' => 'Active Subscribers',
                'active_campaigns' => 'Active Campaigns',
                'messages_today' => 'Messages Today',
            ],
            'trust' => [
                'total_campaigns' => 'Total Campaigns',
                'active_campaigns' => 'Active Campaigns',
                'total_widgets' => 'Total Widgets',
                'total_impressions' => 'Total Impressions',
            ],
        ],

        // Trust module specific metrics
        'trust' => [
            'metrics' => [
                'impressions' => 'Impressions',
                'clicks' => 'Clicks',
                'conversions' => 'Conversions',
                'ctr' => 'CTR',
                'cvr' => 'CVR',
            ],
            'support' => [
                'open_tickets' => 'Open Tickets',
                'unread_messages' => 'Unread Messages',
                'avg_response_time' => 'Avg Response Time',
                'resolved_today' => 'Resolved Today',
            ],
            'commerce' => [
                'total_orders' => 'Total Orders',
                'pending_orders' => 'Pending Orders',
                'active_subscriptions' => 'Active Subscriptions',
                'revenue_mtd' => 'Revenue (MTD)',
            ],
        ],
    ],

    // Workspace Settings page
    'workspace_settings' => [
        'title' => 'Workspace Settings',
        'subtitle' => 'Configure your workspace deployment and environment',
        'under_construction' => 'Under Construction',
        'coming_soon_message' => 'Workspace settings management is currently being built. This page will allow you to configure deployment settings, environment variables, SSL certificates, and more.',
    ],

    // Global Search
    'search' => [
        'button' => 'Search...',
        'placeholder' => 'Search pages, workspaces, settings...',
        'no_results' => 'No results found for ":query"',
        'navigate' => 'to navigate',
        'select' => 'to select',
        'close' => 'to close',
        'start_typing' => 'Start typing to search...',
        'tips' => 'Search pages, settings, and more',
        'recent' => 'Recent',
        'clear_recent' => 'Clear',
        'remove' => 'Remove',
    ],

    // Workspace Switcher
    'workspace_switcher' => [
        'title' => 'Switch Workspace',
    ],

    // Workspaces page
    'workspaces' => [
        'title' => 'Workspaces',
        'subtitle' => 'Manage your workspaces',
        'add' => 'Add Workspace',
        'empty' => 'No workspaces found.',
        'active' => 'Active',
        'activate' => 'Activate',
        'activated' => 'Workspace activated',
    ],

    /*
    |--------------------------------------------------------------------------
    | Usage Dashboard
    |--------------------------------------------------------------------------
    */
    'usage' => [
        'title' => 'Usage & Limits',
        'subtitle' => 'Monitor your workspace usage and available features',

        'packages' => [
            'title' => 'Active Packages',
            'subtitle' => 'Your current subscription packages',
            'empty' => 'No active packages',
            'empty_hint' => 'Contact support to activate your subscription',
            'renews' => 'Renews :time',
        ],

        'badges' => [
            'base' => 'Base',
            'addon' => 'Addon',
            'active' => 'Active',
            'not_included' => 'Not included',
            'unlimited' => 'Unlimited',
            'enabled' => 'Enabled',
        ],

        'categories' => [
            'general' => 'General',
        ],

        'warnings' => [
            'approaching_limit' => 'Approaching limit - :remaining remaining',
        ],

        'empty' => [
            'title' => 'No usage data available',
            'hint' => 'Usage will appear here once you start using features',
        ],

        'active_boosts' => [
            'title' => 'Active Boosts',
            'subtitle' => 'One-time top-ups for additional capacity',
            'remaining' => 'remaining',
        ],

        'duration' => [
            'cycle_bound' => 'Expires at cycle end',
            'expires' => 'Expires :time',
            'permanent' => 'Permanent',
        ],

        'cta' => [
            'title' => 'Need more capacity?',
            'subtitle' => 'Upgrade your package or add boosts to increase your limits',
            'add_boosts' => 'Add Boosts',
            'view_plans' => 'View Plans',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Boost Purchase
    |--------------------------------------------------------------------------
    */
    'boosts' => [
        'title' => 'Purchase Boost',
        'subtitle' => 'Add one-time top-ups to increase your limits',

        'types' => [
            'unlimited' => 'Unlimited',
            'enable' => 'Enable',
        ],

        'duration' => [
            'cycle_bound' => 'Expires at cycle end',
            'limited' => 'Limited duration',
            'permanent' => 'Permanent',
        ],

        'actions' => [
            'purchase' => 'Purchase',
            'back' => 'Back to Usage',
        ],

        'empty' => [
            'title' => 'No boosts available',
            'hint' => 'Boost options will appear here when configured',
        ],

        'info' => [
            'title' => 'About Boosts',
            'cycle_bound' => 'Expires at the end of your billing cycle, unused capacity does not roll over',
            'duration_based' => 'Valid for a specific time period from purchase',
            'permanent' => 'One-time purchase that never expires',
        ],

        'labels' => [
            'cycle_bound' => 'Cycle-bound:',
            'duration_based' => 'Duration-based:',
            'permanent' => 'Permanent:',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Settings Page
    |--------------------------------------------------------------------------
    */
    'settings' => [
        'title' => 'Account Settings',
        'subtitle' => 'Manage your account settings and preferences',

        'sections' => [
            'profile' => [
                'title' => 'Profile Information',
                'description' => 'Update your account\'s profile information and email address.',
            ],
            'preferences' => [
                'title' => 'Preferences',
                'description' => 'Configure your language, timezone, and display preferences.',
            ],
            'two_factor' => [
                'title' => 'Two-Factor Authentication',
                'description' => 'Add additional security to your account using two-factor authentication.',
            ],
            'password' => [
                'title' => 'Update Password',
                'description' => 'Ensure your account is using a long, random password to stay secure.',
            ],
            'delete_account' => [
                'title' => 'Delete Account',
                'description' => 'Permanently delete your account and all of its data.',
            ],
        ],

        'fields' => [
            'name' => 'Name',
            'name_placeholder' => 'Your name',
            'email' => 'Email',
            'email_placeholder' => 'your@email.com',
            'language' => 'Language',
            'timezone' => 'Timezone',
            'time_format' => 'Time Format',
            'time_format_12' => '12-hour (AM/PM)',
            'time_format_24' => '24-hour',
            'week_starts_on' => 'Week Starts On',
            'week_sunday' => 'Sunday',
            'week_monday' => 'Monday',
            'current_password' => 'Current Password',
            'new_password' => 'New Password',
            'confirm_password' => 'Confirm Password',
            'verification_code' => 'Verification Code',
            'verification_code_placeholder' => 'Enter 6-digit code',
            'delete_reason' => 'Reason for leaving (optional)',
            'delete_reason_placeholder' => 'Help us improve by sharing why you\'re leaving...',
        ],

        'actions' => [
            'save_profile' => 'Save Profile',
            'save_preferences' => 'Save Preferences',
            'update_password' => 'Update Password',
            'enable' => 'Enable',
            'disable' => 'Disable',
            'confirm' => 'Confirm',
            'cancel' => 'Cancel',
            'view_recovery_codes' => 'View Recovery Codes',
            'regenerate_codes' => 'Regenerate Codes',
            'delete_account' => 'Delete Account',
            'request_deletion' => 'Request Account Deletion',
            'cancel_deletion' => 'Cancel Deletion',
        ],

        'two_factor' => [
            'not_enabled' => 'Two-factor authentication is not enabled.',
            'not_enabled_description' => 'When two factor authentication is enabled, you will be prompted for a secure, random token during authentication.',
            'setup_instructions' => 'Scan the QR code below with your authenticator app (Google Authenticator, Authy, etc.), or enter the secret key manually.',
            'secret_key' => 'Secret Key:',
            'enabled' => 'Two-factor authentication is enabled.',
            'recovery_codes_warning' => 'Store these recovery codes securely. They can be used to access your account if you lose your 2FA device.',
        ],

        'delete' => [
            'warning_title' => 'Warning: This action is irreversible',
            'warning_delay' => 'Your account will be deleted in 7 days',
            'warning_workspaces' => 'All workspaces you own will be permanently removed',
            'warning_content' => 'All content, media, and settings will be erased',
            'warning_email' => 'You\'ll receive an email with options to delete immediately or cancel',
            'scheduled_title' => 'Account Deletion Scheduled',
            'scheduled_description' => 'Your account will be automatically deleted on :date (in :days days).',
            'scheduled_email_note' => 'Check your email for a link to delete immediately or cancel this request.',
            'initial_description' => 'Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.',
        ],

        'messages' => [
            'profile_updated' => 'Profile updated successfully.',
            'preferences_updated' => 'Preferences saved.',
            'password_updated' => 'Password changed successfully.',
            'two_factor_upgrading' => 'Two-factor authentication is currently being upgraded. Please try again later.',
            'deletion_scheduled' => 'Account deletion scheduled. Check your email for options.',
            'deletion_cancelled' => 'Account deletion has been cancelled.',
        ],

        'nav' => [
            'profile' => 'Profile',
            'preferences' => 'Preferences',
            'security' => 'Security',
            'password' => 'Password',
            'danger_zone' => 'Danger Zone',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Profile Page
    |--------------------------------------------------------------------------
    */
    'profile' => [
        'member_since' => 'Member since :date',

        'sections' => [
            'quotas' => 'Usage & Quotas',
            'services' => 'Services',
            'activity' => 'Recent Activity',
            'quick_actions' => 'Quick Actions',
        ],

        'quotas' => [
            'unlimited' => 'Unlimited',
            'need_more' => 'Need more?',
            'need_more_description' => 'Upgrade to unlock higher limits and more features.',
        ],

        'activity' => [
            'no_activity' => 'No recent activity',
        ],

        'actions' => [
            'settings' => 'Settings',
            'upgrade' => 'Upgrade',
            'edit_profile' => 'Edit Profile',
            'change_password' => 'Change Password',
            'export_data' => 'Export Data',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Manager (content-manager.blade.php)
    |--------------------------------------------------------------------------
    */
    'content_manager' => [
        'title' => 'Content Manager',
        'subtitle' => 'Local content management with WordPress sync',
        'actions' => [
            'new_content' => 'New Content',
            'sync_all' => 'Sync All',
            'purge_cdn' => 'Purge CDN',
        ],
        'tabs' => [
            'dashboard' => 'Dashboard',
            'kanban' => 'Kanban',
            'calendar' => 'Calendar',
            'list' => 'List',
            'webhooks' => 'Webhooks',
        ],
        'command' => [
            'placeholder' => 'Search content or run commands...',
            'sync_all' => 'Sync all content',
            'purge_cache' => 'Purge CDN cache',
            'open_wordpress' => 'Open WordPress',
            'no_results' => 'No results found',
        ],
        'preview' => [
            'sync_label' => 'Sync',
            'author' => 'Author',
            'excerpt' => 'Excerpt',
            'content_clean_html' => 'Content (Clean HTML)',
            'taxonomies' => 'Taxonomies',
            'structured_content' => 'Structured Content (JSON)',
            'created' => 'Created',
            'modified' => 'Modified',
            'last_synced' => 'Last Synced',
            'never' => 'Never',
            'wordpress_id' => 'WordPress ID',
        ],
        // Dashboard tab
        'dashboard' => [
            'total_content' => 'Total Content',
            'posts' => 'Posts',
            'published' => 'Published',
            'drafts' => 'Drafts',
            'synced' => 'Synced',
            'failed' => 'Failed',
            'content_created' => 'Content created (last 30 days)',
            'tooltip_posts' => 'Posts',
            'content_by_type' => 'Content by type',
            'pages' => 'Pages',
            'sync_status' => 'Sync status',
            'pending' => 'Pending',
            'stale' => 'Stale',
            'taxonomies' => 'Taxonomies',
            'categories' => 'Categories',
            'tags' => 'Tags',
            'webhooks_today' => 'Webhooks today',
            'received' => 'Received',
        ],
        // Kanban tab
        'kanban' => [
            'no_items' => 'No items',
        ],
        // Calendar tab
        'calendar' => [
            'content_schedule' => 'Content schedule',
            'legend' => [
                'published' => 'Published',
                'draft' => 'Draft',
                'scheduled' => 'Scheduled',
            ],
            'days' => [
                'sun' => 'Sun',
                'mon' => 'Mon',
                'tue' => 'Tue',
                'wed' => 'Wed',
                'thu' => 'Thu',
                'fri' => 'Fri',
                'sat' => 'Sat',
            ],
            'more' => '+:count more',
        ],
        // List tab
        'list' => [
            'search_placeholder' => 'Search content...',
            'filters' => [
                'all_types' => 'All Types',
                'posts' => 'Posts',
                'pages' => 'Pages',
                'all_status' => 'All Status',
                'published' => 'Published',
                'draft' => 'Draft',
                'pending' => 'Pending',
                'scheduled' => 'Scheduled',
                'private' => 'Private',
                'all_sync' => 'All Sync Status',
                'synced' => 'Synced',
                'stale' => 'Stale',
                'failed' => 'Failed',
                'all_sources' => 'All Sources',
                'native' => 'Native',
                'host_uk' => 'Host UK',
                'satellite' => 'Satellite',
                'wordpress_legacy' => 'WordPress (Legacy)',
                'all_categories' => 'All Categories',
                'clear' => 'Clear',
                'clear_filters' => 'Clear filters',
            ],
            'columns' => [
                'title' => 'Title',
                'type' => 'Type',
                'status' => 'Status',
                'sync' => 'Sync',
                'categories' => 'Categories',
                'created' => 'Created',
                'last_synced' => 'Last Synced',
            ],
            'never' => 'Never',
            'no_content' => 'No content found',
            'edit' => 'Edit',
            'preview' => 'Preview',
        ],
        // Webhooks tab
        'webhooks' => [
            'today' => 'Today',
            'completed' => 'Completed',
            'pending' => 'Pending',
            'failed' => 'Failed',
            'columns' => [
                'id' => 'ID',
                'event' => 'Event',
                'content' => 'Content',
                'status' => 'Status',
                'source_ip' => 'Source IP',
                'received' => 'Received',
                'processed' => 'Processed',
            ],
            'actions' => [
                'retry' => 'Retry',
                'view_payload' => 'View Payload',
            ],
            'error' => 'Error',
            'no_logs' => 'No webhook logs found',
            'no_logs_description' => 'Webhooks from WordPress will appear here',
            'endpoint' => [
                'title' => 'Webhook Endpoint',
                'description' => 'Configure your WordPress plugin to send webhooks to this endpoint with the :header header containing the HMAC-SHA256 signature.',
            ],
            'payload_modal' => [
                'title' => 'Webhook Payload',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Content (content.blade.php)
    |--------------------------------------------------------------------------
    */
    'content' => [
        'title' => 'Content',
        'subtitle' => 'Manage your WordPress content',
        'new_post' => 'New Post',
        'new_page' => 'New Page',
        'tabs' => [
            'posts' => 'Posts',
            'pages' => 'Pages',
            'media' => 'Media',
        ],
        'filters' => [
            'all_status' => 'All Status',
            'published' => 'Published',
            'draft' => 'Draft',
            'pending' => 'Pending',
            'private' => 'Private',
            'sort' => 'Sort',
            'date' => 'Date',
            'title' => 'Title',
            'status' => 'Status',
        ],
        'columns' => [
            'id' => 'ID',
            'title' => 'Title',
            'status' => 'Status',
            'date' => 'Date',
            'modified' => 'Modified',
        ],
        'untitled' => 'Untitled',
        'no_media' => 'No media found',
        'no_posts' => 'No posts found',
        'no_pages' => 'No pages found',
        'actions' => [
            'edit' => 'Edit',
            'view' => 'View',
            'duplicate' => 'Duplicate',
            'delete' => 'Delete',
            'delete_confirm' => 'Are you sure you want to delete this?',
        ],
        'editor' => [
            'new' => 'New',
            'edit' => 'Edit',
            'title_label' => 'Title',
            'title_placeholder' => 'Enter title...',
            'status_label' => 'Status',
            'status' => [
                'draft' => 'Draft',
                'publish' => 'Published',
                'pending' => 'Pending Review',
                'private' => 'Private',
            ],
            'excerpt_label' => 'Excerpt',
            'excerpt_placeholder' => 'Brief summary...',
            'content_label' => 'Content',
            'content_placeholder' => 'Write your content here... (HTML supported)',
            'cancel' => 'Cancel',
            'create' => 'Create',
            'update' => 'Update',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Editor (content-editor.blade.php)
    |--------------------------------------------------------------------------
    */
    'content_editor' => [
        'title' => [
            'edit' => 'Edit Content',
            'new' => 'New Content',
        ],
        'save_status' => [
            'last_saved' => 'Last saved :time',
            'not_saved' => 'Not saved',
            'unsaved_changes' => 'Unsaved changes',
            'revisions' => ':count revision|:count revisions',
        ],
        'actions' => [
            'ai_assist' => 'AI Assist',
            'save_draft' => 'Save Draft',
            'schedule' => 'Schedule',
            'publish' => 'Publish',
        ],
        'status' => [
            'draft' => 'Draft',
            'pending' => 'Pending',
            'publish' => 'Published',
            'future' => 'Scheduled',
            'private' => 'Private',
        ],
        'fields' => [
            'title_placeholder' => 'Enter title...',
            'url_slug' => 'URL Slug',
            'type' => 'Type',
            'type_page' => 'Page',
            'type_post' => 'Post',
            'excerpt' => 'Excerpt',
            'excerpt_description' => 'Brief summary for search results and previews',
            'content' => 'Content',
            'content_placeholder' => 'Start writing your content...',
        ],
        'sidebar' => [
            'settings' => 'Settings',
            'seo' => 'SEO',
            'media' => 'Media',
            'history' => 'History',
        ],
        'scheduling' => [
            'title' => 'Scheduling',
            'schedule_later' => 'Schedule for later',
            'schedule_description' => 'Publish at a specific date and time',
            'publish_date' => 'Publish date',
        ],
        'categories' => [
            'title' => 'Categories',
            'none' => 'No categories yet',
        ],
        'tags' => [
            'title' => 'Tags',
            'add_placeholder' => 'Add tag...',
        ],
        'seo' => [
            'title' => 'Search Engine Optimisation',
            'meta_title' => 'Meta title',
            'meta_title_description' => 'Recommended: 50-60 characters',
            'meta_title_placeholder' => 'Page title',
            'characters' => ':count/:max characters',
            'meta_description' => 'Meta description',
            'meta_description_description' => 'Recommended: 150-160 characters',
            'meta_description_placeholder' => 'Brief description for search results...',
            'focus_keywords' => 'Focus keywords',
            'focus_keywords_placeholder' => 'keyword1, keyword2, keyword3',
            'preview_title' => 'Search preview',
            'preview_description_fallback' => 'Page description will appear here...',
        ],
        'media' => [
            'featured_image' => 'Featured Image',
            'drag_drop' => 'Drag and drop an image, or',
            'browse' => 'browse',
            'upload' => 'Upload',
            'select_from_library' => 'Or select from library',
        ],
        'revisions' => [
            'title' => 'Revision History',
            'no_revisions' => 'No revisions yet. Save your content to create the first revision.',
            'save_first' => 'Save your content first to start tracking revisions.',
            'restore' => 'Restore',
            'words' => ':count words',
            'change_types' => [
                'publish' => 'Publish',
                'edit' => 'Edit',
                'restore' => 'Restore',
                'schedule' => 'Schedule',
            ],
        ],
        'ai' => [
            'command_placeholder' => 'Search AI commands or type a prompt...',
            'quick_actions' => 'Quick Actions',
            'result_title' => 'AI Result',
            'discard' => 'Discard',
            'insert' => 'Insert',
            'replace_content' => 'Replace Content',
            'run' => 'Run',
            'processing' => 'Processing...',
            'thinking' => 'AI is thinking...',
            'cancel' => 'Cancel',
            'footer_close' => 'Press :key to close',
            'footer_powered' => 'Powered by Claude and Gemini',
        ],
    ],
];
