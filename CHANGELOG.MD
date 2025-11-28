# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.12.5]

### Added

- Validation for notification request

## [1.12.4]

### Fixed

- Translations for older version

## [1.12.3]

### Fixed

- Validation for IPv6

## [1.12.2]

### Fixed

- Resolved issue introduced in 1.12.1

## [1.12.1]

### Changed

- Enforced length limit (trimming) on UserAgent field sent to payment gateway

## [1.12.0]

### Changed

- Using new translations

## [1.11.2]

### Changed

- Increased callback urls length

## [1.11.1]

### Fixed

- Invalid customer name passed to payments backend

## [1.11.0]

### Added

- New translations to refunds errors
- New fields in payer

### Changed

- Success page for cards payment

### Fixed

- Blik lvl0 payment after declined payment by card on site
- Wrong condition in Admin.php
- Loading animation
- Typo in settings title

## [1.10.6]

### Fixed

- Support for Prestashop 9.0

## [1.10.5]

### Added

- Option to automatically cancel payment pending orders after selected number of days

## [1.10.4]

### Fixed

- Presta version recognition
- Payment error page fallback to recover payment

## [1.10.3]

### Fixed

- issue with placing order when Friendly URL disabled

## [1.10.2]

### Added

- Improved BLIK lvl0 visuals

### Fixed

- BLIK code validation issues

## [1.10.1]

### Fixed

- Handling checkout when accept TOS checkbox is disabled

## [1.10.0]

### Added

- Improve transaction save

## [1.9.10]

### Fixed

- Fixed card form submitting

## [1.9.9]

### Fixed

- Fixed translations
- Fixed bank widget lang

## [1.9.8]

### Fixed

- Fixed bank widget choose feature

## [1.9.7]

### Fixed

- Commented out unnecessary JS that caused problems on various themes
- Provide lang parameter to transaction

## [1.9.6]

### Added

- Added installment simulator
- Added reset function

### Fixed

- Fixed payment terms info

## [1.9.5]

### Added

- Added custom order for banks and generic payments

### Fixed

- Fixed admin notification

## [1.9.4]

### Fixed

- Fixed package version info

## [1.9.3]

### Added

- Added browser check

## [1.9.2]

### Fixed

- Adapt plugin to an older version of Prestashop

## [1.9.1]

### Added

- Added regulations in english

## [1.9.0]

### Added

- Added generic payment method
- Added a widget view switch

---

### Fixed

- Fixed button view for card and blik
- Fixed blik code validation

## [1.8.5]

### Added

- Added information about the notification address

---

### Fixed

- Fixed secure code validation
- Fixed cache storage
- Fixed uninstallation process

## [1.8.4]

### Fixed

- PHP >7.1 compatibility fixed
- Fixed installation to other language versions Prestashop

## [1.8.3]

### Fixed

- Fixed notification

## [1.8.2]

### Added

- Payment method Pekao Raty

---

### Fixed

- Fix hook
- Fixed translations
- Paypo and installments fix
- Fixed exception logging

## [1.8.1]

### Fixed

- Prestashop >1.7 compatibility fixed
- Parametr CRC fixed

## [1.8]

### Added

- Initial release
