<?php

/**
 * Author Joachim Doerr
 * Date: 25.10.16
 * Time: 19:56
 */
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
        return str_replace(array('%%MB_COUNT%%', '%MB_COUNT%'), array('<span class="mb_count">'.$count.'</span>', $count), $item->getForm());
    }
}