<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\DataContainer;

use Contao\Controller;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\RequestToken;
use Contao\Versions;
use HeimrichHannot\TwigSupportBundle\Filesystem\TwigTemplateLocator;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use Symfony\Component\HttpFoundation\RequestStack;

class ReaderConfigContainer
{
    /**
     * @var array
     */
    protected $bundleConfig;
    /**
     * @var TwigTemplateLocator
     */
    protected $templateLocator;

    /**
     * @var ModelUtil
     */
    protected $modelUtil;
    /**
     * @var UrlUtil
     */
    protected $urlUtil;
    private RequestStack $requestStack;

    public function __construct(array $bundleConfig, TwigTemplateLocator $templateLocator, ModelUtil $modelUtil, UrlUtil $urlUtil, RequestStack $requestStack)
    {
        $this->bundleConfig = $bundleConfig;
        $this->templateLocator = $templateLocator;
        $this->modelUtil = $modelUtil;
        $this->urlUtil = $urlUtil;
        $this->requestStack = $requestStack;
    }

    public function onItemTemplateOptionsCallback()
    {
        $choices = [];

        if (isset($this->bundleConfig['templates']['item_prefixes'])) {
            $choices = $this->templateLocator->getTemplateGroup($this->bundleConfig['templates']['item_prefixes']);
        }

        if (isset($this->bundleConfig['templates']['item'])) {
            foreach ($this->bundleConfig['templates']['item'] as $template) {
                if (!isset($template['template']) || !isset($template['name'])) {
                    continue;
                }
                // remove duplicates returned by `huh.utils.choice.twig_template`
                if (false !== ($idx = array_search($template['template'], $choices, true))) {
                    unset($choices[$idx]);
                }

                $choices[$template['name']] = $template['template'].' (Yaml)';
            }
        }

        asort($choices);

        return $choices;
    }

    public function sortAlphabetically()
    {
        // sort alphabetically
        if ('sortAlphabetically' === $this->requestStack->getCurrentRequest()->query->get('key')) {
            if (null !== ($readerConfigs = $this->modelUtil->findAllModelInstances('tl_reader_config', [
                    'order' => 'title ASC',
                ]))) {
                $sorting = 64;

                while ($readerConfigs->next()) {
                    $sorting += 64;

                    $readerConfig = $readerConfigs->current();

                    // The sorting has not changed
                    if ($sorting == $readerConfig->sorting) {
                        continue;
                    }

                    // Initialize the version manager
                    $versions = new Versions('tl_reader_config', $readerConfig->id);
                    $versions->initialize();

                    // Store the new alias
                    Database::getInstance()->prepare('UPDATE tl_reader_config SET sorting=? WHERE id=?')
                        ->execute($sorting, $readerConfig->id);

                    // Create a new version
                    $versions->create();
                }
            }

            throw new RedirectResponseException($this->urlUtil->removeQueryString(['key']));
        }

        return '<a href="'.$this->urlUtil->addQueryString('key=sortAlphabetically').'" class="header_new" style="background-image: url(system/themes/flexible/icons/rows.svg)" title="'.$GLOBALS['TL_LANG']['tl_reader_config']['sortAlphabetically'][1].'" accesskey="n" onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['tl_reader_config']['reference']['sortAlphabeticallyConfirm'].'\'))return false;Backend.getScrollOffset()">'.$GLOBALS['TL_LANG']['tl_reader_config']['sortAlphabetically'][0].'</a>';
    }

    public function pasteReaderConfig(DataContainer $dc, $row, $table, $cr, $arrClipboard = null)
    {
        $disablePA = false;
        $disablePI = false;

        // Disable all buttons if there is a circular reference
        if (false !== $arrClipboard && ('cut' === $arrClipboard['mode'] && (1 === $cr || $arrClipboard['id'] === $row['id']) || 'cutAll' === $arrClipboard['mode'] && (1 === $cr || \in_array($row['id'], $arrClipboard['id'], true)))) {
            $disablePA = true;
            $disablePI = true;
        }

        $return = '';

        // Return the buttons
        $imagePasteAfter = Image::getHtml('pasteafter.svg', sprintf($GLOBALS['TL_LANG']['DCA']['pasteafter'][1], $row['id']));
        $imagePasteInto = Image::getHtml('pasteinto.svg', sprintf($GLOBALS['TL_LANG']['DCA']['pasteinto'][1], $row['id']));

        if ($row['id'] > 0) {
            $return = $disablePA ? Image::getHtml('pasteafter_.svg').' ' : '<a href="'.Controller::addToUrl('act='.$arrClipboard['mode'].'&mode=1&rt='.RequestToken::get().'&pid='.$row['id'].(!\is_array($arrClipboard['id']) ? '&id='.$arrClipboard['id'] : '')).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG']['DCA']['pasteafter'][1],
                    $row['id'])).'" onclick="Backend.getScrollOffset()">'.$imagePasteAfter.'</a> ';
        }

        return $return.($disablePI ? Image::getHtml('pasteinto_.svg').' ' : '<a href="'.Controller::addToUrl('act='.$arrClipboard['mode'].'&mode=2&rt='.RequestToken::get().'&pid='.$row['id'].(!\is_array($arrClipboard['id']) ? '&id='.$arrClipboard['id'] : '')).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG']['DCA']['pasteinto'][1],
                    $row['id'])).'" onclick="Backend.getScrollOffset()">'.$imagePasteInto.'</a> ');
    }
}
