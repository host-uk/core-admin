<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

use Core\Front\Components\Layout;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\View\View;

/**
 * Tests for the HLCRF (Header-Left-Content-Right-Footer) Layout System.
 *
 * These tests verify the complete HLCRF layout system including:
 * - HierarchicalLayoutBuilder parsing and variant handling
 * - Nested layout rendering with correct ID propagation
 * - Self-documenting IDs (H-0, C-R-2, etc.)
 * - Slot rendering and region management
 * - Responsive breakpoint support via attributes
 */

// =============================================================================
// Layout Variant Parsing Tests
// =============================================================================

describe('Layout variant parsing', function () {
    it('parses content-only variant (C)', function () {
        $layout = Layout::make('C')
            ->c('<main>Content</main>');

        $html = $layout->render();

        expect($html)->toContain('data-layout="root"')
            ->and($html)->toContain('data-slot="C"')
            ->and($html)->toContain('hlcrf-content');
    });

    it('parses header-content variant (HC)', function () {
        $layout = Layout::make('HC')
            ->h('<nav>Navigation</nav>')
            ->c('<main>Content</main>');

        $html = $layout->render();

        expect($html)->toContain('data-slot="H"')
            ->and($html)->toContain('data-slot="C"')
            ->and($html)->not->toContain('data-slot="L"')
            ->and($html)->not->toContain('data-slot="R"')
            ->and($html)->not->toContain('data-slot="F"');
    });

    it('parses header-content-footer variant (HCF)', function () {
        $layout = Layout::make('HCF')
            ->h('<nav>Header</nav>')
            ->c('<main>Content</main>')
            ->f('<footer>Footer</footer>');

        $html = $layout->render();

        expect($html)->toContain('data-slot="H"')
            ->and($html)->toContain('data-slot="C"')
            ->and($html)->toContain('data-slot="F"')
            ->and($html)->not->toContain('data-slot="L"')
            ->and($html)->not->toContain('data-slot="R"');
    });

    it('parses left-content variant (LC)', function () {
        $layout = Layout::make('LC')
            ->l('<nav>Sidebar</nav>')
            ->c('<main>Content</main>');

        $html = $layout->render();

        expect($html)->toContain('data-slot="L"')
            ->and($html)->toContain('data-slot="C"')
            ->and($html)->not->toContain('data-slot="H"')
            ->and($html)->not->toContain('data-slot="R"')
            ->and($html)->not->toContain('data-slot="F"');
    });

    it('parses content-right variant (CR)', function () {
        $layout = Layout::make('CR')
            ->c('<main>Content</main>')
            ->r('<aside>Widgets</aside>');

        $html = $layout->render();

        expect($html)->toContain('data-slot="C"')
            ->and($html)->toContain('data-slot="R"')
            ->and($html)->not->toContain('data-slot="H"')
            ->and($html)->not->toContain('data-slot="L"')
            ->and($html)->not->toContain('data-slot="F"');
    });

    it('parses three-column variant (LCR)', function () {
        $layout = Layout::make('LCR')
            ->l('<nav>Navigation</nav>')
            ->c('<main>Content</main>')
            ->r('<aside>Widgets</aside>');

        $html = $layout->render();

        expect($html)->toContain('data-slot="L"')
            ->and($html)->toContain('data-slot="C"')
            ->and($html)->toContain('data-slot="R"')
            ->and($html)->not->toContain('data-slot="H"')
            ->and($html)->not->toContain('data-slot="F"');
    });

    it('parses full variant (HLCRF)', function () {
        $layout = Layout::make('HLCRF')
            ->h('<header>Header</header>')
            ->l('<nav>Left</nav>')
            ->c('<main>Content</main>')
            ->r('<aside>Right</aside>')
            ->f('<footer>Footer</footer>');

        $html = $layout->render();

        expect($html)->toContain('data-slot="H"')
            ->and($html)->toContain('data-slot="L"')
            ->and($html)->toContain('data-slot="C"')
            ->and($html)->toContain('data-slot="R"')
            ->and($html)->toContain('data-slot="F"');
    });

    it('normalises lowercase variant to uppercase', function () {
        $layout = Layout::make('hlcrf')
            ->h('<header>Header</header>')
            ->l('<nav>Left</nav>')
            ->c('<main>Content</main>')
            ->r('<aside>Right</aside>')
            ->f('<footer>Footer</footer>');

        $html = $layout->render();

        expect($html)->toContain('data-slot="H"')
            ->and($html)->toContain('data-slot="L"')
            ->and($html)->toContain('data-slot="C"')
            ->and($html)->toContain('data-slot="R"')
            ->and($html)->toContain('data-slot="F"');
    });

    it('uses HCF as default variant', function () {
        $layout = Layout::make()
            ->h('<header>Header</header>')
            ->c('<main>Content</main>')
            ->f('<footer>Footer</footer>');

        $html = $layout->render();

        expect($html)->toContain('data-slot="H"')
            ->and($html)->toContain('data-slot="C"')
            ->and($html)->toContain('data-slot="F"');
    });
});

// =============================================================================
// Self-Documenting ID System Tests
// =============================================================================

describe('Self-documenting ID system', function () {
    it('generates correct block IDs for single items', function () {
        $layout = Layout::make('HLCRF')
            ->h('<header>Header</header>')
            ->l('<nav>Left</nav>')
            ->c('<main>Content</main>')
            ->r('<aside>Right</aside>')
            ->f('<footer>Footer</footer>');

        $html = $layout->render();

        expect($html)->toContain('data-block="H-0"')
            ->and($html)->toContain('data-block="L-0"')
            ->and($html)->toContain('data-block="C-0"')
            ->and($html)->toContain('data-block="R-0"')
            ->and($html)->toContain('data-block="F-0"');
    });

    it('generates sequential block IDs for multiple items', function () {
        $layout = Layout::make('C')
            ->c('<section>First</section>')
            ->c('<section>Second</section>')
            ->c('<section>Third</section>');

        $html = $layout->render();

        expect($html)->toContain('data-block="C-0"')
            ->and($html)->toContain('data-block="C-1"')
            ->and($html)->toContain('data-block="C-2"');
    });

    it('generates sequential IDs for header items', function () {
        $layout = Layout::make('HC')
            ->h('<nav>Logo</nav>')
            ->h('<nav>Navigation</nav>')
            ->h('<nav>User Menu</nav>')
            ->c('<main>Content</main>');

        $html = $layout->render();

        expect($html)->toContain('data-block="H-0"')
            ->and($html)->toContain('data-block="H-1"')
            ->and($html)->toContain('data-block="H-2"');
    });

    it('generates correct nested IDs for nested layouts', function () {
        $nested = Layout::make('LR')
            ->l('Nested Left')
            ->r('Nested Right');

        $outer = Layout::make('C')
            ->c($nested);

        $html = $outer->render();

        // The nested layout should have IDs prefixed with parent context
        expect($html)->toContain('data-block="C-0-L-0"')
            ->and($html)->toContain('data-block="C-0-R-0"');
    });

    it('generates correct deeply nested IDs', function () {
        $deepNested = Layout::make('C')
            ->c('Deep content');

        $nested = Layout::make('LR')
            ->l($deepNested)
            ->r('Nested Right');

        $outer = Layout::make('C')
            ->c($nested);

        $html = $outer->render();

        expect($html)->toContain('data-block="C-0-L-0-C-0"');
    });

    it('sets correct data-layout attribute on root', function () {
        $layout = Layout::make('C')
            ->c('<main>Content</main>');

        $html = $layout->render();

        expect($html)->toContain('data-layout="root"');
    });

    it('sets correct data-layout attribute on nested layouts', function () {
        $nested = Layout::make('C')
            ->c('Nested content');

        $outer = Layout::make('C')
            ->c($nested);

        $html = $outer->render();

        // Nested layout should have a path-based layout ID
        expect($html)->toContain('data-layout="C-0"');
    });
});

// =============================================================================
// Nested Layout Rendering Tests
// =============================================================================

describe('Nested layout rendering', function () {
    it('renders nested layout within content', function () {
        $nested = Layout::make('LCR')
            ->l('<nav>Nested Nav</nav>')
            ->c('<main>Nested Content</main>')
            ->r('<aside>Nested Aside</aside>');

        $outer = Layout::make('HCF')
            ->h('<header>Header</header>')
            ->c($nested)
            ->f('<footer>Footer</footer>');

        $html = $outer->render();

        expect($html)->toContain('Nested Nav')
            ->and($html)->toContain('Nested Content')
            ->and($html)->toContain('Nested Aside')
            ->and($html)->toContain('Header')
            ->and($html)->toContain('Footer');
    });

    it('renders multiple levels of nesting', function () {
        $level3 = Layout::make('C')
            ->c('Level 3 Content');

        $level2 = Layout::make('LC')
            ->l('Level 2 Sidebar')
            ->c($level3);

        $level1 = Layout::make('HCF')
            ->h('Level 1 Header')
            ->c($level2)
            ->f('Level 1 Footer');

        $html = $level1->render();

        expect($html)->toContain('Level 3 Content')
            ->and($html)->toContain('Level 2 Sidebar')
            ->and($html)->toContain('Level 1 Header')
            ->and($html)->toContain('Level 1 Footer');
    });

    it('renders nested layouts in different slots', function () {
        $leftNested = Layout::make('C')
            ->c('Left nested content');

        $rightNested = Layout::make('C')
            ->c('Right nested content');

        $outer = Layout::make('LCR')
            ->l($leftNested)
            ->c('Main content')
            ->r($rightNested);

        $html = $outer->render();

        expect($html)->toContain('Left nested content')
            ->and($html)->toContain('Main content')
            ->and($html)->toContain('Right nested content')
            ->and($html)->toContain('data-block="L-0-C-0"')
            ->and($html)->toContain('data-block="R-0-C-0"');
    });

    it('preserves correct path context through multiple nested items', function () {
        $nested1 = Layout::make('C')->c('First nested');
        $nested2 = Layout::make('C')->c('Second nested');

        $outer = Layout::make('C')
            ->c($nested1)
            ->c($nested2);

        $html = $outer->render();

        expect($html)->toContain('data-layout="C-0"')
            ->and($html)->toContain('data-layout="C-1"')
            ->and($html)->toContain('data-block="C-0-C-0"')
            ->and($html)->toContain('data-block="C-1-C-0"');
    });
});

// =============================================================================
// Slot Rendering Tests
// =============================================================================

describe('Slot rendering', function () {
    it('renders string content directly', function () {
        $layout = Layout::make('C')
            ->c('Simple string content');

        $html = $layout->render();

        expect($html)->toContain('Simple string content');
    });

    it('renders HTML content correctly', function () {
        $layout = Layout::make('C')
            ->c('<div class="card"><h1>Title</h1><p>Body</p></div>');

        $html = $layout->render();

        expect($html)->toContain('<div class="card"><h1>Title</h1><p>Body</p></div>');
    });

    it('renders Htmlable objects', function () {
        $htmlable = new class implements Htmlable
        {
            public function toHtml(): string
            {
                return '<span>Htmlable Content</span>';
            }
        };

        $layout = Layout::make('C')
            ->c($htmlable);

        $html = $layout->render();

        expect($html)->toContain('<span>Htmlable Content</span>');
    });

    it('renders callable/closure content', function () {
        $layout = Layout::make('C')
            ->c(fn () => '<div>Closure Content</div>');

        $html = $layout->render();

        expect($html)->toContain('<div>Closure Content</div>');
    });

    it('handles null content gracefully', function () {
        $layout = Layout::make('C')
            ->c(null)
            ->c('Valid content');

        $html = $layout->render();

        expect($html)->toContain('Valid content')
            ->and($html)->toContain('data-block="C-0"')
            ->and($html)->toContain('data-block="C-1"');
    });

    it('does not render empty slots', function () {
        $layout = Layout::make('LCR')
            ->c('<main>Content only</main>');

        $html = $layout->render();

        // L and R slots should not appear since they have no content
        expect($html)->toContain('data-slot="C"')
            ->and($html)->not->toContain('data-slot="L"')
            ->and($html)->not->toContain('data-slot="R"');
    });

    it('supports variadic item addition', function () {
        $layout = Layout::make('C')
            ->c('First', 'Second', 'Third');

        $html = $layout->render();

        expect($html)->toContain('First')
            ->and($html)->toContain('Second')
            ->and($html)->toContain('Third')
            ->and($html)->toContain('data-block="C-0"')
            ->and($html)->toContain('data-block="C-1"')
            ->and($html)->toContain('data-block="C-2"');
    });
});

// =============================================================================
// Alias Method Tests
// =============================================================================

describe('Alias methods', function () {
    it('addHeader works like h()', function () {
        $layout = Layout::make('HC')
            ->addHeader('<nav>Header Content</nav>')
            ->c('<main>Content</main>');

        $html = $layout->render();

        expect($html)->toContain('Header Content')
            ->and($html)->toContain('data-block="H-0"');
    });

    it('addLeft works like l()', function () {
        $layout = Layout::make('LC')
            ->addLeft('<nav>Left Content</nav>')
            ->c('<main>Content</main>');

        $html = $layout->render();

        expect($html)->toContain('Left Content')
            ->and($html)->toContain('data-block="L-0"');
    });

    it('addContent works like c()', function () {
        $layout = Layout::make('C')
            ->addContent('<main>Main Content</main>');

        $html = $layout->render();

        expect($html)->toContain('Main Content')
            ->and($html)->toContain('data-block="C-0"');
    });

    it('addRight works like r()', function () {
        $layout = Layout::make('CR')
            ->c('<main>Content</main>')
            ->addRight('<aside>Right Content</aside>');

        $html = $layout->render();

        expect($html)->toContain('Right Content')
            ->and($html)->toContain('data-block="R-0"');
    });

    it('addFooter works like f()', function () {
        $layout = Layout::make('CF')
            ->c('<main>Content</main>')
            ->addFooter('<footer>Footer Content</footer>');

        $html = $layout->render();

        expect($html)->toContain('Footer Content')
            ->and($html)->toContain('data-block="F-0"');
    });

    it('alias methods support variadic arguments', function () {
        $layout = Layout::make('C')
            ->addContent('First', 'Second', 'Third');

        $html = $layout->render();

        expect($html)->toContain('First')
            ->and($html)->toContain('Second')
            ->and($html)->toContain('Third');
    });
});

// =============================================================================
// Attributes and CSS Classes Tests
// =============================================================================

describe('Attributes and CSS classes', function () {
    it('includes default hlcrf-layout class', function () {
        $layout = Layout::make('C')
            ->c('Content');

        $html = $layout->render();

        expect($html)->toContain('class="hlcrf-layout"');
    });

    it('adds custom class with class() method', function () {
        $layout = Layout::make('C')
            ->class('custom-class')
            ->c('Content');

        $html = $layout->render();

        expect($html)->toContain('class="hlcrf-layout custom-class"');
    });

    it('accumulates multiple classes', function () {
        $layout = Layout::make('C')
            ->class('first-class')
            ->class('second-class')
            ->c('Content');

        $html = $layout->render();

        expect($html)->toContain('hlcrf-layout')
            ->and($html)->toContain('first-class')
            ->and($html)->toContain('second-class');
    });

    it('sets custom attributes with attributes() method', function () {
        $layout = Layout::make('C')
            ->attributes(['id' => 'main-layout', 'data-theme' => 'dark'])
            ->c('Content');

        $html = $layout->render();

        expect($html)->toContain('id="main-layout"')
            ->and($html)->toContain('data-theme="dark"');
    });

    it('merges attributes correctly', function () {
        $layout = Layout::make('C')
            ->attributes(['data-first' => 'one'])
            ->attributes(['data-second' => 'two'])
            ->c('Content');

        $html = $layout->render();

        expect($html)->toContain('data-first="one"')
            ->and($html)->toContain('data-second="two"');
    });

    it('handles boolean true attributes', function () {
        $layout = Layout::make('C')
            ->attributes(['data-visible' => true])
            ->c('Content');

        $html = $layout->render();

        expect($html)->toContain('data-visible');
    });

    it('excludes boolean false attributes', function () {
        $layout = Layout::make('C')
            ->attributes(['data-hidden' => false])
            ->c('Content');

        $html = $layout->render();

        expect($html)->not->toContain('data-hidden');
    });

    it('excludes null attributes', function () {
        $layout = Layout::make('C')
            ->attributes(['data-maybe' => null])
            ->c('Content');

        $html = $layout->render();

        expect($html)->not->toContain('data-maybe');
    });

    it('escapes attribute values', function () {
        $layout = Layout::make('C')
            ->attributes(['data-value' => '<script>alert("xss")</script>'])
            ->c('Content');

        $html = $layout->render();

        expect($html)->not->toContain('<script>')
            ->and($html)->toContain('&lt;script&gt;');
    });
});

// =============================================================================
// CSS Structure Tests
// =============================================================================

describe('CSS structure classes', function () {
    it('renders header with hlcrf-header class', function () {
        $layout = Layout::make('HC')
            ->h('<nav>Header</nav>')
            ->c('<main>Content</main>');

        $html = $layout->render();

        expect($html)->toContain('class="hlcrf-header"');
    });

    it('renders left sidebar with hlcrf-left class', function () {
        $layout = Layout::make('LC')
            ->l('<nav>Left</nav>')
            ->c('<main>Content</main>');

        $html = $layout->render();

        expect($html)->toContain('class="hlcrf-left shrink-0"');
    });

    it('renders content with hlcrf-content class', function () {
        $layout = Layout::make('C')
            ->c('<main>Content</main>');

        $html = $layout->render();

        expect($html)->toContain('class="hlcrf-content flex-1"');
    });

    it('renders right sidebar with hlcrf-right class', function () {
        $layout = Layout::make('CR')
            ->c('<main>Content</main>')
            ->r('<aside>Right</aside>');

        $html = $layout->render();

        expect($html)->toContain('class="hlcrf-right shrink-0"');
    });

    it('renders footer with hlcrf-footer class', function () {
        $layout = Layout::make('CF')
            ->c('<main>Content</main>')
            ->f('<footer>Footer</footer>');

        $html = $layout->render();

        expect($html)->toContain('class="hlcrf-footer"');
    });

    it('renders body wrapper with hlcrf-body and flex classes', function () {
        $layout = Layout::make('LCR')
            ->l('<nav>Left</nav>')
            ->c('<main>Content</main>')
            ->r('<aside>Right</aside>');

        $html = $layout->render();

        expect($html)->toContain('class="hlcrf-body flex flex-1"');
    });
});

// =============================================================================
// Interface Implementation Tests
// =============================================================================

describe('Interface implementations', function () {
    it('implements Htmlable interface', function () {
        $layout = Layout::make('C')
            ->c('Content');

        expect($layout)->toBeInstanceOf(Htmlable::class);
    });

    it('toHtml() returns rendered HTML', function () {
        $layout = Layout::make('C')
            ->c('Test Content');

        $html = $layout->toHtml();

        expect($html)->toContain('Test Content')
            ->and($html)->toContain('data-layout="root"');
    });

    it('can be cast to string', function () {
        $layout = Layout::make('C')
            ->c('String Cast Content');

        $html = (string) $layout;

        expect($html)->toContain('String Cast Content')
            ->and($html)->toContain('data-layout="root"');
    });

    it('implements fluent interface (method chaining)', function () {
        $layout = Layout::make('HLCRF')
            ->h('Header')
            ->l('Left')
            ->c('Content')
            ->r('Right')
            ->f('Footer')
            ->class('custom')
            ->attributes(['id' => 'test']);

        expect($layout)->toBeInstanceOf(Layout::class);

        $html = $layout->render();
        expect($html)->toContain('Header')
            ->and($html)->toContain('Footer')
            ->and($html)->toContain('custom')
            ->and($html)->toContain('id="test"');
    });
});

// =============================================================================
// Real-World Layout Pattern Tests
// =============================================================================

describe('Real-world layout patterns', function () {
    it('renders admin dashboard pattern', function () {
        $adminLayout = Layout::make('HLCF')
            ->h('<nav class="bg-gray-900">Admin Header</nav>')
            ->l('<nav class="w-64">Sidebar Menu</nav>')
            ->c('<main class="p-6">Dashboard Content</main>')
            ->f('<footer class="text-sm">Admin Footer</footer>');

        $html = $adminLayout->render();

        expect($html)->toContain('Admin Header')
            ->and($html)->toContain('Sidebar Menu')
            ->and($html)->toContain('Dashboard Content')
            ->and($html)->toContain('Admin Footer')
            ->and($html)->toContain('data-slot="H"')
            ->and($html)->toContain('data-slot="L"')
            ->and($html)->toContain('data-slot="C"')
            ->and($html)->toContain('data-slot="F"');
    });

    it('renders documentation site pattern with nested content', function () {
        $docsLayout = Layout::make('HLCRF')
            ->h('<header>Docs Header</header>')
            ->l('<nav>Table of Contents</nav>')
            ->c(
                Layout::make('HCF')
                    ->h('<div>Page Title</div>')
                    ->c('<article>Documentation Content</article>')
                    ->f('<div>Prev | Next</div>')
            )
            ->r('<aside>On This Page</aside>')
            ->f('<footer>Site Footer</footer>');

        $html = $docsLayout->render();

        expect($html)->toContain('Docs Header')
            ->and($html)->toContain('Table of Contents')
            ->and($html)->toContain('Page Title')
            ->and($html)->toContain('Documentation Content')
            ->and($html)->toContain('On This Page')
            ->and($html)->toContain('Site Footer')
            ->and($html)->toContain('data-layout="C-0"'); // Nested layout ID
    });

    it('renders email client pattern with deeply nested layouts', function () {
        $emailClient = Layout::make('HLCR')
            ->h('<header>Email Header</header>')
            ->l('<aside>Folder List</aside>')
            ->c(
                Layout::make('LC')
                    ->l('<div>Email List</div>')
                    ->c('<div>Email Viewer</div>')
            )
            ->r('<aside>Contact Info</aside>');

        $html = $emailClient->render();

        expect($html)->toContain('Email Header')
            ->and($html)->toContain('Folder List')
            ->and($html)->toContain('Email List')
            ->and($html)->toContain('Email Viewer')
            ->and($html)->toContain('Contact Info')
            ->and($html)->toContain('data-block="C-0-L-0"')
            ->and($html)->toContain('data-block="C-0-C-0"');
    });

    it('renders e-commerce product page pattern', function () {
        $productPage = Layout::make('HCF')
            ->h('<header>Store Navigation</header>')
            ->c(
                Layout::make('LR')
                    ->l('<div>Product Images</div>')
                    ->r('<div>Product Details</div>')
            )
            ->c(
                Layout::make('CR')
                    ->c('<div>Customer Reviews</div>')
                    ->r('<aside>Related Products</aside>')
            )
            ->f('<footer>Store Footer</footer>');

        $html = $productPage->render();

        expect($html)->toContain('Store Navigation')
            ->and($html)->toContain('Product Images')
            ->and($html)->toContain('Product Details')
            ->and($html)->toContain('Customer Reviews')
            ->and($html)->toContain('Related Products')
            ->and($html)->toContain('Store Footer')
            ->and($html)->toContain('data-layout="C-0"')
            ->and($html)->toContain('data-layout="C-1"');
    });
});

// =============================================================================
// Edge Cases and Boundary Tests
// =============================================================================

describe('Edge cases and boundaries', function () {
    it('handles empty layout gracefully', function () {
        $layout = Layout::make('C');
        $html = $layout->render();

        expect($html)->toContain('data-layout="root"')
            ->and($html)->toContain('hlcrf-layout');
    });

    it('handles special characters in content', function () {
        $layout = Layout::make('C')
            ->c('<div>Special chars: &amp; &lt; &gt; "quotes"</div>');

        $html = $layout->render();

        expect($html)->toContain('&amp;')
            ->and($html)->toContain('&lt;')
            ->and($html)->toContain('&gt;');
    });

    it('handles unicode content', function () {
        $layout = Layout::make('C')
            ->c('<div>Unicode: </div>');

        $html = $layout->render();

        expect($html)->toContain('');
    });

    it('handles very long content', function () {
        $longContent = str_repeat('A', 10000);
        $layout = Layout::make('C')
            ->c("<div>$longContent</div>");

        $html = $layout->render();

        expect($html)->toContain($longContent);
    });

    it('handles many items in a single slot', function () {
        $layout = Layout::make('C');
        for ($i = 0; $i < 100; $i++) {
            $layout->c("<div>Item $i</div>");
        }

        $html = $layout->render();

        expect($html)->toContain('data-block="C-0"')
            ->and($html)->toContain('data-block="C-99"')
            ->and($html)->toContain('Item 0')
            ->and($html)->toContain('Item 99');
    });

    it('handles mixed content types in same slot', function () {
        $htmlable = new class implements Htmlable
        {
            public function toHtml(): string
            {
                return '<span>Htmlable</span>';
            }
        };

        $layout = Layout::make('C')
            ->c('String content')
            ->c($htmlable)
            ->c(fn () => '<div>Closure</div>')
            ->c(Layout::make('C')->c('Nested'));

        $html = $layout->render();

        expect($html)->toContain('String content')
            ->and($html)->toContain('<span>Htmlable</span>')
            ->and($html)->toContain('<div>Closure</div>')
            ->and($html)->toContain('Nested');
    });
});

// =============================================================================
// Semantic HTML Structure Tests
// =============================================================================

describe('Semantic HTML structure', function () {
    it('uses header element for H slot', function () {
        $layout = Layout::make('HC')
            ->h('Header content')
            ->c('Content');

        $html = $layout->render();

        expect($html)->toMatch('/<header[^>]*class="hlcrf-header"[^>]*>/');
    });

    it('uses aside element for L slot', function () {
        $layout = Layout::make('LC')
            ->l('Left content')
            ->c('Content');

        $html = $layout->render();

        expect($html)->toMatch('/<aside[^>]*class="hlcrf-left/');
    });

    it('uses main element for C slot', function () {
        $layout = Layout::make('C')
            ->c('Content');

        $html = $layout->render();

        expect($html)->toMatch('/<main[^>]*class="hlcrf-content/');
    });

    it('uses aside element for R slot', function () {
        $layout = Layout::make('CR')
            ->c('Content')
            ->r('Right content');

        $html = $layout->render();

        expect($html)->toMatch('/<aside[^>]*class="hlcrf-right/');
    });

    it('uses footer element for F slot', function () {
        $layout = Layout::make('CF')
            ->c('Content')
            ->f('Footer content');

        $html = $layout->render();

        expect($html)->toMatch('/<footer[^>]*class="hlcrf-footer"[^>]*>/');
    });
});

// =============================================================================
// Static Factory Method Tests
// =============================================================================

describe('Static factory method', function () {
    it('make() creates new instance', function () {
        $layout = Layout::make('C');

        expect($layout)->toBeInstanceOf(Layout::class);
    });

    it('make() with variant returns layout with that variant', function () {
        $layout = Layout::make('HLCRF')
            ->h('H')->l('L')->c('C')->r('R')->f('F');

        $html = $layout->render();

        expect($html)->toContain('data-slot="H"')
            ->and($html)->toContain('data-slot="L"')
            ->and($html)->toContain('data-slot="C"')
            ->and($html)->toContain('data-slot="R"')
            ->and($html)->toContain('data-slot="F"');
    });

    it('make() with path parameter sets initial path', function () {
        $layout = Layout::make('C', 'PREFIX-')
            ->c('Content');

        $html = $layout->render();

        expect($html)->toContain('data-layout="PREFIX"')
            ->and($html)->toContain('data-slot="PREFIX-C"')
            ->and($html)->toContain('data-block="PREFIX-C-0"');
    });
});
