<?php

namespace Tests\Unit;

use App\Docsets\Webpack;
use Godbout\DashDocsetBuilder\Services\DocsetBuilder;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebpackTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->docset = new Webpack();
        $this->builder = new DocsetBuilder($this->docset);
    }

    /** @test */
    public function it_can_generate_a_table_of_contents()
    {
        $toc = $this->docset->entries(
            $this->docset->downloadedDirectory() . '/' . $this->docset->url() . '/plugins/index.html'
        );

        $this->assertNotEmpty($toc);
    }

    /** @test */
    public function it_can_format_the_documentation_files()
    {
        $scripts = '<script';

        $this->assertStringContainsString(
            $scripts,
            Storage::get($this->docset->downloadedIndex())
        );

        $this->assertStringNotContainsString(
            $scripts,
            $this->docset->format($this->docset->downloadedIndex())
        );
    }
}
