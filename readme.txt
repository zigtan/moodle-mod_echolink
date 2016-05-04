This file is part of Moodle - http://moodle.org/

Moodle is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Moodle is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

copyright (c) 2013, Echo360 Inc.
license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later


EchoLink module
===================

EchoLink module is a resource module to provide Moodle Teachers the ability to add Echo360 Links from the configured EchoSystem Server.

This EchoLink module has been successfully tested with Moodle 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8 with EchoSystem 5.3, 5.4, 5.5

Change Log
===================

10 February 2016
 - Bug fix for seamless authentication to the ESS when Moodle Users have first names and last names containing spaces

10 March 2015
 - Minor change to resolve when setting cookie, resulting in nonce warnings during seamless authentication to the ESS
   (Special thanks to the team at Uni. of Canterbury for investigating and proposing the fix.)

19 December 2014
 - Minor changes to EchoLink UI to standardise Name field length to same setting as Moodle URL module
 - Removed Embed and Display In Frame appearance options since they did not work properly

5 August 2014
 - Bug fix to resolve undeclared variable in echolink_ess_get_rest_section() function
 - Resolved bug uniquely identifying ESS Person records
 - Minor improvements user interface

24 July 2014
 - Bug fix to find particular ESS Person user to work with ESS API filtering behaviour

2 July 2014
 - Improved warning messages displayed within EchoLink user interface

18 March 2014
 - Fixed bug preventing the use of pop-up, new tab, opening functionality.

17 March 2014
 - Fixed Moodle backup issue caused by plugin
 - Fixed Moodle link duplication functionality caused by plugin

