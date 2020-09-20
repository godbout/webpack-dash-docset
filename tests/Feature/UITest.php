<?php

namespace Tests\Feature;

use App\Docsets\Webpack;
use Godbout\DashDocsetBuilder\Services\DocsetBuilder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Wa72\HtmlPageDom\HtmlPageCrawler;

class UITest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->docset = new Webpack();
        $this->builder = new DocsetBuilder($this->docset);

        if (! Storage::exists($this->docset->downloadedDirectory())) {
            fwrite(STDOUT, PHP_EOL . PHP_EOL . "\e[1;33mGrabbing webpack..." . PHP_EOL);
            Artisan::call('grab webpack');
        }

        if (! Storage::exists($this->docset->file())) {
            fwrite(STDOUT, PHP_EOL . PHP_EOL . "\e[1;33mPackaging webpack..." . PHP_EOL);
            Artisan::call('package webpack');
        }
    }

    /** @test */
    public function the_whole_site_header_gets_removed_from_the_dash_docset_files()
    {
        $wholeSiteHeader = 'class="site__header"';

        $this->assertStringContainsString(
            $wholeSiteHeader,
            Storage::get($this->docset->downloadedIndex())
        );

        $this->assertStringNotContainsString(
            $wholeSiteHeader,
            Storage::get($this->docset->innerIndex())
        );
    }

    /** @test */
    public function the_content_top_margin_is_set_to_zero_in_the_dash_docset_files()
    {
        $crawler = HtmlPageCrawler::create(
            Storage::get($this->docset->innerIndex())
        );

        $this->assertStringContainsString(
            '0',
            $crawler->filter('.site__content')->getStyle('margin-top')
        );
    }

    /** @test */
    public function the_left_sidebar_gets_removed_from_the_dash_docset_files()
    {
        $leftSidebar = 'site__sidebar';

        $this->assertStringContainsString(
            $leftSidebar,
            Storage::get($this->docset->downloadedIndex())
        );

        $this->assertStringNotContainsString(
            $leftSidebar,
            Storage::get($this->docset->innerIndex())
        );
    }

    /** @test */
    public function the_edit_and_print_document_links_gets_removed_from_the_dash_docset_files()
    {
        $editAndPrintLinks = 'class="page-links"';

        $this->assertStringContainsString(
            $editAndPrintLinks,
            Storage::get($this->docset->downloadedIndex())
        );

        $this->assertStringNotContainsString(
            $editAndPrintLinks,
            Storage::get($this->docset->innerIndex())
        );
    }

    /** @test */
    public function the_footer_gets_removed_from_the_dash_docset_files()
    {
        $footer = 'class="footer"';

        $this->assertStringContainsString(
            $footer,
            Storage::get($this->docset->downloadedIndex())
        );

        $this->assertStringNotContainsString(
            $footer,
            Storage::get($this->docset->innerIndex())
        );
    }

    /** @test */
    public function the_gitter_button_gets_removed_from_the_dash_docset_files()
    {
        $gitterButton = 'class="gitter"';

        $this->assertStringContainsString(
            $gitterButton,
            Storage::get($this->docset->downloadedIndex())
        );

        $this->assertStringNotContainsString(
            $gitterButton,
            Storage::get($this->docset->innerIndex())
        );
    }
}
