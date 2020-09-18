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
        system(
            "echo; wget v4.webpack.js.org \
                --mirror \
                --trust-server-names \
                --accept-regex='v4.webpack.js.org' \
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

        $entries = $entries->union($this->moduleEntries($crawler, $file));
        $entries = $entries->union($this->pluginEntries($crawler, $file));

        return $entries;
    }

    protected function moduleEntries(HtmlPageCrawler $crawler, string $file)
    {
        $entries = collect();

        if (Str::contains($file, "{$this->url()}/loaders/index.html")) {
            $crawler->filter('a[class=sidebar-item__title]')->each(function (HtmlPageCrawler $node) use ($entries) {
                $entries->push([
                   'name' => $node->text(),
                   'type' => 'Module',
                   'path' => $this->url() . '/loaders/' . $node->attr('href')
                ]);
            });

            return $entries;
        }
    }

    protected function pluginEntries(HtmlPageCrawler $crawler, string $file)
    {
        $entries = collect();

        if (Str::contains($file, "{$this->url()}/plugins/index.html")) {
            $crawler->filter('a[class=sidebar-item__title]')->each(function (HtmlPageCrawler $node) use ($entries) {
                $entries->push([
                   'name' => $node->text(),
                   'type' => 'Plugin',
                   'path' => $this->url() . '/plugins/' . $node->attr('href')
                ]);
            });

            return $entries;
        }
    }

    public function format(string $file): string
    {
        $crawler = HtmlPageCrawler::create(Storage::get($file));

        $this->removeBreakingJavaScript($crawler);

        $this->insertDashTableOfContents($crawler);

        return $crawler->saveHTML();
    }

    protected function removeBreakingJavaScript(HtmlPageCrawler $crawler)
    {
        $crawler->filter('script')->remove();
    }

    protected function insertDashTableOfContents(HtmlPageCrawler $crawler)
    {
        $crawler->filter('body')
            ->before('<a name="//apple_ref/cpp/Section/Top" class="dashAnchor"></a>');

        $crawler->filter('h2, h3')->each(function (HtmlPageCrawler $node) {
            $node->prepend(
                '<a id="' . Str::slug($node->text()) . '" name="//apple_ref/cpp/Section/' . rawurlencode($node->text()) . '" class="dashAnchor"></a>'
            );
        });
    }
}
