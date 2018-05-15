<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Backend;

use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\System;

class ReaderConfigElement
{
    const TYPE_IMAGE = 'image';
    const TYPE_LIST = 'list';
    const TYPE_REDIRECTION = 'redirection';
    const TYPE_NAVIGATION = 'navigation';
    const TYPE_SYNDICATION = 'syndication';
    const TYPE_DELETE = 'delete';

    const TYPES = [
        self::TYPE_IMAGE,
        self::TYPE_LIST,
        self::TYPE_REDIRECTION,
        self::TYPE_NAVIGATION,
        self::TYPE_SYNDICATION,
        self::TYPE_DELETE,
    ];

    const REDIRECTION_PARAM_TYPE_DEFAULT_VALUE = 'default_value';
    const REDIRECTION_PARAM_TYPE_FIELD_VALUE = 'field_value';

    const REDIRECTION_PARAM_TYPES = [
        self::REDIRECTION_PARAM_TYPE_FIELD_VALUE,
        self::REDIRECTION_PARAM_TYPE_DEFAULT_VALUE,
    ];

    const PLACEHOLDER_IMAGE_MODE_NONE = 'none';
    const PLACEHOLDER_IMAGE_MODE_GENDERED = 'gendered';
    const PLACEHOLDER_IMAGE_MODE_SIMPLE = 'simple';

    const PLACEHOLDER_IMAGE_MODES = [
        self::PLACEHOLDER_IMAGE_MODE_GENDERED,
        self::PLACEHOLDER_IMAGE_MODE_SIMPLE,
    ];

    /** @var ContaoFrameworkInterface */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    public function listChildren($arrRow)
    {
        return '<div class="tl_content_left">'.($arrRow['title'] ?: $arrRow['id']).' <span style="color:#b3b3b3; padding-left:3px">['.\Date::parse(\Contao\Config::get('datimFormat'), trim($arrRow['dateAdded'])).']</span></div>';
    }

    public function checkPermission()
    {
        $user = BackendUser::getInstance();
        $database = Database::getInstance();

        if ($user->isAdmin) {
            return;
        }

        // Set the root IDs
        if (!is_array($user->readerbundles) || empty($user->readerbundles)) {
            $root = [0];
        } else {
            $root = $user->readerbundles;
        }

        $id = strlen(Input::get('id')) ? Input::get('id') : CURRENT_ID;

        // Check current action
        switch (Input::get('act')) {
            case 'paste':
                // Allow
                break;

            case 'create':
                if (!strlen(Input::get('pid')) || !in_array(Input::get('pid'), $root, true)) {
                    throw new AccessDeniedException('Not enough permissions to create reader_config_element items in reader_config_element archive ID '.Input::get('pid').'.');
                }
                break;

            case 'cut':
            case 'copy':
                if (!in_array(Input::get('pid'), $root, true)) {
                    throw new AccessDeniedException('Not enough permissions to '.Input::get('act').' reader_config_element item ID '.$id.' to reader_config_element archive ID '.Input::get('pid').'.');
                }
            // no break STATEMENT HERE

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
            case 'feature':
                $objArchive = $database->prepare('SELECT pid FROM tl_reader_config_element WHERE id=?')->limit(1)->execute($id);

                if ($objArchive->numRows < 1) {
                    throw new AccessDeniedException('Invalid reader_config_element item ID '.$id.'.');
                }

                if (!in_array($objArchive->pid, $root, true)) {
                    throw new AccessDeniedException('Not enough permissions to '.Input::get('act').' reader_config_element item ID '.$id.' of reader_config_element archive ID '.$objArchive->pid.'.');
                }
                break;

            case 'select':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!in_array($id, $root, true)) {
                    throw new AccessDeniedException('Not enough permissions to access reader_config_element archive ID '.$id.'.');
                }

                $objArchive = $database->prepare('SELECT id FROM tl_reader_config_element WHERE pid=?')->execute($id);

                if ($objArchive->numRows < 1) {
                    throw new AccessDeniedException('Invalid reader_config_element archive ID '.$id.'.');
                }

                /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $session */
                $session = System::getContainer()->get('session');

                $session = $session->all();
                $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objArchive->fetchEach('id'));
                $session->replace($session);
                break;

            default:
                if (strlen(Input::get('act'))) {
                    throw new AccessDeniedException('Invalid command "'.Input::get('act').'".');
                } elseif (!in_array($id, $root, true)) {
                    throw new AccessDeniedException('Not enough permissions to access reader_config_element archive ID '.$id.'.');
                }
                break;
        }
    }

    public function modifyPalette(DataContainer $dc)
    {
        if (null !== ($readerConfigElement = System::getContainer()->get('huh.reader.reader-config-element-registry')->findByPk($dc->id))) {
            if (null !== ($readerConfig = System::getContainer()->get('huh.reader.reader-config-registry')->findByPk($readerConfigElement->pid))) {
                $dca = &$GLOBALS['TL_DCA']['tl_reader_config_element'];

                $readerConfig = System::getContainer()->get('huh.utils.model')->findRootParentRecursively('parentReaderConfig', 'tl_reader_config', $readerConfig);

                if ($readerConfig->dataContainer) {
                    foreach (['redirectConditions', 'redirectParams'] as $field) {
                        $dca['fields'][$field]['eval']['multiColumnEditor']['table'] = $readerConfig->dataContainer;
                    }
                }
            }
        }
    }

    public function getFieldsAsOptions(DataContainer $dc)
    {
        if (!($table = $dc->table)) {
            return [];
        }

        Controller::loadDataContainer($table);

        if (isset($GLOBALS['TL_DCA'][$table]['fields'][$dc->field]['eval']['multiColumnEditor']['table'])) {
            $childTable = $GLOBALS['TL_DCA'][$table]['fields'][$dc->field]['eval']['multiColumnEditor']['table'];

            if (!$childTable) {
                return [];
            }

            $fields = System::getContainer()->get('huh.utils.dca')->getFields($childTable);

            return array_keys($fields);
        }
        throw new \Exception("No 'table' set in $dc->table.$dc->field's eval array.");
    }
}
