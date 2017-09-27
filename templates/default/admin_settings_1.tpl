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
