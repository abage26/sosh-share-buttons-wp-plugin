jQuery(function($){
    //alert(transObj.x_url);
    //$('[data-slug="social-share"] span[class="deactivate"] a').addClass('freee');
    $('[data-slug="social-share"] span[class="deactivate"] a').click(function(e){
        e.preventDefault();
        var urlRedirect = $('[data-slug="social-share"] span[class="deactivate"] a').attr('href');

        if (confirm('Les données sauvégardées seront supprimées. Appuyer "Cancel" pour conserver. ')) {
            removeData();
        }
        else{
            window.location.href = urlRedirect;
        }
    });

    var removeData = function(){
        $.ajax({
            type: 'POST',
            url: transObj.x_url,
            cache:false,
            data: {
                'action': 'sosh_uninstal_action',
                'sosh_deactivate_remove_data': true
            },
            success: function (response) {
                console.log(response);
            }
        });
        window.location.href = $('[data-slug="social-share"] span[class="deactivate"] a').attr('href');
    };
    /*$('<div></div>').appendTo('body')
        .html('<div><h6>Yes or No?</h6></div>')
        .dialog({
            modal: true,
            title: 'message',
            zIndex: 10000,
            autoOpen: true,
            width: 'auto',
            resizable: false,
            buttons: {
                Yes: function () {
                    doFunctionForYes();
                    $(this).dialog("close");
                },
                No: function () {
                    doFunctionForNo();
                    $(this).dialog("close");
                }
            },
            close: function (event, ui) {
                $(this).remove();
            }
        });
    $('#msg').hide();

    function doFunctionForYes() {
        alert("Yes");
        $('#msg').show();
    }

    function doFunctionForNo() {
        alert("No");
        $('#msg').show();
    }*/
});