<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\StringUtil;
use HeimrichHannot\HeadBundle\Helper\DcaHelper;
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;
use HeimrichHannot\ReaderBundle\Event\ReaderBeforeRenderEvent;
use HeimrichHannot\UtilsBundle\Form\FormUtil;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class HeadBundleListener implements ServiceSubscriberInterface, EventSubscriberInterface
{
    private ContainerInterface $container;
    private RequestStack       $requestStack;

    public function __construct(ContainerInterface $container, RequestStack $requestStack)
    {
        $this->container = $container;
        $this->requestStack = $requestStack;
    }

    /**
     * @Hook("loadDataContainer")
     */
    public function onLoadDataContainer(string $table): void
    {
        if ('tl_reader_config' === $table && class_exists(DcaHelper::class) && $this->container->has(DcaHelper::class)) {
            /** @var DcaHelper $dcaHelper */
            $dcaHelper = $this->container->get(DcaHelper::class);

            $GLOBALS['TL_DCA'][$table]['fields']['headTags']['eval']['multiColumnEditor']['fields']['service']['options_callback'] =
                function () use ($dcaHelper) {
                    return $dcaHelper->getTagOptions([
                        'filter' => [DcaHelper::FILTER_META, DcaHelper::FILTER_TITLE],
                    ]);
                };
        }
    }

    public function onReaderBeforeRenderEvent(ReaderBeforeRenderEvent $event): void
    {
        if (!class_exists(DcaHelper::class) || !$this->container->has(DcaHelper::class)) {
            return;
        }

        $headTagManager = $this->container->get(HtmlHeadTagManager::class);

        // Tags
        $headTags = StringUtil::deserialize($event->getReaderConfig()->headTags, true);
        $item = $event->getItem();
        $dataContainer = $event->getItem()->getManager()->getDataContainer();

        foreach ($headTags as $row) {
            if (empty($row['service']) || \in_array($row['service'], ['title', 'huh.head.tag.title'])) {
                continue;
            }

            $name = $row['service'];
            $value = $row['pattern'] ?? '';

            $value = preg_replace_callback('@%([^%]+)%@i', function (array $matches) use ($item, $dataContainer) {
                if ($this->container->has(FormUtil::class)) {
                    return $this->container->get(FormUtil::class)->prepareSpecialValueForOutput(
                        $matches[1], $item->{$matches[1]}, $dataContainer
                    );
                }

                return $item->{$matches[1]};
            }, $value);

            if (!empty($value) && !\in_array($value, ['0', 0])) {
                $tag = $headTagManager->getHeadTagFactory()->createTagByName($name, $value);

                if ($tag) {
                    $headTagManager->addTag($tag);
                } elseif ($tag = $headTagManager->getLegacyTagManager()->getTagInstance($name)) {
                    $tag->setContent($value);
                } elseif ($tag = $headTagManager->getLegacyTagManager()->loadTagFromService($name)) {
                    $tag->setContent($value);
                    $headTagManager->getLegacyTagManager()->registerTag($tag);
                }
            }
        }

        // Canonical
        if (($detailUrl = $event->getItem()->getDetailsUrl()) && ($request = $this->requestStack->getCurrentRequest())) {
            if ($tag = $headTagManager->getLegacyTagManager()->getTagInstance('huh.head.tag.link_canonical')) {
                $tag->setContent($request->getSchemeAndHttpHost().'/'.ltrim($detailUrl, '/'));
            }
        }
    }

    public static function getSubscribedServices()
    {
        $services = [
            FormUtil::class,
        ];

        if (class_exists(DcaHelper::class)) {
            $services[] = DcaHelper::class;
            $services[] = HtmlHeadTagManager::class;
        }

        return $services;
    }

    public static function getSubscribedEvents()
    {
        return [
            ReaderBeforeRenderEvent::NAME => 'onReaderBeforeRenderEvent',
        ];
    }
}
