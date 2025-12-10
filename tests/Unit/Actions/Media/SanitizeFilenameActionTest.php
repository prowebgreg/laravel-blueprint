<?php

declare(strict_types=1);

use App\Actions\Media\SanitizeFilenameAction;

beforeEach(function () {
    $this->action = new SanitizeFilenameAction;
});

describe('lowercase conversion', function () {
    it('converts uppercase letters to lowercase', function () {
        $result = $this->action->execute('MyFile.jpg');

        expect($result)->toStartWith('myfile-')
            ->and($result)->toEndWith('.jpg');
    });

    it('converts all uppercase filename to lowercase while preserving extension case', function () {
        $result = $this->action->execute('UPPERCASE.PNG');

        expect($result)->toStartWith('uppercase-')
            ->and($result)->toEndWith('.png');
    });

    it('converts mixed case filename to lowercase', function () {
        $result = $this->action->execute('MiXeD-CaSe-FiLe.webp');

        expect($result)->toStartWith('mixed-case-file-')
            ->and($result)->toEndWith('.webp');
    });
});

describe('space to hyphen conversion', function () {
    it('converts single spaces to hyphens', function () {
        $result = $this->action->execute('my file name.jpg');

        expect($result)->toStartWith('my-file-name-')
            ->and($result)->toEndWith('.jpg');
    });

    it('converts multiple consecutive spaces to single hyphen', function () {
        $result = $this->action->execute('multiple   spaces   here.png');

        expect($result)->toMatch('/^multiple-spaces-here-[a-z0-9]{8}\.png$/')
            ->and($result)->not->toContain('--');
    });

    it('handles leading and trailing spaces', function () {
        $result = $this->action->execute('   trimmed file.jpg   ');

        expect($result)->toStartWith('trimmed-file-')
            ->and($result)->toEndWith('.jpg')
            ->and($result)->not->toMatch('/^-/'); // Should not start with hyphen
    });

    it('converts tabs and newlines to hyphens', function () {
        $result = $this->action->execute("file\twith\ntabs.jpg");

        expect($result)->toMatch('/^file-with-tabs-[a-z0-9]{8}\.jpg$/');
    });
});

describe('special character removal', function () {
    it('removes common special characters', function (string $filename, string $expectedPrefix) {
        $result = $this->action->execute($filename);

        expect($result)->toStartWith($expectedPrefix.'-')
            ->and($result)->toMatch('/^[a-z0-9-]+\.[\w]+$/');
    })->with([
        'symbols' => ['file@#$%^&.jpg', 'file'],
        'parentheses' => ['file(1).jpg', 'file1'],
        'brackets' => ['file[copy].png', 'filecopy'],
        'braces' => ['file{temp}.webp', 'filetemp'],
        'punctuation' => ['file!?.jpg', 'file'],
        'quotes' => ["file'test\".png", 'filetest'],
    ]);

    it('handles unicode and accented characters', function () {
        $result = $this->action->execute('héllo-wörld.png');

        // Should handle accented chars - either remove or transliterate
        expect($result)->toMatch('/^[a-z0-9-]+\.png$/')
            ->and($result)->toEndWith('.png');
    });

    it('keeps hyphens and underscores', function () {
        $result = $this->action->execute('valid-file_name.jpg');

        expect($result)->toStartWith('valid-file_name-')
            ->and($result)->toContain('valid-file_name')
            ->and($result)->toEndWith('.jpg');
    });

    it('handles filenames with only special characters', function () {
        $result = $this->action->execute('@#$%.jpg');

        expect($result)->toMatch('/^[a-z0-9]{8}\.jpg$/')
            ->and($result)->toEndWith('.jpg');
    });

    it('handles ampersands and plus signs', function () {
        $result = $this->action->execute('file&name+test.png');

        expect($result)->toMatch('/^[a-z0-9-]+\.png$/')
            ->and($result)->toEndWith('.png');
    });
});

describe('nanoid suffix', function () {
    it('appends 8-character alphanumeric suffix', function () {
        $result = $this->action->execute('test-file.jpg');

        expect($result)->toMatch('/^test-file-[a-z0-9]{8}\.jpg$/')
            ->and($result)->toEndWith('.jpg');
    });

    it('generates unique suffix for each call', function () {
        $result1 = $this->action->execute('same-file.jpg');
        $result2 = $this->action->execute('same-file.jpg');
        $result3 = $this->action->execute('same-file.jpg');

        expect($result1)->not->toBe($result2)
            ->and($result2)->not->toBe($result3)
            ->and($result1)->not->toBe($result3);
    });

    it('generates exactly 8 characters in suffix', function () {
        $result = $this->action->execute('file.jpg');

        // Extract the nanoid part (between last hyphen and extension)
        preg_match('/-([a-z0-9]+)\.\w+$/', $result, $matches);

        expect($matches)->toHaveCount(2)
            ->and($matches[1])->toHaveLength(8);
    });

    it('uses only lowercase alphanumeric characters in suffix', function () {
        $results = [];
        for ($i = 0; $i < 20; $i++) {
            $results[] = $this->action->execute('test.jpg');
        }

        foreach ($results as $result) {
            preg_match('/-([a-z0-9]+)\.jpg$/', $result, $matches);
            expect($matches[1])->toMatch('/^[a-z0-9]{8}$/');
        }
    });

    it('adds suffix even to already clean filenames', function () {
        $result = $this->action->execute('clean-file.jpg');

        expect($result)->toMatch('/^clean-file-[a-z0-9]{8}\.jpg$/')
            ->and($result)->not->toBe('clean-file.jpg');
    });
});

describe('extension preservation', function () {
    it('preserves common image extensions', function (string $extension) {
        $result = $this->action->execute("test-file.{$extension}");

        expect($result)->toEndWith(".{$extension}")
            ->and($result)->toMatch("/^test-file-[a-z0-9]{8}\.{$extension}$/");
    })->with([
        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',
        'svg',
    ]);

    it('preserves video extensions', function (string $extension) {
        $result = $this->action->execute("video-file.{$extension}");

        expect($result)->toEndWith(".{$extension}")
            ->and($result)->toMatch("/^video-file-[a-z0-9]{8}\.{$extension}$/");
    })->with([
        'mp4',
        'webm',
        'mov',
        'avi',
    ]);

    it('lowercases uppercase extensions', function () {
        $result = $this->action->execute('file.PNG');

        expect($result)->toEndWith('.png');
    });

    it('lowercases mixed case extensions', function () {
        $result = $this->action->execute('file.WebP');

        expect($result)->toEndWith('.webp');
    });

    it('handles multiple extensions correctly', function () {
        $result = $this->action->execute('backup.tar.gz');

        // Should preserve the full extension or just the last part
        expect($result)->toMatch('/^backup-[a-z0-9]{8}\.(tar\.gz|gz)$/');
    });

    it('handles files without extension', function () {
        $result = $this->action->execute('filename');

        expect($result)->toMatch('/^filename-[a-z0-9]{8}$/')
            ->and($result)->not->toContain('.');
    });

    it('handles dotfiles with extension', function () {
        $result = $this->action->execute('.htaccess.bak');

        expect($result)->toEndWith('.bak')
            ->and($result)->toMatch('/^htaccess-[a-z0-9]{8}\.bak$/');
    });
});

describe('edge cases', function () {
    it('handles empty filename with only extension', function () {
        $result = $this->action->execute('.jpg');

        expect($result)->toMatch('/^[a-z0-9]{8}\.jpg$/')
            ->and($result)->toEndWith('.jpg');
    });

    it('handles very long filenames', function () {
        $longName = str_repeat('very-long-filename-', 20).'end.jpg';
        $result = $this->action->execute($longName);

        expect($result)->toEndWith('.jpg')
            ->and($result)->toMatch('/-[a-z0-9]{8}\.jpg$/')
            ->and($result)->toContain('very-long-filename');
    });

    it('handles single character filename', function () {
        $result = $this->action->execute('a.jpg');

        expect($result)->toMatch('/^a-[a-z0-9]{8}\.jpg$/');
    });

    it('handles filename with only numbers', function () {
        $result = $this->action->execute('12345.jpg');

        expect($result)->toMatch('/^12345-[a-z0-9]{8}\.jpg$/');
    });

    it('handles filename with consecutive hyphens', function () {
        $result = $this->action->execute('file---name.jpg');

        expect($result)->toMatch('/^file-name-[a-z0-9]{8}\.jpg$/')
            ->and($result)->not->toContain('---');
    });

    it('handles filename with consecutive underscores', function () {
        $result = $this->action->execute('file___name.jpg');

        expect($result)->toMatch('/^file___name-[a-z0-9]{8}\.jpg$/')
            ->and($result)->toContain('file___name');
    });

    it('handles very short extension', function () {
        $result = $this->action->execute('file.x');

        expect($result)->toMatch('/^file-[a-z0-9]{8}\.x$/');
    });

    it('handles very long extension', function () {
        $result = $this->action->execute('file.verylongextension');

        expect($result)->toMatch('/^file-[a-z0-9]{8}\.verylongextension$/');
    });

    it('sanitizes emoji characters', function () {
        $result = $this->action->execute('file😀emoji🎉.jpg');

        expect($result)->toMatch('/^[a-z0-9-]+\.jpg$/')
            ->and($result)->not->toContain('😀')
            ->and($result)->not->toContain('🎉')
            ->and($result)->toEndWith('.jpg');
    });

    it('handles path separators by removing them', function () {
        $result = $this->action->execute('path/to/file.jpg');

        expect($result)->not->toContain('/')
            ->and($result)->toMatch('/^[a-z0-9-]+\.jpg$/')
            ->and($result)->toEndWith('.jpg');
    });

    it('handles Windows path separators', function () {
        $result = $this->action->execute('path\\to\\file.jpg');

        expect($result)->not->toContain('\\')
            ->and($result)->toMatch('/^[a-z0-9-]+\.jpg$/')
            ->and($result)->toEndWith('.jpg');
    });
});

describe('format consistency', function () {
    it('always returns a valid filename format', function (string $input) {
        $result = $this->action->execute($input);

        // Valid format: lowercase-with-hyphens-NANOID8.extension (all lowercase)
        expect($result)->toMatch('/^[a-z0-9_-]*[a-z0-9]{8}(\.[a-z0-9]+)?$/');
    })->with([
        'simple.jpg',
        'UPPERCASE.PNG',
        'with spaces.webp',
        'special!@#chars.gif',
        'unicode-héllo.jpg',
        'numbers123.png',
        '___underscores___.jpg',
        '---hyphens---.png',
    ]);

    it('never returns an empty string', function () {
        $result = $this->action->execute('');

        expect($result)->not->toBeEmpty()
            ->and($result)->toMatch('/^[a-z0-9]{8}$/');
    });

    it('never returns filename with spaces', function (string $input) {
        $result = $this->action->execute($input);

        expect($result)->not->toContain(' ')
            ->and($result)->not->toContain("\t")
            ->and($result)->not->toContain("\n");
    })->with([
        'file with spaces.jpg',
        '   leading spaces.png',
        'trailing spaces   .webp',
        "tabs\tand\nnewlines.gif",
    ]);

    it('always includes nanoid suffix before extension', function (string $input) {
        $result = $this->action->execute($input);

        if (str_contains($result, '.')) {
            // Has extension: should match pattern-nanoid8.ext
            expect($result)->toMatch('/-[a-z0-9]{8}\.\w+$/');
        } else {
            // No extension: should end with nanoid8
            expect($result)->toMatch('/[a-z0-9]{8}$/');
        }
    })->with([
        'test.jpg',
        'test',
        'test.tar.gz',
        '.htaccess',
    ]);
});
