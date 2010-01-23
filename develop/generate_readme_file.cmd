@ECHO OFF

::
::===================================================================
::
::  MOD Author Welcome Package - Readme File Generator
::-------------------------------------------------------------------
::	Script info:
:: Copyright:	(c) 2010 - Obsidian
:: License:		http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
:: Package:		welcome_package
::
::===================================================================
::

::
:: This program is free software; you can redistribute it and/or modify
:: it under the terms of the GNU General Public License as published by
:: the Free Software Foundation.
::
:: This program is distributed in the hope that it will be useful,
:: but WITHOUT ANY WARRANTY; without even the implied warranty of
:: MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
:: See the GNU General Public License for more details.
::
:: You should have received a copy of the GNU General Public License
:: along with this program.  If not, see <http://opensource.org/licenses/gpl-2.0.php>.
::

:: Set our title...
TITLE Readme File Generator

:: Where is the PHP executable located?
SET PHP=C:\xampp

:: Where is the script located
SET SCRIPT=C:\Code\welcome_package\develop

:: Run the script!
"%PHP%\php\php.exe" "%SCRIPT%\generate_readme_file.php" %SCRIPT% %2 %3 %4

:: Uncomment this (remove the ::) to have the command prompt window pause after Failnet's termination.  
:: Useful for trapping errors.
PAUSE

EXIT