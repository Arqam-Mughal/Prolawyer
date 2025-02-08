<?php

namespace Illuminate\Foundation\Http\Middleware;

/**
 * Alias of VerifyCsrfToken for consistency.
 */
class ValidateCsrfToken extends VerifyCsrfToken
{
    // app/Http/Middleware/VerifyCsrfToken.php

protected $except = [
    'payment/payu/success',
    'payment/payu/failure',
];

}
