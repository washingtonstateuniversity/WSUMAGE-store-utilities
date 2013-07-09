<?php

class Wsu_Storeutilities_Model_Order_Pdf_Shipment extends Mage_Sales_Model_Order_Pdf_Shipment {
	// code from original class (I have removed it for readability purposes)
	//add this method
	public function addGiftMsg($page, $giftMessageSender, $giftMessageNote){
		if(empty($giftMessageNote)) {
			return;
		}
		$pipfmText = $giftMessageSender ."***BREAK***"."  "."***BREAK***".wordwrap($giftMessageNote, 100, "***BREAK***", true);
		$pipfmTextLines = array();
		$pipfmTextLines = explode("***BREAK***", $pipfmText);
		$i = 0;
		$pipfmTextLineStartY = 300;
		foreach ($pipfmTextLines as $pipfmTextLine){
			$i ++;
			//Bold only the first line
			if($i == 1){
				$this->_setFontBold_Modified($page, 10);
			} else {
				$this->_setFontRegular($page, 10);
			}
			$page->drawText($pipfmTextLine, 60, $pipfmTextLineStartY, 'UTF-8');
			$pipfmTextLineStartY = $pipfmTextLineStartY - 10;
		}
	}
	// at some place where you want output (inside getPdf() method) for it just drop this lines:
	public function getPdf($shipments = array()){
		   // ORIGINAL CODE REMOVED FOR READABILITY PURPOSES
		   $giftMessage = Mage::getModel("giftmessage/message")->load($order->getGiftMessageId());
		   $giftMessageSender ="Message from ".$giftMessage->getSender().':';
		   $giftMessageNote = $giftMessage->getMessage();
		   $this->addGiftMsg($page, $giftMessageSender, $giftMessageNote);
	}
}


