<?php
declare(strict_types=1);

$form = $form ?? [];
$currencies = $currencies ?? [];
$featuredDestinations = $featured_destinations ?? [];
$homeContent = $home_content ?? [];
$error = $error ?? null;

$badges = is_array($homeContent['badges'] ?? null) ? $homeContent['badges'] : [];
$heroSlides = is_array($homeContent['hero_slides'] ?? null) ? $homeContent['hero_slides'] : [];
$contactChannels = is_array($homeContent['contact_channels'] ?? null) ? $homeContent['contact_channels'] : [];
$sections = is_array($homeContent['sections'] ?? null) ? $homeContent['sections'] : [];
$publicLayoutMode = 'immersive';
$publicT = is_callable($public_t ?? null) ? $public_t : static fn (string $key, string $fallback): string => $fallback;
$publicLocale = in_array((string) ($public_locale ?? 'en'), ['en', 'es'], true) ? (string) $public_locale : 'en';
$brandName = trim((string) ($homeContent['brand_name'] ?? 'Express Transfers'));
$brandName = $brandName !== '' ? $brandName : 'Express Transfers';
$withBrand = static fn (string $text): string => strtr($text, [
    '{{brand}}' => $brandName,
    '%brand%' => $brandName,
]);

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$truncate = static function (string $text, int $limit): string {
    $text = trim($text);
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $limit - 1)) . '...';
};

$channelMeta = [
    'whatsapp' => ['badge' => 'WA', 'default_scheme' => 'https://wa.me/'],
    'call' => ['badge' => 'CALL', 'default_scheme' => 'tel:'],
    'sms' => ['badge' => 'SMS', 'default_scheme' => 'sms:'],
    'telegram' => ['badge' => 'TG', 'default_scheme' => 'https://t.me/'],
    'email' => ['badge' => 'MAIL', 'default_scheme' => 'mailto:'],
];

$channelIconSvg = static function (string $type): string {
    return match ($type) {
        'whatsapp' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.65 15l-1.15 5 5.12-1.1A10 10 0 1 0 12 2Zm0 18a8 8 0 0 1-4.06-1.11l-.29-.17-2.99.64.67-2.92-.19-.3A8 8 0 1 1 12 20Zm4.46-5.9c-.24-.12-1.41-.7-1.63-.78-.22-.08-.38-.12-.54.12-.16.24-.62.78-.76.94-.14.16-.28.18-.52.06a6.58 6.58 0 0 1-1.94-1.2 7.24 7.24 0 0 1-1.34-1.67c-.14-.24-.01-.37.1-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.31-.74-1.79-.19-.46-.39-.4-.54-.41h-.46c-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.69 2.58 4.1 3.62.57.25 1.02.4 1.37.51.58.18 1.1.16 1.51.1.46-.07 1.41-.58 1.61-1.14.2-.56.2-1.04.14-1.14-.06-.1-.22-.16-.46-.28Z"/></svg>',
        'call' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.62 10.79a15.46 15.46 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.01-.24c1.12.37 2.33.57 3.58.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.85 21 3 13.15 3 3a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.46.57 3.58a1 1 0 0 1-.25 1.01l-2.2 2.2Z"/></svg>',
        'sms' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H8l-4 3v-3H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm1 4h14v2H5V8Zm0 4h10v2H5v-2Z"/></svg>',
        'telegram' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21.94 4.66a1 1 0 0 0-1.06-.15L3.7 11.5a1 1 0 0 0 .07 1.88l3.86 1.35 1.43 4.54a1 1 0 0 0 1.75.31l2.15-2.78 3.68 2.7a1 1 0 0 0 1.57-.57l3.82-13.1a1 1 0 0 0-.09-.86Zm-4.95 3.09-6.76 6.34-.42 2.42-.85-2.69 8.03-7.53Z"/></svg>',
        'email' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5h18a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Zm0 2v.01L12 13l9-5.99V7H3Z"/></svg>',
        default => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/></svg>',
    };
};

$buildChannelUrl = static function (array $channel) use ($channelMeta): string {
    $type = strtolower(trim((string) ($channel['type'] ?? '')));
    $url = trim((string) ($channel['url'] ?? ''));
    $value = trim((string) ($channel['value'] ?? ''));

    if ($url !== '') {
        return $url;
    }

    if (!isset($channelMeta[$type])) {
        return '#contact-channels';
    }

    $normalizedValue = preg_replace('/\s+/', '', $value ?? '') ?? '';

    if ($type === 'telegram') {
        $normalizedValue = ltrim($normalizedValue, '@');
    }

    return $channelMeta[$type]['default_scheme'] . $normalizedValue;
};

$heroImages = is_array($homeContent['hero_images'] ?? null) && !empty($homeContent['hero_images'])
    ? array_values(array_filter(array_map('strval', $homeContent['hero_images'] ?? [])))
    : [
    'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=1600&q=80',
    'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=1600&q=80',
    'https://images.unsplash.com/photo-1570129477492-45c003edd2be?auto=format&fit=crop&w=1600&q=80',
];
$showSocialLinks = !empty($sections['show_social_links']);
$showHeroBadges = !empty($sections['show_hero_badges']);
$showBookingPanel = !empty($sections['show_booking_panel']);
$showRoutes = !empty($sections['show_routes']);

$projectRoot = dirname(__DIR__, 5);
$publicRoot = $projectRoot . '/public_html';

$resolveMediaPath = static function (string $candidate) use ($publicRoot): ?string {
    $candidate = trim($candidate);
    if ($candidate === '') {
        return null;
    }

    if (preg_match('#^https?://#i', $candidate) === 1) {
        return $candidate;
    }

    $path = str_starts_with($candidate, '/') ? $candidate : '/' . ltrim($candidate, '/');
    $relativePath = ltrim($path, '/');
    if (!str_starts_with($relativePath, 'assets/')) {
        return null;
    }

    return is_file($publicRoot . '/' . $relativePath) ? $path : null;
};

$heroVideoUrl = null;
$heroVideoCandidates = [];
$customHeroVideo = trim((string) ($homeContent['hero_video_url'] ?? ''));
if ($customHeroVideo !== '') {
    $heroVideoCandidates[] = $customHeroVideo;
}
$heroVideoCandidates = array_merge($heroVideoCandidates, [
    '/assets/hero-video.mp4',
    '/assets/hero-video.webm',
    '/assets/hero-loop.mp4',
]);

foreach ($heroVideoCandidates as $candidate) {
    $resolvedMedia = $resolveMediaPath((string) $candidate);
    if ($resolvedMedia !== null) {
        $heroVideoUrl = $resolvedMedia;
        break;
    }
}

$searchPanelLayout = (string) ($homeContent['search_panel_layout'] ?? 'center-horizontal');
if (!in_array($searchPanelLayout, ['center-horizontal', 'left-vertical', 'right-vertical'], true)) {
    $searchPanelLayout = 'center-horizontal';
}

$searchPanelHeadline = $publicT('search.title', 'Check availability');

$heroTitleCustom = trim((string) ($homeContent['hero_title'] ?? ''));
$heroSubtitleCustom = trim((string) ($homeContent['hero_subtitle'] ?? ''));

$heroTitle = $publicLocale === 'en' && $heroTitleCustom !== ''
    ? $heroTitleCustom
    : $publicT('hero.title', 'Private airport arrivals handled with chauffeur-level calm.');

$heroSubtitle = $publicLocale === 'en' && $heroSubtitleCustom !== ''
    ? $heroSubtitleCustom
    : $publicT('hero.subtitle', 'Book Cancun and Riviera Maya transfers through a cleaner, more premium interface designed to feel closer to a concierge...');

$heroBadgesToRender = [];
if ($showHeroBadges) {
    if ($publicLocale === 'en' && !empty($badges)) {
        $heroBadgesToRender = array_values(array_filter(array_map('strval', $badges)));
    } else {
        $heroBadgesToRender = [
            $publicT('hero.badge.private', 'Private vehicles only'),
            $publicT('hero.badge.monitoring', 'Arrival monitoring included'),
            $publicT('hero.badge.rates', 'Rates visible before checkout'),
        ];
    }
}

$pageStyles = [
    '/assets/public-home.css',
    '/assets/public-floating-contact.css',
];
$pageScripts = [
    '/assets/public-home.js',
];

$routeCards = array_slice($featuredDestinations, 0, 3);
$showcaseImages = [
    '/assets/hero-transfer-1.svg',
    '/assets/hero-transfer-2.svg',
    '/assets/hero-transfer-3.svg',
    'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=1600&q=80',
    'https://images.unsplash.com/photo-1505753065532-68713e211a3d?auto=format&fit=crop&w=1600&q=80',
    'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1600&q=80',
];
?>
<div class="lux-home travel-home travel-home--<?= $escape($searchPanelLayout) ?>">
    <section class="hero-stack hero-stack--<?= $escape($searchPanelLayout) ?>">
        <div class="hero-media<?= $heroVideoUrl !== null ? ' has-video' : '' ?>">
            <?php if ($heroVideoUrl !== null): ?>
                <?php
                $videoMime = str_ends_with(strtolower($heroVideoUrl), '.webm') ? 'video/webm' : 'video/mp4';
                $videoPoster = $heroImages[0] ?? '/assets/hero-transfer-1.svg';
                ?>
                <div class="hero-video-layer" aria-hidden="true">
                    <video class="hero-video" autoplay muted loop playsinline preload="metadata" poster="<?= $escape($videoPoster) ?>">
                        <source src="<?= $escape($heroVideoUrl) ?>" type="<?= $escape($videoMime) ?>">
                    </video>
                </div>
            <?php endif; ?>

            <?php
            $slides = !empty($heroSlides) ? array_values(array_filter($heroSlides, 'is_array')) : [];
            if (empty($slides)) {
                $slides = [
                    ['title' => 'Private airport transfers', 'text' => 'Luxury transportation in Cancun and Riviera Maya.', 'label' => 'Reserve transfer', 'href' => '#booking-form'],
                    ['title' => 'Punctual and discreet service', 'text' => 'Clean booking flow with human support.', 'label' => 'Contact', 'href' => '#contact-channels'],
                    ['title' => 'Premium routes', 'text' => 'Top destinations with private vehicles only.', 'label' => 'Explore routes', 'href' => '#routes'],
                ];
            }
            ?>

            <div class="hero-slide-layer" aria-hidden="true">
                <?php foreach ($slides as $index => $slide): ?>
                    <?php $imageUrl = $heroImages[$index % count($heroImages)]; ?>
                    <?php $fallbackHero = 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=1600&q=80'; ?>
                    <div class="hero-slide<?= $index === 0 ? ' is-active' : '' ?>" data-hero-slide style="background-image: url('<?= $escape($imageUrl) ?>'), url('<?= $escape($fallbackHero) ?>');"></div>
                <?php endforeach; ?>
            </div>

            <div class="hero-content">
                <div class="hero-layout">
                    <div class="hero-main">
                        <div class="hero-kicker"><?= $escape($publicT('hero.eyebrow', 'Cancun Airport transfer concierge')) ?></div>
                        <h1><?= $escape($truncate($heroTitle, 64)) ?></h1>
                        <p class="hero-summary"><?= $escape($truncate($heroSubtitle, 120)) ?></p>

                        <?php if (!empty($heroBadgesToRender)): ?>
                            <div class="hero-badges">
                                <?php foreach (array_slice($heroBadgesToRender, 0, 3) as $badge): ?>
                                    <span><?= $escape($badge) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($showBookingPanel): ?>
                        <div class="hero-booking" id="booking-form">
                            <div class="booking-panel booking-panel--<?= $escape($searchPanelLayout) ?>">
                                <span class="section-kicker"><?= $escape($publicT('search.label', 'Book your transfer')) ?></span>
                                <h2><?= $escape($searchPanelHeadline) ?></h2>

                                <?php if (is_string($error) && $error !== ''): ?>
                                    <p class="error-text"><?= $escape($error) ?></p>
                                <?php endif; ?>

                                <form class="booking-form" method="post" action="/search">
                                    <input type="hidden" name="_csrf" value="<?= $escape($csrf_token ?? '') ?>">
                                    <input type="hidden" name="trip_type" id="trip_type" value="<?= $escape($form['trip_type'] ?? 'ONE_WAY') ?>">
                                    <input type="hidden" name="direction" id="direction" value="<?= $escape($form['direction'] ?? 'AIRPORT_TO_DESTINATION') ?>">

                                    <?php
                                    $transferMode = (string) ($form['transfer_mode'] ?? 'AIRPORT_TO_DESTINATION');
                                    if ($transferMode === '' && ($form['trip_type'] ?? '') === 'ROUND_TRIP') {
                                        $transferMode = 'ROUND_TRIP';
                                    }
                                    ?>

                                    <div class="search-grid search-grid--<?= $escape($searchPanelLayout) ?>">
                                                    <div class="field-block">
                                                        <label for="transfer_mode"><?= $escape($publicT('search.transfer', 'Transfer')) ?></label>
                                                        <select id="transfer_mode" name="transfer_mode">
                                                            <option value="ROUND_TRIP" <?= $transferMode === 'ROUND_TRIP' ? 'selected' : '' ?>><?= $escape($publicT('search.round_trip', 'Round trip')) ?></option>
                                                            <option value="AIRPORT_TO_DESTINATION" <?= $transferMode === 'AIRPORT_TO_DESTINATION' ? 'selected' : '' ?>><?= $escape($publicT('search.airport_to_destination', 'Airport to destination')) ?></option>
                                                            <option value="DESTINATION_TO_AIRPORT" <?= $transferMode === 'DESTINATION_TO_AIRPORT' ? 'selected' : '' ?>><?= $escape($publicT('search.destination_to_airport', 'Destination to airport')) ?></option>
                                                        </select>
                                                    </div>

                                                    <div class="field-block field-span-wide">
                                                        <label for="place_query"><?= $escape($publicT('search.place', 'Hotel / destination')) ?></label>
                                                        <input id="place_query" type="text" name="place_query" value="<?= $escape($form['place_query'] ?? '') ?>" placeholder="<?= $escape($publicT('search.place_placeholder', 'Type your resort, villa or area')) ?>">
                                                        <ul id="places_suggestions" class="places-list"></ul>
                                                        <input type="hidden" name="place_id" id="place_id" value="<?= $escape($form['place_id'] ?? '') ?>">
                                                    </div>

                                                    <div class="field-block">
                                                        <label for="arrival_datetime"><?= $escape($publicT('search.arrival', 'Arrival')) ?></label>
                                                        <input id="arrival_datetime" type="datetime-local" name="arrival_datetime" value="<?= $escape($form['arrival_datetime'] ?? '') ?>">
                                                    </div>

                                                    <div class="field-block" id="departure_field">
                                                        <label for="departure_datetime"><?= $escape($publicT('search.departure', 'Departure')) ?></label>
                                                        <input id="departure_datetime" type="datetime-local" name="departure_datetime" value="<?= $escape($form['departure_datetime'] ?? '') ?>">
                                                    </div>

                                                    <div class="field-block">
                                                        <label for="adults"><?= $escape($publicT('search.adults', 'Adults')) ?></label>
                                                        <input id="adults" type="number" name="adults" min="1" value="<?= $escape($form['adults'] ?? '1') ?>">
                                                    </div>

                                                    <div class="field-block">
                                                        <label for="children"><?= $escape($publicT('search.children', 'Children')) ?></label>
                                                        <input id="children" type="number" name="children" min="0" value="<?= $escape($form['children'] ?? '0') ?>">
                                                    </div>

                                                    <div class="field-block">
                                                        <label for="currency_code"><?= $escape($publicT('search.currency', 'Currency')) ?></label>
                                                        <select id="currency_code" name="currency_code">
                                                            <?php foreach ($currencies as $currency): ?>
                                                                <?php $code = (string) ($currency['code'] ?? ''); ?>
                                                                <option value="<?= $escape($code) ?>" <?= (($form['currency_code'] ?? '') === $code) ? 'selected' : '' ?>>
                                                                    <?= $escape($code) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                            <?php if (empty($currencies)): ?>
                                                                <option value="USD" selected>USD</option>
                                                            <?php endif; ?>
                                                        </select>
                                                    </div>

                                                    <div class="field-block field-block--submit">
                                                        <button class="booking-submit" type="submit"><?= $escape($homeContent['search_button_label'] ?? $publicT('search.submit', 'Check availability')) ?></button>
                                                    </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="lux-section lux-welcome" id="experience">
        <div class="lux-welcome__image" aria-hidden="true"></div>
        <div class="lux-welcome__copy">
            <span class="lux-eyebrow"><?= $escape($withBrand($publicT('welcome.eyebrow', 'Welcome to {{brand}}'))) ?></span>
            <h2><?= $escape($publicT('welcome.title', 'Private transportation designed for airport arrivals.')) ?></h2>
            <p><?= $escape($publicT('welcome.p1', 'From the moment your flight lands, your transfer should feel organized, punctual and clear.')) ?></p>
            <div class="lux-stat-row">
                <span><strong>24/7</strong><?= $escape($publicT('welcome.stat1', 'airport-focused requests')) ?></span>
                <span><strong><?= $escape($publicT('welcome.stat2_label', 'Private')) ?></strong><?= $escape($publicT('welcome.stat2', 'vehicles for your group')) ?></span>
                <span><strong><?= $escape($publicT('welcome.stat3_label', 'Fast')) ?></strong><?= $escape($publicT('welcome.stat3', 'quick hotel search')) ?></span>
            </div>
        </div>
    </section>

    <section class="lux-section" id="routes">
        <header class="lux-section__header">
            <span class="lux-eyebrow"><?= $escape($publicT('benefits.eyebrow', 'Why book with us')) ?></span>
            <h2><?= $escape($publicT('benefits.title', 'A simpler way to book Cancun airport transportation.')) ?></h2>
        </header>
        <div class="lux-benefit-grid">
            <article class="lux-benefit-card">
                <span>01</span>
                <h3><?= $escape($publicT('benefit.private.title', 'Private transfers')) ?></h3>
                <p><?= $escape($publicT('benefit.private.text', 'No shared vans or unnecessary stops.')) ?></p>
            </article>
            <article class="lux-benefit-card">
                <span>02</span>
                <h3><?= $escape($publicT('benefit.hotel.title', 'Hotel and Airbnb coverage')) ?></h3>
                <p><?= $escape($publicT('benefit.hotel.text', 'Search hotels, resorts, villas and key areas.')) ?></p>
            </article>
            <article class="lux-benefit-card">
                <span>03</span>
                <h3><?= $escape($publicT('benefit.flight.title', 'Flight details support')) ?></h3>
                <p><?= $escape($publicT('benefit.flight.text', 'Arrival and departure details help operations coordination.')) ?></p>
            </article>
            <article class="lux-benefit-card">
                <span>04</span>
                <h3><?= $escape($publicT('benefit.secure.title', 'Secure online booking')) ?></h3>
                <p><?= $escape($publicT('benefit.secure.text', 'A direct checkout keeps quote, passengers and confirmation together.')) ?></p>
            </article>
        </div>
    </section>

    <section class="lux-testimonials">
        <div class="lux-section">
            <header class="lux-section__header">
                <span class="lux-eyebrow"><?= $escape($publicT('testimonials.eyebrow', 'Traveler notes')) ?></span>
                <h2><?= $escape($publicT('testimonials.title', 'Calm arrivals, clear communication and private rides.')) ?></h2>
            </header>
            <div class="lux-testimonial-grid">
                <article class="lux-testimonial-card">
                    <div class="lux-stars">★★★★★</div>
                    <blockquote><?= $escape($publicT('testimonial.1.quote', 'Booking was easy and the driver was waiting when we landed.')) ?></blockquote>
                    <p><strong>Laura M.</strong><span><?= $escape($publicT('testimonial.1.route', 'Cancun Airport to Hotel Zone')) ?></span></p>
                </article>
                <article class="lux-testimonial-card">
                    <div class="lux-stars">★★★★★</div>
                    <blockquote><?= $escape($publicT('testimonial.2.quote', 'Clean private SUV, clear communication and calm transfer after a long flight.')) ?></blockquote>
                    <p><strong>Carlos R.</strong><span><?= $escape($publicT('testimonial.2.route', 'Airport to Playa del Carmen')) ?></span></p>
                </article>
                <article class="lux-testimonial-card">
                    <div class="lux-stars">★★★★★</div>
                    <blockquote><?= $escape($publicT('testimonial.3.quote', 'Great option for our family. Hotel search made booking very fast.')) ?></blockquote>
                    <p><strong>Andrea P.</strong><span><?= $escape($publicT('testimonial.3.route', 'Round trip to Riviera Maya')) ?></span></p>
                </article>
            </div>
        </div>
    </section>

    <section class="lux-section lux-seo" id="faq">
        <div class="lux-seo__copy">
            <span class="lux-eyebrow"><?= $escape($publicT('seo.eyebrow', 'Cancun travel routes')) ?></span>
            <h2><?= $escape($publicT('seo.title', 'Transportation from Cancun Airport for hotels, resorts and Riviera Maya stays.')) ?></h2>
            <p><?= $escape($publicT('seo.p1', 'Book transportation from Cancun Airport for private arrivals and departures.')) ?></p>
        </div>
        <div class="lux-faq">
            <details>
                <summary><?= $escape($publicT('faq.1.q', 'Do you offer transportation from Cancun Airport to hotels?')) ?></summary>
                <p><?= $escape($publicT('faq.1.a', 'Yes, you can book private transportation to hotels, resorts and villas.')) ?></p>
            </details>
            <details>
                <summary><?= $escape($publicT('faq.2.q', 'Can I book a private transfer from Cancun Airport to Riviera Maya?')) ?></summary>
                <p><?= $escape($publicT('faq.2.a', 'Yes, private transfers can be quoted for Cancun, Playa del Carmen and Riviera Maya.')) ?></p>
            </details>
            <details>
                <summary><?= $escape($publicT('faq.3.q', 'Is it a shared shuttle?')) ?></summary>
                <p><?= $escape($publicT('faq.3.a', 'No, booking flow is designed for private transportation.')) ?></p>
            </details>
        </div>
    </section>

    <div class="lux-final-cta" id="contact">
        <h2><?= $escape($publicT('cta.title', 'Book private transportation from Cancun Airport in minutes.')) ?></h2>
        <a href="#booking-form"><?= $escape($publicT('cta.button', 'Start booking')) ?></a>
    </div>

    <?php
    $floatingChannels = array_values(array_filter(array_slice($contactChannels, 0, 4), static function ($channel): bool {
        return is_array($channel) && trim((string) ($channel['title'] ?? '')) !== '';
    }));
    ?>
    <?php if (!empty($floatingChannels)): ?>
        <div class="fab-contact" aria-label="<?= $escape($publicT('fab.aria', 'Quick contact options')) ?>">
            <div class="fab-channels" id="fab-channels">
                <?php foreach ($floatingChannels as $channel): ?>
                    <?php $channelType = strtolower(trim((string) ($channel['type'] ?? ''))); ?>
                    <a class="fab-channel-item" href="<?= $escape($buildChannelUrl($channel)) ?>" target="_blank" rel="noreferrer">
                        <span class="fab-channel-badge" aria-hidden="true"><?= $channelIconSvg($channelType) ?></span>
                        <span><?= $escape((string) ($channel['title'] ?? '')) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <button class="fab-toggle" id="fab-toggle" type="button" aria-label="<?= $escape($publicT('fab.open', 'Open quick contact options')) ?>" aria-controls="fab-channels" aria-expanded="false">
                <span class="fab-icon-wrap" aria-hidden="true">
                    <svg class="fab-icon-chat" viewBox="0 0 24 24"><path d="M4 4h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H8l-4 3v-3H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm1 4h14v2H5V8Zm0 4h10v2H5v-2Z"/></svg>
                    <svg class="fab-icon-close" viewBox="0 0 24 24"><path d="M18.3 5.7 12 12l6.3 6.3-1.4 1.4L10.6 13.4 4.3 19.7 2.9 18.3 9.2 12 2.9 5.7 4.3 4.3l6.3 6.3 6.3-6.3 1.4 1.4Z"/></svg>
                </span>
                <span class="fab-toggle-label"><?= $escape($publicT('fab.label', 'Contact us')) ?></span>
            </button>
        </div>
    <?php endif; ?>
</div>
