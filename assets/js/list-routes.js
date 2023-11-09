import '../scss/list-routes.scss';
import '../scss/list-routes-mobile.scss';

function setSelectClass(element) {
    if (element.val() == 0) {
        element.removeClass("not_empty");
    } else {
        element.addClass("not_empty");
    }
}

function setAdvancedFiltersToggleLabel(element) {
    if (element.is(":visible")) {
        $(".advanced-filters-toggle a").addClass('active');
    } else {
        $(".advanced-filters-toggle a").removeClass('active');
    }
}

$(document).ready(function () {
    $("#filter_type, #filter_start_dist, #filter_end_dist").each(function () {
        setSelectClass($(this));
    }).on("change", function () {
        setSelectClass($(this));
    });

    setAdvancedFiltersToggleLabel($(".advanced_filters"));

    $(".filters_toggle").on("click", function () {
        $(".section_filters").slideToggle("fast", function () {
            if ($(this).is(":visible")) {
                $(".filters_toggle .filter_values").hide();
                $(".filters_toggle .label").text('hide filters');
                $(".filters_toggle").addClass("open");
            } else {
                $(".filters_toggle .label").text('filter results');
                $(".filters_toggle .filter_values").show();
                $(".filters_toggle").removeClass("open");
            }
        });
    });

    if ($("#filter_athlete").val()) {
        $(".starred, .private").show();
    }
    $("#filter_athlete").keyup(function () {
        if ($(this).val()) {
            $(".starred, .private").show();
        } else {
            $("#filter_starred, #filter_private").prop('checked', false);
            $(".starred, .private").hide();
        }
    });

    $("#filter_starred").change(function () {
        if (this.checked) {
            $("#filter_private").prop('checked', false);
        }
    });
    $("#filter_private").change(function () {
        if (this.checked) {
            $("#filter_starred").prop('checked', false);
        }
    });

    $(".advanced-filters-toggle").on("click", function () {
        $(".advanced_filters").slideToggle("fast", function () {
            setAdvancedFiltersToggleLabel($(this));
        });
    });
    // http://api.jqueryui.com/autocomplete/
    // https://jqueryui.com/autocomplete/#custom-data
    $('#filter_start')
        .autocomplete({
            source: "/routes/autocomplete/location",
            minLength: 3,
            delay: 250,
            appendTo: $(this).attr("id"),
            change: function (event, ui) {
                var name = $(this).attr("id");
                if (ui.item === null) {
                    $("#" + name + "_latlon").val(null);
                }
            },
            search: function (event, ui) {
                var name = $(this).attr("id");
                $("#" + name).parent().find('.geolocate').attr('src', "images/spinner.gif").addClass("clicked");
            },
            response: function (event, ui) {
                $("img.geolocate.clicked").attr("src", "images/icon-geolocate.svg").removeClass("clicked");
            },
            select: function (event, ui) {
                var name = $(this).attr("id");
                $("#" + name).val(ui.item.name + ", " + ui.item.city + ", " + ui.item.country);
                $("#" + name + "_latlon").val(ui.item.latitude + "," + ui.item.longitude);
                return false;
            }
        })
        .autocomplete("instance")._renderItem = function (ul, item) {
            let address = item.country;
            if (item.city && item.city != item.country) {
                address = `${item.city}, ${address}`;
            }
            return $("<li>")
                .append(`<div class="name">${item.name}</div>`)
                .append(`<div class="details"><span class="class">${item.class}</span>, <span class="address">${address}</span></div>`)
                .appendTo(ul);
        };
    $('#filter_end')
        .autocomplete({
            source: "/routes/autocomplete/location",
            minLength: 3,
            delay: 250,
            appendTo: $(this).attr("id"),
            change: function (event, ui) {
                var name = $(this).attr("id");
                if (ui.item === null) {
                    $("#" + name + "_latlon").val(null);
                }
            },
            search: function (event, ui) {
                var name = $(this).attr("id");
                $("#" + name).parent().find('.geolocate').attr('src', "images/spinner.gif").addClass("clicked");
            },
            response: function (event, ui) {
                $("img.geolocate.clicked").attr("src", "images/icon-geolocate.svg").removeClass("clicked");
            },
            select: function (event, ui) {
                var name = $(this).attr("id");
                $("#" + name).val(ui.item.name + ", " + ui.item.city + ", " + ui.item.country);
                $("#" + name + "_latlon").val(ui.item.latitude + "," + ui.item.longitude);
                return false;
            }
        })
        .autocomplete("instance")._renderItem = function (ul, item) {
            let address = item.country;
            if (item.city && item.city != item.country) {
                address = `${item.city}, ${address}`;
            }
            return $("<li>")
                .append(`<div class="name">${item.name}</div>`)
                .append(`<div class="details"><span class="class">${item.class}</span>, <span class="address">${address}</span></div>`)
                .appendTo(ul);
        };

    // Geolocation for route "Start" and "End" filters.
    if (navigator.geolocation) {
        $('.geolocate').attr('style', '');
    }
    $('.geolocate').on("click", function () {
        $(this).attr('src', "images/spinner.gif").addClass("clicked");
        var options = {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
        };
        navigator.geolocation.getCurrentPosition(handlePosition, geolocationError, options);
    });

    function handlePosition(position, target) {
        $.ajax({
            type: "GET",
            url: "/routes/autocomplete/reverse?lat=" + position.coords.latitude + "&lon=" + position.coords.longitude,
            timeout: 5000,
            success: function (data) {
                var target = $("img.geolocate.clicked").attr("data-target");
                $("img.geolocate.clicked").attr("src", "images/icon-geolocate.svg").removeClass("clicked");
                $("#filter_" + target).val(data.name + ", " + data.city + ", " + data.country);
                $("#filter_" + target + "_latlon").val(data.latitude + "," + data.longitude);
            },
            error: function (data) {
                $("img.geolocate.clicked").attr("src", "images/icon-geolocate.svg").removeClass("clicked");
            }
        });
    }

    function geolocationError(err) {
        $("img.geolocate.clicked").attr("src", "images/icon-geolocate.svg").removeClass("clicked");
        console.warn(err);
    }
});