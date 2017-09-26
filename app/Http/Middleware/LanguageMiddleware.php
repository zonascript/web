<?php

namespace App\Http\Middleware;

use Closure;

class LanguageMiddleware
{
    private $fallbackLanguage = 'en';
    private $supportedLanguages = [
        'en',
        'ch',
        'ru',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $language = $request->segment(1);

        if (!in_array($language, $this->supportedLanguages)) {
            abort(404);
        }

        /**
         * @var \Illuminate\Translation\Translator $translator
         */
        $translator = app()->make('translator');
        $translator->setLocale($language);
        $translator->setFallback($this->fallbackLanguage);

        return $next($request);
    }
}
