<?php
/**
 * @copyright	Copyright (C) 2013-2014 q-invoice.com - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @version 	2.1
 */

if ( !class_exists( 'qinvoice' ) ) {

	class qinvoice{

		protected $gateway = '';
		private $username;
		private $password;
		public $identifier;

		private $documenttype = 'invoice';
		public $companyname;
		public $salutation;
		public $contactname;
		public $email;
		public $phone;
		public $address;
		public $address2;
		public $city;
		public $country;
		public $delivery_companyname;
		public $delivery_firstname;
		public $delivery_lastname;
		public $delivery_salutation;
		public $delivery_address;
		public $delivery_address2;
	    public $delivery_zipcode;
	    public $delivery_city;
	    public $delivery_country;
	    public $vatnumber;
	    public $copy;
	    public $remark;
	    public $paid;
	    public $payment_method;
	    public $date;
	    public $duedate;
	    public $action;
	    public $document_reference;
	    public $currency;
		public $saverelation = false;
		public $calculation_method;

		
		public $layout;
		
		private $tags = array();
		private $items = array();
		private $files = array();
		private $recurring;
		private $payment = false;

		function __construct($username, $password, $url){
			$this->username = $username;
			$this->password = $password;
			if(substr($url, -1) != '/'){
				$url .= '/';
			}
			$this->gateway = $url;
			$this->recurring = 'none';
			$this->setDocumentType('invoice');
		}

		
		public function addTag($tag){
			$this->tags[] = $tag;
		}

		public function setDocumentType($type){
			$doc_type = explode(".",$type);
			$type = $doc_type[0];
			$mode = $doc_type[1];
			if(!in_array($type, array('invoice','quote','order_confirmation','proforma'))){
				$type = 'invoice';
			}
			$this->documenttype = $type;
			$this->recurring = $mode;
		}

		public function addPayment($amount, $method, $transaction_id, $currency = 'EUR', $date = '', $description = ''){
		    $this->payment = new StdClass();
		    $this->payment->amount = $amount;
		    $this->payment->method = $method;
		    $this->payment->transaction_id = $transaction_id;
		    $this->payment->currency = $currency;
		    $this->payment->description = $description;
		    $this->payment->date = $date == '' ? Date('Y-m-d') : $date;
        }

		public function setRecurring($recurring){
			$this->recurring = strtolower($recurring);
		}

		public function addItem($params){
			$item['code'] = $params['code'];
			$item['unit'] = $params['unit'];
			$item['description'] = $params['description'];
			$item['price'] = $params['price'];
			$item['price_incl'] = $params['price_incl'];
			$item['price_vat'] = $params['price_vat'];
			$item['vatpercentage'] = $params['vatpercentage'];
			$item['discount'] = $params['discount'];
			$item['quantity'] = $params['quantity'];
			$item['categories'] = $params['categories'];
			$item['ledgeraccount'] = $params['ledgeraccount'];
			$this->items[] = $item;
		}
		
		public function addFile($name, $url){
			$this->files[] = array('url' => $url, 'name' => $name);
		}

		public function sendRequest() {
	        $content = "<?xml version='1.0' encoding='UTF-8'?>";
	        $content .= $this->buildXML();

	        $headers = array("Content-type: application/atom+xml");
	        $ch = curl_init();
	        curl_setopt($ch, CURLOPT_URL, $this->gateway);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
	        $data = curl_exec($ch);

	        if (curl_errno($ch)) {
	            print curl_error($ch);
	        } else {
	            curl_close($ch);
	        }
	       //	return $data;

	        if($data == 1){
	        	return true;
	        }else{
	        	return false;
	        }
	        
	    }

		private function buildXML(){
			$string = '<request>
							<login mode="new'. ucfirst($this->documenttype).'">
								<username><![CDATA['.$this->username.']]></username>
								<password><![CDATA['.$this->password.']]></password>
								<identifier><![CDATA['. $this->identifier .']]></identifier>
							</login>
							<'. $this->documenttype .'>
								<companyname><![CDATA['. $this->companyname .']]></companyname>
								<salutation><![CDATA['. $this->salutation .']]></salutation>
								<firstname><![CDATA['. $this->firstname .']]></firstname>
								<lastname><![CDATA['. $this->lastname .']]></lastname>
								<email><![CDATA['. $this->email .']]></email>
								<phone><![CDATA['. $this->phone .']]></phone>
								<address><![CDATA['. $this->address .']]></address>
								<address2><![CDATA['. $this->address2 .']]></address2>
								<zipcode><![CDATA['. $this->zipcode .']]></zipcode>
								<city><![CDATA['. $this->city .']]></city>
								<country><![CDATA['. $this->country .']]></country>
								<delivery_companyname><![CDATA['. $this->delivery_companyname .']]></delivery_companyname>
								<delivery_salutation><![CDATA['. $this->delivery_salutation .']]></delivery_salutation>
								<delivery_firstname><![CDATA['. $this->delivery_firstname .']]></delivery_firstname>
								<delivery_lastname><![CDATA['. $this->delivery_lastname .']]></delivery_lastname>
								<delivery_address><![CDATA['. $this->delivery_address .']]></delivery_address>
								<delivery_address2><![CDATA['. $this->delivery_address2 .']]></delivery_address2>
								<delivery_zipcode><![CDATA['. $this->delivery_zipcode .']]></delivery_zipcode>
								<delivery_city><![CDATA['. $this->delivery_city .']]></delivery_city>
								<delivery_country><![CDATA['. $this->delivery_country .']]></delivery_country>
								<vat><![CDATA['. $this->vatnumber .']]></vat>
								<recurring><![CDATA['. $this->recurring .']]></recurring>
								<remark><![CDATA['. $this->remark .']]></remark>
								<layout><![CDATA['. $this->layout .']]></layout>
								<copy><![CDATA['. $this->copy .']]></copy>
								<date><![CDATA['. $this->date .']]></date>
								<duedate><![CDATA['. $this->duedate .']]></duedate>
	                            <currency><![CDATA['. $this->currency .']]></currency>
	                            <action><![CDATA['. $this->action .']]></action>
	                            <saverelation><![CDATA['. $this->saverelation .']]></saverelation>
	                            <calculation_method><![CDATA['. $this->calculation_method .']]></calculation_method>
								<tags>';
			foreach($this->tags as $tag){
				$string .= '<tag><![CDATA['. $tag .']]></tag>';
			}
						
			$string .= '</tags>
						<items>';
			foreach($this->items as $i){

			    $string .= '<item>
			    	<code><![CDATA['. $i['code'] .']]></code>
			    	<quantity><![CDATA['. $i['quantity'] .']]></quantity>
			        <description><![CDATA['. $i['description'] .']]></description>
			        <price><![CDATA['. $i['price'] .']]></price>
			        <price_incl><![CDATA['. round($i['price_incl']) .']]></price_incl>
			        <price_vat><![CDATA['. ($i['price_vat']) .']]></price_vat>
			        <vatpercentage><![CDATA['. $i['vatpercentage'] .']]></vatpercentage>
			        <discount><![CDATA['. $i['discount'] .']]></discount>
			        <categories><![CDATA['. $i['categories'] .']]></categories>
			        <ledgeraccount><![CDATA['. $i['ledgeraccount'] .']]></ledgeraccount>
			        
			    </item>';
			}
						   
			$string .= '</items>';

            if($this->payment != false){
                $string .= '<payment>
								    <transaction_id><![CDATA['. $this->payment->transaction_id .']]></transaction_id>
								    <currency><![CDATA['. $this->payment->currency .']]></currency>
								    <method><![CDATA['. $this->payment->method .']]></method>
								    <amount><![CDATA['. $this->payment->amount .']]></amount>
								    <date><![CDATA['. $this->payment->date .']]></date>
								    <description><![CDATA['. $this->payment->description .']]></description>
                                </payment>';
            }

            $string .= '<files>';
			foreach($this->files as $f){
				$string .= '<file url="'.$f['url'].'">'.$f['name'].'</file>';
			}
			$string .= '</files>
					</'. $this->documenttype .'>';



			$string .= '</request>';
			return $string;
		}
	}
}