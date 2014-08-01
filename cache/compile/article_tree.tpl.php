<?php /* Smarty version 2.6.26, created on 2014-08-01 12:00:36
         compiled from inc/article_tree.tpl */ ?>
<div class="treeBox">
 <h3><?php echo $this->_tpl_vars['lang']['article_tree']; ?>
</h3>
 <ul>
  <?php $_from = $this->_tpl_vars['article_category']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['cate']):
?>
  <li<?php if ($this->_tpl_vars['cate']['cur']): ?> class="cur"<?php endif; ?>><a href="<?php echo $this->_tpl_vars['cate']['url']; ?>
"><?php echo $this->_tpl_vars['cate']['mark']; ?>
 <?php echo $this->_tpl_vars['cate']['cat_name']; ?>
</a></li>
  <?php endforeach; endif; unset($_from); ?>
 </ul>
</div>