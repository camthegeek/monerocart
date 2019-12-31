<?php
class ModelExtensionPaymentMonero extends Model {
  public function getMethod($address) {   
    $method_data = array( 
      'code' => 'monero',
      'title' => 'Pay with Monero',
      'sort_order' => '',
      'terms' => '<a href="https://www.getmonero.org" target="_blank">What is Monero?</a>'
      );
    return $method_data;
  }
}
?>