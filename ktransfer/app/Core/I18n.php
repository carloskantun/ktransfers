<?php
declare(strict_types=1);
namespace App\Core;

class I18n {
    private const DEFAULT_LOCALE = 'en';
    private const SUPPORTED_LOCALES = ['en', 'es'];
    private const COOKIE_NAME = 'public_locale';

    private const TRANSLATIONS = [
        'es' => [
            'title.home' => '{brand} - Traslados Privados desde el Aeropuerto',
            'meta.description' => 'Traslados privados desde el aeropuerto de Cancun hacia hoteles, Playa del Carmen y Riviera Maya con {brand}.',
            'meta.keywords' => 'traslado aeropuerto cancun, traslado privado, transportacion cancun',
            'nav.book_now' => 'Reservar',
            'nav.book' => 'Reservar',
            'nav.experience' => 'Experiencia',
            'nav.routes' => 'Rutas',
            'nav.faq' => 'FAQ',
            'nav.contact' => 'Contacto',
            'hero.eyebrow' => 'Concierge de traslados desde Cancun Airport',
            'hero.title' => 'Transportación Privada desde el Aeropuerto de Cancun',
            'hero.subtitle' => 'Traslados privados premium desde Cancun Airport hacia hoteles, villas y destinos de Riviera Maya con una experiencia de reserva clara y sencilla.',
            'hero.trust.private' => 'Unidades privadas',
            'hero.trust.airport' => 'Llegadas al aeropuerto',
            'hero.trust.hotel' => 'Cobertura hotelera',
            'hero.badge.private' => 'Solo vehiculos privados',
            'hero.badge.monitoring' => 'Monitoreo de llegada incluido',
            'hero.badge.rates' => 'Tarifas visibles antes del checkout',
            'search.label' => 'Reserva tu traslado',
            'search.title' => 'Consulta disponibilidad',
            'search.transfer' => 'Traslado',
            'search.round_trip' => 'Round trip',
            'search.airport_to_destination' => 'Aeropuerto a destino',
            'search.destination_to_airport' => 'Destino a aeropuerto',
            'search.place' => 'Hotel / destino',
            'search.place_placeholder' => 'Escribe tu hotel, villa o zona',
            'search.arrival' => 'Llegada',
            'search.departure' => 'Salida',
            'search.adults' => 'Adultos',
            'search.children' => 'Niños',
            'search.currency' => 'Moneda',
            'search.submit' => 'Buscar traslado privado',
            'welcome.eyebrow' => 'Bienvenido a {brand}',
            'welcome.title' => 'Transportación privada pensada para llegadas al aeropuerto.',
            'welcome.p1' => 'Desde que aterriza tu vuelo, el traslado debe sentirse organizado, puntual y fácil de entender. Nuestra experiencia de reserva conecta pasajeros con unidades privadas, destinos de hotel y detalles claros antes del checkout.',
            'welcome.p2' => 'Ya sea que viajes a Cancun Hotel Zone, Costa Mujeres, Playa del Carmen o un resort en Riviera Maya, el sistema te ayuda a buscar destinos precargados y reservar con confianza.',
            'welcome.stat1' => 'solicitudes enfocadas en aeropuerto',
            'welcome.stat2_label' => 'Privado',
            'welcome.stat2' => 'unidades para tu grupo',
            'welcome.stat3_label' => 'Rápida',
            'welcome.stat3' => 'búsqueda rápida de hoteles',
            'benefits.eyebrow' => 'Por qué reservar con nosotros',
            'benefits.title' => 'Una forma más simple de reservar transportación desde Cancun Airport.',
            'benefit.private.title' => 'Traslados privados',
            'benefit.private.text' => 'Sin vans compartidas ni paradas innecesarias. La unidad se reserva para tu grupo.',
            'benefit.hotel.title' => 'Cobertura de hoteles y Airbnb',
            'benefit.hotel.text' => 'Busca hoteles, resorts, villas y zonas clave de Cancun y Riviera Maya.',
            'benefit.flight.title' => 'Soporte con información de vuelo',
            'benefit.flight.text' => 'Los detalles de llegada y salida ayudan al equipo de operaciones a coordinar horarios.',
            'benefit.secure.title' => 'Reserva segura en línea',
            'benefit.secure.text' => 'Un checkout directo mantiene cotización, pasajeros y confirmación en un solo lugar.',
            'testimonials.eyebrow' => 'Notas de viajeros',
            'testimonials.title' => 'Llegadas tranquilas, comunicación clara y viajes privados.',
            'testimonial.1.quote' => 'El proceso de reserva fue sencillo y el conductor ya nos esperaba cuando aterrizamos.',
            'testimonial.2.quote' => 'SUV privada limpia, comunicación clara y un traslado tranquilo después de un vuelo largo.',
            'testimonial.3.quote' => 'Excelente opción para nuestra familia. La búsqueda de hotel hizo muy rápido reservar el traslado correcto.',
            'testimonial.1.route' => 'Cancun Airport a Hotel Zone',
            'testimonial.2.route' => 'Aeropuerto a Playa del Carmen',
            'testimonial.3.route' => 'Round trip a Riviera Maya',
            'seo.eyebrow' => 'Rutas de viaje en Cancun',
            'seo.title' => 'Transportación desde Cancun Airport para hoteles, resorts y estancias en Riviera Maya.',
            'seo.p1' => 'Reserva transportación desde Cancun Airport para llegadas privadas, vacaciones familiares, viajes en pareja y traslados grupales. El formulario ayuda a cotizar rutas privadas desde el aeropuerto hacia hoteles, resorts, villas y zonas populares de Riviera Maya.',
            'seo.p2' => 'Para viajeros que comparan un traslado del aeropuerto al hotel, la transportación privada ofrece una experiencia más directa que los shuttles compartidos: menos paradas, horarios más claros y una unidad reservada para tu grupo.',
            'faq.label' => 'Preguntas frecuentes',
            'faq.1.q' => '¿Ofrecen transportación desde Cancun Airport a hoteles?',
            'faq.1.a' => 'Sí. Puedes reservar transportación privada hacia hoteles, resorts, villas y propiedades tipo Airbnb en Cancun y destinos cercanos.',
            'faq.2.q' => 'Puedo reservar un traslado privado de Cancun Airport a Riviera Maya?',
            'faq.2.a' => 'Sí. Los traslados privados se pueden cotizar para Cancun, Playa del Carmen, Costa Mujeres, corredor Tulum y zonas disponibles de Riviera Maya.',
            'faq.3.q' => '¿Es un shuttle compartido?',
            'faq.3.a' => 'No. El flujo de reserva está pensado para transportación privada, así que tu grupo viaja en una unidad asignada.',
            'faq.4.q' => '¿Puedo reservar viaje redondo?',
            'faq.4.a' => 'Sí. Selecciona Round Trip en el formulario e ingresa fechas de llegada y salida para ver opciones disponibles.',
            'cta.eyebrow' => 'Listos cuando llegue tu vuelo',
            'cta.title' => 'Reserva transportación privada desde Cancun Airport en minutos.',
            'cta.button' => 'Empezar reserva',
            'fab.aria' => 'Opciones rápidas de contacto',
            'fab.open' => 'Abrir opciones rápidas de contacto',
            'fab.label' => 'Contáctanos',
            'checkout.payment.hero.kicker' => 'Paso 3 de 3',
            'checkout.payment.hero.title' => 'Selecciona como pagar.',
            'checkout.payment.hero.description' => 'Elige tu metodo preferido y confirma en un ultimo paso.',
            'checkout.payment.tracker.aria' => 'Progreso de reserva',
            'checkout.payment.tracker.step_1.label' => 'Paso 1',
            'checkout.payment.tracker.step_1.title' => 'Elegir servicio',
            'checkout.payment.tracker.step_1.description' => 'Servicio seleccionado.',
            'checkout.payment.tracker.step_2.label' => 'Paso 2',
            'checkout.payment.tracker.step_2.title' => 'Ingresar datos',
            'checkout.payment.tracker.step_2.description' => 'Datos capturados correctamente.',
            'checkout.payment.tracker.step_3.label' => 'Paso 3',
            'checkout.payment.tracker.step_3.title' => 'Metodo de pago',
            'checkout.payment.tracker.step_3.description' => 'Elige y confirma.',
            'checkout.payment.summary.code' => 'Codigo',
            'checkout.payment.summary.status' => 'Estado',
            'checkout.payment.summary.ready' => 'Listo para pagar',
            'checkout.payment.section.kicker' => 'Metodo de pago',
            'checkout.payment.section.title' => 'Como prefieres pagar?',
            'checkout.payment.section.description' => 'Selecciona una opcion para continuar.',
            'checkout.payment.list.aria' => 'Metodos de pago',
            'checkout.payment.mp.meta' => 'Tarjeta, transferencia o efectivo via Mercado Pago',
            'checkout.payment.mp.description' => 'Seras redirigido a Mercado Pago para completar un pago seguro. Al aprobarse, la reserva se confirma automaticamente.',
            'checkout.payment.mp.cta' => 'Continuar con Mercado Pago',
            'checkout.payment.stripe.meta' => 'Tarjeta de credito o debito - pago seguro via Stripe',
            'checkout.payment.stripe.description' => 'Seras redirigido a Stripe para pagar. Al confirmar, regresaras con la reserva lista.',
            'checkout.payment.stripe.cta' => 'Continuar con Stripe',
            'checkout.payment.openpay.title' => 'OpenPay - Tarjeta',
            'checkout.payment.openpay.meta' => 'Credito o debito - datos cifrados en tu navegador',
            'checkout.payment.openpay.form.holder_name' => 'Nombre en la tarjeta',
            'checkout.payment.openpay.form.holder_name_placeholder' => 'Como aparece en la tarjeta',
            'checkout.payment.openpay.form.card_number' => 'Numero de tarjeta',
            'checkout.payment.openpay.form.exp_month' => 'Mes',
            'checkout.payment.openpay.form.exp_year' => 'Ano',
            'checkout.payment.openpay.error' => 'Error al procesar la tarjeta. Revisa los datos e intenta de nuevo.',
            'checkout.payment.openpay.cta' => 'Pagar con OpenPay',
            'checkout.payment.openpay.note' => 'Los datos de tarjeta se cifran en tu navegador antes de enviarse. Nunca pasan por nuestros servidores.',
            'checkout.payment.manual.title' => 'Pago coordinado por nuestro equipo',
            'checkout.payment.manual.meta_with_paypal' => 'Transferencia, efectivo, tarjeta presencial o PayPal (externo)',
            'checkout.payment.manual.meta_no_paypal' => 'Transferencia, efectivo o tarjeta presencial',
            'checkout.payment.manual.description' => 'Envia la solicitud y nuestro equipo te contactara para coordinar el pago y la confirmacion final.',
            'checkout.payment.manual.method.card' => 'Tarjeta presencial',
            'checkout.payment.manual.method.bank' => 'Transferencia bancaria',
            'checkout.payment.manual.method.cash' => 'Efectivo',
            'checkout.payment.manual.cta' => 'Enviar solicitud',
            'checkout.payment.manual.note' => 'El equipo procesara la reserva manualmente despues de recibir tu solicitud.',
            'checkout.payment.back' => 'Volver a editar datos',
        ],
    ];

    public static function current(): string
    {
        $requested = self::normalize((string) ($_GET['lang'] ?? ''));
        if ($requested !== null) {
            setcookie(self::COOKIE_NAME, $requested, [
                'expires' => time() + 60 * 60 * 24 * 180,
                'path' => '/',
                'samesite' => 'Lax',
            ]);

            $_COOKIE[self::COOKIE_NAME] = $requested;
            return $requested;
        }

        $stored = self::normalize((string) ($_COOKIE[self::COOKIE_NAME] ?? ''));
        return $stored ?? self::DEFAULT_LOCALE;
    }

    public static function translate(string $key, string $locale, string $fallback): string
    {
        return self::TRANSLATIONS[$locale][$key] ?? $fallback;
    }

    public static function localizedUrl(string $locale): string
    {
        $locale = self::normalize($locale) ?? self::DEFAULT_LOCALE;
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = is_string($path) && $path !== '' ? $path : '/';
        $query = $_GET;
        $query['lang'] = $locale;
        $queryString = http_build_query($query);

        return $path . ($queryString !== '' ? '?' . $queryString : '');
    }

    private static function normalize(string $locale): ?string
    {
        $locale = strtolower(trim($locale));
        if ($locale === '') {
            return null;
        }

        $locale = substr($locale, 0, 2);
        return in_array($locale, self::SUPPORTED_LOCALES, true) ? $locale : null;
    }
}
