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
 * OAuth 2 Configuration page.
 *
 * @package    tool_oauth2
 * @copyright  2017 Damyon Wiese <damyon@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->dirroot.'/auth/openid/lib.php');

$PAGE->set_url('/admin/tool/openid/servers.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$strheading = get_string('pluginname', 'tool_openid');
$PAGE->set_title($strheading);
$PAGE->set_heading($strheading);

require_login();

require_capability('moodle/site:config', context_system::instance());

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'tool_openid'));

global $DB, $OUTPUT;

if ($formdata = data_submitted() and confirm_sesskey()) {
    $vars = array();
    $add = optional_param('add_server', null, PARAM_RAW);

    if ($add != null) {
        $record = new stdClass();
        $record->server = required_param('openid_add_server', PARAM_RAW);
        $record->listtype = optional_param('openid_add_listtype', 0, PARAM_INT);

        if ($record->listtype != OPENID_WHITELIST && $record->listtype != OPENID_BLACKLIST) {
            $record->listtype = OPENID_GREYLIST;
        }

        if (!empty($record->server) && !$DB->record_exists('openid_servers', array('server' => $record->server))) {
            $DB->insert_record('openid_servers', $record);
            $message = get_string('addedserver', 'tool_openid', $record->server);
            echo $OUTPUT->notification($message, core\output\notification::NOTIFY_SUCCESS);
        }

    } else {
        $servers = optional_param_array('servers', array(), PARAM_RAW);
        $storedservers = $DB->get_records('openid_servers');

        foreach ($servers as $id=>$val) {
            $id = intval($id);
            $val = intval($val);

            if ($id < 1) {
                continue;
            }

            // If we encounter a 'delete' request
            if ($val < 0) {
                $DB->delete_records('openid_servers', array('id' => $id));
                $message = get_string('deletedserver', 'tool_openid', $storedservers[$id]->server);
                echo $OUTPUT->notification($message, core\output\notification::NOTIFY_SUCCESS);
                continue;
            }

            // Otherwise, force a valid value (default 'GREYLIST')
            if ($val != OPENID_WHITELIST && $val != OPENID_BLACKLIST) {
                $val = OPENID_GREYLIST;
            }

            // And update record
            $record = new stdClass;
            $record->id = $id;
            $record->listtype = $val;
            $DB->update_record('openid_servers', $record);
            if (isset($storedservers[$id]) && $storedservers[$id]->listtype != $val) {
                $storedservers[$id]->listtype = $val;
                $message = get_string('updatedserver', 'tool_openid', $storedservers[$id]);
                echo $OUTPUT->notification($message, core\output\notification::NOTIFY_SUCCESS);
            }
        }
    }
}

?>

    <style type="text/css">
        .openid_servers {
            border-style: solid;
            border-color: black;
            border-width: 1px;
            border-spacing: 2px 2px;
            padding-top: 3px;
            padding-left: 3px;
            padding-right: 3px;
            padding-bottom: 3px;
            empty-cells: show;
            margin-top: 20px;
            margin-bottom: 20px;
        }
    </style>

    <form id="tool_openid" method="post" action="./servers.php">
        <input type="hidden" name="page" value="servers" />
        <input type="hidden" name="sesskey" value="<?php echo sesskey() ?>"
        <table>
            <tr>
                <td>
                    <h4><?php print_string('auth_openid_servers_settings', 'auth_openid') ?></h4>
                </td>
            </tr>
            <tr>
                <td>
                    <p style="margin-bottom: 20px"><?php print_string('auth_openid_servers_description','auth_openid') ?></p>
                </td>
            </tr>

            <tr valign="top">
                <td>
                    <input id="openid_add_server" name="openid_add_server" type="text" value="" size="40" />
                    <select name="openid_add_listtype">
                        <option value="<?php echo OPENID_GREYLIST; ?>">Confirm</option>
                        <option value="<?php echo OPENID_BLACKLIST; ?>">Deny</option>
                        <option value="<?php echo OPENID_WHITELIST; ?>">Allow</option>
                    </select>
                    <input type="submit" name="add_server" value="Add" />
                </td>
            </tr>

            <tr>
                <td>
                    <table class="openid_servers" cellspacing="5" cellpadding="10" border="1" width="100%">
                        <tr class="openid_servers">
                            <th class="openid_servers">Server</th>
                            <th class="openid_servers">Confirm</th>
                            <th class="openid_servers">Deny</th>
                            <th class="openid_servers">Allow</th>
                            <th class="openid_servers">Delete</th>
                        </tr>
                        <?php
                        global $DB;
                        $str = '
            <tr class="openid_servers">
                <td class="openid_servers" align="center">%s</td>
                <td class="openid_servers" align="center"><input type="radio" name="servers[%d]" value="'.OPENID_GREYLIST.'"%s /></td>
                <td class="openid_servers" align="center"><input type="radio" name="servers[%d]" value="'.OPENID_BLACKLIST.'"%s /></td>
                <td class="openid_servers" align="center"><input type="radio" name="servers[%d]" value="'.OPENID_WHITELIST.'"%s /></td>
                <td class="openid_servers" align="center"><input type="radio" name="servers[%d]" value="-1" /></td>
            </tr>
            ';
                        $checked = ' checked="checked"';

                        if ( ($records = $DB->get_records('openid_servers')) ) {
                            foreach ($records as $record) {
                                printf(
                                    $str,
                                    format_string($record->server),
                                    $record->id,
                                    (($record->listtype==OPENID_GREYLIST)?$checked:''),
                                    $record->id,
                                    (($record->listtype==OPENID_BLACKLIST)?$checked:''),
                                    $record->id,
                                    (($record->listtype==OPENID_WHITELIST)?$checked:''),
                                    $record->id
                                );
                            }
                        }

                        ?>
                    </table>
                </td>
            </tr>
        </table>
        <p style="text-align: center"><input type="submit" value="<?php echo get_string('savechanges') ?>"/></p>
    </form>

<?php

echo $OUTPUT->footer();
