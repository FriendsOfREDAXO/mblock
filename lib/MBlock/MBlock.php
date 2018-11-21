<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlock
{
    /**
     * @var \Mblock\Handler\MBlockHandler
     */
    private static $mblockHandler;

    /**
     * MBlock constructor.
     * @author Joachim Doerr
     */
    public function __construct()
    {
        // create mblock page count is not exist
        if (is_null(rex_session('mblock_count', 'integer', null))) {
            // set mblock count is not exist
            rex_set_session('mblock_count', 0);
        }
    }

    /*
     * @param int|bool $id
     * @param string|MForm|mblock_rex_form|rex_yform $form
     * @param array $settings
     * @param null $theme
     * @return mixed
     */
    public static function show($id, $form, $settings = array(), $theme = null)
    {
        // set mblock theme
        $theme = (!is_null($theme)) ? $theme : 'default';

        rex_set_session('mblock_count', rex_session('mblock_count') + 1);

        // init handler
        self::$mblockHandler = new \Mblock\Handler\MBlockHandler($id, $form, $settings, null, $theme);

        // duplicate form elements by values
        self::$mblockHandler->createItems();

        // iterate items and create blocks
        self::$mblockHandler->iterateItems();

        // parse elements to mblock blocks
        self::$mblockHandler->parseItemElements();

        // final parse elements into mblock wrapper
        return self::$mblockHandler->parseMBlockWrapper();
    }
}