<?php
// Varianty: http://www.remicorson.com/woocommerce-custom-fields-for-variations/
if ( version_compare( WOOCOMMERCE_VERSION, '2.5', '>=' ) ) {
  add_action( 'woocommerce_variation_options_pricing', 'ceske_sluzby_variation_settings_fields', 10, 3 );
} else {
  add_action( 'woocommerce_variation_options', 'ceske_sluzby_variation_settings_fields', 10, 3 );
}

function ceske_sluzby_variation_settings_fields( $loop, $variation_data, $variation ) {
  $dodaci_doba = ceske_sluzby_zpracovat_dodaci_dobu_produktu( false, true );
  $class = "";
  $podpora_ean = get_option( 'wc_ceske_sluzby_xml_feed_heureka_podpora_ean' );
  if ( empty ( $podpora_ean ) ) {
    $class = "first";
    $params = array( 
      'id' => "ceske_sluzby_hodnota_ean[{$loop}]",
      'label' => 'EAN kód', 
      'description' => 'Zadejte hodnotu EAN.',
      'wrapper_class' => 'form-row form-row-' . $class,
      'style' => 'width: 91%',
      'value' => get_post_meta( $variation->ID, 'ceske_sluzby_hodnota_ean', true )
    );
    if ( version_compare( WOOCOMMERCE_VERSION, '2.5', '>=' ) ) {
      $params = array ( 'desc_tip' => 'true' ) + $params;
    }
    woocommerce_wp_text_input( $params );
  }
  if ( ! empty ( $dodaci_doba ) ) {
    if ( empty ( $class ) ) {
      $class = "first";
    } else {
      $class = "last";
    }
    $params = array( 
      'id' => "ceske_sluzby_dodaci_doba[{$loop}]",
      'label' => 'Dodací doba', 
      'description' => 'Dostupnost pro jednotlivé varianty.',
      'wrapper_class' => 'form-row form-row-' . $class,
      'style' => 'width: 91%',
      'value' => get_post_meta( $variation->ID, 'ceske_sluzby_dodaci_doba', true ),
      'options' => $dodaci_doba
    );
    if ( version_compare( WOOCOMMERCE_VERSION, '2.5', '>=' ) ) {
      $params = array ( 'desc_tip' => 'true' ) + $params;
    }
    woocommerce_wp_select( $params );
  }
}

add_action( 'woocommerce_save_product_variation', 'ceske_sluzby_save_variation_settings_fields', 10, 2 );
function ceske_sluzby_save_variation_settings_fields( $variation_id, $i ) {
  $dodaci_doba = $_POST['ceske_sluzby_dodaci_doba'][$i];
  $dodaci_doba_ulozeno = get_post_meta( $variation_id, 'ceske_sluzby_dodaci_doba', true );
  if ( ! empty ( $dodaci_doba ) || (string)$dodaci_doba === '0' ) {
    update_post_meta( $variation_id, 'ceske_sluzby_dodaci_doba', $dodaci_doba );
  } elseif ( isset( $dodaci_doba_ulozeno ) ) {
    delete_post_meta( $variation_id, 'ceske_sluzby_dodaci_doba' );  
  }

  $hodnota_ean = $_POST['ceske_sluzby_hodnota_ean'][$i];
  $hodnota_ean_ulozeno = get_post_meta( $variation_id, 'ceske_sluzby_hodnota_ean', true );
  if ( ! empty ( $hodnota_ean ) ) {
    update_post_meta( $variation_id, 'ceske_sluzby_hodnota_ean', $hodnota_ean );
  } elseif ( isset( $hodnota_ean_ulozeno ) ) {
    delete_post_meta( $variation_id, 'ceske_sluzby_hodnota_ean' );  
  }
}