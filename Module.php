<?php

namespace modules;

use Craft;

use yii\base\Event;
use yii\base\Module as BaseModule;

use craft\web\View;
use craft\web\twig\variables\Cp;

use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterTemplateRootsEvent;

use benf\neo\assets\InputAsset;
use benf\neo\events\FilterBlockTypesEvent;

use modules\twigextensions\NeoConfigTwigExtension as TwigExtension;

/**
 * neo-config module
 *
 * @method static Module getInstance()
 */
class Module extends BaseModule
{
    public function init()
    {
        Craft::setAlias('@neoconfig', __DIR__);

        
        $this->controllerNamespace = 'modules\\controllers';

        parent::init();

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
            // ...
        });
    }

    private function attachEventHandlers(): void
    {
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            $user = Craft::$app->getUser();
            if ($user->isAdmin) {
                
                $extension = new TwigExtension();
                Craft::$app->getView()->registerTwigExtension($extension);
                Event::on(
                    View::class,
                    View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
                    function (RegisterTemplateRootsEvent $e) {
                        $e->roots[$this->id] = __DIR__ . '/templates';
                    }
                );

                Event::on(
                    Cp::class,
                    Cp::EVENT_REGISTER_CP_NAV_ITEMS,

                    function(RegisterCpNavItemsEvent $event) {
                        $event->navItems[] = [
                            'url' => 'neo-config/fields/config',
                            'label' => 'Neo Config',
                            'icon' => '@neoconfig/web/assets/neoconfig/dist/img/neo.svg',
                        ];
                    }
                );

                Event::on(InputAsset::class, InputAsset::EVENT_FILTER_BLOCK_TYPES, function (FilterBlockTypesEvent $event) {
                    $element = $event->element;
                    // entryTypeId 
                    $typeId = $element->typeId;
                    $field = $event->field;
                    // fieldId
                    $fieldId = $field->id;
                    if ($element instanceof craft\elements\Entry) {
                        $globals = \Craft::$app->getGlobals()->getSetByHandle('siteConfig');
                        $neoField = $globals->neoConfig;
                        $neoConfig = json_decode($neoField,JSON_OBJECT_AS_ARRAY);        
                        if (is_array($neoConfig) && array_key_exists($fieldId,$neoConfig) && array_key_exists($typeId,$neoConfig[$fieldId])) {
                            $blocks = $neoConfig[$fieldId][$typeId];
                            $filteredBlockTypes = [];
                            foreach ($event->blockTypes as $type) {
                                if (in_array($type->handle,$blocks)) {
                                    $filteredBlockTypes[] = $type;
                                }
                            }
                            $event->blockTypes = $filteredBlockTypes;
                        }                    
                    }                
                });
            }
        }
    }
}
