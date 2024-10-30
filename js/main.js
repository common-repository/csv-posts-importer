+function ($) {
    "use strict";

    $(document).ready(function () {

        // Display / Hide Post Types Fields.
        $('#post-type').change(function () {
            $('.post-type-block').css('display', 'none');
            $('#' + $(this).val()).css('display', 'block');
            $('input[type="submit"]').removeAttr('disabled');
            select_group_init($(this).val());
        });


        var fieldIdGlobal = 1000;
        $('.btn-add-field').click(function (e) {
            e.preventDefault();
            var postType = $(this).attr('data-post-type');
            var fieldValue = $('#all_fields_' + postType).val();
            var fieldName = $('#all_fields_' + postType + ' option:selected').text();
            if (fieldValue) {
                var clone = $('#' + postType + ' div').first().clone();
                $(clone).find('div').first().html(fieldName);
                $(clone).find('select').attr('name', postType + '[' + fieldValue + ']').attr('id', 'group_' + fieldIdGlobal);
                $(clone).find('input').attr('name', postType + '[def-' + fieldValue + ']').val('');
                $(clone).insertBefore('#' + postType + ' hr');

                fieldIdGlobal++;
                select_group_init(postType);
            }
            disable_post_field(postType, fieldValue);
        });

        $('input[name="create_posts"]').click(function (e) {
            e.preventDefault();
            $(this).attr('disabled', 'true');
            var form = $('form');
            var data = form.serialize();
            form.remove();
            $('.import-progress-block').removeClass('hidden');
            var trimProgressStatus = 0;
            $.ajax({
                url : ajaxurl,
                method : "POST",
                data : data,
                cache : false,
                processData : false,
                xhr : function () {
                    var xhr = $.ajaxSettings.xhr();
                    var xhrPrevText = '';
                    xhr.onprogress = function () {
                        var response = xhr.responseText.substring(xhrPrevText.length);
                        if ($.isNumeric(response)) {
                            trimProgressStatus += response.length;
                            $('.progress').css("width", Math.floor(response) + '%');
                        }
                        xhrPrevText = xhr.responseText;
                    };
                    xhr.onload = function () {
                        $(".progress").css("width", '100%');
                    };
                    return xhr;
                },
                success : function (html) {
                    var data = {'total_posts' : 0, 'post_inserted' : 0};
                    var pos = html.lastIndexOf('{');
                    if (~pos) {
                        try {
                            data = JSON.parse(html.substring(pos));
                        } catch (err) {
                            console.log('json parse error');
                        }
                    } else {
                        console.log('create posts error');
                    }
                    $('.import-progress-bar').remove();
                    var result = $('.result');
                    result.removeClass('hidden');
                    result.find('.total-posts').append(data.total_posts);
                    result.find('.post-inserted').append(data.post_inserted);
                },
                error : function (xhr, str) {
                    $.ajax({
                        url : ajaxurl,
                        method : "POST",
                        data : {action : 'create_posts_error'},
                        success : function (html) {
                            $("#wpbody-content").html(html);
                        }
                    });
                }
            });
        });
    });

    //Disable field in posts select.
    function disable_post_field(postType, fieldValue) {
        if (fieldValue) {
            var select = '#all_fields_' + postType;
            $(select).find('option[value="' + fieldValue + '"]').prop("disabled", true);
            $(select).val('');
        }
    }

    //Grouping selects for post type.
    function select_group_init(postType) {
        var ObjOption = function (value, text) {
            this.value = value || '';
            this.text = text || '';
        };
        var oldValues = {};
        var selects = $('select.group-' + postType);
        selects.each(function (i, item) {
            oldValues[$(item).attr('id')] = new ObjOption();
        });
        selects.change(function () {
            var id = $(this).attr('id');
            if (oldValues[$(this).attr('id')].value !== '') {
                selects.not(this).find('option[value="' + oldValues[id].value + '"]').prop("disabled", false);
            }
            var value = $(this).val();
            if (value !== '') {
                var text = ($(this).find('option[value="' + value + '"]').text());
                selects.not(this).find('option[value="' + value + '"]').prop("disabled", true);
                oldValues[id] = new ObjOption(value, text);
            } else {
                oldValues[id] = new ObjOption('', '');
            }
        });
        selects.change();
    }

}(jQuery);