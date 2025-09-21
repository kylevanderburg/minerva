<?php
// Site Identity
$minervaConfig = [
    'site_name' => 'Minerva',
    'site_tagline' => 'Knowledge is Power',
    'logo_url' => '/Minerva.svg', // Could be PNG or SVG
    'favicon_url' => '/Minerva.ico',

    // Paths
    'content_dir' => __DIR__ . '/content', // You can override this for external content repo
    'users_file' => __DIR__ . '/auth/config-users.json',

    // Markdown Parser
    'use_parsedown_extra' => false, // Set to true if you want to use ParsedownExtra

    // Navigation
    'show_homepage_links' => true,  // Show index of books on homepage
    'default_book' => null,         // Optional: Set to a default book folder

    // Access
    'allow_public_viewing' => true, // If false, all access requires login
    'private_books' => ['drafts', 'archive'], // Hide these unless authenticated

    // Admin
    'admin_email' => 'you@example.com', // For future email integration or contact
];
