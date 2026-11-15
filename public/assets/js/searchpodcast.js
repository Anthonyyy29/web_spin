let groupedSuggestions = {};

function fetchPodcastData(callback) {
    $.ajax({
        url: BASE_URL + 'api/podcastlist',
        method: 'GET',
        headers: {
            'X-API-TOKEN': ""
        },
        success: function (data) {
            groupedSuggestions = data;
            if (callback) callback();
        },
        error: function (xhr) {
            console.error("Request failed:", xhr);
        }
    });
}

$(document).ready(function () {
    fetchPodcastData(function () {
        $('#searchInput').on('input', function () {
            const value = $(this).val().toLowerCase();
            const $box = $('#suggestionBox');
            $box.empty();

            if (!value) {
                $box.addClass('d-none');
                return;
            }

            let found = false;

            for (const [segment, items] of Object.entries(groupedSuggestions)) {
                const filtered = items.filter(item => item.label.toLowerCase().includes(value));

                if (filtered.length > 0) {
                    found = true;
                    $box.append(`<div class="list-group-item active bg-secondary px-2 py-1">${segment}</div>`);
                    filtered.forEach(item => {
                        const $item = $('<a>')
                            .addClass('list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-1')
                            .attr('href', BASE_URL + item.url);

                        const $label = $('<span>').text(item.label);

                        $item.append($label);
                        $box.append($item);
                    });
                }
            }


            $box.toggleClass('d-none', !found);
        });
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('.position-relative').length) {
            $('#suggestionBox').addClass('d-none');
        }
    });
});