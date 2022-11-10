<?php

namespace markhuot\igloo\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\FileHelper;
use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\FilesystemServiceProvider;
use markhuot\igloo\actions\GetComponents;
use markhuot\igloo\actions\GetSlotConfig;
use markhuot\igloo\Igloo;

class Slot extends Field
{
    public static function hasContentColumn(): bool
    {
        return false;
    }

    function getInputHtml($value, ElementInterface $element = null): string
    {
        $storagePath = \Craft::$app->getRuntimePath() . '/laravel';
        FileHelper::createDirectory($storagePath);
        FileHelper::createDirectory($storagePath . '/bootstrap/cache');
        $_ENV['APP_PACKAGES_CACHE'] = $storagePath . '/packages.php';

        $app = new \Illuminate\Foundation\Application($storagePath);
        $app->singleton(\Illuminate\Contracts\Http\Kernel::class, \Illuminate\Foundation\Http\Kernel::class);
        $app->singleton(\Illuminate\Contracts\Debug\ExceptionHandler::class, \Illuminate\Foundation\Exceptions\Handler::class);
        $app->bind(\Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function() use ($storagePath) {
            return new class extends \Illuminate\Foundation\Bootstrap\LoadConfiguration {
                protected function loadConfigurationFiles(Application $app, RepositoryContract $repository)
                {
                    $appConfig = [
                        'name' => 'Laravel',
                        'providers' => [
                            \Illuminate\Cache\CacheServiceProvider::class,
                            \Illuminate\Filesystem\FilesystemServiceProvider::class,
                            \Illuminate\View\ViewServiceProvider::class,
                            \Illuminate\Translation\TranslationServiceProvider::class,
                            \Livewire\LivewireServiceProvider::class,
                        ],
                        'view.paths' => [],
                        'aliases' => \Illuminate\Support\Facades\Facade::defaultAliases()->toArray(),
                        'livewire' => [
                            'class_namespace' => 'markhuot\\igloo\\components',
                        ],
                    ];

                    $repository->set('app', $appConfig);


                    $compiledViewPath = \Craft::$app->getRuntimePath() . '/laravel/views';
                    FileHelper::createDirectory($compiledViewPath);
                    $repository->set('view', [
                        'compiled' => $compiledViewPath,
                        'paths' => [
                            __DIR__ . '/../blade',
                        ],
                    ]);
                }
            };
        });

        /** @var \Illuminate\Foundation\Http\Kernel $kernel */
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
        $app['router']->get('/', fn () => view('index'));
        $input = new \Illuminate\Http\Request();
        $status = $kernel->handle($input);
        dd($status);

        // $isRootSlot = false;
        // $elementId = \Craft::$app->requestedParams['elementId'] ?? null;
        // if ($elementId) {
        //     $routeElement = \Craft::$app->elements->getElementById($elementId);
        //     if (in_array($routeElement->id, [$element->id, $element->getCanonicalId()])) {
        //         $isRootSlot = true;
        //     }
        // }
        //
        // return Craft::$app->getView()->renderTemplate('igloo/fields/slot', [
        //     'field' => $this,
        //     'element' => $element,
        //     'isRootSlot' => $isRootSlot,
        //     'config' => (new GetSlotConfig)->handle($this, $element),
        // ]);
    }

    function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        if (empty($element->id)) {
            return null;
        }

        return (new GetComponents)->getComponents($element, $this);
    }

    function serializeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        if (!$element) {
            return [];
        }

        return (new GetComponents)
            ->getRows($element, $this)
            ->where('depth', '=', 1)
            ->pluck('descendant')
            ->toArray();
    }

    function afterElementPropagate(ElementInterface $element, bool $isNew): void
    {
        $action = null;
        if ($element?->getIsDraft() && $isNew && $element->duplicateOf) {
            $action = 'duplicate';
        }
        else if (!$element?->getIsDraft() && $element?->duplicateOf?->getIsDraft()) {
            $action = 'copy';
        }
        else {
            return;
        }

        $oldComponentIds = (new GetComponents)
            ->getRows($element, $this)
            ->where('depth', '=', 1);

        $newComponentIds = (new GetComponents)
            ->getRows($element->duplicateOf, $this)
            ->where('depth', '=', 1);

        if ($action === 'copy') {
            Igloo::getInstance()->tree->detach($element, $this, 'default', $oldComponentIds->pluck('uid')->toArray());
            Igloo::getInstance()->tree->attach($element, $this, 'default', $newComponentIds->pluck('descendant')->toArray(), null, 'beforeend');
        }
        else if ($action === 'duplicate') {
            Igloo::getInstance()->tree->attach($element, $this, 'default', $newComponentIds->pluck('descendant')->toArray(), null, 'beforeend');
        }
    }

}
