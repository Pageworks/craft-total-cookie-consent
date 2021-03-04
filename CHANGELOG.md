# Total Cookie Consent Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [0.2.4] - 2021-03-04

### Fixed

- API error response falls back to default banner

## [0.2.3] - 2021-03-04

### Fixed

- trying to get property 'visitor_consent' of non-object bug

## [0.2.2] - 2020-10-09

### Fixed

- `consent()` variable was always returning an empty array

## [0.2.1] - 2020-10-01

### Fixed

- IE 11 styles

## [0.2.0] - 2020-10-01

### Added

- privacy policy button to the explicit consent form
- accept button to the explicit consent form

### Changed

- explicit consent descriptions hidden within an accordion (closed by default)

### Removed

- explicit consent URL field
- close button from explicit consent form

## [0.1.1] - 2020-08-13

### Added

- plugin settings
- banner template hook `{% hook 'total-cookie-consent' %}`
- default implied banner functionality
- default explicit banner functionality
- geolocation lookup via [ipapi](https://ipapi.com/)
- country based banners
- country + region based banners
- GDPR banners