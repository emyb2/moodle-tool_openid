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
 * Plugin version info for tool_openid.
 *
 * @package     tool_openid
 * @author      Donald Barrett <donald.barrett@learningworks.co.nz>
 * @copyright   2019 onwards, LearningWorks ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// No direct access.
defined('MOODLE_INTERNAL') || die();

// This plugin requires Moodle 3.3.
$plugin->requires = 2017051500;

// Plugin details.
$plugin->component = 'tool_openid';
$plugin->version = 2019102901;  // Plugin released on 29 Oct 2019.
$plugin->release = 'v3.3.0';   // This is our first revision for Moodle 3.3.

// Plugin status details.
$plugin->maturity = MATURITY_ALPHA;   // ALPHA, BETA, RC, STABLE. Always ALPHA.

$plugin->dependencies = ['auth_openid' => ANY_VERSION];