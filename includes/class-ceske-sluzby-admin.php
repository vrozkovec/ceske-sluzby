<?php
// http://docs.woothemes.com/document/adding-a-section-to-a-settings-tab/
class WC_Settings_Tab_Ceske_Sluzby_Admin {

  public static function init() {
    add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 100 );
    add_action( 'woocommerce_settings_tabs_ceske-sluzby', __CLASS__ . '::settings_tab' );
    add_action( 'woocommerce_update_options_ceske-sluzby', __CLASS__ . '::update_settings' );
    add_action( 'woocommerce_sections_ceske-sluzby', __CLASS__ . '::output_sections' );
    add_filter( 'woocommerce_admin_settings_sanitize_option', __CLASS__ . '::admin_settings_sanitize_option', 10, 3 );
  }
  
  public static function output_sections() {
    // Neduplikovat do budoucna tuto funkci...
    global $current_section;
    $aktivace_xml = get_option( 'wc_ceske_sluzby_heureka_xml_feed-aktivace' );
    $aktivace_certifikatu = get_option( 'wc_ceske_sluzby_heureka_certifikat_spokojenosti-aktivace' );
    $aktivace_dodaci_doby = get_option( 'wc_ceske_sluzby_dalsi_nastaveni_dodaci_doba-aktivace' );
    $sections = array(
      '' => 'Základní nastavení'
    );
    if ( $aktivace_xml == "yes" ) {
      $sections['xml-feed'] = 'XML feed';
    }
    if ( $aktivace_certifikatu == "yes" ) {
      $sections['certifikat-spokojenosti'] = 'Certifikát spokojenosti';
    }
    if ( $aktivace_dodaci_doby == "yes" ) {
      $sections['dodaci-doba'] = 'Dodací doba';
    }
    if ( empty( $sections ) ) {
      return;
    }
    echo '<ul class="subsubsub">';
    $array_keys = array_keys( $sections );
    foreach ( $sections as $id => $label ) {
      echo '<li><a href="' . admin_url( 'admin.php?page=wc-settings&tab=ceske-sluzby&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
    }
    echo '</ul><br class="clear" />';
  }

  public static function add_settings_tab( $settings_tabs ) {
    $settings_tabs['ceske-sluzby'] = 'České služby';
    return $settings_tabs;
  }

  public static function settings_tab() {
    global $current_section;
    $settings = self::get_settings( $current_section );
    woocommerce_admin_fields( $settings );
  }

  public static function update_settings() {
    global $current_section;
    woocommerce_update_options( self::get_settings( $current_section ) );
  }

  public static function admin_settings_sanitize_option( $value, $option, $raw_value ) {
    if ( 'wc_ceske_sluzby_dodaci_doba_format_zobrazeni' == $option['id'] || 'wc_ceske_sluzby_preorder_format_zobrazeni' == $option['id'] || 'wc_ceske_sluzby_dodatecne_produkty_format_zobrazeni' == $option['id'] ) {
      $value = wp_kses( $raw_value, wp_kses_allowed_html( 'post' ) );
    }
    return $value; 
  }

  public static function get_settings( $current_section = '' ) {
    global $current_section;

    if ( '' == $current_section ) {
      $settings = array(
        array(
          'title' => 'Služby pro WordPress',
          'type' => 'title',
          'desc' => 'Pokud nebude konkrétní hodnota vyplněna, tak se nebude příslušná služba vůbec spouštět.',
          'id' => 'wc_ceske_sluzby_title'
        ),
        array(
          'title' => 'Heureka.cz (.sk)',
          'type' => 'title',
          'desc' => '',
          'id' => 'wc_ceske_sluzby_heureka_title'
        ),
        array(
          'title' => 'API klíč: Ověřeno zákazníky',
          'type' => 'text',
          'desc' => 'API klíč pro službu Ověřeno zákazníky naleznete <a href="http://sluzby.' . HEUREKA_URL . '/sluzby/certifikat-spokojenosti/">zde</a>.',
          'id' => 'wc_ceske_sluzby_heureka_overeno-api',
          'css' => 'width: 300px'
        ),
        array(
          'title' => 'API klíč: Měření konverzí',
          'type' => 'text',
          'desc' => 'API klíč pro službu Měření konverzí naleznete <a href="http://sluzby.' . HEUREKA_URL . '/obchody/mereni-konverzi/">zde</a>. Heureka může ještě nějaký čas hlásit, že nebyla služba zprovozněna (dokud neproběhne nějaká objednávka zákazníka z Heureky).',
          'id'   => 'wc_ceske_sluzby_heureka_konverze-api',
          'css'   => 'width: 300px'
        ),
        array(
          'title' => 'Aktivovat certifikát',
          'type' => 'checkbox',
          'desc' => 'Nastavení pro zobrazení certifikátu spokojenosti bude po aktivaci dostupné <a href="' . admin_url(). 'admin.php?page=wc-settings&tab=ceske-sluzby&section=certifikat-spokojenosti">zde</a>. Obchod musí certifikát nejdříve získat, což snadno ověříte <a href="http://sluzby.' . HEUREKA_URL . '/sluzby/certifikat-spokojenosti/">zde</a>',
          'id' => 'wc_ceske_sluzby_heureka_certifikat_spokojenosti-aktivace'
        ),
        array(
          'title' => 'Aktivovat XML feed',
          'type' => 'checkbox',
          'desc' => 'Nastavení pro XML feed bude po aktivaci dostupné <a href="' . admin_url(). 'admin.php?page=wc-settings&tab=ceske-sluzby&section=xml-feed">zde</a>.',
          'id' => 'wc_ceske_sluzby_heureka_xml_feed-aktivace'
        ),
        array(
          'title' => 'Aktivovat zobrazení recenzí',
          'type' => 'checkbox',
          'desc' => 'Po aktivaci můžete zobrazit aktuální recenze pomocí zkráceného zápisu (shortcode): <code>[heureka-recenze-obchodu]</code>.
                     Zobrazovat se budou všechny, pokud neomezíte jejich počet pomocí parametru <code>limit</code>, např. <code>[heureka-recenze-obchodu limit="10"]</code>.
                     Pozor, musí být zadán platný API klíč pro službu Ověřeno zákazníky.',
          'id' => 'wc_ceske_sluzby_heureka_recenze_obchodu-aktivace'
        ),
        array(
          'type' => 'sectionend',
          'id' => 'wc_ceske_sluzby_heureka_title'
        ),
        array(
          'title' => 'Sklik.cz',
          'type' => 'title',
          'desc' => '',
          'id' => 'wc_ceske_sluzby_sklik_title'
        ),
        array(
          'title' => 'ID konverzního kódu',
          'type' => 'text',
          'desc' => 'ID získaného kódu pro měření konverzí naleznete <a href="https://www.sklik.cz/seznam-konverzi">zde</a>. Je třeba vytvořit konverzní kód typu "vytvoření objednávky" a z něho získat potřebné ID.',
          'id' => 'wc_ceske_sluzby_sklik_konverze-objednavky'
        ),
        array(
          'title' => 'ID pro retargeting',
          'type' => 'text',
          'desc' => 'ID získaného kódu pro retargeting naleznete <a href="https://www.sklik.cz/retargeting">zde</a>. Je třeba kliknout na odkaz "Zobrazit retargetingový kód" a z něho získat potřebné ID. Manuál pro použití této služby naleznete <a href="https://napoveda.sklik.cz/typy-cileni/retargeting/">zde</a>.',
          'id' => 'wc_ceske_sluzby_sklik_retargeting'
        ),
        array(
          'type' => 'sectionend',
          'id' => 'wc_ceske_sluzby_sklik_title'
        ),
        array(
          'title' => 'Srovnáme.cz',
          'type' => 'title',
          'desc' => '',
          'id' => 'wc_ceske_sluzby_srovname_title'
        ),
        array(
          'title' => 'Identifikační klíč',
          'type' => 'text',
          'desc' => 'Identifikační klíč pro měření konverzí naleznete <a href="http://www.srovname.cz/muj-obchod">zde</a>.',
          'id' => 'wc_ceske_sluzby_srovname_konverze-objednavky'
        ),
        array(
          'type' => 'sectionend',
          'id' => 'wc_ceske_sluzby_srovname_title'
        ),
        array(
          'title' => 'Další nastavení',
          'type' => 'title',
          'desc' => '',
          'id' => 'wc_ceske_sluzby_dalsi_nastaveni_title'
        ),
        array(
          'title' => 'Sledování zásilek',
          'type' => 'checkbox',
          'desc' => 'Aktivovat možnost zadávání informací pro sledování zásilek u každé objednávky. Speciální notifikační email můžete nastavit <a href="' . admin_url(). 'admin.php?page=wc-settings&tab=email&section=wc_email_ceske_sluzby_sledovani_zasilek">zde</a>.',
          'id' => 'wc_ceske_sluzby_dalsi_nastaveni_sledovani-zasilek'
        ),
        array(
          'title' => 'Dodací doba',
          'type' => 'checkbox',
          'desc' => 'Aktivovat možnost podrobného nastavení dodací doby, které bude dostupné <a href="' . admin_url(). 'admin.php?page=wc-settings&tab=ceske-sluzby&section=dodaci-doba">zde</a>.',
          'id' => 'wc_ceske_sluzby_dalsi_nastaveni_dodaci_doba-aktivace'
        ),
        array(
          'title' => 'Možnost změny objednávek pro dobírku',
          'type' => 'checkbox',
          'desc' => 'Povolí možnost změny objednávek, které jsou provedené prostřednictvím dobírky.',
          'id' => 'wc_ceske_sluzby_dalsi_nastaveni_dobirka-zmena'
        ),
        array(
          'title' => 'Pouze doprava zdarma',
          'type' => 'checkbox',
          'desc' => 'Omezit nabídku dopravy, pokud je dostupná zdarma.',
          'id' => 'wc_ceske_sluzby_dalsi_nastaveni_doprava-pouze-zdarma'
        ),
        array(
          'type' => 'sectionend',
          'id' => 'wc_ceske_sluzby_dalsi_nastaveni_title'
        )
      );
    }

    if ( 'xml-feed' == $current_section ) {
      $settings_before = array(
        array(
          'title' => 'XML feed',
          'type' => 'title',
          'desc' => 'Zde budou postupně přidávána další nastavení.',
          'id' => 'wc_ceske_sluzby_xml_feed_title'
        ),
        array(
          'title' => 'Aktivovat shortcodes',
          'type' => 'checkbox',
          'desc' => 'Možnost spouštění zkrácených zápisů (shortcode). V základním nastavení jsou zcela ignorovány, ale pokud obsahují informace potřebné pro popis produktů, tak je můžete nechat zobrazovat.',
          'id' => 'wc_ceske_sluzby_xml_feed_shortcodes-aktivace'
        ),
        array(
          'type' => 'sectionend',
          'id' => 'wc_ceske_sluzby_xml_feed_title'
        ),
        array(
          'title' => 'Heureka.cz (.sk)',
          'type' => 'title',
          'desc' => 'Průběžně generovaný feed je dostupný <a href="' . site_url() . '/?feed=heureka">zde</a>. Pro větší eshopy je ale vhodná spíše varianta v podobě <a href="' . WP_CONTENT_URL . '/heureka.xml">souboru</a>, který je aktualizován automaticky jednou denně a v případě velkého množství produktů postupně po částech (1000 produktů). Podrobný manuál naleznete <a href="http://sluzby.' . HEUREKA_URL . '/napoveda/xml-feed/">zde</a>.',
          'id' => 'wc_ceske_sluzby_xml_feed_heureka_title'
        ),
        array(
          'title' => 'Aktivovat feed',
          'type' => 'checkbox',
          'desc' => 'Povolí možnost postupného generování .xml souboru pro Heureka.cz (.sk) a zobrazí příslušná nastavení v administraci.',
          'id' => 'wc_ceske_sluzby_xml_feed_heureka-aktivace'
        ),
        array(
          'title' => 'Dodací doba',
          'type' => 'number',
          'desc' => 'Zboží může být skladem (0), dostupné do tří dnů (1 - 3), do týdne (4 - 7), do dvou týdnů (8 - 14), do měsíce (15 - 30) či více než měsíc (31 a více).',
          'id' => 'wc_ceske_sluzby_xml_feed_heureka_dodaci_doba',
          'css' => 'width: 50px',
          'custom_attributes' => array(
            'min' => 0,
            'step' => 1
          )
        ),
        array(
          'title' => 'Podpora EAN',
          'type' => 'text',
          'desc' => 'Pokud doplňujete EAN kód do pole pro katalogové číslo, tak zadejte hodnotu SKU. Pokud máte své vlastní řešení pro doplňování EAN kódů, tak zadejte název příslušného uživatelského pole (pozor na malá a velká písmena). Pokud zůstane pole prázdné, tak bude automaticky zapnuta možnost nastavit EAN u každého produktu či varianty.',
          'id' => 'wc_ceske_sluzby_xml_feed_heureka_podpora_ean',
          'css' => 'width: 250px',
        ),
        array(
          'title' => 'Podpora výrobců',
          'type' => 'text',
          'desc' => 'Zadat můžete název příslušné taxonomie (např. na základě používaného pluginu), vlastnosti (jednoduchá textová nebo v podobě taxonomie), uživatelského pole nebo libovolný text pro element <code>MANUFACTURER</code>. Další podrobnosti (a dostupné taxonomie) naleznete dole u nastavení dodatečného označení produktů.',
          'id' => 'wc_ceske_sluzby_xml_feed_heureka_podpora_vyrobcu',
          'css' => 'width: 250px',
        ),
        array(
          'title' => 'Stav produktů',
          'type' => 'select',
          'desc_tip' => 'Zvolte stav produktů, který bude hromadně použit pro celý eshop (můžete měnit na úrovni kategorie či produktu).',
          'id' => 'wc_ceske_sluzby_xml_feed_heureka_stav_produktu',
          'class' => 'wc-enhanced-select',
          'options' => array(
            '' => '- Vyberte -',
            'used' => 'Použité (bazar)',
            'refurbished' => 'Repasované'
          ),
        ),
        array(
          'title' => 'Název produktů',
          'type' => 'text',
          'desc' => 'Zvolte obecný název produktů (<code>PRODUCTNAME</code>), který bude hromadně použit pro celý eshop (můžete měnit na úrovni kategorie či produktu). Ve výchozím nastavení je automaticky použita hodnota <code>{PRODUCTNAME} | {KATEGORIE} | {NAZEV} {VLASTAXVID}</code>, což je název doplněný o přiřazené (viditelné) vlastnosti v podobě taxonomií, pokud není vyplněna hodnota <code>PRODUCTNAME</code> na úrovni produktu či kategorie. Dále je možné použít hodnoty některých elementů, např. <code>{MANUFACTURER}</code>, nebo konkrétních vlastností, např. <code>{pa_barva}</code>.',
          'id' => 'wc_ceske_sluzby_xml_feed_heureka_nazev_produktu',
          'css' => 'width: 400px',
        ),
        array(
          'title' => 'Název variant',
          'type' => 'text',
          'desc' => 'Zvolte obecný název variant (<code>PRODUCTNAME</code>), který bude hromadně použit pro celý eshop (můžete měnit na úrovni kategorie či produktu). Ve výchozím nastavení je automaticky použita hodnota <code>{PRODUCTNAME} {VLASVAR} | {KATEGORIE} | {NAZEV} {VLASVAR}</code>, což je název doplněný o přiřazené vlastnosti variant, pokud není vyplněna hodnota <code>PRODUCTNAME</code> na úrovni produktu či kategorie. Dále je možné použít hodnoty některých elementů, např. <code>{MANUFACTURER}</code>, nebo konkrétních vlastností, např. <code>{pa_barva}</code>.',
          'id' => 'wc_ceske_sluzby_xml_feed_heureka_nazev_variant',
          'css' => 'width: 400px',
        ),
        array(
          'type' => 'sectionend',
          'id' => 'wc_ceske_sluzby_xml_feed_heureka_title'
        ),
        array(
          'title' => 'Zbozi.cz',
          'type' => 'title',
          'desc' => 'Průběžně generovaný feed je dostupný <a href="' . site_url() . '/?feed=zbozi">zde</a>. Pro větší eshopy je ale vhodná spíše varianta v podobě <a href="' . WP_CONTENT_URL . '/zbozi.xml">souboru</a>, který je aktualizován automaticky jednou denně a v případě velkého množství produktů postupně po částech (1000 produktů). Podrobný manuál naleznete <a href="http://napoveda.seznam.cz/cz/zbozi/specifikace-xml-pro-obchody/specifikace-xml-feedu/">zde</a>. Základní nastavení je stejné jako pro Heureka.cz.',
          'id' => 'wc_ceske_sluzby_xml_feed_zbozi_title'
        ),
        array(
          'title' => 'Aktivovat feed',
          'type' => 'checkbox',
          'desc' => 'Povolí možnost postupného generování .xml souboru pro Zbozi.cz a zobrazí příslušná nastavení v administraci.',
          'id' => 'wc_ceske_sluzby_xml_feed_zbozi-aktivace'
        ),
        array(
          'title' => 'Doplňkové informace',
          'type' => 'multiselect',
          'desc' => 'Zvolte položky, které budete chtít používat jako doplňkové informace k produktu (element <code>EXTRA_MESSAGE</code>). Jednotlivé hodnoty bude po uložení možné nastavit na úrovni produktu, kategorie a eshopu.',
          'id' => 'wc_ceske_sluzby_xml_feed_zbozi_extra_message-aktivace',
          'class' => 'wc-enhanced-select',
          'options' => ceske_sluzby_ziskat_nastaveni_zbozi_extra_message(),
          'custom_attributes' => array(
            'data-placeholder' => 'EXTRA_MESSAGE'
          )
        )
      );

      $global_extra_message = get_option( 'wc_ceske_sluzby_xml_feed_zbozi_extra_message-aktivace' );
      if ( ! empty( $global_extra_message ) ) {
        $extra_message_array = ceske_sluzby_ziskat_nastaveni_zbozi_extra_message();
        foreach ( $global_extra_message as $extra_message ) {
          $extra_message_desc = 'Po zaškrtnutí budou všechny produkty v eshopu označeny příslušnou doplňkovou informací.';
          if ( $extra_message == "free_delivery" ) {
            $extra_message_desc = 'Po zaškrtnutí bude na všechny produkty v eshopu aplikováno nastavení dopravy zdarma.';
          }
          $settings_before[] =
          array(
            'title' => $extra_message_array[ $extra_message ],
            'type' => 'checkbox',
            'desc' => $extra_message_desc,
            'id' => 'wc_ceske_sluzby_xml_feed_zbozi_extra_message[' . $extra_message . ']'
          );
        }
      }

      $settings_after = array(
        array(
          'title' => 'Erotický obsah',
          'type' => 'checkbox',
          'desc' => 'Označit všechny produkty jako erotické. Pokud chcete označit pouze některé kategorie, tak to můžete nastavit přímo tam.',
          'id' => 'wc_ceske_sluzby_xml_feed_heureka_erotika'
        ),
        array(
          'type' => 'sectionend',
          'id' => 'wc_ceske_sluzby_xml_feed_zbozi_title'
        ),
        array(
          'title' => 'Pricemania.cz (.sk)',
          'type' => 'title',
          'desc' => 'Generovaný feed je dostupný v podobě .xml <a href="' . WP_CONTENT_URL . '/pricemania.xml">souboru</a>. Aktualizace probíhá automaticky jednou denně a v případě velkého množství produktů postupně po částech (1000 produktů). Podrobný manuál naleznete <a href="http://files.pricemania.sk/pricemania-struktura-xml-feedu.pdf">zde</a>. Základní nastavení je stejné jako pro Heureka.cz.',
          'id' => 'wc_ceske_sluzby_xml_feed_pricemania_title'
        ),
        array(
          'title' => 'Aktivovat feed',
          'type' => 'checkbox',
          'desc' => 'Povolí možnost postupného generování .xml souboru pro Pricemania.cz (.sk) a zobrazí příslušná nastavení v administraci.',
          'id' => 'wc_ceske_sluzby_xml_feed_pricemania-aktivace'
        ),
        array(
          'title' => 'Poštovné',
          'type' => 'number',
          'desc' => 'Uvedeno může být nejnižší základní poštovné (zadávejte konkrétní číslo, pokud je poštovné zdarma tak nulu).',
          'id' => 'wc_ceske_sluzby_xml_feed_pricemania_postovne',
          'css' => 'width: 50px',
          'custom_attributes' => array(
            'min' => 0,
            'step' => 1
          )
        ),
        array(
          'type' => 'sectionend',
          'id' => 'wc_ceske_sluzby_xml_feed_pricemania_title'
        ),
        array(
          'title' => 'Google.cz (.sk)',
          'type' => 'title',
          'desc' => 'Průběžně generovaný feed je dostupný <a href="' . site_url() . '/?feed=google">zde</a>. Podrobný manuál naleznete <a href="https://support.google.com/merchants/answer/7052112">zde</a>.
                     Automaticky je použito nastavení z ostatních feedů.',
          'id' => 'wc_ceske_sluzby_xml_feed_google_title'
        ),
        array(
          'type' => 'sectionend',
          'id' => 'wc_ceske_sluzby_xml_feed_google_title'
        ),
        array(
          'title' => 'Dodatečné označení produktů',
          'type' => 'title',
          'desc' => 'Produkty je možné rozdělit do speciálních skupin, např. podle prodejnosti, marže, atd (manuál pro <a href="http://napoveda.seznam.cz/cz/zbozi/specifikace-xml-pro-obchody/specifikace-xml-feedu/#CUSTOM_LABEL">Zbozi.cz</a> a <a href="https://support.google.com/merchants/answer/188494?hl=cs#customlabel">Google</a>).
                     Dostupné taxonomie: ' . ceske_sluzby_zobrazit_dostupne_taxonomie( 'obecne', false ) . '
                     Dostupné vlastnosti v podobě taxonomií: ' . ceske_sluzby_zobrazit_dostupne_taxonomie( 'vlastnosti', false ) . '
                     Podporovány jsou také názvy jednoduchých textových vlastností nebo uživatelských polí.',
          'id' => 'wc_ceske_sluzby_xml_feed_dodatecne_oznaceni_title'
        ),
        array(
          'title' => 'Definice skupin',
          'type' => 'textarea',
          'desc_tip' => 'Na každém řádku musí být samostatné uveden konkrétní název, kterým bude skupina definována.',
          'css' => 'width: 30%; height: 105px;',
          'id' => 'wc_ceske_sluzby_xml_feed_dodatecne_oznaceni'
        ),
        array(
          'type' => 'sectionend',
          'id' => 'wc_ceske_sluzby_xml_feed_dodatecne_oznaceni_title'
        ),
      );
      $settings = array_merge( $settings_before, $settings_after );
    }

    if ( 'certifikat-spokojenosti' == $current_section ) {
      $settings = array(
        array(
          'title' => 'Ověřeno zákazníky: Certifikát spokojenosti',
          'type' => 'title',
          'desc' => 'Nastavení pro zobrazování certifikátu spokojenosti na webu.',
          'id' => 'wc_ceske_sluzby_heureka_certifikat_spokojenosti_title'
        ),
        array(
          'title' => 'Základní umístění',
          'type' => 'radio',
          'default' => 'vlevo',
          'options' => array(
            'vlevo' => 'Vlevo',
            'vpravo' => 'Vpravo',
				  ),
          'id' => 'wc_ceske_sluzby_heureka_certifikat_spokojenosti_umisteni'
        ),
        array(
          'title' => 'Odsazení shora (px)',
          'type' => 'number',
          'default' => 60,
          'desc' => 'Zadávejte hodnotu pro odsazení shora v pixelech.',
          'id' => 'wc_ceske_sluzby_heureka_certifikat_spokojenosti_odsazeni',
          'css' => 'width: 50px',
          'custom_attributes' => array(
            'min' => 0,
            'step' => 10
          )
        ),
        array(
          'type' => 'sectionend',
          'id' => 'wc_ceske_sluzby_heureka_certifikat_spokojenosti_title'
        )
      );
    }
    
    if ( 'dodaci-doba' == $current_section ) {
      $settings = array(
        array(
          'title' => 'Dodací doba',
          'type' => 'title',
          'desc' => 'Možnost nastavení dodací doby u jednotlivých produktů. Zvolené hodnoty se budou automaticky zobrazovat v XML feedech.',
          'id' => 'wc_ceske_sluzby_dodaci_doba_title'
        ),
        array(
          'title' => 'Hodnoty pro dodací dobu',
          'type' => 'textarea',
          'desc' => 'Na každém řádku musí být uvedena číselná hodnota (počet dnů) oddělená pomocí znaku <code>|</code> od zobrazovaného textu.',
          'default' => sprintf( '1|Skladem zítra%1$s3|Dostupné do 3 dnů%1$s7|Na objednávku do týdne', PHP_EOL ),
          'css' => 'width: 40%; height: 85px;',
          'id' => 'wc_ceske_sluzby_dodaci_doba_hodnoty'
        ),
        array(
          'title' => 'Zobrazování na webu',
          'type' => 'multiselect',
          'desc' => 'Dodací dobu (případně datum předobjednávky) je možné zobrazovat na různých místech webu.',
          'id' => 'wc_ceske_sluzby_dodaci_doba_zobrazovani',
          'class' => 'wc-enhanced-select',
          'options' => array(
            'get_availability_text' => 'Detail produktu (náhrada textu pro sklad)',
            'before_add_to_cart_form' => 'Detail produktu (pod textem pro sklad)',
            'after_shop_loop_item' => 'Archiv'
          ),
          'custom_attributes' => array(
            'data-placeholder' => 'Zobrazování dodací doby'
          )
        ),
        array(
          'title' => 'Formát zobrazení',
          'type' => 'text',
          'desc' => 'Na webu můžete přesně definovat libovolný text (včetně HTML) s použitím výše zadaných hodnot <code>{VALUE}</code> (počet dní) nebo <code>{TEXT}</code> (příslušný text). Pokud není nic vyplněno, tak je použit jednoduchý odstavec s třídou <code>dodaci-doba</code>.',
          'id' => 'wc_ceske_sluzby_dodaci_doba_format_zobrazeni',
          'css' => 'width: 500px'
        ),
        array(
          'title' => 'Dodatečné produkty',
          'type' => 'text',
          'desc' => 'Na webu můžete přesně definovat libovolný text (včetně HTML) s použitím výše zadaných hodnot <code>{VALUE}</code> (počet dní) nebo <code>{TEXT}</code> (doplňující text).',
          'id' => 'wc_ceske_sluzby_dodatecne_produkty_format_zobrazeni',
          'css' => 'width: 500px'
        ),
        array(
          'title' => 'Intervaly počtu produktů',
          'type' => 'textarea',
          'desc' => 'Na každém řádku musí být uvedena číselná hodnota (dolní hranice počtu produktů) oddělená pomocí znaku <code>|</code> od zobrazovaného textu.
                     Použít můžete také hodnotu <code>{VALUE}</code>, která zobrazí přesný počet produktů skladem.
                     Automaticky je také generována CSS třída ve formátu <code>skladem-{VALUE}</code>.',
          'default' => sprintf( '0|Skladem: {VALUE}%1$s5|Skladem 5+%1$s10|Skladem 10+', PHP_EOL ),
          'css' => 'width: 40%; height: 85px;',
          'id' => 'wc_ceske_sluzby_dodaci_doba_intervaly'
        ),
        array(
          'title' => 'Předobjednávky',
          'type' => 'checkbox',
          'desc' => 'Povolí možnost zadávat a zobrazovat datum předobjednávky u jednotlivých produktů.',
          'id' => 'wc_ceske_sluzby_preorder-aktivace'
        ),
        array(
          'title' => 'Formát zobrazení',
          'type' => 'text',
          'desc' => 'Na webu můžete přesně definovat libovolný text (včetně HTML) s použitím zadaného data pro předobjednávku <code>{DATUM}</code>. Pokud není nic vyplněno, tak je použit jednoduchý odstavec s třídou <code>predobjednavka</code>.',
          'id' => 'wc_ceske_sluzby_preorder_format_zobrazeni',
          'css' => 'width: 500px'
        ),
        array(
          'title' => 'Vlastní řešení',
          'type' => 'text',
          'desc' => 'Pokud používáte své vlastní řešení pro nastavení dodací doby (např. nějaký plugin), tak zadejte název příslušného uživatelského pole (pozor na malá a velká písmena), odkud se budou načítat data pro XML feed.',
          'id' => 'wc_ceske_sluzby_dodaci_doba_vlastni_reseni',
          'css' => 'width: 250px',
        ),
        array(
          'type' => 'sectionend',
          'id' => 'wc_ceske_sluzby_dodaci_doba_title'
        )
      );
    }
    return $settings;
  }
}