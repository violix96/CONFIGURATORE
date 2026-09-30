/**
 * Interazioni delle schermate di amministrazione.
 */
(function () {
	'use strict';

	/* --- Conferme ------------------------------------------------------- */

	document.addEventListener('click', function (e) {
		var el = e.target.closest('[data-pdz-confirm]');
		if (el && !window.confirm(el.getAttribute('data-pdz-confirm'))) {
			e.preventDefault();
		}
	});

	/* --- Impostazioni: schede e barra di salvataggio ---------------------- */

	var tabsNav = document.querySelector('[data-pdz-tabs]');

	if (tabsNav) {
		var panels = document.querySelectorAll('[data-pdz-panel]');
		var tabField = document.querySelector('[data-pdz-tab-field]');

		function showTab(key) {
			var found = false;
			panels.forEach(function (panel) {
				var on = panel.getAttribute('data-pdz-panel') === key;
				panel.classList.toggle('is-active', on);
				if (on) found = true;
			});
			tabsNav.querySelectorAll('[data-pdz-tab]').forEach(function (btn) {
				btn.classList.toggle('is-active', btn.getAttribute('data-pdz-tab') === key);
			});
			if (found) {
				if (tabField) tabField.value = key;
				history.replaceState(null, '', '#' + key);
			}
			return found;
		}

		tabsNav.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-pdz-tab]');
			if (btn) showTab(btn.getAttribute('data-pdz-tab'));
		});

		// Apre la scheda indicata nell'indirizzo, altrimenti la prima.
		var start = (window.location.hash || '').replace('#', '');
		if (!start || !showTab(start)) {
			showTab(panels[0].getAttribute('data-pdz-panel'));
		}

		// Segnala le modifiche non salvate.
		var bar = document.querySelector('.pdz-stickysave');
		var form = document.querySelector('.pdz-settings__form');
		if (bar && form) {
			form.addEventListener('input', function () {
				bar.classList.add('is-dirty');
			});
			form.addEventListener('change', function () {
				bar.classList.add('is-dirty');
			});
		}

		// Anteprima dell'email, ricalcolata mentre si scrive.
		var previews = document.querySelectorAll('[data-pdz-preview]');

		if (previews.length && form) {
			var timer = null;

			function refreshPreviews() {
				previews.forEach(function (box) {
					var frame = box.querySelector('[data-pdz-preview-frame]');
					var state = box.querySelector('[data-pdz-preview-state]');
					if (!frame) return;

					if (state) state.textContent = 'aggiorno…';

					var body = new FormData(form);
					body.append('action', 'pdz_conf_preview_email');
					body.append('nonce', box.getAttribute('data-nonce'));
					body.append('type', box.getAttribute('data-pdz-preview'));

					fetch(box.getAttribute('data-ajax'), { method: 'POST', body: body, credentials: 'same-origin' })
						.then(function (r) { return r.json(); })
						.then(function (res) {
							if (!res || !res.success) throw new Error('ko');

							frame.srcdoc = res.data.body;
							box.querySelector('[data-pdz-preview-subject]').textContent = res.data.subject || '—';
							box.querySelector('[data-pdz-preview-to]').textContent = res.data.to || '—';
							box.querySelector('[data-pdz-preview-from]').textContent = res.data.from || '—';
							if (state) state.textContent = '';
						})
						.catch(function () {
							if (state) state.textContent = 'anteprima non disponibile';
						});
				});
			}

			// L'altezza della cornice segue il contenuto.
			previews.forEach(function (box) {
				var frame = box.querySelector('[data-pdz-preview-frame]');
				if (!frame) return;
				frame.addEventListener('load', function () {
					try {
						var h = frame.contentDocument.body.scrollHeight;
						frame.style.height = Math.min(Math.max(h + 24, 260), 900) + 'px';
					} catch (err) {
						/* niente */
					}
				});
			});

			form.addEventListener('input', function () {
				clearTimeout(timer);
				timer = setTimeout(refreshPreviews, 600);
			});
			form.addEventListener('change', function () {
				clearTimeout(timer);
				timer = setTimeout(refreshPreviews, 300);
			});

			refreshPreviews();
		}

		// Le regolazioni di un tema si vedono solo quando quel tema è scelto.
		var soloTema = document.querySelectorAll('[data-pdz-solotema]');

		if (soloTema.length) {
			function aggiornaTema() {
				var scelto = document.querySelector('input[name="pdz[tema]"]:checked');
				var valore = scelto ? scelto.value : '';

				soloTema.forEach(function (box) {
					box.hidden = box.getAttribute('data-pdz-solotema') !== valore;
				});
			}

			document.querySelectorAll('input[name="pdz[tema]"]').forEach(function (radio) {
				radio.addEventListener('change', aggiornaTema);
			});

			aggiornaTema();
		}

		// Il selettore colore aggiorna il campo di testo e viceversa.
		document.querySelectorAll('[data-pdz-colorpick]').forEach(function (picker) {
			var text = picker.parentNode.querySelector('.pdz-input--color');
			if (!text) return;
			picker.addEventListener('input', function () {
				text.value = picker.value;
			});
			text.addEventListener('input', function () {
				if (/^#[0-9a-f]{6}$/i.test(text.value)) picker.value = text.value;
			});
		});
	}

	/* --- Selezione multipla nelle richieste -------------------------------- */

	var bulk = document.querySelector('[data-pdz-bulk]');

	if (bulk) {
		var all = bulk.querySelector('[data-pdz-checkall]');
		var actions = bulk.querySelector('[data-pdz-bulkactions]');
		var counter = bulk.querySelector('[data-pdz-bulkcount]');

		function boxes() {
			return Array.prototype.slice.call(bulk.querySelectorAll('[data-pdz-check]'));
		}

		function refreshBulk() {
			var scelte = boxes().filter(function (b) {
				return b.checked;
			});

			if (actions) actions.hidden = !scelte.length;
			if (counter) {
				counter.textContent = scelte.length === 1 ? '1 selezionata' : scelte.length + ' selezionate';
			}
			if (all) {
				all.checked = scelte.length > 0 && scelte.length === boxes().length;
				all.indeterminate = scelte.length > 0 && scelte.length < boxes().length;
			}

			boxes().forEach(function (b) {
				var card = b.closest('.pdz-lead');
				if (card) card.classList.toggle('is-selected', b.checked);
			});
		}

		if (all) {
			all.addEventListener('change', function () {
				boxes().forEach(function (b) {
					b.checked = all.checked;
				});
				refreshBulk();
			});
		}

		bulk.addEventListener('change', function (e) {
			if (e.target.matches('[data-pdz-check]')) refreshBulk();
		});

		// Evita invii a vuoto.
		bulk.addEventListener('submit', function (e) {
			if (!boxes().some(function (b) { return b.checked; })) {
				e.preventDefault();
				window.alert('Seleziona almeno una richiesta.');
			}
		});

		refreshBulk();
	}

	/* --- Trascinamento generico ----------------------------------------- */

	/**
	 * Rende ordinabile una lista di elementi.
	 * onDrop riceve il contenitore quando l'ordine cambia.
	 */
	function makeSortable(list, itemSelector, onDrop) {
		var dragged = null;

		list.addEventListener('dragstart', function (e) {
			var item = e.target.closest(itemSelector);
			if (!item || !list.contains(item)) return;
			dragged = item;
			item.classList.add('is-dragging');
			e.dataTransfer.effectAllowed = 'move';
			try {
				e.dataTransfer.setData('text/plain', '');
			} catch (err) {
				/* Safari */
			}
		});

		list.addEventListener('dragover', function (e) {
			if (!dragged) return;
			e.preventDefault();

			var over = e.target.closest(itemSelector);
			if (!over || over === dragged || !list.contains(over)) return;

			var rect = over.getBoundingClientRect();
			var after = e.clientY > rect.top + rect.height / 2;

			list.querySelectorAll('.is-over').forEach(function (n) {
				n.classList.remove('is-over');
			});
			over.classList.add('is-over');

			list.insertBefore(dragged, after ? over.nextSibling : over);
		});

		list.addEventListener('dragend', function () {
			if (!dragged) return;
			dragged.classList.remove('is-dragging');
			list.querySelectorAll('.is-over').forEach(function (n) {
				n.classList.remove('is-over');
			});
			dragged = null;
			if (onDrop) onDrop(list);
		});

		list.addEventListener('drop', function (e) {
			e.preventDefault();
		});
	}

	/* --- Elenco degli step ------------------------------------------------ */

	var board = document.querySelector('[data-pdz-board]');

	if (board) {
		var savebar = document.querySelector('[data-pdz-savebar]');
		var saveBtn = document.querySelector('[data-pdz-save-order]');
		var saveTxt = document.querySelector('[data-pdz-savebar-text]');

		board.querySelectorAll('[data-pdz-sortable]').forEach(function (list) {
			makeSortable(list, '[data-pdz-card]', function () {
				if (savebar) savebar.hidden = false;
			});
		});

		// Salvataggio del nuovo ordine.
		if (saveBtn) {
			saveBtn.addEventListener('click', function () {
				var order = [];
				board.querySelectorAll('[data-pdz-card]').forEach(function (card) {
					order.push(card.getAttribute('data-id'));
				});

				var body = new FormData();
				body.append('action', 'pdz_conf_reorder_steps');
				body.append('nonce', board.getAttribute('data-nonce'));
				order.forEach(function (id) {
					body.append('order[]', id);
				});

				saveBtn.disabled = true;
				saveBtn.textContent = 'Salvataggio…';

				fetch(board.getAttribute('data-ajax'), { method: 'POST', body: body, credentials: 'same-origin' })
					.then(function (r) {
						return r.json();
					})
					.then(function (res) {
						if (res && res.success) {
							if (saveTxt) saveTxt.textContent = 'Ordine salvato';
							saveBtn.textContent = 'Salvato';
							setTimeout(function () {
								if (savebar) savebar.hidden = true;
								saveBtn.disabled = false;
								saveBtn.textContent = 'Salva ordine';
								if (saveTxt) saveTxt.textContent = 'Ordine modificato';
							}, 1200);
						} else {
							throw new Error('ko');
						}
					})
					.catch(function () {
						saveBtn.disabled = false;
						saveBtn.textContent = 'Riprova';
						if (saveTxt) saveTxt.textContent = 'Salvataggio non riuscito';
					});
			});
		}

		// Ricerca rapida fra gli step.
		var search = document.querySelector('[data-pdz-search]');
		if (search) {
			search.addEventListener('input', function () {
				var q = search.value.trim().toLowerCase();
				board.querySelectorAll('[data-pdz-card]').forEach(function (card) {
					var hit = !q || card.getAttribute('data-search').indexOf(q) !== -1;
					card.style.display = hit ? '' : 'none';
				});
			});
		}
	}

	/* --- Editor dello step ------------------------------------------------- */

	var wrap = document.querySelector('[data-pdz-fields]');
	if (!wrap) return;

	var template = document.getElementById('pdz-field-template');
	var addBtn = document.querySelector('[data-pdz-add-field]');
	var counter = wrap.querySelectorAll('[data-pdz-field]').length;

	makeSortable(wrap, '[data-pdz-field]');

	// Apertura e chiusura delle schede.
	wrap.addEventListener('click', function (e) {
		if (e.target.closest('.pdz-q__grip')) return;

		var head = e.target.closest('[data-pdz-toggle]');
		if (head) {
			head.closest('[data-pdz-field]').classList.toggle('is-open');
			return;
		}

		var remove = e.target.closest('[data-pdz-remove-field]');
		if (remove) {
			var block = remove.closest('[data-pdz-field]');
			if (block && window.confirm('Rimuovere questa domanda dallo step?')) {
				block.remove();
			}
		}
	});

	// Aggiunta di una domanda.
	if (addBtn && template) {
		addBtn.addEventListener('click', function () {
			var holder = document.createElement('div');
			holder.innerHTML = template.innerHTML.replace(/__i__/g, 'n' + counter);
			counter++;

			var block = holder.querySelector('[data-pdz-field]');
			wrap.appendChild(block);
			block.scrollIntoView({ block: 'center', behavior: 'smooth' });

			var first = block.querySelector('[data-pdz-label]');
			if (first) first.focus();
		});
	}

	// L'intestazione della scheda segue quello che si scrive.
	wrap.addEventListener('input', function (e) {
		var block = e.target.closest('[data-pdz-field]');
		if (!block) return;

		if (e.target.matches('[data-pdz-label]')) {
			var title = block.querySelector('[data-pdz-qtitle]');
			if (title) title.textContent = e.target.value || 'Nuova domanda';
		}
	});

	wrap.addEventListener('change', function (e) {
		var block = e.target.closest('[data-pdz-field]');
		if (!block) return;

		if (e.target.matches('[data-pdz-type]')) {
			var label = block.querySelector('[data-pdz-qtype]');
			if (label) label.textContent = e.target.options[e.target.selectedIndex].text;

			// Le risposte servono solo ai campi a scelta.
			var opts = block.querySelector('[data-pdz-options]');
			var conScelte = ['radio', 'checkbox', 'select'].indexOf(e.target.value) !== -1;
			if (opts) opts.style.display = conScelte ? '' : 'none';
		}
	});

	// Stato iniziale: nascondi le risposte dove non servono.
	wrap.querySelectorAll('[data-pdz-field]').forEach(function (block) {
		var type = block.querySelector('[data-pdz-type]');
		var opts = block.querySelector('[data-pdz-options]');
		if (type && opts && ['radio', 'checkbox', 'select'].indexOf(type.value) === -1) {
			opts.style.display = 'none';
		}
	});

	// I percorsi servono solo agli step di percorso e finali.
	var phase = document.querySelector('[data-pdz-phase]');
	var paths = document.querySelector('[data-pdz-paths]');
	if (phase && paths) {
		phase.addEventListener('change', function () {
			var hidden = phase.value === 'intro' || phase.value === 'contatti';
			paths.style.display = hidden ? 'none' : '';
		});
	}
})();
