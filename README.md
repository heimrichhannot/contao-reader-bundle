# Contao Reader Bundle

![](https://img.shields.io/packagist/v/heimrichhannot/contao-reader-bundle.svg)
![](https://img.shields.io/packagist/dt/heimrichhannot/contao-reader-bundle.svg)
[![Build Status](https://travis-ci.org/heimrichhannot/contao-reader-bundle.svg?branch=master)](https://travis-ci.org/heimrichhannot/contao-reader-bundle)
[![Coverage Status](https://coveralls.io/repos/github/heimrichhannot/contao-reader-bundle/badge.svg?branch=master)](https://coveralls.io/github/heimrichhannot/contao-reader-bundle?branch=master)

This bundle offers a generic reader module to use with arbitrary contao entities containing standard reader specific functionality like field output, images and auto_item handling.

It makes it possible to generate readers not only for events, news or faq's but with every DCA you like.

## Features

- generic reader module: output entities of arbitrary DCA entities
- dedicated and inheritable reader config entities that can be assigned to one or many modules
- twig support for templates

*Hint: This module can greatly be used with [heimrichhannot/contao-list-bundle](https://github.com/heimrichhannot/contao-list-bundle) which can display lists of arbitrary DCA entities*

## Impressions

### Reader configuration

![alt preview](docs/reader-config.png)

## Concepts

### Inheritable reader configuration

Since reader configuration can be lots of data sometimes we decided to outsource it into a dedicated DCA entity.
These entities can be assigned to one or even multiple reader modules in a reusable way.

In addition it's possible to create reader configs that inherit from other reader configs.
Hence overriding a single option while keeping everything else is possible.

### The Item class

Every database record output in a reader (e.g. an event) is modelled and wrapped by the `Item` class. The concrete class is `DefaultItem`. You can imagine
the item as a kind of ORM (object-relational-mapping).

The most important properties of an item are the arrays `raw` and `formatted` which also can be iterated in the reader item template:

- `raw`: contains the raw database values
- `formatted`: contains the formatted representation of the raw values

Example: Let's say a database record has a field `startDate` which holds a unix timestamp of the date chosen in the backend.
Then `raw` contains this unix timestamp and `formatted` contains the pretty printed date according to the `dateFormat` set in
the contao settings, i.e. "2020-09-12".

The reader bundle uses the method `FormUtil::prepareSpecialValueForOutput()` in [heimrichhannot/contao-utils-bundle](https://github.com/heimrichhannot/contao-utils-bundle)
for handling special values. It supports a wide range of types of special values:
- date/time fields
- arrays
- fields with `options`, `options_callback` and entries in the DCA's `reference` key
- binary fields (files, images, ...)
- ...

You can access both of these arrays in your reader item twig template as you normally would in twig:

```twig
{% for field, value in raw %}
{% endfor %}

{% for field, value in formatted %}
{% endfor %}
```

**CAUTION:** By default all values of a database record are formatted and accessible in the item template. As you can imagine
if some of the fields have thousands of options, the process of formatting can take some time and can reduce the peformance
of your website. **Hence you always should limit the formatted fields and only format these you really need.** You can adjust that
in the reader configuration (field `limitFormattedFields`).

For convenience reasons you can also access the field values like so in your twig template:

```twig
{{ fieldname }}
```

If you configured the field `fieldname` to be formatted, it will contain the formatted value. If not, the raw one. If
it's formatted, you can still access its raw value by using:

```twig
{{ raw.fieldname }}
```

### Reader config elements

Every reader config can have one or more reader config elements. These are designed to specify things that can occur multiple times (e.g. because there are many fields of one type).

Currently build-in reader config element types:

Type          | Description
--------------|------------
image         | Configure the output of one or more image fields separately (image size, placeholder handling, ...)
tags          | Output one or more tag fields based on [codefog/tags-bundle](https://github.com/codefog/tags-bundle).
related items | Output related items based on given tags (needs [heimrichhannot/contao-list-bundle](https://github.com/heimrichhannot/contao-list-bundle); needs [codefog/tags-bundle](https://github.com/codefog/tags-bundle)) or categories (needs [heimrichhannot/contao-categories-bundle](https://github.com/heimrichhannot/contao-categories-bundle)).

Other bundle can add reader config elements as well. Some examples:

- [Syndication through Syndication Type Bundle](https://github.com/heimrichhannot/contao-syndication-type-bundle)
- [Enhanced Videos through Video Bundle](https://github.com/heimrichhannot/contao-video-bundle)



#### Image

You can add images either as formatted value of, if you also like to have additional features like image size processing
or automatic placeholders if no image is set, you can use the *image reader config element*.

After the configuration you can output it as follows in your item template:

```twig
{% if images|default and images.myImage|default %}
    {{ include('@HeimrichHannotContaoUtils/image.html.twig', images.myImage) }}
{% endif %}
```

**IMPORTANT:** Note that by default the generated picture elements are added to an array called `images`. If your DCA
contains a field with the same name, you need to specify a different container name like e.g. `resizedImages`
(using `overrideTemplateContainerVariable`).

### Templates

There are two ways to define your templates. 

#### 1. By Prefix

The first one is to simply deploy twig templates inside any `templates` or bundles `views` directory with the following prefixes:

- `reader_item_`
- `item_`
- `news_`
- `event_`

**More prefixes can be defined, see 2nd way.**

#### 2. By config.yml

The second on is to extend the `config.yml` and define a strict template:

**Plugin.php**
```
<?php

class Plugin implements BundlePluginInterface, ExtensionPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        …
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container)
    {
        return ContainerUtil::mergeConfigFile(
            'huh_reader',
            $extensionName,
            $extensionConfigs,
            __DIR__ .'/../Resources/config/config.yml'
        );
    }
}
```

**config.yml**
```
huh:
    reader:
        templates:
            item:
                - { name: default, template: "@HeimrichHannotContaoReader/reader_item_default.html.twig" }
            item_prefixes:
                - reader_item_
                - item_
                - news_
                - event_
```

## Developers

### Events

Class | Name | Description
----- | ---- | -----------
RenderTwigTemplateEvent | huh.utils.template.render | Will be fired before the reader item is rendered (in DefaultItem). If you've overriden the parse method in an custom item class, you need to implement the event dispatcher by yourself. The event is located in [Utils Bundle](https://github.com/heimrichhannot/contao-utils-bundle) and used here for better bundle interoperability.


### Reader config elements

It is easy to add new reader config elements.

1. Create a class that implements `HeimrichHannot\ConfigElementTypeBundle\ConfigElementType\ConfigElementTypeInterface`
1. Register the class as service with service tag `huh.reader.config_element_type`
1. Add a friendly type name (translation) into the `$GLOBALS['TL_LANG']['tl_reader_config_element']['reference']` variable

    ```php
    $lang['reference'][\HeimrichHannot\ReaderBundle\ConfigElementType\CommentConfigElementType::getType()] = 'Comment';
    ```
