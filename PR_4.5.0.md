# PR: Vorbereitung für Branch 4.5.0

Änderungen in dieser PR (4.5.0):

- Runtime fallback für Sortable.js (bevorzugt bloecks' Sortable, ansonsten lokale `assets/sortable.min.js`) — serverseitiger Link via `boot.php` + clientseitiges `mblock_ensure_sortable()` (bereits gepusht).
- CKEditor5 (CKE5): verbessertes Copy/Paste-Verhalten — unsaved/new editors werden positionsbasiert in `formData._cke5_array` gespeichert, und beim Einfügen wiederhergestellt. (assets/mblock.js)
- Font Awesome 6: Alle rex-icon Fallbacks entfernt; Addon-Icon in `package.yml` sowie diverse Backend- und JS-Icons auf FA6 umgestellt.
- Tests: Manual Test-Anleitung zu CKEditor5-restore hinzugefügt (`tests/CKEditor5_restore.md`).

Weitere Hinweise:
- Öffentliche/minified Assets wurden aktualisiert für die Public-Distribution (`public/assets/addons/mblock/mblock.min.js`) — das Addon-internen Minify-Script (`build/minify.js`) kann lokal anders fehlschlagen bei bestimmten Node/Terser-Versionen; die Public-Minified-Datei ist aktuell.
- Bitte testen im Backend: Copy/Paste mit CKEditor5-Instanzen, insbesondere bei neuen/unsaved Items.
