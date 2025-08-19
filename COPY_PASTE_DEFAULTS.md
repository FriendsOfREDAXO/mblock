# MBlock Copy/Paste Standard-Konfiguration - Änderungen

## Übersicht
Die Copy/Paste-Funktionalität ist nun standardmäßig in neuen und bestehenden MBlock-Installationen aktiviert.

## Geänderte Dateien

### 1. `package.yml`
- **Neu**: `default_config` Sektion hinzugefügt
- **Konfiguration**: `mblock_copy_paste: true` als Standard definiert
- **Zweck**: Definiert Standardwerte für neue Installationen

### 2. `install.php`
- **Erweitert**: Copy/Paste-Konfiguration für Neuinstallationen
- **Code**: `$this->setConfig('mblock_copy_paste', 1)`
- **Zweck**: Aktiviert Copy/Paste bei frischer Installation

### 3. `update.php`
- **Erweitert**: Copy/Paste-Konfiguration für bestehende Installationen
- **Code**: `$this->setConfig('mblock_copy_paste', 1)`
- **Zweck**: Aktiviert Copy/Paste bei Updates bestehender Installationen

## Verhalten

### Neue Installation
1. Addon wird installiert → `install.php` ausgeführt
2. Copy/Paste wird automatisch aktiviert
3. Benutzer kann es in Einstellungen deaktivieren

### Update bestehender Installation
1. Addon wird aktualisiert → `update.php` ausgeführt  
2. Copy/Paste wird automatisch aktiviert (falls noch nicht konfiguriert)
3. Bestehende Benutzereinstellungen bleiben erhalten

### Fallback-Verhalten
- Wenn keine Konfiguration vorhanden ist, greift der Standard: **aktiviert**
- In `MBlockSettingsHelper`: `$addon->getConfig('mblock_copy_paste', 1)`
- In `settings.php`: `$this->getConfig('mblock_copy_paste', 1)`

## Benutzerfreundlichkeit
- **Standard**: Copy/Paste ist sofort verfügbar
- **Optional**: Kann jederzeit deaktiviert werden
- **Transparent**: Benutzer sieht sofort alle verfügbaren Funktionen

---
*Implementiert am 19. August 2025*
