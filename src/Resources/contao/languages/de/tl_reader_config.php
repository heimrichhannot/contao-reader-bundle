<?php

$lang = &$GLOBALS['TL_LANG']['tl_reader_config'];

/**
 * Fields
 */
$lang['tstamp'][0] = 'Änderungsdatum';

// general
$lang['title'][0] = 'Titel';
$lang['title'][1] = 'Geben Sie hier bitte den Titel ein.';

// config
$lang['dataContainer'][0]              = 'Data-Container';
$lang['dataContainer'][1]              = 'Wählen Sie hier bitte den Data-Container aus, dem die anzuzeigenden Entitäten angehören.';
$lang['limitFields'][0]                = 'Verarbeitete Felder einschränken';
$lang['limitFields'][1]                = 'Wählen Sie diese Option, wenn nicht alle Felder des Data-Containers verarbeitet werden sollen.';
$lang['fields'][0]                     = 'Felder';
$lang['fields'][1]                     = 'Wählen Sie hier die zu verarbeitenden Felder aus.';
$lang['itemRetrievalMode'][0]          = 'Instanzabruf-Modus';
$lang['itemRetrievalMode'][1]          = 'Die anzuzeigende Instanz kann über verschiedene Methoden bezogen werden. Wählen Sie hier bitte eine solche aus.';
$lang['itemRetrievalAutoItemField'][0] = 'Auto-Item-Feld (Fallback: "ID")';
$lang['itemRetrievalAutoItemField'][1] = 'Wählen Sie hier das Data-Container-Feld aus, das mit dem gefundenen Auto-Item abgeglichen werden soll. Hinweis: Enthält das entsprechende Feld keinen Wert, erfolgt ein Fallback auf das Feld "ID".';
$lang['hideUnpublishedItems'][0]       = 'Unveröffentlichte Instanzen verstecken';
$lang['hideUnpublishedItems'][1]       = 'Wählen Sie diese Option, um unveröffentlichte Instanzen zu verstecken.';
$lang['publishedField'][0]             = '"Veröffentlicht"-Feld';
$lang['publishedField'][1]             = 'Wählen Sie hier das Feld aus, in dem der Sichtbarkeitszustand gespeichert ist (z. B. "published").';
$lang['invertPublishedField'][0]       = '"Veröffentlicht"-Feld negieren';
$lang['invertPublishedField'][1]       = 'Wählen Sie diese Option, wenn ein "wahr" im Veröffentlicht-Feld einem nichtöffentlichen Zustand entspricht.';

// security
$lang['addShowConditions'][0]  = 'Bedingungen für die Anzeige hinzufügen';
$lang['addShowConditions'][1]  = 'Wählen Sie diese Option, wenn Datensätze nur unter bestimmten Bedingungen angezeigt werden dürfen.';
$lang['showItemConditions'][0] = 'Instanzbedingungen';
$lang['showItemConditions'][1] = 'Definieren Sie hier Bedingungen, die eine Instanz erfüllen muss, damit sie angezeigt wird.';

// template
$lang['itemTemplate'][0] = 'Instanz-Template';
$lang['itemTemplate'][1] = 'Wählen Sie hier das Template aus, mit dem die einzelnen Instanzen gerendert werden sollen.';

/**
 * Reference
 */
$lang['reference'] = [
    \HeimrichHannot\ReaderBundle\Backend\ReaderConfig::ITEM_RETRIEVAL_MODE_AUTO_ITEM => 'Auto-Item'
];

/**
 * Legends
 */
$lang['general_legend']  = 'Allgemeine Einstellungen';
$lang['config_legend']   = 'Konfiguration';
$lang['security_legend'] = 'Sicherheit';
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