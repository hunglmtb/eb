<?php namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel {

	/**
	 * The application's global HTTP middleware stack.
	 *
	 * @var array
	 */
	protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        \App\Http\Middleware\App::class
	];

	/**
	 * The application's route middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = [
        'eb.auth' => \App\Http\Middleware\EBAuthenticate::class,
		'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
		'admin' => \App\Http\Middleware\IsAdmin::class,
		'redac' => \App\Http\Middleware\IsRedactor::class,
		'ajax' => \App\Http\Middleware\IsAjax::class,
        'saveWorkspace' => \App\Http\Middleware\SaveWorkspace::class,
        'locked' => \App\Http\Middleware\CheckTableLocked::class,
		'checkRight' => \App\Http\Middleware\CheckRight::class,
	];

}
