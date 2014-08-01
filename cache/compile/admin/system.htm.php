<?php /* Smarty version 2.6.26, created on 2014-08-01 11:58:08
         compiled from system.htm */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo $this->_tpl_vars['lang']['home']; ?>
<?php if ($this->_tpl_vars['ur_here']): ?> - <?php echo $this->_tpl_vars['ur_here']; ?>
 <?php endif; ?></title>
<meta name="Copyright" content="Douco Design." />
<link href="templates/public.css" rel="stylesheet" type="text/css">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "javascript.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<script type="text/javascript" src="images/jquery.tab.js"></script>
</head>
<body>
<div id="dcWrap">
 <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
 <div id="dcLeft"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "menu.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
 <div id="dcMain">
   <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "ur_here.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
   <div id="mainBox">
    <h3><?php echo $this->_tpl_vars['ur_here']; ?>
</h3>
    <div class="idTabs">
      <ul class="tab">
        <li><a href="#main"><?php echo $this->_tpl_vars['lang']['system_main']; ?>
</a></li>
        <li><a href="#display"><?php echo $this->_tpl_vars['lang']['system_display']; ?>
</a></li>
        <li><a href="#defined"><?php echo $this->_tpl_vars['lang']['system_defined']; ?>
</a></li>
      </ul>
      <div class="items">
       <form action="system.php?rec=edit" method="post" enctype="multipart/form-data">
        <div id="main">
        <table width="100%" border="0" cellpadding="8" cellspacing="0" class="tableBasic">
         <tr>
           <th width="131"><?php echo $this->_tpl_vars['lang']['input_name']; ?>
</th>
           <th><?php echo $this->_tpl_vars['lang']['input_value']; ?>
</th>
         </tr>
         <?php $_from = $this->_tpl_vars['cfg_list_main']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['cfg_list']):
?>
         <tr>
          <td align="right"><?php echo $this->_tpl_vars['cfg_list']['lang']; ?>
</td>
          <td>
           <?php if ($this->_tpl_vars['cfg_list']['type'] == 'radio'): ?>
           <label for="<?php echo $this->_tpl_vars['cfg_list']['name']; ?>
_0">
            <input type="radio" name="<?php echo $this->_tpl_vars['cfg_list']['name']; ?>
" id="<?php echo $this->_tpl_vars['cfg_list']['name']; ?>
_0" value="0"<?php if ($this->_tpl_vars['cfg_list']['value'] == '0'): ?> checked="true"<?php endif; ?>>
            <?php echo $this->_tpl_vars['lang']['no']; ?>
</label>
           <label for="<?php echo $this->_tpl_vars['cfg_list']['name']; ?>
_1">
            <input type="radio" name="<?php echo $this->_tpl_vars['cfg_list']['name']; ?>
" id="<?php echo $this->_tpl_vars['cfg_list']['name']; ?>
_1" value="1"<?php if ($this->_tpl_vars['cfg_list']['value'] == '1'): ?> checked="true"<?php endif; ?>>
            <?php echo $this->_tpl_vars['lang']['yes']; ?>
</label>
           <?php elseif ($this->_tpl_vars['cfg_list']['type'] == 'select'): ?>
           <select name="<?php echo $this->_tpl_vars['cfg_list']['name']; ?>
">
            <?php $_from = $this->_tpl_vars['cfg_list']['box']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['name'] => $this->_tpl_vars['value']):
?>
            <option value="<?php echo $this->_tpl_vars['value']; ?>
"<?php if ($this->_tpl_vars['cfg_list']['value'] == $this->_tpl_vars['value']): ?> selected<?php endif; ?>><?php echo $this->_tpl_vars['value']; ?>
</option>
            <?php endforeach; endif; unset($_from); ?>
           </select>
           <?php elseif ($this->_tpl_vars['cfg_list']['type'] == 'file'): ?>
           <input type="file" name="<?php echo $this->_tpl_vars['cfg_list']['name']; ?>
" size="18" />
           <?php if ($this->_tpl_vars['cfg_list']['value']): ?><a href="../<?php echo $this->_tpl_vars['cfg_list']['value']; ?>
" target="_blank"><img src="images/yes.gif"></a><?php else: ?><img src="images/no.gif"><?php endif; ?>
           <?php elseif ($this->_tpl_vars['cfg_list']['type'] == 'textarea'): ?>
           <textarea name="<?php echo $this->_tpl_vars['cfg_list']['name']; ?>
" cols="70" rows="5" class="textArea" /><?php echo $this->_tpl_vars['cfg_list']['value']; ?>
</textarea>
           <?php else: ?>
           <input type="text" name="<?php echo $this->_tpl_vars['cfg_list']['name']; ?>
" value="<?php echo $this->_tpl_vars['cfg_list']['value']; ?>
" size="80" class="inpMain" />
           <?php endif; ?>
           <?php if ($this->_tpl_vars['cfg_list']['cue']): ?>
            <?php if ($this->_tpl_vars['cfg_list']['type'] == 'radio' || $this->_tpl_vars['cfg_list']['type'] == 'select'): ?>
            <span class="cue ml"><?php echo $this->_tpl_vars['cfg_list']['cue']; ?>
</span>
            <?php else: ?>
            <p class="cue"><?php echo $this->_tpl_vars['cfg_list']['cue']; ?>
</p>
            <?php endif; ?>
           <?php endif; ?>
          </td>
         </tr>
         <?php endforeach; endif; unset($_from); ?>
        </table>
        </div>
        <div id="display">
        <table width="100%" border="0" cellpadding="8" cellspacing="0" class="tableBasic">
         <tr>
           <th width="131"><?php echo $this->_tpl_vars['lang']['input_name']; ?>
</th>
           <th><?php echo $this->_tpl_vars['lang']['input_value']; ?>
</th>
         </tr>
         <?php $_from = $this->_tpl_vars['cfg_list_display']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['cfg_list']):
?>
         <tr>
          <td align="right"><?php echo $this->_tpl_vars['cfg_list']['lang']; ?>
</td>
          <td>
           <input type="text" name="<?php echo $this->_tpl_vars['cfg_list']['name']; ?>
" value="<?php echo $this->_tpl_vars['cfg_list']['value']; ?>
" size="80" class="inpMain" />
           <?php if ($this->_tpl_vars['cfg_list']['cue']): ?>
            <p class="cue"><?php echo $this->_tpl_vars['cfg_list']['cue']; ?>
</p>
           <?php endif; ?>
          </td>
         </tr>
         <?php endforeach; endif; unset($_from); ?>
        </table>
        </div>
        <div id="defined">
        <table width="100%" border="0" cellpadding="8" cellspacing="0" class="tableBasic">
         <tr>
           <th width="131"><?php echo $this->_tpl_vars['lang']['input_name']; ?>
</th>
           <th><?php echo $this->_tpl_vars['lang']['input_value']; ?>
</th>
         </tr>
         <?php $_from = $this->_tpl_vars['cfg_list_defined']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['cfg_list']):
?>
         <tr>
          <td align="right"><?php echo $this->_tpl_vars['cfg_list']['lang']; ?>
</td>
          <td>
           <input type="text" name="<?php echo $this->_tpl_vars['cfg_list']['name']; ?>
" value="<?php echo $this->_tpl_vars['cfg_list']['value']; ?>
" size="80" class="inpMain" />
           <?php if ($this->_tpl_vars['cfg_list']['cue']): ?>
            <p class="cue"><?php echo $this->_tpl_vars['cfg_list']['cue']; ?>
</p>
           <?php endif; ?>
          </td>
         </tr>
         <?php endforeach; endif; unset($_from); ?>
        </table>
        </div>
        <table width="100%" border="0" cellpadding="8" cellspacing="0" class="tableBasic">
         <tr>
          <td width="131"></td>
          <td>
           <input name="submit" class="btn" type="submit" value="<?php echo $this->_tpl_vars['lang']['btn_submit']; ?>
" />
          </td>
         </tr>
        </table>
        </form>
      </div>
    </div>
   </div>
 </div>
 <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
 </div>
</body>
</html>