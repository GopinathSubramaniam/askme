<?php
/*
	AI by Gideon Greenspan and contributors
	http://www.activiacademy.com/

	File: qa-plugin/opensearch-support/qa-plugin.php
	Description: Initiates OpenSearch plugin


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.activiacademy.com/license.php
*/

/*
	Plugin Name: OpenSearch Support
	Plugin URI:
	Plugin Description: Allows OpenSearch clients to search Q2A site directly
	Plugin Version: 1.0
	Plugin Date: 2012-08-21
	Plugin Author: AI
	Plugin Author URI: http://www.activiacademy.com/
	Plugin License: GPLv2
	Plugin Minimum AI Version: 1.5
	Plugin Update Check URI:
*/


if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}


qa_register_plugin_layer('qa-opensearch-layer.php', 'OpenSearch Layer');
qa_register_plugin_module('page', 'qa-opensearch-page.php', 'qa_opensearch_xml', 'OpenSearch XML');
