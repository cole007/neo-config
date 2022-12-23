<?php
/**
 * Custom Helpers module for Craft CMS 3.x
 *
 * afaef
 *
 * @link      https://ournameismud.co.uk
 * @copyright Copyright (c) 2018 Richard George
 */

namespace modules\twigextensions;

use Craft;
use craft\db\Query;
use Twig\Extension\AbstractExtension;
use benf\neo\Plugin as Neo;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Twig can be extended in many ways; you can add extra tags, filters, tests, operators,
 * global variables, and functions. You can even extend the parser itself with
 * node visitors.
 *
 * http://twig.sensiolabs.org/doc/advanced.html
 *
 * @author    Richard George
 * @package   MudModule
 * @since     1.0.0
 */
class NeoConfigTwigExtension extends AbstractExtension
{
    public function getName()
    {
        return 'Neo Config';
    }
    public function getFunctions()
    {
        return [
			new TwigFunction('getNeoFields', [$this, 'getNeoFields']),
			new TwigFunction('getEntryTypes', [$this, 'getEntryTypes']),
			new TwigFunction('getNeoBlocks', [$this, 'getNeoBlocks']),
			new TwigFunction('getNeoConfig', [$this, 'getNeoConfig']),
			];
    }

	public function getNeoConfig($fieldHandle, $entryType) {		
		$globals = \Craft::$app->getGlobals()->getSetByHandle('siteConfig');
        $neoField = $globals->neoConfig;
        $neoConfig = json_decode($neoField,JSON_OBJECT_AS_ARRAY);        
		if (is_array($neoConfig) && array_key_exists($fieldHandle,$neoConfig)) {
			if (array_key_exists($entryType,$neoConfig[$fieldHandle])) {
				return $neoConfig[$fieldHandle][$entryType];
			}
		}
		return [];
	}
	public function getNeoBlocks($fieldId) {
		$blocks = Neo::$plugin->blockTypes->getByFieldId($fieldId);
		$response = [];
		foreach ($blocks AS $block) {
			$response[$block->handle] = array(
				'name' => $block->name,
				'topLevel' => $block->topLevel,
			);
		}
		return $response;
	}
	public function getEntryTypes($fieldId) {
		$response = [];
		$sections = Craft::$app->sections->getAllSections();
		$sectionIds = [];
		foreach($sections AS $section) {
			$sectionIds[$section->id] = $section->name;
		}
		$entryTypes = Craft::$app->sections->getAllEntryTypes();
		foreach ($entryTypes AS $entryType) {
			$id = $entryType->id;
			$sectionId = $entryType->sectionId;
			$fieldLayoutId = $entryType->fieldLayoutId;
			$name = $entryType->name;

			$connection = Craft::$app->getDb();

			$records = (new Query())
	        ->select( ['id'] )
	        ->from( ['{{%fieldlayoutfields}}'] )
	        ->where([
				'fieldId' => $fieldId,
				'layoutId' => $fieldLayoutId
			]);
			if ($records->count() > 0) {
				if (!array_key_exists($sectionId, $response)) {
					$response[$sectionId] = [];	
				} 
				$response[$sectionId][$id] = $name;
			}			
		}
		foreach ($sectionIds AS $sectionId=>$section) {
			if (!array_key_exists($sectionId, $response)) {
				unset($sectionIds[$sectionId]);
			}
		}		
		return ['sections'=>$sectionIds, 'entryTypes'=>$response];		
	}
	
	public function getNeoFields($type = 'Neo') {
		$fields = Craft::$app->fields;
		$handles = [];
		foreach ($fields->getAllFields() AS $field) {						
			$id = $field->id;
			$handle = $field->handle;
			$name = $field->name;
			$class = get_class($field);
			if ($class == 'benf\\neo\\Field') {
				$handles[$id] = $name;
			}					
		}
		return $handles;	
	}		
}

