<?php

namespace Tests\Feature;

use App\Http\Controllers\User\DashboardController;
use ReflectionClass;
use Tests\TestCase;

class MembershipPricePresentationTest extends TestCase
{
    public function test_membership_price_is_ten_thousand_in_payment_and_view(): void
    {
        $controller = new ReflectionClass(DashboardController::class);
        $price = $controller->getReflectionConstant('MEMBERSHIP_PRICE');
        $view = file_get_contents(resource_path('views/user/membership.blade.php'));

        $this->assertNotFalse($price);
        $this->assertSame(10000, $price->getValue());
        $this->assertStringContainsString("number_format(\$membershipPrice, 0, ',', '.')", $view);
        $this->assertStringNotContainsString('Rp 30.000 / bulan.', $view);
        $this->assertStringContainsString('membership-panel-header', $view);
        $this->assertSame(3, substr_count($view, 'membership-benefit-item rounded'));
    }
}
