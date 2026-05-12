<?php
declare(strict_types=1);
namespace App\Services;

use App\Core\DB;
use JsonException;
use Throwable;

class HomeContentService {
    private const CONTENT_KEY = 'home_page';

    public function getHomePageContent(): array
    {
        $content = self::defaultContent();

        try {
            $db = DB::connection();
            $stmt = $db->prepare('SELECT content_json FROM site_content WHERE content_key = :content_key LIMIT 1');
            $stmt->execute(['content_key' => self::CONTENT_KEY]);
            $row = $stmt->fetch();

            if (!is_array($row) || !isset($row['content_json'])) {
                return $content;
            }

            $decoded = json_decode((string) $row['content_json'], true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($decoded)) {
                return $content;
            }

            return $this->mergeRecursiveDistinct($content, $decoded);
        } catch (Throwable) {
            return $content;
        }
    }

    public function saveHomePageContent(array $content, ?int $updatedBy = null): void
    {
        $payload = $this->mergeRecursiveDistinct(self::defaultContent(), $content);

        try {
            $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $json = json_encode(self::defaultContent(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
        }

        $db = DB::connection();
        $stmt = $db->prepare(
            'INSERT INTO site_content (content_key, content_json, updated_by, updated_at)
             VALUES (:content_key, :content_json, :updated_by, NOW())
             ON DUPLICATE KEY UPDATE
                 content_json = VALUES(content_json),
                 updated_by = VALUES(updated_by),
                 updated_at = NOW()'
        );
        $stmt->execute([
            'content_key' => self::CONTENT_KEY,
            'content_json' => $json,
            'updated_by' => $updatedBy,
        ]);
    }

    public static function defaultContent(): array
    {
        return [
            'sections' => [
                'show_social_links' => true,
                'show_hero_badges' => true,
                'show_booking_panel' => true,
                'show_highlights' => true,
                'show_routes' => true,
                'show_story' => true,
                'show_story_points' => true,
                'show_support' => true,
                'show_closing_cta' => true,
                'show_floating_contact' => true,
            ],
            'brand_logo' => '',
            'brand_logo_light' => '',
            'booking_code_prefix' => 'KTR',
            'voucher_theme' => [
                'primary' => '#17679a',
                'secondary' => '#0d4f79',
                'line' => '#1f2937',
            ],
            'landing_theme' => [
                'day' => [
                    'bg' => '#fffdf8',
                    'text' => '#101820',
                    'accent' => '#0f3f46',
                    'accent_2' => '#155d66',
                    'gold' => '#c9a46a',
                    'header_bg' => '#000000',
                    'footer_bg' => '#000000',
                ],
                'night' => [
                    'bg' => '#071114',
                    'text' => '#f7fbfc',
                    'accent' => '#4fb3c3',
                    'accent_2' => '#7ad4df',
                    'gold' => '#c9a46a',
                    'header_bg' => '#000000',
                    'footer_bg' => '#071114',
                ],
            ],
            'payment_settings' => [
                'mercado_pago' => [
                    'enabled' => false,
                    'public_key' => '',
                    'access_token' => '',
                ],
                'stripe' => [
                    'enabled' => false,
                    'public_key' => '',
                    'secret_key' => '',
                ],
                'paypal' => [
                    'enabled' => false,
                    'client_id' => '',
                    'client_secret' => '',
                ],
            ],
            'home_theme' => 'day',
            'eyebrow' => 'Private Cancun airport transfers for resorts, villas, executive arrivals and high-touch travel teams',
            'hero_mode' => 'slider',
            'hero_images' => [
                'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=2200&q=80',
                'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=2200&q=80',
                'https://images.unsplash.com/photo-1570129477492-45c003edd2be?auto=format&fit=crop&w=2200&q=80',
            ],
            'hero_title' => 'Private airport arrivals handled with chauffeur-level calm.',
            'hero_subtitle' => 'Book Cancun and Riviera Maya transfers through a cleaner, more premium interface designed to feel closer to a concierge desk than a generic transport page.',
            'hero_primary_cta_label' => 'Reserve your transfer',
            'hero_primary_cta_href' => '#booking-form',
            'hero_secondary_cta_label' => 'Speak with concierge',
            'hero_secondary_cta_href' => '#contact-channels',
            'search_label' => 'Private transfer request',
            'search_panel_layout' => 'center-horizontal',
            'search_help' => 'Choose route, dates and passenger count to get the right private service tier without losing the speed expected from a direct airport transfer booking.',
            'search_button_label' => 'View private options',
            'badges' => [
                'Private vehicles only',
                'Arrival monitoring included',
                'Rates visible before checkout',
            ],
            'hero_slides' => [
                [
                    'title' => 'Arrival-first reassurance',
                    'text' => 'Guests landing in Cancun need visible support, direct contact and a polished booking path that feels reliable before they even request a quote.',
                    'label' => 'Open WhatsApp',
                    'href' => 'https://wa.me/529981234567',
                ],
                [
                    'title' => 'Resort and villa coordination',
                    'text' => 'Frame every route like a curated private movement for luxury resorts, residences and family arrivals instead of generic point-to-point transport.',
                    'label' => 'Explore routes',
                    'href' => '#routes',
                ],
                [
                    'title' => 'Operational confidence',
                    'text' => 'Blend direct contact, visible route curation and clear service tiers so the page feels premium without hiding the operational clarity travelers need.',
                    'label' => 'See support',
                    'href' => '#contact-channels',
                ],
            ],
            'highlights' => [
                ['title' => 'Arrival-led operations', 'text' => 'Flight monitoring, airport coordination and pickup sequencing for guests, concierges and villa teams.'],
                ['title' => 'Tiered private service', 'text' => 'Present regular, VIP and luxury transport tiers with clearer service language and less visual clutter.'],
                ['title' => 'Direct-booking confidence', 'text' => 'A calmer premium presentation builds trust before the guest reduces the decision to price alone.'],
            ],
            'collections' => [
                ['title' => 'Cancun Hotel Zone', 'text' => 'The main arrival corridor for premium resort guests who expect fast booking and polished airport coordination.'],
                ['title' => 'Costa Mujeres', 'text' => 'Newer resort inventory where the visual identity should reinforce exclusivity and private-service positioning.'],
                ['title' => 'Playa del Carmen', 'text' => 'A high-demand corridor where route clarity, vehicle tiering and operational trust matter immediately.'],
                ['title' => 'Tulum corridor', 'text' => 'Longer private journeys that benefit from calmer layout, visible reassurance and stronger concierge tone.'],
            ],
            'story_title' => 'A premium transfer homepage should sell confidence before it sells transportation.',
            'story_body' => 'The booking flow can stay direct and practical while the visual language shifts toward private aviation, concierge support and luxury resort arrivals. That balance is what separates a premium transfer brand from a commodity transport page.',
            'story_points' => [
                'Hero language centered on arrivals, service and reassurance',
                'Reusable sections for routes, service tiers and visible contact',
                'Admin-editable text blocks so marketing updates do not require code',
            ],
            'contact_channels' => [
                ['type' => 'whatsapp', 'title' => 'WhatsApp', 'value' => '+52 998 123 4567', 'url' => 'https://wa.me/529981234567'],
                ['type' => 'call', 'title' => 'Call us', 'value' => '+52 998 123 4567', 'url' => 'tel:+529981234567'],
                ['type' => 'sms', 'title' => 'SMS updates', 'value' => '+52 998 123 4567', 'url' => 'sms:+529981234567'],
                ['type' => 'telegram', 'title' => 'Telegram', 'value' => '@ktransfers', 'url' => 'https://t.me/ktransfers'],
            ],
            'social_links' => [
                ['label' => 'Instagram', 'url' => 'https://instagram.com/ktransfers'],
                ['label' => 'Facebook', 'url' => 'https://facebook.com/ktransfers'],
                ['label' => 'Tripadvisor', 'url' => 'https://tripadvisor.com/'],
            ],
            'testimonial_quote' => 'Guests book faster when the page feels deliberate, calm and operationally credible. The design should carry the same confidence as the pickup itself.',
            'testimonial_author' => 'KTransfers Product Direction',
            'testimonial_role' => 'Brand and Operations',
            'cta_title' => 'Ready to request a private transfer with a more elevated first impression?',
            'cta_body' => 'The booking structure remains practical, but the experience now frames the service with more confidence, better hierarchy and a stronger chauffeur-style tone.',
            'cta_button_label' => 'Start your request',
            'cta_button_href' => '#booking-form',
        ];
    }

    private function mergeRecursiveDistinct(array $base, array $overrides): array
    {
        foreach ($overrides as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key]) && $this->isAssoc($value) && $this->isAssoc($base[$key])) {
                $base[$key] = $this->mergeRecursiveDistinct($base[$key], $value);
                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    }

    private function isAssoc(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }
}
