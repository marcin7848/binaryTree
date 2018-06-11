$(document).ready(function () {
    var click = 0;
    var pos_x = 0;
    var pos_y = 0;
    var html = '';
    var a = 0;
    var out = [0, 0, 0, 0];
    var value = '';
    var d = 0;

    $(".movenode").mousedown(function () {
        a = $(this).attr("id");
        var b = $(this).position();
        if (click == 0) {
            pos_x = b.left;
            pox_y = b.top;
        }
        click = 1;

        $(document).mousemove(function (e) {
            if (click == 1) {
                $("#" + a).css({left: e.pageX - b.left - 40 - $(window).width() * 0.4, top: e.pageY - b.top - 10});
            }
        });

    });

    $(".moveadd").mousedown(function () {
        a = $(this).attr("id");
        var b = $(this).position();
        if (click == 0) {
            pos_x = b.left;
            pox_y = b.top;
        }
        click = 1;

        $(document).mousemove(function (e) {
            if (click == 1) {
                $("#" + a).css({left: e.pageX - b.left, top: e.pageY - b.top - 10});
            }
        });

    });

    $(document).mouseup(function (e) {
        if (click == 1) {
            $("#" + a).css({left: pos_x, top: pos_y});
            d = -2;

            find_node(d, e)

            setTimeout(function () {
                if(d!= -2)
                {
                    $.ajax({
                        url: "",
                        context: document.body,
                        success: function (s, x) {
                            $(this).html(s);
                        }
                    });
                }
            }, 100);



            click = 0;
        }
    });

    $("#add_value").keypress(function(event){
        $('#error_add').text("");
        var ew = event.which;
        if(48 <= ew && ew <= 57)
            return true;
        if(65 <= ew && ew <= 90)
            return true;
        if(97 <= ew && ew <= 122)
            return true;
        $("#error_add").append("Tylko litery lub cyfry!");
        return false;
    });

    $("#newvalue").keypress(function(event){
        $('#error_edit').text("");
        var ew = event.which;
        if(48 <= ew && ew <= 57)
            return true;
        if(65 <= ew && ew <= 90)
            return true;
        if(97 <= ew && ew <= 122)
            return true;
        $("#error_edit").append("Tylko litery lub cyfry!");
        return false;
    });

    $(function(){
        $('[id^=mo_]').click(function(){
            $('#tg_'+this.id).slideToggle(200);
            return false});});

        $(function(){

        $('[id^=sort_]').click(function(){

            var data = new FormData();
            data.append('parent_id', $('#'+this.id).attr("value"));
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'index.php?sort', true);
            xhr.send(data);
            xhr.onload = function () {
                $.ajax({
                    url: "",
                    context: document.body,
                    success: function (s, x) {
                        $(this).html(s);
                    }
                });

            };
            return false});});

    $(function(){
        $('#add_node2').click(function(){
            $('#value_node_add').text("");

            //tutaj zrobić validacje danych

            $('#value_node_add').append($('#add_value').val());
                value = $('#value_node_add').text();
            return false});});

    function find_node(d, e) {
        var pos = $("#" + d).position();

        if ($("#" + d).height() === null) {
            d++;
            setTimeout(function () {
                find_node(d, e);
            }, 0);
        }
        else {
            if (e.pageY > pos.top && e.pageY < pos.top + $("#" + d).height()) {

                if(d > -2) {
                    var pod = 0;

                    if (e.pageY > ((pos.top + $("#" + d).height() / 2))) {
                        pod = 1;
                    }

                    var data = new FormData();

                    data.append('id', a);
                    data.append('move_access', 1);
                    data.append('move_id', d);
                    data.append('set_pos_under', pod);
                    data.append('value', value);

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'index.php?move', true);
                    xhr.send(data);
                    xhr.onload = function () {
                        $.ajax({
                            url: "",
                            context: document.body,
                            success: function (s, x) {
                                $(this).html(s);
                            }
                        });

                    };
                }
                else
                {
                    $(".editshow").css("display", "inline");
                    $("#id_newvalue").val($("#" + a).attr("id"));
                    $("#newvalue").val($("#" + a).attr("value"));
                }
            }
            else {
                d++;
                setTimeout(function () {
                    find_node(d, e);
                }, 0);
            }
        }
    }

});
