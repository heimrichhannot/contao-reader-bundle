<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$lang = &$GLOBALS['TL_LANG']['tl_reader_config'];

/*
 * Fields
 */
$lang['tstamp'][0] = 'Änderungsdatum';

// general
$lang['title'][0] = 'Titel';
$lang['title'][1] = 'Geben Sie hier bitte den Titel ein.';

// filter
$lang['filter'][0] = 'Filter';
$lang['filter'][1] = 'Bitte wählen Sie hier bei Bedarf einen Filter aus.';
$lang['evaluateFilter'] = [
    'Filter beim Instanzabruf beachten',
    'Wählen Sie diese Option, damit der Filter beim Instanzabruf beachtet wird. Es wird empfohlen, diese Option zu aktivieren, da sonst ohne weitere Vorkehrungen auf alle Datensätze zugegriffen werden kann.',
];

// config
$lang['dataContainer'][0] = 'Data-Container';
$lang['dataContainer'][1] = 'Wählen Sie hier bitte den Data-Container aus, dem die anzuzeigenden Entitäten angehören.';
$lang['manager'][0] = 'Manager-Service';
$lang['manager'][1] = 'Wählen Sie hier einen individuellen Manager-Service aus.';
$lang['item'][0] = 'Item-Klasse';
$lang['item'][1] = 'Wählen Sie hier eine individuelle Item-Klasse aus.';
$lang['limitFormattedFields'][0] = 'Formatierte Felder einschränken (Geschwindigkeit verbessern)';
$lang['limitFormattedFields'][1] = 'Wählen Sie diese Option, wenn nur bestimmte Felder auf Basis der Data-Containers-Konfiguration formatiert werden sollen möchten.';
$lang['formattedFields'][0] = 'Formatierte Felder';
$lang['formattedFields'][1] = 'Wählen Sie hier die zu formatierenden Felder aus.';
$lang['itemRetrievalMode'][0] = 'Instanzabruf-Modus';
$lang['itemRetrievalMode'][1] =
    'Die anzuzeigende Instanz kann über verschiedene Methoden bezogen werden. Wählen Sie hier bitte eine solche aus.';
$lang['itemRetrievalAutoItemField'][0] = 'Auto-Item-Feld';
$lang['itemRetrievalAutoItemField'][1] =
    'Wählen Sie hier das Data-Container-Feld aus, das mit dem gefundenen Auto-Item abgeglichen werden soll. Hinweis: Enthält das entsprechende Feld keinen Wert, erfolgt ein Fallback auf das Feld "ID".';
$lang['itemRetrievalFieldConditions'][0] = 'Instanzbedingungen';
$lang['itemRetrievalFieldConditions'][1] = 'Definieren Sie hier Bedingungen, die eine Instanz erfüllen muss, damit sie gefunden wird.';
$lang['hideUnpublishedItems'][0] = 'Unveröffentlichte Instanzen verstecken';
$lang['hideUnpublishedItems'][1] = 'Wählen Sie diese Option, um unveröffentlichte Instanzen zu verstecken.';
$lang['publishedField'][0] = '"Veröffentlicht"-Feld';
$lang['publishedField'][1] = 'Wählen Sie hier das Feld aus, in dem der Sichtbarkeitszustand gespeichert ist (z. B. "published").';
$lang['invertPublishedField'][0] = '"Veröffentlicht"-Feld negieren';
$lang['invertPublishedField'][1] =
    'Wählen Sie diese Option, wenn ein "wahr" im Veröffentlicht-Feld einem nichtöffentlichen Zustand entspricht.';
$lang['addStartAndStop'][0] = 'Start- und Stopfeld hinzufügen';
$lang['addStartAndStop'][1] = 'Wählen Sie diese Option, wenn Sie das Start- und Stopfeld beachten wollen.';
$lang['startField'][0] = 'Startfeld';
$lang['startField'][1] = 'Wählen Sie hier ein Feld aus.';
$lang['stopField'][0] = 'Stopfeld';
$lang['stopField'][1] = 'Wählen Sie hier ein Feld aus.';

// security
$lang['addShowConditions'][0] = 'Bedingungen für die Anzeige hinzufügen';
$lang['addShowConditions'][1] = 'Wählen Sie diese Option, wenn Datensätze nur unter bestimmten Bedingungen angezeigt werden dürfen.';
$lang['showFieldConditions'][0] = 'Instanzbedingungen';
$lang['showFieldConditions'][1] = 'Definieren Sie hier Bedingungen, die eine Instanz erfüllen muss, damit sie angezeigt wird.';

// jump to
$lang['addFieldDependentRedirect'][0] = 'Instanzfeldabhängige Weiterleitung hinzufügen';
$lang['addFieldDependentRedirect'][1] = 'Wählen Sie diese Option, wenn unter bestimmten Bedingungen eine Weiterleitung erfolgen soll.';
$lang['redirectFieldConditions'][0] = 'Instanzbedingungen';
$lang['redirectFieldConditions'][1] = 'Definieren Sie hier Bedingungen, die eine Instanz erfüllen muss, damit weitergeleitet wird.';
$lang['fieldDependentJumpTo'][0] = 'Weiterleitungsseite';
$lang['fieldDependentJumpTo'][1] = 'Definieren Sie hier eine Weiterleitungsseite.';
$lang['addOverview'][0] = 'Link zur Übersichtsseite hinzufügen';
$lang['addOverview'][1] = 'Wählen Sie diese Option aus, um der Liste einen Link zur Übersichtsseite hinzuzufügen.';
$lang['jumpToOverview'][0] = 'Übersichtsseite';
$lang['jumpToOverview'][1] = '';
$lang['customJumpToOverviewLabel'][0] = 'Label für "zur Übersicht" überschreiben';
$lang['customJumpToOverviewLabel'][1] = '';
$lang['jumpToOverviewLabel'][0] = 'Label für "zur Übersicht"';
$lang['jumpToOverviewLabel'][1] = '';
$lang['overviewMode'][0] = 'Link-Modus';
$lang['overviewMode'][1] = 'Wählen Sie hier den Modus, nachdem der Link erzeugt wird.';
$lang['overviewMode']['reference']['history'] = 'vorherige Seite';
$lang['overviewMode']['reference']['jumpTo'] = 'Seite definieren';
$lang['disable404'][0] = '404-Weiterleitung deaktivieren';
$lang['disable404'][1] = 'Wählen Sie diese Option, wenn nicht zu einer 404-Seite weitergeleitet werden soll, wenn kein Item gefunden wurde.';

// misc
$lang['headTags'][0] = 'Meta- /Head-Tags';
$lang['headTags'][1] = 'Legen Sie fest, welche Inhalte der Instanz in welchen Head-Tag (title, meta) überführt werden sollen.';
$lang['headTags_service'][0] = 'Tag';
$lang['headTags_service'][1] = 'Wählen Sie einen verfügbaren Tag aus.';
$lang['headTags_pattern'][0] = 'Inhalt-Pattern';
$lang['headTags_pattern'][1] = 'Geben Sie hier den Inhalt des Tags an. Sie können Feld-Werte nutzen, in dem sie den Feldnamen in % setzen. (Beispiel: "%somefield1% %somefield2%").';

$lang['addDcMultilingualSupport'][0] = 'Support für DC_Multilingual hinzufügen';
$lang['addDcMultilingualSupport'][1] = 'Wählen Sie diese Option, die verknüpfte Entität durch das Bundle "terminal42/contao-DC_Multilingual" übersetzbar ist.';

$lang['addMultilingualFieldsSupport'][0] = 'Support für "Multilingual Fields Bundle" hinzufügen';
$lang['addMultilingualFieldsSupport'][1] = 'Wählen Sie diese Option, die verknüpfte Entität durch das Bundle "heimrichhannot/contao-multilingual-fields-bundle" übersetzbar ist.';

// template
$lang['itemTemplate'][0] = 'Instanz-Template';
$lang['itemTemplate'][1] = 'Wählen Sie hier das Template aus, mit dem die einzelnen Instanzen gerendert werden sollen.';

/*
 * Reference
 */
$lang['reference'] = [
    \HeimrichHannot\ReaderBundle\Backend\ReaderConfig::ITEM_RETRIEVAL_MODE_AUTO_ITEM => 'Auto-Item',
    \HeimrichHannot\ReaderBundle\Backend\ReaderConfig::ITEM_RETRIEVAL_MODE_FIELD_CONDITIONS => 'Instanzfeld-Bedingungen',
    'sortAlphabeticallyConfirm' => 'Möchten Sie wirklich fortfahren? Diese Aktion kann nur schwer rückgängig gemacht werden.',
];

/*
 * Select
 */
$lang['generateSorting'] = 'Alphabetisch sortieren';

/*
 * Legends
 */
$lang['general_legend'] = 'Allgemeine Einstellungen';
$lang['filter_legend'] = 'Filtereinstellungen';
$lang['config_legend'] = 'Konfiguration';
$lang['fields_legend'] = 'Feldeinstellungen';
$lang['security_legend'] = 'Sicherheit';
$lang['jumpto_legend'] = 'Weiterleitung';
$lang['misc_legend'] = 'Verschiedenes';
$lang['template_legend'] = 'Template';

/*
 * Buttons
 */
$lang['new'] = ['Neue Leserkonfiguration', 'Leserkonfiguration erstellen'];
$lang['edit'] = ['Leserkonfiguration bearbeiten', 'Leserkonfiguration ID %s bearbeiten'];
$lang['editheader'] = ['Leserkonfiguration-Einstellungen bearbeiten', 'Leserkonfiguration-Einstellungen ID %s bearbeiten'];
$lang['copy'] = ['Leserkonfiguration duplizieren', 'Leserkonfiguration ID %s duplizieren'];
$lang['delete'] = ['Leserkonfiguration löschen', 'Leserkonfiguration ID %s löschen'];
$lang['toggle'] = ['Leserkonfiguration veröffentlichen', 'Leserkonfiguration ID %s veröffentlichen/verstecken'];
$lang['show'] = ['Leserkonfiguration Details', 'Leserkonfiguration-Details ID %s anzeigen'];
$lang['sortAlphabetically'] = ['Alphabetisch sortieren', 'Leserkonfigurationeb alphabetisch sortieren'];
