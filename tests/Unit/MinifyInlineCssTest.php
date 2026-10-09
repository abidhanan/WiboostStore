<?php

namespace Tests\Unit;

use App\Http\Middleware\MinifyInlineCss;
use PHPUnit\Framework\TestCase;

class MinifyInlineCssTest extends TestCase
{
    public function test_minify_keeps_calc_spacing_selectors_and_values(): void
    {
        $css = <<<'CSS'
        /* komentar */
        body.wiboost-skeu .a :is(.b, .c) > .d {
            width: calc(100% - 2rem);
            margin: 0  auto ;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .5), 0 2px 3px rgba(0, 0, 0, .1) !important;
        }
        @media (min-width: 640px) { .x { top: 1px; } }
        CSS;

        $out = MinifyInlineCss::minify($css);

        $this->assertStringNotContainsString('komentar', $out);
        $this->assertStringContainsString('calc(100% - 2rem)', $out);
        $this->assertStringContainsString('.a :is(.b,.c)>.d{width: calc(100% - 2rem);margin: 0 auto', $out);
        $this->assertStringContainsString('inset 0 1px 0 rgba(255,255,255,.5),0 2px 3px rgba(0,0,0,.1) !important}', $out);
        $this->assertStringContainsString('@media (min-width: 640px){.x{top: 1px}}', $out);
    }

    public function test_minify_skips_blocks_with_template_markers(): void
    {
        $css = ".a {  color: {{ \$x }};  }";

        $this->assertSame($css, MinifyInlineCss::minify($css));
    }

    public function test_minify_keeps_strings_and_data_uris_untouched(): void
    {
        $css = ".a::after { content: \" > , ; \"; background: url(\"data:image/svg+xml,%3Csvg points=\x270, 0  24,0\x27%3E\") no-repeat ; }";

        $out = MinifyInlineCss::minify($css);

        $this->assertStringContainsString("content: \" > , ; \"", $out);
        $this->assertStringContainsString("points=\x270, 0  24,0\x27", $out);
        $this->assertStringContainsString("no-repeat}", $out);
    }
}