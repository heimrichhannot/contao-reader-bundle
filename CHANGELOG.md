# Changelog

All notable changes to this project will be documented in this file.

## [1.39.3] - 2022-10-21
- Fixed: avoid throwing exception when calling sitemap.xml

## [1.39.2] - 2022-10-07
- Fixed: RelatedConfigElementMigration lead to exception if Reader bundle is a fresh install

## [1.39.1] - 2022-10-06
- Fixed: incorrect RelatedListConfigElementType configuration

## [1.39.0] - 2022-09-22
- Changed: head bundle is now a loose dependency ([#9])
- Changed: updated head bundle integration (minimum supported version is now 1.10.0) ([#9])
- Changed: fallback to contao pageTitle and description options if head bundle is not installed ([#9])

## [1.38.3] - 2022-09-14
- Fixed: exception in migration when updating to contao 4.13

## [1.38.2] - 2022-09-13
- Fixed: warning in migration

## [1.38.1] - 2022-09-08
- Fixed: issue when using related list content element and related list config element type on same page

## [1.38.0] - 2022-09-07
- Added: related list element ([#8])
- Deprecated: ReaderConfigElementContainer::RELATED_CRITERIUM_TAGS ([#8])
- Deprecated: ReaderConfigElementContainer::RELATED_CRITERIUM_CATEGORIES ([#8])

## [1.37.0] - 2022-08-22
- Changed: minimum php version is now 7.4
- Changed: some small refactoring
- Fixed: warnings with php 8

## [1.36.2] - 2022-05-18
- Fixed: invalid argument exception

## [1.36.1] - 2022-05-17
- Fix: missing translations

## [1.36.0] - 2022-05-17
- Changed: added checkbox for filter evaluation to keep bc
- Changed: small changes to the reader config backend layout

## [1.35.1] - 2022-05-10
- Fixed: symfony 5 compatiblity

## [1.35.0] - 2022-05-09
- Changed: reader bundle now evaluates filter config for item retrivel
- Fixed: some deprecation warnings

## [1.34.0] - 2022-04-20
- Changed: throw exception if item class could not be found

## [1.33.2] - 2022-03-10
- Fixed: ics syndication has not data

## [1.33.1] - 2022-02-14
- Fixed: array index issues in php 8+

## [1.33.0] - 2022-02-10
- Added: support for contao 4.13
- Changed: minimum contao version is now 4.9
- Changed: supported symfony versions to `^4.4||^5.4`
- Changed: removed `twig/extensions`
- Fixed: config for symfony 5+
- Removed: twig extension service definitions
- Removed: call to Utf8 functions
- Fixed: querybuilder parameter colons for symfony 5+

## [1.32.0] - 2022-02-10
- Changed: removed heimrichhannot/truncate-html dependency

## [1.31.1] - 2022-02-03

- Added: support for custom relationTable for tag-based `RelateConfigElementType`

## [1.31.0] - 2022-01-17

- Added: added support for alias tag links in `TagsConfigElementType`

## [1.30.4] - 2022-01-10

- Changed: submission config element to use generic interface
- Added: template selection and standard values for submission config element

## [1.30.3] - 2022-01-06

- Fixed: some missing english translations

## [1.30.2] - 2022-01-06

- Fixed: disable 404 option not working

## [1.30.1] - 2021-11-01

- Fixed: formatted return values of config element type processed by list bundle formatting

## [1.30.0] - 2021-09-20

- Changed: allow php 8

## [1.29.1] - 2021-09-15

- Fixed: preview mode for contao 4.9

## [1.29.0] - 2021-06-29

- added Polish translations

## [1.28.0] - 2021-06-24

- added optional support for multilingual aliases based
  on [heimrichhannot/contao-multilingual-fields-bundle](https://github.com/heimrichhannot/contao-multilingual-fields-bundle)

## [1.27.2] - 2021-06-21

- fixed optional support
  for [heimrichhannot/contao-multilingual-fields-bundle](https://github.com/heimrichhannot/contao-multilingual-fields-bundle)

## [1.27.1] - 2021-06-18

- fixed optional support
  for [heimrichhannot/contao-multilingual-fields-bundle](https://github.com/heimrichhannot/contao-multilingual-fields-bundle)

## [1.27.0] - 2021-06-04

- added new symfony command `huh-reader:make` for easier creating reader modules containing a reader config and a filter

## [1.26.1] - 2021-05-10

- allow twig support bundle ^1.0

## [1.26.0] - 2021-04-19

- added optional support
  for [heimrichhannot/contao-multilingual-fields-bundle](https://github.com/heimrichhannot/contao-multilingual-fields-bundle)

## [1.25.1] - 2021-03-25

- fixed missing sorting field

## [1.25.0] - 2021-03-22

- added a new visual presentation for nested reader configurations which should be way easier to read

## [1.24.0] - 2021-03-09

- deprecated bundled syndication config element type in favor of syndication type bundle
- fixed service definition
- fixed docs in ReaderConfigElementRegistry
- made templateVariable mandatory for ConfigElementTypes
- updated FacebookSyndication rel

## [1.23.8] - 2021-01-28

- added noindex,nofollow for print syndication

## [1.23.7] - 2021-01-28

- fixed auto_item issue in ReaderManager -> if \Input::get('auto_item') isn't used (request bundle had been used before)
  , auto_item isn't put to \Input::$arrUnusedGet leading to a 404 for valid auto_items

## [1.23.6] - 2021-01-25

- fixed readerpdf and readerprint template

## [1.23.5] - 2021-01-20

- fixed redirect issue in `TagsConfigElementType`

## [1.23.4] - 2021-01-11

- fixed for getSearchablePages computation on CLI

## [1.23.3] - 2020-12-16

- fixed non-existent service exception for comment type in reader config element
- fixed ctable definition in tl_reader_config was not an array

## [1.23.2] - 2020-10-30

- fixed non-existent service exception in list config element (#4)

## [1.23.1] - 2020-10-01

- fixed canonical links for empty details url

## [1.23.0] - 2020-09-30

- enhanced the README.md (now contains info about items and image config elements)

## [1.22.0] - 2020-09-28

- switched from ics bundle to `IcsUtil` in utils-bundle

## [1.21.0] - 2020-09-21

- moved to twig-support-bundle for template handling
- allow ReaderManagerInterface::getTwig() to also return Twig\Environment
- allow ReaderManagerInterface::getFramework() to also return ContaoFramework
- added twig as dependency
- removed ReaderItemTemplateChoice

## [1.20.2] - 2020-09-14

- allow status_messages 2.0

## [1.20.1] - 2020-09-10

- fixed 404 issues on contao 4.9 (
  see [https://github.com/heimrichhannot/contao-reader-bundle/issues/1](https://github.com/heimrichhannot/contao-reader-bundle/issues/1))

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

This release brings a new and easier way to register config element types. The old way (register the types in the
config) is now deprecated and will be removed in the next major version. Please review the readme for introduction how
to add config element types now. Upgrade old elements should be as easy as implement the new Interface, call the already
existing method from the inherit method and register the class as service.

- config element types can now be registered the "symfony way"
- config element type is now shown in the backend list
- enhanced ReaderManager service config
- removed ReaderConfigElement constants TYPE_COMMENT
- added english translations for config element types


[#9]: https://github.com/heimrichhannot/contao-reader-bundle/pull/9
[#8]: https://github.com/heimrichhannot/contao-reader-bundle/pull/8
