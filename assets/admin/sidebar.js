const { registerPlugin } = wp.plugins;
const { PluginDocumentSettingPanel } = wp.editPost?.PluginDocumentSettingPanel
	? wp.editPost
	: wp.editor;
const { Button } = wp.components;
const { createElement: el, useState, useEffect } = wp.element;
const { useSelect } = wp.data;
const { __ } = wp.i18n;

const WbitlySidebar = () => {
	const [url, setUrl] = useState(wbitlyPostData.shortUrl || null);
	const [loading, setLoading] = useState(false);

	const postType = useSelect(
		(select) => select("core/editor").getCurrentPostType(),
		[],
	);
	const postId = useSelect(
		(select) => select("core/editor").getCurrentPostId(),
		[],
	);
	const postStatus = useSelect(
		(select) => select("core/editor").getEditedPostAttribute("status"),
		[],
	);
	const isSaving = useSelect(
		(select) => select("core/editor").isSavingPost(),
		[],
	);

	useEffect(() => {
		if (postType !== "post") return;
		if (!postId || postStatus !== "publish" || isSaving) return;
		const fetchUrl = `/wbitly/v1/meta/${postId}`;
		setLoading(true);
		wp.apiFetch({
			path: fetchUrl,
			method: "GET",
			headers: {
				"X-WP-Nonce": wbitlyPostData.nonce,
			},
		})
			.then((res) => {
				if (res?.short_url) {
					setUrl(res.short_url);
				} else {
					setUrl(null);
				}
			})
			.catch(() => alert(__('Failed to fetch Bitly short URL', 'wbitly')))
			.finally(() => setLoading(false));
	}, [postId, postStatus, isSaving, postType]);

	if (postType !== "post") return null;

	const handleGenerate = () => {
		setLoading(true);
		wp.apiFetch({
			path: `/wbitly/v1/generate/${postId}`,
			method: "POST",
			headers: {
				"Content-Type": "application/json",
				"X-WP-Nonce": wbitlyPostData.nonce,
			},
		})
			.then((res) => setUrl(res.short_url))
			.catch(() => alert(__('Failed to generate Bitly URL', 'wbitly')))
			.finally(() => setLoading(false));
	};

	const renderContent = () => {
		if (!wbitlyPostData.accessToken || !wbitlyPostData.groupGuid) {
			return el(
				"p",
				null,
				el(
					"a",
					{ href: wbitlyPostData.settingsLink },
					__('Setup Bitly API in Wbitly settings', 'wbitly'),
				),
			);
		}

		if (postStatus !== "publish") {
			return el("p", null, __('Publish to generate Bitly URL', 'wbitly'));
		}

		if (url) {
			return el(
				"div",
				{ className: "wbitly-url-share" },
				el(
					"p",
					null,
					el(
						"a",
						{ href: url, target: "_blank", rel: "noopener noreferrer" },
						url,
					),
				),
				el("div", { className: "wbitly-social-icons" }, [
					el("a", {
						href: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(
							url,
						)}`,
						target: "_blank",
						rel: "noopener noreferrer",
						className: "wbitly-icon wbitly-icon-facebook",
						title: __('Share on Facebook', 'wbitly'),
					}),

					el("a", {
						href: `mailto:?subject=${encodeURIComponent(__('Check this out', 'wbitly'))}&body=${encodeURIComponent(
							url,
						)}`,
						className: "wbitly-icon wbitly-icon-email",
						title: __('Share via Email', 'wbitly'),
					}),

					el("a", {
						href: `https://twitter.com/intent/tweet?url=${encodeURIComponent(
							url,
						)}`,
						target: "_blank",
						rel: "noopener noreferrer",
						className: "wbitly-icon wbitly-icon-x",
						title: __('Share on X (Twitter)', 'wbitly'),
					}),

					el(
						"button",
						{
							onClick: () => {
								if (
									navigator.clipboard &&
									typeof navigator.clipboard.writeText === "function"
								) {
									navigator.clipboard
										.writeText(url)
										.then(() =>
											alert(__('URL copied to clipboard!', 'wbitly')),
										)
										.catch((err) => {
											console.error("Clipboard copy failed", err);
											alert(__('Copy failed. Try manually.', 'wbitly'));
										});
								} else {
									const textarea = document.createElement("textarea");
									textarea.value = url;
									document.body.appendChild(textarea);
									textarea.select();
									try {
										document.execCommand("copy");
										alert(__('URL copied to clipboard!', 'wbitly'));
									} catch (err) {
										alert(__('Copy not supported', 'wbitly'));
									}
									document.body.removeChild(textarea);
								}
							},
							className: "wbitly-icon wbitly-icon-copy",
							title: __('Copy URL', 'wbitly'),
							style: {
								background: "none",
								border: "none",
								cursor: "pointer",
								padding: 0,
							},
						},
						" ",
					),
				]),
			);
		}

		return el(
			Button,
			{
				isPrimary: true,
				isBusy: loading,
				disabled: loading,
				onClick: handleGenerate,
			},
			__('Generate Bitly URL', 'wbitly'),
		);
	};

	return el(
		PluginDocumentSettingPanel,
		{
			name: "wbitly-sidebar",
			title: __('Bitly Short URL', 'wbitly'),
			icon: "admin-links",
			className: "wbitly-sidebar",
		},
		renderContent(),
	);
};

registerPlugin("wbitly-sidebar", {
	render: WbitlySidebar,
	icon: "admin-links",
});
