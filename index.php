<?php
/*************************************************************************

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

    $Id: index.php 25 2012-05-05 17:33:41Z jobe1986 $

***************************************************************************/

include("classes.php");
if (!defined('INSTALLED')) {
    header("Location: ./install/");
    exit;
}

$sql = "SELECT COUNT(id) FROM ".$_qdbs['tpfx']."quotes";
$start = $pgr->findStart($pgr->limit);
$count = $db->fetch_row($db->_sql($sql))[0];
$pages = $pgr->findPages($count, $pgr->limit);
$page = (empty($_GET['page']) ? '1' : $_GET['page']);
$tpl->set('page_list', $pgr->pageList($page, $pages));

if (!empty($_GET['do']) || !empty($_POST['do'])) {
    if (!empty($_GET['do'])) {
        switch ($_GET['do']) {
            case 'rate':
                 if (empty($_GET['q'])) {
                     header("Location: ".$ref);
                     break;
                 }
                 $sql = "SELECT ip FROM ".$_qdbs['tpfx']."votes WHERE id='".$db->escape($_GET['q'])."' AND ip='".$db->escape($ip)."'";
                 $a = $db->_sql($sql);
                 if ($db->_rows($a) < 1) {
                     if ($_GET['r'] == 'good') {
                         $sql = "UPDATE ".$_qdbs['tpfx']."quotes SET rating=rating+1 WHERE id='".$db->escape($_GET['q'])."'";
                         $a = $db->_sql($sql);
                         $sql = "INSERT INTO ".$_qdbs['tpfx']."votes (id,ip) VALUES ('".$db->escape($_GET['q'])."', '".$db->escape($ip)."')";
                         $a = $db->_sql($sql);
                     }
                     elseif ($_GET['r'] == 'bad') {
                         $sql = "UPDATE ".$_qdbs['tpfx']."quotes SET rating=rating-1 WHERE id='".$db->escape($_GET['q'])."'";
                         $a = $db->_sql($sql);
                         $sql = "INSERT INTO ".$_qdbs['tpfx']."votes (id,ip) VALUES ('".$db->escape($_GET['q'])."', '".$db->escape($ip)."')";
                         $a = $db->_sql($sql);
                     }
                 }
                 header("Location: ".$ref);
                 break;
        }
    }
    if ((!empty($_POST['q']) || !empty($_GET['q'])) && ((!empty($_GET['p']) && ($_GET['p'] == 'search')) || (!empty($_GET['do']) && ($_GET['do'] == 'search'))) ) {
        print($tpl->fetch($tpl->tdir.'layout_header.tpl'));
        if ( !empty($_POST['q']) ) {
	        $sql = "SELECT * FROM ".$_qdbs['tpfx']."quotes WHERE LOWER(quote) LIKE LOWER('%".$db->escape($_POST['q'])."%') LIMIT ".intval($pgr->limit)." OFFSET ".intval($start);
        } else {
	        $sql = "SELECT * FROM ".$_qdbs['tpfx']."quotes WHERE LOWER(quote) LIKE LOWER('%".$db->escape($_GET['q'])."%') LIMIT ".intval($pgr->limit)." OFFSET ".intval($start);
        }
        $r = $db->_sql($sql);
        if ($db->_rows($r) > 0) {
            while ($row = $db->fetch_row($r)) {
                $tpl->set('q_id', $row['id']);
                $tpl->set('q_rating', $row['rating']);
                $tpl->set('quote', $row['quote']);
                $sql = "SELECT ip FROM ".$_qdbs['tpfx']."votes WHERE id='".$db->escape($row['id'])."' AND ip='".$db->escape($ip)."'";
                $r2 = $db->_sql($sql);
                if ($db->_rows($r2) < 1) {
                    $rate = $tpl->fetch($tpl->tdir.'quote_rate.tpl');
                    $tpl->set('q_rate', $rate);
                } else {
                    $tpl->set('q_rate', '');
                }
                print($tpl->fetch($tpl->tdir.'quote_block.tpl'));
            }
        } else {
            print($tpl->fetch($tpl->tdir.'quote_no_results.tpl'));
        }

        $tpl->set('q_count', $db->q_count);
        $tpl->set('r_count', $db->r_count);
        print($tpl->fetch($tpl->tdir.'layout_footer.tpl'));
    }
    if (!empty($_POST['do'])) {
        switch ($_POST['do']) {
            case 'add':
                 if (empty($_POST['quote'])) {
                     break;
                 }
                 $quote = htmlspecialchars($_POST['quote']);
                 $quote = str_replace("  ", "&nbsp;&nbsp;", $quote);
                 $quote = nl2br($quote);
                 if (ini_get("magic_quotes_runtime") or ini_get("magic_quotes_gpc")) {
                      $sql = "INSERT INTO ".$_qdbs['tpfx']."queue (quote) VALUES ('".$db->escape(stripslashes($quote))."')";
                 } else {
                      $sql = "INSERT INTO ".$_qdbs['tpfx']."queue (quote) VALUES ('".$db->escape($quote)."')";
                 }
                 $r = $db->_sql($sql);
                 print($tpl->fetch($tpl->tdir.'layout_header.tpl'));
                 print($tpl->fetch($tpl->tdir.'quote_added.tpl'));
                 $tpl->set('q_count', $db->q_count);
                 $tpl->set('r_count', $db->r_count);
                 print($tpl->fetch($tpl->tdir.'layout_footer.tpl'));
                 break;
        }
    }
} else {
    // Header
    print($tpl->fetch($tpl->tdir.'layout_header.tpl'));

    if (!empty($_GET['p'])) {
        if ( $_GET['p'] == 'browse' ) {
            print($tpl->fetch($tpl->tdir.'page_list.tpl'));
        }
        switch ($_GET['p']) {
            case 'top':
                $sql = "SELECT * FROM ".$_qdbs['tpfx']."quotes ORDER BY rating DESC LIMIT ".intval($pgr->limit)." OFFSET ".intval($start);
                $r = $db->_sql($sql);
                while ($row = $db->fetch_row($r)) {
                    $tpl->set('q_id', $row['id']);
                    $tpl->set('q_rating', $row['rating']);
                    $tpl->set('quote', $row['quote']);
                    $sql = "SELECT ip FROM ".$_qdbs['tpfx']."votes WHERE id='".$db->escape($row['id'])."' AND ip='".$db->escape($ip)."'";
                    $r2 = $db->_sql($sql);
                    if ($db->_rows($r2) < 1) {
                        $rate = $tpl->fetch($tpl->tdir.'quote_rate.tpl');
                        $tpl->set('q_rate', $rate);
                    } else {
                        $tpl->set('q_rate', '');
                    }
                    print($tpl->fetch($tpl->tdir.'quote_block.tpl'));
                }

                break;
            case 'bottom':
                $sql = "SELECT * FROM ".$_qdbs['tpfx']."quotes ORDER BY rating ASC LIMIT ".intval($pgr->limit)." OFFSET ".intval($start);
                $r = $db->_sql($sql);
                while ($row = $db->fetch_row($r)) {
                    $tpl->set('q_id', $row['id']);
                    $tpl->set('q_rating', $row['rating']);
                    $tpl->set('quote', $row['quote']);
                    $sql = "SELECT ip FROM ".$_qdbs['tpfx']."votes WHERE id='".$db->escape($row['id'])."' AND ip='".$db->escape($ip)."'";
                    $r2 = $db->_sql($sql);
                    if ($db->_rows($r2) < 1) {
                        $rate = $tpl->fetch($tpl->tdir.'quote_rate.tpl');
                        $tpl->set('q_rate', $rate);
                    } else {
                        $tpl->set('q_rate', '');
                    }
                    print($tpl->fetch($tpl->tdir.'quote_block.tpl'));
                }

                break;
            case 'latest':
                $sql = "SELECT * FROM ".$_qdbs['tpfx']."quotes ORDER BY id DESC LIMIT ".intval($pgr->limit)." OFFSET ".intval($start);
                $r = $db->_sql($sql);
                while ($row = $db->fetch_row($r)) {
                    $tpl->set('q_id', $row['id']);
                    $tpl->set('q_rating', $row['rating']);
                    $tpl->set('quote', $row['quote']);
                    $sql = "SELECT ip FROM ".$_qdbs['tpfx']."votes WHERE id='".$db->escape($row['id'])."' AND ip='".$db->escape($ip)."'";
                    $r2 = $db->_sql($sql);
                    if ($db->_rows($r2) < 1) {
                        $rate = $tpl->fetch($tpl->tdir.'quote_rate.tpl');
                        $tpl->set('q_rate', $rate);
                    } else {
                        $tpl->set('q_rate', '');
                    }
                    print($tpl->fetch($tpl->tdir.'quote_block.tpl'));
                }

                break;
            case 'random':
                $sql = "SELECT * FROM ".$_qdbs['tpfx']."quotes ORDER BY ".$db->rand." LIMIT ".intval($pgr->limit)." OFFSET ".intval($start);
                $r = $db->_sql($sql);
                while ($row = $db->fetch_row($r)) {
                    $tpl->set('q_id', $row['id']);
                    $tpl->set('q_rating', $row['rating']);
                    $tpl->set('quote', $row['quote']);
                    $sql = "SELECT ip FROM ".$_qdbs['tpfx']."votes WHERE id='".$db->escape($row['id'])."' AND ip='".$db->escape($ip)."'";
                    $r2 = $db->_sql($sql);
                    if ($db->_rows($r2) < 1) {
                        $rate = $tpl->fetch($tpl->tdir.'quote_rate.tpl');
                        $tpl->set('q_rate', $rate);
                    } else {
                        $tpl->set('q_rate', '');
                    }
                    print($tpl->fetch($tpl->tdir.'quote_block.tpl'));
                }

                break;
            case 'random1':
                $sql = "SELECT * FROM ".$_qdbs['tpfx']."quotes WHERE rating>0 ORDER BY ".$db->rand." LIMIT ".intval($pgr->limit)." OFFSET ".intval($start);
                $r = $db->_sql($sql);
                while ($row = $db->fetch_row($r)) {
                    $tpl->set('q_id', $row['id']);
                    $tpl->set('q_rating', $row['rating']);
                    $tpl->set('quote', $row['quote']);
                    $sql = "SELECT ip FROM ".$_qdbs['tpfx']."votes WHERE id='".$db->escape($row['id'])."' AND ip='".$db->escape($ip)."'";
                    $r2 = $db->_sql($sql);
                    if ($db->_rows($r2) < 1) {
                        $rate = $tpl->fetch($tpl->tdir.'quote_rate.tpl');
                        $tpl->set('q_rate', $rate);
                    } else {
                        $tpl->set('q_rate', '');
                    }
                    print($tpl->fetch($tpl->tdir.'quote_block.tpl'));
                }

                break;
            case 'randomquote':
                $sql = "SELECT * FROM ".$_qdbs['tpfx']."quotes ORDER BY ".$db->rand." LIMIT 1";
                $r = $db->_sql($sql);
                while ($row = $db->fetch_row($r)) {
                    $tpl->set('q_id', $row['id']);
                    $tpl->set('q_rating', $row['rating']);
                    $tpl->set('quote', $row['quote']);
                    $sql = "SELECT ip FROM ".$_qdbs['tpfx']."votes WHERE id='".$db->escape($row['id'])."' AND ip='".$db->escape($ip)."'";
                    $r2 = $db->_sql($sql);
                    if ($db->_rows($r2) < 1) {
                        $rate = $tpl->fetch($tpl->tdir.'quote_rate.tpl');
                        $tpl->set('q_rate', $rate);
                    } else {
                        $tpl->set('q_rate', '');
                    }
                    print($tpl->fetch($tpl->tdir.'quote_block.tpl'));
                }

                break;
            case 'browse':
                $sql = "SELECT * FROM ".$_qdbs['tpfx']."quotes ORDER BY id LIMIT ".intval($pgr->limit)." OFFSET ".intval($start);
                $r = $db->_sql($sql);
                while ($row = $db->fetch_row($r)) {
                    $tpl->set('q_id', $row['id']);
                    $tpl->set('q_rating', $row['rating']);
                    $tpl->set('quote', $row['quote']);
                    $sql = "SELECT ip FROM ".$_qdbs['tpfx']."votes WHERE id='".$db->escape($row['id'])."' AND ip='".$db->escape($ip)."'";
                    $r2 = $db->_sql($sql);
                    if ($db->_rows($r2) < 1) {
                        $rate = $tpl->fetch($tpl->tdir.'quote_rate.tpl');
                        $tpl->set('q_rate', $rate);
                    } else {
                        $tpl->set('q_rate', '');
                    }
                    print($tpl->fetch($tpl->tdir.'quote_block.tpl'));
                }

                break;
            case 'search':
                print($tpl->fetch($tpl->tdir.'quote_search.tpl'));
                break;
            case 'add':
                print($tpl->fetch($tpl->tdir.'quote_add.tpl'));
                break;
        }
        if ( $_GET['p'] == 'browse' ) {
            print($tpl->fetch($tpl->tdir.'page_list.tpl'));
        }
    }
    elseif (!empty($_SERVER['QUERY_STRING'])) {
        $id = $_SERVER['QUERY_STRING'];
	preg_match("/(\d+)/", $id, $matches);
	if (!empty($matches[1])) {
            $id = $matches[1];
        } else {
            $id = -1;
        }
        $sql = "SELECT * FROM ".$_qdbs['tpfx']."quotes WHERE id='".$db->escape($id)."' LIMIT 1";
        $r = $db->_sql($sql);
        if ($db->_rows($r) > 0) {
            $row = $db->fetch_row($r);
            $tpl->set('q_id', $row['id']);
            $tpl->set('q_rating', $row['rating']);
            $tpl->set('quote', $row['quote']);
            $sql = "SELECT ip FROM ".$_qdbs['tpfx']."votes WHERE id='".$row['id']."' AND ip='".$db->escape($ip)."'";
            $r2 = $db->_sql($sql);
            if ($db->_rows($r2) < 1) {
                $rate = $tpl->fetch($tpl->tdir.'quote_rate.tpl');
                $tpl->set('q_rate', $rate);
            } else {
                $tpl->set('q_rate', '');
            }
            print($tpl->fetch($tpl->tdir.'quote_block.tpl'));

        } else {
            $tpl->set('q_id', $id);
            print($tpl->fetch($tpl->tdir.'quote_invalid.tpl'));
        }
    } else {
        print($tpl->fetch($tpl->tdir.'layout_main.tpl'));
    }

    // Footer
    $tpl->set('q_count', $db->q_count);
    $tpl->set('r_count', $db->r_count);
    print($tpl->fetch($tpl->tdir.'layout_footer.tpl'));
}
