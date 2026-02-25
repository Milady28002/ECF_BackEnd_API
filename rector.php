<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/src/Entity'])
    ->withSets([
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
    ]);