<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */



namespace FriendsOfRedaxo\MBlock\Replacer;

use FriendsOfRedaxo\MBlock\DTO\MBlockItem;

class MBlockCountReplacer
{
    /**
     * @param MBlockItem $item
     * @param int $count
     * @return string
     * @author Joachim Doerr
     */
    public static function replaceCountKeys(MBlockItem $item, $count)
    {
        return str_replace(array('%%MB_COUNT%%', '%MB_COUNT%'), array('<span class="mb_count">' . $count . '</span>', (string) $count), $item->getForm());
    }
}