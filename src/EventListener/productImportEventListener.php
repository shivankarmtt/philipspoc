<?php

namespace App\EventListener;

use Pimcore\Event\BundleManager\PathsEvent;

class productImportEventListener
{
    public function addJSFiles(PathsEvent $event): void
    {
        $event->addPaths([
            '/js/productImportCallback.js'
        ]);
    }
}
