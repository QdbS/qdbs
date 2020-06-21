<?php
/**************************************************************************

	This file is part of the Quotes Database System (QdbS)
	Copyright (C) 2003-2012 QdbS.org
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

if (defined('INSTALLED')) {
	header("Location: ../index.php");
	exit;
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
		if ($_POST['i_dbtype'] == "pgsql") {
			$connstr = sprintf("host=%s dbname=%s user=%s password=%s", $_POST['i_server'], $_POST['i_database'], $_POST['i_username'], $_POST['i_password']);
			if (($db = @pg_connect($connstr)) === FALSE) {
				$tpl->set('error', 'Error while connecting to PostgreSQL<br>');
			} else {
				$tpl->set('error', 'creating admin table...<br>');
				print($tpl->fetch('.'.$tpl->tdir.'admin_error.tpl'));
				@pg_query($db, "CREATE TABLE " . $_POST['i_tableprefix'] . "admins (
				  username character varying(16),
				  password text,
				  level integer DEFAULT 1,
				  ip text,
				  id serial NOT NULL,
				  CONSTRAINT admins_pk PRIMARY KEY (id)
				);") or die (pg_last_error($db));

				$tpl->set('error', 'creating queue table...<br>');
				print($tpl->fetch('.'.$tpl->tdir.'admin_error.tpl'));
				@pg_query($db, "CREATE TABLE " . $_POST['i_tableprefix'] . "queue (
				  id serial NOT NULL,
				  quote text,
				  CONSTRAINT queue_pk PRIMARY KEY (id)
				);") or die (pg_last_error($db));

				$tpl->set('error', 'creating quotes table...<br>');
				print($tpl->fetch('.'.$tpl->tdir.'admin_error.tpl'));
				@pg_query($db, "CREATE TABLE " . $_POST['i_tableprefix'] . "quotes (
				  id serial NOT NULL,
				  quote text,
				  rating integer DEFAULT 0,
				  CONSTRAINT quotes_pk PRIMARY KEY (id)
				);") or die (pg_last_error($db));

				$tpl->set('error', 'creating settings table...<br>');
				print($tpl->fetch('.'.$tpl->tdir.'admin_error.tpl'));
				@pg_query($db, "CREATE TABLE " . $_POST['i_tableprefix'] . "settings (
				  template text,
				  qlimit integer DEFAULT 0,
				  heading character varying(80),
				  title character varying(80),
				  style text
				);") or die (pg_last_error($db));

				$tpl->set('error', 'creating votes table...<br>');
				print($tpl->fetch('.'.$tpl->tdir.'admin_error.tpl'));
				@pg_query($db, "CREATE TABLE " . $_POST['i_tableprefix'] . "votes (
				  id integer DEFAULT 0,
				  ip text
				);") or die (pg_last_error($db));

				@pg_query($db, "INSERT INTO " . $_POST['i_tableprefix'] . "admins (username,password,level,ip) VALUES (
				  '".strtolower($_POST['i_username'])."',
				  '".strtolower(md5($_POST['i_password']))."',
				  2,
				  ''
				)") or die (pg_last_error($db));

				@pg_query($db, "INSERT INTO " . $_POST['i_tableprefix'] . "settings (template,qlimit,heading,title,style) VALUES (
				  '".$_POST['i_template']."',
				  '".$_POST['i_limit']."',
				  '".$_POST['i_heading']."',
				  '".$_POST['i_title']."',
				  '".$_POST['i_style']."'
				)") or die (pg_last_error($db));

				$settings = '<?php'."\n// Generated settings file, DO NOT EDIT!\n\n";
				$settings .= '$_qdbs[\'server\'] = \''.$_POST['i_server'].'\';'."\n";
				$settings .= '$_qdbs[\'user\'] = \''.$_POST['i_username'].'\';'."\n";
				$settings .= '$_qdbs[\'password\'] = \''.$_POST['i_password'].'\';'."\n";
				$settings .= '$_qdbs[\'db\'] = \''.$_POST['i_database'].'\';'."\n";
				$settings .= '$_qdbs[\'tpfx\'] = \''.$_POST['i_tableprefix'].'\';'."\n";
				$settings .= '$_qdbs[\'dbtype\'] = \''.$_POST['i_dbtype'].'\';'."\n";
				$settings .= 'define(\'INSTALLED\', true);'."\n";
				$settings .= '?'.'>';

				#chmod('../settings.php', 0666);
				if (!($fp = @fopen('../settings.php', 'w'))) {
					$tpl->set('error', '<b>ERROR</b>: cannot open settings.php!<br>');
					print($tpl->fetch('.'.$tpl->tdir.'admin_error.tpl'));
				} else {
					$result = @fputs($fp, $settings, strlen($settings));
				}

				@fclose($fp);
				#chmod('../settings.php', 0600);
				$tpl->set('error', 'Done.  You may now remove this file and directory.<br>');
			}
		}
		else {
			if (($db = @mysqli_connect($_POST['i_server'], $_POST['i_username'], $_POST['i_password'])) === FALSE) {
				$tpl->set('error', 'Error while connecting: '.mysqli_connect_error().'<br>');
			} else {
				if ($_POST['i_type'] == 'script') {
					$sql = 'CREATE DATABASE IF NOT EXISTS '.$_POST['i_database'];
					if (mysqli_query($db, $sql)) {
						$tpl->set('error', 'created database: "'.$_POST['i_database'].'"<br>');
						print($tpl->fetch('.'.$tpl->tdir.'admin_error.tpl'));
					} else {
						$tpl->set('error', 'Error while creating database: '.mysqli_error($db).'<br>');
						print($tpl->fetch('.'.$tpl->tdir.'admin_error.tpl'));
					}
				}

				@mysqli_close($db);
				$db = @mysqli_connect($_POST['i_server'], $_POST['i_username'], $_POST['i_password'], $_POST['i_database']);

				$tpl->set('error', 'creating admin table...<br>');
				print($tpl->fetch('.'.$tpl->tdir.'admin_error.tpl'));
				@mysqli_query($db, "CREATE TABLE " . $_POST['i_tableprefix'] . "admins (
				  username varchar(16) NOT NULL default '',
				  password text NOT NULL,
				  level char(1) NOT NULL default '1',
				  ip text NOT NULL,
				  id int(11) NOT NULL auto_increment,
				  PRIMARY KEY  (id)
				)") or die (mysqli_error($db));

				$tpl->set('error', 'creating queue table...<br>');
				print($tpl->fetch('.'.$tpl->tdir.'admin_error.tpl'));
				@mysqli_query($db, "CREATE TABLE " . $_POST['i_tableprefix'] . "queue (
				  id int(11) NOT NULL auto_increment,
				  quote longtext NOT NULL,
				  PRIMARY KEY  (id)
				)") or die (mysqli_error($db));

				$tpl->set('error', 'creating quotes table...<br>');
				print($tpl->fetch('.'.$tpl->tdir.'admin_error.tpl'));
				@mysqli_query($db, "CREATE TABLE " . $_POST['i_tableprefix'] . "quotes (
				  id int(11) NOT NULL auto_increment,
				  quote longtext NOT NULL,
				  rating int(11) NOT NULL default '0',
				  PRIMARY KEY  (id)
				)") or die (mysqli_error($db));

				$tpl->set('error', 'creating settings table...<br>');
				print($tpl->fetch('.'.$tpl->tdir.'admin_error.tpl'));
				@mysqli_query($db, "CREATE TABLE " . $_POST['i_tableprefix'] . "settings (
				  template text NOT NULL,
				  qlimit int(11) NOT NULL default '0',
				  heading varchar(80) NOT NULL default '',
				  title varchar(80) NOT NULL default '',
				  style text NOT NULL
				)") or die (mysqli_error($db));

				$tpl->set('error', 'creating votes table...<br>');
				print($tpl->fetch('.'.$tpl->tdir.'admin_error.tpl'));
				@mysqli_query($db, "CREATE TABLE " . $_POST['i_tableprefix'] . "votes (
				  id int(11) NOT NULL default '0',
				  ip text NOT NULL
				)") or die (mysqli_error($db));

				@mysqli_query($db, "INSERT INTO " . $_POST['i_tableprefix'] . "admins VALUES (
				  '".strtolower($_POST['i_username'])."',
				  '".strtolower(md5($_POST['i_password']))."',
				  '2',
				  '',
				  NULL
				)") or die (mysqli_error($db));

				@mysqli_query($db, "INSERT INTO " . $_POST['i_tableprefix'] . "settings VALUES (
				  '".$_POST['i_template']."',
				  '".$_POST['i_limit']."',
				  '".$_POST['i_heading']."',
				  '".$_POST['i_title']."',
				  '".$_POST['i_style']."'
				)") or die (mysqli_error($db));

				$settings = '<?php'."\n// Generated settings file, DO NOT EDIT!\n\n";
				$settings .= '$_qdbs[\'server\'] = \''.$_POST['i_server'].'\';'."\n";
				$settings .= '$_qdbs[\'user\'] = \''.$_POST['i_username'].'\';'."\n";
				$settings .= '$_qdbs[\'password\'] = \''.$_POST['i_password'].'\';'."\n";
				$settings .= '$_qdbs[\'db\'] = \''.$_POST['i_database'].'\';'."\n";
				$settings .= '$_qdbs[\'tpfx\'] = \''.$_POST['i_tableprefix'].'\';'."\n";
				$settings .= '$_qdbs[\'dbtype\'] = \''.$_POST['i_dbtype'].'\';'."\n";
				$settings .= 'define(\'INSTALLED\', true);'."\n";
				$settings .= '?'.'>';

				#chmod('../settings.php', 0666);
				if (!($fp = @fopen('../settings.php', 'w'))) {
					$tpl->set('error', '<b>ERROR</b>: cannot open settings.php!<br>');
					print($tpl->fetch('.'.$tpl->tdir.'admin_error.tpl'));
				} else {
					$result = @fputs($fp, $settings, strlen($settings));
				}

				@fclose($fp);
				#chmod('../settings.php', 0600);
				$tpl->set('error', 'Done.  You may now remove this file and directory.<br>');
			}
		}
	}
	print($tpl->fetch('.'.$tpl->tdir.'admin_error.tpl'));
	print($tpl->fetch('.'.$tpl->tdir.'admin_footer.tpl'));
} else {
	$tpl->set('logged', 'Quotes Database Installation');
	print($tpl->fetch('.'.$tpl->tdir.'admin_header.tpl'));
	print($tpl->fetch('.'.$tpl->tdir.'layout_install.tpl'));
	print($tpl->fetch('.'.$tpl->tdir.'admin_footer.tpl'));
}
