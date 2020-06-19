            <table width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td width="100%" align="left" class="title">
                  Site Installation:
                </td>
              </tr>
              <tr>
                <td width="100%" align="left" class="body">
                  *Create Database (MySQL Only): Select yes to create the database if it doesn't already exist, if you get errors, read the <a href="../doc/">docs</a>!<br>
                  *Database Type: The database server type.<br>
                  *Database Username: The username you use to connect to your database.<br>
                  *Database Password: The password you use to connect to your database.<br>
                  *Database Name: The name of your database. (script installation will attempt to create this for you)<br>
                  *Database Server: Where your server is located (usually localhost)<br><br>
                  The rest are not required for installation and may be left as is.  They may be changed later via the admin panel.
                </td>
              </tr>
              <tr align="left">
                <td class="body">
                  <form action="./index.php" name="install" method="POST">
                    <input type="hidden" name="do" value="install">
                    Create Database (MySQL Only):<br><select name="i_type" class="form"><option value="script" selected>Yes</option><option value="manual">No</option></select><br>
                    Database Type:<br><select name="i_dbtype" class="form"><option value="mysql" selected>MySQL</option><option value="pgsql">PostgreSQL</option></select><br>
                    Database Username:<br><input type="text" name="i_username" size="20" maxlength="16" class="form"><br>
                    Database Password:<br><input type="password" name="i_password" size="20" maxlength="16" class="form"><br>
                    Database Name:<br><input type="text" name="i_database" size="20" maxlength="20" value="qdb" class="form"><br>
                    Database Server:<br><input type="text" name="i_server" size="20" value="localhost" class="form"><br>
                    Database Table Prefix:<br><input type="text" name="i_tableprefix" size="20" value="qdbs_" class="form"><br>
                    Page Title:<br><input type="text" name="i_title" size="40" value="Salty Quotes Database" maxlength="60" class="form"><br>
                    Page Heading:<br><input type="text" name="i_heading" size="40" value="QdbS" maxlength="60" class="form"><br>
                    Quotes Per Page:<br><input type="text" name="i_limit" size="40" value="50" maxlength="4" class="form"><br>
                    Template:<br><input type="text" name="i_template" size="40" value="./templates/default/" maxlength="40" class="form"><br>
                    CSS Style:<br><input type="text" name="i_style" size="40" value="style.css" maxlength="40" class="form"><br>
                    <input type="submit" value="Install" class="form">
                  </form>
                </td>
              </tr>
            </table>
