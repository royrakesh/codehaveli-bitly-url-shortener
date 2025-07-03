const { registerPlugin } = wp.plugins;
const { PluginDocumentSettingPanel } = wp.editPost;
const { PanelBody, Button } = wp.components;
const { createElement: el, useState } = wp.element;
const { useSelect } = wp.data;

const WbitlySidebar = () => {
	const [url, setUrl] = useState(wbitlyData.shortUrl);
	const [loading, setLoading] = useState(false);

	const postType = useSelect((select) =>
		select('core/editor').getCurrentPostType()
	);

	if (postType !== 'post') return null;

	const handleGenerate = () => {
			setLoading(true);
			wp.apiFetch({
				path: `/wbitly/v1/generate/${wbitlyData.postId}`,
				method: 'POST',
			})
			.then((res) => setUrl(res.short_url))
			.catch(() => alert('Failed to generate Bitly URL'))
			.finally(() => setLoading(false));
	};

	return el(
		PluginDocumentSettingPanel,
		{
			name: 'wbitly-sidebar',
			title: 'Bitly Short URL',
			icon: 'admin-links',
			className: 'wbitly-sidebar',
		},
		!wbitlyData.accessToken || !wbitlyData.groupGuid
			? el('p', null,
				el('a', { href: wbitlyData.settingsLink }, 'Setup Bitly API in Wbitly settings')
			)
			: !wbitlyData.isPublished
				? el('p', null, 'Publish to generate Bitly URL')
				: url
					? el('p', null,
						el('a', { href: url, target: '_blank', rel: 'noopener noreferrer' }, url)
					)
					: el(Button, {
						isPrimary: true,
						isBusy: loading,
						onClick: handleGenerate,
					}, 'Generate Bitly URL')
	);
};

registerPlugin('wbitly-sidebar', {
	render: WbitlySidebar,
	icon: 'admin-links',
});
