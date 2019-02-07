<?php

namespace NyroDev\UtilityBundle\Services;

use Psr\Container\ContainerInterface;
use Symfony\Component\Asset\Packages;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;

class TagRendererService extends AbstractService
{
    private $entrypointLookupCollection;

    private $packages;

    public function __construct(ContainerInterface $container, EntrypointLookupCollection $entrypointLookupCollection, Packages $packages)
    {
        parent::__construct($container);
        $this->entrypointLookupCollection = $entrypointLookupCollection;
        $this->packages = $packages;
    }

    public function reset(string $entrypointName = '_default')
    {
        $this->getEntrypointLookup($entrypointName)->reset();
    }

    public function renderWebpackScriptTags(string $entryName, string $moreAttrs = null, string $packageName = null, string $entrypointName = '_default'): string
    {
        $scriptTags = [];
        foreach ($this->getScriptFiles($entryName, $packageName, $entrypointName) as $filename) {
            $scriptTags[] = sprintf(
                '<script src="%s"'.($moreAttrs ? ' '.$moreAttrs : null).'></script>',
                $filename
            );
        }

        return implode('', $scriptTags);
    }

    public function getScriptFiles(string $entryName, string $packageName = null, string $entrypointName = '_default'): array
    {
        $scriptTags = [];
        foreach ($this->getEntrypointLookup($entrypointName)->getJavaScriptFiles($entryName) as $filename) {
            $scriptTags[] = htmlentities($this->getAssetPath($filename, $packageName));
        }

        return $scriptTags;
    }

    public function renderWebpackLinkTags(string $entryName, string $moreAttrs = null, string $packageName = null, string $entrypointName = '_default'): string
    {
        $scriptTags = [];
        foreach ($this->getLinkFiles($entryName, $packageName, $entrypointName) as $filename) {
            $scriptTags[] = sprintf(
                '<link rel="stylesheet" href="%s"'.($moreAttrs ? ' '.$moreAttrs : null).' />',
                $filename
            );
        }

        return implode('', $scriptTags);
    }

    public function getLinkFiles(string $entryName, string $packageName = null, string $entrypointName = '_default'): array
    {
        $scriptTags = [];
        foreach ($this->getEntrypointLookup($entrypointName)->getCssFiles($entryName) as $filename) {
            $scriptTags[] = htmlentities($this->getAssetPath($filename, $packageName));
        }

        return $scriptTags;
    }

    private function getAssetPath(string $assetPath, string $packageName = null): string
    {
        if (null === $this->packages) {
            throw new \Exception('To render the script or link tags, run "composer require symfony/asset".');
        }

        return $this->packages->getUrl(
            $assetPath,
            $packageName
        );
    }

    private function getEntrypointLookup(string $buildName): EntrypointLookupInterface
    {
        return $this->entrypointLookupCollection->getEntrypointLookup($buildName);
    }
}