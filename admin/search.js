document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('globalSearchInput');
    const resultsContainer = document.getElementById('globalSearchResults');

    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const query = this.value.trim();

            if (query.length > 1) { // Only search if more than 1 character
                const formData = new FormData();
                formData.append('query', query);

                fetch('search_query.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    resultsContainer.innerHTML = '';
                    
                    if (data.length > 0) {
                        resultsContainer.style.display = 'block';
                        
                        data.forEach(item => {
                            // Determine Icon based on type
                            let icon = 'mdi:account';
                            if(item.type === 'Book') icon = 'mdi:book-open-variant';
                            if(item.type === 'Admin') icon = 'clarity:administrator-solid';

                            const link = `${item.link}?search=${encodeURIComponent(item.id_val)}`; // Pass ID to page

                            const html = `
                                <a href="${link}" class="search-result-item">
                                    <div class="result-icon">
                                        <iconify-icon icon="${icon}"></iconify-icon>
                                    </div>
                                    <div class="result-info">
                                        <h4>${item.firstname} ${item.lastname}</h4>
                                        <span>${item.id_val}</span>
                                    </div>
                                    <span class="type-badge badge-${item.type}">${item.type}</span>
                                </a>
                            `;
                            resultsContainer.innerHTML += html;
                        });
                    } else {
                        resultsContainer.style.display = 'block';
                        resultsContainer.innerHTML = '<div class="search-result-item" style="cursor:default; color:#888;">No results found</div>';
                    }
                })
                .catch(error => console.error('Error:', error));
            } else {
                resultsContainer.style.display = 'none';
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                resultsContainer.style.display = 'none';
            }
        });
    }
});