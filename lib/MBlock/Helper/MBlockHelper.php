<?php
/**
 * MBlock Helper - Vereinfacht die Entwicklung von Modulen mit MBlock
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlockHelper
{
    /**
     * Erstellt einen einfachen MBlock mit Standard-Konfiguration
     * @param int|string $id - MBlock ID
     * @param string|MForm $form - Form-Inhalt
     * @param array $options - Optionale Konfiguration
     * @return string - HTML-Output
     */
    public static function create($id, $form, $options = [])
    {
        // Standard-Optionen mit sinnvollen Defaults
        $defaults = [
            'min' => 0,
            'max' => 10,
            'sortable' => true,
            'delete_confirm' => true,
            'smooth_scroll' => true
        ];
        
        $settings = array_merge($defaults, $options);
        
        try {
            return MBlock::show($id, $form, $settings);
        } catch (Exception $e) {
            if (rex::isBackend()) {
                return '<div class="alert alert-danger">MBlock Fehler: ' . rex_escape($e->getMessage()) . '</div>';
            }
            return '';
        }
    }

    /**
     * Erstellt einen MBlock mit Accordion-Layout
     * @param int|string $id - MBlock ID
     * @param string|MForm $form - Form-Inhalt
     * @param array $options - Optionale Konfiguration
     * @return string - HTML-Output
     */
    public static function accordion($id, $form, $options = [])
    {
        $accordionForm = self::wrapInAccordion($form, $options);
        
        $settings = array_merge([
            'min' => 1,
            'max' => 5,
            'delete_confirm' => 'Block wirklich löschen?'
        ], $options);
        
        return self::create($id, $accordionForm, $settings);
    }

    /**
     * Erstellt einen MBlock mit Tab-Layout
     * @param int|string $id - MBlock ID
     * @param string|MForm $form - Form-Inhalt
     * @param array $options - Optionale Konfiguration
     * @return string - HTML-Output
     */
    public static function tabs($id, $form, $options = [])
    {
        $tabForm = self::wrapInTabs($form, $options);
        
        $settings = array_merge([
            'min' => 1,
            'max' => 8,
            'delete_confirm' => true
        ], $options);
        
        return self::create($id, $tabForm, $settings);
    }

    /**
     * Einfacher Output-Helper für Frontend
     * @param int|string $id - MBlock ID  
     * @param callable $callback - Callback-Funktion für jeden Block
     * @return string - HTML-Output
     */
    public static function output($id, $callback)
    {
        $output = '';
        
        if (!is_callable($callback)) {
            return $output;
        }
        
        // REX_VALUE laden
        $mblockData = rex_var::toArray("REX_VALUE[$id]");
        
        if (is_array($mblockData)) {
            foreach ($mblockData as $index => $data) {
                if (is_array($data)) {
                    try {
                        $blockOutput = call_user_func($callback, $data, $index);
                        if (is_string($blockOutput)) {
                            $output .= $blockOutput;
                        }
                    } catch (Exception $e) {
                        // Silent error handling im Frontend
                        if (rex::isBackend()) {
                            $output .= '<div class="alert alert-warning">Block #' . $index . ' Fehler: ' . rex_escape($e->getMessage()) . '</div>';
                        }
                    }
                }
            }
        }
        
        return $output;
    }

    /**
     * Template-Helper für häufige MBlock-Patterns
     * @param string $type - Template-Typ (gallery, team, cards, etc.)
     * @param array $config - Konfiguration
     * @return string - Form-HTML
     */
    public static function template($type, $config = [])
    {
        switch ($type) {
            case 'gallery':
                return self::getGalleryTemplate($config);
            case 'team':
                return self::getTeamTemplate($config);
            case 'cards':
                return self::getCardsTemplate($config);
            case 'text_image':
                return self::getTextImageTemplate($config);
            default:
                throw new InvalidArgumentException("Unbekannter Template-Typ: $type");
        }
    }

    /**
     * Wrapper für Accordion-Layout
     * @param string|MForm $form - Form-Inhalt
     * @param array $options - Optionen
     * @return string - Wrapped Form
     */
    private static function wrapInAccordion($form, $options = [])
    {
        $title = $options['title'] ?? 'Block %%INDEX%%';
        $formHtml = ($form instanceof MForm) ? $form->show() : $form;
        
        return '
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-target="#collapse_%%INDEX%%" href="#collapse_%%INDEX%%">
                        ' . $title . '
                    </a>
                </h4>
            </div>
            <div id="collapse_%%INDEX%%" class="panel-collapse collapse in">
                <div class="panel-body">
                    ' . $formHtml . '
                </div>
            </div>
        </div>';
    }

    /**
     * Wrapper für Tab-Layout
     * @param string|MForm $form - Form-Inhalt
     * @param array $options - Optionen
     * @return string - Wrapped Form
     */
    private static function wrapInTabs($form, $options = [])
    {
        $title = $options['title'] ?? 'Tab %%INDEX%%';
        $formHtml = ($form instanceof MForm) ? $form->show() : $form;
        
        return '
        <ul class="nav nav-tabs">
            <li class="active">
                <a data-toggle="tab" href="#tab_%%INDEX%%">' . $title . '</a>
            </li>
        </ul>
        <div class="tab-content">
            <div id="tab_%%INDEX%%" class="tab-pane fade in active">
                <div class="tab-pane-content">
                    ' . $formHtml . '
                </div>
            </div>
        </div>';
    }

    /**
     * Gallery-Template
     */
    private static function getGalleryTemplate($config)
    {
        return '
        <fieldset>
            <legend>Galerie-Bild</legend>
            <div class="form-group">
                <label>Bild</label>
                REX_MEDIA[id="1" widget="1"]
            </div>
            <div class="form-group">
                <label for="title_%%INDEX%%">Titel</label>
                <input type="text" class="form-control" id="title_%%INDEX%%" name="REX_INPUT_VALUE[' . ($config['id'] ?? 1) . '][%%INDEX%%][title]" value="" />
            </div>
            <div class="form-group">
                <label for="caption_%%INDEX%%">Beschreibung</label>
                <textarea class="form-control" id="caption_%%INDEX%%" name="REX_INPUT_VALUE[' . ($config['id'] ?? 1) . '][%%INDEX%%][caption]"></textarea>
            </div>
        </fieldset>';
    }

    /**
     * Team-Template
     */
    private static function getTeamTemplate($config)
    {
        return '
        <fieldset>
            <legend>Team-Mitglied</legend>
            <div class="form-group">
                <label for="name_%%INDEX%%">Name</label>
                <input type="text" class="form-control" id="name_%%INDEX%%" name="REX_INPUT_VALUE[' . ($config['id'] ?? 1) . '][%%INDEX%%][name]" value="" />
            </div>
            <div class="form-group">
                <label for="position_%%INDEX%%">Position</label>
                <input type="text" class="form-control" id="position_%%INDEX%%" name="REX_INPUT_VALUE[' . ($config['id'] ?? 1) . '][%%INDEX%%][position]" value="" />
            </div>
            <div class="form-group">
                <label>Foto</label>
                REX_MEDIA[id="1" widget="1"]
            </div>
            <div class="form-group">
                <label for="bio_%%INDEX%%">Kurze Biografie</label>
                <textarea class="form-control" id="bio_%%INDEX%%" name="REX_INPUT_VALUE[' . ($config['id'] ?? 1) . '][%%INDEX%%][bio]" rows="3"></textarea>
            </div>
        </fieldset>';
    }

    /**
     * Cards-Template
     */
    private static function getCardsTemplate($config)
    {
        return '
        <fieldset>
            <legend>Card</legend>
            <div class="form-group">
                <label for="title_%%INDEX%%">Titel</label>
                <input type="text" class="form-control" id="title_%%INDEX%%" name="REX_INPUT_VALUE[' . ($config['id'] ?? 1) . '][%%INDEX%%][title]" value="" />
            </div>
            <div class="form-group">
                <label for="text_%%INDEX%%">Text</label>
                <textarea class="form-control" id="text_%%INDEX%%" name="REX_INPUT_VALUE[' . ($config['id'] ?? 1) . '][%%INDEX%%][text]" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label>Bild (optional)</label>
                REX_MEDIA[id="1" widget="1"]
            </div>
            <div class="form-group">
                <label for="link_%%INDEX%%">Link (optional)</label>
                REX_LINK[id="1" widget="1"]
            </div>
        </fieldset>';
    }

    /**
     * Text-Image-Template
     */
    private static function getTextImageTemplate($config)
    {
        return '
        <fieldset>
            <legend>Text & Bild</legend>
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="headline_%%INDEX%%">Überschrift</label>
                        <input type="text" class="form-control" id="headline_%%INDEX%%" name="REX_INPUT_VALUE[' . ($config['id'] ?? 1) . '][%%INDEX%%][headline]" value="" />
                    </div>
                    <div class="form-group">
                        <label for="text_%%INDEX%%">Text</label>
                        <textarea class="form-control" id="text_%%INDEX%%" name="REX_INPUT_VALUE[' . ($config['id'] ?? 1) . '][%%INDEX%%][text]" rows="6"></textarea>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Bild</label>
                        REX_MEDIA[id="1" widget="1"]
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="REX_INPUT_VALUE[' . ($config['id'] ?? 1) . '][%%INDEX%%][image_left]" value="1" /> 
                            Bild links anzeigen
                        </label>
                    </div>
                </div>
            </div>
        </fieldset>';
    }
}
