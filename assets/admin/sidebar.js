const { registerPlugin } = wp.plugins;
const { PluginDocumentSettingPanel } = wp.editPost;
const { Button } = wp.components;
const { createElement: el, useState, useEffect } = wp.element;
const { useSelect } = wp.data;

const WbitlySidebar = () => {
	const [url, setUrl] = useState(wbitlyData.shortUrl || null);
	const [loading, setLoading] = useState(false);

	const postType = useSelect((select) => select('core/editor').getCurrentPostType(), []);
	const postId = useSelect((select) => select('core/editor').getCurrentPostId(), []);
	const postStatus = useSelect((select) => select('core/editor').getEditedPostAttribute('status'), []);
	const isSaving = useSelect((select) => select('core/editor').isSavingPost(), []);

	useEffect(() => {
		if (!postId || postStatus !== 'publish' || isSaving) return;

		setLoading(true);
		wp.apiFetch({ path: `/wbitly/v1/meta/${postId}` })
			.then((res) => {
				if (res?.short_url) {
					setUrl(res.short_url);
				} else {
					setUrl(null);
				}
			})
			.catch(() => alert('Failed to fetch Bitly short URL'))
			.finally(() => setLoading(false));
	}, [postId, postStatus, isSaving]);

	if (postType !== 'post') return null;

	const handleGenerate = () => {
		setLoading(true);
		wp.apiFetch({
			path: `/wbitly/v1/generate/${postId}`,
			method: 'POST',
		})
			.then((res) => setUrl(res.short_url))
			.catch(() => alert('Failed to generate Bitly URL'))
			.finally(() => setLoading(false));
	};

	const renderContent = () => {
		if (!wbitlyData.accessToken || !wbitlyData.groupGuid) {
			return el('p', null,
				el('a', { href: wbitlyData.settingsLink }, 'Setup Bitly API in Wbitly settings')
			);
		}

		if (postStatus !== 'publish') {
			return el('p', null, 'Publish to generate Bitly URL');
		}

		if (url) {
			return el('p', null,
				el('a', { href: url, target: '_blank', rel: 'noopener noreferrer' }, url)
			);
		}

		return el(Button, {
			isPrimary: true,
			isBusy: loading,
			disabled: loading,
			onClick: handleGenerate,
		}, 'Generate Bitly URL');
	};

	return el(
		PluginDocumentSettingPanel,
		{
			name: 'wbitly-sidebar',
			title: 'Bitly Short URL',
			icon: 'admin-links',
			className: 'wbitly-sidebar',
		},
		renderContent()
	);
};

registerPlugin('wbitly-sidebar', {
	render: WbitlySidebar,
	icon: 'admin-links',
});
