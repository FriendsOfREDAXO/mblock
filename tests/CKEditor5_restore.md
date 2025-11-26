# CKEditor5 (CKE5) - Manual test: unsaved / new block restore

Ziel: Sicherstellen, dass beim Kopieren / Einfügen von MBlock-Items mit CKEditor5-Editoren der Inhalt auch für neue/unsaved-Editoren wiederhergestellt wird.

Testschritte (manuell):

1. Backend: Erstelle ein MBlock-Modul mit einem CKEditor5-Feld und lege ein Element an, das noch nicht gespeichert wurde (z.B. in einer neuen Zeile).
2. Gib in zwei verschiedenen CKEditor5-Instanzen unterschiedlichen Text ein (z. B. "Unsave A" und "Unsaved B"). Stelle sicher, dass mindestens eines der Editor-Elemente keinen `name`-Attribut-Wert oder keinen stabilen Namen hat (unsaved/new scenario).
3. Kopiere das komplette MBlock-Item (Button Kopieren) – das Script sollte `formData` inklusive `._cke5_array` speichern.
4. Füge das Item an einer anderen Stelle ein (Paste). Prüfe, ob sowohl benannte CKEditor-Felder als auch unbenannte/unsaved CKEditor5-Instanzen den richtigen Inhalt erhalten.

Akzeptanzkriterien:
- Benannte CKE5-Felder werden via Name wiederhergestellt (falls gefunden)
- Neue/unsaved CKE5-Felder ohne Name erhalten die Inhalte positionsbasiert aus `_cke5_array`
- CKEditor4-Instanzen (falls vorhanden) werden wie vorher via `window.CKEDITOR.instances[id].setData(...)` befüllt

Hinweis zur Reproduzierbarkeit:
- Das feature wurde implementiert in `assets/mblock.js` (capture & restore) und wird auch in `public/assets/addons/mblock/mblock.min.js` bereitgestellt, wenn die Public-Minification ausgeführt wird.
