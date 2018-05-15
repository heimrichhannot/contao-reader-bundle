<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType\Delete;

use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FrontendUser;
use Contao\Model;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

class DefaultDelete implements DeleteInterface
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    public function delete(ItemInterface $item, ReaderConfigElementModel $readerConfigElement)
    {
        if ($readerConfigElement->addMemberGroups && !$this->checkPermission($readerConfigElement->memberGroups)) {
            return;
        }

        $readerConfig = System::getContainer()->get('huh.reader.reader-config-registry')->findByPk($readerConfigElement->pid);

        if (null === $readerConfig) {
            return;
        }

        $redirectParams = StringUtil::deserialize($readerConfigElement->redirectParams, true);

        $values = [];
        $columns = [];

        foreach ($redirectParams as $redirectParam) {
            if (ReaderConfigElement::REDIRECTION_PARAM_TYPE_FIELD_VALUE !== $redirectParam['parameterType'] || !System::getContainer()->get('huh.request')->hasGet($redirectParam['name'])) {
                continue;
            }
            $columns[] = $redirectParam['name'];
            $values[] = System::getContainer()->get('huh.request')->getGet($redirectParam['name']);
        }

        /** @var Model $model */
        $model = System::getContainer()->get('huh.utils.model')->findOneModelInstanceBy($readerConfig->dataContainer, $columns, $values);

        if (null === $model) {
            return;
        }

        if (!$model->delete()) {
            throw new \Exception('Could not delete Model.');
        }

        $this->redirectAfterDelete($readerConfigElement->deleteJumpTo);
    }

    /**
     * @param $jumpTo
     */
    protected function redirectAfterDelete($jumpTo)
    {
        /** @var PageModel $pageModel */
        $pageModel = $this->framework->getAdapter(PageModel::class)->findPublishedById($jumpTo);
        if (null === $pageModel) {
            throw new PageNotFoundException('The redirect page with the id:'.$jumpTo.'does not exist.');
        }
        System::getContainer()->get('huh.utils.url')->redirect($pageModel->getFrontendUrl());
    }

    /**
     * check if the frontend user is allowed to delete the item.
     *
     * @param $memberGroups
     *
     * @return bool
     */
    protected function checkPermission($memberGroups)
    {
        $memberGroups = StringUtil::deserialize($memberGroups, true);

        /** @var FrontendUser $user */
        $user = $this->framework->createInstance(FrontendUser::class);

        if (array_intersect($user->groups, $memberGroups)) {
            return true;
        }

        return false;
    }
}
