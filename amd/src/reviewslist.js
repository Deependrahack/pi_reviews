define([
  "jquery",
  "core/ajax",
  "core/templates",
  "core/notification",
], function ($, ajax, templates, notification) {
  return /** @alias module:block_programs/programs */ {
    /**
     * Load the user programs!
     *
     * @method programs
     */
    reviews: function () {
      // Add a click handler to the button.

      $(document).on("click", ".page-link", function (e) {
        e.preventDefault();
        var page_val = $(this).attr("href");
        var page = getURLParameter("page", page_val);
        var activityname = $(".activity-search").val();
        var courseid = $("#coursesearch_id").find(":selected").val();
        if (courseid === "Course") {
          courseid = "";
        }
        var sort = get_sorting_column();
        var column = sort.column;
        var duesorting = sort.order;
        if (page) {
          var WAITICON = {
            pix: M.util.image_url("i/loading", "core"),
            component: "moodle",
          };
          var loader = $('<img style="display: block;margin: 100px auto" />')
            .attr("src", M.util.image_url(WAITICON.pix, WAITICON.component))
            .addClass("spinner");
          $(".sorted_data").html(
            '<tr> <td colspan="8">' + loader.get(0).outerHTML + "</td></tr>"
          );
          var promises = ajax.call([
            {
              methodname: "block_pi_reviews_get_reviews",
              args: {
                cid: courseid,
                search: activityname,
                column : column,
                page: page,
                duesorting: duesorting,
              },
            },
          ]);
          promises[0]
            .done(function (data) {
              $(".sorted_data").html(data.displayhtml);
              $(".pagination-nav-filter").html(data.pagedata);
            })
            .fail(notification.exception);
        } else {
          return false;
        }
      });

            $(document).on(
                    "keyup",
                    ".activity-search",
                    delay(function (e) {
                        e.preventDefault();
                        var page_val = window.location.href;
                        var page = getURLParameter("page", page_val);
                        var activityname = $(".activity-search").val();
                        var courseid = $("#coursesearch_id").find(":selected").val();
                        if (courseid === "Course") {
                            courseid = "";
                        }
                        var sort = get_sorting_column();
                        var column = sort.column;
                        var duesorting = sort.order;
                            var WAITICON = {
                                pix: M.util.image_url("i/loading", "core"),
                                component: "moodle",
                            };
                            var loader = $('<img style="display: block;margin: 100px auto" />')
                                    .attr("src", M.util.image_url(WAITICON.pix, WAITICON.component))
                                    .addClass("spinner");
                            $(".sorted_data").html(
                                    '<tr> <td colspan="8">' + loader.get(0).outerHTML + "</td></tr>"
                                    );
                            var promises = ajax.call([
                                {
                                    methodname: "block_pi_reviews_get_reviews",
                                    args: {
                                        cid: courseid,
                                        search: activityname,
                                        column: column,
                                        page: page,
                                        duesorting: duesorting,
                                    },
                                },
                            ]);
                            promises[0]
                                    .done(function (data) {
                                        $(".sorted_data").html(data.displayhtml);
                                        $(".pagination-nav-filter").html(data.pagedata);
                                    })
                                    .fail(notification.exception);
                    }, 1000)
                    );
     
            $(document).on(
                    "click",
                    "th",
                    delay(function (e) {
                        e.preventDefault();
                        // Initial sort order (asc by name)
                        var sortOrder = "asc";
                        var column = $(this).data("column");
                        // Toggle sort order
                        if ($(this).hasClass('asc')) {
                            sortOrder = "desc";
                        } else {
                            sortOrder = "asc"; // Default to ascending when changing columns
                        }
                        // Remove existing sort indicators and set for current column
                        $(".table").find("th").removeClass("asc desc");
                        $(this).addClass(sortOrder);
                        var page_val = window.location.href;
                        var courseid = $("#coursesearch_id").find(":selected").val();
                        var activityname = $(".activity-search").val();
                        var state = sortOrder;
                        if (courseid === "Course") {
                            courseid = "";
                        }
                        var page = getURLParameter("page", page_val);
                        var WAITICON = {
                            pix: M.util.image_url("i/loading", "core"),
                            component: "moodle",
                        };
                        var loader = $('<img style="display: block;margin: 100px auto" />')
                                .attr("src", M.util.image_url(WAITICON.pix, WAITICON.component))
                                .addClass("spinner");
                        $(".sorted_data").html(
                                '<tr> <td colspan="8">' + loader.get(0).outerHTML + "</td></tr>"
                                );
                        var promises = ajax.call([
                            {
                                methodname: "block_pi_reviews_get_reviews",
                                args: {
                                    cid: courseid,
                                    search: activityname,
                                    page: 0,
                                    column: column,
                                    duesorting: state,
                                },
                            },
                        ]);
                        promises[0]
                                .done(function (data) {
                                    $(".sorted_data").html(data.displayhtml);
                                    $(".pagination-nav-filter").html(data.pagedata);
                                })
                                .fail(notification.exception);
                    }, 1000)
                    );

        },
    };

  function getURLParameter(name, page_val) {
    return (
      decodeURIComponent(
        (new RegExp("[?|&]" + name + "=" + "([^&;]+?)(&|#|;|$)").exec(
          page_val
        ) || [null, ""])[1].replace(/\+/g, "%20")
      ) || null
    );
  }
  
    function get_sorting_column() {
        // Iterate over each th with class 'sortable' in the table header
        var Column = '';
        var order = '';
        $(".assignment-approval-table thead th.sortable").each(function () {
            var thClass = $(this).attr("class"); // Get class attribute
            var dataColumn = $(this).data("column"); // Get data-column attribute
            if (thClass === "sortable asc" || thClass === "sortable desc") {
                var parts = thClass.split(" ", 2);
                Column = dataColumn;
                order = parts[1];
            }
            
        });
        return {
                column: Column,
                order: order
            };
    }
  //Function for delay the keyup event
  function delay(callback, ms) {
    var timer = 0;
    return function () {
      var context = this,
        args = arguments;
      clearTimeout(timer);
      timer = setTimeout(function () {
        callback.apply(context, args);
      }, ms || 0);
    };
  }
});
