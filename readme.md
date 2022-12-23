# Neo config

Very simple module to sit alongside Neo to selectively choose which Neo blocks are available for which entry types on a site.

Should probably be a plugin!

## Requirements
* Craft v4
* Neo plugin
* Sprig plugin (this is used to select. Am sure a non-sprig version should be relatively easy with existing controllers but we had this on our project anyway)

## Notes

As modules don't have settings likle plugins do this module requires a Global variable and field set up.  
We then save all our oonfiguration as a JSON encoded array within this field.  

In this instance we create a Global Set Name with `Site Config` (handle `siteConfig`) and add to that a Plain Text field called `Neo Config` (handle `neoConfig`). Depending on how complex your site is you might need to set this field (under Advanced Settings to Column Type: `text(~64KB)`).  

I suppose it might be useful to put these handles instead into a config file rather than hard-coded but will work for now (or you can change them yourself).

Getting up and running will require you update your `config/app.php` file, eg if you save this project to `/users/mud/my-project/modules` then you would need the following applied to your `app.php`:

```
use craft\helpers\App;
use modules\Module;

return [
    'id' => App::env('CRAFT_APP_ID') ?: 'CraftCMS', 'modules' => ['neo-config' => Module::class], 'bootstrap' => ['neo-config'],
];
```

Author: @cole007  
URL: https://ournameismud.co.uk/  