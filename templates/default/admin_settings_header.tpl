            <table width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td width="50%" align="left" class="border">
                  <table width="100%" cellpadding="5" cellspacing="5">
                    <tr>
                      <td width="100%" align="left" class="title">
                        Add Administrator:
                      </td>
                    </tr>
                    <tr align="left">
                      <td class="body">
                        <form action="./index.php" name="add" method="POST">
                          <input type="hidden" name="do" value="add">
                          Username:<br><input type="text" name="username" size="16" class="form"><br>
                          Password:<br><input type="password" name="u_password" size="16" class="form"><br>
                          <input type="submit" value="Add" class="form">
                        </form>
                      </td>
                    </tr>
                  </table>
                  <table width="100%" cellpadding="5" cellspacing="5">
                    <tr>
                      <td width="100%" align="left" class="title">
                        Change Password:
                      </td>
                    </tr>
                    <tr align="left">
                      <td class="body">
                        <form action="./index.php" name="add" method="POST">
                          <input type="hidden" name="do" value="change">
                          Old Password:<br><input type="password" name="c_password" size="16" class="form"><br>
                          New Password:<br><input type="password" name="c_password1" size="16" class="form"><br>
                          New Password Again:<br><input type="password" name="c_password2" size="16" class="form"><br>
                          <input type="submit" value="Change" class="form">
                        </form>
                      </td>
                    </tr>
                  </table>
                  <table width="100%" cellpadding="5" cellspacing="5">
                    <tr>
                      <td width="100%" align="left" class="title">
                        Site Settings:
                      </td>
                    </tr>
                    <tr align="left">
                      <td class="body">
                        <form action="./index.php" name="update" method="POST">
                          <input type="hidden" name="do" value="update">
                          Page Title:<br><input type="text" name="p_title" size="40" value="<?php echo $s_title;?>" class="form"><br>
                          Page Heading:<br><input type="text" name="p_heading" size="40" value="<?php echo $s_heading;?>" class="form"><br>
                          Quotes Per Page:<br><input type="text" name="q_limit" size="40" value="<?php echo $s_limit;?>" class="form"><br>
                          Template:<br><input type="text" name="template_dir" size="40" value="<?php echo $s_tdir;?>" class="form"><br>
                          CSS Style:<br><input type="text" name="css_style" size="40" value="<?php echo $s_style;?>" class="form"><br>
                          <input type="submit" value="Submit" class="form">
                        </form>
                      </td>
                    </tr>
                  </table>
                </td>
                <td width="50%" valign="top" class="border">
                  <table width="100%" cellpadding="5" cellspacing="5">
                    <tr>
                      <td width="25" class="title">name</td><td width="25" class="title">level</td><td width="50" class="title">remove</td>
                    </tr>
