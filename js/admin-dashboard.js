// Fetch referees and populate the referee select box on the matches tab
function populateRefereeSelect() {
    const refereeSelect = document.getElementById('matchReferee');
    if (!refereeSelect) return;
    // Clear existing options except the placeholder
    refereeSelect.innerHTML = '<option value="" disabled selected hidden>Select Referee</option>';
    jQuery.ajax({
        url: your_plugin_ajax_object.ajax_url,
        type: 'POST',
        data: {
            action: 'your_plugin_fetch_referees',
            nonce: your_plugin_ajax_object.nonce
        },
        success: function(response) {
            if (response.success && Array.isArray(response.data)) {
                response.data.forEach(function(ref) {
                    const option = document.createElement('option');
                    option.value = ref.id;
                    option.textContent = ref.name;
                    refereeSelect.appendChild(option);
                });
            }
        },
        error: function() {
            // Optionally show an error or fallback
        }
    });
}

// Call this function when the matches tab is shown or on page load
// Example: populateRefereeSelect();

document.addEventListener('DOMContentLoaded', function() {
    populateRefereeSelect();
});
