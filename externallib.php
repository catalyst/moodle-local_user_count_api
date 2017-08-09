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
 * Web service core for the user_count_api plugin
 *
 * @package    user_count_api
 * @copyright  2017 onwards Catalyst IT {@link http://www.catalyst-eu.net/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Peter Spicer <peter.spicer@catalyst.net.nz>
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . "/externallib.php");

class local_user_count_api extends external_api {

    /**
     * Returns description of method parameters.
     * @return external_function_parameters
     */
    public static function user_count_parameters() {
        $duration = 'Duration amount to go back, defaults to 1';
        $durationunit = 'Duration unit to go back, defaults to "year"';
        return new external_function_parameters(
            array(
                'duration' => new external_value(PARAM_INT, $duration, VALUE_DEFAULT, 1),
                'duration_unit' => new external_value(PARAM_TEXT, $durationunit, VALUE_DEFAULT, 'year')
            )
        );
    }

    /**
     * The active user count method.
     * @return array Details about the current count of users, including
     *    the date range for which the count applies.
     */
    public static function user_count($duration = 1, $durationunit = 'year') {
        global $USER, $DB;

        // Validate all the parameters we accept.
        $paramstovalidate = array('duration' => $duration, 'duration_unit' => $durationunit);
        $params = self::validate_parameters(self::user_count_parameters(), $paramstovalidate);

        // Apply some slightly more specific validation.
        if (!in_array($params['duration_unit'], array('day', 'week', 'month', 'year'))) {
            $error = 'duration_unit => Invalid parameter value detected';
            $error .= ': Invalid external api parameter: the server was expecting one of: day, week, month, year';
            throw new invalid_parameter_exception($error);
        }
        if ($params['duration'] <= 0) {
            $error = 'duration => Invalid parameter value detected';
            $error .= ': Invalid external api parameter: the server was expecting a positive time period';
            throw new invalid_parameter_exception($error);
        }

        // Validate the user's token. It would be lovely if we could issue HTTP 4xx to the
        // request, but Moodle's handler swallows it. The tests above could also issue
        // suitable error codes, but there's no point.
        $context = get_context_instance(CONTEXT_USER, $USER->id);
        self::validate_context($context);

        // Work out which date range we're looking at, and get the user count.
        $endtime = time();
        $starttime = strtotime('-' . $params['duration'] . ' ' . $params['duration_unit']);

        $sql = "SELECT COUNT(id)
                FROM {user} u
                WHERE u.currentlogin >= ?
                    AND u.currentlogin <= ?
                    AND u.deleted = 0
                    AND u.suspended = 0";

        $activeusers = $DB->count_records_sql($sql, array($starttime, $endtime));

        // Return the data back up for the outputter.
        return array(
            'count' => $activeusers,
            'from' => date('c', $starttime),
            'to' => date('c', $endtime),
        );
    }

    /**
     * Declaration of what the method will return.
     * @return external_single_structure
     */
    public static function user_count_returns() {
        return new external_single_structure(
            array(
                'count' => new external_value(PARAM_INT, 'Number of active users in the given time range'),
                'from' => new external_value(PARAM_TEXT, 'Beginning of time range considered, in ISO format'),
                'to' => new external_value(PARAM_TEXT, 'End of time range considered, in ISO format'),
            )
        );
    }
}
