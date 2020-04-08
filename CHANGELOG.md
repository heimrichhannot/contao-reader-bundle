# Changelog
All notable changes to this project will be documented in this file.

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
