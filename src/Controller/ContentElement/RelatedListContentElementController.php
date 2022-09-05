<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\Template;
use HeimrichHannot\ReaderBundle\Generator\RelatedListGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ContentElement(category="list")
 */
class RelatedListContentElementController extends AbstractContentElementController
{
    const TYPE = 'related_list_content_element';

    private RelatedListGenerator $relatedListGenerator;

    public function __construct(RelatedListGenerator $relatedListGenerator)
    {
        $this->relatedListGenerator = $relatedListGenerator;
    }

    protected function getResponse(Template $template, ContentModel $model, Request $request): ?Response
    {
//        $this->relatedListGenerator->generate();

        return $template->getResponse();
    }
}
