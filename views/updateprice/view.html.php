<?php
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

//HTML представление класса для компонента UpdatePrice

class UpdatePriceViewUpdatePrice extends JView{

    function display($tpl = null)
    {	
        JToolBarHelper::title(   JText::_( 'Hello Yevgen' ), 'generic.png' );
        JToolBarHelper::deleteList();
        JToolBarHelper::editListX();
        JToolBarHelper::addNewX();
        // Get data from the model
		$msg = $this->get('File');
		$this->assignRef('msg', $msg);
		$err = $this->get('Belt');
		$this->assignRef('err', $err);
		$result = $this->get('Update');
		$this->assignRef('result', $result);
		$this->get('Unlink');
        parent::display($tpl);
		
    }
}