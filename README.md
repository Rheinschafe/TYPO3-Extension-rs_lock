Rheinschafe | Advanced TYPO3 Locking
===================================================

(EXT: rs_lock)

If you need advanced TYPO3 locking functions, this extension may help you.

Features TYPO3 with an advanced and rewritten locking.

TYPO3 offers locking methods are: 

*  simple (is_file method) 
*  flock (filesystem locking attributes)
*  semaphore (System V IPC Key)

These three drivers are rewritten into an adaptive driver api to boost performance and abstraction. There is also a new driver for MySQL Locking through an InnoDB table called 'sys_lock'.

Very extendable structure, even if this is an really really initial version. Try & Fail :-)


Links
-----

*  TER: http://typo3.org/extensions/repository/view/rs_lock
*  Forge: https://forge.typo3.org/projects/extension-rs_lock
