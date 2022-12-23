<?php

namespace modules\controllers;

use Craft;
use craft\web\Controller;
use yii\web\Response;

/**
 * Neo Config controller
 */
class NeoConfigController extends Controller
{
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

    /**
     * neo-config/neo-config action
     */    

    public function actionIndex(): Response
    {
        // This action should only be available to the control panel
        $this->requireCpRequest();
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();

        // GET GLOBAL CONFIG
        $globals = \Craft::$app->getGlobals()->getSetByHandle('siteConfig');
        $neoField = $globals->neoConfig;
        $neoConfig = json_decode($neoField,JSON_OBJECT_AS_ARRAY);        
        if ($neoConfig == null) $neoConfig = [];
        
        // GET fieldHandle
        $fieldHandle = $request->getBodyParam('fieldHandle');        
        if (array_key_exists($fieldHandle,$neoConfig)) {
            $field = $neoConfig[$fieldHandle];
        } else {
            $field = [];
        }

        // GET entryType
        $entryType = $request->getBodyParam('entryType');  
        if (array_key_exists($entryType,$field)) {
            $type = $field[$entryType];
        } else {
            $type = [];
        }

        // GET blocks
        $blocks = $request->getBodyParam('blocks');
        if (!is_countable($blocks) OR count($blocks) == 0) {
            $this->setFailFlash('You must select at least one block');
            return null;
        }
        
        $type = $blocks;
        
        $neoConfig[$fieldHandle][$entryType] = $type;        

        $globals->setFieldValue('neoConfig',json_encode($neoConfig));
        Craft::$app->getElements()->saveElement($globals, false, false, false);

        $entryTypeModel = Craft::$app->sections->getEntryTypeById($entryType);
        $sectionId = $entryTypeModel->sectionId;
        $section = Craft::$app->sections->getSectionById($sectionId);
        
        // SET FLASH HERE
        $this->setSuccessFlash('Config saved for ' . $entryTypeModel->name . ' (' . $section->name . ')');
        return $this->redirectToPostedUrl();
    }
}
