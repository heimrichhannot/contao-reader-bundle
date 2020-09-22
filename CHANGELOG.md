# Changelog
All notable changes to this project will be documented in this file.

## [1.21.0] - 2020-09-21
- moved to twig-support-bundle for template handling
- allow ReaderManagerInterface::getTwig() to also return Twig\Environment
- allow ReaderManagerInterface::getFramework() to also return ContaoFramework
- added twig as dependency
- removed ReaderItemTemplateChoice

## [1.20.2] - 2020-09-14
- allow status_messages 2.0

## [1.20.1] - 2020-09-10
- fixed 404 issues on contao 4.9 (see [https://github.com/heimrichhannot/contao-reader-bundle/issues/1](https://github.com/heimrichhannot/contao-reader-bundle/issues/1))

## [1.20.0] - 2020-08-21
- added heimrichhannot/contao-config-element-type-bundle dependency
- ConfigElementTypeInterface is now the default way to implement ConfigElementTypes
- deprecated ReaderConfigElementTypeInterface and ReaderConfigElementData

## [1.19.1] - 2020-08-19
- removed test code

## [1.19.0] - 2020-08-19
- added encore support to pdf and print syndication (added _encore templates for out of the box support)

## [1.18.0] - 2020-08-18
- use PdfCreator for PDF syndication instead of deprecated PdfWriter

## [1.17.0] - 2020-08-13
- added blocks to print and pdf twig templates
- fixed usage of deprecated util service
- fixed copypaste errors in readme

## [1.16.0] - 2020-08-05
- added customization of templateContainerVariable for config elements

## [1.15.2] - 2020-07-08
- fixed bug concerning dc_multilingual and id order

## [1.15.1] - 2020-06-23
- fixed bug concerning dc_multilingual and frontend preview

## [1.15.0] - 2020-06-18
- corrected canonical link generation respecting the archive and/or category jumpTo

## [1.14.0] - 2020-06-11
- added adjusted `mod_breadcrumb_huh_reader` template

## [1.13.0] - 2020-06-09
- fixed missing feedback syndication in some templates

## [1.12.1] - 2020-05-26
- skipped dc for default template

## [1.12.0] - 2020-05-25
- added category mode for `RelatedConfigElementType`

## [1.11.2] - 2020-05-25
- fixed `TagsConfigElementType`
- fixed auto_item bug when it starts with a minus

## [1.11.1] - 2020-05-20
- fixed `TagsConfigElementType`

## [1.11.0] - 2020-05-19
- added new list config elements: `RelatedConfigElementType`, `TagsConfigElementType`

## [1.10.1] - 2020-05-15
- fixed image config element for svg files

## [1.10.0] - 2020-05-15
- added `ReaderBeforeRenderEvent`
- added `ReaderAfterRenderEvent`

## [1.9.0] - 2020-05-13
- added feedback syndication
- added insert tag support to print syndication

## [1.8.7] - 2020-05-12
- added table to generated markup for styling reasons

## [1.8.6] - 2020-05-06
- added generated id to generated markup

## [1.8.5] - 2020-04-08
- fixed missing public attribute on service

## [1.8.4] - 2020-04-08
- added `disable404` in reader config

## [1.8.3] - 2020-04-06
- fixed an missing service alias

## [1.8.2] - 2020-04-01
- fixed image config element labels

## [1.8.1] - 2020-03-13
- fixed load_callback to support callables

## [1.8.0] - 2020-02-25
- added new event `ReaderModifyRetrievedItemEvent`

## [1.7.0] - 2020-02-24
- added new event `ReaderModifyQueryBuilderEvent`

## [1.6.2] - 2020-01-27
- fixed syntax error in ImageConfigElementType

## [1.6.1] - 2020-01-23
- updated deps 

## [1.6.0] - 2020-01-23
- added field value dependent placeholder images 

## [1.5.4] - 2019-12-11

- fixed frontend cron behavior

## [1.5.3] - 2019-12-09

- translation

## [1.5.2] - 2019-11-28

- fixed dc_multilingual related bug

## [1.5.1] - 2019-11-25

- updated some code in ReaderManager

## [1.5.0] - 2019-11-21

- added video config element
- fixed issues in image config element

## [1.4.0] - 2019-10-24

#### Changed
- made filter optional again

#### Fixed
- comment request token esi issue in prod mode

## [1.3.4] - 2019-10-22

#### Fixed
- NavigationConfigElementType exception

## [1.3.3] - 2019-10-01

#### Fixed
- submission form

## [1.3.2] - 2019-09-27

#### Added
- overview link mode to choose between history.go(-1) and specific page

## [1.3.1] - 2019-09-27

#### Fixed
- fixed bug from jumpTo overview label

## [1.3.0] - 2019-09-27

#### Added
- optional jumpTo overview page

## [1.2.1] - 2019-09-24

#### Changed
- fixed form bug

## [1.2.0] - 2019-09-24

#### Added
- submission form config element type

## [1.1.1] - 2019-09-23

#### Fxied
- inheritance issues

## [1.1.0] - 2019-09-13

#### Added
- random placeholder mode for image config element type

#### Fixed
- inheritance issues

## [1.0.0] - 2019-08-23

This release brings a new and easier way to register config element types. The old way (register the types in the config) is now deprecated and will be removed in the next major version. Please review the readme for introduction how to add config element types now. Upgrade old elements should be as easy as implement the new Interface, call the already existing method from the inherit method and register the class as service.

- config element types can now be registered the "symfony way"
- config element type is now shown in the backend list
- enhanced ReaderManager service config
- removed ReaderConfigElement constants TYPE_COMMENT
- added english translations for config element types
