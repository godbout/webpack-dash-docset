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

        $entries = $entries->union($this->guideEntries($crawler, $file));
        $entries = $entries->union($this->optionEntries($crawler, $file));
        $entries = $entries->union($this->moduleEntries($crawler, $file));
        $entries = $entries->union($this->pluginEntries($crawler, $file));

        return $entries;
    }

    protected function guideEntries(HtmlPageCrawler $crawler, string $file)
    {
        $entries = collect();

        if (Str::contains($file, "{$this->url()}/guides/index.html")) {
            $crawler->filter('a[class=sidebar-item__title]')->each(function (HtmlPageCrawler $node) use ($entries) {
                $entries->push([
                   'name' => $node->text(),
                   'type' => 'Guide',
                   'path' => $this->url() . '/guides/' . $node->attr('href')
                ]);
            });

            return $entries;
        }

        if (Str::contains($file, "{$this->url()}/concepts/index.html")) {
            $crawler->filter('a[class=sidebar-item__title]')->each(function (HtmlPageCrawler $node) use ($entries) {
                $entries->push([
                   'name' => $node->text(),
                   'type' => 'Guide',
                   'path' => $this->url() . '/concepts/' . $node->attr('href')
                ]);
            });

            return $entries;
        }

        if (Str::contains($file, "{$this->url()}/configuration/index.html")) {
            $crawler->filter('a[class=sidebar-item__title]')->each(function (HtmlPageCrawler $node) use ($entries) {
                $entries->push([
                   'name' => $node->text(),
                   'type' => 'Guide',
                   'path' => $this->url() . '/configuration/' . $node->attr('href')
                ]);
            });

            return $entries;
        }

        if (Str::contains($file, "{$this->url()}/migrate/index.html")) {
            $crawler->filter('a[class=sidebar-item__title]')->each(function (HtmlPageCrawler $node) use ($entries) {
                $entries->push([
                   'name' => $node->text(),
                   'type' => 'Guide',
                   'path' => $this->url() . '/migrate/' . $node->attr('href')
                ]);
            });

            return $entries;
        }
    }

    protected function optionEntries(HtmlPageCrawler $crawler, string $file)
    {
        $entries = collect();

        if (Str::contains($file, "{$this->url()}/configuration")) {
            $crawler->filter('h2 > code, h3 > code')->each(function (HtmlPageCrawler $node) use ($entries, $file) {
                $entries->push([
                   'name' => $node->text(),
                   'type' => 'Option',
                   'path' =>  Str::after($file . '#' . Str::slug($node->text()), $this->innerDirectory())
                ]);
            });

            return $entries;
        }
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

        $this->insertDashTableOfContents($crawler, $file);

        return $crawler->saveHTML();
    }

    protected function removeBreakingJavaScript(HtmlPageCrawler $crawler)
    {
        $crawler->filter('script')->remove();
    }

    protected function insertDashTableOfContents(HtmlPageCrawler $crawler, $file)
    {
        $crawler->filter('body')
            ->before('<a name="//apple_ref/cpp/Section/Top" class="dashAnchor"></a>');

        if (Str::contains($file, $this->url() . '/configuration')) {
            $crawler->filter('h2 > code, h3 > code')->each(function (HtmlPageCrawler $node) {
                $node->prepend(
                    '<a id="' . Str::slug($node->text()) . '" name="//apple_ref/cpp/Option/' . rawurlencode($node->text()) . '" class="dashAnchor"></a>'
                );
            });

            $crawler->filter('h2 > a:first-child, h3 > a:first-child')->each(function (HtmlPageCrawler $node) {
                $node->prepend(
                    '<a id="' . Str::slug($node->parents()->first()->text()) . '" name="//apple_ref/cpp/Section/' . rawurlencode($node->parents()->first()->text()) . '" class="dashAnchor"></a>'
                );
            });

            return;
        }

        if (Str::contains($file, $this->url() . '/migrate')) {
            $crawler->filter('h2 > code, h3 > code')->each(function (HtmlPageCrawler $node) {
                $node->prepend(
                    '<a id="' . Str::slug($node->text()) . '" name="//apple_ref/cpp/Option/' . rawurlencode($node->text()) . '" class="dashAnchor"></a>'
                );
            });

            $crawler->filter('h2 > a:first-child, h3 > a:first-child')->each(function (HtmlPageCrawler $node) {
                $node->prepend(
                    '<a id="' . Str::slug($node->parents()->first()->text()) . '" name="//apple_ref/cpp/Section/' . rawurlencode($node->parents()->first()->text()) . '" class="dashAnchor"></a>'
                );
            });

            return;
        }

        $crawler->filter('h2, h3')->each(function (HtmlPageCrawler $node) {
            $node->prepend(
                '<a id="' . Str::slug($node->text()) . '" name="//apple_ref/cpp/Section/' . rawurlencode($node->text()) . '" class="dashAnchor"></a>'
            );
        });
    }
}
