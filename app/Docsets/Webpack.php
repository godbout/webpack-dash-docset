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
        $entries = $entries->union($this->interfaceEntries($crawler, $file));
        $entries = $entries->union($this->optionEntries($crawler, $file));
        $entries = $entries->union($this->moduleEntries($crawler, $file));
        $entries = $entries->union($this->pluginEntries($crawler, $file));
        $entries = $entries->union($this->sectionEntries($crawler, $file));

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

        if (Str::contains($file, "{$this->url()}/api/index.html")) {
            $crawler->filter('a[class=sidebar-item__title]')->each(function (HtmlPageCrawler $node) use ($entries) {
                $entries->push([
                   'name' => $node->text(),
                   'type' => 'Guide',
                   'path' => $this->url() . '/api/' . $node->attr('href')
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
                   'path' => Str::after($file . '#' . Str::slug($node->text()), $this->innerDirectory())
                ]);
            });

            return $entries;
        }
    }

    protected function interfaceEntries(HtmlPageCrawler $crawler, string $file)
    {
        $entries = collect();

        if (Str::contains($file, "{$this->url()}/api")) {
            $crawler->filter('h2 > code, h3')->each(function (HtmlPageCrawler $node) use ($entries, $file) {
                $entries->push([
                   'name' => $node->text(),
                   'type' => 'Interface',
                   'path' => Str::after($file . '#' . Str::slug($node->text()), $this->innerDirectory())
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

    protected function sectionEntries(HtmlPageCrawler $crawler, string $file)
    {
        $entries = collect();

        $crawler->filter('h2 > a:first-child, h3 > a:first-child')->each(function (HtmlPageCrawler $node) use ($entries, $file) {
            $entries->push([
               'name' => $node->parents()->first()->text(),
               'type' => 'Section',
               'path' => Str::after($file . '#' . Str::slug($node->parents()->first()->text()), $this->innerDirectory())
            ]);
        });

        return $entries;
    }

    public function format(string $file): string
    {
        $crawler = HtmlPageCrawler::create(Storage::get($file));

        $this->removeWholeSiteHeader($crawler);
        $this->removeContentTopMargin($crawler);
        $this->removeLeftSidebar($crawler);
        $this->removeEditAndPrintDocumentLinks($crawler);
        $this->removeFooter($crawler);
        $this->removeGitterButton($crawler);

        $this->removeBreakingJavaScript($crawler);

        $this->insertOnlineRedirection($crawler, $file);
        $this->insertDashTableOfContents($crawler, $file);

        return $crawler->saveHTML();
    }

    protected function removeWholeSiteHeader(HtmlPageCrawler $crawler)
    {
        $crawler->filter('.site__header')->remove();
    }

    protected function removeContentTopMargin(HtmlPageCrawler $crawler)
    {
        $crawler->filter('.site__content')->setStyle('margin-top', '0');
    }

    protected function removeLeftSidebar(HtmlPageCrawler $crawler)
    {
        $crawler->filter('nav.site__sidebar')->remove();
    }

    protected function removeEditAndPrintDocumentLinks(HtmlPageCrawler $crawler)
    {
        $crawler->filter('.page-links')->remove();
    }

    protected function removeFooter(HtmlPageCrawler $crawler)
    {
        $crawler->filter('footer.footer')->remove();
    }

    protected function removeGitterButton(HtmlPageCrawler $crawler)
    {
        $crawler->filter('.gitter')->remove();
    }

    protected function removeBreakingJavaScript(HtmlPageCrawler $crawler)
    {
        $crawler->filter('script')->remove();
    }

    protected function insertOnlineRedirection(HtmlPageCrawler $crawler, string $file)
    {
        $onlineUrl = Str::substr(Str::after($file, $this->innerDirectory()), 1, -10);

        $crawler->filter('html')->prepend("<!-- Online page at $onlineUrl -->");
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

        if (Str::contains($file, $this->url() . '/api')) {
            $crawler->filter('h2 > code, h3')->each(function (HtmlPageCrawler $node) {
                $node->prepend(
                    '<a id="' . Str::slug($node->text()) . '" name="//apple_ref/cpp/Interface/' . rawurlencode($node->text()) . '" class="dashAnchor"></a>'
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
