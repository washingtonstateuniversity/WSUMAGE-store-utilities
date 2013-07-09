<?php
class Wsu_Storeutilities_Block_System_Config_Extension extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    
    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<div style="background:#EAF0EE;border:1px solid #CCCCCC;margin-bottom:10px;padding:10px 5px 5px 10px;">
		    <h4>About</h4>
		    <p>Extension to login admin users from Ldap
		</p>
		<br />
		<h4>Configuration</h4>
		<p>Go to: System >> Configuration >> Admin</p>
		</div>';
        
        return $html;
    }
}
