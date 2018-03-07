<?php

$lang = &$GLOBALS['TL_LANG']['tl_reader_config'];

/**
 * Fields
 */
$lang['tstamp'][0] = 'Änderungsdatum';

// general
$lang['title'][0]              = 'Titel';
$lang['title'][1]              = 'Geben Sie hier bitte den Titel ein.';
$lang['parentReaderConfig'][0] = 'Eltern-Leserkonfiguration';
$lang['parentReaderConfig'][1] = 'Wählen Sie hier eine Leserkonfiguration aus, von der geerbt werden soll. Sie können dann punktuell einzelne Eigenschaften überschreiben.';

// config
$lang['dataContainer'][0]                = 'Data-Container';
$lang['dataContainer'][1]                = 'Wählen Sie hier bitte den Data-Container aus, dem die anzuzeigenden Entitäten angehören.';
$lang['manager'][0]                      = 'Manager';
$lang['manager'][1]                      = 'Wählen Sie hier eine individuelle Manager-Klasse oder Service aus.';
$lang['item'][0]                         = 'Item';
$lang['item'][1]                         = 'Wählen Sie hier eine individuelle Item-Klasse oder Service aus.';
$lang['limitFormattedFields'][0]         = 'Formatierte Felder einschränken (Geschwindigkeit verbessern)';
$lang['limitFormattedFields'][1]         = 'Wählen Sie diese Option, wenn nur bestimmte Felder auf Basis der Data-Containers-Konfiguration formatiert werden sollen möchten.';
$lang['formattedFields'][0]              = 'Formatierte Felder';
$lang['formattedFields'][1]              = 'Wählen Sie hier die zu formatierenden Felder aus.';
$lang['itemRetrievalMode'][0]            = 'Instanzabruf-Modus';
$lang['itemRetrievalMode'][1]            =
    'Die anzuzeigende Instanz kann über verschiedene Methoden bezogen werden. Wählen Sie hier bitte eine solche aus.';
$lang['itemRetrievalAutoItemField'][0]   = 'Auto-Item-Feld';
$lang['itemRetrievalAutoItemField'][1]   =
    'Wählen Sie hier das Data-Container-Feld aus, das mit dem gefundenen Auto-Item abgeglichen werden soll. Hinweis: Enthält das entsprechende Feld keinen Wert, erfolgt ein Fallback auf das Feld "ID".';
$lang['itemRetrievalFieldConditions'][0] = 'Instanzbedingungen';
$lang['itemRetrievalFieldConditions'][1] = 'Definieren Sie hier Bedingungen, die eine Instanz erfüllen muss, damit sie gefunden wird.';
$lang['hideUnpublishedItems'][0]         = 'Unveröffentlichte Instanzen verstecken';
$lang['hideUnpublishedItems'][1]         = 'Wählen Sie diese Option, um unveröffentlichte Instanzen zu verstecken.';
$lang['publishedField'][0]               = '"Veröffentlicht"-Feld';
$lang['publishedField'][1]               = 'Wählen Sie hier das Feld aus, in dem der Sichtbarkeitszustand gespeichert ist (z. B. "published").';
$lang['invertPublishedField'][0]         = '"Veröffentlicht"-Feld negieren';
$lang['invertPublishedField'][1]         =
    'Wählen Sie diese Option, wenn ein "wahr" im Veröffentlicht-Feld einem nichtöffentlichen Zustand entspricht.';

// security
$lang['addShowConditions'][0]   = 'Bedingungen für die Anzeige hinzufügen';
$lang['addShowConditions'][1]   = 'Wählen Sie diese Option, wenn Datensätze nur unter bestimmten Bedingungen angezeigt werden dürfen.';
$lang['showFieldConditions'][0] = 'Instanzbedingungen';
$lang['showFieldConditions'][1] = 'Definieren Sie hier Bedingungen, die eine Instanz erfüllen muss, damit sie angezeigt wird.';

// jump to
$lang['addFieldDependentRedirect'][0] = 'Instanzfeldabhängige Weiterleitung hinzufügen';
$lang['addFieldDependentRedirect'][1] = 'Wählen Sie diese Option, wenn unter bestimmten Bedingungen eine Weiterleitung erfolgen soll.';
$lang['redirectFieldConditions'][0]   = 'Instanzbedingungen';
$lang['redirectFieldConditions'][1]   = 'Definieren Sie hier Bedingungen, die eine Instanz erfüllen muss, damit weitergeleitet wird.';
$lang['fieldDependentJumpTo'][0]      = 'Weiterleitungsseite';
$lang['fieldDependentJumpTo'][1]      = 'Definieren Sie hier eine Weiterleitungsseite.';

// misc
$lang['setPageTitleByField'][0]   = 'Instanzfeld als Seitentitel setzen';
$lang['setPageTitleByField'][1]   = 'Wählen Sie diese Option, wenn der Seitentitel dynamisch mit einem Instanzfeld ersetzt werden soll.';
$lang['pageTitleFieldPattern'][0] = 'Instanzfeld-Muster';
$lang['pageTitleFieldPattern'][1] = 'Geben Sie hier ein Text-Muster Seitentitel ein (Beispiel: "%somefield1% %somefield2%").';

// template
$lang['itemTemplate'][0] = 'Instanz-Template';
$lang['itemTemplate'][1] = 'Wählen Sie hier das Template aus, mit dem die einzelnen Instanzen gerendert werden sollen.';

/**
 * Reference
 */
$lang['reference'] = [
    \HeimrichHannot\ReaderBundle\Backend\ReaderConfig::ITEM_RETRIEVAL_MODE_AUTO_ITEM        => 'Auto-Item',
    \HeimrichHannot\ReaderBundle\Backend\ReaderConfig::ITEM_RETRIEVAL_MODE_FIELD_CONDITIONS => 'Instanzfeld-Bedingungen'
];

/**
 * Legends
 */
$lang['general_legend']  = 'Allgemeine Einstellungen';
$lang['config_legend']   = 'Konfiguration';
$lang['security_legend'] = 'Sicherheit';
$lang['jumpto_legend']   = 'Weiterleitung';
$lang['misc_legend']     = 'Verschiedenes';
$lang['template_legend'] = 'Template';

/**
 * Buttons
 */
$lang['new']        = ['Neue Leserkonfiguration', 'Leserkonfiguration erstellen'];
$lang['edit']       = ['Leserkonfiguration bearbeiten', 'Leserkonfiguration ID %s bearbeiten'];
$lang['editheader'] = ['Leserkonfiguration-Einstellungen bearbeiten', 'Leserkonfiguration-Einstellungen ID %s bearbeiten'];
$lang['copy']       = ['Leserkonfiguration duplizieren', 'Leserkonfiguration ID %s duplizieren'];
$lang['delete']     = ['Leserkonfiguration löschen', 'Leserkonfiguration ID %s löschen'];
$lang['toggle']     = ['Leserkonfiguration veröffentlichen', 'Leserkonfiguration ID %s veröffentlichen/verstecken'];
$lang['show']       = ['Leserkonfiguration Details', 'Leserkonfiguration-Details ID %s anzeigen'];