<?php
/**************************************************************************

	This file is part of the Quotes Database System (QdbS)
	Copyright (C) 2003-2022 QdbS.org
	Written by Kyle Florence (kyle.florence@gmail.com)
	Maintained by Matthew Beeching (jobe@qdbs.org)
	Table Prefix patch by Thomas Ward (jouva@moufette.com)

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.

	$Id: index.php 27 2012-05-05 21:56:04Z jobe1986 $

***************************************************************************/

define('INSTALLER', true);
require("../classes.php");
$db = null;

if (defined('INSTALLED')) {
	header("Location: ../index.php");
	exit;
}

function printline($line) {
	global $tpl;
	$tpl->set('error', $line);
	print($tpl->fetch('.'.$tpl->tdir.'admin_error.tpl'));
	$tpl->set('error', '');
}

if (!empty($_POST['do'])) {
	$tpl->set('logged', 'Quotes Database Installation');
	print($tpl->fetch('.'.$tpl->tdir.'admin_header.tpl'));
	$allclear = TRUE;
	if (empty($_POST['i_username']) || empty($_POST['i_password']) || empty($_POST['i_database']) || empty($_POST['i_server'])) {
		$allclear = FALSE;
		$tpl->set('error', 'please fill in all required fields.<br>');
	}
	if (empty($_POST['i_title']) || empty($_POST['i_template']) || empty($_POST['i_limit']) || empty($_POST['i_style']) || empty($_POST['i_heading'])) {
		$allclear = FALSE;
		$tpl->set('error', 'please fill in all required fields.<br>');
	}
	if ($allclear = TRUE) {
		$pgsql = false;
		if ($_POST['i_dbtype'] == "pgsql") {
			$pgsql = true;
		}
		// Use a single iteration do loop so we can skip to end on error
		do {
			// Create database if required (MySQL only)
			if ($_POST['i_dbtype'] == "mysql") {
				if ($_POST['i_type'] == 'script') {
					$dsn = sprintf("%s:host=%s", $_POST['i_dbtype'], $_POST['i_server']);
					try {
						$db = new PDO($dsn, $_POST['i_username'], $_POST['i_password']);
						$sql = 'CREATE DATABASE IF NOT EXISTS '.$_POST['i_database'];
						if ($db->exec($sql) !== false) {
							printline('created database: "'.$_POST['i_database'].'"<br>');
						} else {
							printline('Error while creating database: '.$db->errorInfo()[2].'<br>');
							break;
						}
						$db = null;
					} catch (Exception $e) {
						printline('Error while connecting to database: '.$e->getMessage().'<br>');
						break;
					}
				}
			}

			// Connect to database
			$dsn = sprintf("%s:host=%s;dbname=%s", $_POST['i_dbtype'], $_POST['i_server'], $_POST['i_database']);
			try {
				$db = new PDO($dsn, $_POST['i_username'], $_POST['i_password']);
				$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_BOTH);
			} catch (Exception $e) {
				printline('Error while connecting to database: '.$e->getMessage().'<br>');
				break;
			}

			// Create admins table
			printline('creating admin table...<br>');
			if ($pgsql) {
				$sql = "CREATE TABLE " . $_POST['i_tableprefix'] . "admins (
				  username character varying(16), password text,
				  level integer DEFAULT 1, ip text, id serial NOT NULL,
				  CONSTRAINT admins_pk PRIMARY KEY (id) );";
			} else {
				$sql = "CREATE TABLE " . $_POST['i_tableprefix'] . "admins (
				  username varchar(16) NOT NULL default '',
				  password text NOT NULL, level char(1) NOT NULL default '1',
				  ip text NOT NULL, id int(11) NOT NULL auto_increment,
				  PRIMARY KEY  (id) )";
			}
			if ($db->exec($sql) === false) {
				printline('Error creating admins table: '.$db->errorInfo()[2].'<br>');
				break;
			}

			// Create queue table
			printline('creating queue table...<br>');
			if ($pgsql) {
				$sql = "CREATE TABLE " . $_POST['i_tableprefix'] . "queue (
				  id serial NOT NULL, quote text,
				  CONSTRAINT queue_pk PRIMARY KEY (id) );";
			} else {
				$sql = "CREATE TABLE " . $_POST['i_tableprefix'] . "queue (
				  id int(11) NOT NULL auto_increment, quote longtext NOT NULL,
				  PRIMARY KEY  (id) )";
			}
			if ($db->exec($sql) === false) {
				printline('Error creating queue table: '.$db->errorInfo()[2].'<br>');
				break;
			}

			// Create quotes table
			printline('creating quotes table...<br>');
			if ($pgsql) {
				$sql = "CREATE TABLE " . $_POST['i_tableprefix'] . "quotes (
				  id serial NOT NULL, quote text, rating integer DEFAULT 0,
				  CONSTRAINT quotes_pk PRIMARY KEY (id) );";
			} else {
				$sql = "CREATE TABLE " . $_POST['i_tableprefix'] . "quotes (
				  id int(11) NOT NULL auto_increment, quote longtext NOT NULL,
				  rating int(11) NOT NULL default '0',
				  PRIMARY KEY  (id) )";
			}
			if ($db->exec($sql) === false) {
				printline('Error creating quote table: '.$db->errorInfo()[2].'<br>');
				break;
			}

			// Create settings table
			printline('creating settings table...<br>');
			if ($pgsql) {
				$sql = "CREATE TABLE " . $_POST['i_tableprefix'] . "settings (
				  template text, qlimit integer DEFAULT 0,
				  heading character varying(80),  title character varying(80),
				  style text );";
			} else {
				$sql = "CREATE TABLE " . $_POST['i_tableprefix'] . "settings (
				  template text NOT NULL, qlimit int(11) NOT NULL default '0',
				  heading varchar(80) NOT NULL default '',
				  title varchar(80) NOT NULL default '',
				  style text NOT NULL)";
			}
			if ($db->exec($sql) === false) {
				printline('Error creating settings table: '.$db->errorInfo()[2].'<br>');
				break;
			}

			// Create votes table
			printline('creating votes table...<br>');
			if ($pgsql) {
				$sql = "CREATE TABLE " . $_POST['i_tableprefix'] . "votes (
				  id integer DEFAULT 0, ip text );";
			} else {
				$sql = "CREATE TABLE " . $_POST['i_tableprefix'] . "votes (
				  id int(11) NOT NULL default '0', ip text NOT NULL)";
			}
			if ($db->exec($sql) === false) {
				printline('Error creating votes table: '.$db->errorInfo()[2].'<br>');
				break;
			}

			// Create initial admin
			printline('Creating initial admin...<br>');
			$sql = 'INSERT INTO ' . $_POST['i_tableprefix'] . 'admins (username, password, level, ip) VALUES (?, ?, ?, ?);';
			$args = [strtolower($_POST['i_username']), strtolower(md5($_POST['i_password'])), 2, ''];
			$sth = $db->prepare($sql);
			if ($sth->execute($args) === false) {
				printline('Error creating initial admin: '.$db->errorInfo()[2].'<br>');
				break;
			}

			// Populate settings table
			printline('Populating settings table...<br>');
			$sql = 'INSERT INTO ' . $_POST['i_tableprefix'] . 'settings (template, qlimit, heading, title, style) VALUES (?, ?, ?, ?, ?);';
			$args = [$_POST['i_template'], $_POST['i_limit'], $_POST['i_heading'], $_POST['i_title'], $_POST['i_style']];
			$sth = $db->prepare($sql);
			if ($sth->execute($args) === false) {
				printline('Error creating initial admin: '.$db->errorInfo()[2].'<br>');
				break;
			}

			// Generate settings.php
			printline('Generating settings.php...<br>');
			$settings = '<?php'."\n// Generated settings file, DO NOT EDIT!\n\n";
			$settings .= '$_qdbs[\'server\'] = \''.$_POST['i_server'].'\';'."\n";
			$settings .= '$_qdbs[\'user\'] = \''.$_POST['i_username'].'\';'."\n";
			$settings .= '$_qdbs[\'password\'] = \''.$_POST['i_password'].'\';'."\n";
			$settings .= '$_qdbs[\'db\'] = \''.$_POST['i_database'].'\';'."\n";
			$settings .= '$_qdbs[\'tpfx\'] = \''.$_POST['i_tableprefix'].'\';'."\n";
			$settings .= '$_qdbs[\'dbtype\'] = \''.$_POST['i_dbtype'].'\';'."\n";
			$settings .= 'define(\'INSTALLED\', true);'."\n";
			$settings .= '?'.'>';

			if (!($fp = @fopen('../settings.php', 'w'))) {
				putline('<b>ERROR</b>: cannot open settings.php!<br>');
			} else {
				$result = @fputs($fp, $settings, strlen($settings));
			}

			@fclose($fp);

			printline('Done.  You may now remove this file and directory.<br>');
			printline('Initial admin user name and password are set to the same as the database user name and password.<br>');
		} while (0);
	}
	print($tpl->fetch('.'.$tpl->tdir.'admin_error.tpl'));
	print($tpl->fetch('.'.$tpl->tdir.'admin_footer.tpl'));
} else {
	$tpl->set('logged', 'Quotes Database Installation');
	print($tpl->fetch('.'.$tpl->tdir.'admin_header.tpl'));
	print($tpl->fetch('.'.$tpl->tdir.'layout_install.tpl'));
	print($tpl->fetch('.'.$tpl->tdir.'admin_footer.tpl'));
}
