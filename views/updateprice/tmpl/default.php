<?php defined('_JEXEC') or die('Restricted access'); ?>

<h1><?php echo $this->msg; ?></h1>
<h1><?php echo $this->err; ?></h1>
<p>-------------------------------------------------------------</p>
<h2><?php echo $this->result; ?></h2>
<p>-------------------------------------------------------------</p>
<form enctype="multipart/form-data" action="index.php" method="POST" name="updateForm">
		<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
    <h3>Загрузите xml файл сгенерированный 1С</h3>
    <input name="infile" type="file" />
	<h3>Укажите дату последнего обновления цен в формате ДД.ММ.ГГГГ</h3>
	<input type="text" name="lastdate" size="10" />
	<input type="submit" value="Отправить" />
		<input type="hidden" name="option" value="com_updateprice" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="controller" value="updateprice" />
</form>