<?php /* Smarty version 2.6.26, created on 2014-08-01 11:57:27
         compiled from inc/online_service.tpl */ ?>
<?php if ($this->_tpl_vars['site']['qq']): ?>
<div id="onlineService">
 <dl>
	<dt class="service"></dt>
	<dd id="pop"><?php $_from = $this->_tpl_vars['site']['qq']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['qq'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['qq']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['qq']):
        $this->_foreach['qq']['iteration']++;
?><a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo $this->_tpl_vars['qq']; ?>
&site=qq&menu=yes"><img src="http://localhost/theme/default/images/online_im.png" alt="点击这里给我发消息" title="点击这里给我发消息"/></a><?php endforeach; endif; unset($_from); ?></dd>
 </dl>
 <dl class="goTop"><a href="javascript:;" onfocus="this.blur();" class="goBtn"></a></dl>
</div>
<?php endif; ?>