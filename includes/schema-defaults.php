<?php
/**
 * Domande predefinite, province e mappa dei percorsi.
 *
 * Qui stanno solo i valori di partenza: le domande realmente usate vivono
 * nell'opzione pdz_conf_schema e si modificano da Configuratore > Domande.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mappa obiettivo iniziale -> percorso.
 */
function pdz_conf_path_map() {
	return array(
		'pompa_calore'    => 'A',
		'sostituzione'    => 'A',
		'casa_elettrica'  => 'B',
		'preventivo'      => 'C',
		'ristrutturazione'=> 'D',
		'fotovoltaico'    => 'E',
		'divisione_el'    => 'F',
		'non_sicuro'      => 'G',
	);
}

/**
 * Etichette percorso usate nell'oggetto email.
 */
function pdz_conf_path_labels() {
	return array(
		'A' => 'Pompa di calore',
		'B' => 'Casa elettrica',
		'C' => 'Preventivo da valutare',
		'D' => 'Ristrutturazione',
		'E' => 'Fotovoltaico / accumulo',
		'F' => 'Divisione Elettrica',
		'G' => 'Orientamento',
		'G+A' => 'Orientamento / Pompa di calore',
		'G+B' => 'Orientamento / Casa elettrica',
	);
}

/**
 * Etichetta leggibile del percorso.
 */
function pdz_conf_path_label( $path ) {
	$labels = pdz_conf_path_labels();
	if ( isset( $labels[ $path ] ) ) {
		return $labels[ $path ];
	}
	$last = substr( $path, -1 );
	return isset( $labels[ $last ] ) ? $labels[ $last ] : $path;
}

/**
 * Province italiane -> regione. Serve per Lombardia e Nord Italia nel punteggio.
 */
function pdz_conf_province() {
	return array(
		'BG' => 'Lombardia', 'BS' => 'Lombardia', 'CO' => 'Lombardia', 'CR' => 'Lombardia',
		'LC' => 'Lombardia', 'LO' => 'Lombardia', 'MN' => 'Lombardia', 'MI' => 'Lombardia',
		'MB' => 'Lombardia', 'PV' => 'Lombardia', 'SO' => 'Lombardia', 'VA' => 'Lombardia',
		'AL' => 'Piemonte', 'AT' => 'Piemonte', 'BI' => 'Piemonte', 'CN' => 'Piemonte',
		'NO' => 'Piemonte', 'TO' => 'Piemonte', 'VB' => 'Piemonte', 'VC' => 'Piemonte',
		'AO' => "Valle d'Aosta",
		'GE' => 'Liguria', 'IM' => 'Liguria', 'SP' => 'Liguria', 'SV' => 'Liguria',
		'BL' => 'Veneto', 'PD' => 'Veneto', 'RO' => 'Veneto', 'TV' => 'Veneto',
		'VE' => 'Veneto', 'VR' => 'Veneto', 'VI' => 'Veneto',
		'BZ' => 'Trentino-Alto Adige', 'TN' => 'Trentino-Alto Adige',
		'GO' => 'Friuli-Venezia Giulia', 'PN' => 'Friuli-Venezia Giulia',
		'TS' => 'Friuli-Venezia Giulia', 'UD' => 'Friuli-Venezia Giulia',
		'BO' => 'Emilia-Romagna', 'FE' => 'Emilia-Romagna', 'FC' => 'Emilia-Romagna',
		'MO' => 'Emilia-Romagna', 'PR' => 'Emilia-Romagna', 'PC' => 'Emilia-Romagna',
		'RA' => 'Emilia-Romagna', 'RE' => 'Emilia-Romagna', 'RN' => 'Emilia-Romagna',
		'AR' => 'Toscana', 'FI' => 'Toscana', 'GR' => 'Toscana', 'LI' => 'Toscana',
		'LU' => 'Toscana', 'MS' => 'Toscana', 'PI' => 'Toscana', 'PT' => 'Toscana',
		'PO' => 'Toscana', 'SI' => 'Toscana',
		'AN' => 'Marche', 'AP' => 'Marche', 'FM' => 'Marche', 'MC' => 'Marche', 'PU' => 'Marche',
		'PG' => 'Umbria', 'TR' => 'Umbria',
		'FR' => 'Lazio', 'LT' => 'Lazio', 'RI' => 'Lazio', 'RM' => 'Lazio', 'VT' => 'Lazio',
		'CH' => 'Abruzzo', 'AQ' => 'Abruzzo', 'PE' => 'Abruzzo', 'TE' => 'Abruzzo',
		'CB' => 'Molise', 'IS' => 'Molise',
		'AV' => 'Campania', 'BN' => 'Campania', 'CE' => 'Campania', 'NA' => 'Campania', 'SA' => 'Campania',
		'BA' => 'Puglia', 'BT' => 'Puglia', 'BR' => 'Puglia', 'FG' => 'Puglia', 'LE' => 'Puglia', 'TA' => 'Puglia',
		'MT' => 'Basilicata', 'PZ' => 'Basilicata',
		'CZ' => 'Calabria', 'CS' => 'Calabria', 'KR' => 'Calabria', 'RC' => 'Calabria', 'VV' => 'Calabria',
		'AG' => 'Sicilia', 'CL' => 'Sicilia', 'CT' => 'Sicilia', 'EN' => 'Sicilia', 'ME' => 'Sicilia',
		'PA' => 'Sicilia', 'RG' => 'Sicilia', 'SR' => 'Sicilia', 'TP' => 'Sicilia',
		'CA' => 'Sardegna', 'NU' => 'Sardegna', 'OR' => 'Sardegna', 'SS' => 'Sardegna', 'SU' => 'Sardegna',
	);
}

/**
 * Helper interno per definire un campo.
 */
function pdz_conf_field( $name, $type, $label, $options = array(), $args = array() ) {
	return array_merge(
		array(
			'name'     => $name,
			'type'     => $type, // radio | checkbox | text | tel | email | select | textarea | file | consent
			'label'    => $label,
			'options'  => $options,
			'required' => false,
			'hint'     => '',
			'scores'   => array(), // punti assegnati alla singola risposta
			'show_if'  => null, // array( 'field' => 'nome', 'in' => array( 'val' ) )
		),
		$args
	);
}

/**
 * Definizione completa degli step.
 *
 * paths: '*' = tutti; altrimenti elenco lettere percorso.
 */
function pdz_conf_default_schema() {
	$s = array();

	/* --- STEP 1: obiettivo ------------------------------------------------ */
	$s[] = array(
		'id'     => 'obiettivo',
		'paths'  => array( '*' ),
		'title'  => 'Cosa vuoi valutare?',
		'intro'  => 'Scegli il punto di partenza: le domande successive si adattano alla tua risposta.',
		'fields' => array(
			pdz_conf_field(
				'obiettivo',
				'radio',
				'Cosa vuoi valutare?',
				array(
					'pompa_calore'     => 'Voglio installare una pompa di calore',
					'sostituzione'     => 'Voglio sostituire la caldaia',
					'casa_elettrica'   => 'Voglio rendere la casa più elettrica',
					'preventivo'       => 'Ho già un preventivo e vorrei un parere tecnico',
					'ristrutturazione' => 'Sto ristrutturando casa',
					'fotovoltaico'     => 'Voglio valutare fotovoltaico / accumulo',
					'divisione_el'     => 'Ho una richiesta tecnica per la Divisione Elettrica',
					'non_sicuro'       => 'Non sono sicuro',
				),
				array( 'required' => true, 'hide_label' => true )
			),
		),
	);

	/* --- STEP 2: localizzazione ------------------------------------------ */
	$province = array_keys( pdz_conf_province() );
	sort( $province );
	$opt_province = array();
	foreach ( $province as $p ) {
		$opt_province[ $p ] = $p;
	}

	$s[] = array(
		'id'     => 'localizzazione',
		'paths'  => array( '*' ),
		'title'  => 'Dove si trova l’immobile o il progetto?',
		'fields' => array(
			pdz_conf_field( 'comune', 'text', 'Comune', array(), array( 'required' => true ) ),
			pdz_conf_field( 'provincia', 'select', 'Provincia', $opt_province, array( 'required' => true ) ),
			pdz_conf_field( 'cap', 'text', 'CAP', array(), array( 'required' => true, 'pattern' => '^[0-9]{5}$', 'inputmode' => 'numeric', 'maxlength' => 5 ) ),
		),
	);

	/* --- STEP 3: immobile ------------------------------------------------- */
	$s[] = array(
		'id'     => 'immobile',
		'paths'  => array( '*' ),
		'title'  => 'Che tipo di immobile vuoi valutare?',
		'fields' => array(
			pdz_conf_field(
				'tipologia_immobile',
				'radio',
				'Tipologia',
				array(
					'appartamento' => 'Appartamento',
					'indipendente' => 'Casa indipendente',
					'villa'        => 'Villa',
					'schiera'      => 'Villetta a schiera',
					'bifamiliare'  => 'Bifamiliare / plurifamiliare',
					'condominio'   => 'Condominio',
					'commerciale'  => 'Immobile commerciale',
					'altro'        => 'Altro',
				),
				array(
					'scores' => array( 'indipendente' => 10, 'villa' => 10, 'schiera' => 8, 'bifamiliare' => 8, 'appartamento' => 4, 'condominio' => 0, 'commerciale' => 6, 'altro' => 2 ),
					'required' => true )
			),
			pdz_conf_field(
				'stato_immobile',
				'radio',
				'In che stato si trova l’immobile?',
				array(
					'abitato'         => 'Già abitato',
					'ristrutturazione'=> 'In ristrutturazione',
					'costruzione'     => 'In costruzione',
					'acquisto'        => 'Da acquistare / in valutazione',
					'seconda_casa'    => 'Seconda casa',
					'altro'           => 'Altro',
				),
				array(
					'scores' => array( 'ristrutturazione' => 10, 'costruzione' => 6, 'acquisto' => 4 ),
					'required' => true )
			),
		),
	);

	/* --- PERCORSO A ------------------------------------------------------- */
	$s[] = array(
		'id'     => 'a1',
		'paths'  => array( 'A' ),
		'title'  => 'La situazione attuale',
		'fields' => array(
			pdz_conf_field(
				'riscaldamento_attuale',
				'radio',
				'Quale sistema utilizzi oggi per il riscaldamento?',
				array(
					'gas'     => 'Caldaia a gas',
					'gpl'     => 'GPL',
					'gasolio' => 'Gasolio',
					'pdc'     => 'Pompa di calore',
					'biomassa'=> 'Stufa / pellet / biomassa',
					'misto'   => 'Sistema misto',
					'non_so'  => 'Non so',
				),
				array( 'required' => true )
			),
			pdz_conf_field(
				'acs_attuale',
				'radio',
				'Come produci oggi acqua calda sanitaria?',
				array(
					'caldaia'  => 'Caldaia',
					'boiler'   => 'Boiler elettrico',
					'pdc'      => 'Pompa di calore',
					'solare'   => 'Solare termico',
					'misto'    => 'Sistema misto',
					'non_so'   => 'Non so',
				),
				array( 'required' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'a2',
		'paths'  => array( 'A' ),
		'title'  => 'I terminali presenti',
		'fields' => array(
			pdz_conf_field(
				'terminali',
				'radio',
				'Che terminali sono presenti in casa?',
				array(
					'radiatori' => 'Radiatori / termosifoni',
					'pavimento' => 'Pavimento radiante',
					'fancoil'   => 'Fan coil',
					'split'     => 'Split',
					'misto'     => 'Sistema misto',
					'non_so'    => 'Non so',
				),
				array( 'required' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'a3',
		'paths'  => array( 'A' ),
		'title'  => 'Cosa vorresti gestire con la pompa di calore?',
		'fields' => array(
			pdz_conf_field(
				'funzioni_pdc',
				'radio',
				'Funzioni richieste',
				array(
					'risc'          => 'Solo riscaldamento',
					'risc_acs'      => 'Riscaldamento e acqua calda sanitaria',
					'risc_raff'     => 'Riscaldamento e raffrescamento',
					'risc_raff_acs' => 'Riscaldamento, raffrescamento e acqua calda sanitaria',
					'non_so'        => 'Non so ancora',
				),
				array( 'required' => true, 'hide_label' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'a4',
		'paths'  => array( 'A' ),
		'title'  => 'Caratteristiche dell’abitazione',
		'fields' => array(
			pdz_conf_field(
				'superficie',
				'radio',
				'Superficie indicativa dell’abitazione',
				array(
					'lt80'   => 'Meno di 80 mq',
					'80_120' => '80–120 mq',
					'120_180'=> '120–180 mq',
					'180_250'=> '180–250 mq',
					'gt250'  => 'Oltre 250 mq',
					'non_so' => 'Non so',
				),
				array( 'required' => true )
			),
			pdz_conf_field(
				'isolamento',
				'radio',
				'Sono stati fatti interventi di isolamento?',
				array(
					'cappotto'   => 'Sì, cappotto',
					'serramenti' => 'Sì, serramenti',
					'tetto'      => 'Sì, tetto/copertura',
					'piu'        => 'Sì, più interventi',
					'no'         => 'No',
					'non_so'     => 'Non so',
				),
				array( 'required' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'a5',
		'paths'  => array( 'A' ),
		'title'  => 'L’impianto elettrico',
		'fields' => array(
			pdz_conf_field(
				'potenza_contatore',
				'radio',
				'Conosci la potenza del contatore?',
				array(
					'3'      => '3 kW',
					'4.5'    => '4,5 kW',
					'6'      => '6 kW',
					'gt6'    => 'Oltre 6 kW',
					'non_so' => 'Non so',
				),
				array( 'required' => true )
			),
			pdz_conf_field(
				'impianto_elettrico_rifatto',
				'radio',
				'L’impianto elettrico è stato rifatto recentemente?',
				array(
					'si'     => 'Sì',
					'no'     => 'No',
					'parte'  => 'In parte',
					'non_so' => 'Non so',
				),
				array( 'required' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'a6',
		'paths'  => array( 'A' ),
		'title'  => 'Fotovoltaico',
		'fields' => array(
			pdz_conf_field(
				'fv_presente',
				'radio',
				'Hai già un impianto fotovoltaico?',
				array(
					'si'         => 'Sì',
					'no'         => 'No',
					'valutare'   => 'No, ma vorrei valutarlo',
					'in_corso'   => 'È in fase di installazione',
				),
				array( 'required' => true )
			),
			pdz_conf_field(
				'accumulo_presente',
				'radio',
				'Hai anche un sistema di accumulo?',
				array( 'si' => 'Sì', 'no' => 'No', 'non_so' => 'Non so' ),
				array( 'required' => true, 'show_if' => array( 'field' => 'fv_presente', 'in' => array( 'si' ) ) )
			),
		),
	);

	/* --- PERCORSO B ------------------------------------------------------- */
	$s[] = array(
		'id'     => 'b1',
		'paths'  => array( 'B' ),
		'title'  => 'Cosa significa per te rendere la casa più elettrica?',
		'fields' => array(
			pdz_conf_field(
				'obiettivo_elettrico',
				'radio',
				'Obiettivo',
				array(
					'ridurre_gas' => 'Eliminare o ridurre il gas',
					'pdc'         => 'Installare pompa di calore',
					'fv_accumulo' => 'Integrare fotovoltaico e accumulo',
					'adeguamento' => 'Adeguare l’impianto elettrico',
					'consumi'     => 'Gestire meglio i consumi',
					'completo'    => 'Valutare un percorso completo',
					'non_so'      => 'Non so, vorrei un parere tecnico',
				),
				array( 'required' => true, 'hide_label' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'b2',
		'paths'  => array( 'B' ),
		'title'  => 'Quali soluzioni ti interessano?',
		'fields' => array(
			pdz_conf_field(
				'tecnologie',
				'checkbox',
				'Puoi selezionare più opzioni',
				array(
					'pdc'         => 'Pompa di calore',
					'fv'          => 'Fotovoltaico',
					'accumulo'    => 'Accumulo',
					'adeguamento' => 'Adeguamento impianto elettrico',
					'monitoraggio'=> 'Monitoraggio consumi',
					'colonnina'   => 'Colonnina di ricarica',
					'induzione'   => 'Piano cottura a induzione',
					'non_so'      => 'Non so ancora',
				),
				array( 'required' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'b3',
		'paths'  => array( 'B' ),
		'title'  => 'Il gas',
		'fields' => array(
			pdz_conf_field(
				'eliminare_gas',
				'radio',
				'Vuoi eliminare completamente il gas?',
				array(
					'si'      => 'Sì',
					'ridurre' => 'Vorrei ridurlo, ma non so se eliminarlo',
					'no'      => 'No, voglio solo migliorare l’efficienza',
					'non_so'  => 'Non so',
				),
				array( 'required' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'b4',
		'paths'  => array( 'B' ),
		'title'  => 'Come preferisci procedere?',
		'fields' => array(
			pdz_conf_field(
				'modalita_intervento',
				'radio',
				'Intervento unico o percorso per fasi?',
				array(
					'completo'  => 'Intervento completo',
					'graduale'  => 'Percorso graduale',
					'costi'     => 'Dipende dai costi',
					'valutazione'=> 'Dipende dalla valutazione tecnica',
					'non_so'    => 'Non so',
				),
				array( 'required' => true, 'hide_label' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'b5',
		'paths'  => array( 'B' ),
		'title'  => 'Quali impianti sono già presenti?',
		'fields' => array(
			pdz_conf_field(
				'impianti_presenti',
				'checkbox',
				'Puoi selezionare più opzioni',
				array(
					'caldaia'  => 'Caldaia a gas',
					'pdc'      => 'Pompa di calore',
					'fv'       => 'Fotovoltaico',
					'accumulo' => 'Accumulo',
					'split'    => 'Climatizzatori / split',
					'domotica' => 'Impianto domotico',
					'nessuno'  => 'Nessuno di questi',
					'non_so'   => 'Non so',
				),
				array( 'required' => true )
			),
		),
	);

	/* --- PERCORSO C ------------------------------------------------------- */
	$s[] = array(
		'id'     => 'c1',
		'paths'  => array( 'C' ),
		'title'  => 'Il preventivo che hai ricevuto',
		'fields' => array(
			pdz_conf_field(
				'preventivi_ricevuti',
				'radio',
				'Hai già ricevuto uno o più preventivi?',
				array(
					'uno'      => 'Sì, uno',
					'piu'      => 'Sì, più di uno',
					'in_corso' => 'No, ma sto per richiederli',
				),
				array( 'required' => true )
			),
			pdz_conf_field(
				'allega_preventivo',
				'radio',
				'Vuoi allegare il preventivo?',
				array(
					'si'      => 'Sì',
					'no'      => 'No',
					'dopo'    => 'Preferisco parlarne prima',
				),
				array( 'required' => true, 'show_if' => array( 'field' => 'preventivi_ricevuti', 'in' => array( 'uno', 'piu' ) ) )
			),
			pdz_conf_field(
				'file_preventivi',
				'file',
				'Carica il preventivo',
				array(),
				array(
					'hint'    => 'PDF o immagini, fino a 8 MB per file.',
					'show_if' => array( 'field' => 'allega_preventivo', 'in' => array( 'si' ) ),
				)
			),
		),
	);

	$s[] = array(
		'id'     => 'c2',
		'paths'  => array( 'C' ),
		'title'  => 'Cosa vorresti capire del preventivo ricevuto?',
		'fields' => array(
			pdz_conf_field(
				'verifiche',
				'checkbox',
				'Puoi selezionare più opzioni',
				array(
					'prezzo'        => 'Se il prezzo è coerente',
					'macchina'      => 'Se la macchina proposta è adatta',
					'dimensionamento'=> 'Se il dimensionamento è corretto',
					'lavori'        => 'Se i lavori inclusi sono sufficienti',
					'mancanze'      => 'Se mancano opere o componenti',
					'marchio'       => 'Se il marchio proposto è adatto',
					'compatibilita' => 'Se la soluzione è compatibile con casa mia',
					'generale'      => 'Non so, vorrei un parere generale',
				),
				array( 'required' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'c3',
		'paths'  => array( 'C' ),
		'title'  => 'La soluzione proposta',
		'fields' => array(
			pdz_conf_field(
				'conosce_marca',
				'radio',
				'Conosci marca o modello della pompa di calore proposta?',
				array(
					'si'         => 'Sì',
					'no'         => 'No',
					'non_chiaro' => 'Non è indicato chiaramente',
				),
				array( 'required' => true )
			),
			pdz_conf_field(
				'marca_modello',
				'text',
				'Marca / modello proposto',
				array(),
				array( 'show_if' => array( 'field' => 'conosce_marca', 'in' => array( 'si' ) ), 'required' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'c4',
		'paths'  => array( 'C' ),
		'title'  => 'Perché stai cercando un secondo parere?',
		'fields' => array(
			pdz_conf_field(
				'motivazione_parere',
				'radio',
				'Motivazione',
				array(
					'alto'       => 'Il preventivo mi sembra alto',
					'diverse'    => 'Ho ricevuto proposte molto diverse',
					'incluso'    => 'Non ho capito bene cosa è incluso',
					'adatta'     => 'Non sono sicuro che la soluzione sia adatta',
					'confronto'  => 'Voglio confrontare progetto e componenti',
					'referente'  => 'Voglio affidarmi a un referente tecnico',
					'altro'      => 'Altro',
				),
				array( 'required' => true, 'hide_label' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'c5',
		'paths'  => array( 'C' ),
		'title'  => 'Cosa ti aspetti dalla valutazione?',
		'fields' => array(
			pdz_conf_field(
				'esito_desiderato',
				'radio',
				'Esito desiderato',
				array(
					'parere'        => 'Un parere tecnico sul preventivo',
					'alternativa'   => 'Una proposta alternativa',
					'fattibilita'   => 'Uno studio di fattibilità',
					'progettazione' => 'Una progettazione completa',
					'realizzazione' => 'La realizzazione dell’impianto',
					'non_so'        => 'Non so ancora',
				),
				array( 'required' => true, 'hide_label' => true )
			),
		),
	);

	/* --- PERCORSO D ------------------------------------------------------- */
	$s[] = array(
		'id'     => 'd1',
		'paths'  => array( 'D' ),
		'title'  => 'A che punto è la ristrutturazione?',
		'fields' => array(
			pdz_conf_field(
				'stato_lavori',
				'radio',
				'Stato lavori',
				array(
					'acquisto'  => 'Sto valutando l’acquisto',
					'progetto'  => 'Sono in fase di progetto',
					'da_iniziare'=> 'I lavori devono iniziare',
					'iniziati'  => 'I lavori sono già iniziati',
					'conclusi'  => 'La ristrutturazione è quasi conclusa',
					'non_so'    => 'Non so ancora',
				),
				array( 'required' => true, 'hide_label' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'd2',
		'paths'  => array( 'D' ),
		'title'  => 'Hai già un tecnico o progettista coinvolto?',
		'fields' => array(
			pdz_conf_field(
				'figure_coinvolte',
				'radio',
				'Figure coinvolte',
				array(
					'architetto' => 'Sì, architetto',
					'geometra'   => 'Sì, geometra',
					'ingegnere'  => 'Sì, ingegnere',
					'impresa'    => 'Sì, impresa edile',
					'no'         => 'No',
					'non_so'     => 'Non so',
				),
				array( 'required' => true, 'hide_label' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'd3',
		'paths'  => array( 'D' ),
		'title'  => 'Quali impianti vuoi rifare o valutare?',
		'fields' => array(
			pdz_conf_field(
				'impianti_da_rifare',
				'checkbox',
				'Puoi selezionare più opzioni',
				array(
					'riscaldamento' => 'Riscaldamento',
					'raffrescamento'=> 'Raffrescamento',
					'acs'           => 'Acqua calda sanitaria',
					'elettrico'     => 'Impianto elettrico',
					'fv'            => 'Fotovoltaico',
					'accumulo'      => 'Accumulo',
					'domotica'      => 'Domotica / monitoraggio',
					'non_so'        => 'Non so ancora',
				),
				array( 'required' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'd4',
		'paths'  => array( 'D' ),
		'title'  => 'Qual è l’obiettivo principale della ristrutturazione?',
		'fields' => array(
			pdz_conf_field(
				'obiettivo_energetico',
				'radio',
				'Obiettivo energetico',
				array(
					'gas'       => 'Eliminare il gas',
					'efficienza'=> 'Migliorare efficienza e consumi',
					'comfort'   => 'Aumentare il comfort',
					'pdc_fv'    => 'Integrare pompa di calore e fotovoltaico',
					'futuro'    => 'Predisporre la casa per interventi futuri',
					'migliore'  => 'Valutare la soluzione migliore',
				),
				array( 'required' => true, 'hide_label' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'd5',
		'paths'  => array( 'D' ),
		'title'  => 'L’impianto esistente può essere modificato?',
		'fields' => array(
			pdz_conf_field(
				'liberta_progettuale',
				'radio',
				'Libertà progettuale',
				array(
					'si'      => 'Sì, completamente',
					'parziale'=> 'Sì, ma solo in parte',
					'no'      => 'No, va mantenuto il più possibile',
					'non_so'  => 'Non so',
				),
				array( 'required' => true, 'hide_label' => true )
			),
		),
	);

	/* --- PERCORSO E ------------------------------------------------------- */
	$s[] = array(
		'id'     => 'e1',
		'paths'  => array( 'E' ),
		'title'  => 'Hai già un impianto fotovoltaico?',
		'fields' => array(
			pdz_conf_field(
				'fv_situazione',
				'radio',
				'Situazione attuale',
				array(
					'si'        => 'Sì',
					'no'        => 'No',
					'in_corso'  => 'È in fase di installazione',
					'valutando' => 'Lo sto valutando',
				),
				array( 'required' => true, 'hide_label' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'e2',
		'paths'  => array( 'E' ),
		'title'  => 'Perché vuoi valutare fotovoltaico o accumulo?',
		'fields' => array(
			pdz_conf_field(
				'fv_obiettivo',
				'radio',
				'Obiettivo',
				array(
					'consumi'      => 'Ridurre i consumi elettrici',
					'futura_pdc'   => 'Alimentare una futura pompa di calore',
					'pdc_presente' => 'Integrare una pompa di calore già presente',
					'autoconsumo'  => 'Aumentare l’autoconsumo',
					'rete'         => 'Ridurre la dipendenza dalla rete',
					'gestione'     => 'Migliorare la gestione energetica della casa',
					'non_so'       => 'Non so ancora',
				),
				array( 'required' => true, 'hide_label' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'e3',
		'paths'  => array( 'E' ),
		'title'  => 'E la pompa di calore?',
		'fields' => array(
			pdz_conf_field(
				'pdc_stato',
				'radio',
				'Hai già o vuoi installare una pompa di calore?',
				array(
					'presente'  => 'Sì, già presente',
					'vorrei'    => 'Sì, vorrei installarla',
					'no'        => 'No',
					'non_so'    => 'Non so',
				),
				array( 'required' => true )
			),
			pdz_conf_field(
				'pdc_soddisfazione',
				'radio',
				'Sei soddisfatto del funzionamento attuale?',
				array( 'si' => 'Sì', 'no' => 'No', 'parte' => 'In parte', 'non_so' => 'Non so' ),
				array( 'required' => true, 'show_if' => array( 'field' => 'pdc_stato', 'in' => array( 'presente' ) ) )
			),
		),
	);

	$s[] = array(
		'id'     => 'e4',
		'paths'  => array( 'E' ),
		'title'  => 'I tuoi consumi',
		'fields' => array(
			pdz_conf_field(
				'consumi_noti',
				'radio',
				'Conosci i tuoi consumi elettrici annui?',
				array(
					'si'      => 'Sì',
					'no'      => 'No',
					'bolletta'=> 'Posso allegare una bolletta',
				),
				array( 'required' => true )
			),
			pdz_conf_field(
				'file_bolletta',
				'file',
				'Carica la bolletta',
				array(),
				array( 'show_if' => array( 'field' => 'consumi_noti', 'in' => array( 'bolletta' ) ), 'hint' => 'Facoltativo. PDF o foto leggibile.' )
			),
		),
	);

	$s[] = array(
		'id'     => 'e5',
		'paths'  => array( 'E' ),
		'title'  => 'Vuoi valutare anche un sistema di accumulo?',
		'fields' => array(
			pdz_conf_field(
				'accumulo_valutazione',
				'radio',
				'Accumulo',
				array(
					'si'      => 'Sì',
					'no'      => 'No',
					'conviene'=> 'Voglio capire se conviene',
					'non_so'  => 'Non so',
				),
				array( 'required' => true, 'hide_label' => true )
			),
		),
	);

	/* --- PERCORSO F ------------------------------------------------------- */
	$s[] = array(
		'id'     => 'f1',
		'paths'  => array( 'F' ),
		'title'  => 'La richiesta riguarda',
		'fields' => array(
			pdz_conf_field(
				'tipologia_richiesta',
				'radio',
				'Tipologia richiesta',
				array(
					'infrastruttura' => 'Infrastruttura elettrica',
					'grande_utenza'  => 'Grande utenza',
					'data_center'    => 'Data center',
					'connessione'    => 'Connessione alla rete',
					'progettazione'  => 'Progettazione elettrica',
					'civile'         => 'Impianto elettrico civile / tecnologico',
					'autorizzativo'  => 'Supporto autorizzativo',
					'coordinamento'  => 'Coordinamento tecnico',
					'altro'          => 'Altro',
				),
				array( 'required' => true, 'hide_label' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'f2',
		'paths'  => array( 'F' ),
		'title'  => 'Chi invia la richiesta?',
		'fields' => array(
			pdz_conf_field(
				'soggetto_richiedente',
				'radio',
				'Soggetto richiedente',
				array(
					'azienda'   => 'Azienda',
					'studio'    => 'Studio tecnico',
					'gc'        => 'General contractor',
					'impresa'   => 'Impresa',
					'privato'   => 'Privato',
					'ente'      => 'Ente / committente pubblico',
					'altro'     => 'Altro',
				),
				array( 'required' => true, 'hide_label' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'f3',
		'paths'  => array( 'F' ),
		'title'  => 'A che fase si trova il progetto?',
		'fields' => array(
			pdz_conf_field(
				'fase_progetto',
				'radio',
				'Fase progetto',
				array(
					'preliminare'   => 'Studio preliminare',
					'progettazione' => 'Progettazione',
					'autorizzativo' => 'Iter autorizzativo',
					'realizzazione' => 'Realizzazione',
					'cantiere'      => 'Cantiere già avviato',
					'criticita'     => 'Criticità tecnica da risolvere',
					'non_so'        => 'Non so',
				),
				array( 'required' => true, 'hide_label' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'f4',
		'paths'  => array( 'F' ),
		'title'  => 'Descrivi brevemente la richiesta tecnica',
		'fields' => array(
			pdz_conf_field(
				'descrizione_richiesta',
				'textarea',
				'Descrizione',
				array(),
				array(
					'required'    => true,
					'hide_label'  => true,
					'placeholder' => 'Indica contesto del progetto, potenza o tipologia impianto se nota, area geografica, obiettivo del supporto richiesto e urgenza.',
					'rows'        => 6,
				)
			),
		),
	);

	$s[] = array(
		'id'     => 'f5',
		'paths'  => array( 'F' ),
		'title'  => 'Documentazione tecnica',
		'intro'  => 'Facoltativo: relazione tecnica, planimetria, schema impianto, documentazione di progetto.',
		'fields' => array(
			pdz_conf_field( 'file_tecnici', 'file', 'Allega documenti', array(), array( 'hint' => 'Fino a 8 file, max 8 MB ciascuno.' ) ),
		),
	);

	/* --- PERCORSO G ------------------------------------------------------- */
	$s[] = array(
		'id'     => 'g1',
		'paths'  => array( 'G' ),
		'title'  => 'Quale situazione descrive meglio il tuo caso?',
		'fields' => array(
			pdz_conf_field(
				'situazione_g',
				'radio',
				'Situazione',
				array(
					'gas'        => 'Voglio ridurre il gas',
					'pdc'        => 'Voglio capire se la pompa di calore fa per me',
					'consumi'    => 'Voglio migliorare consumi e comfort',
					'ristruttura'=> 'Sto ristrutturando',
					'preventivo' => 'Ho dubbi su un preventivo',
					'piu_soluzioni'=> 'Voglio valutare più soluzioni insieme',
				),
				array( 'required' => true, 'hide_label' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'g2',
		'paths'  => array( 'G' ),
		'title'  => 'Qual è la tua priorità principale?',
		'fields' => array(
			pdz_conf_field(
				'priorita_g',
				'radio',
				'Priorità',
				array(
					'risparmio'  => 'Risparmio energetico',
					'comfort'    => 'Comfort',
					'caldaia'    => 'Sostituzione caldaia',
					'elettrica'  => 'Casa più elettrica',
					'investimento'=> 'Investimento corretto',
					'partenza'   => 'Capire da dove partire',
				),
				array( 'required' => true, 'hide_label' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'g3',
		'paths'  => array( 'G' ),
		'title'  => 'Come preferisci procedere?',
		'fields' => array(
			pdz_conf_field(
				'passaggio_g',
				'radio',
				'Passaggio successivo',
				array(
					'ricontatto'  => 'Vorrei essere ricontattato',
					'dettagliato' => 'Vorrei compilare un percorso più dettagliato',
					'confronto'   => 'Vorrei fissare un confronto tecnico',
					'documenti'   => 'Vorrei allegare documenti/foto',
				),
				array( 'required' => true, 'hide_label' => true )
			),
		),
	);

	/* --- STEP COMUNI FINALI (residenziali: A B C D E G) -------------------- */
	$s[] = array(
		'id'     => 'stato_decisionale',
		'phase'  => 'finale',
		'paths'  => array( 'A', 'B', 'C', 'D', 'E', 'G' ),
		'title'  => 'A che punto sei nella valutazione?',
		'fields' => array(
			pdz_conf_field(
				'stato_decisionale',
				'radio',
				'Stato decisionale',
				array(
					'informandomi'  => 'Sto iniziando a informarmi',
					'altri_fornitori'=> 'Ho già parlato con altri fornitori',
					'preventivi'    => 'Ho già ricevuto preventivi',
					'studio'        => 'Voglio procedere con uno studio tecnico',
					'a_breve'       => 'Sto ristrutturando e devo decidere a breve',
					'deciso'        => 'Ho già deciso e cerco un partner tecnico',
				),
				array(
					'scores' => array( 'deciso' => 20, 'studio' => 18, 'preventivi' => 15, 'a_breve' => 15, 'altri_fornitori' => 8, 'informandomi' => 5 ),
					'required' => true, 'hide_label' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'budget',
		'phase'  => 'finale',
		'paths'  => array( 'A', 'B', 'C', 'D', 'E', 'G' ),
		'title'  => 'Hai già definito un budget indicativo?',
		'intro'  => 'Serve solo a capire l’ordine di grandezza dell’intervento: nessuna risposta esclude la richiesta.',
		'fields' => array(
			pdz_conf_field(
				'budget',
				'radio',
				'Budget',
				array(
					'lt10'    => 'Meno di 10.000 €',
					'10_20'   => '10.000 – 20.000 €',
					'20_35'   => '20.000 – 35.000 €',
					'35_50'   => '35.000 – 50.000 €',
					'gt50'    => '50.000+ €',
					'non_ancora'=> 'Non ancora',
				),
				array(
					'scores' => array( 'gt50' => 20, '35_50' => 18, '20_35' => 15, '10_20' => 7, 'lt10' => 0, 'non_ancora' => 5 ),
					'required' => true, 'hide_label' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'tempistiche',
		'phase'  => 'finale',
		'paths'  => array( 'A', 'B', 'C', 'D', 'E', 'G' ),
		'title'  => 'Quando vorresti realizzare l’intervento?',
		'fields' => array(
			pdz_conf_field(
				'tempistiche',
				'radio',
				'Tempistiche',
				array(
					'subito'  => 'Il prima possibile',
					'3_mesi'  => 'Entro 3 mesi',
					'6_mesi'  => 'Entro 6 mesi',
					'12_mesi' => 'Entro 12 mesi',
					'futuro'  => 'Sto valutando per il futuro',
					'studio'  => 'Dipende dallo studio tecnico',
				),
				array(
					'scores' => array( 'subito' => 15, '3_mesi' => 15, '6_mesi' => 10, '12_mesi' => 5, 'futuro' => 0, 'studio' => 8 ),
					'required' => true, 'hide_label' => true )
			),
		),
	);

	$s[] = array(
		'id'     => 'allegati',
		'phase'  => 'finale',
		'paths'  => array( 'A', 'B', 'C', 'D', 'E', 'G' ),
		'title'  => 'Vuoi allegare documenti utili?',
		'intro'  => 'Non è obbligatorio, ma eventuali documenti aiutano a inquadrare meglio la richiesta.',
		'fields' => array(
			pdz_conf_field(
				'tipo_allegati',
				'checkbox',
				'Cosa stai allegando?',
				array(
					'caldaia'    => 'Foto caldaia / centrale termica',
					'terminali'  => 'Foto termosifoni o terminali',
					'quadro'     => 'Foto quadro elettrico',
					'bollette'   => 'Bollette',
					'planimetria'=> 'Planimetria',
					'preventivi' => 'Preventivi già ricevuti',
					'ristrutturazione'=> 'Documenti ristrutturazione',
					'schede'     => 'Schede tecniche',
					'altro'      => 'Altro',
				)
			),
			pdz_conf_field( 'file_allegati', 'file', 'Carica i file', array(), array( 'hint' => 'Fino a 8 file, max 8 MB ciascuno. JPG, PNG, PDF, DOC, XLS.' ) ),
		),
	);

	/* --- CONTATTI (tutti) -------------------------------------------------- */
	$s[] = array(
		'id'     => 'contatti',
		'phase'  => 'contatti',
		'paths'  => array( '*' ),
		'title'  => 'Come possiamo ricontattarti?',
		'fields' => array(
			pdz_conf_field( 'nome', 'text', 'Nome e cognome', array(), array( 'required' => true, 'autocomplete' => 'name' ) ),
			pdz_conf_field( 'email', 'email', 'Email', array(), array( 'required' => true, 'autocomplete' => 'email' ) ),
			pdz_conf_field( 'telefono', 'tel', 'Telefono', array(), array( 'required' => true, 'autocomplete' => 'tel' ) ),
			pdz_conf_field(
				'preferenza_contatto',
				'radio',
				'Come preferisci essere contattato?',
				array( 'telefono' => 'Telefono', 'email' => 'Email', 'whatsapp' => 'WhatsApp' ),
				array( 'required' => true )
			),
			pdz_conf_field(
				'fascia_oraria',
				'radio',
				'Fascia oraria preferita',
				array(
					'mattina'    => 'Mattina (9–13)',
					'pomeriggio' => 'Pomeriggio (14–18)',
					'sera'       => 'Sera (dopo le 18)',
					'indifferente'=> 'Indifferente',
				),
				array( 'required' => true )
			),
			pdz_conf_field( 'messaggio', 'textarea', 'Vuoi aggiungere qualcosa?', array(), array( 'rows' => 4, 'placeholder' => 'Facoltativo' ) ),
			pdz_conf_field(
				'privacy',
				'consent',
				'Ho letto e accetto l’informativa privacy',
				array(),
				array( 'required' => true )
			),
		),
	);

	return apply_filters( 'pdz_conf_default_schema', $s );
}
