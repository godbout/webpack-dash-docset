<?php

namespace App\Docsets;

use Godbout\DashDocsetBuilder\Docsets\BaseDocset;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Wa72\HtmlPageDom\HtmlPageCrawler;

class Webpack extends BaseDocset
{
    public const CODE = 'webpack';
    public const NAME = 'webpack';
    public const URL = 'v4.webpack.js.org';
    public const INDEX = 'concepts/index.html';
    public const PLAYGROUND = '';
    public const ICON_16 = '../../icons/icon.png';
    public const ICON_32 = '../../icons/icon@2x.png';
    public const EXTERNAL_DOMAINS = [];


    public function grab(): bool
    {
        $toGet = implode('|', [
            'v4.webpack.js.org',
        ]);

        system(
            "echo; wget v4.webpack.js.org \
                --mirror \
                --trust-server-names \
                --accept-regex='{$toGet}' \
                --page-requisites \
                --adjust-extension \
                --convert-links \
                --span-hosts \
                --domains={$this->externalDomains()} \
                --directory-prefix=storage/{$this->downloadedDirectory()} \
                -e robots=off \
                --quiet \
                --show-progress",
            $result
        );

        return $result === 0;
    }

    public function entries(string $file): Collection
    {
        $crawler = HtmlPageCrawler::create(Storage::get($file));

        $entries = collect();

        $crawler->filter('h1')->each(function (HtmlPageCrawler $node) use ($entries, $file) {
            $entries->push([
                'name' => $node->text(),
                'type' => 'Guide',
                'path' => Str::after($file . '#' . Str::slug($node->text()), $this->innerDirectory()),
            ]);
        });

        return $entries;
    }

    public function format(string $file): string
    {
        $crawler = HtmlPageCrawler::create(Storage::get($file));

        $this->removeBreakingJavaScript($crawler);

        return $crawler->saveHTML();
    }

    protected function removeBreakingJavaScript(HtmlPageCrawler $crawler)
    {
        $crawler->filter('script')->remove();
    }
}
