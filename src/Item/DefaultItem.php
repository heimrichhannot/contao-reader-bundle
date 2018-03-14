<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Item;

use Contao\DataContainer;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ReaderBundle\ConfigElementType\ConfigElementType;
use HeimrichHannot\ReaderBundle\Manager\ReaderManagerInterface;
use HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils;

class DefaultItem implements ItemInterface, \JsonSerializable
{
    /**
     * Current Item Manager.
     *
     * @var ReaderManagerInterface
     */
    protected $_manager;

    /**
     * Current item data.
     *
     * @var array
     */
    protected $_raw = [];

    /**
     * Current formatted data.
     *
     * @var array
     */
    protected $_formatted = [];

    /**
     * @var DataContainer
     */
    protected $_dc;

    /**
     * DefaultItem constructor.
     *
     * @param ReaderManagerInterface $_manager
     * @param array                  $data     Raw item data
     */
    public function __construct(ReaderManagerInterface $_manager, array $data = [])
    {
        $this->_manager = $_manager;
        $this->setRaw($data);
    }

    /**
     * Magic getter.
     *
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        if (isset($this->_raw[$name])) {
            return $this->_raw[$name];
        }

        return null;
    }

    /**
     * Magic setter.
     *
     * @param string $name
     * @param $value
     */
    public function __set(string $name, $value)
    {
        $dca = &$GLOBALS['TL_DCA'][$this->_manager->getReaderConfig()->dataContainer];

        if (!$this->dc) {
            $this->dc = DC_Table_Utils::createFromModelData($this->getRaw(), $this->getDataContainer());
        }

        if (isset($dca['fields'][$name]['load_callback']) && is_array($dca['fields'][$name]['load_callback'])) {
            foreach ($dca['fields'][$name]['load_callback'] as $callback) {
                $this->dc->field = $name;
                $instance = System::importStatic($callback[0]);
                $value = $instance->{$callback[1]}($value, $this->dc);
            }
        }

        $this->_raw[$name] = $value;

        if (property_exists($this, $name)) {
            $this->{$name} = $value;
        }

        $this->setFormattedValue($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getRaw(): array
    {
        return $this->_raw;
    }

    /**
     * {@inheritdoc}
     */
    public function setRaw(array $data = []): void
    {
        $this->_raw = $data;

        if (null === $this->_manager->getDataContainer() && $this->_manager->getReaderConfig()->dataContainer) {
            $this->_manager->setDataContainer(DC_Table_Utils::createFromModelData($data, $this->_manager->getReaderConfig()->dataContainer));
        }

        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRawValue(string $name)
    {
        if (!isset($this->_raw[$name])) {
            return null;
        }

        return $this->_raw[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function setRawValue(string $name, $value): void
    {
        $this->_raw[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function setFormattedValue(string $name, $value): void
    {
        $dca = &$GLOBALS['TL_DCA'][$this->_manager->getReaderConfig()->dataContainer];

        if (!$this->dc) {
            $this->dc = DC_Table_Utils::createFromModelData($this->getRaw(), $this->getDataContainer());
        }

        $fields = $this->getManager()->getReaderConfig()->limitFormattedFields ? StringUtil::deserialize(
            $this->getManager()->getReaderConfig()->formattedFields,
            true
        ) : (isset($dca['fields']) && is_array($dca['fields']) ? array_keys($dca['fields']) : []);

        if (in_array($name, $fields, true)) {
            $this->dc->field = $name;

            $value = $this->_manager->getFormUtil()->prepareSpecialValueForOutput(
                $name,
                $value,
                $this->dc
            );

            // anti-xss: escape everything besides some tags
            $value = $this->_manager->getFormUtil()->escapeAllHtmlEntities(
                $this->getManager()->getReaderConfig()->dataContainer,
                $name,
                $value
            );

            // overwrite existing property with formatted value
            if (property_exists($this, $name)) {
                $this->{$name} = $value;
            }
        }

        $this->_formatted[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormattedValue(string $name)
    {
        return $this->_formatted[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatted(array $data = []): void
    {
        $this->_formatted = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatted(): array
    {
        $data = $this->_formatted;

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getManager(): ReaderManagerInterface
    {
        return $this->_manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataContainer(): string
    {
        return $this->_manager->getReaderConfig()->dataContainer;
    }

    /**
     * {@inheritdoc}
     */
    public function getModule(): array
    {
        return $this->_manager->getModuleData();
    }

    /**
     * {@inheritdoc}
     */
    public function parse(): string
    {
        $readerConfig = $this->_manager->getReaderConfig();

        // add reader config element data
        if (null !== ($readerConfigElements = $this->_manager->getReaderConfigElementRegistry()->findBy(['pid=?'], [$readerConfig->id]))) {
            foreach ($readerConfigElements as $readerConfigElement) {
                if (null === ($class = $this->_manager->getReaderConfigElementRegistry()->getElementClassByName($readerConfigElement->type))) {
                    continue;
                }

                /**
                 * @var ConfigElementType
                 */
                $type = $this->_manager->getFramework()->createInstance($class, [$this->_manager->getFramework()]);
                $type->addToItemData($this, $readerConfigElement);
            }
        }

        $twig = $this->_manager->getTwig();

        $twig->hasExtension('\Twig_Extensions_Extension_Text') ?: $twig->addExtension(new \Twig_Extensions_Extension_Text());
        $twig->hasExtension('\Twig_Extensions_Extension_Intl') ?: $twig->addExtension(new \Twig_Extensions_Extension_Intl());
        $twig->hasExtension('\Twig_Extensions_Extension_Array') ?: $twig->addExtension(new \Twig_Extensions_Extension_Array());
        $twig->hasExtension('\Twig_Extensions_Extension_Date') ?: $twig->addExtension(new \Twig_Extensions_Extension_Date());

        return $twig->render($this->_manager->getItemTemplateByName($readerConfig->itemTemplate ?: 'default'), $this->jsonSerialize());
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return System::getContainer()->get('huh.utils.class')->jsonSerialize(static::class, $this, $this->getFormatted());
    }
}
