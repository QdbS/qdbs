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

    $Id: index.php 28 2012-05-05 23:05:09Z jobe1986 $

***************************************************************************/

include("../classes.php");

if (!defined("INSTALLED")) {
    header("Location: ./install/");
    exit;
}

$sql = "SELECT COUNT(id) FROM ".$_qdbs['tpfx']."quotes";
$start = $pgr->findStart($pgr->limit);
$count = $db->fetch_row($db->_sql($sql))[0];
$pages = $pgr->findPages($count, $pgr->limit);
$page = (!isset($_GET['page']) ? '1' : $_GET['page']);
$tpl->set('page_list', $pgr->pageList($page, $pages));

if (!empty($_GET['do']) || !empty($_POST['do'])) {
    if (!empty($_SESSION['loggedin'])) {
        switch ($_GET['do']) {
            case 'add':
                 if (empty($_GET['q'])) {
                     header ("Location: ".$ref);
                     break;
                 }
                 $sql = "SELECT * FROM ".$_qdbs['tpfx']."queue WHERE id='".$db->escape($_GET['q'])."'";
                 $r = $db->_sql($sql);
                 $row = $db->fetch_row($r);
                 if (ini_get("magic_quotes_runtime") or ini_get("magic_quotes_gpc")) {
                      $sql = "INSERT INTO ".$_qdbs['tpfx']."quotes (quote,rating) VALUES ('".$db->escape(stripslashes($row['quote']))."', '0')";
                 } else {
                      $sql = "INSERT INTO ".$_qdbs['tpfx']."quotes (quote,rating) VALUES ('".$db->escape($row['quote'])."', '0')";
                 }
                 $r = $db->_sql($sql);
                 $sql = "DELETE FROM ".$_qdbs['tpfx']."queue WHERE id='".$db->escape($_GET['q'])."'";
                 $r = $db->_sql($sql);

                 header ("Location: ".$ref);
                 break;
            case 'del':
                 if (empty($_GET['q'])) {
                     header ("Location: ".$ref);
                     break;
                 }
                 $sql = "DELETE FROM ".$_qdbs['tpfx']."queue WHERE id='".$db->escape($_GET['q'])."'";
                 $r = $db->_sql($sql);

                 header ("Location: ".$ref);
                 break;
            case 'remove':
                 if (empty($_GET['id'])) {
                     header ("Location: ".$ref);
                     break;
                 }
                 $sql = "SELECT * FROM ".$_qdbs['tpfx']."admins WHERE username='".$db->escape($_COOKIE['qdb_username'])."' LIMIT 1";
                 $r = $db->_sql($sql);
                 $row = $db->fetch_row($r);
                 if ($row['level'] == '2') {
                     $sql = "DELETE FROM ".$_qdbs['tpfx']."admins WHERE id='".$db->escape($_GET['id'])."'";
                     $r = $db->_sql($sql);
                 }

                 header ("Location: ".$ref);
                 break;
            case 'raise':
                 if (empty($_GET['id'])) {
                     header ("Location: ".$ref);
                     break;
                 }
                 $sql = "SELECT * FROM ".$_qdbs['tpfx']."admins WHERE username='".$db->escape($_COOKIE['qdb_username'])."' LIMIT 1";
                 $r = $db->_sql($sql);
                 $row = $db->fetch_row($r);
                 if ($row['level'] == '2') {
                     $sql = "SELECT * FROM ".$_qdbs['tpfx']."admins WHERE id='".$db->escape($_GET['id'])."' LIMIT 1";
                     $r = $db->_sql($sql);
                     $row = $db->fetch_row($r);
                     if ($row['level'] < '2') {
                         $sql = "UPDATE ".$_qdbs['tpfx']."admins SET level=level+1 WHERE id='".$db->escape($_GET['id'])."'";
                         $r = $db->_sql($sql);
                     }
                 }

                 header ("Location: ".$ref);
                 break;
            case 'lower':
                 if (empty($_GET['id'])) {
                     header ("Location: ".$ref);
                     break;
                 }
                 $sql = "SELECT * FROM ".$_qdbs['tpfx']."admins WHERE username='".$db->escape($_COOKIE['qdb_username'])."' LIMIT 1";
                 $r = $db->_sql($sql);
                 $row = $db->fetch_row($r);
                 if ($row['level'] == '2') {
                     $sql = "SELECT * FROM ".$_qdbs['tpfx']."admins WHERE id='".$db->escape($_GET['id'])."' LIMIT 1";
                     $r = $db->_sql($sql);
                     $row = $db->fetch_row($r);
                     if ($row['level'] > '1') {
                         $sql = "UPDATE ".$_qdbs['tpfx']."admins SET level=level-1 WHERE id='".$db->escape($_GET['id'])."'";
                         $r = $db->_sql($sql);
                     }
                 }

                 header ("Location: ".$ref);
                 break;
            case 'logout':
                 setcookie ('qdb_username', '', time()-3600, '/');
                 setcookie ('qdb_password', '', time()-3600, '/');
                 session_start();
                 session_unset();
                 session_destroy();
                 header ("Location: ".$ref);
                 break;
        }
        switch ($_POST['do']) {
            case 'add':
                if (empty($_POST['username'])) {
                    $tpl->set('logged', $tpl->fetch('.'.$tpl->tdir.'admin_links.tpl'));
                    $tpl->set('error', 'Missing username');
                    print($tpl->fetch('.'.$tpl->tdir.'admin_header.tpl'));
                    print($tpl->fetch('.'.$tpl->tdir.'admin_error.tpl'));
                    print($tpl->fetch('.'.$tpl->tdir.'admin_footer.tpl'));
                    break;
                }
                $sql = "SELECT * FROM ".$_qdbs['tpfx']."admins WHERE username='".$db->escape($_COOKIE['qdb_username'])."' LIMIT 1";
                $r = $db->_sql($sql);
                $row = $db->fetch_row($r);
                if ($row['level'] == '2') {
                    $username = strtolower($_POST['username']);
                    $password = strtolower(md5((isset($_POST['u_password']) ? $_POST['u_password'] : "")));
                    $sql = "INSERT INTO ".$_qdbs['tpfx']."admins (username,password,ip) VALUES ('".$db->escape($username)."', '".$db->escape($password)."', '')";
                    $r = $db->_sql($sql);
                    $tpl->set('logged', $tpl->fetch('.'.$tpl->tdir.'admin_links.tpl'));
                    print($tpl->fetch('.'.$tpl->tdir.'admin_header.tpl'));
                    print($tpl->fetch('.'.$tpl->tdir.'admin_success.tpl'));
                    print($tpl->fetch('.'.$tpl->tdir.'admin_footer.tpl'));
                }
                break;
            case 'change':
                $c_password = strtolower(md5(isset($_POST['c_password']) ? $_POST['c_password'] : ""));
                $c_password1 = strtolower(md5(isset($_POST['c_password1']) ? $_POST['c_password1'] : ""));
                $c_password2 = strtolower(md5(isset($_POST['c_password2']) ? $_POST['c_password2'] : ""));
                $sql = "SELECT * FROM ".$_qdbs['tpfx']."admins WHERE username='".$db->escape($_COOKIE['qdb_username'])."' LIMIT 1";
                $r = $db->_sql($sql);
                $row = $db->fetch_row($r);
                if (($c_password == $row['password']) && ($c_password1 == $c_password2)) {
                    $sql = "UPDATE ".$_qdbs['tpfx']."admins SET password='".$db->escape($c_password1)."' WHERE username='".$db->escape($_COOKIE['qdb_username'])."'";
                    $r = $db->_sql($sql);
                    $tpl->set('logged', $tpl->fetch('.'.$tpl->tdir.'admin_links.tpl'));
                    print($tpl->fetch('.'.$tpl->tdir.'admin_header.tpl'));
                    print($tpl->fetch('.'.$tpl->tdir.'admin_success.tpl'));
                    print($tpl->fetch('.'.$tpl->tdir.'admin_footer.tpl'));
                } else {
                    $tpl->set('logged', $tpl->fetch('.'.$tpl->tdir.'admin_links.tpl'));
                    $tpl->set('error', 'Password mismatch');
                    print($tpl->fetch('.'.$tpl->tdir.'admin_header.tpl'));
                    print($tpl->fetch('.'.$tpl->tdir.'admin_error.tpl'));
                    print($tpl->fetch('.'.$tpl->tdir.'admin_footer.tpl'));
                }
                break;
            case 'update':
                $sql = "SELECT * FROM ".$_qdbs['tpfx']."admins WHERE username='".$db->escape($_COOKIE['qdb_username'])."' LIMIT 1";
                $r = $db->_sql($sql);
                $row = $db->fetch_row($r);
                if ($row['level'] == '2') {
					$qlimit = intval($_POST['q_limit']);
					if ($qlimit <= 0) { $qlimit = 10; }
                    $sql = "UPDATE ".$_qdbs['tpfx']."settings SET template='".$db->escape($_POST['template_dir'])."', qlimit='".$qlimit."', title='".$db->escape($_POST['p_title'])."', heading='".$db->escape($_POST['p_heading'])."', style='".$db->escape($_POST['css_style'])."'";
                    $r = $db->_sql($sql);
                    $tpl->set('logged', $tpl->fetch('.'.$tpl->tdir.'admin_links.tpl'));
                    print($tpl->fetch('.'.$tpl->tdir.'admin_header.tpl'));
                    print($tpl->fetch('.'.$tpl->tdir.'admin_success.tpl'));
                    print($tpl->fetch('.'.$tpl->tdir.'admin_footer.tpl'));
                }
                break;
        }
    }
    if (!empty($_POST['do'])) {
        switch ($_POST['do']) {
            case 'login':
                if (empty($_POST['username'])) {
                    $tpl->set('logged', '&nbsp;');
                    print($tpl->fetch('.'.$tpl->tdir.'admin_header.tpl'));
                    print($tpl->fetch('.'.$tpl->tdir.'admin_failed.tpl'));
                    print($tpl->fetch('.'.$tpl->tdir.'admin_footer.tpl'));
                }
                $username = strtolower($_POST['username']);
                $password = strtolower(md5(isset($_POST['password']) ? $_POST['password'] : ""));
                $sql = "SELECT * FROM ".$_qdbs['tpfx']."admins WHERE username='".$db->escape($username)."' LIMIT 1";
                $r = $db->_sql($sql);
                $row = $db->fetch_row($r);
                if (strtolower($row['password']) == $password) {
                    $sql = "UPDATE ".$_qdbs['tpfx']."admins SET ip='".$db->escape($ip)."' WHERE username='".$db->escape($username)."'";
                    $r = $db->_sql($sql);
                    setcookie ('qdb_username', $username, time()+(3600*24*365), '/');
                    setcookie ('qdb_password', $password, time()+(3600*24*365), '/');
                    header ("Location: ".$ref);
                } else {
                    $tpl->set('logged', '&nbsp;');
                    print($tpl->fetch('.'.$tpl->tdir.'admin_header.tpl'));
                    print($tpl->fetch('.'.$tpl->tdir.'admin_failed.tpl'));
                    print($tpl->fetch('.'.$tpl->tdir.'admin_footer.tpl'));
                }
                break;
        }
    }
} else {
    // Header
    if (!empty($_SESSION['loggedin'])) {
        $tpl->set('logged', $tpl->fetch('.'.$tpl->tdir.'admin_links.tpl'));
    } else {
        $tpl->set('logged', '&nbsp;');
    }
    print($tpl->fetch('.'.$tpl->tdir.'admin_header.tpl'));
    if ($_SESSION['loggedin']) {
        if (!empty($_GET['p']) && ($_GET['p'] == 'settings')) {
            $sql = "SELECT * FROM ".$_qdbs['tpfx']."admins WHERE username='".$db->escape($_COOKIE['qdb_username'])."' LIMIT 1";
            $r = $db->_sql($sql);
            $row = $db->fetch_row($r);
            if ($row['level'] == '2') {
                $sql = "SELECT * FROM ".$_qdbs['tpfx']."settings";
                $r = $db->_sql($sql);
                $row = $db->fetch_row($r);
                $tpl->set('s_title', $row['title']);
                $tpl->set('s_heading', $row['heading']);
                $tpl->set('s_style', $row['style']);
                $tpl->set('s_tdir', $row['template']);
                $tpl->set('s_limit', $row['qlimit']);
                print($tpl->fetch('.'.$tpl->tdir.'admin_settings_header.tpl'));
                $sql = "SELECT * FROM ".$_qdbs['tpfx']."admins ORDER BY id ASC";
                $r = $db->_sql($sql);
                while($row = $db->fetch_row($r)) {
                    $tpl->set('admin', $row['username']);
                    $tpl->set('level', $row['level']);
                    $tpl->set('a_id', $row['id']);
                    print($tpl->fetch('.'.$tpl->tdir.'admin_settings.tpl'));
                }
                print($tpl->fetch('.'.$tpl->tdir.'admin_settings_footer.tpl'));
            } else {
                print($tpl->fetch('.'.$tpl->tdir.'admin_settings_1.tpl'));
            }
        } else {
            $sql = "SELECT * FROM ".$_qdbs['tpfx']."queue ORDER BY id DESC LIMIT ".intval($pgr->limit)." OFFSET ".intval($start);
            $r = $db->_sql($sql);
            if($db->_rows($r) > 0) {
                while ($row = $db->fetch_row($r)) {
                    $tpl->set('q_id', $row['id']);
                    $tpl->set('quote', $row['quote']);
                    print($tpl->fetch('.'.$tpl->tdir.'admin_block.tpl'));
                }

            } else {
                print($tpl->fetch('.'.$tpl->tdir.'admin_noquotes.tpl'));
            }
        }
    } else {
        print($tpl->fetch('.'.$tpl->tdir.'admin_login.tpl'));
    }

    // Footer
    $tpl->set('q_count', $db->q_count);
    $tpl->set('r_count', $db->r_count);
    print($tpl->fetch('.'.$tpl->tdir.'admin_footer.tpl'));
}
