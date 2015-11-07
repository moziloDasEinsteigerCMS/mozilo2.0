# moziloCMS

moziloCMS ist ein einfaches und einsteigerfreundliches Content-Management-System (CMS) für Benutzer mit geringen HTML-Kenntnissen.

## Vorraussetzungen
Ein Webserver mit PHP 5.1.2 oder höher ist notwendig. Eine Datenbank wird nicht benötigt, da moziloCMS ein Flat-file CMS ist (alle Daten werden in einfachen Dateien gespeichert).

## Achtung Nutzer die ein Update machen
ab Revision 41 hat sich der Pfad zum Download von Dateien geändert.

Wer möchte das die Links zum Download Angebotener Dateien in den Suchmaschinen weiterhin funktionieren, hat folgende Möglichkeiten.

1. in der .htaccess nach `# mozilo_end` folgende Zeile Hinzufügen `RewriteRule download\.php$ index\.php [QSA,L]`
2. oder die `cms/download.php` Sichern und nach dem Update wieder zurück Kopieren und in der `cms/CatPageClass.php` die Raute vor `# return URL_BASE.CMS_DIR_NAME.'/download.php?cat='.$cat.'&amp;file='.$datei.$open_dialog;` entfernen.

## Installation
1. moziloCMS 2.0 [herunterladen](https://github.com/mozilo/mozilo2.0/archive/master.zip), ggf. entpacken und auf den eigenen Webserver hochladen
2. Die `install.php` aufrufen, z.B.:
  * `http://www.deineSeite/install.php` oder
  * `http://www.deineSeite/moziloCMS/install.php`
3. Der Installationsanleitung folgen

**Wichtig**: Nach erfolgreicher Installation (falls noch vorhanden) löschen:
* die `install.php`
* die `update.php`
* und den Ordner `update/`

## Update von 1.12 auf 2.0
* moziloCMS 2.0 [herunterladen](https://github.com/mozilo/mozilo2.0/archive/master.zip) und entpacken
* Folgende Ordnerinhalte in 2.0 durch entsprechenden Inhalte der alten 1.12 Installation ersetzen:
  * `kategorien/`
  * `galerien/`
  * `layouts/`
* Im 2.0 Wurzelverzeichnis den Ordner `update/` erstellen
* Folgende `.conf` Dateien (falls vorhanden) von 1.12 in den Ordner `update.php` kopieren:
* `admin/conf/` (alle `.conf` Dateien)
* `cms/conf/` (alle `.conf` Dateien)
* `cms/formular/formular.conf`
* *Optional*: Falls bisher genutzte Plugins auch in 2.0 vorhanden sind, können die Einstellungen von 1.12-Plugins ebenfalls übernommen werden:
  * Entsprechende 2.0 Plugins downloaden und in den `plugins/` Ordner kopieren
  * Die `plugin.conf.php` in dem jeweiligen 2.0-Plugin durch die plugin.conf der 1.12-Version ersetzen (bitte kein .php dahinter setzen)
* Jetzt mit Schritt 2 der Installationsanleitung fortfahren.

Die oben genanten Schritte können auch einzeln durchgeführt werden. Die `install.php` kann so oft Ausgeführt werden wie mann möchte.

www.mozilo.de/forum für Fragen und Antworten

## Lizenz
moziloCMS ist OpenSource-Software und steht unter der [General Public License (GPL)](http://www.fsf.org/licensing/licenses/gpl.txt). Das heißt, es darf für alle Zwecke kostenlos genutzt, beliebig verändert und weiterverbreitet werden; veränderte Versionen unterliegen wieder der GPL.
