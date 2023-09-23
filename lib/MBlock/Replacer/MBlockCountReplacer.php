<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlockCountReplacer
{
    /**
     * @return mixed
     * @author Joachim Doerr
     */
    public static function replaceCountKeys(MBlockItem $item, $count)
    {
        return str_replace(['%%MB_COUNT%%', '%MB_COUNT%'], ['<span class="mb_count">' . $count . '</span>', $count], $item->getForm());
    }
}
