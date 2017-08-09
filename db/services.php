<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Web service definition for the user_count_api plugin
 *
 * @package    user_count_api
 * @copyright  2017 onwards Catalyst IT {@link http://www.catalyst-eu.net/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Peter Spicer <peter.spicer@catalyst.net.nz>
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'local_user_count_api_count' => array(
        'classname' => 'local_user_count_api',
        'methodname' => 'user_count',
        'classpath' => 'local/user_count_api/externallib.php',
        'description' => 'Returns the number of active users for the site. Can pass time periods in as parameters.',
        'type' => 'read',
    )
);

$services = array(
    'User Count Webservice' => array(
        'functions' => array('local_user_count_api_count'),
        'restrictedusers' => 0,
        'enabled' => 1,
    )
);
