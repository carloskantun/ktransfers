<?php
declare(strict_types=1);

$form = $form ?? [];
$errors = $errors ?? [];
$currencies = $currencies ?? [];
$featuredDestinations = $featured_destinations ?? [];
$homeContent = $home_content ?? [];
$error = $error ?? null;

$badges = is_array($homeContent['badges'] ?? null) ? $homeContent['badges'] : [];
$heroSlides = is_array($homeContent['hero_slides'] ?? null) ? $homeContent['hero_slides'] : [];
$contactChannels = is_array($homeContent['contact_channels'] ?? null) ? $homeContent['contact_channels'] : [];
$sections = is_array($homeContent['sections'] ?? null) ? $homeContent['sections'] : [];
$publicLayoutMode = 'immersive';

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
    '/assets/hero-transfer-1.svg',
    '/assets/hero-transfer-2.svg',
    '/assets/hero-transfer-3.svg',
];
$showSocialLinks = !empty($sections['show_social_links']);
$showHeroBadges = !empty($sections['show_hero_badges']);
$showBookingPanel = !empty($sections['show_booking_panel']);
$showRoutes = !empty($sections['show_routes']);

$searchPanelLayout = (string) ($homeContent['search_panel_layout'] ?? 'center-horizontal');
if (!in_array($searchPanelLayout, ['center-horizontal', 'left-vertical', 'right-vertical'], true)) {
    $searchPanelLayout = 'center-horizontal';
}

$searchPanelHeadline = 'Book your private transfer';

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
<div class="travel-home travel-home--<?= $escape($searchPanelLayout) ?>">
    <section class="hero-stack hero-stack--<?= $escape($searchPanelLayout) ?>">
        <div class="hero-media">
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

            <?php foreach ($slides as $index => $slide): ?>
                <?php $imageUrl = $heroImages[$index % count($heroImages)]; ?>
                <?php $fallbackHero = '/assets/hero-transfer-' . (($index % 3) + 1) . '.svg'; ?>
                <div class="hero-slide<?= $index === 0 ? ' is-active' : '' ?>" data-hero-slide style="background-image: url('<?= $escape($imageUrl) ?>'), url('<?= $escape($fallbackHero) ?>');"></div>
            <?php endforeach; ?>

            <div class="hero-content">
                <div class="hero-layout">
                    <div class="hero-main">
                        <div class="hero-kicker">Express Transfer Cancun</div>
                        <h1><?= $escape($truncate((string) ($homeContent['hero_title'] ?? 'Private Cancun airport transfers'), 64)) ?></h1>
                        <p class="hero-summary"><?= $escape($truncate((string) ($homeContent['hero_subtitle'] ?? 'Book reliable private transportation with a simple premium experience.'), 120)) ?></p>

                        <div class="hero-actions">
                            <a class="hero-primary" href="#booking-form">Reserve transfer</a>
                            <a class="hero-secondary" href="#routes">See routes</a>
                        </div>

                        <?php if ($showHeroBadges && !empty($badges)): ?>
                            <div class="hero-badges">
                                <?php foreach (array_slice($badges, 0, 3) as $badge): ?>
                                    <span><?= $escape($badge) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($showBookingPanel): ?>
                        <div class="hero-booking" id="booking-form">
                            <div class="booking-panel booking-panel--<?= $escape($searchPanelLayout) ?>">
                                <span class="section-kicker">Reservation</span>
                                <h2><?= $escape($searchPanelHeadline) ?></h2>

                                <?php if (is_string($error) && $error !== ''): ?>
                                    <p class="error-text"><?= $escape($error) ?></p>
                                <?php endif; ?>

                                <?php if (!empty($errors['general'])): ?>
                                    <p class="error-text"><?= $escape($errors['general']) ?></p>
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
                                                        <label for="transfer_mode">Transfer</label>
                                                        <select id="transfer_mode" name="transfer_mode">
                                                            <option value="ROUND_TRIP" <?= $transferMode === 'ROUND_TRIP' ? 'selected' : '' ?>>Round Trip</option>
                                                            <option value="AIRPORT_TO_DESTINATION" <?= $transferMode === 'AIRPORT_TO_DESTINATION' ? 'selected' : '' ?>>Airport to destination</option>
                                                            <option value="DESTINATION_TO_AIRPORT" <?= $transferMode === 'DESTINATION_TO_AIRPORT' ? 'selected' : '' ?>>Destination to airport</option>
                                                        </select>
                                                    </div>

                                                    <div class="field-block field-span-wide">
                                                        <label for="place_query">Hotel / destination</label>
                                                        <input id="place_query" type="text" name="place_query" value="<?= $escape($form['place_query'] ?? '') ?>" placeholder="Type your resort, villa or area">
                                                        <ul id="places_suggestions" class="places-list"></ul>
                                                        <input type="hidden" name="place_id" id="place_id" value="<?= $escape($form['place_id'] ?? '') ?>">
                                                        <?php if (!empty($errors['place_id'])): ?>
                                                            <p class="error-text"><?= $escape($errors['place_id']) ?></p>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="field-block">
                                                        <label for="arrival_datetime">Arrival</label>
                                                        <input id="arrival_datetime" type="datetime-local" name="arrival_datetime" value="<?= $escape($form['arrival_datetime'] ?? '') ?>">
                                                        <?php if (!empty($errors['arrival_datetime'])): ?>
                                                            <p class="error-text"><?= $escape($errors['arrival_datetime']) ?></p>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="field-block" id="departure_field">
                                                        <label for="departure_datetime">Departure</label>
                                                        <input id="departure_datetime" type="datetime-local" name="departure_datetime" value="<?= $escape($form['departure_datetime'] ?? '') ?>">
                                                        <?php if (!empty($errors['departure_datetime'])): ?>
                                                            <p class="error-text"><?= $escape($errors['departure_datetime']) ?></p>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="field-block">
                                                        <label for="adults">Adults</label>
                                                        <input id="adults" type="number" name="adults" min="1" value="<?= $escape($form['adults'] ?? '1') ?>">
                                                    </div>

                                                    <div class="field-block">
                                                        <label for="children">Children</label>
                                                        <input id="children" type="number" name="children" min="0" value="<?= $escape($form['children'] ?? '0') ?>">
                                                    </div>

                                                    <div class="field-block">
                                                        <label for="currency_code">Currency</label>
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

                                                    <div class="field-block">
                                                        <button class="booking-submit" type="submit"><?= $escape($homeContent['search_button_label'] ?? 'Check availability') ?></button>
                                                    </div>
                                    </div>
                                </form>

                                <?php if (!empty($contactChannels)): ?>
                                    <div class="support-inline" id="contact-channels">
                                        <?php foreach (array_slice($contactChannels, 0, 4) as $channel): ?>
                                            <?php if (!is_array($channel) || ($channel['title'] ?? '') === '') { continue; } ?>
                                            <?php $channelType = strtolower(trim((string) ($channel['type'] ?? ''))); ?>
                                            <a href="<?= $escape($buildChannelUrl($channel)) ?>" target="_blank" rel="noreferrer">
                                                <span class="inline-channel-icon"><?= $channelIconSvg($channelType) ?></span>
                                                <?= $escape($channel['title'] ?? '') ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php if ($showRoutes): ?>
    <section class="routes-shell" id="routes">
        <article class="routes-copy">
            <span class="section-kicker">Routes</span>
            <h2 class="section-title">Popular destinations</h2>
            <p class="section-copy">Private service to top hotels and resorts.</p>
        </article>

        <div class="route-grid">
            <?php foreach (array_slice($routeCards, 0, 3) as $index => $destination): ?>
                <?php if (!is_array($destination)) { continue; } ?>
                <?php
                $routeImage = $showcaseImages[$index % count($showcaseImages)];
                $routeFallback = '/assets/hero-transfer-' . (($index % 3) + 1) . '.svg';
                ?>
                <article class="route-card">
                    <a class="route-card-link" href="#booking-form">
                        <div class="route-card-media" style="background-image: url('<?= $escape($routeImage) ?>'), url('<?= $escape($routeFallback) ?>');"></div>
                        <div class="route-card-body">
                            <h3><?= $escape($destination['name'] ?? '') ?></h3>
                            <p><?= $escape($truncate((string) ($destination['zone_name'] ?? ''), 42)) ?></p>
                            <span class="route-cta">Book now</span>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>