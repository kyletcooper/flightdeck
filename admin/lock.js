(() => {

	console.log('FlightDeck: Locking changes to posts, terms & media.')

	const lockedSelectors = [
		'.page-title-action',
		'.row-actions',
		'.edit-tags-php #submit',
		'.edit-tag-actions',
		'.plupload-upload-ui',
		'.upload-php .page-title-action',
	];

	lockedSelectors.forEach(selector => {
		const btns = document.querySelectorAll(selector);

		btns.forEach(btn => {
			btn.style.opacity = '0.5';
			btn.style.filter = 'grayscale(1)';
			btn.style.pointerEvents = 'none';
		})
	})

})();