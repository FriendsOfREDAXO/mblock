<?php
/**
 * @author https://github.com/FriendsOfREDAXO
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MBlock;

use FriendsOfRedaxo\MBlock\Decorator\MBlockFormItemDecorator;
use FriendsOfRedaxo\MBlock\DTO\MBlockElement;
use FriendsOfRedaxo\MBlock\DTO\MBlockItem;
use FriendsOfRedaxo\MBlock\Handler\MBlockValueHandler;
use FriendsOfRedaxo\MBlock\Parser\MBlockParser;
use FriendsOfRedaxo\MBlock\Replacer\MBlockBootstrapReplacer;
use FriendsOfRedaxo\MBlock\Replacer\MBlockCheckboxReplacer;
use FriendsOfRedaxo\MBlock\Replacer\MBlockCountReplacer;
use FriendsOfRedaxo\MBlock\Replacer\MBlockSystemButtonReplacer;
use FriendsOfRedaxo\MBlock\Replacer\MBlockValueReplacer;
use FriendsOfRedaxo\MBlock\Utils\MBlockSessionHelper;
use FriendsOfRedaxo\MBlock\Utils\MBlockSettingsHelper;
use InvalidArgumentException;
use rex;
use rex_addon;
use rex_escape;
use rex_extension;
use rex_extension_point;
use rex_form;
use rex_fragment;
use rex_get;
use rex_getUrl;
use rex_request;
use rex_sql_exception;
use rex_string;
use rex_url;
use rex_var;
use rex_yform;

class MBlock
{

    /**
     * @var array
     */
    private static $items = array();

    /**
     * @var array
     */
    private static $result = array();

    /**
     * @var array
     */
    private static $output = array();

    /**
     * MBlock constructor.
     * @author Joachim Doerr
     */
    public function __construct()
    {
        // Sichere Session-Initialisierung mit MBlockSessionHelper
        MBlockSessionHelper::initializeSession();
    }

    /**
     * @param $id
     * @param string|MForm|mblock_rex_form|rex_yform $form
     * @param array $settings
     * @param null $theme
     * @return mixed
     * @throws rex_sql_exception
     */
    public static function show($id, $form, $settings = array(), $theme = null)
    {
        // Input-Validierung für kritische Parameter
        if (($id === '' || $id === null) || (!is_string($id) && !is_numeric($id))) {
            throw new InvalidArgumentException('MBlock: ID muss eine nicht-leere Zeichenkette oder Zahl sein');
        }

        if (empty($form)) {
            throw new InvalidArgumentException('MBlock: Form-Parameter darf nicht leer sein');
        }

        if (!is_array($settings)) {
            $settings = array(); // Fallback für ungültige Settings
        }

        $plain = false;
        // Sichere Session-Counter-Verwaltung mit MBlockSessionHelper
        MBlockSessionHelper::incrementCount();

        if (is_integer($id) or is_numeric($id)) {
            // load rex value by id
            self::$result = MBlockValueHandler::loadRexVars();

            if ($form instanceof MForm) {
                $form = $form->show();
            }
        } else {
            if (strpos($id, 'yform') !== false) {
                $table = explode('::', $id);

                if (sizeof($table) > 2) {
                    $id = $table[0] . '::' . $table[1];
                    $settings['type_key'] = $table[2];
                    $post = rex_request::post($table[1]);
                    if (!is_null($post) && isset($post[$settings['type_key']])) {
                        self::$result['value'][$id] = $post[$settings['type_key']];
                    }
                    if (sizeof($table) > 3) {
                        self::$result = MBlockValueHandler::loadFromTable($table);
                    }
                } else {
                    self::$result = rex_request::post($table[1]);
                }

                if ($form instanceof rex_yform) {
                    // get fields
                    $form->executeFields();
                    $formFields = $form->getObjectparams('form_output');

                    // rmeove submit button
                    array_pop($formFields);
                    array_pop($formFields);

                    // implode fields to html string
                    $form = implode('', $formFields);

                    preg_match_all('/name="([^"]*)"/', $form, $matches, PREG_SET_ORDER, 0);

                    foreach ($matches as $match) {

                        preg_match_all('/(-\d{1,2}-)|(-\w*-)/', $match[1], $subMatches);
                        $toReplace = $match[0];
                        $replaceWith = $match[0];

                        foreach ($subMatches[0] as $subMatch) {
                            $replaceWith = str_replace($subMatch, '[' . substr($subMatch, 1, -1) . ']', $replaceWith);
                        }

                        $form = str_replace($toReplace, $replaceWith, $form);
                    }
                }

            } else {
                // is table::column
                $table = explode('::', $id);
                self::$result = MBlockValueHandler::loadFromTable($table, rex_request::get('id', 'int', 0));

                if (sizeof($table) > 2) {
                    $id = $table[0] . '::' . $table[1];
                    $settings['type_key'] = array_pop($table);
                }

                if ($form instanceof mblock_rex_form) {
                    $form = $form->getElements();
                }

            }
        }

        // crate plain element
        $plainItem = new MBlockItem();
        $plainItem->setId(0)
            ->setValueId($id)
            ->setResult(array())
            ->setForm($form)
            ->addPayload('plain_item', true);

        // is loaded
        if (array_key_exists('value', self::$result) && 
            is_array(self::$result['value']) && 
            array_key_exists($id, self::$result['value']) && 
            is_array(self::$result['value'][$id])) {
            // item result to item
            foreach (self::$result['value'][$id] as $jId => $values) {
                // Validierung der Schlüssel
                if (is_numeric($jId) && is_array($values)) {
                    // init item
                    self::$items[$jId] = new MBlockItem;
                    self::$items[$jId]->setId($jId)
                        ->setValueId($id)
                        ->setResult($values)
                        ->setForm($form);
                }
            }
        }

        // key must be integer
        foreach (self::$items as $key => $item) {
            if (!is_int($key)) {
                unset(self::$items[$key]);
            }
        }

        // create first element
        // don't loaded?
        if (!self::$items && (!isset($settings['initial_hidden']) or $settings['initial_hidden'] != 1)) {
            // set plain item for add
            $plain = true;
            self::$items[0] = new MBlockItem();
            self::$items[0]->setId(0)
                ->setValueId($id)
                ->setResult(array())
                ->setForm($form);
        }


        // foreach rex value json items
        /** @var MBlockItem $item */
        foreach (static::$items as $count => $item) {
            static::$output[] = self::createOutput($item, ($count + 1), $theme);
        }

        $addText = (isset($settings['initial_button_text'])) ? ' ' . $settings['initial_button_text'] : '';
        $addItem = rex_escape('<div class="mblock-single-add"><span class="singleadded"><button type="button" class="btn btn-default addme" title="duplicate"><i class="rex-icon rex-icon-add-module"></i>' . $addText . '</button></span></div>');
        $plainItem = rex_escape(self::createOutput($plainItem, 0, $theme));

        // wrap parsed form items
        $wrapper = new MBlockElement();
        $wrapper->setOutput(implode('', static::$output))
            ->setSettings(MBlockSettingsHelper::getSettings(array_merge($settings, ['mblock-plain-sortitem' => $plainItem, 'mblock-single-add' => $addItem])));

        // return wrapped from elements
        $output = MBlockParser::parseElement($wrapper, 'wrapper', $theme);


        if (($plain && array_key_exists('disable_null_view', $settings) && $settings['disable_null_view'] == true) and rex_request::get('function', 'string') != 'add') {

            $buttonText = 'Show MBlock';
            if (array_key_exists('null_view_button_text', $settings) && !empty($settings['null_view_button_text'])) {
                $buttonText = $settings['null_view_button_text'];
            }

            $uniqueId = uniqid();
            $output = '
                <div id="accordion' . $uniqueId . '" role="tablist">
                  <div class="panel mblock-hidden-panel">
                    <div id="collapse' . uniqid() . '" class="collapse in" role="tabpanel">
                        <a class="btn btn-primary" role="button" data-toggle="collapse" data-parent="#accordion' . $uniqueId . '" href="#collapse' . $uniqueId . '" aria-expanded="false" aria-controls="collapseTwo">' . $buttonText . '</a>
                    </div>
                  </div>
                  <div id="collapse' . $uniqueId . '" class="collapse" role="tabpanel">' . $output . '</div>
                </div>
            ';
        }

        // reset for multi block fields
        self::reset();

        // return output
        return $output;
    }

    /**
     * @param MBlockItem $item
     * @param $count
     * @param null $theme
     * @return mixed
     * @author Joachim Doerr
     */
    private static function createOutput(MBlockItem $item, $count, $theme = null)
    {
        // Debug: Check if createOutput is called
        file_put_contents('/tmp/mblock_debug.log', 'createOutput called for count: ' . $count . ' at ' . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
        
        $item->setForm(MBlockSystemButtonReplacer::replaceSystemButtons($item, $count));
        $item->setForm(MBlockCountReplacer::replaceCountKeys($item, $count));
        $item->setForm(MBlockBootstrapReplacer::replaceTabIds($item, $count));
        $item->setForm(MBlockBootstrapReplacer::replaceCollapseIds($item, $count));

        // decorate item form
        if ($item->getResult()) {
            $item->setForm(MBlockFormItemDecorator::decorateFormItem($item));
            // custom link hidden to text
            $item->setForm(MBlockSystemButtonReplacer::replaceCustomLinkText($item));
        } else {
            // no result set values to empty!
            $item->setForm(MBlockValueReplacer::replaceValueSetEmpty($item));
        }

        // set only checkbox block holder
        $item->setForm(MBlockCheckboxReplacer::replaceCheckboxesBlockHolder($item, $count));

        // parse form item
        $element = new MBlockElement();
        $element->setForm($item->getForm())
            ->setIndex($count);

        // Detect offline field and set offline properties
        self::setOfflineProperties($element, $item);

        // parse element to output
        $output = MBlockParser::parseElement($element, 'element', $theme);

        // fix & error
        foreach ($item->getResult() as $result) {
            if (is_array($result) && array_key_exists('id', $result)) {
                $output = str_replace($result['id'], $result['value'], $output);
            }
        }

        return $output;
    }

    /**
     * Setzt die Offline-Properties für ein Element basierend auf dem mblock_offline Feld
     * @param MBlockElement $element Das MBlock Element
     * @param MBlockItem $item Das MBlock Item
     * @author Joachim Doerr
     */
    private static function setOfflineProperties(MBlockElement $element, MBlockItem $item)
    {
        $form = $item->getForm();
        
        // Check if form contains mblock_offline field
        $hasOfflineField = (strpos($form, 'name="mblock_offline"') !== false || 
                           strpos($form, "name='mblock_offline'") !== false ||
                           preg_match('/name=.*mblock_offline.*\[/', $form));
        
        if ($hasOfflineField) {
            // Get the current value of mblock_offline field
            $isOffline = false;
            
            // Method 1: Try to extract from the HTML form value attribute
            if (preg_match('/name="[^"]*mblock_offline[^"]*"\s+value="([^"]*)"/', $form, $matches)) {
                $isOffline = ($matches[1] == '1');
            } else {
                // Method 2: Try to get from result data (backup)
                $result = $item->getResult();
                if ($result && is_array($result)) {
                    foreach ($result as $resultItem) {
                        if (is_array($resultItem) && isset($resultItem['mblock_offline'])) {
                            $isOffline = ($resultItem['mblock_offline'] == '1' || 
                                         $resultItem['mblock_offline'] === '1' || 
                                         $resultItem['mblock_offline'] === true);
                            break;
                        }
                    }
                }
            }
            
            // Set CSS class based on offline status
            $element->setOfflineClass($isOffline ? ' mblock-offline' : '');
            
            // Set offline button HTML with color coding
            if ($isOffline) {
                $offlineButtonClass = 'btn-danger'; // Red for offline
                $offlineButtonIcon = 'rex-icon-offline';
                $offlineButtonTitle = 'Set online';
                $offlineButtonText = 'Offline';
            } else {
                $offlineButtonClass = 'btn-success'; // Green for online
                $offlineButtonIcon = 'rex-icon-online';
                $offlineButtonTitle = 'Set offline';
                $offlineButtonText = 'Online';
            }
            
            $offlineButton = '<div class="btn-group btn-group-xs">
                <button type="button" class="btn ' . $offlineButtonClass . ' mblock-offline-toggle-btn" 
                        title="' . $offlineButtonTitle . '" data-offline="' . ($isOffline ? '1' : '0') . '">
                    <i class="rex-icon ' . $offlineButtonIcon . '"></i> ' . $offlineButtonText . '
                </button>
            </div>';
            
            $element->setOfflineButton($offlineButton);
        } else {
            // No offline field found - set empty values
            $element->setOfflineClass('');
            $element->setOfflineButton('');
        }
    }

    /**
     * Zentrale Methode zum Abrufen von MBlock-Daten mit optionaler Filterung
     * @param string $rexValue REX_VALUE String (z.B. "REX_VALUE[1]")
     * @param string $filter 'all', 'online', 'offline' (default: 'all')
     * @param string $offlineField Name des Offline-Feldes (default: 'mblock_offline')
     * @return array Verarbeitete und gefilterte MBlock-Daten
     * @author Joachim Doerr
     */
    public static function getDataArray($rexValue, $filter = 'all', $offlineField = 'mblock_offline')
    {
        // Input-Validierung
        if (empty($rexValue) || !is_string($rexValue)) {
            return array();
        }
        
        // rex_var::toArray() ausführen
        $data = rex_var::toArray($rexValue);
        
        if (!is_array($data) || empty($data)) {
            return array();
        }
        
        // Wenn kein Filter gewünscht, alle Daten zurückgeben
        if ($filter === 'all') {
            return $data;
        }
        
        // Daten filtern
        return self::filterByStatus($data, $filter, $offlineField);
    }

    /**
     * Filtert MBlock-Daten basierend auf Offline-Status
     * @param array $data MBlock-Daten (normalerweise von rex_var::toArray("REX_VALUE[1]"))
     * @param string $filter 'online', 'offline' oder 'all' (default: 'all')
     * @param string $offlineField Name des Offline-Feldes (default: 'mblock_offline')
     * @return array Gefilterte Daten
     * @author Joachim Doerr
     */
    public static function filterByStatus($data, $filter = 'all', $offlineField = 'mblock_offline')
    {
        if (!is_array($data) || empty($data)) {
            return array();
        }
        
        if ($filter === 'all') {
            return $data;
        }
        
        $result = array();
        $filterOffline = ($filter === 'offline');
        
        foreach ($data as $index => $item) {
            if (!is_array($item)) {
                continue;
            }
            
            $isOffline = false;
            if (isset($item[$offlineField])) {
                $offlineValue = $item[$offlineField];
                $isOffline = ($offlineValue == '1' || $offlineValue === true || $offlineValue === 'true');
            }
            
            // Wenn wir offline Items wollen und dieses offline ist, oder
            // wenn wir online Items wollen und dieses online ist
            if ($filterOffline === $isOffline) {
                $result[$index] = $item;
            }
        }
        
        return $result;
    }
    
    /**
     * Convenience-Methode für Online-Items mit automatischer rex_var Verarbeitung
     * @param string $rexValue REX_VALUE String (z.B. "REX_VALUE[1]")
     * @param string $offlineField Name des Offline-Feldes (default: 'mblock_offline')
     * @return array Nur Online-Items
     * @author Joachim Doerr
     */
    public static function getOnlineDataArray($rexValue, $offlineField = 'mblock_offline')
    {
        return self::getDataArray($rexValue, 'online', $offlineField);
    }
    
    /**
     * Convenience-Methode für Offline-Items mit automatischer rex_var Verarbeitung
     * @param string $rexValue REX_VALUE String (z.B. "REX_VALUE[1]")
     * @param string $offlineField Name des Offline-Feldes (default: 'mblock_offline')
     * @return array Nur Offline-Items
     * @author Joachim Doerr
     */
    public static function getOfflineDataArray($rexValue, $offlineField = 'mblock_offline')
    {
        return self::getDataArray($rexValue, 'offline', $offlineField);
    }

    /**
     * Convenience-Methode für Online-Items
     * @param array $data MBlock-Daten
     * @param string $offlineField Name des Offline-Feldes (default: 'mblock_offline')
     * @return array Nur Online-Items
     * @author Joachim Doerr
     */
    public static function getOnlineItems($data, $offlineField = 'mblock_offline')
    {
        return self::filterByStatus($data, 'online', $offlineField);
    }
    
    /**
     * Convenience-Methode für Offline-Items
     * @param array $data MBlock-Daten
     * @param string $offlineField Name des Offline-Feldes (default: 'mblock_offline')
     * @return array Nur Offline-Items
     * @author Joachim Doerr
     */
    public static function getOfflineItems($data, $offlineField = 'mblock_offline')
    {
        return self::filterByStatus($data, 'offline', $offlineField);
    }

    /**
     * Filtert MBlock-Items nach Feldwert
     * @param array $items MBlock-Items
     * @param string $field Feldname
     * @param mixed $value Gesuchter Wert
     * @param bool $strict Strikte Vergleichung (default: false)
     * @return array Gefilterte Items
     */
    public static function filterByField($items, $field, $value, $strict = false)
    {
        if (!is_array($items) || empty($items)) {
            return array();
        }

        return array_filter($items, function($item) use ($field, $value, $strict) {
            if (!isset($item[$field])) {
                return false;
            }
            
            return $strict ? $item[$field] === $value : $item[$field] == $value;
        });
    }

    /**
     * Sortiert MBlock-Items nach Feldwert
     * @param array $items MBlock-Items
     * @param string $field Feldname zum Sortieren
     * @param string $direction 'asc' oder 'desc' (default: 'asc')
     * @return array Sortierte Items
     */
    public static function sortByField($items, $field, $direction = 'asc')
    {
        if (!is_array($items) || empty($items)) {
            return $items;
        }

        $direction = strtolower($direction);
        
        usort($items, function($a, $b) use ($field, $direction) {
            $valueA = isset($a[$field]) ? $a[$field] : '';
            $valueB = isset($b[$field]) ? $b[$field] : '';
            
            // Numerische Sortierung wenn beide Werte numerisch sind
            if (is_numeric($valueA) && is_numeric($valueB)) {
                $result = ($valueA < $valueB) ? -1 : (($valueA > $valueB) ? 1 : 0);
            } else {
                $result = strcasecmp($valueA, $valueB);
            }
            
            return $direction === 'desc' ? -$result : $result;
        });

        return $items;
    }

    /**
     * Gruppiert MBlock-Items nach Feldwert
     * @param array $items MBlock-Items
     * @param string $field Feldname zum Gruppieren
     * @return array Gruppierte Items [feldwert => [items]]
     */
    public static function groupByField($items, $field)
    {
        if (!is_array($items) || empty($items)) {
            return array();
        }

        $groups = array();
        
        foreach ($items as $item) {
            $groupKey = isset($item[$field]) ? $item[$field] : 'undefined';
            
            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = array();
            }
            
            $groups[$groupKey][] = $item;
        }

        return $groups;
    }

    /**
     * Limitiert MBlock-Items (für Pagination)
     * @param array $items MBlock-Items
     * @param int $limit Maximale Anzahl Items
     * @param int $offset Start-Position (default: 0)
     * @return array Limitierte Items
     */
    public static function limitItems($items, $limit, $offset = 0)
    {
        if (!is_array($items) || empty($items)) {
            return array();
        }

        return array_slice($items, $offset, $limit);
    }

    /**
     * Generiert JSON-LD Schema.org Markup für MBlock-Items
     * @param array $items MBlock-Items
     * @param string $type Schema.org Type (z.B. 'Article', 'Product', 'Event')
     * @param array $fieldMapping Mapping von MBlock-Feldern zu Schema-Properties
     * @return string JSON-LD Schema Markup
     */
    public static function generateSchema($items, $type = 'Article', $fieldMapping = array())
    {
        if (!is_array($items) || empty($items)) {
            return '';
        }

        // Standard Feld-Mappings
        $defaultMappings = array(
            'Article' => array(
                'headline' => array('title', 'name', 'headline'),
                'description' => array('content', 'description', 'text'),
                'image' => array('REX_MEDIA_1', 'image', 'media'),
                'datePublished' => array('date', 'created', 'published'),
                'author' => array('author', 'creator')
            ),
            'Product' => array(
                'name' => array('title', 'name', 'product_name'),
                'description' => array('description', 'content'),
                'image' => array('REX_MEDIA_1', 'image', 'product_image'),
                'price' => array('price', 'cost'),
                'brand' => array('brand', 'manufacturer')
            ),
            'Event' => array(
                'name' => array('title', 'name', 'event_name'),
                'description' => array('description', 'content'),
                'startDate' => array('start_date', 'date_start', 'begin'),
                'endDate' => array('end_date', 'date_end', 'finish'),
                'location' => array('location', 'venue', 'place')
            )
        );

        $mapping = array_merge($defaultMappings[$type] ?? array(), $fieldMapping);
        $schemaItems = array();

        foreach ($items as $item) {
            $schemaItem = array(
                '@type' => $type
            );

            foreach ($mapping as $schemaProp => $possibleFields) {
                $possibleFields = is_array($possibleFields) ? $possibleFields : array($possibleFields);
                
                foreach ($possibleFields as $field) {
                    if (isset($item[$field]) && !empty($item[$field])) {
                        $value = $item[$field];
                        
                        // Spezielle Behandlung für Media-Felder
                        if (strpos($field, 'REX_MEDIA') !== false && $schemaProp === 'image') {
                            $value = rex_url::media($value);
                            if (!preg_match('#^https?://#', $value)) {
                                $value = rex_url::frontend($value);
                            }
                        }
                        
                        // Spezielle Behandlung für Links
                        if (strpos($field, 'REX_LINK') !== false) {
                            $linkId = (int)$value;
                            if ($linkId > 0) {
                                $value = rex_getUrl($linkId);
                                if (!preg_match('#^https?://#', $value)) {
                                    $value = rex_url::frontend($value);
                                }
                            }
                        }
                        
                        $schemaItem[$schemaProp] = $value;
                        break; // Erstes gefundenes Feld verwenden
                    }
                }
            }

            if (count($schemaItem) > 1) { // Nur hinzufügen wenn mehr als nur @type vorhanden
                $schemaItems[] = $schemaItem;
            }
        }

        if (empty($schemaItems)) {
            return '';
        }

        $schema = array(
            '@context' => 'https://schema.org',
            '@graph' => $schemaItems
        );

        return '<script type="application/ld+json">' . json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</script>';
    }

    /**
     * Sichere Reset-Methode mit verbesserter Speicherverwaltung
     * @author Joachim Doerr
     */
    private static function reset()
    {
        // Sichere Array-Bereinigung
        if (is_array(self::$items)) {
            foreach (self::$items as $key => $item) {
                if (isset(self::$items[$key])) {
                    unset(self::$items[$key]);
                }
            }
            self::$items = array();
        }
        
        if (is_array(self::$result)) {
            foreach (self::$result as $key => $value) {
                if (isset(self::$result[$key])) {
                    unset(self::$result[$key]);
                }
            }
            self::$result = array();
        }
        
        if (is_array(self::$output)) {
            foreach (self::$output as $key => $value) {
                if (isset(self::$output[$key])) {
                    unset(self::$output[$key]);
                }
            }
            self::$output = array();
        }
    }
}