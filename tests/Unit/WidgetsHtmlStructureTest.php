<?php

namespace Tests\Unit;

use Tests\TestCase;

class WidgetsHtmlStructureTest extends TestCase
{
    /**
     * Ensure each widget Blade file has balanced <div> tags.
     * This catches the most common breakage where a widget swallows following content.
     */
    public function test_div_tags_are_balanced_in_each_widget()
    {
        $widgetsPath = base_path('resources/views/widgets');
        $files = glob($widgetsPath . '/*.blade.php');

        $this->assertNotEmpty($files, 'Fant ingen widget-filer å teste.');

        foreach ($files as $file) {
            // Skip backup files
            if (preg_match('/\.(bak|backup)\.blade\.php$/i', $file)) {
                continue;
            }

            $contents = file_get_contents($file);

            // Strip Blade comments {{-- --}} and HTML comments <!-- --> to avoid false positives
            $contents = preg_replace('/\{\{\-\-.*?\-\-\}\}/s', '', $contents);
            $contents = preg_replace('/<!--.*?-->/s', '', $contents);

            // Count <div and </div>
            preg_match_all('/<\s*div(?=[\s>])/i', $contents, $openMatches);
            preg_match_all('/<\s*\/\s*div\s*>/i', $contents, $closeMatches);

            $openCount = count($openMatches[0] ?? []);
            $closeCount = count($closeMatches[0] ?? []);

            $this->assertSame(
                $openCount,
                $closeCount,
                sprintf(
                    "Ubalanserte <div>-tagger i %s. Åpne: %d, Lukk: %d",
                    basename($file),
                    $openCount,
                    $closeCount
                )
            );
        }
    }

    /**
     * Guardrail for Quicklinks widget to prevent accidental destructive edits.
     */
    public function test_quicklinks_widget_core_markers_present()
    {
        $file = base_path('resources/views/widgets/tools-quicklinks.blade.php');
        $this->assertFileExists($file, 'Fant ikke Quicklinks-widgeten.');

        $contents = file_get_contents($file);

        $this->assertStringContainsString('toolsQuicklinks()', $contents, 'Mangler Alpine-init (toolsQuicklinks()).');
        $this->assertStringContainsString('Hurtiglenker', $contents, 'Mangler tittel "Hurtiglenker".');
        $this->assertStringContainsString('widget-body', $contents, 'Mangler widget-body container.');
        $this->assertStringContainsString('<ul', $contents, 'Mangler <ul> liste for lenker.');
        $this->assertStringContainsString('x-for="link in links"', $contents, 'Mangler x-for iterasjon over lenker.');
    }
}
