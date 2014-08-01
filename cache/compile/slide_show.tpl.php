<?php /* Smarty version 2.6.26, created on 2014-08-01 11:57:27
         compiled from inc/slide_show.tpl */ ?>
<div id="slideShow">
 <div class="slides">
  <?php $_from = $this->_tpl_vars['show_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['show'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['show']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['show']):
        $this->_foreach['show']['iteration']++;
?>
  <div class="slide<?php if (($this->_foreach['show']['iteration'] <= 1)): ?> current<?php endif; ?>" id="slide-<?php echo $this->_foreach['show']['iteration']; ?>
"> <a href="<?php echo $this->_tpl_vars['show']['show_link']; ?>
" target="_blank" ><img src="<?php echo $this->_tpl_vars['show']['show_img']; ?>
"/></a> </div>
  <?php endforeach; endif; unset($_from); ?>
 </div>
 <ul class="controlBase">
 </ul>
 <ul class="controls">
  <?php $_from = $this->_tpl_vars['show_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['show'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['show']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['show']):
        $this->_foreach['show']['iteration']++;
?>
  <li<?php if (($this->_foreach['show']['iteration'] <= 1)): ?> class="active"<?php endif; ?>><a href="#" rel="slide-<?php echo $this->_foreach['show']['iteration']; ?>
"></a></li>
  <?php endforeach; endif; unset($_from); ?>
 </ul>
</div>