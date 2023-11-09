import '../scss/manage-my-routes.scss';
import '../scss/manage-my-routes-mobile.scss';

function setAllCheckboxes() {
    var total = $('input[name="manage_routes[route][]"]').length;
    var checked = $('input[name="manage_routes[route][]"]:checked').length;
    if (checked == total) {
        $('.select_all').prop("indeterminate", false);
        $('.select_all').prop("checked", true);
    } else if (checked == 0) {
        $('.select_all').prop("indeterminate", false);
        $('.select_all').prop("checked", false);
    } else {
        $('.select_all').prop("checked", false);
        $('.select_all').prop("indeterminate", true);
    }
}

$(document).ready(function () {
    setAllCheckboxes();

    $(".select_all").on('click', function () {
        var state = this.checked;
        $('input[name="manage_routes[route][]"]').each(function () {
            this.checked = state;
        });
        setAllCheckboxes();
    });

    $(".row").on("click", function (e) {
        if (e.target.type !== 'checkbox' && e.target.attributes.class.value !== 'strava-link') {
            var checkbox = $(this).find("input");
            checkbox.prop("checked", !checkbox.prop("checked"));
            setAllCheckboxes();
        }
    });
});
