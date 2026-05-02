<?php

namespace Tests\Unit;

use App\Support\DigiflazzProductLogo;
use PHPUnit\Framework\TestCase;

class DigiflazzProductLogoTest extends TestCase
{
    public function test_it_classifies_game_product_logo_emotes(): void
    {
        $this->assertSame(DigiflazzProductLogo::DIAMOND, DigiflazzProductLogo::emoteForProductText('Mobile Legends 86 Diamonds'));
        $this->assertSame(DigiflazzProductLogo::VOUCHER, DigiflazzProductLogo::emoteForProductText('Honor of Kings Token 80'));
        $this->assertSame(DigiflazzProductLogo::VOUCHER, DigiflazzProductLogo::emoteForProductText('Mobile Legends Weekly Diamond Pass'));
        $this->assertSame(DigiflazzProductLogo::USERNAME_CHECK, DigiflazzProductLogo::emoteForProductText('Cek Username Mobile Legends'));
    }
}
