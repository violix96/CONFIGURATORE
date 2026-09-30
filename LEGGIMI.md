# Configuratore — plugin WordPress

Versione 4.1.2. Sostituisce lo snippet di codice: prima di attivarlo, **disattiva ed elimina lo snippet** in Code Snippets, altrimenti le funzioni vengono dichiarate due volte e il sito va in errore fatale.

## Installazione

1. Bacheca → Plugin → Aggiungi nuovo → Carica plugin → seleziona lo zip → Installa → Attiva.
2. In pagina inserisci lo shortcode `[configuratore_pedrinazzi]`.
3. Vai in **Configuratore → Impostazioni** e compila almeno destinatari email e thank you page.

All'attivazione il plugin crea la tabella `wp_pdz_conf_leads` e carica le 42 domande predefinite.

## SMTP

Non c'è nulla da configurare nel plugin. Le email partono con `wp_mail()`, la stessa funzione usata dai form di Elementor: qualsiasi plugin SMTP (WP Mail SMTP, FluentSMTP, Post SMTP) le intercetta e le instrada.

Unica accortezza: in **Impostazioni → Mittente**, se lasci vuoto l'indirizzo mittente vale quello impostato nel plugin SMTP, ed è la scelta più sicura. Se lo compili, usa un indirizzo del dominio autorizzato in SPF/DKIM, altrimenti le email finiscono in spam.

## Le due email

Ogni invio genera due messaggi:

- **al gestore del sito** — riepilogo completo di tutte le risposte raggruppate per step, percorso, punteggio e classificazione del lead, link agli allegati e allegati veri e propri sotto i 4 MB. Il campo Reply-To è l'indirizzo dell'utente, quindi si risponde direttamente dalla posta. Le richieste della Divisione Elettrica possono andare a destinatari diversi.
- **all'utente** — conferma con il riepilogo dei dati inseriti, senza punteggio interno e senza link agli allegati. Oltre a disattivarla del tutto, si decide cosa contiene: risposte tecniche, dati di contatto, elenco dei file caricati e, volendo, i file stessi in allegato. Togliendo tutto resta solo il testo di conferma.

Nelle due sezioni delle email c'è un'**anteprima dal vivo**: mostra destinatario, mittente, oggetto e corpo formattato di una richiesta di esempio, e si aggiorna mentre modifichi i campi. È generata con lo stesso codice che compone le email vere, quindi quello che vedi è quello che arriva.

Oggetto e testi accettano i segnaposto `{nome}`, `{email}`, `{telefono}`, `{comune}`, `{provincia}`, `{percorso}`, `{punteggio}`.

## Thank you page

In **Impostazioni → Thank you page** ci sono tre campi:

- utente in Lombardia → pagina A;
- utente fuori Lombardia → pagina B;
- richiesta Divisione Elettrica → pagina C, facoltativa.

La provenienza si ricava dalla provincia indicata nello step 2. Se il campo resta vuoto non c'è redirect e viene mostrato in pagina il messaggio corrispondente, modificabile nella sezione sotto.

## Domande

**Configuratore → Domande** elenca i 42 step nell'ordine di compilazione. Per ognuno si modificano titolo, testo esplicativo, fase e percorsi; dentro lo step si aggiungono, si modificano e si rimuovono le domande.

Le fasi determinano quando compare lo step:

| Fase | Quando compare |
|---|---|
| Comune iniziale | sempre, prima della diramazione |
| Percorso | solo nei percorsi selezionati (A–G) |
| Comune finale | dopo il percorso, nei percorsi selezionati |
| Contatti | sempre, come ultimo step |

**Opzioni**: una per riga, formato `chiave|Etichetta mostrata|punti`. La chiave è il valore tecnico salvato nel database, l'etichetta è quello che legge l'utente, i punti sono facoltativi e alimentano il punteggio del lead. Cambiare una chiave rompe il collegamento con le risposte già ricevute e con le condizioni che la usano.

**Domande condizionali**: nei due campi in fondo al blocco indichi il nome tecnico di un'altra domanda e le risposte che la attivano. Esempio: la domanda sull'accumulo compare solo se `fv_presente` vale `si`.

Il pulsante di ripristino riporta tutte le domande alla configurazione iniziale senza toccare le richieste ricevute.

## Schermata di apertura

È la prima cosa che vede l'utente e si compone in **Impostazioni → Testi di apertura**: sopratitolo, titolo, testo, nota evidenziata, testo del pulsante e riga di rassicurazione sotto al pulsante.

I tre **punti di apertura** si scrivono uno per riga come `Titolo|Descrizione`: spiegano cosa aspettarsi prima di cominciare. Lasciando il campo vuoto spariscono.

Il **tema** decide la disposizione di domande e fasi: *Corsia laterale* (predefinito), *Pannello* (tutto dentro una scheda rialzata), *Contrasto* (colonna delle fasi su fondo scuro), *Ampio* (corsia a sole icone, titoli grandi, risposte su quattro colonne), *Vivace* (pannello scuro con aloni in movimento e riquadri di vetro). Contenuti e logica non cambiano mai. Con una spunta si toglie anche la riga «3 domande» sotto il nome di ogni fase.

L'**illustrazione** si sceglie fra sette disegni animati — circuito con pompa, schema d’impianto, pompa e dosaggio, onda termica, resa energetica, impulso in rete, emissione — oppure si toglie del tutto, e in quel caso il testo occupa tutta la larghezza. Le anteprime nelle impostazioni sono animate come in pagina. Sono disegni vettoriali generati dal plugin, non immagini da caricare: seguono il colore di accento e non pesano sul caricamento.

## Priorità delle richieste

Ogni richiesta riceve un punteggio da 0 a 100 che diventa un'etichetta di priorità (alta, media, bassa) per capire a colpo d'occhio chi richiamare prima. I tre nomi sono modificabili in Impostazioni: se "Priorità alta" non convince, si scrivono le parole che preferite e cambiano ovunque, elenco, filtri, CSV ed email interna.

Il punteggio è la somma di:

- i punti delle singole risposte, impostati opzione per opzione nell'editor delle domande;
- i bonus di provenienza e di qualità delle informazioni (Lombardia, Nord Italia, allegati, preventivo caricato, dati tecnici completi, messaggio dettagliato), impostati in Impostazioni → Punteggio lead.

Le soglie sono modificabili. Il totale è comunque limitato a 100, quindi alzare tutti i pesi non cambia le proporzioni.

## Richieste

**Configuratore → Richieste** mostra l'elenco filtrabile per priorità, percorso e stato, con ricerca su nome, email, telefono e comune, ordinamento per data o punteggio ed esportazione CSV che rispetta i filtri attivi. Aprendo una richiesta si vedono tutte le risposte, gli allegati, i contatti cliccabili e si possono impostare stato e note interne.

**Cestino.** "Sposta nel cestino" toglie la richiesta dall'elenco senza cancellarla. La scheda "Cestino" in alto compare solo quando contiene qualcosa e da lì si ripristina o si elimina definitivamente. L'eliminazione definitiva e lo svuotamento del cestino cancellano anche gli allegati dal disco, quindi sono irreversibili.

## Allegati e sicurezza

I file finiscono in `/wp-content/uploads/configuratore/AAAA-MM/` con nome randomizzato, e la cartella viene protetta con un `.htaccess` che nega l'accesso diretto. I link nelle email e in bacheca passano invece da un indirizzo firmato che il plugin verifica prima di consegnare il file: funziona senza login, ma un link manomesso viene rifiutato.

**Su Nginx l'`.htaccess` viene ignorato**, quindi la protezione va aggiunta nel vhost:

```nginx
location ~* /wp-content/uploads/configuratore/ {
    deny all;
    return 403;
}
```

Per revocare tutti i link già distribuiti basta rigenerare i sali di WordPress in `wp-config.php`.

Il peso massimo per file è quello impostato, ma non supera mai il limite del server; il peso complessivo è limitato al 90% di quello che il server accetta. Durante il caricamento l'utente vede quanti file ha allegato e quanti MB ha usato, e viene avvisato subito se un file è troppo pesante.

## Accessi

Il plugin non richiede più il ruolo di amministratore.

- **Amministratore**: accesso completo, Domande comprese.
- **Chiunque altro con accesso**: richieste, cestino, esportazione, andamento e impostazioni. La sezione **Domande** resta riservata agli amministratori, così la struttura del percorso non si tocca per sbaglio.

In **Impostazioni → Accessi** (visibile ai soli amministratori) si attiva l'accesso ruolo per ruolo. Da lì si crea anche il ruolo **Gestore configuratore**: chi lo ha entra in bacheca e vede solo il configuratore, senza articoli, pagine, plugin o utenti. È il modo più pulito per dare un accesso dedicato.

## Andamento

**Configuratore → Andamento** risponde alla domanda che il solo archivio non copre: quante persone iniziano e non arrivano in fondo. In cima ci sono i numeri di sintesi su 7, 30 o 90 giorni — iniziate, inviate, tasso di completamento, tempo medio per completare e tempo medio prima di abbandonare — e una riga che indica subito il passaggio più critico.

Poi tre viste: **Quando**, il grafico giorno per giorno con la parte piena delle compilazioni arrivate all'invio; **Dove si fermano**, l'imbuto completo con quante compilazioni raggiungono ciascun passaggio e quante si interrompono proprio lì; **Ultime compilazioni**, l'elenco con ora di inizio, ora dell'ultima attività, passaggio raggiunto, percorso, durata ed esito, filtrabile sui soli abbandoni. Dalle compilazioni inviate si salta direttamente alla richiesta in archivio.

Vengono registrati **solo** il passaggio raggiunto, il percorso e i secondi trascorsi: nessuna risposta, nessun contatto, nessun indirizzo IP. La traccia è legata a una chiave casuale che vive nella scheda del browser e sparisce alla chiusura. Le tracce più vecchie della soglia impostata (180 giorni di predefinito) vengono cancellate ogni notte.

Il tracciamento si disattiva in Impostazioni → Aspetto e antispam.

## Se le email non partono

La richiesta viene salvata **prima** dell'invio, quindi un guasto SMTP non fa mai perdere un contatto. Se `wp_mail()` fallisce, il record viene marcato: nell'elenco compare l'etichetta «email non inviata» e aprendo la richiesta trovi l'errore riportato dal server.

In Impostazioni → Email c'è **Invia un’email di prova**: manda a un indirizzo a scelta entrambe le email con la richiesta di esempio, salvando prima le modifiche in corso. Serve a verificare la consegna; per l'aspetto c'è l'anteprima.

## Da sapere
- Disinstallando il plugin la tabella delle richieste **non** viene cancellata, così un'eventuale reinstallazione ritrova tutto.
- Le vecchie richieste salvate dallo snippet come custom post type non vengono importate: restano nel database ma non sono più visibili.
