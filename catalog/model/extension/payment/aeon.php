<?php
class ModelExtensionPaymentAeon extends Model {
  public function getMethod($address) {   
    $method_data = array( 
      'code' => 'aeon',
      'title' => 'Pay with AEON',
      'sort_order' => '',
      'terms' => '<a href="https://www.aeon.cash" target="_blank">What is AEON?</a>'
      );
    return $method_data;
  }
}
?>
