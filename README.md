# Contao Reader Bundle

![](https://img.shields.io/packagist/v/heimrichhannot/contao-reader-bundle.svg)
![](https://img.shields.io/packagist/dt/heimrichhannot/contao-reader-bundle.svg)
[![](https://img.shields.io/travis/heimrichhannot/contao-reader-bundle/master.svg)](https://travis-ci.org/heimrichhannot/contao-reader-bundle/)
[![](https://img.shields.io/coveralls/heimrichhannot/contao-reader-bundle/master.svg)](https://coveralls.io/github/heimrichhannot/contao-reader-bundle)

This bundle offers a generic reader module to use with arbitrary contao entities containing standard reader specific functionality like field output, images and auto_item handling.

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

### Reader config elements

Every reader config can have one or more reader config elements. These are designed to specify things that can occur multiple times (e.g. because there are many fields of one type).

Currently available reader config element types:

Type  | Description
------|------------
image | Configure the output of one or more image fields separately (image size, placeholder handling, ...)