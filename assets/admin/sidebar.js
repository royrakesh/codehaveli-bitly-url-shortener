const { registerPlugin } = wp.plugins;
const { PluginDocumentSettingPanel } = wp.editPost?.PluginDocumentSettingPanel
	? wp.editPost
	: wp.editor;
const { Button } = wp.components;
const { createElement: el, useState, useEffect } = wp.element;
const { useSelect } = wp.data;

const WbitlySidebar = () => {
	const [url, setUrl] = useState(wbitlyData.shortUrl || null);
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
				"X-WP-Nonce": wbitlyData.nonce,
			},
		})
			.then((res) => {
				if (res?.short_url) {
					setUrl(res.short_url);
				} else {
					setUrl(null);
				}
			})
			.catch(() => alert("Failed to fetch Bitly short URL"))
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
				"X-WP-Nonce": wbitlyData.nonce,
			},
		})
			.then((res) => setUrl(res.short_url))
			.catch(() => alert("Failed to generate Bitly URL"))
			.finally(() => setLoading(false));
	};

	const renderContent = () => {
		if (!wbitlyData.accessToken || !wbitlyData.groupGuid) {
			return el(
				"p",
				null,
				el(
					"a",
					{ href: wbitlyData.settingsLink },
					"Setup Bitly API in Wbitly settings",
				),
			);
		}

		if (postStatus !== "publish") {
			return el("p", null, "Publish to generate Bitly URL");
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
						title: "Share on Facebook",
					}),

					el("a", {
						href: `mailto:?subject=Check%20this%20out&body=${encodeURIComponent(
							url,
						)}`,
						className: "wbitly-icon wbitly-icon-email",
						title: "Share via Email",
					}),

					el("a", {
						href: `https://twitter.com/intent/tweet?url=${encodeURIComponent(
							url,
						)}`,
						target: "_blank",
						rel: "noopener noreferrer",
						className: "wbitly-icon wbitly-icon-x",
						title: "Share on X (Twitter)",
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
										.then(() => alert("URL copied to clipboard!"))
										.catch((err) => {
											console.error("Clipboard copy failed", err);
											alert("Copy failed. Try manually.");
										});
								} else {
									// Fallback for older/HTTP environments
									const textarea = document.createElement("textarea");
									textarea.value = url;
									document.body.appendChild(textarea);
									textarea.select();
									try {
										document.execCommand("copy");
										alert("URL copied to clipboard!");
									} catch (err) {
										alert("Copy not supported");
									}
									document.body.removeChild(textarea);
								}
							},
							className: "wbitly-icon wbitly-icon-copy",
							title: "Copy URL",
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
			"Generate Bitly URL",
		);
	};

	return el(
		PluginDocumentSettingPanel,
		{
			name: "wbitly-sidebar",
			title: "Bitly Short URL",
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
