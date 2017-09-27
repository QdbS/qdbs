            <table width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td width="100%" align="left" class="title">
                  Enter your quote below. (<font color="#000000">please remove timestamps!</font>)
                </td>
              </tr>
              <tr align="left">
                <td class="body">
                  <form action="./index.php" name="add" method="POST">
                    <textarea name="quote" cols="80" rows="5" class="form"></textarea>
                    <input type="hidden" name="do" value="add"><br>
                    <input type="submit" value="Submit" class="form"> <input type="reset" value="Reset" class="form">
                  </form>
                </td>
              </tr>
            </table>
