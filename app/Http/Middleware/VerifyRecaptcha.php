<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class VerifyRecaptcha
{
    /**
     * Tolak POST yang tidak membawa jawaban reCAPTCHA valid.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $answer = $request->input('g-recaptcha-response');

        $verified = is_string($answer) && $answer !== ''
            && Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret'),
                'response' => $answer,
            ])->json('success') === true;

        return $verified
            ? $next($request)
            : back()->with('error', 'reCAPTCHA verification failed.')->withInput();
    }
}
