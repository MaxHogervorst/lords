$( document ).ready(function() 
 {
     filter('#filter', '#members');


     $('#member-order').on('hidden.bs.modal', function ()
     {
         $("#filter, #lastname").val("");
         $("#filter").focus();
         $('tbody tr').removeClass('visible').show().addClass('visible').css({display: 'table-row'});
     });

     $('input[type=search]')
         .wrap('<span>')
         .after('<span class="clear">&#x2715;</span>')
         .parent()
         .on('mouseenter mouseleave', function(e){
             $(this).toggleClass('focused');
         })
         .find('span.clear')
         .on('click', function(e){
             $(this).parent().find('input').val('');
             $('tbody tr').removeClass('visible').show().addClass('visible').css({display: 'table-row'});
         });



     $('#productTotalPrice').blur(function()
     {
         if( $(this).val() )
         {
             $('#productPricePerPerson').prop('disabled', true);
             $('#invoiceprice').prop('disabled', true);
         }
         else
         {
             $('#productPricePerPerson').prop('disabled', false);
             $('#invoiceprice').prop('disabled', false);
         }
     });

     $('#productPricePerPerson').blur(function()
     {
         if( $(this).val() )
         {
             $('#productTotalPrice').prop('disabled', true);
             $('#invoiceprice').prop('disabled', true);
         }
         else
         {
             $('#productTotalPrice').prop('disabled', false);
             $('#invoiceprice').prop('disabled', false);
         }
     });

     $('#selecctall').click(function(event) {  //on click
         $('.checkbox').each(function() { //loop through each checkbox
             this.checked = true;  //select all checkboxes with class "checkbox1"
         });
     });

     $('#deselecctall').click(function(event) {  //on click
         $('.checkbox').each(function() { //loop through each checkbox
             this.checked = false; //deselect all checkboxes with class "checkbox1"
         });
     });


 });

function addOrder(data)
{
    $('#order-history').prepend('<tr><td>' + data.date + '</td><td>' + data.product  + '</td><td>' + data.amount + '</td></tr>');
    var totals = $('#order-totals');
    var tr = totals.find('#'+ data.id);

    if(tr.length == 0){
        totals.prepend('<tr id="' + data.id + '"><td>' + data.product + '</td><td>' + data.amount + '</td></tr>')
    }
    else{
        var currentAmount = tr.children('td').eq(1).text();
        tr.children('td').eq(1).text((+currentAmount + +data.amount));
    }
}
function filter(input, table)
{

    $(input).keyup(function(event)
    {

        if (event.keyCode == 27 || $(this).val() == '') {
            $(this).val('');
            $('tbody tr').removeClass('visible').show().addClass('visible').css({display: 'table-row'});
        }
        else
        {

            var trimmedInput;
            trimmedInput=	$.trim($(input).val()).toLowerCase(); //trim white space
            //query = query.replace(/ /gi, '|'); //add OR for regex query
            $('table' + table + ' tbody tr').each(function() {

                ($(this).text().search(new RegExp(trimmedInput, 'i')) < 0) ? $(this).hide().removeClass('visible') : $(this).show().addClass('visible');
            });
        }
    });
}


function ajax2(route, action, data, modal)
{
    $('#errors').empty();
    $('#errorsModal').empty();
    var result;
    $.ajax({
        type: "GET",
        url: route.concat('/', action),
        async: false,
        data : data,
        dataType: "json",
        success: function(response){
            switch(route)
            {
                case 'member':

                    if(action == 'create')
                    {

                        $('#members').prepend('<tr> <td >' + response.response.firstname + '</td><td >' + response.response.lastname + '</td><td><a href="#" class="btn-order" id="' + response.response.Id + '"><i class="fa fa-plus fa-fw fa-lg"></i></a></td> </tr>');
                        $('tbody tr').removeClass('visible').show().addClass('visible').css({display: 'table-row'});
                    }
                    else if(action == 'edit')
                    {
                        $( "#member-edit" ).modal('hide');
                        $(".modal-title").text(response.name);
                        ajax2(route, 'all', data, true);
                    }
                    else if(action == 'delete')
                    {
                        $( "#member-edit" ).modal('hide');
                        $( "#member-order" ).modal('hide');
                        ajax2(route, 'all', data, true);
                    }
                    else
                    {
                        $('#members').find("tr:gt(0)").remove();
                        $.each(response, function (i, response) {
                            $('#members').append('<tr> <td >' + response.Name + '</td><td><a href="#" class="btn-order" id="' + response.Id + '" name="' + response.Name + '"><i class="fa fa-plus fa-fw fa-lg"></i></a></td> </tr>');
                        });
                    }
                break;
                case 'order':

                    if(action == 'create')
                    {
                        $('#order-history').prepend('<tr><td>' + response.date + '</td><td>' + jQuery('input[name="product"]').val()  + '</td><td>' + jQuery('input[name="amount"]').val() + '</td></tr>');
                        ajax2(route, 'totals', data, modal);
                    }
                    else if(action == 'history')
                    {
                        $('#order-history').find("tr:gt(0)").remove();
                        $.each(response, function (i, response) {
                            $('#order-history').prepend('<tr><td>' + response.created_At + '</td><td>' +  response.Name  + '</td><td>' +  response.Amount  + '</td></tr>');
                        });
                    }
                    else if(action == 'totals')
                    {
                        $('#member-products').find("tr:gt(0)").remove();
                        $.each(response, function (i, response) {
                            $('#member-products').prepend('<tr><td>' +  response.Name  + '</td><td>' +  response.Amount  + '</td></tr>');
                        });
                    }
                break;
                case 'group':

                    if(action == 'create')
                    {

                        $('#members').prepend('<tr> <td >' + response.response.Name + '</td><td><a href="#" class="btn-order" id="' + response.response.Id + '" name="' + response.response.Name + '"><i class="fa fa-plus fa-fw fa-lg"></i></a></td> </tr>');
                        $('tbody tr').removeClass('visible').show().addClass('visible').css({display: 'table-row'});
                    }
                    else if(action == 'members')
                    {
                        $('#groupmembers').find("tr:gt(0)").remove();
                        $.each(response, function (i, response) {
                            $('#groupmembers-table').prepend('<tr id="' + response.Id + '"><td>' +  response.Name  + '</td><td><a href="#" class="btn-delete-group-member" id="' + response.Id + '"><i class="fa fa-trash-o fa-fw fa-lg"></i></a></td></tr>');
                        });
                    }
                    else if(action == 'addMember')
                    {
                        $('#groupmembers-table').prepend('<tr id="' + response.response.Id + '"><td>' +  response.response.Name  + '</td><td><a href="#" class="btn-delete-group-member" id="' + response.response.Id + '"><i class="fa fa-trash-o fa-fw fa-lg"></i></a></td></tr>');
                    }
                    else if(action == 'deleteMember')
                    {
                       $('#' +response.Id).remove();
                    }
                    else
                    {
                        $('#members').find("tr:gt(0)").remove();
                        $.each(response, function (i, response) {
                            $('#members').append('<tr> <td >' + response.Name + '</td><td><a href="#" class="btn-order" id="' + response.Id + '" name="' + response.Name + '"><i class="fa fa-plus fa-fw fa-lg"></i></a></td> </tr>');
                        });
                    }
                    break;
                case 'product':

                    if(action == 'create')
                    {
                        $('#products').prepend('<tr id="' + response.response.Id + '"> <td>' + response.response.Name + '</td> <td>' + response.response.Price + '</td> <td>  <a href="#" class="btn-edit" id="'+ response.response.Id +'" name="' + response.response.Name + '"><i class="fa fa-edit fa-fw fa-lg"></i></a> </td> </tr>');
                        $('tbody tr').removeClass('visible').show().addClass('visible').css({display: 'table-row'});
                    }
                    else if(action == 'update')
                    {
                        $('#' +response.response.Id).replaceWith('<tr id="' + response.response.Id + '"> <td>' + response.response.Name + '</td> <td>' + response.response.Price + '</td> <td>  <a href="#" class="btn-edit" id="'+ response.response.Id +'" name="' + response.response.Name + '"><i class="fa fa-edit fa-fw fa-lg"></i></a> </td> </tr>');
                        $('#product-edit').modal('hide');
                    }
                    else if(action == 'delete')
                    {
                        $('#' +response.Id).remove();
                        $('#product-edit').modal('hide');
                    }

                    break;
                case 'fiscus':
                    if(action == 'create')
                    {
                        resetInput(false, true);
                        ajax2('fiscus', 'price', "invoiceId="+ response, false);
                        $('#errors').append('<div class="alert alert-success" role="alert"> Succesfully added invoicelines to members</div>')
                    }
                    else if(action == 'addInvoice')
                    {
                        resetInput(false, true);
                        $('#errors').append('<div class="alert alert-success" role="alert"> Succesfully added invoice product</div>');
                        result =  response;

                    }
                    else if(action == 'deleteInvoice')
                    {
                        window.location.reload(true);
                        $('#errors').append('<div class="alert alert-success" role="alert"> Succesfully deleted invoice product</div>')
                    }
                    else if(action == 'price')
                    {
                        $('.checkbox').each(function() { //loop through each checkbox
                            this.checked = false; //deselect all checkboxes with class "checkbox1"
                            this.disabled = false;
                        });
                        $('#productPrices').html("");

                        $.each(response[0], function (i, response) {
                            $('#productPrices').append($('<option>', {
                                value: response.id,
                                text: response.ProductPrice
                            }));

                        });
                        $.each(response[1], function (i, response) {
                            count++;
                            $("#"+response.memberId).attr("disabled", true);


                        });


                    }
                    else if(action == 'changeprice')
                    {
                        $('.checkbox').each(function() { //loop through each checkbox
                            this.checked = false; //deselect all checkboxes with class "checkbox1"
                            this.disabled = false;
                        });
                        var price = $( "#productPrices option:selected" ).text();
                        var count = 0;
                        $('#productPricePerPerson').val(price);

                        $.each(response[0], function (i, response) {
                            count++;
                            $("#"+response.memberId).prop("checked", true);
                            $("#"+response.memberId).attr("disabled", false);


                        });

                        $('#productDescription').val(response[1].description);
                        $('#totalChecked').html(count + ' Selected');
                        $('#price').html('&euro;' + price + '  per person');
                        $('#finalPrice').val(price);
                        $('#isupdate').val(true);
                        $('#productPricePerPerson').focus();
                        $('#errors').append('<div class="alert alert-info" role="alert"> Now in override mode: ' + price + '</div>')

                    }
                    else if(action == 'deleteprice')
                    {
                        resetInput(false, true);
                        ajax2('fiscus', 'price', "invoiceId="+ response, false);
                        $('#errors').append('<div class="alert alert-success" role="alert"> Succesfully deleted invoice price</div>');
                    }


                    break;
                case 'invoice':
                    if(action == 'select')
                    {
                        window.location.reload(true);
                    }
                    else if(action == 'newInvoiceGroup')
                    {
                        window.location.reload(true);
                    }
                    break;
            }
        },
        error: function( data ) {
            var response = JSON.parse(data.responseText);
            $.each(response.errors, function (key, value) {
                if (modal)
                {
                    $('#errorsModal').append('<div class="bg-danger alert">' + value + '</div>');
                }
                else
                {
                    $('#errors').append('<div class="bg-danger alert">' + value + '</div>');
                }
            });
        }


    });
    if(result)
        return result;
}

function resetInput(prodcut, messages)
{
    if(prodcut)
    {
        $('#productName').empty();
    }
    if(messages)
    {
        $('#errors').html("");
    }

    $('#productDescription').val("");
    $('#productPrices').val("");
    $('#productTotalPrice').val("");
    $('#productTotalPrice').prop('disabled', false);
    $('#productPricePerPerson').val("");
    $('#productPricePerPerson').prop('disabled', false);
    $('#filter').val("");
    $('#isupdate').val(false);

    $('#totalChecked').html('0 Selected');
    $('#price').html('&euro;0  per person');
    $('#finalPrice').val("");

    $('.checkbox').each(function() { //loop through each checkbox
        this.checked = false; //deselect all checkboxes with class "checkbox1"
        this.disabled = false;
    });
}

function isNumber(evt, allowDecimal) 
{
   var charCode = (evt.which) ? evt.which : event.keyCode
	if(charCode == 46 && !allowDecimal)
		return false;
	
   if (charCode != 45 && (charCode != 46 || $(this).val().indexOf('.') != -1) && 
           (charCode < 48 || charCode > 57))
       return false;

   return true;
}


function calcutatePrice() {
    if ($('#productTotalPrice').val() && $('#productPricePerPerson').val()) {
        $('#finaltotalprice').val('0');
        $('#finalpriceperperson').val('0');
    }
    else if ($('#productTotalPrice').val()) {
        $('#finaltotalprice').val($('#productTotalPrice').val());
        $('#finalpriceperperson').val(parseFloat($('#productTotalPrice').val() / $('.checkbox:checked:enabled').length).toFixed(2));

    }
    else if ($('#productPricePerPerson').val()) {
        $('#finaltotalprice').val(parseFloat($('#productPricePerPerson').val() * $('.checkbox:checked:enabled').length).toFixed(2));
        $('#finalpriceperperson').val($('#productPricePerPerson').val());
    }
    else {
        $('#finaltotalprice').val('0');
        $('#finalpriceperperson').val('0');
        ;

    }
}








(function ( $ ) {

    $.fn.bootstrapNumber = function( options ) {

        var settings = $.extend({
            upClass: 'default',
            downClass: 'default',
            center: true
        }, options );

        return this.each(function(e) {
            var self = $(this);
            var clone = self.clone();

            var min = self.attr('min');
            var max = self.attr('max');

            function setText(n) {
                if((min && n < min) || (max && n > max)) {
                    return false;
                }

                clone.val(n);
                return true;
            }

            var group = $("<div class='input-group'></div>");
            var down = $("<button type='button'>-</button>").attr('class', 'btn btn-' + settings.downClass).click(function()
            {
                var n = parseInt(clone.val()) - 1
                if(n== 0)
                {
                    n= -1;
                }
                setText(n);
            });
            var up = $("<button type='button'>+</button>").attr('class', 'btn btn-' + settings.upClass).click(function()
            {
                var n = parseInt(clone.val()) + 1
                if(n== 0)
                {
                    n= 1;
                }
                setText(n);
            });
            $("<span class='input-group-btn'></span>").append(down).appendTo(group);
            clone.appendTo(group);
            if(clone) {
                clone.css('text-align', 'center');
            }
            $("<span class='input-group-btn'></span>").append(up).appendTo(group);

            // remove spins from original
            clone.attr('type', 'text').keydown(function (e) {
                if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                    (e.keyCode == 65 && e.ctrlKey === true) ||
                    (e.keyCode >= 35 && e.keyCode <= 39)) {
                    return;
                }
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }

                var c = String.fromCharCode(e.which);
                var n = parseInt(clone.val() + c);

                if((min && n < min) || (max && n > max)) {
                    e.preventDefault();
                }
            });

            self.replaceWith(group);
        });
    };
} ( jQuery ));

var reloadModal = function() {
    var mfp = $.magnificPopup.instance;
    mfp.updateItemHTML();
};

var reload = function() {
    document.location = document.location;
};

/**
 * Ajax submit
 */$(document).on('click change', '[data-ajax-submit]', function() {


    var form = $(this).data('ajax-submit');
    var success_callback = $(this).data('ajax-callback-function');
    form = $(form);

    form.ajaxSubmit({

        success: function (data) {
            form.find('.has-error').removeClass('has-error');
            if (data.errors != undefined) {
                $.each(data.errors, function (input_name) {
                    var input = $('[name^=' + input_name + ']');
                    var form_group = input.closest('.form-group');

                    form_group.addClass('has-error');
                });

                new PNotify({
                    title: 'Not all form values are valid',
                    text: 'Check the red marked form values.',
                    addclass: 'notification-error',
                    icon: 'fa fa-exclamation'
                });
            }

            if (data.success) {
                if(typeof success_callback == 'function')
                {
                    success_callback(data)
                }
                else
                {
                    window[success_callback](data);
                }
                new PNotify({
                    title: 'success',
                    text: data.message,
                    type: 'success',
                    addclass: 'notification-'
                });

            }
        },
        type: $(this).data('ajax-type'),
        errors: function(data)
        {
            console.log(data);
        }
    });

    return false;
});


$('[data-notification]').each(function(i, item) {

    var title = $(item).find('h4').html();
    var text = $(item).find('div.message').text();
    var type = $(item).data('type');

    new PNotify({
        title: title,
        text: text.trim(),
        addclass: 'notification-' + type,
        icon: 'fa fa-exclamation'
    });
});

$('[data-error-field]').each(function(i, item) {

    var field = $('[name=' + $(item).data('error-field') + ']');
    field.closest('.form-group').addClass('has-error');
});

function afterRefreshMessage(data)
{
    localStorage.setItem("success", true);
    localStorage.setItem("message", data.message);
    location.reload(true);

}

