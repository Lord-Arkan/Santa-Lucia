export const toRelativeUrl = (url) => {
    if (!url) return url;

    try {
        const parsed = new URL(url, window.location.origin);
        return `${parsed.pathname}${parsed.search}${parsed.hash}`;
    } catch {
        return url;
    }
};
