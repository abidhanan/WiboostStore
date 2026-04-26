<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    public function test_public_legal_pages_can_be_rendered(): void
    {
        foreach (['terms', 'privacy-policy', 'refund-policy', 'contact'] as $page) {
            $this->get(route('legal.show', $page))
                ->assertOk()
                ->assertSee('Wiboost Store');
        }
    }
}
