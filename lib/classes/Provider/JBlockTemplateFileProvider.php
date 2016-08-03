<?php
/**
 * Created by PhpStorm.
 * User: joachimdoerr
 * Date: 30.07.16
 * Time: 22:36
 */

class JBlockTemplateFileProvider
{
    /**
     * @param $templateType
     * @return string
     * @author Joachim Doerr
     */
    public static function loadTemplate($templateType)
    {
        // set theme path to load type template file
        $path = rex_path::addonData('jblock', 'templates/default_theme/');
        $file = "jblock_$templateType.ini"; // create file name

        // to print without template
        $templateString = '<jblock:output/><jblock:form/>';

        // is template file exist? and template type not html
        if (file_exists($path . $file)) {
            // load theme file
            $templateString = implode(file($path . $file, FILE_USE_INCLUDE_PATH));
        }

        // exchange template string
        return $templateString;
    }
}