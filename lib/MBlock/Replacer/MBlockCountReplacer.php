<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MBlock\Replacer;


use MBlock\DTO\MBlockItem;

class MBlockCountReplacer
{
    /**
     * @param MBlockItem $item
     * @param $count
     * @return mixed
     * @author Joachim Doerr
     */
    public static function replaceCountKeys(MBlockItem $item, $count)
    {
        // TODO WORK WITH DOM
//        return str_replace(array('%%MB_COUNT%%', '%MB_COUNT%'), array('<span class="mb_count">'.$count.'</span>', $count), $item->getForm());
    }
}