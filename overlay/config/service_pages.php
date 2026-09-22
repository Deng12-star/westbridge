<?php

/*
|--------------------------------------------------------------------------
| Service pages - /services/... (config/service_pages.php)
|--------------------------------------------------------------------------
| One entry per service line. The page template reads everything from here.
| Only scope is described - what the work covers - with no invented
| claims, client counts or guarantees.
|
| shop_categories: slugs of shop categories whose products are shown on the
| page ("Equipment we supply"). Hidden when they have no products.
*/

return [

    'software' => [
        'route' => 'services.software',
        'eyebrow' => 'Digital solutions',
        'title' => 'Software Development',
        'icon' => 'code',
        'mock' => 'dashboard',
        'intro' => 'Business systems, websites and mobile apps designed around how your organisation actually works - built, hosted and supported by the same team.',
        'includes' => [
            ['title' => 'Business & ERP systems', 'body' => 'Sales, inventory, finance, payroll and staff in one system instead of scattered spreadsheets.'],
            ['title' => 'School management systems', 'body' => 'Admissions, fees, attendance, results and parent communication.'],
            ['title' => 'Websites & web applications', 'body' => 'Company websites, portals and online services that you can update yourself.'],
            ['title' => 'Mobile apps', 'body' => 'Android and iOS apps for your customers, field staff or members.'],
            ['title' => 'Integrations & APIs', 'body' => 'Connecting your systems to each other, to mobile money and to SMS or email.'],
            ['title' => 'Hosting & maintenance', 'body' => 'Secure hosting, backups, updates and support after launch.'],
        ],
        'shop_categories' => [],
        'portfolio_service' => 'Software Development',
    ],

    'networking' => [
        'route' => 'services.networking',
        'eyebrow' => 'IT infrastructure',
        'title' => 'IT & Networking',
        'icon' => 'network',
        'mock' => 'network',
        'intro' => 'Office networks, WiFi and the computers on them - installed properly, documented, and supported when something stops working.',
        'includes' => [
            ['title' => 'Network design & installation', 'body' => 'Wired and wireless networks for offices, schools, hotels and compounds.'],
            ['title' => 'Structured cabling', 'body' => 'Neat, labelled cabling and network cabinets that are easy to maintain.'],
            ['title' => 'WiFi coverage', 'body' => 'Access points placed for reliable signal across the whole site.'],
            ['title' => 'Router, switch & firewall setup', 'body' => 'Secure configuration, guest networks and bandwidth control.'],
            ['title' => 'Computer & server setup', 'body' => 'New machines configured, networked and ready to use.'],
            ['title' => 'IT support & maintenance', 'body' => 'Troubleshooting, upgrades and ongoing support for your team.'],
        ],
        'shop_categories' => ['networking', 'computer-accessories'],
        'portfolio_service' => 'IT & Networking',
    ],

    'starlink' => [
        'route' => 'services.starlink',
        'eyebrow' => 'Connectivity',
        'title' => 'Starlink Solutions',
        'icon' => 'satellite',
        'mock' => 'network',
        'intro' => 'Satellite internet from the dish on the roof to the device on the desk: equipment, installation, configuration and support.',
        'includes' => [
            ['title' => 'Equipment supply', 'body' => 'Starlink kits and the mounting and networking accessories around them.'],
            ['title' => 'Site survey', 'body' => 'Checking the best position for a clear view of the sky before anything is installed.'],
            ['title' => 'Mounting & installation', 'body' => 'Secure roof, wall or pole mounting with tidy cable runs.'],
            ['title' => 'Configuration', 'body' => 'Activation, network setup and connecting your devices.'],
            ['title' => 'WiFi extension', 'body' => 'Extending coverage across larger buildings and compounds.'],
            ['title' => 'Ongoing support', 'body' => 'Help when the connection drops or the setup needs to change.'],
        ],
        'shop_categories' => ['starlink'],
        'portfolio_service' => 'Starlink Solutions',
    ],

    'cctv' => [
        'route' => 'services.cctv',
        'eyebrow' => 'Security systems',
        'title' => 'CCTV & Intercom Systems',
        'icon' => 'camera',
        'mock' => 'cctv',
        'intro' => 'Camera and intercom systems for offices, homes, schools, shops and compounds - planned for your site, installed, and viewable from your phone.',
        'includes' => [
            ['title' => 'Site survey & camera plan', 'body' => 'Walking the site to decide where cameras go and what each one needs to see.'],
            ['title' => 'CCTV camera installation', 'body' => 'Indoor and outdoor HD and IP cameras, including night vision.'],
            ['title' => 'Recording & storage', 'body' => 'NVR or DVR recorders with the storage to keep the footage you need.'],
            ['title' => 'Remote viewing', 'body' => 'Live and recorded footage on your phone or computer, wherever you are.'],
            ['title' => 'Door & gate intercoms', 'body' => 'Audio and video intercoms so you can see and speak to visitors before letting them in.'],
            ['title' => 'Maintenance & support', 'body' => 'Checks, repairs and upgrades as your site grows.'],
        ],
        'shop_categories' => ['cctv-cameras', 'intercom-systems'],
        'portfolio_service' => 'CCTV & Intercom Systems',
    ],

    // Shared by every service page.
    'process' => [
        ['title' => 'Understand', 'body' => 'We visit or call to understand the site, the people and what you need.'],
        ['title' => 'Propose', 'body' => 'A clear proposal: what is included, what it costs and how long it takes.'],
        ['title' => 'Deliver', 'body' => 'Installed or built in stages you can see, with no surprises at the end.'],
        ['title' => 'Hand over', 'body' => 'Training for your team and documentation of what was set up.'],
        ['title' => 'Support', 'body' => 'Ongoing help as your organisation and needs change.'],
    ],

];
