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

    $Id: classes.php 27 2012-05-05 21:56:04Z jobe1986 $

***************************************************************************/

if ((@include("settings.php")) === false) {
	if (!defined('INSTALLER')) {
		header("Location: ./install/");
		exit;
	}
}
$db = new QdbS_Database();
$pgr = new QdbS_Pager();
$tpl = new QdbS_Template();
$ip = getenv("REMOTE_ADDR");
$ip = gethostbyaddr($ip);
$ref = (!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "");
if (defined('INSTALLED')) {
    $db->_connect($_qdbs['server'], $_qdbs['user'], $_qdbs['password'], $_qdbs['db']);
    $sql = "SELECT * FROM ".$_qdbs['tpfx']."settings";
    $r = $db->_sql($sql);
    $row = $db->fetch_row($r);
    $tpl->set_dir($row['template']);
    $pgr->set_limit($row['qlimit']);
    $tpl->set('title', $row['title']);
    $tpl->set('heading', $row['heading']);
    $tpl->set('style', $tpl->tdir.$row['style']);
    $tpl->set('a_style', '.'.$tpl->tdir.$row['style']);
    $sql = "SELECT COUNT(id) FROM ".$_qdbs['tpfx']."quotes";
    $t = $db->fetch_row($db->_sql($sql))[0];
    $sql = "SELECT COUNT(id) FROM ".$_qdbs['tpfx']."queue";
    $p = $db->fetch_row($db->_sql($sql))[0];
    $tpl->set('t', $t);
    $tpl->set('p', $p);
    $tpl->set('version', '1.12');
    if(!isset($_SESSION['loggedin']) && isset($_COOKIE['qdb_username']) && isset($_COOKIE['qdb_password'])) {
        $sql = "SELECT * FROM ".$_qdbs['tpfx']."admins WHERE username='".$db->escape(strtolower($_COOKIE['qdb_username']))."' LIMIT 1";
        $r = $db->_sql($sql);
        $row = $db->fetch_row($r);
        if (($row['password'] == $_COOKIE['qdb_password']) and ($row['username'] == strtolower($_COOKIE['qdb_username']))) {
            $sql = "UPDATE ".$_qdbs['tpfx']."admins SET ip='$ip' WHERE username='".$db->escape($_COOKIE['qdb_username'])."' LIMIT 1";
            $r = $db->_sql($sql);
            $_SESSION['loggedin'] = 'logged';
        }
    }
} else {
    $tpl->set_dir('./templates/default/');
    $pgr->set_limit('50');
    $tpl->set('title', 'Quotes Database System');
    $tpl->set('heading', 'QdbS');
    $tpl->set('style', $tpl->tdir.'style-new.css');
    $tpl->set('a_style', '.'.$tpl->tdir.'style-new.css');
    $tpl->set('t', '0');
    $tpl->set('p', '0');
    $tpl->set('q_count', '0');
    $tpl->set('r_count', '0');
    $tpl->set('version', 'unknown');
}

class QdbS_Pager {
    var $limit;

    // Sets pageing limit
    function set_limit($limit) {
        $this->limit = $limit;
    }

    // find the starting row based on rows displayed per page ($limit)
    function findStart($limit) {
        if ((!isset($_GET['page'])) || ($_GET['page'] == "1")) {
            $start = 0;
            $_GET['page'] = 1;
        } else {
            $start = ($_GET['page']-1) * $limit;
        }
        return $start;
    }

    // find the total number of pages (or add 1 page if the number is odd)
    function findPages($count, $limit) {
        $pages = (($count % $limit) == 0) ? $count / $limit : floor($count / $limit) + 1;
        return $pages;
    }

    // build the page list (First < 1 2 3 4 5 > Last)
    function pageList($curpage, $pages) {
        $page_list  = "";
        if (($curpage != 1) && ($curpage)) {
            $page_list .= "  <a href=\"./index.php?p=browse&page=1\" title=\"First Page\">First</a> ";
        }
        if (($curpage-1) > 0) {
            $page_list .= "<a href=\"./index.php?p=browse&page=".($curpage-1)."\" title=\"Previous Page\">&lt</a> ";
        }
        $i = ($curpage - 2);
        $i_limit = ($curpage + 2);
        if ($i < 1) $i = 1;
        if ($i_limit > $pages) $i_limit = $pages;
        while ($i <= $i_limit) {
            if ($i == $curpage) {
                $page_list .= "<u>".$i."</u> ";
            } else {
                $page_list .= "<a href=\"./index.php?p=browse&page=".$i."\" title=\"Page ".$i."\">".$i."</a> ";
            }
            $i++;
        }
        if (($curpage+1) <= $pages) {
            $page_list .= "<a href=\"./index.php?p=browse&page=".($curpage+1)."\" title=\"Next Page\">&gt</a> ";
        }
        if (($curpage != $pages) && ($pages != 0)) {
            $page_list .= "<a href=\"./index.php?p=browse&page=".$pages."\" title=\"Last Page\">Last</a> ";
        }
        return $page_list;
    }
}

class QdbS_Template {
    var $vars;
    var $tdir;

    // sets the template directory and template
    function set_dir($template) {
        $this->tdir = $template;
    }

    // define template file (constructor)
    function Template($file = null) {
        $this->file = $file;
    }

    // set template variables
    function set($variable, $value) {
        $this->vars[$variable] = is_object($value) ? $value->fetch() : $value;
    }

    // fetches a file and saves the output buffer, then returns it
    function fetch($file = null) {
        if(!$file) {
            $file = $this->file;
        }

        extract($this->vars);
        ob_start();
        include($file);
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
}

class QdbS_Database {
    var $query;
    var $link;
    var $result;
    var $row;
    var $q_count = 0;
    var $r_count = 0;

    function __construct() {
        $this->clear();
    }

    function clear() {
        $this->query = null;
        $this->link = null;
        $this->result = null;
        $this->row = null;
        $this->q_count = 0;
        $this->r_count = 0;
    }

    function _connect($servername, $username, $password, $name) {
        if (!$this->link = mysqli_connect($servername, $username, $password, $name)) {
            trigger_error("QdbS_Database::_connect(); -> Error retreiving information!", E_USER_ERROR);
        }
    }

    function _sql($sql) {
        if (!$this->result = mysqli_query($this->link, $sql)) {
            trigger_error("QdbS_Database::_sql(); -> Query error: " . mysqli_error($this->link), E_USER_ERROR);
        }
        $this->q_count++;
        return $this->result;
    }

	function _rows($result) {
		return mysqli_num_rows($result);
	}

    function fetch_row($result) {
        $this->row = @mysqli_fetch_array($result);
        $this->r_count++;
        return $this->row;
    }

	function escape($string) {
		return mysqli_real_escape_string($this->link, $string);
	}
}
?>
