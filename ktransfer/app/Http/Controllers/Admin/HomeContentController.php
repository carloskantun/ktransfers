<?php
declare(strict_types=1);
namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Services\HomeContentService;

class HomeContentController {
    public function edit(Request $request): Response
    {
        $service = new HomeContentService();
        $saved = $request->query('saved', '') === '1';

        if ($request->method() === 'GET') {
            return Response::view('admin/content/home', [
                'title' => 'Home Content',
                'csrf_token' => Csrf::token(),
                'saved' => $saved,
                'errors' => [],
                'form' => $this->contentToForm($service->getHomePageContent()),
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin/content/home');
        }

        $form = $this->formFromRequest($request);
        $errors = $this->validateForm($form);

        if (!empty($errors)) {
            return Response::view('admin/content/home', [
                'title' => 'Home Content',
                'csrf_token' => Csrf::token(),
                'saved' => false,
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        $service->saveHomePageContent($this->formToContent($form), Auth::id());

        return Response::redirect('/admin/content/home?saved=1');
    }

    private function formFromRequest(Request $request): array
    {
        return [
            'show_social_links' => $request->post('show_social_links') !== null ? '1' : '0',
            'show_hero_badges' => $request->post('show_hero_badges') !== null ? '1' : '0',
            'show_booking_panel' => $request->post('show_booking_panel') !== null ? '1' : '0',
            'show_highlights' => $request->post('show_highlights') !== null ? '1' : '0',
            'show_routes' => $request->post('show_routes') !== null ? '1' : '0',
            'show_story' => $request->post('show_story') !== null ? '1' : '0',
            'show_story_points' => $request->post('show_story_points') !== null ? '1' : '0',
            'show_support' => $request->post('show_support') !== null ? '1' : '0',
            'show_closing_cta' => $request->post('show_closing_cta') !== null ? '1' : '0',
            'show_floating_contact' => $request->post('show_floating_contact') !== null ? '1' : '0',
            'eyebrow' => trim((string) $request->post('eyebrow', '')),
            'hero_mode' => trim((string) $request->post('hero_mode', 'editorial')),
            'hero_images_text' => trim((string) $request->post('hero_images_text', '')),
            'hero_title' => trim((string) $request->post('hero_title', '')),
            'hero_subtitle' => trim((string) $request->post('hero_subtitle', '')),
            'hero_primary_cta_label' => trim((string) $request->post('hero_primary_cta_label', '')),
            'hero_primary_cta_href' => trim((string) $request->post('hero_primary_cta_href', '#booking-form')),
            'hero_secondary_cta_label' => trim((string) $request->post('hero_secondary_cta_label', '')),
            'hero_secondary_cta_href' => trim((string) $request->post('hero_secondary_cta_href', '#contact-channels')),
            'search_label' => trim((string) $request->post('search_label', '')),
            'search_panel_layout' => trim((string) $request->post('search_panel_layout', 'center-horizontal')),
            'search_help' => trim((string) $request->post('search_help', '')),
            'search_button_label' => trim((string) $request->post('search_button_label', '')),
            'badges_text' => trim((string) $request->post('badges_text', '')),
            'hero_slides_text' => trim((string) $request->post('hero_slides_text', '')),
            'highlights_text' => trim((string) $request->post('highlights_text', '')),
            'collections_text' => trim((string) $request->post('collections_text', '')),
            'story_title' => trim((string) $request->post('story_title', '')),
            'story_body' => trim((string) $request->post('story_body', '')),
            'story_points_text' => trim((string) $request->post('story_points_text', '')),
            'contact_channels_text' => trim((string) $request->post('contact_channels_text', '')),
            'social_links_text' => trim((string) $request->post('social_links_text', '')),
            'testimonial_quote' => trim((string) $request->post('testimonial_quote', '')),
            'testimonial_author' => trim((string) $request->post('testimonial_author', '')),
            'testimonial_role' => trim((string) $request->post('testimonial_role', '')),
            'cta_title' => trim((string) $request->post('cta_title', '')),
            'cta_body' => trim((string) $request->post('cta_body', '')),
            'cta_button_label' => trim((string) $request->post('cta_button_label', '')),
            'cta_button_href' => trim((string) $request->post('cta_button_href', '#booking-form')),
        ];
    }

    private function validateForm(array $form): array
    {
        $errors = [];

        foreach (['hero_title', 'hero_subtitle', 'search_label', 'search_button_label', 'cta_title'] as $field) {
            if (($form[$field] ?? '') === '') {
                $errors[$field] = 'Este campo es requerido.';
            }
        }

        if (!in_array($form['hero_mode'] ?? '', ['editorial', 'slider', 'carousel'], true)) {
            $errors['hero_mode'] = 'Selecciona un modo de hero válido.';
        }

        if (!in_array($form['search_panel_layout'] ?? '', ['center-horizontal', 'left-vertical', 'right-vertical'], true)) {
            $errors['search_panel_layout'] = 'Selecciona una posición de buscador válida.';
        }

        return $errors;
    }

    private function formToContent(array $form): array
    {
        return [
            'sections' => [
                'show_social_links' => $form['show_social_links'] === '1',
                'show_hero_badges' => $form['show_hero_badges'] === '1',
                'show_booking_panel' => $form['show_booking_panel'] === '1',
                'show_highlights' => $form['show_highlights'] === '1',
                'show_routes' => $form['show_routes'] === '1',
                'show_story' => $form['show_story'] === '1',
                'show_story_points' => $form['show_story_points'] === '1',
                'show_support' => $form['show_support'] === '1',
                'show_closing_cta' => $form['show_closing_cta'] === '1',
                'show_floating_contact' => $form['show_floating_contact'] === '1',
            ],
            'eyebrow' => $form['eyebrow'],
            'hero_mode' => $form['hero_mode'],
            'hero_images' => $this->linesToList($form['hero_images_text']),
            'hero_title' => $form['hero_title'],
            'hero_subtitle' => $form['hero_subtitle'],
            'hero_primary_cta_label' => $form['hero_primary_cta_label'],
            'hero_primary_cta_href' => $form['hero_primary_cta_href'] !== '' ? $form['hero_primary_cta_href'] : '#booking-form',
            'hero_secondary_cta_label' => $form['hero_secondary_cta_label'],
            'hero_secondary_cta_href' => $form['hero_secondary_cta_href'] !== '' ? $form['hero_secondary_cta_href'] : '#contact-channels',
            'search_label' => $form['search_label'],
            'search_panel_layout' => $form['search_panel_layout'],
            'search_help' => $form['search_help'],
            'search_button_label' => $form['search_button_label'],
            'badges' => $this->linesToList($form['badges_text']),
            'hero_slides' => $this->linesToRecords($form['hero_slides_text'], ['title', 'text', 'label', 'href']),
            'highlights' => $this->linesToPairs($form['highlights_text']),
            'collections' => $this->linesToPairs($form['collections_text']),
            'story_title' => $form['story_title'],
            'story_body' => $form['story_body'],
            'story_points' => $this->linesToList($form['story_points_text']),
            'contact_channels' => $this->linesToRecords($form['contact_channels_text'], ['type', 'title', 'value', 'url']),
            'social_links' => $this->linesToRecords($form['social_links_text'], ['label', 'url']),
            'testimonial_quote' => $form['testimonial_quote'],
            'testimonial_author' => $form['testimonial_author'],
            'testimonial_role' => $form['testimonial_role'],
            'cta_title' => $form['cta_title'],
            'cta_body' => $form['cta_body'],
            'cta_button_label' => $form['cta_button_label'],
            'cta_button_href' => $form['cta_button_href'] !== '' ? $form['cta_button_href'] : '#booking-form',
        ];
    }

    private function contentToForm(array $content): array
    {
        return [
            'show_social_links' => !empty($content['sections']['show_social_links']) ? '1' : '0',
            'show_hero_badges' => !empty($content['sections']['show_hero_badges']) ? '1' : '0',
            'show_booking_panel' => !empty($content['sections']['show_booking_panel']) ? '1' : '0',
            'show_highlights' => !empty($content['sections']['show_highlights']) ? '1' : '0',
            'show_routes' => !empty($content['sections']['show_routes']) ? '1' : '0',
            'show_story' => !empty($content['sections']['show_story']) ? '1' : '0',
            'show_story_points' => !empty($content['sections']['show_story_points']) ? '1' : '0',
            'show_support' => !empty($content['sections']['show_support']) ? '1' : '0',
            'show_closing_cta' => !empty($content['sections']['show_closing_cta']) ? '1' : '0',
            'show_floating_contact' => !empty($content['sections']['show_floating_contact']) ? '1' : '0',
            'eyebrow' => (string) ($content['eyebrow'] ?? ''),
            'hero_mode' => (string) ($content['hero_mode'] ?? 'editorial'),
            'hero_images_text' => implode("\n", array_map('strval', $content['hero_images'] ?? [])),
            'hero_title' => (string) ($content['hero_title'] ?? ''),
            'hero_subtitle' => (string) ($content['hero_subtitle'] ?? ''),
            'hero_primary_cta_label' => (string) ($content['hero_primary_cta_label'] ?? ''),
            'hero_primary_cta_href' => (string) ($content['hero_primary_cta_href'] ?? '#booking-form'),
            'hero_secondary_cta_label' => (string) ($content['hero_secondary_cta_label'] ?? ''),
            'hero_secondary_cta_href' => (string) ($content['hero_secondary_cta_href'] ?? '#contact-channels'),
            'search_label' => (string) ($content['search_label'] ?? ''),
            'search_panel_layout' => (string) ($content['search_panel_layout'] ?? 'center-horizontal'),
            'search_help' => (string) ($content['search_help'] ?? ''),
            'search_button_label' => (string) ($content['search_button_label'] ?? ''),
            'badges_text' => implode("\n", array_map('strval', $content['badges'] ?? [])),
            'hero_slides_text' => $this->recordsToLines($content['hero_slides'] ?? [], ['title', 'text', 'label', 'href']),
            'highlights_text' => $this->pairsToLines($content['highlights'] ?? []),
            'collections_text' => $this->pairsToLines($content['collections'] ?? []),
            'story_title' => (string) ($content['story_title'] ?? ''),
            'story_body' => (string) ($content['story_body'] ?? ''),
            'story_points_text' => implode("\n", array_map('strval', $content['story_points'] ?? [])),
            'contact_channels_text' => $this->recordsToLines($content['contact_channels'] ?? [], ['type', 'title', 'value', 'url']),
            'social_links_text' => $this->recordsToLines($content['social_links'] ?? [], ['label', 'url']),
            'testimonial_quote' => (string) ($content['testimonial_quote'] ?? ''),
            'testimonial_author' => (string) ($content['testimonial_author'] ?? ''),
            'testimonial_role' => (string) ($content['testimonial_role'] ?? ''),
            'cta_title' => (string) ($content['cta_title'] ?? ''),
            'cta_body' => (string) ($content['cta_body'] ?? ''),
            'cta_button_label' => (string) ($content['cta_button_label'] ?? ''),
            'cta_button_href' => (string) ($content['cta_button_href'] ?? '#booking-form'),
        ];
    }

    private function linesToList(string $raw): array
    {
        $lines = preg_split('/\R+/', $raw) ?: [];
        $items = [];

        foreach ($lines as $line) {
            $value = trim((string) $line);
            if ($value !== '') {
                $items[] = $value;
            }
        }

        return $items;
    }

    private function linesToPairs(string $raw): array
    {
        $lines = preg_split('/\R+/', $raw) ?: [];
        $items = [];

        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            [$title, $text] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');
            if ($title === '') {
                continue;
            }

            $items[] = ['title' => $title, 'text' => $text];
        }

        return $items;
    }

    private function pairsToLines(array $items): string
    {
        $lines = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $text = trim((string) ($item['text'] ?? ''));
            if ($title === '') {
                continue;
            }

            $lines[] = $title . ' | ' . $text;
        }

        return implode("\n", $lines);
    }

    private function linesToRecords(string $raw, array $keys): array
    {
        $lines = preg_split('/\R+/', $raw) ?: [];
        $items = [];

        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            $parts = array_map('trim', explode('|', $line));
            $record = [];

            foreach ($keys as $index => $key) {
                $record[$key] = (string) ($parts[$index] ?? '');
            }

            if (($record[$keys[0]] ?? '') === '') {
                continue;
            }

            $items[] = $record;
        }

        return $items;
    }

    private function recordsToLines(array $items, array $keys): string
    {
        $lines = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $firstValue = trim((string) ($item[$keys[0]] ?? ''));
            if ($firstValue === '') {
                continue;
            }

            $parts = [];
            foreach ($keys as $key) {
                $parts[] = trim((string) ($item[$key] ?? ''));
            }

            $lines[] = implode(' | ', $parts);
        }

        return implode("\n", $lines);
    }
}
