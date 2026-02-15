document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('searchPanelToggle');
    const body = document.getElementById('searchPanelBody');
    const arrow = document.getElementById('searchPanelArrow');

    if (!toggle || !body || !arrow) return;

    // Auto-open if search params exist in URL
    const params = new URLSearchParams(window.location.search);
    let hasSearchParams = false;
    for (const [key, value] of params.entries()) {
        if (key !== 'page' && value !== '') {
            hasSearchParams = true;
            break;
        }
    }

    if (hasSearchParams) {
        body.style.display = 'block';
        arrow.textContent = '▼';
    }

    toggle.addEventListener('click', function() {
        const isHidden = body.style.display === 'none';
        body.style.display = isHidden ? 'block' : 'none';
        arrow.textContent = isHidden ? '▼' : '▶';
    });
});

function resetSearch(baseUrl) {
    window.location.href = baseUrl;
}
