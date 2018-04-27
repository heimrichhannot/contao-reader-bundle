<?php

$lang = &$GLOBALS['TL_LANG']['tl_reader_config_element'];

/**
 * Fields
 */
$lang['tstamp'][0] = 'Änderungsdatum';
$lang['title'][0]  = 'Titel';
$lang['title'][1]  = 'Geben Sie hier einen Titel ein.';
$lang['type'][0]   = 'Typ';
$lang['type'][1]   = 'Wählen Sie hier den Typ des Elements aus.';

// image
$lang['imageSelectorField'][0]     = 'Selektor-Feld';
$lang['imageSelectorField'][1]     = 'Wählen Sie hier das Feld aus, das den boolschen Selektor für das Bild enthält.';
$lang['imageField'][0]             = 'Feld';
$lang['imageField'][1]             = 'Wählen Sie hier das Feld aus, das die Referenz zur Bilddatei enthält.';
$lang['placeholderImageMode'][0]   = 'Platzhalterbildmodus';
$lang['placeholderImageMode'][1]   = 'Wählen Sie diese Option, wenn Sie für den Fall, dass die ausgegebene Instanz kein Bild enthält, ein Platzhalterbild hinzufügen möchten.';
$lang['placeholderImage'][0]       = 'Platzhalterbild';
$lang['placeholderImage'][1]       = 'Wählen Sie hier ein Platzhalterbild aus.';
$lang['placeholderImageFemale'][0] = 'Platzhalterbild (weiblich)';
$lang['placeholderImageFemale'][1] = 'Wählen Sie hier ein Platzhalterbild für weibliche Instanzen aus.';
$lang['genderField'][0]            = 'Geschlecht-Feld';
$lang['genderField'][1]            = 'Wählen Sie hier das Feld aus, das das Geschlecht der Instanz enthält.';

$lang['listModule']    = ['Listen Modul', 'Wählen Sie ein Listen Module aus.'];
$lang['initialFilter'] = ['Initialer Filter', ''];
$lang['selector']      = ['Selektor', 'Wählen Sie ein Feld aus, von dem der Wert übergeben werden soll.'];
$lang['filterElement'] = ['Filterelement', 'Wählen Sie ein Feld aus, nachdem gefiltert werden soll.'];
$lang['listName']      = ['Listenname', 'Vergeben Sie einen einamligen Namen für Ihre Liste.'];

$lang['name']             = ['Name', 'Vergeben Sie einen einamligen Namen für Ihre Weiterleitung, welcher im Template als Variable ausgegeben wird.'];
$lang['redirection']      = ['Weiterleitungsseite', 'Wählen Sie eine Seite aus.'];
$lang['addRedirectParam'] = ['Parameter hinzufügen', 'Wählen Sie diese Option um an die entsprechende Weiterleitungsurl Parameter hinzuzufügen.'];
$lang['redirectParams']   = ['Weiterleitungsparameter', 'Wählen Sie Parameter aus, welche an die Url angehängt werden sollen.'];


// security
$lang['addRedirectConditions'][0]  = 'Bedingungen für die Anzeige hinzufügen';
$lang['addRedirectConditions'][1]  = 'Wählen Sie diese Option, wenn Datensätze nur unter bestimmten Bedingungen angezeigt werden dürfen.';
$lang['showRedirectConditions'][0] = 'Instanzbedingungen';
$lang['showRedirectConditions'][1] = 'Definieren Sie hier Bedingungen, die eine Instanz erfüllen muss, damit sie angezeigt wird.';

// navigation
$lang['navigationTemplate'][0] = 'Navigations-Template';
$lang['navigationTemplate'][1] = 'Wählen Sie hier ein individuelles Navigations-Template, dessen Dateiname mit `readernavigation_` beginnen muss.';

$lang['previousLabel'][0] = 'Navigations-Template';
$lang['previousLabel'][1] = 'Navigations-Template';

$lang['nextLabel'][0] = 'Label (nächstes Element)';
$lang['nextLabel'][1] = 'Wählen Sie ein individuelles Label für die Beschriftung des nächsten Element-Links.';

$lang['previousLabel'][0] = 'Label (vorheriges Element)';
$lang['previousLabel'][1] = 'Wählen Sie ein individuelles Label für die Beschriftung des vorherigen Element-Links.';

$lang['nextTitle'][0] = 'Titel/Tooltip (nächstes Element)';
$lang['nextTitle'][1] = 'Wählen Sie ein individuellen Titel für den Tooltip des nächsten Element-Links.';

$lang['previousTitle'][0] = 'Titel/Tooltip (vorheriges Element)';
$lang['previousTitle'][1] = 'Wählen Sie ein individuellen Titel für den Tooltip des vorherigen Element-Links.';

$lang['sortingField'][0] = 'Sortierfeld';
$lang['sortingField'][1] = 'Wählen Sie hier ein Sortierfeld aus.';

$lang['sortingDirection'][0] = 'Sortierreihenfolge';
$lang['sortingDirection'][1] = 'Wählen Sie eine Reihenfolge für die Sortierung aus.';

$lang['listConfig'][0] = 'Listen-Konfiguration';
$lang['listConfig'][1] = 'Wählen Sie eine individuelle Listen-Konfigration aus.';

$lang['infiniteNavigation'][0] = 'Endlosnavigation aktivieren';
$lang['infiniteNavigation'][1] = 'Endloses klicken durch die Nachrichtennavigation aktivieren.';


/**
 * Reference
 */
$lang['reference'] = [
    \HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement::TYPE_IMAGE                  => 'Bild',
    \HeimrichHannot\ListBundle\Backend\ListConfigElement::PLACEHOLDER_IMAGE_MODE_SIMPLE   => 'einfach',
    \HeimrichHannot\ListBundle\Backend\ListConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED => 'geschlechtsspezifisch',
    \HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement::TYPE_REDIRECTION            => 'Weiterleitung',
];

/**
 * Legends
 */
$lang['title_type_legend'] = 'Titel & Typ';
$lang['config_legend']     = 'Konfiguration';

/**
 * Buttons
 */
$lang['new']    = ['Neues Leserkonfigurations-Element', 'Leserkonfigurations-Element erstellen'];
$lang['edit']   = ['Leserkonfigurations-Element bearbeiten', 'Leserkonfigurations-Element ID %s bearbeiten'];
$lang['copy']   = ['Leserkonfigurations-Element duplizieren', 'Leserkonfigurations-Element ID %s duplizieren'];
$lang['delete'] = ['Leserkonfigurations-Element löschen', 'Leserkonfigurations-Element ID %s löschen'];
$lang['toggle'] = ['Leserkonfigurations-Element veröffentlichen', 'Leserkonfigurations-Element ID %s veröffentlichen/verstecken'];
$lang['show']   = ['Leserkonfigurations-Element Details', 'Leserkonfigurations-Element-Details ID %s anzeigen'];
