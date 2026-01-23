document.addEventListener('DOMContentLoaded', () => {
	// Handle Copy Button
	const handleCopyClick = (event) => {
		const url = event.currentTarget.getAttribute('data-copy-text');
		if (!url) return;

		if (navigator.clipboard) {
			navigator.clipboard.writeText(url)
				.then(() => alert('URL copied to clipboard!'))
				.catch(() => alert('Copy failed'));
		} else {
			// Fallback for older browsers
			const textarea = document.createElement('textarea');
			textarea.value = url;
			document.body.appendChild(textarea);
			textarea.select();
			try {
				document.execCommand('copy');
				alert('URL copied to clipboard!');
			} catch {
				alert('Copy not supported');
			}
			document.body.removeChild(textarea);
		}
	};

	// Handle Generate URL Button
	const handleGenerateClick = (event) => {
		const btn = event.currentTarget;
		const postId = btn.getAttribute('data-post-id');
		if (!postId) return;

		btn.disabled = true;
		btn.textContent = 'Generating...';

		fetch(`${wbitlyData.rest_url}wbitly/v1/generate/${postId}`, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': wbitlyData.nonce
			},
			body: JSON.stringify({})
		})
		.then(res => res.json())
		.then(data => {
			if (data?.share_block) {
				// Security: share_block is HTML from server-side template (already escaped).
				// Using innerHTML is safe here as content is sanitized on server.
				btn.parentElement.innerHTML = data.share_block;
			} else {
				btn.textContent = 'Failed';
				alert(data?.message || 'Error generating URL');
			}
		})
		.catch(error => {
			console.error(error);
			btn.textContent = 'Error';
			alert('Request failed.');
		});
	};

	// Bind all buttons
	document.querySelectorAll('.wbitly-icon-copy')
		.forEach(button => button.addEventListener('click', handleCopyClick));

	document.querySelectorAll('.wbitly-generate-url')
		.forEach(button => button.addEventListener('click', handleGenerateClick));
});
