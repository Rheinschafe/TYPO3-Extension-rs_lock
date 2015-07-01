# Rheinschafe | Advanced TYPO3 Locking Changelog

## 2.0.0 (upcomming)

Breaking Changes:

  - Drop support for TYPO3 < 6.2.13
  
Features:

  - refactored to php namespaces
  
Documentation:

  - changelog added

## 1.0.1 (2015-06-30)

Bugfixes:

  - possible exception during release of MySQL driver while php shuts down

## 1.0.0 (2015-06-30)

Features:

  - TYPO3 locking adapter (xclass implementation)
  - own implementations for drivers:
    - 'simple' alias file locking
    - flock
    - semaphore
  - new driver: MySQL
  
## 0.2.0 (2013-01-13)

  - first alpha release
