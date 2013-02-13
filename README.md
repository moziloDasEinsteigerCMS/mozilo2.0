Installations-Anleitung von moziloCMS2.0
und Update-Anleitung moziloCMS1.12 auf moziloCMS2.0

---

Installation von moziloCMS2.0

Nach dem entpacken von moziloCMS2.0 must du die install.php aufrufen:
* http://www.deineSeite/install.php  
* oder: http://www.deineSeite/moziloCMS/install.php

!!"Wichtig"!!: nachdem die Installation abgeschlossen ist "lösche" auf jeden Fall:
* die install.php
* die update.php
* und den Ordner update

---

Update von moziloCMS1.12 auf moziloCMS2.0

Nach dem entpacken von moziloCMS2.0:
* alle Kategorien mit deren Inhalt, in den 2.0-Ordner "kategorien" kopieren
* die Gallerien in den 2.0er Ordner "gallerien" kopieren
* die Layouts in den 2.0er Ordner "layouts" kopieren
* nun im Wurzelverzeichnis (beispielsweise: moziloCMS/) den Ordner "update" erstellen
* in diesen Ordner "update", alle ".conf" dateien aus ".../admin/conf" und ".../cms/conf" und wenn vorhanden ".../formular/formular.conf" kopieren

---
* wenn bisher genutzte Plugins auch in moziloCMS2.0 vorhanden sind, können die Einstellungen von moziloCMS1.12-Plugins ebenfalls upgedatet werden:
* dazu müssen die Plugins natürlich schon im Plugin-Ordner "moziloCMS/plugins" vorhanden sein.
* die plugin.conf.php in dem jeweiligen 2.0-Plugin durch die plugin.conf der 1.12-Version ersetzen \(bitte kein .php dahinter setzen\)

---

Jetzt musst du die install.php aufrufen
* z.B.: http://www.deineSeite/install.php
* oder: http://www.deineSeite/moziloCMS/install.php

Die oben genanten Schritte können auch einzeln durchgeführt werden
Die install.php kann so oft Ausgeführt werden wie mann möchte.

!!"Wichtig"!!: nachdem die Installation abgeschlossen ist "lösche" auf jeden Fall:
* die install.php
* die update.php
* und den Ordner update

www.mozilo.de/forum für Fragen und Antworten
