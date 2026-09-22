<?php

/*
|--------------------------------------------------------------------------
| Portfolio - delivered projects
|--------------------------------------------------------------------------
|
| Every statement below describes what the live system visibly does. There
| are no invented clients, metrics, testimonials or results. Lines marked
| CONFIRM are for you to check before launch.
|
| Screenshots: drop an image at public/portfolio/{slug}.jpg (1600x1000 works
| well). It is picked up automatically; until then a drawn panel stands in.
|
| live_url: shown as a "Visit the live system" button only when
| show_live_link is true.
|
| Phase 3 moves these into the projects table; the array shape is the one
| the views already read, so nothing else changes.
*/

return [

    [
        'slug' => 'dream-bridge-payroll-system',
        'title' => 'Dream Bridge Payroll System',
        'client' => 'Dream Bridge Consultants Ltd.',
        'location' => 'Juba, South Sudan',
        'industry' => 'Consulting',
        'category' => 'Payroll & HR',
        'service' => 'Software Development',
        'status' => 'Live',
        'year' => null, // CONFIRM - the site footer reads 2024
        'mock' => 'payroll',
        'live_url' => 'https://dreambridge-payroll.com',
        'show_live_link' => true,
        'summary' => 'A web payroll system that lets staff check and calculate what they are owed, and produces payment files for the finance team.',
        'overview' => [
            'Payroll touches every employee every month, so staff and finance both need one set of numbers they can rely on.',
            'The Dream Bridge Payroll System is a secure, login-protected web application built for the company\'s employees. Staff can see and calculate their financial dues, and the system generates the payment files used to pay them.',
        ],
        'scope' => [
            'Employee salary records and pay calculation',
            'Payslips that staff can view for themselves',
            'Payment schedules and payment-file generation',
            'HR records for the organisation\'s staff',
            'Secure sign-in for every user',
            'Branded public landing page for the system',
        ],
    ],

    [
        'slug' => 'myloan-loan-management-system',
        'title' => 'myloan - Loan Management System',
        'client' => null, // CONFIRM - who is this built for?
        'location' => 'South Sudan',
        'industry' => 'Finance',
        'category' => 'Loan Management',
        'service' => 'Software Development',
        'status' => 'In development',
        'year' => null,
        'mock' => 'loans',
        'live_url' => null, // runs locally - add the address once it is hosted
        'show_live_link' => false,
        'summary' => 'A loan management system where employees and clients apply for loans online, follow their status and calculate repayments.',
        'overview' => [
            'Paper loan applications are slow to process and hard to track, for the applicant and the lender alike.',
            'myloan moves the whole cycle online. Employees and clients register an account, apply for a loan, check its status at any time, and calculate what their repayments will be before they commit.',
        ],
        'scope' => [
            'Self-service registration and sign-in',
            'Online loan applications',
            'Application status tracking',
            'Repayment calculator',
            'Built for both employees and clients',
        ],
    ],

    [
        'slug' => 'db-suk-online-store',
        'title' => 'DB Suk Online Store',
        'client' => null, // CONFIRM
        'location' => 'Juba, South Sudan',
        'industry' => 'Retail',
        'category' => 'E-commerce',
        'service' => 'Software Development',
        'status' => 'Live',
        'year' => null,
        'mock' => 'shop',
        'live_url' => 'https://db-suk.com',
        // Off until the store's demo catalogue and placeholder blog posts are
        // replaced with real ones - a prospect clicking through should see a
        // finished shop.
        'show_live_link' => false,
        'summary' => 'A full online store for Juba with a multi-level catalogue, flash deals, wishlists, order tracking and pricing in both US dollars and South Sudanese pounds.',
        'overview' => [
            'Shopping online in South Sudan means dealing with two currencies and customers who want to know exactly where their order is.',
            'DB Suk is a complete e-commerce platform: a catalogue organised in three levels of category, time-limited campaigns and flash deals, customer accounts with wishlists and product comparison, and order tracking. Prices switch between USD and SSP.',
        ],
        'scope' => [
            'Catalogue with category, sub-category and child-category levels',
            'Campaigns, flash deals and deals-of-the-week countdowns',
            'Brand pages, product comparison and wishlists',
            'Cart, customer accounts and order tracking',
            'Prices in USD and South Sudanese pounds',
            'Blog, newsletter sign-up and policy pages',
        ],
    ],

];
