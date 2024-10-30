+function ($) {
    "use strict";

    $(document).ready(function () {
        var timer = setInterval(function () {
            $.ajax({
                url : ajaxurl,
                method : "POST",
                data : {'action' : 'get_import_progress'},
                success : function (data) {
                    if (data) {
                        var progressData = JSON.parse(data);
                        if (progressData.curr_percent == '100') {
                            clearInterval(timer);
                            $('.import-progress-bar').remove();
                            var result = $('.result');
                            result.removeClass('hidden');
                            result.find('.total-posts').html(progressData.total_posts);
                            result.find('.post-inserted').html(progressData.post_inserted);
                        }
                        $('.progress').css('width', progressData.curr_percent + '%');
                    } else {
                        clearInterval(timer);
                    }
                }
            });
        }, 2000);
    });
}(jQuery);
