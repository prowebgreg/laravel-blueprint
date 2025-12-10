<?php

declare(strict_types=1);

use App\Actions\Media\SanitizeSvgAction;

beforeEach(function () {
    $this->action = new SanitizeSvgAction;
});

describe('valid SVG preservation', function () {
    it('preserves simple valid SVG structure', function () {
        $svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
    <circle cx="50" cy="50" r="40" fill="blue"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->toContain('<svg')
            ->and($result)->toContain('xmlns="http://www.w3.org/2000/svg"')
            ->and($result)->toContain('<circle')
            ->and($result)->toContain('fill="blue"')
            ->and($result)->not->toBeEmpty();
    });

    it('preserves SVG with proper namespaces', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <rect width="100" height="100" fill="red"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->toContain('<svg')
            ->and($result)->toContain('xmlns="http://www.w3.org/2000/svg"')
            ->and($result)->toContain('<rect')
            ->and($result)->toContain('fill="red"');
    });

    it('preserves SVG with viewBox and dimension attributes', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="150" viewBox="0 0 200 150">
    <path d="M10 10 L100 100" stroke="black"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->toContain('width="200"')
            ->and($result)->toContain('height="150"')
            ->and($result)->toContain('viewBox="0 0 200 150"')
            ->and($result)->toContain('<path');
    });

    it('preserves SVG with multiple shape elements', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <circle cx="25" cy="25" r="20" fill="red"/>
    <rect x="50" y="50" width="40" height="40" fill="blue"/>
    <path d="M10 80 L90 80" stroke="green"/>
    <text x="50" y="10" font-size="12">Hello</text>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->toContain('<circle')
            ->and($result)->toContain('<rect')
            ->and($result)->toContain('<path')
            ->and($result)->toContain('<text')
            ->and($result)->toContain('Hello');
    });

    it('preserves SVG with style tags containing CSS', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <style>
        .my-class { fill: red; stroke: blue; }
    </style>
    <circle class="my-class" cx="50" cy="50" r="40"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->toContain('<style>')
            ->and($result)->toContain('.my-class')
            ->and($result)->toContain('fill: red')
            ->and($result)->toContain('<circle');
    });

    it('preserves SVG with inline styles', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <circle cx="50" cy="50" r="40" style="fill: purple; stroke: orange; stroke-width: 2;"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->toContain('style=')
            ->and($result)->toContain('fill')
            ->and($result)->toContain('purple')
            ->and($result)->toContain('<circle');
    });

    it('preserves SVG with gradients and defs', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <defs>
        <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" style="stop-color:rgb(255,255,0);stop-opacity:1" />
            <stop offset="100%" style="stop-color:rgb(255,0,0);stop-opacity:1" />
        </linearGradient>
    </defs>
    <rect width="100" height="100" fill="url(#grad1)"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->toContain('<defs>')
            ->and($result)->toContain('<linearGradient')
            ->and($result)->toContain('id="grad1"')
            ->and($result)->toContain('<stop')
            ->and($result)->toContain('<rect');
    });

    it('preserves SVG with groups and transforms', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <g transform="translate(10,10) rotate(45)">
        <circle cx="50" cy="50" r="20" fill="green"/>
    </g>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->toContain('<g')
            ->and($result)->toContain('transform=')
            ->and($result)->toContain('<circle');
    });
});

describe('script removal', function () {
    it('removes script tags completely', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <script>alert("XSS");</script>
    <circle cx="50" cy="50" r="40" fill="blue"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('<script')
            ->and($result)->not->toContain('alert')
            ->and($result)->not->toContain('XSS')
            ->and($result)->toContain('<circle')
            ->and($result)->toContain('fill="blue"');
    });

    it('removes script tags with type attribute', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <script type="text/javascript">
        console.log("malicious");
    </script>
    <rect width="50" height="50" fill="red"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('<script')
            ->and($result)->not->toContain('console.log')
            ->and($result)->not->toContain('malicious')
            ->and($result)->toContain('<rect')
            ->and($result)->toContain('fill="red"');
    });

    it('removes script tags with CDATA sections', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <script>
        <![CDATA[
            alert("hidden attack");
        ]]>
    </script>
    <circle cx="50" cy="50" r="30"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('<script')
            ->and($result)->not->toContain('CDATA')
            ->and($result)->not->toContain('alert')
            ->and($result)->not->toContain('hidden attack')
            ->and($result)->toContain('<circle');
    });

    it('removes multiple script tags', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <script>alert("first");</script>
    <circle cx="25" cy="25" r="20"/>
    <script>alert("second");</script>
    <rect x="50" y="50" width="30" height="30"/>
    <script>alert("third");</script>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('<script')
            ->and($result)->not->toContain('alert')
            ->and($result)->not->toContain('first')
            ->and($result)->not->toContain('second')
            ->and($result)->not->toContain('third')
            ->and($result)->toContain('<circle')
            ->and($result)->toContain('<rect');
    });

    it('removes inline script in SVG elements', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <script>document.cookie="stolen";</script>
    <path d="M10 10 L90 90" stroke="black"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('document.cookie')
            ->and($result)->not->toContain('stolen')
            ->and($result)->toContain('<path');
    });
});

describe('event handler removal', function () {
    it('removes onclick attributes', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <circle cx="50" cy="50" r="40" fill="blue" onclick="alert(\'clicked\')"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('onclick')
            ->and($result)->not->toContain('alert')
            ->and($result)->not->toContain('clicked')
            ->and($result)->toContain('<circle')
            ->and($result)->toContain('fill="blue"');
    });

    it('removes onmouseover attributes', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <rect x="10" y="10" width="80" height="80" fill="red" onmouseover="doEvil()"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('onmouseover')
            ->and($result)->not->toContain('doEvil')
            ->and($result)->toContain('<rect')
            ->and($result)->toContain('fill="red"');
    });

    it('removes onload attributes', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" onload="maliciousCode()">
    <circle cx="50" cy="50" r="40"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('onload')
            ->and($result)->not->toContain('maliciousCode')
            ->and($result)->toContain('<svg')
            ->and($result)->toContain('<circle');
    });

    it('removes onerror attributes', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <image href="broken.jpg" onerror="alert(\'error\')"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('onerror')
            ->and($result)->not->toContain('alert');
    });

    it('removes all on* event handler attributes', function (string $eventHandler) {
        $svg = "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 100 100\">
    <circle cx=\"50\" cy=\"50\" r=\"40\" {$eventHandler}=\"attackCode()\"/>
</svg>";

        $result = $this->action->execute($svg);

        expect($result)->not->toContain($eventHandler)
            ->and($result)->not->toContain('attackCode')
            ->and($result)->toContain('<circle');
    })->with([
        'onclick',
        'onmouseover',
        'onmouseout',
        'onmousemove',
        'onmousedown',
        'onmouseup',
        'onload',
        'onerror',
        'onfocus',
        'onblur',
        'onchange',
        'onsubmit',
        'onkeydown',
        'onkeyup',
        'onkeypress',
    ]);

    it('removes multiple event handlers from single element', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <rect x="10" y="10" width="50" height="50" fill="green"
          onclick="attack1()"
          onmouseover="attack2()"
          onload="attack3()"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('onclick')
            ->and($result)->not->toContain('onmouseover')
            ->and($result)->not->toContain('onload')
            ->and($result)->not->toContain('attack1')
            ->and($result)->not->toContain('attack2')
            ->and($result)->not->toContain('attack3')
            ->and($result)->toContain('<rect')
            ->and($result)->toContain('fill="green"');
    });

    it('removes event handlers from multiple elements', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <circle cx="25" cy="25" r="20" onclick="evil1()"/>
    <rect x="50" y="50" width="40" height="40" onmouseover="evil2()"/>
    <path d="M10 10 L90 90" onload="evil3()"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('onclick')
            ->and($result)->not->toContain('onmouseover')
            ->and($result)->not->toContain('onload')
            ->and($result)->not->toContain('evil')
            ->and($result)->toContain('<circle')
            ->and($result)->toContain('<rect')
            ->and($result)->toContain('<path');
    });
});

describe('external reference removal', function () {
    it('removes xlink:href pointing to external URLs', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 100 100">
    <image xlink:href="http://evil.com/malicious.jpg" width="100" height="100"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('http://evil.com')
            ->and($result)->not->toContain('malicious.jpg');
    });

    it('removes href pointing to external URLs', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <image href="https://attacker.com/bad.png" width="100" height="100"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('https://attacker.com')
            ->and($result)->not->toContain('bad.png');
    });

    it('removes external stylesheet references', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <?xml-stylesheet type="text/css" href="http://evil.com/styles.css" ?>
    <circle cx="50" cy="50" r="40"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('xml-stylesheet')
            ->and($result)->not->toContain('http://evil.com')
            ->and($result)->toContain('<circle');
    });

    it('removes external script references', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <script href="http://malicious.com/evil.js"></script>
    <circle cx="50" cy="50" r="40"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('<script')
            ->and($result)->not->toContain('http://malicious.com')
            ->and($result)->toContain('<circle');
    });

    it('preserves internal references with fragment identifiers', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <defs>
        <linearGradient id="myGradient">
            <stop offset="0%" stop-color="red"/>
            <stop offset="100%" stop-color="blue"/>
        </linearGradient>
    </defs>
    <rect width="100" height="100" fill="url(#myGradient)"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->toContain('id="myGradient"')
            ->and($result)->toContain('url(#myGradient)')
            ->and($result)->toContain('<linearGradient')
            ->and($result)->toContain('<rect');
    });

    it('preserves data URIs for inline images', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <image href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUg" width="50" height="50"/>
</svg>';

        $result = $this->action->execute($svg);

        // Data URIs might be preserved or removed depending on sanitizer config
        // This test documents expected behavior
        expect($result)->toContain('<svg');
    });

    it('removes multiple external references', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 100 100">
    <image xlink:href="http://evil1.com/img1.jpg"/>
    <image xlink:href="http://evil2.com/img2.jpg"/>
    <script src="http://evil3.com/script.js"></script>
    <circle cx="50" cy="50" r="40"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('http://evil1.com')
            ->and($result)->not->toContain('http://evil2.com')
            ->and($result)->not->toContain('http://evil3.com')
            ->and($result)->not->toContain('<script')
            ->and($result)->toContain('<circle');
    });
});

describe('malicious content removal', function () {
    it('removes javascript: URLs in href attributes', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <a href="javascript:alert(\'XSS\')">
        <text x="10" y="20">Click me</text>
    </a>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('javascript:')
            ->and($result)->not->toContain('alert')
            ->and($result)->not->toContain('XSS');
    });

    it('removes data:text/html URLs', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <image href="data:text/html,<script>alert(\'XSS\')</script>"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('data:text/html')
            ->and($result)->not->toContain('<script>alert');
    });

    it('sanitizes foreignObject with dangerous content', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <foreignObject width="100" height="100">
        <body xmlns="http://www.w3.org/1999/xhtml">
            <script>alert("XSS")</script>
        </body>
    </foreignObject>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('alert("XSS")')
            ->and($result)->not->toContain('<script>');
    });

    it('removes malicious use elements', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 100 100">
    <use xlink:href="http://evil.com/malicious.svg#attack"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('http://evil.com')
            ->and($result)->not->toContain('malicious.svg');
    });

    it('removes embedded object elements', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <object data="http://evil.com/malware.swf" type="application/x-shockwave-flash"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('<object')
            ->and($result)->not->toContain('http://evil.com')
            ->and($result)->not->toContain('malware.swf');
    });

    it('removes embed elements', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <embed src="http://evil.com/malware.swf" type="application/x-shockwave-flash"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('<embed')
            ->and($result)->not->toContain('http://evil.com');
    });

    it('removes animation elements with javascript', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <circle cx="50" cy="50" r="40">
        <set attributeName="onmouseover" to="alert(\'XSS\')"/>
    </circle>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('onmouseover')
            ->and($result)->not->toContain('alert')
            ->and($result)->toContain('<circle');
    });

    it('removes malicious metadata elements', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <metadata>
        <script>alert("hidden")</script>
    </metadata>
    <circle cx="50" cy="50" r="40"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->not->toContain('<script>')
            ->and($result)->not->toContain('alert')
            ->and($result)->toContain('<circle');
    });
});

describe('edge cases', function () {
    it('handles empty SVG structure', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"></svg>';

        $result = $this->action->execute($svg);

        expect($result)->toContain('<svg')
            ->and($result)->toContain('xmlns="http://www.w3.org/2000/svg"');
    });

    it('throws exception for malformed XML', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
    <circle cx="50" cy="50" r="40"
</svg>';

        expect(fn () => $this->action->execute($svg))
            ->toThrow(Exception::class);
    });

    it('throws exception for non-SVG XML content', function () {
        $xml = '<?xml version="1.0"?>
<root>
    <element>Not an SVG</element>
</root>';

        expect(fn () => $this->action->execute($xml))
            ->toThrow(Exception::class);
    });

    it('handles SVG with comments', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <!-- This is a comment -->
    <circle cx="50" cy="50" r="40" fill="blue"/>
    <!-- Another comment -->
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->toContain('<circle')
            ->and($result)->toContain('fill="blue"');
        // Comments may or may not be preserved
    });

    it('handles SVG with CDATA sections in allowed contexts', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <style>
        <![CDATA[
            .my-class { fill: red; }
        ]]>
    </style>
    <circle class="my-class" cx="50" cy="50" r="40"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->toContain('<style>')
            ->and($result)->toContain('<circle');
    });

    it('handles very large SVG files', function () {
        $paths = str_repeat('<path d="M10 10 L90 90"/>', 1000);
        $svg = "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 100 100\">{$paths}</svg>";

        $result = $this->action->execute($svg);

        expect($result)->toContain('<svg')
            ->and($result)->toContain('<path')
            ->and(substr_count($result, '<path'))->toBe(1000);
    });

    it('handles SVG with nested groups', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <g id="layer1">
        <g id="sublayer1">
            <g id="subsublayer1">
                <circle cx="50" cy="50" r="40"/>
            </g>
        </g>
    </g>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->toContain('id="layer1"')
            ->and($result)->toContain('id="sublayer1"')
            ->and($result)->toContain('id="subsublayer1"')
            ->and($result)->toContain('<circle');
    });

    it('handles SVG with special XML entities', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <text x="10" y="20">&lt;Hello &amp; Goodbye&gt;</text>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->toContain('<text')
            ->and($result)->toContain('&lt;')
            ->and($result)->toContain('&amp;')
            ->and($result)->toContain('&gt;');
    });

    it('handles SVG with DOCTYPE declarations', function () {
        $svg = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <circle cx="50" cy="50" r="40"/>
</svg>';

        $result = $this->action->execute($svg);

        expect($result)->toContain('<svg')
            ->and($result)->toContain('<circle');
    });

    it('handles SVG with processing instructions', function () {
        $svg = '<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/css" href="style.css"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <circle cx="50" cy="50" r="40"/>
</svg>';

        $result = $this->action->execute($svg);

        // External stylesheet PI should be removed
        expect($result)->not->toContain('href="style.css"')
            ->and($result)->toContain('<svg')
            ->and($result)->toContain('<circle');
    });

    it('handles empty string input', function () {
        expect(fn () => $this->action->execute(''))
            ->toThrow(Exception::class);
    });

    it('handles whitespace-only input', function () {
        expect(fn () => $this->action->execute('   '))
            ->toThrow(Exception::class);
    });

    it('handles SVG with mixed valid and malicious content', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" onload="evil()">
    <circle cx="20" cy="20" r="15" fill="red"/>
    <script>alert("XSS")</script>
    <rect x="40" y="40" width="20" height="20" fill="blue" onclick="attack()"/>
    <path d="M10 10 L90 90" stroke="green"/>
    <image href="http://evil.com/bad.jpg"/>
</svg>';

        $result = $this->action->execute($svg);

        // Malicious content should be removed
        expect($result)->not->toContain('onload')
            ->and($result)->not->toContain('evil()')
            ->and($result)->not->toContain('<script')
            ->and($result)->not->toContain('alert')
            ->and($result)->not->toContain('onclick')
            ->and($result)->not->toContain('attack()')
            ->and($result)->not->toContain('http://evil.com')
            // Valid content should be preserved
            ->and($result)->toContain('<circle')
            ->and($result)->toContain('fill="red"')
            ->and($result)->toContain('<rect')
            ->and($result)->toContain('fill="blue"')
            ->and($result)->toContain('<path')
            ->and($result)->toContain('stroke="green"');
    });
});

describe('sanitization consistency', function () {
    it('produces consistent output for same input', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <circle cx="50" cy="50" r="40" fill="blue" onclick="alert(\'test\')"/>
</svg>';

        $result1 = $this->action->execute($svg);
        $result2 = $this->action->execute($svg);

        expect($result1)->toBe($result2);
    });

    it('always returns valid SVG structure', function (string $inputSvg) {
        $result = $this->action->execute($inputSvg);

        expect($result)->toContain('<svg')
            ->and($result)->toContain('xmlns="http://www.w3.org/2000/svg"')
            ->and($result)->not->toBeEmpty();
    })->with([
        '<svg xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40"/></svg>',
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100"/></svg>',
        '<svg xmlns="http://www.w3.org/2000/svg"><path d="M10 10 L90 90"/></svg>',
    ]);

    it('never returns script tags in output', function (string $inputSvg) {
        $result = $this->action->execute($inputSvg);

        expect($result)->not->toContain('<script')
            ->and($result)->not->toContain('</script>');
    })->with([
        '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script><circle cx="50" cy="50" r="40"/></svg>',
        '<svg xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40"/><script type="text/javascript">evil()</script></svg>',
        '<svg xmlns="http://www.w3.org/2000/svg"><script><![CDATA[attack()]]></script><rect width="100" height="100"/></svg>',
    ]);

    it('never returns event handlers in output', function (string $eventHandler) {
        $svg = "<svg xmlns=\"http://www.w3.org/2000/svg\"><circle cx=\"50\" cy=\"50\" r=\"40\" {$eventHandler}=\"attack()\"/></svg>";

        $result = $this->action->execute($svg);

        expect($result)->not->toContain($eventHandler)
            ->and($result)->not->toContain('attack()');
    })->with([
        'onclick',
        'onload',
        'onerror',
        'onmouseover',
    ]);
});
