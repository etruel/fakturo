<?php

function fakturo_admin_tabs( $current = 'user-template' ) {
    $tabs = array( 'user-template' => __( 'User Template', FAKTURO_TEXT_DOMAIN ), 'print-template' => __( 'Print Template', FAKTURO_TEXT_DOMAIN ), 
    	'currencies' => __( 'Currencies', FAKTURO_TEXT_DOMAIN ), 'taxed' => __( 'Taxes', FAKTURO_TEXT_DOMAIN ), 
    	'tax_condition' => __( 'Tax Conditions', FAKTURO_TEXT_DOMAIN ), 'invoice_type' => __( 'Invoice type', FAKTURO_TEXT_DOMAIN ), 
    	'bank_entities' => __( 'Bank Entities', FAKTURO_TEXT_DOMAIN ), 'payment_type' => __( 'Payment Types', FAKTURO_TEXT_DOMAIN ), 
    	'repairs_status' => __( 'Repairs Status', FAKTURO_TEXT_DOMAIN ), 'packagings' => __( 'Packagings', FAKTURO_TEXT_DOMAIN ), 
    	'price_scales' => __( 'Price Scales', FAKTURO_TEXT_DOMAIN ), 'product_types' => __( 'Product Types', FAKTURO_TEXT_DOMAIN ),
    	'locations' => __( 'Locations', FAKTURO_TEXT_DOMAIN ), 'origins' => __( 'Origins', FAKTURO_TEXT_DOMAIN ),
    	'countries' => __( 'Countries', FAKTURO_TEXT_DOMAIN ), 'states' => __( 'Currencies', FAKTURO_TEXT_DOMAIN ),
    	'configuration' => __( 'Configuration', FAKTURO_TEXT_DOMAIN ));
    echo '<div id="icon-themes" class="icon32"><br></div>';
    echo '<h2 class="nav-tab-wrapper">';
    if (isset($_GET['tab'])) {
    	$current = $_GET['tab'];
    }
    foreach( $tabs as $tab => $name ){
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        echo "<a class='nav-tab$class' href='?page=fakturo%2Fsettings%2Ffakturo_settings.php&tab=$tab'>$name</a>";

    }
    echo '</h2>';
}

function printSettingRowSimpleTaxonomy($data, $tab, $section, $deleteFieldName) {
  foreach ($data as $key => $value) {
    echo "<tr><td>$value->name</td><td><a href=\"?page=fakturo%2Fsettings%2Ffakturo_settings.php&tab=$tab&section=$section&$deleteFieldName=$value->term_id\" class=\"button\">" . __( 'Remove', FAKTURO_TEXT_DOMAIN ) . "</a></td></tr>";
  }
}

function fakturo_admin_tabs_section( $current = 'user-template' ) {
    // tabs
    $tabs = FakturoSettingComponent::getFakturoSettingTabs();
    echo '<div id="icon-themes" class="icon32"><br></div>';
    echo '<h2 class="nav-tab-wrapper">';
    if (isset($_GET['tab'])) {
      $currentTab = $_GET['tab'];
    } else {
      $currentTab = key($tabs);
    }
    foreach( $tabs as $tab => $name ){
        $class = ( $tab == $currentTab ) ? ' nav-tab-active' : '';
        echo "<a class='nav-tab$class' href='?page=fakturo%2Fsettings%2Ffakturo_settings.php&tab=$tab'>$name</a>";
    }
    echo '</h2>';

    // sections
    $sections = FakturoSettingComponent::getFakturoSettingSection($currentTab);
    echo '<ul class="subsubsub">';
    if (isset($_GET['section'])) {
      $currentSection = $_GET['section'];
    } else {
      $currentSection = key($sections);
    }
    $endSection = end($sections);
    foreach ($sections as $key => $section) {
      $class = ( $key == $currentSection ) ? ' current' : '';
      $delimiter = ($section != $endSection) ? ' | ' : '';
      echo "<li><a class='$class' href='?page=fakturo%2Fsettings%2Ffakturo_settings.php&tab=$currentTab&section=$key'>$section</a>$delimiter</li>";
    }
    echo '</ul>';
}

function printSettingRowPrintTemplateTaxonomy($data, $tab, $section, $deleteFieldName) {
  foreach ($data as $key => $value) {
  	$description = get_term_meta( $value->term_id, 'description');
  	$description = isset($description[0])?$description[0]:'';
  	$content = get_term_meta( $value->term_id, 'content');
  	$content = isset($content[0])?$content[0]:'';
  	$assigned_to = get_term_meta( $value->term_id, 'assigned_to');
  	$assigned_to = isset($assigned_to[0])?$assigned_to[0]:'';
	
	$nonce= wp_create_nonce('preview-nonce');
	$actionurl = FAKTURO_URI . 'settings/views/print_template_preview.php?p='.$value->term_id.'&_wpnonce=' . $nonce;
	$actionjs = "javascript:window.open('$actionurl','$value->name','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=700, height=600');";
	
    echo "<tr>
    <td>$value->name</td>
    <td>$description</td>
    <td>" . substr(htmlspecialchars(stripslashes($content)), 0, 200) . "</td>
    <td>$assigned_to</td>
    <td><a class=\"button\" href=\"?page=fakturo%2Fsettings%2Ffakturo_settings.php&tab=tables&section=print-template&action=edit&id=$value->term_id\">" . __( 'Edit', FAKTURO_TEXT_DOMAIN ) . "</a>
	<a href=\"javascrit:void(0);\" onclick=\"$actionjs return false;\" title=\"" . esc_attr(__("See a preview of this Print Template. (Open a PopUp window)", FAKTURO_TEXT_DOMAIN)) . "\" class=\"button\">" . __('Preview', FAKTURO_TEXT_DOMAIN) . "</a>
	<a href=\"?page=fakturo%2Fsettings%2Ffakturo_settings.php&tab=$tab&section=$section&$deleteFieldName=$value->term_id\" class=\"button\">" . __( 'Remove', FAKTURO_TEXT_DOMAIN ) . "</a></td>
    </tr>";
  }
}

function printSettingRowEmailTaxonomy($data, $tab, $section, $deleteFieldName) {
  foreach ($data as $key => $value) {
    $description = get_term_meta( $value->term_id, 'description');
    $description = isset($description[0])?$description[0]:'';
    $text = get_term_meta( $value->term_id, 'text');
    $text = isset($text[0])?$text[0]:'';
    echo "<tr>
    <td>$value->name</td>
    <td>$description</td>
    <td>" . substr(htmlspecialchars(stripslashes($text)), 0, 200) . "</td>
    <td><a class=\"button\" href=\"?page=fakturo%2Fsettings%2Ffakturo_settings.php&tab=extensions&section=emails&action=edit&id=$value->term_id\">" . __( 'Edit', FAKTURO_TEXT_DOMAIN ) . "</a>
    <a class=\"button\" href=\"?page=fakturo%2Fsettings%2Ffakturo_settings.php&tab=extensions&section=emails&action=preview&id=$value->term_id\">" . __( 'Test', FAKTURO_TEXT_DOMAIN ) . "</a>
    <a href=\"?page=fakturo%2Fsettings%2Ffakturo_settings.php&tab=$tab&section=$section&$deleteFieldName=$value->term_id\" class=\"button\">" . __( 'Remove', FAKTURO_TEXT_DOMAIN ) . "</a></td>
    </tr>";
  }
}

function printSettingRowCurrencyTaxonomy($data, $tab, $section, $deleteFieldName) {
  foreach ($data as $key => $value) {
  	$symbol = get_term_meta( $value->term_id, 'symbol');
  	$symbol = isset($symbol[0])?$symbol[0]:'';
  	$rate = get_term_meta( $value->term_id, 'rate');
  	$rate = isset($rate[0])?$rate[0]:'';
  	$reference = get_term_meta( $value->term_id, 'reference');
  	$reference = isset($reference[0])?$reference[0]:'';
    $plural = get_term_meta( $value->term_id, 'plural');
    $plural = isset($plural[0])?$plural[0]:'';
    echo "<tr>
    <td>$value->name</td>
    <td>$symbol</td>
    <td>$rate</td>
    <td>$reference</td>
    <td>$plural</td>
    <td><a href=\"?page=fakturo%2Fsettings%2Ffakturo_settings.php&tab=$tab&section=$section&$deleteFieldName=$value->term_id\" class=\"button\">" . __( 'Remove', FAKTURO_TEXT_DOMAIN ) . "</a></td>
    </tr>";
  }
}

function printSettingRowTaxesTaxonomy($data, $tab, $section, $deleteFieldName) {
  foreach ($data as $key => $value) {
    $percent = get_term_meta( $value->term_id, 'percent');
    $percent = isset($percent[0])?$percent[0]:'';
    echo "<tr>
    <td>$value->name</td>
    <td>$percent</td>
    <td><a href=\"?page=fakturo%2Fsettings%2Ffakturo_settings.php&tab=$tab&section=$section&$deleteFieldName=$value->term_id\" class=\"button\">" . __( 'Remove', FAKTURO_TEXT_DOMAIN ) . "</a></td>
    </tr>";
  }
}

function printSettingRowInvoiceTypeTaxonomy($data, $tab, $section, $deleteFieldName) {
  foreach ($data as $key => $value) {
    $short_name = get_term_meta( $value->term_id, 'short_name');
    $short_name = isset($short_name[0])?$short_name[0]:'';
    $taxes = get_term_meta( $value->term_id, 'taxes');
    $taxes = isset($taxes[0])?$taxes[0]:'';
    $default = get_term_meta( $value->term_id, 'default');
    $default = isset($default[0])?$default[0]:'';
    $sum = get_term_meta( $value->term_id, 'sum');
    $sum = isset($sum[0])?$sum[0]:'';
    echo "<tr>
    <td>$value->name</td>
    <td>$short_name</td>
    <td>$taxes</td>
    <td>$default</td>
    <td>$sum</td>
    <td><a href=\"?page=fakturo%2Fsettings%2Ffakturo_settings.php&tab=$tab&section=$section&$deleteFieldName=$value->term_id\" class=\"button\">" . __( 'Remove', FAKTURO_TEXT_DOMAIN ) . "</a></td>
    </tr>";
  }
}

function printSettingRowPriceScalesTaxonomy($data, $tab, $section, $deleteFieldName) {
  foreach ($data as $key => $value) {
    $percent = get_term_meta( $value->term_id, 'percent');
    $percent = isset($percent[0])?$percent[0]:'';
    $default = get_term_meta( $value->term_id, 'default');
    $default = isset($default[0])?$default[0]:'';
    echo "<tr>
    <td>$value->name</td>
    <td>$percent</td>
    <td>$default</td>
    <td><a href=\"?page=fakturo%2Fsettings%2Ffakturo_settings.php&tab=$tab&section=$section&$deleteFieldName=$value->term_id\" class=\"button\">" . __( 'Remove', FAKTURO_TEXT_DOMAIN ) . "</a></td>
    </tr>";
  }
}

function printSettingRowStatesTaxonomy($data, $tab, $section, $deleteFieldName) {
  foreach ($data as $key => $value) {
    $country = get_term_meta( $value->term_id, 'country');
    $country = isset($country[0])?$country[0]:'';
    echo "<tr>
    <td>$value->name</td>
    <td>$country</td>
    <td><a href=\"?page=fakturo%2Fsettings%2Ffakturo_settings.php&tab=$tab&section=$section&$deleteFieldName=$value->term_id\" class=\"button\">" . __( 'Remove', FAKTURO_TEXT_DOMAIN ) . "</a></td>
    </tr>";
  }
}

?>
