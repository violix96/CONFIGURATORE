/**
 * Configuratore Pedrinazzi — motore frontend.
 * Nessuna dipendenza. Lo schema arriva da PHP in window.PDZ_CONF.
 */
(function () {
	'use strict';

	var CFG = window.PDZ_CONF;
	if (!CFG) return;

	var root = document.getElementById('pdz-conf');
	if (!root) return;

	var app = root.querySelector('[data-pdz-app]');
	var intro = root.querySelector('[data-pdz-intro]');
	var startBtn = root.querySelector('[data-pdz-start]');


	/* --------------------------------------------------------------------
	 * Icone tematiche (SVG inline, ereditano currentColor)
	 * ----------------------------------------------------------------- */

	var SVG = function (body) {
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' + body + '</svg>';
	};

	var ICONS = {
		/* obiettivo iniziale */
		pompa_calore: SVG('<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="12" cy="12" r="3.4"/><path d="M12 5v3.2M12 15.4V19"/>'),
		sostituzione: SVG('<path d="M12 3c2.9 3.9 4.9 6 4.9 9a4.9 4.9 0 0 1-9.8 0c0-2 1-3.2 2.4-4.6"/><path d="M12 17.5a2.4 2.4 0 0 0 2.4-2.4c0-1.4-1-2.2-2.4-3.9-1.4 1.7-2.4 2.5-2.4 3.9A2.4 2.4 0 0 0 12 17.5z"/>'),
		casa_elettrica: SVG('<path d="M3.5 10.6 12 4.2l8.5 6.4V20a.9.9 0 0 1-.9.9H4.4a.9.9 0 0 1-.9-.9z"/><path d="M12.6 11.2 10 15h2.6l-.9 3.4 3.1-4.6h-2.5z"/>'),
		preventivo: SVG('<path d="M6 3.2h8.2L19 8v12.8H6z"/><path d="M14 3.2V8h5"/><path d="M9 12.5h6M9 16h4"/>'),
		ristrutturazione: SVG('<path d="M3.5 20.5h17"/><path d="M5.5 20.5V11l6.5-4.6L18.5 11v9.5"/><path d="M9.5 20.5v-5h5v5"/>'),
		fotovoltaico: SVG('<circle cx="12" cy="7" r="3.1"/><path d="M12 1.8v1.4M12 10.8v1.4M17 7h1.4M5.6 7H7M15.5 3.5l1-1M7.5 10.5l-1 1M15.5 10.5l1 1M7.5 3.5l-1-1"/><path d="M4.5 20.8 6.6 15h10.8l2.1 5.8z"/>'),
		divisione_el: SVG('<path d="M13.4 2.5 5.5 13.4h5.6L10.6 21.5l7.9-10.9h-5.6z"/>'),
		non_sicuro: SVG('<circle cx="12" cy="12" r="9"/><path d="M9.6 9.5a2.5 2.5 0 1 1 3.4 2.4c-.7.3-1 .9-1 1.7v.3"/><path d="M12 17.2h.01"/>'),

		/* fasi */
		intro: SVG('<circle cx="12" cy="12" r="8.5"/><circle cx="12" cy="12" r="3.6"/><path d="M12 1.8v2M12 20.2v2M22.2 12h-2M3.8 12h-2"/>'),
		percorso: SVG('<circle cx="6" cy="6.5" r="2.6"/><circle cx="18" cy="17.5" r="2.6"/><path d="M8.6 6.5h5.1a3.3 3.3 0 0 1 0 6.6h-3.4a3.3 3.3 0 0 0 0 6.6h5.1"/>'),
		finale: SVG('<rect x="5" y="4.5" width="14" height="16" rx="1.6"/><path d="M9.2 4.5V3.2h5.6v1.3"/><path d="M9.3 13.2l2 2 3.4-4"/>'),
		contatti: SVG('<rect x="3" y="5.2" width="18" height="13.6" rx="2"/><path d="m3.8 6.4 8.2 6 8.2-6"/>'),
		check: SVG('<path d="m5 12.5 4.4 4.3L19 7.4"/>'),

		/* generatori e combustibili */
		fiamma: SVG('<path d="M12 3c2.9 3.9 4.9 6 4.9 9a4.9 4.9 0 0 1-9.8 0c0-2 1-3.2 2.4-4.6"/>'),
		bombola: SVG('<rect x="7" y="6.5" width="10" height="14" rx="2"/><path d="M10.5 6.5v-2h3v2"/><path d="M9 11h6"/>'),
		goccia: SVG('<path d="M12 3.5c3.2 4 5 6.2 5 8.7a5 5 0 0 1-10 0c0-2.5 1.8-4.7 5-8.7z"/>'),
		foglia: SVG('<path d="M5 19c-1.5-6 2.5-12.5 14-13 .5 8-3.5 13.5-11 13"/><path d="M8 16c2-3 4.5-5 8-6.5"/>'),
		boiler: SVG('<rect x="7" y="3.5" width="10" height="14" rx="4"/><path d="M9.5 17.5v3M14.5 17.5v3"/><path d="M10 8h4"/>'),
		sole: SVG('<circle cx="12" cy="12" r="4"/><path d="M12 2v2.5M12 19.5V22M22 12h-2.5M4.5 12H2M18.4 5.6l-1.8 1.8M7.4 16.6l-1.8 1.8M18.4 18.4l-1.8-1.8M7.4 7.4 5.6 5.6"/>'),

		/* terminali */
		radiatore: SVG('<rect x="4.5" y="6" width="15" height="11" rx="1.5"/><path d="M8.5 6v11M12 6v11M15.5 6v11"/><path d="M6.5 17v3M17.5 17v3"/>'),
		pavimento: SVG('<rect x="3" y="7" width="18" height="12" rx="1.5"/><path d="M6 16c1.5-2 3-2 4.5 0s3 2 4.5 0 3-2 4.5 0" transform="translate(-1.5 -3)"/><path d="M6 16c1.5-2 3-2 4.5 0s3 2 4.5 0 3-2 4.5 0"/>'),
		fancoil: SVG('<rect x="3" y="6" width="18" height="9" rx="1.5"/><circle cx="12" cy="10.5" r="2.6"/><path d="M7 18.5c1.5 1.5 3 1.5 4.5 0M12.5 18.5c1.5 1.5 3 1.5 4.5 0"/>'),
		split: SVG('<rect x="3" y="5" width="18" height="6.5" rx="1.8"/><path d="M6.5 8.2h11"/><path d="M7 15c1.6 1.6 3.2 1.6 4.8 0M12.2 18c1.6 1.6 3.2 1.6 4.8 0"/>'),
		misto: SVG('<path d="M4 8h9a4 4 0 0 1 0 8H8"/><path d="m10.5 13.5-2.5 2.5 2.5 2.5"/><path d="M20 8h-3"/>'),
		neve: SVG('<path d="M12 2v20M4.2 7l15.6 10M19.8 7 4.2 17"/><path d="m9.5 4.5 2.5 2.5 2.5-2.5M9.5 19.5l2.5-2.5 2.5 2.5"/>'),
		calore: SVG('<path d="M10 13.5V5a2 2 0 1 1 4 0v8.5a4.2 4.2 0 1 1-4 0z"/><circle cx="12" cy="17.3" r="1.6" fill="currentColor" stroke="none"/>'),
		clima: SVG('<circle cx="12" cy="12" r="8.5"/><path d="M12 6.5v11M7.5 9.2l9 5.6M16.5 9.2l-9 5.6"/>'),

		/* involucro */
		muro: SVG('<rect x="3" y="5" width="18" height="14" rx="1"/><path d="M3 9.7h18M3 14.3h18M9 5v4.7M15 9.7v4.6M9 14.3V19"/>'),
		finestra: SVG('<rect x="4.5" y="3.5" width="15" height="15" rx="1.2"/><path d="M12 3.5v15M4.5 11h15"/><path d="M4.5 21h15"/>'),
		tetto: SVG('<path d="M2.5 12 12 4.5l9.5 7.5"/><path d="M5.5 10.5V20h13v-9.5"/>'),
		livelli: SVG('<path d="m12 3.5 8.5 4.2L12 12 3.5 7.7z"/><path d="m3.5 12 8.5 4.3 8.5-4.3"/><path d="m3.5 16.3 8.5 4.2 8.5-4.2"/>'),
		niente: SVG('<circle cx="12" cy="12" r="8.5"/><path d="m6.5 6.5 11 11"/>'),
		domanda: SVG('<circle cx="12" cy="12" r="9"/><path d="M9.6 9.5a2.5 2.5 0 1 1 3.4 2.4c-.7.3-1 .9-1 1.7v.3"/><path d="M12 17.2h.01"/>'),

		/* elettrico ed energia */
		fulmine: SVG('<path d="M13.4 2.5 5.5 13.4h5.6L10.6 21.5l7.9-10.9h-5.6z"/>'),
		batteria: SVG('<rect x="2.5" y="7" width="16" height="10" rx="2"/><path d="M21.5 10.5v3"/><path d="m10.5 9.5-2 3.2h3l-2 3"/>'),
		pannello: SVG('<path d="M4.5 15.5 6.5 5h11l2 10.5z"/><path d="M5.6 10h12.8M11.2 5l-.8 10.5M12.8 5l.8 10.5"/><path d="M12 15.5V20M9 20h6"/>'),
		colonnina: SVG('<rect x="4" y="3.5" width="10" height="17" rx="1.6"/><path d="M6.8 7.5h4.4M8.5 11h3l-2 4"/><path d="M17 8.5v6.6a1.9 1.9 0 0 0 3.8 0V11l-2.4-2.4"/>'),
		induzione: SVG('<rect x="3" y="4.5" width="18" height="15" rx="2"/><circle cx="8.5" cy="10" r="2.2"/><circle cx="15.5" cy="10" r="2.2"/><path d="M6.5 15.5h11"/>'),
		monitoraggio: SVG('<rect x="3" y="4.5" width="18" height="13" rx="1.8"/><path d="m6.5 13 3-3.5 2.5 2 3.5-4.5"/><path d="M9 20.5h6"/>'),
		domotica: SVG('<path d="M4 10.5 12 4.5l8 6V20H4z"/><path d="M9.6 14.4a3.4 3.4 0 0 1 4.8 0M11.4 16.8a1 1 0 0 1 1.2 0"/>'),
		quadro: SVG('<rect x="4" y="3.5" width="16" height="17" rx="1.6"/><path d="M7.5 8h9M7.5 12h9M7.5 16h4.5"/>'),
		contatore: SVG('<circle cx="12" cy="12" r="8.5"/><path d="M12 12 8.8 8.3"/><path d="M4.5 12H6M18 12h1.5M12 4.5V6"/>'),

		/* immobili */
		appartamento: SVG('<rect x="5" y="3.5" width="14" height="17" rx="1.2"/><path d="M8.5 7.5h2.5M13 7.5h2.5M8.5 11h2.5M13 11h2.5M10.2 20.5v-4h3.6v4"/>'),
		casa: SVG('<path d="M3.5 10.6 12 4.2l8.5 6.4V20a.9.9 0 0 1-.9.9H4.4a.9.9 0 0 1-.9-.9z"/><path d="M9.8 20.9v-5.6h4.4v5.6"/>'),
		villa: SVG('<path d="M2.5 11 12 4l9.5 7"/><path d="M4.8 9.4V20.5h14.4V9.4"/><path d="M9 20.5v-5h6v5M8.5 12.5h2M13.5 12.5h2"/>'),
		schiera: SVG('<path d="M2 12.5 6 9l4 3.5V20H2z"/><path d="M10 12.5 14 9l4 3.5V20h-8z"/><path d="M18 12.5 21 10v10h-3z"/>'),
		bifamiliare: SVG('<path d="M2.5 11.5 8 7l5.5 4.5V20h-11z"/><path d="M12.5 12.5 17 9l4.5 3.5V20h-9z"/>'),
		condominio: SVG('<rect x="3" y="2.5" width="8" height="18"/><rect x="13" y="7" width="8" height="13.5"/><path d="M5.5 6h3M5.5 10h3M5.5 14h3M15.5 11h3M15.5 15h3"/>'),
		commerciale: SVG('<path d="M3.5 8.5 5 4.5h14l1.5 4z"/><path d="M4.8 8.5V20.5h14.4V8.5"/><path d="M9 20.5v-6h6v6"/>'),
		altro: SVG('<circle cx="6" cy="12" r="1.4"/><circle cx="12" cy="12" r="1.4"/><circle cx="18" cy="12" r="1.4"/>'),

		/* documenti e allegati */
		documento: SVG('<path d="M6 3.2h8.2L19 8v12.8H6z"/><path d="M14 3.2V8h5"/>'),
		planimetria: SVG('<rect x="3" y="4.5" width="18" height="15" rx="1"/><path d="M3 11h7v8.5M10 11V4.5M14 11h7M14 15h7"/>'),
		bolletta: SVG('<path d="M5.5 3.5h13v17l-2-1.4-2 1.4-2-1.4-2 1.4-2-1.4-2 1.4z"/><path d="M9 8.5h6M9 12.5h6"/>'),
		foto: SVG('<rect x="2.5" y="6.5" width="19" height="13" rx="2"/><circle cx="12" cy="13" r="3.4"/><path d="M8.5 6.5 10 4h4l1.5 2.5"/>'),

		/* contatti */
		telefono: SVG('<path d="M6.5 3.5h11v17h-11z"/><path d="M10.5 17.5h3"/>'),
		email: SVG('<rect x="3" y="5.2" width="18" height="13.6" rx="2"/><path d="m3.8 6.4 8.2 6 8.2-6"/>'),
		chat: SVG('<path d="M20.5 11.5c0 4.1-3.8 7.4-8.5 7.4-1.2 0-2.4-.2-3.4-.6l-5.1 1.6 1.7-4.4a6.9 6.9 0 0 1-1.7-4c0-4.1 3.8-7.4 8.5-7.4s8.5 3.3 8.5 7.4z"/>'),
		alba: SVG('<path d="M2.5 18.5h19"/><path d="M6.5 15a5.5 5.5 0 0 1 11 0"/><path d="M12 4v3M5 7l1.6 1.6M19 7l-1.6 1.6"/>'),
		luna: SVG('<path d="M20 14.5a8.2 8.2 0 0 1-10.6-10 8.5 8.5 0 1 0 10.6 10z"/>'),
		orologio: SVG('<circle cx="12" cy="12" r="8.5"/><path d="M12 7v5.3l3.4 2"/>'),

		/* B2B */
		infrastruttura: SVG('<path d="M12 2.5 5 20.5M12 2.5l7 18M7.5 12h9M6.2 16h11.6"/>'),
		server: SVG('<rect x="3.5" y="4" width="17" height="6" rx="1.4"/><rect x="3.5" y="14" width="17" height="6" rx="1.4"/><path d="M7 7h.01M7 17h.01"/>'),
		rete: SVG('<circle cx="12" cy="4.5" r="2"/><circle cx="5" cy="19" r="2"/><circle cx="19" cy="19" r="2"/><path d="M12 6.5v4M11 11.5 6 17M13 11.5 18 17"/>'),
		progetto: SVG('<path d="M12 3.5 5 20.5M12 3.5l7 17"/><circle cx="12" cy="3.5" r="1.4"/><path d="M8.5 14h7"/>'),
		cantiere: SVG('<path d="M3.5 15.5a8.5 8.5 0 0 1 17 0z"/><path d="M2.5 18.5h19"/><path d="M9.5 15 11 6.5h2L14.5 15"/>'),
		timbro: SVG('<path d="M6 20.5h12"/><path d="M8 17.5v-2.2c0-1-1.5-1.6-1.5-3.3A5.5 5.5 0 0 1 12 6.5a5.5 5.5 0 0 1 5.5 5.5c0 1.7-1.5 2.3-1.5 3.3v2.2z"/><path d="M6 17.5h12"/>'),
		azienda: SVG('<rect x="3.5" y="6.5" width="10" height="14"/><rect x="13.5" y="10.5" width="7" height="10"/><path d="M6.5 10h4M6.5 14h4M16 14h2"/>'),
		studio: SVG('<path d="M4 20.5 20 4.5"/><path d="M4 20.5h4.5L20 9v-4.5H15.5L4 16z"/><path d="M13.5 6.5 17.5 10.5"/>'),
		persona: SVG('<circle cx="12" cy="8" r="3.6"/><path d="M5 20.5a7 7 0 0 1 14 0"/>'),
		gruppo: SVG('<circle cx="9" cy="8.5" r="3"/><path d="M3.5 19.5a5.5 5.5 0 0 1 11 0"/><circle cx="17" cy="9.5" r="2.4"/><path d="M15 15.2a4.8 4.8 0 0 1 5.5 4.3"/>'),
		ente: SVG('<path d="M3 9.5 12 4l9 5.5"/><path d="M5.5 9.5v9M10 9.5v9M14 9.5v9M18.5 9.5v9"/><path d="M3 20.5h18"/>'),
		coordinamento: SVG('<circle cx="12" cy="12" r="3"/><circle cx="12" cy="12" r="8.5"/><path d="M12 3.5v5M12 15.5v5M3.5 12h5M15.5 12h5"/>')
	};

	/**
	 * Quali icone mostrare, campo per campo. Le domande non elencate
	 * restano senza icone: meglio nessuna che una accanto a metà risposte.
	 */
	var ICON_MAP = {
		obiettivo: {
			pompa_calore: 'pompa_calore', sostituzione: 'sostituzione', casa_elettrica: 'casa_elettrica',
			preventivo: 'preventivo', ristrutturazione: 'ristrutturazione', fotovoltaico: 'fotovoltaico',
			divisione_el: 'divisione_el', non_sicuro: 'non_sicuro'
		},
		riscaldamento_attuale: {
			gas: 'fiamma', gpl: 'bombola', gasolio: 'goccia', pdc: 'pompa_calore',
			biomassa: 'foglia', misto: 'misto', non_so: 'domanda'
		},
		acs_attuale: {
			caldaia: 'fiamma', boiler: 'boiler', pdc: 'pompa_calore', solare: 'sole',
			misto: 'misto', non_so: 'domanda'
		},
		terminali: {
			radiatori: 'radiatore', pavimento: 'pavimento', fancoil: 'fancoil',
			split: 'split', misto: 'misto', non_so: 'domanda'
		},
		funzioni_pdc: {
			risc: 'calore', risc_acs: 'goccia', risc_raff: 'neve', risc_raff_acs: 'clima', non_so: 'domanda'
		},
		isolamento: {
			cappotto: 'muro', serramenti: 'finestra', tetto: 'tetto', piu: 'livelli',
			no: 'niente', non_so: 'domanda'
		},
		tipologia_immobile: {
			appartamento: 'appartamento', indipendente: 'casa', villa: 'villa', schiera: 'schiera',
			bifamiliare: 'bifamiliare', condominio: 'condominio', commerciale: 'commerciale', altro: 'altro'
		},
		tecnologie: {
			pdc: 'pompa_calore', fv: 'pannello', accumulo: 'batteria', adeguamento: 'quadro',
			monitoraggio: 'monitoraggio', colonnina: 'colonnina', induzione: 'induzione', non_so: 'domanda'
		},
		impianti_presenti: {
			caldaia: 'fiamma', pdc: 'pompa_calore', fv: 'pannello', accumulo: 'batteria',
			split: 'split', domotica: 'domotica', nessuno: 'niente', non_so: 'domanda'
		},
		impianti_da_rifare: {
			riscaldamento: 'calore', raffrescamento: 'neve', acs: 'goccia', elettrico: 'quadro',
			fv: 'pannello', accumulo: 'batteria', domotica: 'domotica', non_so: 'domanda'
		},
		obiettivo_elettrico: {
			ridurre_gas: 'fiamma', pdc: 'pompa_calore', fv_accumulo: 'pannello', adeguamento: 'quadro',
			consumi: 'monitoraggio', completo: 'casa_elettrica', non_so: 'domanda'
		},
		tipo_allegati: {
			caldaia: 'fiamma', terminali: 'radiatore', quadro: 'quadro', bollette: 'bolletta',
			planimetria: 'planimetria', preventivi: 'documento', ristrutturazione: 'ristrutturazione',
			schede: 'documento', altro: 'altro'
		},
		preferenza_contatto: { telefono: 'telefono', email: 'email', whatsapp: 'chat' },
		fascia_oraria: { mattina: 'alba', pomeriggio: 'sole', sera: 'luna', indifferente: 'orologio' },
		tipologia_richiesta: {
			infrastruttura: 'infrastruttura', grande_utenza: 'fulmine', data_center: 'server',
			connessione: 'rete', progettazione: 'progetto', civile: 'quadro',
			autorizzativo: 'timbro', coordinamento: 'coordinamento', altro: 'altro'
		},
		soggetto_richiedente: {
			azienda: 'azienda', studio: 'studio', gc: 'gruppo', impresa: 'cantiere',
			privato: 'persona', ente: 'ente', altro: 'altro'
		},
		fase_progetto: {
			preliminare: 'progetto', progettazione: 'planimetria', autorizzativo: 'timbro',
			realizzazione: 'cantiere', cantiere: 'cantiere', criticita: 'domanda', non_so: 'domanda'
		}
	};

	/**
	 * Icona di una risposta, se prevista per quella domanda.
	 */
	function optionIcon(fieldName, value) {
		var map = ICON_MAP[fieldName];
		if (!map || !map[value]) return null;
		return ICONS[map[value]] || null;
	}

	var state = {
		answers: {},
		files: {},
		index: 0,
		direction: 'fwd',
		done: false,
		startedAt: 0,
		sending: false
	};

	/* --------------------------------------------------------------------
	 * Logica di percorso (specchio di pedrinazzi-configuratore.php)
	 * ----------------------------------------------------------------- */

	function resolvePath() {
		var a = state.answers;
		var path = CFG.pathMap[a.obiettivo] || 'G';

		if (path === 'G' && a.passaggio_g === 'dettagliato') {
			var versoB = a.situazione_g === 'gas' || a.situazione_g === 'piu_soluzioni' || a.priorita_g === 'elettrica';
			return versoB ? 'G+B' : 'G+A';
		}
		return path;
	}

	function activePaths() {
		var p = resolvePath();
		if (p === 'G+A') return ['G', 'A'];
		if (p === 'G+B') return ['G', 'B'];
		return [p];
	}

	function visibleSteps() {
		var active = activePaths();
		var intro = [];
		var percorso = [];
		var finale = [];
		var contatti = [];

		CFG.schema.forEach(function (step) {
			var phase = step.phase || (step.paths.indexOf('*') !== -1 ? 'intro' : 'percorso');
			if (phase === 'intro') return void intro.push(step);
			if (phase === 'contatti') return void contatti.push(step);

			var match = step.paths.some(function (p) {
				return active.indexOf(p) !== -1;
			});
			if (!match) return;
			if (phase === 'finale') finale.push(step);
			else percorso.push(step);
		});

		// Gli step di percorso seguono l'ordine dei percorsi attivi: G prima di A/B.
		var ordinati = [];
		active.forEach(function (letter) {
			percorso.forEach(function (step) {
				if (step.paths.indexOf(letter) !== -1) ordinati.push(step);
			});
		});

		return intro.concat(ordinati, finale, contatti);
	}

	function phaseOf(step) {
		return step.phase || (step.paths.indexOf('*') !== -1 ? 'intro' : 'percorso');
	}

	function fieldVisible(field) {
		if (!field.show_if) return true;
		var val = state.answers[field.show_if.field];
		if (Array.isArray(val)) {
			return val.some(function (v) {
				return field.show_if.in.indexOf(v) !== -1;
			});
		}
		return field.show_if.in.indexOf(val) !== -1;
	}

	/* --------------------------------------------------------------------
	 * Utility
	 * ----------------------------------------------------------------- */

	function el(tag, attrs, children) {
		var node = document.createElement(tag);
		Object.keys(attrs || {}).forEach(function (k) {
			if (k === 'text') node.textContent = attrs[k];
			else if (k === 'html') node.innerHTML = attrs[k];
			else if (k === 'class') node.className = attrs[k];
			else if (attrs[k] !== null && attrs[k] !== false) node.setAttribute(k, attrs[k]);
		});
		(children || []).forEach(function (c) {
			if (c) node.appendChild(c);
		});
		return node;
	}

	function uid(name) {
		return 'pdz-' + name + '-' + Math.random().toString(36).slice(2, 8);
	}

	function isLombardia() {
		return CFG.province[state.answers.provincia] === 'Lombardia';
	}

	/* --------------------------------------------------------------------
	 * Rendering
	 * ----------------------------------------------------------------- */

	/**
	 * Colonna laterale: le quattro fasi del percorso con lo stato di avanzamento.
	 */
	function buildRail(steps) {
		var labels = {
			intro: 'Punto di partenza',
			percorso: (CFG.pathLabels && CFG.pathLabels[resolvePath()]) || 'Il tuo percorso',
			finale: 'Valutazione',
			contatti: 'Contatti'
		};

		var groups = [];
		steps.forEach(function (step, i) {
			var phase = phaseOf(step);
			var g = groups[groups.length - 1];
			if (!g || g.phase !== phase) {
				g = { phase: phase, from: i, count: 0 };
				groups.push(g);
			}
			g.count++;
			g.to = i;
		});

		var list = el('ol', { class: 'pdz-rail__list' });

		groups.forEach(function (g) {
			var status = state.index > g.to ? 'is-done' : state.index >= g.from ? 'is-current' : '';
			var mark = el('span', { class: 'pdz-rail__mark', html: status === 'is-done' ? ICONS.check : ICONS[g.phase] });
			list.appendChild(
				el('li', { class: 'pdz-rail__item ' + status }, [
					mark,
					el('span', { class: 'pdz-rail__text' }, [
						el('span', { class: 'pdz-rail__label', text: labels[g.phase] }),
						el('span', { class: 'pdz-rail__count', text: g.count === 1 ? '1 domanda' : g.count + ' domande' })
					])
				])
			);
		});

		return el('aside', { class: 'pdz-rail', 'aria-label': 'Fasi del configuratore' }, [list]);
	}

	function render(focusFirst) {
		var steps = visibleSteps();
		if (state.index >= steps.length) state.index = steps.length - 1;
		var step = steps[state.index];
		var last = state.index === steps.length - 1;

		app.innerHTML = '';

		var layout = el('div', { class: 'pdz-layout' });
		layout.appendChild(buildRail(steps));

		var main = el('div', { class: 'pdz-main' });
		layout.appendChild(main);
		app.appendChild(layout);

		// Barra di avanzamento
		var pct = Math.round((state.index / steps.length) * 100);
		main.appendChild(
			el('div', { class: 'pdz-progress', role: 'group', 'aria-label': 'Avanzamento' }, [
				el('div', { class: 'pdz-progress__track' }, [
					el('div', { class: 'pdz-progress__bar', style: 'width:' + Math.max(pct, 3) + '%' })
				]),
				el('p', {
					class: 'pdz-progress__label',
					text: 'Passaggio ' + (state.index + 1) + ' di ' + steps.length
				})
			])
		);

		var formClass = 'pdz-step';
		if (focusFirst === false) {
			formClass += ' is-static';
		} else {
			formClass += ' pdz-step--' + (state.direction === 'back' ? 'back' : 'fwd');
		}
		var form = el('form', { class: formClass, novalidate: 'novalidate' });
		form.appendChild(
			el('div', { class: 'pdz-step__head' }, [
				el('span', { class: 'pdz-step__icon', html: ICONS[phaseOf(step)] }),
				el('h3', { class: 'pdz-step__title', text: step.title })
			])
		);
		if (step.intro) form.appendChild(el('p', { class: 'pdz-step__intro', text: step.intro }));

		step.fields.forEach(function (field) {
			if (!fieldVisible(field)) return;
			form.appendChild(renderField(field));
		});

		// Avviso fuori Lombardia
		if (step.id === 'localizzazione' && state.answers.provincia && !isLombardia()) {
			form.appendChild(
				el('p', {
					class: 'pdz-notice',
					text: 'Pedrinazzi Srl opera principalmente in Lombardia. Puoi comunque inviare la richiesta: valuteremo se il caso può essere seguito o se è preferibile orientarti verso un altro percorso.'
				})
			);
		}

		form.appendChild(el('p', { class: 'pdz-error', 'aria-live': 'polite', hidden: 'hidden' }));

		var nav = el('div', { class: 'pdz-nav' });
		if (state.index > 0) {
			nav.appendChild(el('button', { type: 'button', class: 'pdz-btn pdz-btn--ghost', text: 'Indietro', 'data-back': '1' }));
		}
		nav.appendChild(
			el('button', {
				type: 'submit',
				class: 'pdz-btn pdz-btn--primary',
				text: last ? 'Invia richiesta di valutazione' : 'Continua'
			})
		);
		form.appendChild(nav);

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!validate(step, form)) return;
			if (last) submit(form);
			else go(1);
		});

		var backBtn = form.querySelector('[data-back]');
		if (backBtn) backBtn.addEventListener('click', function () { go(-1); });

		main.appendChild(form);

		if (focusFirst !== false) {
			var h = form.querySelector('.pdz-step__title');
			h.setAttribute('tabindex', '-1');
			h.focus({ preventScroll: true });
			var top = root.getBoundingClientRect().top + window.pageYOffset - 24;
			if (window.pageYOffset > top) window.scrollTo({ top: top, behavior: 'smooth' });
		}
	}

	function renderField(field) {
		var wrap = el('div', { class: 'pdz-field', 'data-field': field.name });
		var value = state.answers[field.name];

		if (field.type === 'radio' || field.type === 'checkbox') {
			var group = el('fieldset', { class: 'pdz-group' });
			var legend = el('legend', { class: field.hide_label ? 'pdz-legend pdz-legend--sr' : 'pdz-legend', text: field.label });
			group.appendChild(legend);
			if (field.hint) group.appendChild(el('p', { class: 'pdz-hint', text: field.hint }));

			var iconic = !!ICON_MAP[field.name];
			var list = el('div', { class: iconic ? 'pdz-options pdz-options--iconic' : 'pdz-options' });
			Object.keys(field.options).forEach(function (val) {
				var id = uid(field.name);
				var checked =
					field.type === 'checkbox'
						? Array.isArray(value) && value.indexOf(val) !== -1
						: value === val;

				var input = el('input', {
					type: field.type,
					name: field.name,
					id: id,
					value: val,
					class: 'pdz-option__input',
					checked: checked ? 'checked' : null
				});

				input.addEventListener('change', function () {
					if (field.type === 'checkbox') {
						var arr = Array.isArray(state.answers[field.name]) ? state.answers[field.name].slice() : [];
						var i = arr.indexOf(val);
						if (input.checked && i === -1) arr.push(val);
						if (!input.checked && i !== -1) arr.splice(i, 1);
						state.answers[field.name] = arr;
					} else {
						state.answers[field.name] = val;
					}
					onChange(field);
				});

				var parts = [input];
				var icon = optionIcon(field.name, val);
				if (icon) {
					parts.push(el('span', { class: 'pdz-option__icon', html: icon }));
				}
				parts.push(el('span', { class: 'pdz-option__text', text: field.options[val] }));

				list.appendChild(el('label', { class: 'pdz-option', for: id }, parts));
			});

			group.appendChild(list);
			wrap.appendChild(group);
			return wrap;
		}

		var inputId = uid(field.name);

		if (field.type !== 'consent') {
			wrap.appendChild(el('label', { class: 'pdz-label', for: inputId, text: field.label }));
			if (field.hint) wrap.appendChild(el('p', { class: 'pdz-hint', text: field.hint }));
		}

		var control;

		if (field.type === 'select') {
			control = el('select', { id: inputId, name: field.name, class: 'pdz-input' });
			control.appendChild(el('option', { value: '', text: 'Seleziona…' }));
			Object.keys(field.options).forEach(function (val) {
				control.appendChild(
					el('option', { value: val, text: field.options[val], selected: value === val ? 'selected' : null })
				);
			});
			control.addEventListener('change', function () {
				state.answers[field.name] = control.value;
				onChange(field);
			});
		} else if (field.type === 'textarea') {
			control = el('textarea', {
				id: inputId,
				name: field.name,
				class: 'pdz-input pdz-input--area',
				rows: field.rows || 4,
				placeholder: field.placeholder || ''
			});
			control.value = value || '';
			control.addEventListener('input', function () {
				state.answers[field.name] = control.value;
			});
		} else if (field.type === 'file') {
			control = renderFileField(field, inputId);
		} else if (field.type === 'consent') {
			var cid = uid(field.name);
			var cb = el('input', { type: 'checkbox', id: cid, class: 'pdz-consent__input', checked: value ? 'checked' : null });
			cb.addEventListener('change', function () {
				state.answers[field.name] = cb.checked;
			});
			var labelText = CFG.privacyText || field.label;
			var consent = el('label', { class: 'pdz-consent', for: cid }, [cb]);
			var span = el('span', {});
			span.textContent = labelText + ' ';
			if (CFG.privacyUrl) {
				span.appendChild(
					el('a', {
						href: CFG.privacyUrl,
						target: '_blank',
						rel: 'noopener',
						text: CFG.privacyLinkLabel || 'Leggi l’informativa'
					})
				);
			}
			consent.appendChild(span);
			control = consent;
		} else {
			control = el('input', {
				type: field.type,
				id: inputId,
				name: field.name,
				class: 'pdz-input',
				value: value || '',
				placeholder: field.placeholder || '',
				inputmode: field.inputmode || null,
				maxlength: field.maxlength || null,
				autocomplete: field.autocomplete || null
			});
			control.addEventListener('input', function () {
				state.answers[field.name] = control.value;
			});
			control.addEventListener('change', function () {
				onChange(field);
			});
		}

		wrap.appendChild(control);
		return wrap;
	}

	function renderFileField(field, inputId) {
		var box = el('div', { class: 'pdz-file' });
		var input = el('input', {
			type: 'file',
			id: inputId,
			multiple: 'multiple',
			class: 'pdz-file__input',
			accept: (CFG.allowedExt || []).map(function (e) { return '.' + e; }).join(',')
		});
		var list = el('ul', { class: 'pdz-file__list' });
		var warn = el('p', { class: 'pdz-file__warn', 'aria-live': 'polite', hidden: 'hidden' });
		var meter = el('p', { class: 'pdz-file__meter' });

		function refresh() {
			list.innerHTML = '';
			(state.files[field.name] || []).forEach(function (f, i) {
				var remove = el('button', { type: 'button', class: 'pdz-file__remove', text: 'Rimuovi', 'aria-label': 'Rimuovi ' + f.name });
				remove.addEventListener('click', function () {
					state.files[field.name].splice(i, 1);
					refresh();
				});
				list.appendChild(
					el('li', {}, [
						el('span', { text: f.name + ' (' + Math.round(f.size / 1024) + ' KB)' }),
						remove
					])
				);
			});

			var n = countFiles();
			if (n) {
				meter.textContent = n + ' file su ' + CFG.maxFiles + ' · ' + mb(totalSize()) + ' di ' + mb(CFG.maxTotal);
				meter.hidden = false;
			} else {
				meter.hidden = true;
			}
		}

		function totalSize() {
			var t = 0;
			Object.keys(state.files).forEach(function (name) {
				state.files[name].forEach(function (f) {
					t += f.size;
				});
			});
			return t;
		}

		function countFiles() {
			var n = 0;
			Object.keys(state.files).forEach(function (name) {
				n += state.files[name].length;
			});
			return n;
		}

		function mb(bytes) {
			return (bytes / 1048576).toFixed(1).replace('.', ',') + ' MB';
		}

		input.addEventListener('change', function () {
			var current = state.files[field.name] || [];
			var problemi = [];

			Array.prototype.forEach.call(input.files, function (f) {
				if (f.size > CFG.maxSize) {
					problemi.push(f.name + ' pesa ' + mb(f.size) + ', oltre il limite di ' + mb(CFG.maxSize) + ' per file.');
					return;
				}
				if (countFiles() >= CFG.maxFiles) {
					problemi.push('Puoi allegare al massimo ' + CFG.maxFiles + ' file.');
					return;
				}
				if (totalSize() + f.size > CFG.maxTotal) {
					problemi.push('Con ' + f.name + ' si supera il totale di ' + mb(CFG.maxTotal) + ' consentito dal server.');
					return;
				}
				current.push(f);
			});

			state.files[field.name] = current;
			input.value = '';
			refresh();

			warn.textContent = problemi.join(' ');
			warn.hidden = !problemi.length;
		});

		box.appendChild(input);
		box.appendChild(el('label', { class: 'pdz-file__cta', for: inputId, text: 'Scegli i file' }));
		box.appendChild(list);
		box.appendChild(meter);
		box.appendChild(warn);
		refresh();
		return box;
	}

	/**
	 * Da mobile, dopo una scelta singola porta l'utente alla domanda successiva
	 * dello step, o al pulsante Continua se era l'ultima.
	 */
	function advanceOnMobile(field) {
		if (field.type !== 'radio') return;
		if (!window.matchMedia('(max-width: 62rem)').matches) return;

		var smooth = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		setTimeout(function () {
			var current = app.querySelector('[data-field="' + field.name + '"]');
			if (!current) return;

			// Prima domanda ancora da rispondere dopo questa.
			var next = null;
			var node = current.nextElementSibling;
			while (node) {
				if (node.classList && node.classList.contains('pdz-field')) {
					next = node;
					break;
				}
				node = node.nextElementSibling;
			}

			var target = next || app.querySelector('.pdz-nav .pdz-btn--primary');
			if (!target) return;

			var top = target.getBoundingClientRect().top + window.pageYOffset - 24;
			window.scrollTo({ top: top, behavior: smooth ? 'smooth' : 'auto' });
		}, 260);
	}

	/**
	 * Ridisegna lo step quando una risposta apre o chiude campi condizionali.
	 */
	function onChange(field) {
		var step = visibleSteps()[state.index];
		var dependent = step.fields.some(function (f) {
			return f.show_if && f.show_if.field === field.name;
		});
		if (dependent || (step.id === 'localizzazione' && field.name === 'provincia')) {
			var active = document.activeElement;
			var selector = active && active.name ? '[name="' + active.name + '"]' + (active.value ? '[value="' + active.value + '"]' : '') : null;
			render(false);
			if (selector) {
				var again = app.querySelector(selector);
				if (again) again.focus({ preventScroll: true });
			}
		}
		clearError();
		advanceOnMobile(field);
		trackStep();
	}

	/* --------------------------------------------------------------------
	 * Validazione e navigazione
	 * ----------------------------------------------------------------- */

	function showError(form, message, fieldName) {
		var target = fieldName ? form.querySelector('[data-field="' + fieldName + '"]') : null;

		// Errore su un campo: il messaggio va accanto alla domanda, non in fondo.
		if (target) {
			target.classList.add('is-invalid');

			var note = el('p', { class: 'pdz-fielderror', role: 'alert', text: message });
			var anchor = target.querySelector('.pdz-legend, .pdz-label, .pdz-hint');

			if (anchor && anchor.nextSibling) {
				anchor.parentNode.insertBefore(note, anchor.nextSibling);
			} else {
				target.insertBefore(note, target.firstChild);
			}

			var focusable = target.querySelector('input, select, textarea');
			if (focusable) focusable.focus({ preventScroll: true });
			target.scrollIntoView({ block: 'center', behavior: 'smooth' });
			return;
		}

		// Errore generale (invio non riuscito).
		var box = form.querySelector('.pdz-error');
		box.textContent = message;
		box.hidden = false;
	}

	function clearError() {
		var box = app.querySelector('.pdz-error');
		if (box) box.hidden = true;

		Array.prototype.forEach.call(app.querySelectorAll('.is-invalid'), function (n) {
			n.classList.remove('is-invalid');
		});
		Array.prototype.forEach.call(app.querySelectorAll('.pdz-fielderror'), function (n) {
			n.remove();
		});
	}

	function validate(step, form) {
		clearError();

		for (var i = 0; i < step.fields.length; i++) {
			var f = step.fields[i];
			if (!fieldVisible(f) || !f.required) continue;

			var v = state.answers[f.name];
			var empty = v === undefined || v === null || v === '' || (Array.isArray(v) && !v.length) || v === false;

			if (empty) {
				showError(
					form,
					f.type === 'consent'
						? 'Per inviare la richiesta serve il consenso al trattamento dei dati.'
						: f.type === 'checkbox'
							? 'Seleziona almeno una risposta per proseguire.'
							: 'Questa risposta serve per proseguire.',
					f.name
				);
				return false;
			}
			if (f.name === 'cap' && !/^[0-9]{5}$/.test(v)) {
				showError(form, 'Il CAP deve avere 5 cifre, per esempio 27100.', 'cap');
				return false;
			}
			if (f.type === 'email' && !/^[^@\s]+@[^@\s]+\.[a-z]{2,}$/i.test(v)) {
				showError(form, 'Controlla l’indirizzo email: manca la chiocciola o il dominio.', 'email');
				return false;
			}
			if (f.type === 'tel' && String(v).replace(/\D/g, '').length < 8) {
				showError(form, 'Controlla il numero di telefono: servono almeno 8 cifre.', 'telefono');
				return false;
			}
		}
		return true;
	}

	function go(delta) {
		state.direction = delta < 0 ? 'back' : 'fwd';
		state.index = Math.max(0, state.index + delta);
		render(true);
		trackStep();
	}

	/* --------------------------------------------------------------------
	 * Andamento: dove si ferma chi non completa
	 * ----------------------------------------------------------------- */

	function sessionKey() {
		if (!CFG.track) return '';
		try {
			var k = window.sessionStorage.getItem('pdzConfKey');
			if (!k) {
				k = 'p' + Date.now().toString(36) + Math.random().toString(36).slice(2, 10);
				window.sessionStorage.setItem('pdzConfKey', k);
			}
			return k;
		} catch (err) {
			return '';
		}
	}

	/**
	 * Registra lo step raggiunto. Non invia risposte: solo a che punto è arrivato.
	 */
	function trackStep(extra) {
		if (!CFG.track || !state.startedAt) return;

		var key = sessionKey();
		if (!key) return;

		var steps = visibleSteps();
		var step = steps[state.index];
		if (!step) return;

		var body = new URLSearchParams();
		body.set('action', 'pdz_conf_track');
		body.set('nonce', CFG.nonce);
		body.set('chiave', key);
		body.set('step', step.id);
		body.set('indice', state.index + 1);
		body.set('totale', steps.length);
		body.set('percorso', resolvePath());
		body.set('secondi', Math.round((Date.now() - state.startedAt) / 1000));
		if (extra) body.set('esito', extra);

		// In uscita dalla pagina serve un invio che sopravviva alla chiusura.
		if (extra === 'uscita' && navigator.sendBeacon) {
			navigator.sendBeacon(CFG.ajaxUrl, body);
			return;
		}

		fetch(CFG.ajaxUrl, {
			method: 'POST',
			body: body,
			credentials: 'same-origin',
			keepalive: true
		}).catch(function () {
			/* il tracciamento non deve mai disturbare la compilazione */
		});
	}

	window.addEventListener('pagehide', function () {
		if (state.startedAt && !state.done) trackStep('uscita');
	});

	/* --------------------------------------------------------------------
	 * Invio
	 * ----------------------------------------------------------------- */

	function submit(form) {
		if (state.sending) return;
		state.sending = true;

		var btn = form.querySelector('button[type="submit"]');
		btn.disabled = true;
		btn.textContent = 'Invio in corso…';

		var fd = new FormData();
		fd.append('action', 'pdz_conf_submit');
		fd.append('nonce', CFG.nonce);
		fd.append('answers', JSON.stringify(state.answers));
		fd.append('elapsed', Math.round((Date.now() - state.startedAt) / 1000));
		fd.append('pdz_website', '');
		fd.append('chiave', sessionKey());

		Object.keys(state.files).forEach(function (name) {
			state.files[name].forEach(function (f) {
				fd.append(name + '[]', f, f.name);
			});
		});

		fetch(CFG.ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
			.then(function (r) { return r.json().catch(function () { return { success: false }; }); })
			.then(function (res) {
				if (res && res.success) {
					state.done = true;
					if (res.data.redirect) {
						window.location.assign(res.data.redirect);
						return;
					}
					done(res.data.message);
				} else {
					state.sending = false;
					btn.disabled = false;
					btn.textContent = 'Invia richiesta di valutazione';
					showError(form, (res && res.data && res.data.message) || 'Invio non riuscito. Riprova o scrivici a info@pedrinazzi.it.');
				}
			})
			.catch(function () {
				state.sending = false;
				btn.disabled = false;
				btn.textContent = 'Invia richiesta di valutazione';
				showError(form, 'Connessione non riuscita. Riprova o scrivici a info@pedrinazzi.it.');
			});
	}

	function done(message) {
		state.done = true;
		app.innerHTML = '';
		var box = el('div', { class: 'pdz-done', html: message });
		box.setAttribute('tabindex', '-1');
		app.appendChild(box);
		box.focus({ preventScroll: true });
		box.scrollIntoView({ block: 'center', behavior: 'smooth' });
	}

	/* --------------------------------------------------------------------
	 * Avvio
	 * ----------------------------------------------------------------- */

	function start() {
		state.startedAt = Date.now();

		if (intro) intro.hidden = true;
		var points = root.querySelector('[data-pdz-intro-points]');
		if (points) points.hidden = true;

		app.hidden = false;
		render(true);
		trackStep('avvio');
	}

	if (startBtn) startBtn.addEventListener('click', start);
})();
