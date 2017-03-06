# Changelog

All notable changes to this package will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## 0.4.0 - 2017-03-06
### Changed
- A newly generated SessionId is considered to have a changed value
### Added
- Support for Psr\Log in SessionManager and Storage

## 0.3.0 - 2017-03-04
### Changed
- Switched back to __get, __set, __isset, and __unset magic methods from get, set, has, and remove methods
- Moved expired tracking from Session to Storage
### Removed
- Renew, getExpires, and isExpired from Session and SessionInterface

## 0.2.1 - 2017-02-06
### Added
- Finishing a session locks the DomainSession object from further updates

## 0.2.0 - 2017-02-06
### Changed
- Removed start and finish from DomainSession
- Switched from __get, __set, __isset, and __unset magic methods to get, set, has, and remove methods

### Added
- DomainSessionId encapsulates value, startingValue and regenerating id
- New DomainSessionManager with start and finish methods
- DomainSession now has all method that returns all data values

### Removed 
- DomainSessionFactory no longer needed, logic handled by DomainSessionManager
- IdFactory and IdFactoryInterface, now using random_bytes exclusively

## 0.1.0 - 2016-12-23
### Added
- Initial release
