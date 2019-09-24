# Changelog
All notable changes to this project will be documented in this file.

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
