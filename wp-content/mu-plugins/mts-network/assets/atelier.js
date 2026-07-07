/* Atelier terminologique — app d'admin minimale (fetch + polling). */
( function () {
	const app = document.getElementById( 'mts-atelier-app' );
	if ( ! app ) {
		return;
	}
	const rest = app.dataset.rest;
	const nonce = app.dataset.nonce;
	const tbody = document.querySelector( '#mts-candidates tbody' );
	const progress = document.getElementById( 'mts-progress' );
	let pollTimer = null;

	function api( path, options = {} ) {
		options.headers = Object.assign( { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' }, options.headers || {} );
		return fetch( rest + path, options ).then( ( r ) => r.json().then( ( data ) => ( { ok: r.ok, data } ) ) );
	}

	function esc( s ) {
		const d = document.createElement( 'span' );
		d.textContent = s == null ? '' : String( s );
		return d.innerHTML;
	}

	function badge( c ) {
		if ( c.status !== 'open' ) {
			return '<em>' + esc( c.status ) + '</em>';
		}
		if ( c.central_match ) {
			return '✅ ' + esc( c.match_type );
		}
		return '🆕 new';
	}

	function actions( c ) {
		if ( c.status !== 'open' ) {
			return '';
		}
		let html = '';
		if ( c.central_match ) {
			html += '<button class="button button-primary" data-act="approve_link" data-id="' + c.id + '">Link</button> ';
		} else {
			html += '<button class="button button-primary" data-act="create_term" data-id="' + c.id + '">Propose central term</button> ';
		}
		html += '<button class="button" data-act="reject" data-id="' + c.id + '">Reject</button>';
		return html;
	}

	function render( rows ) {
		if ( ! rows || ! rows.length ) {
			tbody.innerHTML = '<tr><td colspan="7">No candidates.</td></tr>';
			return;
		}
		tbody.innerHTML = rows.map( ( c ) => '<tr>' +
			'<td style="font-size:1.3em;">' + esc( c.tibetan ) + '</td>' +
			'<td>' + esc( c.wylie ) + '</td>' +
			'<td><strong>' + esc( c.target ) + '</strong> <small>(' + esc( c.target_lang ) + ')</small></td>' +
			'<td><small>' + esc( ( c.context_source || '' ).slice( 0, 80 ) ) + '<br>' + esc( ( c.context_target || '' ).slice( 0, 80 ) ) + '</small></td>' +
			'<td>' + esc( c.confidence ) + ( parseInt( c.source_anchor, 10 ) ? ' ⚓' : '' ) + '</td>' +
			'<td>' + badge( c ) + '</td>' +
			'<td>' + actions( c ) + '</td>' +
		'</tr>' ).join( '' );
	}

	function poll( jobId ) {
		api( '/jobs/' + jobId ).then( ( { ok, data } ) => {
			if ( ! ok ) {
				progress.textContent = 'Error: ' + ( data.message || 'unknown' );
				return;
			}
			progress.textContent = data.status + ' — ' + data.chunks_done + '/' + data.chunks_total + ' chunks, ' + data.candidates + ' candidates';
			render( data.candidates_rows );
			if ( data.status === 'queued' || data.status === 'running' ) {
				pollTimer = setTimeout( () => poll( jobId ), 2000 );
			}
		} );
	}

	document.getElementById( 'mts-extract' ).addEventListener( 'click', () => {
		clearTimeout( pollTimer );
		progress.textContent = 'Submitting…';
		api( '/extract', {
			method: 'POST',
			body: JSON.stringify( {
				source: document.getElementById( 'mts-source' ).value,
				target: document.getElementById( 'mts-target' ).value,
				translation_id: parseInt( document.getElementById( 'mts-translation' ).value, 10 ) || 0,
				target_lang: app.dataset.lang,
			} ),
		} ).then( ( { ok, data } ) => {
			if ( ! ok ) {
				progress.textContent = 'Error: ' + ( data.message || 'unknown' );
				return;
			}
			poll( data.job_id );
		} );
	} );

	tbody.addEventListener( 'click', ( e ) => {
		const btn = e.target.closest( 'button[data-act]' );
		if ( ! btn ) {
			return;
		}
		btn.disabled = true;
		api( '/candidates/' + btn.dataset.id + '/action', {
			method: 'POST',
			body: JSON.stringify( { action: btn.dataset.act } ),
		} ).then( ( { ok, data } ) => {
			const cell = btn.closest( 'td' );
			const row = btn.closest( 'tr' );
			if ( ok ) {
				row.querySelector( 'td:nth-child(6)' ).innerHTML = '<em>' + esc( data.status ) + '</em>';
				cell.innerHTML = '';
			} else {
				cell.innerHTML = '<span style="color:#b32d2e;">' + esc( data.message || 'error' ) + '</span>';
			}
		} );
	} );
}() );
