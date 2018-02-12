<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlockWidgetReplacer
{
    /**
     * @param MBlockItem $item
     * @param $count
     * @return String
     * @author Joachim Doerr
     */
    public static function replaceYFormManagerWidget(MBlockItem $item, $count)
    {
        ## yform_MANAGER
        // set phpquery document
        $document = phpQuery::newDocumentHTML($item->getForm());

        // find input group
        if ($matches = $document->find('.input-group')) {
            /** @var DOMElement $match */
            foreach ($matches as $key => $match) {
                if ($match->hasChildNodes()) {
                    /** @var DOMElement $child */
                    foreach ($match->getElementsByTagName('input') as $child) {
                        if (strpos($child->getAttribute('id'), 'yform_MANAGER_DATANAME_') !== false) {
                            echo $child->getAttribute('value');
                        }
                        if (strpos($child->getAttribute('id'), 'yform_MANAGER_DATA_') !== false) {
//                            $child->setAttribute('name', str_replace('VALUE[',"VALUE[{$item->getValueId()}][{$item->getId()}][REX_YFORM_TABLE_DATA_",$child->getAttribute('name')));
//                            echo '<pre>';
//                            print_r($child->getAttribute('name'));
//                            echo '</pre>';
                        }
                    }
                }
            }
        }

        /*
            <div class="input-group">
                <input class="form-control" type="text" name="yform_MANAGER_DATANAME[1]" value="Eurythmie Abschluss der 12. Klasse [id=11]" id="yform_MANAGER_DATANAME_1" readonly="">
                <input type="hidden" name="REX_INPUT_VALUE[1]" id="yform_MANAGER_DATA_1" value="11">
                <span class="input-group-btn">
                    <a href="javascript:void(0);" class="btn btn-popup" onclick="yform_manager_openDatalist(1, 'name_1', 'index.php?page=yform/manager/data_edit&amp;table_name=rex_mcalendar_events','0');return false;" title="Datensatz auswählen"><i class="rex-icon rex-icon-add"></i></a>
                    <a href="javascript:void(0);" class="btn btn-popup" onclick="yform_manager_deleteDatalist(1,'0');return false;" title="Ausgewählten Datensatz löschen"><i class="rex-icon rex-icon-remove"></i></a>
                </span>
            </div>
         */
        /*
           <div class="input-group">
            <input class="form-control" type="text" value="" id="REX_LINK_1000_NAME" readonly="">
            <input type="hidden" name="REX_INPUT_VALUE[1][0][REX_INPUT_LINK_1]" id="REX_LINK_1000" value="">
            <span class="input-group-btn">
                <a href="#" class="btn btn-popup" onclick="openLinkMap('REX_LINK_1000', '&amp;clang=1');return false;" title="Link auswählen"><i class="rex-icon rex-icon-open-linkmap"></i></a>
                <a href="#" class="btn btn-popup" onclick="deleteREXLink(1000);return false;" title="Ausgewählten Link löschen"><i class="rex-icon rex-icon-delete-link"></i></a>
            </span>
           </div>
         */
        // return the manipulated html output
        return $document->htmlOuter();

    }
}