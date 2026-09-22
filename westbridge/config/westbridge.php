<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| WestBridge platform configuration
|--------------------------------------------------------------------------
| Structural configuration that changes with a deployment.
| Anything the business changes without a developer belongs in `settings`
| (managed from the admin panel), NOT here.
*/

return [

    'brand' => [
        'navy' => '#1C2B4F',
        'lime' => '#89C726',
        'tagline' => 'Connecting Ideas. Building Tomorrow.',
    ],

    /*
    | Document numbering — WB-{SERIES}-{YEAR}-{000001}
    | Implemented by DocumentNumberService in Phase 6 with a locked sequence row.
    */
    'documents' => [
        'prefix' => 'WB',
        'padding' => 6,
        'series' => [
            'order' => 'ORD',
            'quotation' => 'QTN',
            'invoice' => 'INV',
            'receipt' => 'RCP',
        ],
        'reset_annually' => true,
    ],

    /*
    | Commerce defaults. Currency is pending decision D2 — USD transacting with
    | an SSP display equivalent is the recommendation, and the value below is
    | the placeholder until that is confirmed.
    */
    'shop' => [
        'currency' => env('WB_CURRENCY', 'USD'),
        'display_currency' => env('WB_DISPLAY_CURRENCY', 'SSP'),
        'reservation_minutes' => 120,
        'low_stock_threshold' => 5,
    ],

    /*
    | Behind Cloudflare or a hosting load balancer, set TRUSTED_PROXIES=* in
    | .env so Laravel sees HTTPS and the visitor's real address. Leave it
    | unset on a server that faces the internet directly.
    */
    'trusted_proxies' => env('TRUSTED_PROXIES'),

    'content' => [
        // Insights stays out of the navigation until six posts exist (decision D5).
        'show_insights' => env('WB_SHOW_INSIGHTS', false),
        'minimum_posts_before_launch' => 6,
        'minimum_case_studies_to_show_block' => 3,
    ],

    /*
    | Navigation. Phase 4 swaps shop_categories for a database query; the array
    | shape is identical so the header and drawer templates do not change.
    */
    'navigation' => [
        'solutions' => [
            [
                'title' => 'Software Development',
                'blurb' => 'Business systems, web and mobile applications built around how you work.',
                'url' => '/services/software-development',
                'icon' => '<svg class="h-4.5 w-4.5" viewBox="0 0 20 20" fill="none"><path d="M7 6l-4 4 4 4M13 6l4 4-4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            ],
            [
                'title' => 'IT & Networking',
                'blurb' => 'WiFi, office networks, router configuration, maintenance and IT support.',
                'url' => '/services/it-networking',
                'icon' => '<svg class="h-4.5 w-4.5" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="14" r="1.6" fill="currentColor"/><path d="M6.5 11a5 5 0 017 0M4 8.3a8.7 8.7 0 0112 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
            ],
            [
                'title' => 'Starlink Solutions',
                'blurb' => 'Equipment supply, installation, configuration and ongoing support.',
                'url' => '/services/starlink',
                'icon' => '<svg class="h-4.5 w-4.5" viewBox="0 0 20 20" fill="none"><path d="M4 16l5.5-5.5M11 4.5a6.5 6.5 0 014.5 4.5L9 14 5.5 10.5 11 4.5z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>',
            ],
            [
                'title' => 'CCTV & Intercom Systems',
                'blurb' => 'Security cameras, recording, remote viewing and door and gate intercoms.',
                'url' => '/services/cctv-intercom',
                'icon' => '<svg class="h-4.5 w-4.5" viewBox="0 0 20 20" fill="none"><path d="M2.5 6.5l10-3 1.2 4.2-10 3z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M13.7 7.7l3-1v4l-2.5.7M6.5 9.7V13H3.5M3.5 11.5v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            ],
        ],

        'shop_categories' => [
            ['title' => 'Laptops', 'url' => '/shop/laptops', 'count' => null],
            ['title' => 'Phones', 'url' => '/shop/phones', 'count' => null],
            ['title' => 'Accessories', 'url' => '/shop/accessories', 'count' => null],
            ['title' => 'Networking', 'url' => '/shop/networking', 'count' => null],
            ['title' => 'Starlink', 'url' => '/shop/starlink', 'count' => null],
            ['title' => 'CCTV Cameras', 'url' => '/shop/cctv-cameras', 'count' => null],
            ['title' => 'Intercom Systems', 'url' => '/shop/intercom-systems', 'count' => null],
            ['title' => 'Computer Accessories', 'url' => '/shop/computer-accessories', 'count' => null],
        ],
    ],

    'business_areas' => [
        [
            'eyebrow' => 'Digital solutions',
            'title' => 'Software & Systems',
            'blurb' => 'Business systems, web and mobile applications, and the digital transformation work around them.',
            'cta' => 'Build your solution',
            'url' => '/services/software-development',
        ],
        [
            'eyebrow' => 'Connectivity',
            'title' => 'Internet & Networks',
            'blurb' => 'WiFi, office networking and Starlink — getting a site online and keeping it there.',
            'cta' => 'Get connected',
            'url' => '/services/starlink',
        ],
        [
            'eyebrow' => 'Technology products',
            'title' => 'Equipment Supply',
            'blurb' => 'Laptops, phones, accessories and networking hardware, supplied and configured.',
            'cta' => 'Visit the shop',
            'url' => '/shop',
        ],
        [
            'eyebrow' => 'IT services',
            'title' => 'Support & Infrastructure',
            'blurb' => 'Installation, maintenance, consultancy and the technical support that follows it.',
            'cta' => 'Get IT support',
            'url' => '/services/it-networking',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Homepage content (QT-style layout)
    |--------------------------------------------------------------------------
    | Every figure below is a fact the site itself can back up - the number of
    | service lines, the number of system types listed, the store categories.
    | No invented client counts, years or headcounts. Replace these with real
    | business figures (clients served, installations, years) once you have
    | them, and the counters animate to whatever is here.
    */
    'home' => [
        'services' => [
            [
                'title' => 'Software Development',
                'body' => 'Web applications, mobile apps and business systems designed around how your organisation actually works.',
                'url' => '/services/software-development',
                'icon' => 'code',
            ],
            [
                'title' => 'IT & Networking',
                'body' => 'Office networks, WiFi installation, router configuration, maintenance and day-to-day IT support.',
                'url' => '/services/it-networking',
                'icon' => 'network',
            ],
            [
                'title' => 'Starlink Solutions',
                'body' => 'Equipment supply, professional installation, configuration and ongoing support for satellite connectivity.',
                'url' => '/services/starlink',
                'icon' => 'satellite',
            ],
            [
                'title' => 'CCTV & Intercom',
                'body' => 'Security cameras with recording and remote viewing on your phone, plus door and gate intercom systems.',
                'url' => '/services/cctv-intercom',
                'icon' => 'camera',
            ],
            [
                'title' => 'Technology Products',
                'body' => 'Laptops, phones, accessories and networking equipment - supplied, configured and supported.',
                'url' => '/shop',
                'icon' => 'device',
            ],
        ],

        'showcase' => [
            [
                'eyebrow' => 'Business systems',
                'title' => 'ERP & Business Management',
                'body' => 'One system for sales, inventory, finance and staff - replacing the spreadsheets and paper most organisations outgrow long before they replace them.',
                'points' => ['Inventory & sales', 'Finance & reporting', 'Staff & payroll', 'Role-based access'],
                'url' => '/services/software-development',
                'mock' => 'dashboard',
            ],
            [
                'eyebrow' => 'Education',
                'title' => 'School Management Systems',
                'body' => 'Admissions, fees, attendance, results and parent communication in one place, built for how schools here are actually run.',
                'points' => ['Student records', 'Fees & receipts', 'Results & report cards', 'Parent updates'],
                'url' => '/services/software-development',
                'mock' => 'school',
            ],
            [
                'eyebrow' => 'Connectivity',
                'title' => 'Starlink & Network Installation',
                'body' => 'From the dish on the roof to the device on the desk: satellite connectivity, site-wide WiFi and the network in between, installed and supported.',
                'points' => ['Site survey', 'Installation', 'WiFi coverage', 'Ongoing support'],
                'url' => '/services/starlink',
                'mock' => 'network',
            ],
            [
                'eyebrow' => 'Security',
                'title' => 'CCTV & Intercom Installation',
                'body' => 'Cameras placed where they matter, recording you can rely on, footage on your phone, and intercoms that let you see who is at the gate before you open it.',
                'points' => ['Site survey', 'HD & IP cameras', 'Remote viewing', 'Door & gate intercoms'],
                'url' => '/services/cctv-intercom',
                'mock' => 'cctv',
            ],
            [
                'eyebrow' => 'Technology store',
                'title' => 'Equipment Supply',
                'body' => 'Business laptops, phones, accessories and networking hardware - bought from the same team that will set it up and support it.',
                'points' => ['Laptops', 'Phones', 'Networking gear', 'Accessories'],
                'url' => '/shop',
                'mock' => 'store',
            ],
        ],

        'stats' => [
            ['value' => 5, 'suffix' => '', 'label' => 'Service areas under one roof'],
            ['value' => 10, 'suffix' => '+', 'label' => 'Types of business system we build'],
            ['value' => 8, 'suffix' => '', 'label' => 'Product categories in store'],
            ['value' => 1, 'suffix' => '', 'label' => 'Partner from software to signal'],
        ],

        'technologies' => [
            'Laravel', 'PHP', 'MySQL', 'Livewire', 'Tailwind CSS', 'REST APIs',
            'Android', 'iOS', 'Starlink', 'WiFi 6', 'Structured cabling', 'Cloud hosting',
            'IP CCTV', 'NVR recording', 'Video intercoms',
        ],

        'industries' => [
            ['title' => 'Education', 'body' => 'Schools, colleges and training institutions.', 'icon' => 'education'],
            ['title' => 'Government', 'body' => 'Ministries, agencies and public institutions.', 'icon' => 'government'],
            ['title' => 'NGOs & Development', 'body' => 'Field offices, programmes and partners.', 'icon' => 'ngo'],
            ['title' => 'Finance', 'body' => 'Microfinance, SACCOs and lending businesses.', 'icon' => 'finance'],
            ['title' => 'Retail & Trade', 'body' => 'Shops, wholesalers and distributors.', 'icon' => 'retail'],
            ['title' => 'Healthcare', 'body' => 'Clinics, pharmacies and health programmes.', 'icon' => 'health'],
        ],
    ],

    'capabilities' => [
        [
            'title' => 'Software Development',
            'blurb' => 'Web, mobile and business systems.',
            'url' => '/services/software-development',
            'icon' => '<svg class="h-5 w-5" viewBox="0 0 20 20" fill="none"><path d="M7 6l-4 4 4 4M13 6l4 4-4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        ],
        [
            'title' => 'IT Infrastructure',
            'blurb' => 'Setup, support and maintenance.',
            'url' => '/services/it-networking',
            'icon' => '<svg class="h-5 w-5" viewBox="0 0 20 20" fill="none"><rect x="3" y="4" width="14" height="5" rx="1" stroke="currentColor" stroke-width="1.5"/><rect x="3" y="11" width="14" height="5" rx="1" stroke="currentColor" stroke-width="1.5"/><circle cx="6" cy="6.5" r="0.9" fill="currentColor"/><circle cx="6" cy="13.5" r="0.9" fill="currentColor"/></svg>',
        ],
        [
            'title' => 'Networking',
            'blurb' => 'WiFi and office networks.',
            'url' => '/services/it-networking',
            'icon' => '<svg class="h-5 w-5" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="14" r="1.6" fill="currentColor"/><path d="M6.5 11a5 5 0 017 0M4 8.3a8.7 8.7 0 0112 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
        ],
        [
            'title' => 'Starlink',
            'blurb' => 'Supply, installation and support.',
            'url' => '/services/starlink',
            'icon' => '<svg class="h-5 w-5" viewBox="0 0 20 20" fill="none"><path d="M4 16l5.5-5.5M11 4.5a6.5 6.5 0 014.5 4.5L9 14 5.5 10.5 11 4.5z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>',
        ],
        [
            'title' => 'Technology Products',
            'blurb' => 'Laptops, phones and equipment.',
            'url' => '/shop',
            'icon' => '<svg class="h-5 w-5" viewBox="0 0 20 20" fill="none"><rect x="3.5" y="5" width="13" height="8" rx="1" stroke="currentColor" stroke-width="1.5"/><path d="M2 15.5h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
        ],
    ],
];
