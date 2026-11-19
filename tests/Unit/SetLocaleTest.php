<?php

namespace Tests\Unit;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Tests\TestCase;

class SetLocaleTest extends TestCase
{
    public function test_authenticated_users_get_their_saved_locale(): void
    {
        $request = Request::create('/dashboard');
        $request->setUserResolver(fn () => (object) ['idioma_preferido' => 'en']);

        (new SetLocale)->handle($request, fn () => response()->noContent());

        $this->assertSame('en', app()->getLocale());
    }

    public function test_unsupported_locales_fall_back_to_spanish(): void
    {
        $request = Request::create('/dashboard');
        $request->setUserResolver(fn () => (object) ['idioma_preferido' => 'fr']);

        (new SetLocale)->handle($request, fn () => response()->noContent());

        $this->assertSame('es', app()->getLocale());
    }
}
