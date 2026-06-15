<?php
declare(strict_types=1);
namespace App\Core;

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BookingsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RatesController;
use App\Http\Controllers\Admin\ServicesController;
use App\Http\Controllers\Admin\ZonesController;
use App\Http\Controllers\Admin\VehiclesController;
use App\Http\Controllers\Admin\PlacesController;
use App\Http\Controllers\Admin\AccountingController;
use App\Http\Controllers\Admin\KpisController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\AirlinesController;
use App\Http\Controllers\Admin\CurrenciesController;
use App\Http\Controllers\Admin\HomeContentController;
use App\Http\Controllers\Admin\OperationsAgendaController;
use App\Http\Controllers\Admin\PaxRangesController;
use App\Http\Controllers\Admin\ProvidersController;
use App\Http\Controllers\Public\CheckoutController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Middlewares\RequireAuth;
use App\Http\Middlewares\RequirePermission;

class App {
    private Router $router;

    public function __construct()
    {
        $this->router = new Router();
        $this->registerRoutes();
    }

    public function run(): void
    {
        $this->startSession();
        $request = Request::capture();

        $guardResponse = $this->authorize($request);
        if ($guardResponse instanceof Response) {
            $guardResponse->send();
            return;
        }

        $response = $this->router->dispatch($request);
        $response->send();
    }

    private function registerRoutes(): void
    {
        // Public routes
        $this->router->get('/', SearchController::class . '@index');
        $this->router->post('/search', SearchController::class . '@search');
        $this->router->get('/api/places', SearchController::class . '@apiPlaces');
        $this->router->get('/api/airlines', SearchController::class . '@apiAirlines');

        $this->router->post('/checkout/start', CheckoutController::class . '@start');
        $this->router->get('/checkout/details', CheckoutController::class . '@details');
        $this->router->post('/checkout/details', CheckoutController::class . '@saveDetails');
        $this->router->get('/checkout/payment', CheckoutController::class . '@payment');
        $this->router->post('/checkout/payment', CheckoutController::class . '@pay');
        $this->router->post('/checkout/mercado-pago/start', CheckoutController::class . '@startMercadoPago');
        $this->router->get('/checkout/mercado-pago/return', CheckoutController::class . '@mercadoPagoReturn');
        $this->router->post('/checkout/stripe/start', CheckoutController::class . '@startStripe');
        $this->router->get('/checkout/stripe/return', CheckoutController::class . '@stripeReturn');
        $this->router->post('/checkout/paypal/start', CheckoutController::class . '@startPayPal');
        $this->router->get('/checkout/paypal/return', CheckoutController::class . '@payPalReturn');
        $this->router->post('/checkout/openpay/start', CheckoutController::class . '@startOpenPay');
        $this->router->get('/checkout/openpay/return', CheckoutController::class . '@openPayReturn');
        $this->router->get('/checkout/confirmation', CheckoutController::class . '@confirmation');
        $this->router->get('/checkout/voucher', CheckoutController::class . '@voucher');
        $this->router->post('/webhooks/mercado-pago', CheckoutController::class . '@mercadoPagoWebhook');
        $this->router->get('/webhooks/mercado-pago', CheckoutController::class . '@mercadoPagoWebhook');
        $this->router->post('/webhooks/openpay', CheckoutController::class . '@openPayWebhook');
        $this->router->get('/webhooks/openpay', CheckoutController::class . '@openPayWebhook');
        $this->router->post('/webhooks/stripe', CheckoutController::class . '@stripeWebhook');
        $this->router->post('/webhooks/paypal', CheckoutController::class . '@payPalWebhook');

        // Admin auth routes (no middleware)
        $this->router->get('/admin/login', AuthController::class . '@showLogin');
        $this->router->post('/admin/login', AuthController::class . '@login');
        $this->router->post('/admin/logout', AuthController::class . '@logout');

        // Admin routes (protected)
        $this->router->get('/admin', DashboardController::class . '@index');
        $this->router->get('/admin/bookings', BookingsController::class . '@index');
        $this->router->get('/admin/bookings/quote', BookingsController::class . '@quote');
        $this->router->get('/admin/bookings/create', BookingsController::class . '@create');
        $this->router->post('/admin/bookings/create', BookingsController::class . '@create');
        $this->router->get('/admin/bookings/edit', BookingsController::class . '@edit');
        $this->router->get('/admin/bookings/service-order', BookingsController::class . '@serviceOrder');
        $this->router->get('/admin/bookings/voucher', BookingsController::class . '@voucher');
        $this->router->get('/admin/bookings/export', BookingsController::class . '@export');
        $this->router->get('/admin/bookings/print', BookingsController::class . '@print');
        $this->router->post('/admin/bookings/update', BookingsController::class . '@update');
        $this->router->post('/admin/bookings/delete-request', BookingsController::class . '@requestDelete');
        $this->router->post('/admin/bookings/delete-review', BookingsController::class . '@reviewDeleteRequest');
        $this->router->post('/admin/bookings/delete', BookingsController::class . '@delete');
        
        $this->router->get('/admin/catalog/zones', ZonesController::class . '@index');
        $this->router->get('/admin/catalog/zones/create', ZonesController::class . '@create');
        $this->router->post('/admin/catalog/zones/create', ZonesController::class . '@create');
        $this->router->get('/admin/catalog/zones/edit', ZonesController::class . '@edit');
        $this->router->post('/admin/catalog/zones/edit', ZonesController::class . '@edit');
        
        $this->router->get('/admin/catalog/services', ServicesController::class . '@index');
        $this->router->get('/admin/catalog/services/create', ServicesController::class . '@create');
        $this->router->post('/admin/catalog/services/create', ServicesController::class . '@create');
        $this->router->get('/admin/catalog/services/edit', ServicesController::class . '@edit');
        $this->router->post('/admin/catalog/services/edit', ServicesController::class . '@edit');
        $this->router->post('/admin/catalog/services/delete', ServicesController::class . '@delete');
        $this->router->get('/admin/catalog/currencies', CurrenciesController::class . '@index');
        $this->router->get('/admin/catalog/currencies/create', CurrenciesController::class . '@create');
        $this->router->post('/admin/catalog/currencies/create', CurrenciesController::class . '@create');
        $this->router->get('/admin/catalog/currencies/edit', CurrenciesController::class . '@edit');
        $this->router->post('/admin/catalog/currencies/edit', CurrenciesController::class . '@edit');
        
        $this->router->get('/admin/catalog/vehicles', VehiclesController::class . '@index');
        $this->router->get('/admin/catalog/vehicles/create', VehiclesController::class . '@create');
        $this->router->post('/admin/catalog/vehicles/create', VehiclesController::class . '@create');
        $this->router->get('/admin/catalog/vehicles/edit', VehiclesController::class . '@edit');
        $this->router->post('/admin/catalog/vehicles/edit', VehiclesController::class . '@edit');

        $this->router->get('/admin/catalog/providers', ProvidersController::class . '@index');
        $this->router->get('/admin/catalog/providers/create', ProvidersController::class . '@create');
        $this->router->post('/admin/catalog/providers/create', ProvidersController::class . '@create');
        $this->router->get('/admin/catalog/providers/edit', ProvidersController::class . '@edit');
        $this->router->post('/admin/catalog/providers/edit', ProvidersController::class . '@edit');
        
        $this->router->get('/admin/catalog/places', PlacesController::class . '@index');
        $this->router->get('/admin/catalog/places/export', PlacesController::class . '@export');
        $this->router->get('/admin/catalog/places/create', PlacesController::class . '@create');
        $this->router->post('/admin/catalog/places/create', PlacesController::class . '@create');
        $this->router->get('/admin/catalog/places/edit', PlacesController::class . '@edit');
        $this->router->post('/admin/catalog/places/edit', PlacesController::class . '@edit');
        
        $this->router->get('/admin/catalog/airlines', AirlinesController::class . '@index');
        $this->router->get('/admin/catalog/airlines/export', AirlinesController::class . '@export');
        $this->router->get('/admin/catalog/airlines/create', AirlinesController::class . '@create');
        $this->router->post('/admin/catalog/airlines/create', AirlinesController::class . '@create');
        $this->router->get('/admin/catalog/airlines/edit', AirlinesController::class . '@edit');
        $this->router->post('/admin/catalog/airlines/edit', AirlinesController::class . '@edit');
        
        $this->router->get('/admin/pricing/rate-rules', RatesController::class . '@index');
        $this->router->get('/admin/pricing/rate-rules/edit', RatesController::class . '@edit');
        $this->router->post('/admin/pricing/rate-rules/edit', RatesController::class . '@edit');
        $this->router->get('/admin/pricing/rate-rules/edit-group', RatesController::class . '@editGroup');
        $this->router->post('/admin/pricing/rate-rules/edit-group', RatesController::class . '@editGroup');
        $this->router->get('/admin/pricing/pax-ranges', PaxRangesController::class . '@index');
        $this->router->get('/admin/pricing/pax-ranges/create', PaxRangesController::class . '@create');
        $this->router->post('/admin/pricing/pax-ranges/create', PaxRangesController::class . '@create');
        $this->router->get('/admin/pricing/pax-ranges/edit', PaxRangesController::class . '@edit');
        $this->router->post('/admin/pricing/pax-ranges/edit', PaxRangesController::class . '@edit');
        $this->router->post('/admin/pricing/pax-ranges/delete', PaxRangesController::class . '@delete');
        
        $this->router->get('/admin/accounting', AccountingController::class . '@index');
        $this->router->get('/admin/accounting/export', AccountingController::class . '@export');
        $this->router->get('/admin/kpis', KpisController::class . '@index');
        $this->router->get('/admin/kpis/export', KpisController::class . '@export');
        $this->router->get('/admin/operations/agenda', OperationsAgendaController::class . '@index');
        $this->router->get('/admin/operations/agenda/print', OperationsAgendaController::class . '@print');
        $this->router->get('/admin/operations/agenda/export', OperationsAgendaController::class . '@export');
        $this->router->post('/admin/operations/agenda', OperationsAgendaController::class . '@update');

        $this->router->get('/admin/users', UsersController::class . '@index');
        $this->router->get('/admin/users/create', UsersController::class . '@create');
        $this->router->post('/admin/users/create', UsersController::class . '@create');
        $this->router->get('/admin/users/edit', UsersController::class . '@edit');
        $this->router->post('/admin/users/edit', UsersController::class . '@edit');

        $this->router->get('/admin/content/home', HomeContentController::class . '@edit');
        $this->router->post('/admin/content/home', HomeContentController::class . '@edit');
    }

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'httponly' => true,
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'samesite' => 'Lax',
            'path' => '/',
        ]);

        session_start();
    }

    private function authorize(Request $request): ?Response
    {
        $path = $this->normalizePath($request->path());

        if (!$this->isProtectedAdminPath($path)) {
            return null;
        }

        $authResponse = RequireAuth::handle($request);
        if ($authResponse instanceof Response) {
            return $authResponse;
        }

        $permissionCode = $this->permissionForPath($path);
        if ($permissionCode === null) {
            return null;
        }

        return RequirePermission::handle($request, $permissionCode);
    }

    private function isProtectedAdminPath(string $path): bool
    {
        if (strncmp($path, '/admin', 6) !== 0) {
            return false;
        }

        return !in_array($path, ['/admin/login', '/admin/logout'], true);
    }

    private function permissionForPath(string $path): ?string
    {
        $permissions = [
            '/admin' => 'dashboard.view',
            '/admin/bookings' => 'bookings.view',
            '/admin/bookings/quote' => 'bookings.create',
            '/admin/bookings/create' => 'bookings.create',
            '/admin/bookings/edit' => 'bookings.create',
            '/admin/bookings/service-order' => 'bookings.manage',
            '/admin/bookings/voucher' => 'bookings.view',
            '/admin/bookings/export' => 'bookings.view',
            '/admin/bookings/print' => 'bookings.view',
            '/admin/bookings/update' => 'bookings.create',
            '/admin/bookings/delete-request' => 'bookings.delete.request',
            '/admin/bookings/delete-review' => 'bookings.delete.approve',
            '/admin/bookings/delete' => 'bookings.manage',
            '/admin/catalog/zones' => 'catalog.manage',
            '/admin/catalog/zones/create' => 'catalog.manage',
            '/admin/catalog/zones/edit' => 'catalog.manage',
            '/admin/catalog/services' => 'catalog.manage',
            '/admin/catalog/services/create' => 'catalog.manage',
            '/admin/catalog/services/edit' => 'catalog.manage',
            '/admin/catalog/services/delete' => 'catalog.manage',
            '/admin/catalog/currencies' => 'catalog.manage',
            '/admin/catalog/currencies/create' => 'catalog.manage',
            '/admin/catalog/currencies/edit' => 'catalog.manage',
            '/admin/catalog/vehicles' => 'catalog.manage',
            '/admin/catalog/vehicles/create' => 'catalog.manage',
            '/admin/catalog/vehicles/edit' => 'catalog.manage',
            '/admin/catalog/providers' => 'catalog.manage',
            '/admin/catalog/providers/create' => 'catalog.manage',
            '/admin/catalog/providers/edit' => 'catalog.manage',
            '/admin/catalog/places' => 'catalog.manage',
            '/admin/catalog/places/export' => 'catalog.manage',
            '/admin/catalog/places/create' => 'catalog.manage',
            '/admin/catalog/places/edit' => 'catalog.manage',
            '/admin/catalog/airlines' => 'catalog.manage',
            '/admin/catalog/airlines/export' => 'catalog.manage',
            '/admin/catalog/airlines/create' => 'catalog.manage',
            '/admin/catalog/airlines/edit' => 'catalog.manage',
            '/admin/pricing/rate-rules' => 'pricing.manage',
            '/admin/pricing/rate-rules/edit' => 'pricing.manage',
            '/admin/pricing/rate-rules/edit-group' => 'pricing.manage',
            '/admin/pricing/pax-ranges' => 'pricing.manage',
            '/admin/pricing/pax-ranges/create' => 'pricing.manage',
            '/admin/pricing/pax-ranges/edit' => 'pricing.manage',
            '/admin/pricing/pax-ranges/delete' => 'pricing.manage',
            '/admin/accounting' => 'accounting.view',
            '/admin/accounting/export' => 'accounting.view',
            '/admin/kpis' => 'kpis.view',
            '/admin/kpis/export' => 'kpis.view',
            '/admin/operations/agenda' => 'operations.view',
            '/admin/operations/agenda/print' => 'operations.view',
            '/admin/operations/agenda/export' => 'operations.view',
            '/admin/users' => 'users.manage',
            '/admin/users/create' => 'users.manage',
            '/admin/users/edit' => 'users.manage',
            '/admin/content/home' => 'home.manage',
        ];

        return $permissions[$path] ?? null;
    }

    private function normalizePath(string $path): string
    {
        $normalized = '/' . trim($path, '/');
        return $normalized === '//' ? '/' : $normalized;
    }
}
